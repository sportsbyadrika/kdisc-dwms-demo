<?php
namespace App\Core;

/**
 * Three independent guards share one session:
 *   seeker   - job seekers
 *   employer - employers
 *   official - departmental users / administrators
 */
class Auth
{
    public const GUARDS = ['seeker', 'employer', 'official'];

    public static function login(string $guard, array $user): void
    {
        $_SESSION['auth'][$guard] = [
            'id'         => (int) $user['id'],
            'name'       => $user['name'] ?? ($user['company_name'] ?? ''),
            'email'      => $user['email'] ?? '',
            'role'       => $user['role_slug'] ?? null,
            'role_name'  => $user['role_name'] ?? null,
            'office_id'  => $user['office_id'] ?? null,
            'logged_at'  => time(),
        ];
        session_regenerate_id(true);
    }

    public static function user(string $guard): ?array
    {
        return $_SESSION['auth'][$guard] ?? null;
    }

    public static function id(string $guard): ?int
    {
        return isset($_SESSION['auth'][$guard]) ? (int) $_SESSION['auth'][$guard]['id'] : null;
    }

    public static function check(string $guard): bool
    {
        return isset($_SESSION['auth'][$guard]);
    }

    public static function refresh(string $guard, array $patch): void
    {
        if (isset($_SESSION['auth'][$guard])) {
            $_SESSION['auth'][$guard] = array_merge($_SESSION['auth'][$guard], $patch);
        }
    }

    public static function logout(?string $guard = null): void
    {
        if ($guard === null) {
            unset($_SESSION['auth']);
        } else {
            unset($_SESSION['auth'][$guard]);
        }
    }

    /** Any guard currently signed in (first match wins for the nav bar). */
    public static function current(): ?array
    {
        foreach (self::GUARDS as $g) {
            if (self::check($g)) {
                return ['guard' => $g] + self::user($g);
            }
        }
        return null;
    }

    public static function requireGuard(string $guard, string $loginPath): array
    {
        if (!self::check($guard)) {
            $_SESSION['intended'] = current_path();
            flash('error', 'Please sign in to continue.');
            redirect($loginPath);
        }
        return self::user($guard);
    }

    /** Official permission check driven by the role's permission list. */
    public static function can(string $permission): bool
    {
        $u = self::user('official');
        if (!$u) {
            return false;
        }
        if ($u['role'] === 'super_admin') {
            return true;
        }
        $perms = $_SESSION['auth']['official']['permissions'] ?? [];
        return in_array($permission, $perms, true) || in_array('*', $perms, true);
    }
}
