<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\Lookup;

/**
 * Dashboard, employer verification, job moderation, seeker registry,
 * enquiries, site settings and the signed-in user's own password.
 */
class OfficialController extends OfficialBaseController
{
    public function dashboard(): void
    {
        $stats = [
            'seekers'   => (int) DB::value('SELECT COUNT(*) FROM job_seekers WHERE is_active = 1'),
            'verified'  => (int) DB::value("SELECT COUNT(*) FROM job_seekers WHERE kyc_status = 'verified'"),
            'employers' => (int) DB::value('SELECT COUNT(*) FROM employers'),
            'pending'   => (int) DB::value("SELECT COUNT(*) FROM employers WHERE status = 'pending' AND profile_completed = 1"),
            'jobs'      => (int) DB::value("SELECT COUNT(*) FROM jobs WHERE status = 'published'"),
            'apps'      => (int) DB::value('SELECT COUNT(*) FROM applications'),
            'skills'    => (int) DB::value("SELECT COUNT(*) FROM skill_programmes WHERE status = 'published'"),
            'services'  => (int) DB::value("SELECT COUNT(*) FROM career_services WHERE status = 'published'"),
        ];

        $registrations = DB::all(
            "SELECT DATE(created_at) AS day, COUNT(*) AS n FROM job_seekers
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
             GROUP BY DATE(created_at) ORDER BY day"
        );
        $series = [];
        for ($i = 13; $i >= 0; $i--) {
            $series[date('Y-m-d', strtotime("-$i days"))] = 0;
        }
        foreach ($registrations as $r) {
            $series[$r['day']] = (int) $r['n'];
        }

        $this->shell('official.dashboard', [
            'pageTitle'      => 'Administration',
            'stats'          => $stats,
            'series'         => $series,
            'pendingList'    => DB::all("SELECT id, company_name, district, created_at FROM employers WHERE status = 'pending' AND profile_completed = 1 ORDER BY updated_at LIMIT 5"),
            'byDistrict'     => DB::all("SELECT district, COUNT(*) AS n FROM jobs WHERE status = 'published' AND district <> '' GROUP BY district ORDER BY n DESC LIMIT 8"),
            'activity'       => DB::all('SELECT * FROM activity_log ORDER BY id DESC LIMIT 12'),
            'topApplications'=> DB::all(
                "SELECT j.title, e.company_name, COUNT(a.id) AS n
                 FROM applications a JOIN jobs j ON j.id = a.job_id JOIN employers e ON e.id = j.employer_id
                 GROUP BY a.job_id ORDER BY n DESC LIMIT 5"
            ),
        ]);
    }

    /* ------------------------------------------------ employer verification */

    public function employers(): void
    {
        $this->authorise('employers.verify');
        $status = input('status') ?: 'pending';
        $where  = $status === 'all' ? '1' : 'status = ?';
        $params = $status === 'all' ? [] : [$status];

        $employers = DB::all(
            "SELECT e.*, (SELECT COUNT(*) FROM jobs j WHERE j.employer_id = e.id) AS jobs,
                    (SELECT COUNT(*) FROM employer_documents d WHERE d.employer_id = e.id) AS documents
             FROM employers e WHERE $where ORDER BY e.profile_completed DESC, e.updated_at DESC",
            $params
        );
        $counts = [];
        foreach (DB::all('SELECT status, COUNT(*) AS n FROM employers GROUP BY status') as $r) {
            $counts[$r['status']] = (int) $r['n'];
        }

        $this->shell('official.employers', [
            'pageTitle' => 'Employer verification',
            'employers' => $employers,
            'counts'    => $counts,
            'total'     => array_sum($counts),
            'active'    => $status,
            'ownership' => Lookup::OWNERSHIP_TYPES,
        ]);
    }

    public function employerShow(string $id): void
    {
        $this->authorise('employers.verify');
        $employer = DB::first('SELECT * FROM employers WHERE id = ?', [(int) $id]);
        if (!$employer) {
            abort(404, 'That employer does not exist.');
        }
        $this->shell('official.employer-detail', [
            'pageTitle' => $employer['company_name'],
            'employer'  => $employer,
            'documents' => DB::all('SELECT * FROM employer_documents WHERE employer_id = ? ORDER BY id DESC', [$employer['id']]),
            'jobs'      => DB::all('SELECT id, code, title, status, vacancies, published_at FROM jobs WHERE employer_id = ? ORDER BY id DESC', [$employer['id']]),
            'ownership' => Lookup::OWNERSHIP_TYPES,
        ]);
    }

