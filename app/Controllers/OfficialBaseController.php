<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;

abstract class OfficialBaseController
{
    protected array $user;

    public function __construct()
    {
        Auth::requireGuard('official', '/official/login');
        $row = DB::first(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name, r.permissions
             FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?',
            [Auth::id('official')]
        );
        if (!$row || !(int) $row['is_active']) {
            Auth::logout('official');
            flash('error', 'Your session is no longer valid. Please sign in again.');
            redirect('/official/login');
        }
        $this->user = $row;
        // Permissions can change while a user is signed in.
        $_SESSION['auth']['official']['permissions'] = json_decode((string) $row['permissions'], true) ?: [];
        Auth::refresh('official', ['name' => $row['name'], 'role' => $row['role_slug'], 'role_name' => $row['role_name']]);
    }

    protected function id(): int
    {
        return (int) $this->user['id'];
    }

    protected function isSuperAdmin(): bool
    {
        return $this->user['role_slug'] === 'super_admin';
    }

    /** Stop the request unless the signed-in user holds the permission. */
    protected function authorise(string $permission): void
    {
        if (!Auth::can($permission)) {
            abort(403, 'Your role does not allow this action. Ask a super administrator if you need access.');
        }
    }

    protected function shell(string $template, array $data = []): void
    {
        $slot = render($template, $data + ['me' => $this->user], null);

        echo render('layouts.shell-page', [
            'pageTitle' => $data['pageTitle'] ?? 'Administration',
            'slot'      => $slot,
            'nav'       => $this->nav(),
            'identity'  => [
                'name'     => $this->user['name'],
                'subtitle' => $this->user['designation'] ?: $this->user['email'],
                'photo'    => null,
                'badges'   => ['<span class="badge-blue">' . icon('shield', 'h-3 w-3') . e($this->user['role_name']) . '</span>'],
            ],
        ], 'app');
    }

    /** Only the sections the user's role can reach are listed. */
    protected function nav(): array
    {
        $nav = ['Overview' => [['label' => 'Dashboard', 'path' => '/official/dashboard', 'icon' => 'grid']]];

        $content = [];
        if (Auth::can('hero.manage')) {
            $content[] = ['label' => 'Home page hero', 'path' => '/official/hero', 'icon' => 'layers', 'match' => '/official/hero'];
        }
        if (Auth::can('skills.manage')) {
            $content[] = ['label' => 'Skilling programmes', 'path' => '/official/skills', 'icon' => 'graduation', 'match' => '/official/skills'];
        }
        if (Auth::can('careers.manage')) {
            $content[] = ['label' => 'Career services', 'path' => '/official/careers', 'icon' => 'compass', 'match' => '/official/careers'];
        }
        if ($content) {
            $nav['Content'] = $content;
        }

        $registry = [];
        if (Auth::can('employers.verify')) {
            $pending = (int) DB::value("SELECT COUNT(*) FROM employers WHERE status = 'pending' AND profile_completed = 1");
            $registry[] = ['label' => 'Employer verification', 'path' => '/official/employers', 'icon' => 'building', 'count' => $pending];
        }
        if (Auth::can('jobs.moderate')) {
            $registry[] = ['label' => 'Job titles', 'path' => '/official/jobs', 'icon' => 'briefcase'];
        }
        if (Auth::can('seekers.view')) {
            $registry[] = ['label' => 'Job seekers', 'path' => '/official/seekers', 'icon' => 'users'];
        }
        if (Auth::can('messages.view')) {
            $unread = (int) DB::value('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
            $registry[] = ['label' => 'Enquiries', 'path' => '/official/messages', 'icon' => 'inbox', 'count' => $unread];
        }
        if ($registry) {
            $nav['Registry'] = $registry;
        }

        $admin = [];
        if (Auth::can('offices.manage')) {
            $admin[] = ['label' => 'Offices & sections', 'path' => '/official/offices', 'icon' => 'building', 'match' => '/official/offices'];
        }
        if (Auth::can('users.manage')) {
            $admin[] = ['label' => 'Users', 'path' => '/official/users', 'icon' => 'users', 'match' => '/official/users'];
        }
        if (Auth::can('roles.manage')) {
            $admin[] = ['label' => 'Roles & permissions', 'path' => '/official/roles', 'icon' => 'shield', 'match' => '/official/roles'];
        }
        if (Auth::can('settings.manage')) {
            $admin[] = ['label' => 'Site settings', 'path' => '/official/settings', 'icon' => 'cog'];
        }
        if ($admin) {
            $nav['Administration'] = $admin;
        }

        $nav['Account'] = [['label' => 'Change password', 'path' => '/official/password', 'icon' => 'key']];
        return $nav;
    }
}
