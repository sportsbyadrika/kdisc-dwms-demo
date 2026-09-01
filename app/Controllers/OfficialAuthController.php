<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;

class OfficialAuthController
{
    public function loginForm(): void
    {
        if (Auth::check('official')) {
            redirect('/official/dashboard');
        }
        view('auth.login', [
            'pageTitle'  => 'Officials login',
            'guardIcon'  => 'shield',
            'tone'       => 'ink',
            'heading'    => 'Officials sign in',
            'sub'        => 'For departmental users. Accounts are created by an administrator.',
            'action'     => '/official/login',
            'asideTitle' => 'Departmental access',
            'asideSub'   => 'Verify employers, publish skilling programmes and career services, and manage offices, users and roles.',
            'points'     => [
                ['shield-check', 'Role-based access', 'Each user sees only what their role permits.'],
                ['building', 'Office hierarchy', 'Offices, departments and sections in one tree.'],
                ['clipboard', 'Full audit trail', 'Every administrative action is logged.'],
            ],
            'altLinks'   => [
                ['Job seeker login', '/login'],
                ['Employer login', '/employer/login'],
            ],
        ]);
    }

    public function login(): void
    {
        verify_csrf();
        $email    = strtolower((string) input('email'));
        $password = (string) input('password');

        $user = DB::first(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name, r.permissions
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = ?',
            [$email]
        );
        if (!$user || !password_verify($password, $user['password'])) {
            flash('error', 'The e-mail address or password is not correct.');
            flash_errors(['email' => 'Check your e-mail address and password.'], ['email' => $email]);
            redirect('/official/login');
        }
        if (!(int) $user['is_active']) {
            flash('error', 'This account has been deactivated. Please contact your administrator.');
            redirect('/official/login');
        }

        DB::update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);
        Auth::login('official', $user);
        $_SESSION['auth']['official']['permissions'] = json_decode((string) $user['permissions'], true) ?: [];
        log_activity('official', (int) $user['id'], 'login', 'Signed in');

        if ((int) $user['must_reset']) {
            flash('warning', 'Please set a new password before continuing.');
            redirect('/official/password');
        }
        flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect('/official/dashboard');
    }
}