    public function employerDecide(string $id): void
    {
        verify_csrf();
        $this->authorise('employers.verify');
        $employer = DB::first('SELECT * FROM employers WHERE id = ?', [(int) $id]);
        if (!$employer) {
            abort(404, 'That employer does not exist.');
        }

        $decision = (string) input('decision');
        $map = ['verify' => 'verified', 'reject' => 'rejected', 'suspend' => 'suspended', 'reinstate' => 'pending'];
        if (!isset($map[$decision])) {
            flash('error', 'Choose a valid decision.');
            back();
        }
        $remarks = mb_substr((string) input('remarks'), 0, 255);
        if (in_array($decision, ['reject', 'suspend'], true) && $remarks === '') {
            flash('error', 'Please record a reason when rejecting or suspending an organisation.');
            redirect('/official/employers/' . $employer['id']);
        }

        DB::update('employers', [
            'status'      => $map[$decision],
            'remarks'     => $remarks ?: null,
            'verified_by' => $this->id(),
            'verified_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $employer['id']]);

        // A suspended organisation's vacancies come down with it.
        if ($decision === 'suspend') {
            DB::run("UPDATE jobs SET status = 'closed' WHERE employer_id = ? AND status = 'published'", [$employer['id']]);
        }

        log_activity('official', $this->id(), 'employer_' . $map[$decision], $employer['company_name'] . ' marked ' . $map[$decision], 'employers', (int) $employer['id']);
        flash('success', $employer['company_name'] . ' is now marked ' . $map[$decision] . '.');
        redirect('/official/employers/' . $employer['id']);
    }

    /* ------------------------------------------------------ job moderation */

    public function jobs(): void
    {
        $this->authorise('jobs.moderate');
        $q      = input('q');
        $status = input('status');
        $where  = ['1'];
        $params = [];
        if ($q) {
            $where[]  = '(j.title LIKE ? OR j.code LIKE ? OR e.company_name LIKE ?)';
            $like     = '%' . $q . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if (in_array($status, ['draft', 'published', 'closed', 'archived'], true)) {
            $where[]  = 'j.status = ?';
            $params[] = $status;
        }

        $jobs = DB::all(
            'SELECT j.*, e.company_name, e.status AS employer_status,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS applications
             FROM jobs j JOIN employers e ON e.id = j.employer_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY j.published_at DESC, j.id DESC LIMIT 100',
            $params
        );

        $this->shell('official.jobs', [
            'pageTitle' => 'Job titles',
            'jobs'      => $jobs,
            'q'         => $q,
            'active'    => $status,
        ]);
    }

    public function jobModerate(string $id): void
    {
        verify_csrf();
        $this->authorise('jobs.moderate');
        $job = DB::first('SELECT * FROM jobs WHERE id = ?', [(int) $id]);
        if (!$job) {
            abort(404, 'That job title does not exist.');
        }
        $action = (string) input('action');
        if (!in_array($action, ['close', 'archive', 'republish'], true)) {
            flash('error', 'Choose a valid action.');
            back();
        }
        $status = ['close' => 'closed', 'archive' => 'archived', 'republish' => 'published'][$action];
        DB::update('jobs', ['status' => $status], 'id = :id', ['id' => $job['id']]);
        log_activity('official', $this->id(), 'job_' . $status, $job['title'] . ' marked ' . $status, 'jobs', (int) $job['id']);
        flash('success', 'The job title is now ' . $status . '.');
        back();
    }

    /* --------------------------------------------------------- job seekers */

    public function seekers(): void
    {
        $this->authorise('seekers.view');
        $q      = input('q');
        $kyc    = input('kyc');
        $where  = ['1'];
        $params = [];
        if ($q) {
            $where[]  = '(s.name LIKE ? OR s.email LIKE ? OR s.mobile LIKE ?)';
            $like     = '%' . $q . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if (in_array($kyc, ['not_started', 'pending', 'verified', 'failed'], true)) {
            $where[]  = 's.kyc_status = ?';
            $params[] = $kyc;
        }

        $seekers = DB::all(
            'SELECT s.*, (SELECT COUNT(*) FROM applications a WHERE a.seeker_id = s.id) AS applications,
                    (SELECT district FROM seeker_addresses ad WHERE ad.seeker_id = s.id AND ad.address_type = "communication") AS district
             FROM job_seekers s WHERE ' . implode(' AND ', $where) . '
             ORDER BY s.created_at DESC LIMIT 100',
            $params
        );

        $this->shell('official.seekers', [
            'pageTitle' => 'Job seekers',
            'seekers'   => $seekers,
            'q'         => $q,
            'kyc'       => $kyc,
        ]);
    }

    public function seekerShow(string $id): void
    {
        $this->authorise('seekers.view');
        $seeker = DB::first('SELECT * FROM job_seekers WHERE id = ?', [(int) $id]);
        if (!$seeker) {
            abort(404, 'That job seeker does not exist.');
        }
        $sid = (int) $seeker['id'];
        $this->shell('official.seeker-detail', [
            'pageTitle'      => $seeker['name'],
            'seeker'         => $seeker,
            'addresses'      => DB::all('SELECT * FROM seeker_addresses WHERE seeker_id = ?', [$sid]),
            'documents'      => DB::all('SELECT * FROM seeker_documents WHERE seeker_id = ? ORDER BY id DESC', [$sid]),
            'qualifications' => DB::all('SELECT * FROM seeker_qualifications WHERE seeker_id = ? ORDER BY year_of_pass DESC', [$sid]),
            'experiences'    => DB::all('SELECT * FROM seeker_experiences WHERE seeker_id = ? ORDER BY from_date DESC', [$sid]),
            'skills'         => DB::all('SELECT * FROM seeker_skills WHERE seeker_id = ?', [$sid]),
            'applications'   => DB::all(
                'SELECT a.status, a.applied_at, j.title, e.company_name FROM applications a
                 JOIN jobs j ON j.id = a.job_id JOIN employers e ON e.id = j.employer_id
                 WHERE a.seeker_id = ? ORDER BY a.applied_at DESC',
                [$sid]
            ),
            'docTypes'       => Lookup::DOC_TYPES,
            'quals'          => Lookup::QUALIFICATIONS,
            'statuses'       => Lookup::APPLICATION_STATUS,
        ]);
    }

    public function seekerVerifyDocument(string $id): void
    {
        verify_csrf();
        $this->authorise('seekers.view');
        $doc = DB::first('SELECT * FROM seeker_documents WHERE id = ?', [(int) $id]);
        if (!$doc) {
            abort(404, 'That document does not exist.');
        }
        $verified = input('verified') === '1' ? 1 : 0;
        DB::update('seeker_documents', [
            'is_verified' => $verified,
            'verified_by' => $this->id(),
            'remarks'     => mb_substr((string) input('remarks'), 0, 255) ?: null,
        ], 'id = :id', ['id' => $doc['id']]);
        log_activity('official', $this->id(), 'document_' . ($verified ? 'verified' : 'unverified'), 'Seeker document reviewed', 'seeker_documents', (int) $doc['id']);
        flash('success', $verified ? 'Document marked verified.' : 'Verification removed.');
        redirect('/official/seekers/' . $doc['seeker_id']);
    }

    /* ----------------------------------------------------------- enquiries */

    public function messages(): void
    {
        $this->authorise('messages.view');
        $this->shell('official.messages', [
            'pageTitle' => 'Enquiries',
            'messages'  => DB::all('SELECT * FROM contact_messages ORDER BY is_read, id DESC LIMIT 200'),
        ]);
    }

    public function messageRead(string $id): void
    {
        verify_csrf();
        $this->authorise('messages.view');
        DB::update('contact_messages', ['is_read' => 1], 'id = :id', ['id' => (int) $id]);
        back();
    }

    /* ------------------------------------------------------------ settings */

    public function settings(): void
    {
        $this->authorise('settings.manage');
        $this->shell('official.settings', [
            'pageTitle' => 'Site settings',
            'settings'  => DB::all('SELECT * FROM settings ORDER BY setting_key'),
        ]);
    }

    public function settingsSave(): void
    {
        verify_csrf();
        $this->authorise('settings.manage');
        $allowed = DB::all('SELECT setting_key FROM settings');
        foreach ($allowed as $row) {
            $key = $row['setting_key'];
            if (!array_key_exists($key, $_POST)) {
                continue;
            }
            DB::update('settings', ['setting_value' => mb_substr((string) input($key), 0, 2000)], 'setting_key = :k', ['k' => $key]);
        }
        log_activity('official', $this->id(), 'settings_updated', 'Site settings updated');
        flash('success', 'Settings saved.');
        redirect('/official/settings');
    }

    /* ------------------------------------------------------------ password */

    public function passwordForm(): void
    {
        $this->shell('official.password', ['pageTitle' => 'Change password']);
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
        if (!$errors && !password_verify($data['current_password'], $this->user['password'])) {
            $errors['current_password'] = 'Your current password is not correct.';
        }
        if ($errors) {
            flash_errors($errors);
            redirect('/official/password');
        }

        DB::update('users', [
            'password'   => password_hash($data['password'], PASSWORD_BCRYPT),
            'must_reset' => 0,
        ], 'id = :id', ['id' => $this->id()]);
        log_activity('official', $this->id(), 'password_change', 'Password changed');
        flash('success', 'Your password has been changed.');
        redirect('/official/password');
    }

}
