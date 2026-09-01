<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\Profile;

class SeekerController extends SeekerBaseController
{
    public function dashboard(): void
    {
        $id      = $this->id();
        $summary = Profile::completeness($id);
        Profile::refreshScore($id);

        $stats = [
            'applications' => (int) DB::value('SELECT COUNT(*) FROM applications WHERE seeker_id = ?', [$id]),
            'shortlisted'  => (int) DB::value("SELECT COUNT(*) FROM applications WHERE seeker_id = ? AND status IN ('shortlisted','interview','selected')", [$id]),
            'saved'        => (int) DB::value('SELECT COUNT(*) FROM wishlists WHERE seeker_id = ?', [$id]),
            'enrolments'   => (int) DB::value('SELECT COUNT(*) FROM skill_enrolments WHERE seeker_id = ?', [$id]),
        ];

        $applications = DB::all(
            "SELECT a.id, a.status, a.applied_at, j.id AS job_id, j.title, j.job_location, e.company_name
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             JOIN employers e ON e.id = j.employer_id
             WHERE a.seeker_id = ? ORDER BY a.applied_at DESC LIMIT 5",
            [$id]
        );

        $saved = DB::all(
            "SELECT w.job_id, j.title, j.last_date, e.company_name
             FROM wishlists w
             JOIN jobs j ON j.id = w.job_id
             JOIN employers e ON e.id = j.employer_id
             WHERE w.seeker_id = ? ORDER BY w.created_at DESC LIMIT 5",
            [$id]
        );

        // Simple recommendations: same district, and not already applied to.
        $district = DB::value("SELECT district FROM seeker_addresses WHERE seeker_id = ? AND address_type = 'communication'", [$id]);
        $recommended = DB::all(
            "SELECT j.id, j.title, j.job_location, j.salary_min, j.salary_max, j.last_date, e.company_name
             FROM jobs j
             JOIN employers e ON e.id = j.employer_id
             WHERE j.status = 'published' AND (j.last_date IS NULL OR j.last_date >= CURDATE())
               AND j.id NOT IN (SELECT job_id FROM applications WHERE seeker_id = ?)
             ORDER BY (j.district = ?) DESC, j.published_at DESC
             LIMIT 4",
            [$id, $district ?: '']
        );

        $this->shell('jobseeker.dashboard', [
            'pageTitle'    => 'Dashboard',
            'summary'      => $summary,
            'stats'        => $stats,
            'applications' => $applications,
            'saved'        => $saved,
            'recommended'  => $recommended,
        ]);
    }

    /* ------------------------------------------------------ applications */

    public function applications(): void
    {
        $status = input('status');
        $params = [$this->id()];
        $where  = 'a.seeker_id = ?';
        if ($status && array_key_exists($status, \App\Core\Lookup::APPLICATION_STATUS)) {
            $where .= ' AND a.status = ?';
            $params[] = $status;
        }

        $applications = DB::all(
            "SELECT a.*, j.title, j.code, j.job_location, j.last_date, j.status AS job_status,
                    e.company_name, e.logo
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             JOIN employers e ON e.id = j.employer_id
             WHERE $where
             ORDER BY a.applied_at DESC",
            $params
        );

        $counts = [];
        foreach (DB::all('SELECT status, COUNT(*) AS n FROM applications WHERE seeker_id = ? GROUP BY status', [$this->id()]) as $r) {
            $counts[$r['status']] = (int) $r['n'];
        }

        $this->shell('jobseeker.applications', [
            'pageTitle'    => 'Applications',
            'applications' => $applications,
            'counts'       => $counts,
            'total'        => array_sum($counts),
            'active'       => $status,
        ]);
    }

    public function withdraw(string $id): void
    {
        verify_csrf();
        $row = DB::first('SELECT * FROM applications WHERE id = ? AND seeker_id = ?', [(int) $id, $this->id()]);
        if (!$row) {
            abort(404, 'That application no longer exists.');
        }
        if (in_array($row['status'], ['selected', 'rejected', 'withdrawn'], true)) {
            flash('error', 'This application can no longer be withdrawn.');
            redirect('/dashboard/applications');
        }
        DB::update('applications', ['status' => 'withdrawn'], 'id = :id', ['id' => $row['id']]);
        log_activity('seeker', $this->id(), 'application_withdrawn', 'Application withdrawn', 'applications', (int) $row['id']);
        flash('success', 'Your application has been withdrawn.');
        redirect('/dashboard/applications');
    }

    /* -------------------------------------------------------- saved jobs */

    public function saved(): void
    {
        $jobs = DB::all(
            "SELECT j.*, e.company_name, e.logo,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.seeker_id = w.seeker_id) AS applied
             FROM wishlists w
             JOIN jobs j ON j.id = w.job_id
             JOIN employers e ON e.id = j.employer_id
             WHERE w.seeker_id = ?
             ORDER BY w.created_at DESC",
            [$this->id()]
        );
        $this->shell('jobseeker.saved', ['pageTitle' => 'Saved jobs', 'jobs' => $jobs]);
    }

    public function unsave(string $id): void
    {
        verify_csrf();
        DB::delete('wishlists', 'seeker_id = :sid AND job_id = :jid', ['sid' => $this->id(), 'jid' => (int) $id]);
        flash('success', 'Removed from your saved jobs.');
        redirect('/dashboard/saved');
    }

    public function passwordForm(): void
    {
        $this->shell('jobseeker.password', ['pageTitle' => 'Change password']);
    }

    public function passwordUpdate(): void
    {
        verify_csrf();
        $data = [
            'current_password' => input('current_password'),
            'password'         => input('password'),
            'password_confirmation' => input('password_confirmation'),
        ];
        $errors = validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|max:64',
            'password_confirmation' => 'required|same:password',
        ], $data);

        if (!$errors && !password_verify($data['current_password'], $this->seeker['password'])) {
            $errors['current_password'] = 'Your current password is not correct.';
        }
        if (!$errors && $data['current_password'] === $data['password']) {
            $errors['password'] = 'Choose a password different from the current one.';
        }
        if ($errors) {
            flash_errors($errors);
            redirect('/dashboard/password');
        }

        DB::update('job_seekers', ['password' => password_hash($data['password'], PASSWORD_BCRYPT)], 'id = :id', ['id' => $this->id()]);
        log_activity('seeker', $this->id(), 'password_change', 'Password changed');
        flash('success', 'Your password has been changed.');
        redirect('/dashboard/password');
    }
}
