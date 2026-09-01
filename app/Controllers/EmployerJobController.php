<?php
namespace App\Controllers;

use App\Core\Database as DB;
use App\Core\Lookup;

/**
 * The curation sheet wizard: an employer builds a job title over four steps,
 * each saved as it is completed, then publishes it.
 */
class EmployerJobController extends EmployerBaseController
{
    public const STEPS = [
        1 => ['The role', 'briefcase'],
        2 => ['Eligibility', 'graduation'],
        3 => ['Engagement', 'wallet'],
        4 => ['Process & publish', 'send'],
    ];

    private function rules(int $step): array
    {
        return [
            1 => [
                'title'            => 'required|min:3|max:180',
                'category_id'      => 'numeric',
                'employment_type'  => 'required|in:' . implode(',', array_keys(Lookup::EMPLOYMENT_TYPES)),
                'work_mode'        => 'required|in:' . implode(',', array_keys(Lookup::WORK_MODES)),
                'vacancies'        => 'required|numeric',
                'description'      => 'required|min:30|max:5000',
                'responsibilities' => 'max:5000',
            ],
            2 => [
                'min_qualification'  => 'required|in:' . implode(',', array_keys(Lookup::JOB_QUALIFICATIONS)),
                'qualification_note' => 'max:255',
                'skills_required'    => 'max:500',
                'experience_min'     => 'numeric',
                'experience_max'     => 'numeric',
                'age_min'            => 'numeric',
                'age_max'            => 'numeric',
                'gender_preference'  => 'required|in:any,male,female',
            ],
            3 => [
                'salary_min'    => 'numeric',
                'salary_max'    => 'numeric',
                'salary_period' => 'required|in:' . implode(',', array_keys(Lookup::SALARY_PERIODS)),
                'job_location'  => 'required|max:180',
                'district'      => 'required|max:100',
                'state'         => 'required|max:100',
                'benefits'      => 'max:500',
            ],
            4 => [
                'selection_process' => 'max:255',
                'contact_email'     => 'email|max:150',
                'contact_mobile'    => 'mobile',
                'last_date'         => 'date',
            ],
        ][$step] ?? [];
    }

    /* ------------------------------------------------------------- list */

    public function index(): void
    {
        $status = input('status');
        $where  = 'employer_id = ?';
        $params = [$this->id()];
        if (in_array($status, ['draft', 'published', 'closed', 'archived'], true)) {
            $where .= ' AND status = ?';
            $params[] = $status;
        }

        $jobs = DB::all(
            "SELECT jobs.*, job_categories.name AS category_name,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = jobs.id) AS applications,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = jobs.id AND a.status = 'shortlisted') AS shortlisted
             FROM jobs LEFT JOIN job_categories ON job_categories.id = jobs.category_id
             WHERE $where ORDER BY jobs.updated_at DESC, jobs.id DESC",
            $params
        );

        $counts = [];
        foreach (DB::all('SELECT status, COUNT(*) AS n FROM jobs WHERE employer_id = ? GROUP BY status', [$this->id()]) as $r) {
            $counts[$r['status']] = (int) $r['n'];
        }

        $this->shell('employer.jobs', [
            'pageTitle' => 'Job titles',
            'jobs'      => $jobs,
            'counts'    => $counts,
            'total'     => array_sum($counts),
            'active'    => $status,
        ]);
    }

    /* ----------------------------------------------------------- wizard */

    public function create(): void
    {
        $this->requireVerified();
        $this->wizard(null, 1);
    }

    public function edit(string $id): void
    {
        $job  = $this->ownedJob((int) $id);
        $step = (int) (input('step') ?: 1);
        $this->wizard($job, max(1, min(4, $step)));
    }

    private function wizard(?array $job, int $step): void
    {
        $this->shell('employer.job-wizard', [
            'pageTitle'  => $job ? 'Edit: ' . $job['title'] : 'Publish a job title',
            'job'        => $job,
            'step'       => $step,
            'steps'      => self::STEPS,
            'categories' => DB::all('SELECT id, name FROM job_categories WHERE is_active = 1 ORDER BY name'),
            'quals'      => Lookup::JOB_QUALIFICATIONS,
            'types'      => Lookup::EMPLOYMENT_TYPES,
            'modes'      => Lookup::WORK_MODES,
            'periods'    => Lookup::SALARY_PERIODS,
            'districts'  => Lookup::DISTRICTS,
        ]);
    }

    public function store(): void
    {
        verify_csrf();
        $this->requireVerified();
        $data = $this->collect(1);
        $errors = validate($this->rules(1), $data);
        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/employer/jobs/create');
        }

