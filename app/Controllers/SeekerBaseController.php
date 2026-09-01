<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\Profile;

/**
 * Shared plumbing for every signed-in job seeker screen: the guard check,
 * the current seeker row, and the sidebar navigation.
 */
abstract class SeekerBaseController
{
    protected array $seeker;

    public function __construct()
    {
        Auth::requireGuard('seeker', '/login');
        $row = DB::first('SELECT * FROM job_seekers WHERE id = ?', [Auth::id('seeker')]);
        if (!$row || !(int) $row['is_active']) {
            Auth::logout('seeker');
            flash('error', 'Your session is no longer valid. Please sign in again.');
            redirect('/login');
        }
        $this->seeker = $row;
        Auth::refresh('seeker', ['name' => $row['name'], 'photo' => $row['photo'], 'email' => $row['email']]);
    }

    protected function id(): int
    {
        return (int) $this->seeker['id'];
    }

    /** Render a seeker page inside the dashboard shell. */
    protected function shell(string $template, array $data = []): void
    {
        $id      = $this->id();
        $slot    = render($template, $data + ['seeker' => $this->seeker], null);
        $summary = Profile::completeness($id);

        $badges = [];
        $badges[] = $this->seeker['email_verified']
            ? '<span class="badge-green">' . icon('check', 'h-3 w-3') . 'E-mail</span>'
            : '<span class="badge-amber">' . icon('alert', 'h-3 w-3') . 'E-mail</span>';
        $badges[] = $this->seeker['kyc_status'] === 'verified'
            ? '<span class="badge-green">' . icon('shield-check', 'h-3 w-3') . 'e-KYC</span>'
            : '<span class="badge-gray">' . icon('shield', 'h-3 w-3') . 'e-KYC pending</span>';

        echo render('layouts.shell-page', [
            'pageTitle' => $data['pageTitle'] ?? 'Dashboard',
            'slot'      => $slot,
            'nav'       => $this->nav($id),
            'identity'  => [
                'name'     => $this->seeker['name'],
                'subtitle' => $this->seeker['headline'] ?: $this->seeker['email'],
                'photo'    => $this->seeker['photo'],
                'badges'   => $badges,
                'score'    => $summary['score'],
            ],
        ], 'app');
    }

    protected function nav(int $id): array
    {
        $count = static fn(string $sql) => (int) DB::value($sql, [$id]);

        return [
            'Overview' => [
                ['label' => 'Dashboard', 'path' => '/dashboard', 'icon' => 'grid'],
                ['label' => 'Applications', 'path' => '/dashboard/applications', 'icon' => 'send',
                 'count' => $count('SELECT COUNT(*) FROM applications WHERE seeker_id = ?')],
                ['label' => 'Saved jobs', 'path' => '/dashboard/saved', 'icon' => 'bookmark',
                 'count' => $count('SELECT COUNT(*) FROM wishlists WHERE seeker_id = ?')],
            ],
            'My profile' => [
                ['label' => 'Basic details', 'path' => '/dashboard/profile', 'icon' => 'user'],
                ['label' => 'e-KYC', 'path' => '/dashboard/kyc', 'icon' => 'fingerprint',
                 'flag' => $this->seeker['kyc_status'] !== 'verified'],
                ['label' => 'Addresses', 'path' => '/dashboard/address', 'icon' => 'map-pin'],
                ['label' => 'Documents & proofs', 'path' => '/dashboard/documents', 'icon' => 'id-card'],
                ['label' => 'Resume', 'path' => '/dashboard/resume', 'icon' => 'document'],
            ],
            'Career record' => [
                ['label' => 'Qualifications', 'path' => '/dashboard/qualifications', 'icon' => 'graduation'],
                ['label' => 'Experience', 'path' => '/dashboard/experience', 'icon' => 'briefcase'],
                ['label' => 'Certifications', 'path' => '/dashboard/certifications', 'icon' => 'shield-check'],
                ['label' => 'Achievements', 'path' => '/dashboard/achievements', 'icon' => 'star'],
                ['label' => 'Skills', 'path' => '/dashboard/skills', 'icon' => 'sparkles'],
            ],
            'Account' => [
                ['label' => 'Change password', 'path' => '/dashboard/password', 'icon' => 'key'],
            ],
        ];
    }
}
