<?php
namespace App\Controllers;

use App\Core\Database as DB;
use App\Core\Lookup;

/**
 * Super-admin territory: the office tree, user accounts and roles.
 */
class OfficialAdminController extends OfficialBaseController
{
    /* -------------------------------------------------------------- offices */

    public function offices(): void
    {
        $this->authorise('offices.manage');
        $offices = DB::all(
            'SELECT o.*, p.name AS parent_name,
                    (SELECT COUNT(*) FROM users u WHERE u.office_id = o.id) AS users,
                    (SELECT COUNT(*) FROM offices c WHERE c.parent_id = o.id) AS children
             FROM offices o LEFT JOIN offices p ON p.id = o.parent_id
             ORDER BY COALESCE(o.parent_id, o.id), o.parent_id IS NOT NULL, o.name'
        );

        $editing = null;
        if ($editId = (int) input('edit')) {
            $editing = DB::first('SELECT * FROM offices WHERE id = ?', [$editId]);
        }

        $this->shell('official.offices', [
            'pageTitle' => 'Offices, departments and sections',
            'offices'   => $offices,
            'tree'      => $this->buildTree($offices),
            'editing'   => $editing,
            'parents'   => DB::all("SELECT id, name, type FROM offices WHERE is_active = 1 ORDER BY type = 'office' DESC, name"),
            'districts' => Lookup::DISTRICTS,
        ]);
    }

    public function officeSave(): void
    {
        verify_csrf();
        $this->authorise('offices.manage');

        $id   = (int) input('office_id');
        $data = [
            'name'      => input('name'),
            'code'      => strtoupper((string) input('code')) ?: null,
            'type'      => input('type'),
            'parent_id' => (int) input('parent_id') ?: null,
            'district'  => input('district') ?: null,
            'address'   => input('address') ?: null,
            'phone'     => input('phone') ?: null,
            'email'     => input('email') ?: null,
            'is_active' => input('is_active') ? 1 : 0,
        ];
        $errors = validate([
            'name'  => 'required|min:3|max:150',
            'code'  => 'max:40',
            'type'  => 'required|in:office,department,section',
            'email' => 'email|max:150',
        ], $data);

        // An office sits at the top; departments and sections need a parent.
        if (!$errors && $data['type'] !== 'office' && !$data['parent_id']) {
            $errors['parent_id'] = 'A ' . $data['type'] . ' must sit under a parent office.';
        }
        if (!$errors && $data['type'] === 'office') {
            $data['parent_id'] = null;
        }
        if (!$errors && $id && $data['parent_id'] === $id) {
            $errors['parent_id'] = 'An office cannot be its own parent.';
        }
        if (!$errors && $data['code'] && DB::value('SELECT id FROM offices WHERE code = ? AND id <> ?', [$data['code'], $id])) {
            $errors['code'] = 'That code is already in use.';
        }
        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/official/offices' . ($id ? '?edit=' . $id : ''));
        }