        $data['employer_id'] = $this->id();
        $data['status']      = 'draft';
        $data['code']        = 'TMP' . bin2hex(random_bytes(6));
        $id = DB::insert('jobs', $data);
        DB::update('jobs', ['code' => 'JOB' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)], 'id = :id', ['id' => $id]);
        log_activity('employer', $this->id(), 'job_draft_created', 'Draft job title created', 'jobs', $id);

        flash('success', 'Draft saved. Now set the eligibility criteria.');
        redirect('/employer/jobs/' . $id . '/edit?step=2');
    }

    public function update(string $id): void
    {
        verify_csrf();
        $job  = $this->ownedJob((int) $id);
        $step = max(1, min(4, (int) input('step')));
        $data = $this->collect($step);
        $errors = validate($this->rules($step), $data);

        // Cross-field checks.
        if ($step === 2) {
            if ($data['experience_max'] !== null && $data['experience_min'] !== null
                && (float) $data['experience_max'] < (float) $data['experience_min']) {
                $errors['experience_max'] = 'Maximum experience cannot be less than the minimum.';
            }
            if ($data['age_max'] !== null && $data['age_min'] !== null && (int) $data['age_max'] < (int) $data['age_min']) {
                $errors['age_max'] = 'Maximum age cannot be less than the minimum.';
            }
        }
        if ($step === 3 && $data['salary_max'] !== null && $data['salary_min'] !== null
            && (float) $data['salary_max'] < (float) $data['salary_min']) {
            $errors['salary_max'] = 'Maximum salary cannot be less than the minimum.';
        }
        if ($step === 4 && !empty($data['last_date']) && strtotime($data['last_date']) < strtotime('today')) {
            $errors['last_date'] = 'The last date to apply must be today or later.';
        }

        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/employer/jobs/' . $job['id'] . '/edit?step=' . $step);
        }

        DB::update('jobs', $data, 'id = :id AND employer_id = :eid', ['id' => $job['id'], 'eid' => $this->id()]);

        // The final step can also publish.
        if ($step === 4 && input('action') === 'publish') {
            $missing = $this->missingForPublish(array_merge($job, $data));
            if ($missing) {
                flash('error', 'Cannot publish yet: ' . implode(' ', $missing));
                redirect('/employer/jobs/' . $job['id'] . '/edit?step=1');
            }
            DB::update('jobs', [
                'status'       => 'published',
                'published_at' => $job['published_at'] ?: date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $job['id']]);
            log_activity('employer', $this->id(), 'job_published', 'Job title published', 'jobs', (int) $job['id']);
            flash('success', 'Published. Your vacancy is now visible to job seekers.');
            redirect('/employer/jobs');
        }

        if ($step < 4) {
            flash('success', 'Step ' . $step . ' saved.');
            redirect('/employer/jobs/' . $job['id'] . '/edit?step=' . ($step + 1));
        }
        flash('success', 'Draft saved.');
        redirect('/employer/jobs');
    }

    /** Collect only the fields belonging to one step, normalised for storage. */
    private function collect(int $step): array
    {
        $data = [];
        foreach (array_keys($this->rules($step)) as $field) {
            $value = input($field);
            if ($field === 'contact_mobile') {
                $value = preg_replace('/\D/', '', (string) $value);
            }
            $data[$field] = ($value === '' || $value === null) ? null : $value;
        }
        // Columns that must never be null.
        foreach (['experience_min' => 0, 'vacancies' => 1, 'gender_preference' => 'any', 'state' => 'Kerala'] as $k => $default) {
            if (array_key_exists($k, $data) && $data[$k] === null) {
                $data[$k] = $default;
            }
        }
        return $data;
    }

    private function missingForPublish(array $job): array
    {
        $missing = [];
        foreach ([
            'title' => 'a job title', 'description' => 'a description',
            'job_location' => 'a job location', 'district' => 'a district',
        ] as $field => $label) {
            if (empty($job[$field])) {
                $missing[] = 'add ' . $label . '.';
            }
        }
        return $missing;
    }

    /* ---------------------------------------------------- status actions */

    public function publish(string $id): void
    {
        verify_csrf();
        $job = $this->ownedJob((int) $id);
        $missing = $this->missingForPublish($job);
        if ($missing) {
            flash('error', 'Cannot publish yet: ' . implode(' ', $missing));
            redirect('/employer/jobs/' . $job['id'] . '/edit');
        }
        DB::update('jobs', [
            'status'       => 'published',
            'published_at' => $job['published_at'] ?: date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $job['id']]);
        flash('success', 'Published. Your vacancy is now visible to job seekers.');
        redirect('/employer/jobs');
    }

    public function close(string $id): void
    {
        verify_csrf();
        $job = $this->ownedJob((int) $id);
        DB::update('jobs', ['status' => 'closed'], 'id = :id', ['id' => $job['id']]);
        flash('success', 'The vacancy is closed. Existing applicants can still see their application.');
        redirect('/employer/jobs');
    }

    public function reopen(string $id): void
    {
        verify_csrf();
        $job = $this->ownedJob((int) $id);
        DB::update('jobs', ['status' => 'published'], 'id = :id', ['id' => $job['id']]);
        flash('success', 'The vacancy is open again.');
        redirect('/employer/jobs');
    }

    public function destroy(string $id): void
    {
        verify_csrf();
        $job = $this->ownedJob((int) $id);
        if ((int) DB::value('SELECT COUNT(*) FROM applications WHERE job_id = ?', [$job['id']]) > 0) {
            flash('error', 'This job title has applications and cannot be deleted. Close it instead.');
            redirect('/employer/jobs');
        }
        DB::delete('jobs', 'id = :id AND employer_id = :eid', ['id' => $job['id'], 'eid' => $this->id()]);
        flash('success', 'Job title deleted.');
        redirect('/employer/jobs');
    }

    /* ------------------------------------------------------- applicants */

    public function applicants(string $id): void
    {
        $job    = $this->ownedJob((int) $id);
        $status = input('status');

        $where  = 'a.job_id = ?';
        $params = [$job['id']];
        if (array_key_exists((string) $status, Lookup::APPLICATION_STATUS)) {
            $where .= ' AND a.status = ?';
            $params[] = $status;
        }

        $applicants = DB::all(
            "SELECT a.*, s.name, s.email, s.mobile, s.photo, s.headline, s.kyc_status, s.dob, s.profile_score,
                    r.file_path AS resume_path, r.file_name AS resume_name,
                    (SELECT GROUP_CONCAT(DISTINCT q.course ORDER BY q.year_of_pass DESC SEPARATOR ', ')
                     FROM seeker_qualifications q WHERE q.seeker_id = s.id) AS qualifications,
                    (SELECT GROUP_CONCAT(DISTINCT sk.skill_name SEPARATOR ', ')
                     FROM seeker_skills sk WHERE sk.seeker_id = s.id) AS skills
             FROM applications a
             JOIN job_seekers s ON s.id = a.seeker_id
             LEFT JOIN seeker_resumes r ON r.id = a.resume_id
             WHERE $where
             ORDER BY FIELD(a.status,'selected','interview','shortlisted','applied','rejected','withdrawn'), a.match_score DESC, a.applied_at DESC",
            $params
        );

        $counts = [];
        foreach (DB::all('SELECT status, COUNT(*) AS n FROM applications WHERE job_id = ? GROUP BY status', [$job['id']]) as $r) {
            $counts[$r['status']] = (int) $r['n'];
        }

        $this->shell('employer.applicants', [
            'pageTitle'  => 'Applicants — ' . $job['title'],
            'job'        => $job,
            'applicants' => $applicants,
            'counts'     => $counts,
            'total'      => array_sum($counts),
            'active'     => $status,
            'statuses'   => Lookup::APPLICATION_STATUS,
        ]);
    }

    public function updateApplication(string $id): void
    {
        verify_csrf();
        $row = DB::first(
            'SELECT a.*, j.employer_id, j.title FROM applications a
             JOIN jobs j ON j.id = a.job_id WHERE a.id = ?',
            [(int) $id]
        );
        if (!$row || (int) $row['employer_id'] !== $this->id()) {
            abort(404, 'That application does not belong to your organisation.');
        }

        $status = (string) input('status');
        $allowed = ['applied', 'shortlisted', 'interview', 'selected', 'rejected'];
        if (!in_array($status, $allowed, true)) {
            flash('error', 'Choose a valid status.');
            back();
        }
        if ($row['status'] === 'withdrawn') {
            flash('error', 'The candidate has withdrawn this application.');
            back();
        }

        DB::update('applications', [
            'status'           => $status,
            'employer_remarks' => mb_substr((string) input('remarks'), 0, 255) ?: null,
        ], 'id = :id', ['id' => $row['id']]);
        log_activity('employer', $this->id(), 'application_status', 'Application marked ' . $status, 'applications', (int) $row['id']);

        flash('success', 'Application marked as ' . Lookup::label(Lookup::APPLICATION_STATUS, $status) . '.');
        redirect('/employer/jobs/' . $row['job_id'] . '/applicants');
    }

    /** All applications across every job title. */
    public function allApplications(): void
    {
        $applications = DB::all(
            "SELECT a.*, s.name, s.photo, s.headline, s.kyc_status, j.title, j.code
             FROM applications a
             JOIN job_seekers s ON s.id = a.seeker_id
             JOIN jobs j ON j.id = a.job_id
             WHERE j.employer_id = ?
             ORDER BY a.applied_at DESC LIMIT 100",
            [$this->id()]
        );
        $this->shell('employer.applications', [
            'pageTitle'    => 'Applications',
            'applications' => $applications,
            'statuses'     => Lookup::APPLICATION_STATUS,
        ]);
    }

    private function ownedJob(int $id): array
    {
        $job = DB::first('SELECT * FROM jobs WHERE id = ? AND employer_id = ?', [$id, $this->id()]);
        if (!$job) {
            abort(404, 'That job title does not belong to your organisation.');
        }
        return $job;
    }
}
