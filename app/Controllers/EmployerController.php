<?php
namespace App\Controllers;

use App\Core\Database as DB;

class EmployerController extends EmployerBaseController
{
    public function dashboard(): void
    {
        $id = $this->id();

        $stats = [
            'published'   => (int) DB::value("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND status = 'published'", [$id]),
            'drafts'      => (int) DB::value("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND status = 'draft'", [$id]),
            'applications'=> (int) DB::value('SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = ?', [$id]),
            'shortlisted' => (int) DB::value("SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = ? AND a.status IN ('shortlisted','interview','selected')", [$id]),
            'views'       => (int) DB::value('SELECT COALESCE(SUM(views),0) FROM jobs WHERE employer_id = ?', [$id]),
            'vacancies'   => (int) DB::value("SELECT COALESCE(SUM(vacancies),0) FROM jobs WHERE employer_id = ? AND status = 'published'", [$id]),
        ];

        // Per-job funnel, most recent first.
        $funnel = DB::all(
            "SELECT j.id, j.title, j.code, j.status, j.views, j.vacancies, j.last_date,
                    COUNT(a.id) AS applications,
                    SUM(a.status = 'shortlisted') AS shortlisted,
                    SUM(a.status = 'interview')   AS interview,
                    SUM(a.status = 'selected')    AS selected
             FROM jobs j LEFT JOIN applications a ON a.job_id = j.id
             WHERE j.employer_id = ?
             GROUP BY j.id ORDER BY j.published_at DESC, j.id DESC LIMIT 6",
            [$id]
        );

        $recent = DB::all(
            "SELECT a.id, a.status, a.applied_at, a.match_score, s.name, s.photo, s.headline, s.kyc_status,
                    j.title, j.id AS job_id
             FROM applications a
             JOIN job_seekers s ON s.id = a.seeker_id
             JOIN jobs j ON j.id = a.job_id
             WHERE j.employer_id = ? ORDER BY a.applied_at DESC LIMIT 6",
            [$id]
        );

        // Applications per day for the last 14 days.
        $trend = DB::all(
            "SELECT DATE(a.applied_at) AS day, COUNT(*) AS n
             FROM applications a JOIN jobs j ON j.id = a.job_id
             WHERE j.employer_id = ? AND a.applied_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
             GROUP BY DATE(a.applied_at) ORDER BY day",
            [$id]
        );
        $series = [];
        for ($i = 13; $i >= 0; $i--) {
            $series[date('Y-m-d', strtotime("-$i days"))] = 0;
        }
        foreach ($trend as $t) {
            $series[$t['day']] = (int) $t['n'];
        }

        $this->shell('employer.dashboard', [
            'pageTitle' => 'Employer dashboard',
            'stats'     => $stats,
            'funnel'    => $funnel,
            'recent'    => $recent,
            'series'    => $series,
            'progress'  => $this->profileProgress(),
        ]);
    }

    public function passwordForm(): void
    {
        $this->shell('employer.password', ['pageTitle' => 'Change password']);
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

        if (!$errors && !password_verify($data['current_password'], $this->employer['password'])) {
            $errors['current_password'] = 'Your current password is not correct.';
        }
        if ($errors) {
            flash_errors($errors);
            redirect('/employer/password');
        }

        DB::update('employers', ['password' => password_hash($data['password'], PASSWORD_BCRYPT)], 'id = :id', ['id' => $this->id()]);
        log_activity('employer', $this->id(), 'password_change', 'Password changed');
        flash('success', 'Your password has been changed.');
        redirect('/employer/password');
    }
}
