<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;

abstract class EmployerBaseController
{
    protected array $employer;

    public function __construct()
    {
        Auth::requireGuard('employer', '/employer/login');
        $row = DB::first('SELECT * FROM employers WHERE id = ?', [Auth::id('employer')]);
        if (!$row || $row['status'] === 'suspended') {
            Auth::logout('employer');
            flash('error', 'Your session is no longer valid. Please sign in again.');
            redirect('/employer/login');
        }
        $this->employer = $row;
        Auth::refresh('employer', ['name' => $row['company_name'], 'photo' => $row['logo'], 'email' => $row['email']]);
    }

    protected function id(): int
    {
        return (int) $this->employer['id'];
    }

    /** Publishing is gated on a complete profile and a verified organisation. */
    protected function requireVerified(): void
    {
        if (!$this->employer['profile_completed']) {
            flash('info', 'Complete your organisation profile before publishing job titles.');
            redirect('/employer/profile');
        }
    }

    protected function shell(string $template, array $data = []): void
    {
        $slot = render($template, $data + ['employer' => $this->employer], null);

        $statusBadge = [
            'verified' => '<span class="badge-green">' . icon('shield-check', 'h-3 w-3') . 'Verified</span>',
            'pending'  => '<span class="badge-amber">' . icon('clock', 'h-3 w-3') . 'Verification pending</span>',
            'rejected' => '<span class="badge-red">' . icon('x-circle', 'h-3 w-3') . 'Rejected</span>',
            'suspended'=> '<span class="badge-red">Suspended</span>',
        ][$this->employer['status']] ?? '';

        echo render('layouts.shell-page', [
            'pageTitle' => $data['pageTitle'] ?? 'Employer dashboard',
            'slot'      => $slot,
            'nav'       => $this->nav(),
            'identity'  => [
                'name'     => $this->employer['company_name'],
                'subtitle' => $this->employer['industry'] ?: $this->employer['email'],
                'photo'    => $this->employer['logo'],
                'badges'   => [$statusBadge],
                'score'    => $this->profileProgress(),
            ],
        ], 'app');
    }

    protected function profileProgress(): int
    {
        $required = [
            'company_name', 'ownership_type', 'industry', 'employee_range', 'pan',
            'address_line1', 'district', 'pincode', 'contact_person', 'contact_mobile',
        ];
        $done = 0;
        foreach ($required as $f) {
            if (!empty($this->employer[$f])) {
                $done++;
            }
        }
        return (int) round($done / count($required) * 100);
    }

    protected function nav(): array
    {
        $id = $this->id();
        return [
            'Overview' => [
                ['label' => 'Dashboard', 'path' => '/employer/dashboard', 'icon' => 'grid'],
                ['label' => 'Applications', 'path' => '/employer/applications', 'icon' => 'inbox',
                 'count' => (int) DB::value('SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = ?', [$id])],
            ],
            'Hiring' => [
                ['label' => 'Job titles', 'path' => '/employer/jobs', 'icon' => 'briefcase', 'match' => '/employer/jobs',
                 'count' => (int) DB::value('SELECT COUNT(*) FROM jobs WHERE employer_id = ?', [$id])],
                ['label' => 'Publish a job title', 'path' => '/employer/jobs/create', 'icon' => 'plus'],
            ],
            'Organisation' => [
                ['label' => 'Company profile', 'path' => '/employer/profile', 'icon' => 'building',
                 'flag' => !$this->employer['profile_completed']],
                ['label' => 'Documents', 'path' => '/employer/documents', 'icon' => 'document'],
                ['label' => 'Change password', 'path' => '/employer/password', 'icon' => 'key'],
            ],
        ];
    }
}