        if ($id) {
            DB::update('offices', $data, 'id = :id', ['id' => $id]);
            $message = 'Office updated.';
        } else {
            $id = DB::insert('offices', $data);
            $message = 'Office created.';
        }
        log_activity('official', $this->id(), 'office_saved', $data['name'] . ' saved', 'offices', $id);
        flash('success', $message);
        redirect('/official/offices');
    }

    public function officeDelete(string $id): void
    {
        verify_csrf();
        $this->authorise('offices.manage');
        $office = DB::first('SELECT * FROM offices WHERE id = ?', [(int) $id]);
        if (!$office) {
            abort(404, 'That office does not exist.');
        }
        if ((int) DB::value('SELECT COUNT(*) FROM offices WHERE parent_id = ?', [$office['id']]) > 0) {
            flash('error', 'Remove or reassign the departments and sections under this office first.');
            redirect('/official/offices');
        }
        if ((int) DB::value('SELECT COUNT(*) FROM users WHERE office_id = ?', [$office['id']]) > 0) {
            flash('error', 'Users are still attached to this office. Reassign them first.');
            redirect('/official/offices');
        }
        DB::delete('offices', 'id = :id', ['id' => $office['id']]);
        log_activity('official', $this->id(), 'office_deleted', $office['name'] . ' deleted');
        flash('success', 'Office deleted.');
        redirect('/official/offices');
    }

    private function buildTree(array $offices): array
    {
        $byParent = [];
        foreach ($offices as $o) {
            $byParent[$o['parent_id'] ?? 0][] = $o;
        }
        return $byParent;
    }

    /* ---------------------------------------------------------------- users */

    public function users(): void
    {
        $this->authorise('users.manage');
        $users = DB::all(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug, o.name AS office_name, o.type AS office_type
             FROM users u JOIN roles r ON r.id = u.role_id LEFT JOIN offices o ON o.id = u.office_id
             ORDER BY u.is_active DESC, u.name'
        );
        $editing = null;
        if ($editId = (int) input('edit')) {
            $editing = DB::first('SELECT * FROM users WHERE id = ?', [$editId]);
        }

        $this->shell('official.users', [
            'pageTitle' => 'Users',
            'users'     => $users,
            'editing'   => $editing,
            'roles'     => DB::all('SELECT id, name, slug FROM roles ORDER BY is_system DESC, name'),
            'offices'   => DB::all("SELECT id, name, type FROM offices WHERE is_active = 1 ORDER BY type = 'office' DESC, name"),
            'generated' => $_SESSION['generated_password'] ?? null,
        ]);
        unset($_SESSION['generated_password']);
    }

    public function userSave(): void
    {
        verify_csrf();
        $this->authorise('users.manage');

        $id   = (int) input('user_id');
        $data = [
            'name'        => input('name'),
            'designation' => input('designation') ?: null,
            'email'       => strtolower((string) input('email')),
            'mobile'      => preg_replace('/\D/', '', (string) input('mobile')) ?: null,
            'role_id'     => (int) input('role_id'),
            'office_id'   => (int) input('office_id') ?: null,
            'is_active'   => input('is_active') ? 1 : 0,
        ];
        $errors = validate([
            'name'    => 'required|min:3|max:120',
            'email'   => 'required|email|max:150',
            'mobile'  => 'mobile',
            'role_id' => 'required|numeric',
        ], $data);

        if (!$errors && DB::value('SELECT id FROM users WHERE email = ? AND id <> ?', [$data['email'], $id])) {
            $errors['email'] = 'A user already exists with this e-mail address.';
        }
        $role = DB::first('SELECT * FROM roles WHERE id = ?', [$data['role_id']]);
        if (!$errors && !$role) {
            $errors['role_id'] = 'Choose a valid role.';
        }
        // Only a super administrator may mint another one.
        if (!$errors && $role && $role['slug'] === 'super_admin' && !$this->isSuperAdmin()) {
            $errors['role_id'] = 'Only a super administrator can assign the super administrator role.';
        }
        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/official/users' . ($id ? '?edit=' . $id : ''));
        }

        if ($id) {
            // Never let a user lock themselves out of their own account.
            if ($id === $this->id()) {
                $data['is_active'] = 1;
                $data['role_id']   = (int) $this->user['role_id'];
            }
            DB::update('users', $data, 'id = :id', ['id' => $id]);
            log_activity('official', $this->id(), 'user_updated', $data['name'] . ' updated', 'users', $id);
            flash('success', 'User updated.');
        } else {
            $password = $this->generatePassword();
            $data['password']   = password_hash($password, PASSWORD_BCRYPT);
            $data['must_reset'] = 1;
            $data['created_by'] = $this->id();
            $id = DB::insert('users', $data);
            $_SESSION['generated_password'] = ['email' => $data['email'], 'password' => $password];
            log_activity('official', $this->id(), 'user_created', $data['name'] . ' created', 'users', $id);
            flash('success', 'User created. Share the temporary password shown below — it is displayed only once.');
        }
        redirect('/official/users');
    }

    public function userResetPassword(string $id): void
    {
        verify_csrf();
        $this->authorise('users.manage');
        $user = DB::first('SELECT * FROM users WHERE id = ?', [(int) $id]);
        if (!$user) {
            abort(404, 'That user does not exist.');
        }
        $password = $this->generatePassword();
        DB::update('users', [
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'must_reset' => 1,
        ], 'id = :id', ['id' => $user['id']]);
        $_SESSION['generated_password'] = ['email' => $user['email'], 'password' => $password];
        log_activity('official', $this->id(), 'user_password_reset', $user['name'] . ' password reset', 'users', (int) $user['id']);
        flash('success', 'A temporary password has been generated. It is shown once — share it securely.');
        redirect('/official/users');
    }

    public function userDelete(string $id): void
    {
        verify_csrf();
        $this->authorise('users.manage');
        $user = DB::first('SELECT * FROM users WHERE id = ?', [(int) $id]);
        if (!$user) {
            abort(404, 'That user does not exist.');
        }
        if ((int) $user['id'] === $this->id()) {
            flash('error', 'You cannot deactivate your own account.');
            redirect('/official/users');
        }
        // Deactivate rather than delete, so the audit trail survives.
        DB::update('users', ['is_active' => 0], 'id = :id', ['id' => $user['id']]);
        log_activity('official', $this->id(), 'user_deactivated', $user['name'] . ' deactivated', 'users', (int) $user['id']);
        flash('success', $user['name'] . ' has been deactivated.');
        redirect('/official/users');
    }

    private function generatePassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $out = '';
        for ($i = 0; $i < 10; $i++) {
            $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $out . '@' . random_int(10, 99);
    }

    /* ---------------------------------------------------------------- roles */

    public function roles(): void
    {
        $this->authorise('roles.manage');
        $roles = DB::all(
            'SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) AS users
             FROM roles r ORDER BY r.is_system DESC, r.name'
        );
        $editing = null;
        if ($editId = (int) input('edit')) {
            $editing = DB::first('SELECT * FROM roles WHERE id = ?', [$editId]);
        }

        $this->shell('official.roles', [
            'pageTitle'   => 'Roles and permissions',
            'roles'       => $roles,
            'editing'     => $editing,
            'permissions' => Lookup::PERMISSIONS,
        ]);
    }

    public function roleSave(): void
    {
        verify_csrf();
        $this->authorise('roles.manage');

        $id    = (int) input('role_id');
        $name  = (string) input('name');
        $perms = array_values(array_intersect((array) ($_POST['permissions'] ?? []), array_keys(Lookup::PERMISSIONS)));

        $data = [
            'name'        => $name,
            'description' => input('description') ?: null,
            'permissions' => json_encode($perms),
        ];
        $errors = validate(['name' => 'required|min:3|max:80', 'description' => 'max:255'], $data);

        $existing = $id ? DB::first('SELECT * FROM roles WHERE id = ?', [$id]) : null;
        if ($id && !$existing) {
            abort(404, 'That role does not exist.');
        }
        if (!$errors && !$perms) {
            $errors['permissions'] = 'Select at least one permission.';
        }
        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/official/roles' . ($id ? '?edit=' . $id : ''));
        }

        if ($existing) {
            // The super administrator role always keeps full access.
            if ($existing['slug'] === 'super_admin') {
                $data['permissions'] = json_encode(['*']);
            }
            DB::update('roles', $data, 'id = :id', ['id' => $id]);
            $message = 'Role updated.';
        } else {
            $slug = $this->uniqueSlug($name);
            $id = DB::insert('roles', $data + ['slug' => $slug, 'is_system' => 0]);
            $message = 'Role created.';
        }
        log_activity('official', $this->id(), 'role_saved', $name . ' saved', 'roles', $id);
        flash('success', $message);
        redirect('/official/roles');
    }

    public function roleDelete(string $id): void
    {
        verify_csrf();
        $this->authorise('roles.manage');
        $role = DB::first('SELECT * FROM roles WHERE id = ?', [(int) $id]);
        if (!$role) {
            abort(404, 'That role does not exist.');
        }
        if ((int) $role['is_system']) {
            flash('error', 'System roles cannot be deleted.');
            redirect('/official/roles');
        }
        if ((int) DB::value('SELECT COUNT(*) FROM users WHERE role_id = ?', [$role['id']]) > 0) {
            flash('error', 'Users still hold this role. Reassign them first.');
            redirect('/official/roles');
        }
        DB::delete('roles', 'id = :id', ['id' => $role['id']]);
        log_activity('official', $this->id(), 'role_deleted', $role['name'] . ' deleted');
        flash('success', 'Role deleted.');
        redirect('/official/roles');
    }

    private function uniqueSlug(string $name): string
    {
        $base = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($name)), '_') ?: 'role';
        $slug = $base;
        $i = 2;
        while (DB::value('SELECT id FROM roles WHERE slug = ?', [$slug])) {
            $slug = $base . '_' . $i++;
        }
        return $slug;
    }
}
