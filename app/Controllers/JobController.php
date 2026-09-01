<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\Lookup;
use App\Core\Profile;
use App\Core\Search;

class JobController
{
    private const BASE_WHERE = "jobs.status = 'published' AND (jobs.last_date IS NULL OR jobs.last_date >= CURDATE())";

    /** Filter definitions shared by the query, the side panel and the chips. */
    private function spec(): array
    {
        $categories = [];
        foreach (DB::all('SELECT id, name FROM job_categories WHERE is_active = 1 ORDER BY name') as $c) {
            $categories[(string) $c['id']] = $c['name'];
        }
        $districts = array_combine(Lookup::DISTRICTS, Lookup::DISTRICTS);

        return [
            'q' => [
                'label' => 'Keyword', 'search' => true,
                'sql' => static function ($v) {
                    $like = '%' . $v . '%';
                    return ['(jobs.title LIKE ? OR jobs.description LIKE ? OR jobs.skills_required LIKE ? OR employers.company_name LIKE ?)',
                            [$like, $like, $like, $like]];
                },
            ],
            'category' => ['label' => 'Category', 'column' => 'jobs.category_id', 'options' => $categories, 'multiple' => true, 'facet' => 'jobs.category_id'],
            'district' => ['label' => 'District', 'column' => 'jobs.district', 'options' => $districts, 'multiple' => true, 'facet' => 'jobs.district'],
            'type'     => ['label' => 'Employment type', 'column' => 'jobs.employment_type', 'options' => Lookup::EMPLOYMENT_TYPES, 'multiple' => true, 'facet' => 'jobs.employment_type'],
            'mode'     => ['label' => 'Work mode', 'column' => 'jobs.work_mode', 'options' => Lookup::WORK_MODES, 'multiple' => true, 'facet' => 'jobs.work_mode'],
            'qual'     => ['label' => 'Minimum qualification', 'column' => 'jobs.min_qualification', 'options' => Lookup::JOB_QUALIFICATIONS, 'multiple' => true, 'facet' => 'jobs.min_qualification'],
            'exp' => [
                'label' => 'Experience',
                'options' => ['fresher' => 'Fresher (0 years)', '1' => '1 year and above', '3' => '3 years and above', '5' => '5 years and above'],
                'sql' => static function ($v) {
                    return $v === 'fresher'
                        ? ['jobs.experience_min <= 0', []]
                        : ['jobs.experience_min >= ?', [(float) $v]];
                },
            ],
            'salary' => [
                'label' => 'Monthly salary',
                'options' => ['10000' => '₹10,000+', '20000' => '₹20,000+', '30000' => '₹30,000+', '50000' => '₹50,000+'],
                'sql' => static fn($v) => ['(jobs.salary_max >= ? OR jobs.salary_min >= ?)', [(float) $v, (float) $v]],
            ],
            'sort' => ['label' => 'Sort', 'options' => ['recent' => 'Most recent', 'closing' => 'Closing soon', 'salary' => 'Highest salary', 'relevance' => 'Relevance']],
        ];
    }

    public function index(): void
    {
        $spec   = $this->spec();
        $active = Search::filters($spec);

        // "sort" is a presentation choice, not a WHERE clause.
        $sortKey = $active['sort'] ?? 'recent';
        $filters = $active;
        unset($filters['sort']);

        [$where, $params] = Search::where($spec, $filters);
        $from = 'FROM jobs JOIN employers ON employers.id = jobs.employer_id';

        $order = [
            'recent'    => 'jobs.published_at DESC, jobs.id DESC',
            'closing'   => 'jobs.last_date IS NULL, jobs.last_date ASC',
            'salary'    => 'COALESCE(jobs.salary_max, jobs.salary_min) DESC',
            'relevance' => 'jobs.views DESC, jobs.published_at DESC',
        ][$sortKey] ?? 'jobs.published_at DESC';

        $result = Search::paginate(
            "SELECT jobs.*, employers.company_name, employers.logo, employers.status AS employer_status,
                    job_categories.name AS category_name
             $from LEFT JOIN job_categories ON job_categories.id = jobs.category_id
             WHERE " . self::BASE_WHERE . "$where ORDER BY $order",
            $params,
            "SELECT COUNT(*) $from WHERE " . self::BASE_WHERE . $where,
            $params,
            10
        );

        // Facets are counted against the same joined set.
        $facetFrom = 'jobs JOIN employers ON employers.id = jobs.employer_id';
        $facets = [];
        foreach ($spec as $key => $def) {
            if (empty($def['facet'])) {
                continue;
            }
            $facets[$key] = Search::facet($facetFrom, self::BASE_WHERE, [], $spec, $filters, $key, $def['facet']);
        }

        $saved = $this->savedJobIds();

        view('jobs.index', [
            'pageTitle' => 'Search jobs',
            'spec'      => $spec,
            'active'    => $active,
            'filters'   => $filters,
            'facets'    => $facets,
            'result'    => $result,
            'saved'     => $saved,
            'sortKey'   => $sortKey,
        ]);
    }

    public function show(string $id): void
    {
        $job = DB::first(
            "SELECT jobs.*, employers.company_name, employers.logo, employers.about AS company_about,
                    employers.website, employers.industry, employers.employee_range, employers.status AS employer_status,
                    employers.city AS company_city, employers.district AS company_district,
                    job_categories.name AS category_name
             FROM jobs
             JOIN employers ON employers.id = jobs.employer_id
             LEFT JOIN job_categories ON job_categories.id = jobs.category_id
             WHERE jobs.id = ?",
            [(int) $id]
        );
        if (!$job || !in_array($job['status'], ['published', 'closed'], true)) {
            abort(404, 'That vacancy is no longer listed.');
        }
        DB::run('UPDATE jobs SET views = views + 1 WHERE id = ?', [$job['id']]);

        $seekerId    = Auth::id('seeker');
        $application = null;
        $eligibility = null;
        $saved       = false;
        if ($seekerId) {
            $application = DB::first('SELECT * FROM applications WHERE job_id = ? AND seeker_id = ?', [$job['id'], $seekerId]);
            $saved       = (bool) DB::value('SELECT id FROM wishlists WHERE job_id = ? AND seeker_id = ?', [$job['id'], $seekerId]);
            $eligibility = $this->checkEligibility($job, $seekerId);
        }

        $similar = DB::all(
            "SELECT jobs.id, jobs.title, jobs.job_location, jobs.salary_min, jobs.salary_max, employers.company_name
             FROM jobs JOIN employers ON employers.id = jobs.employer_id
             WHERE jobs.status = 'published' AND jobs.id <> ?
               AND (jobs.category_id = ? OR jobs.district = ?)
               AND (jobs.last_date IS NULL OR jobs.last_date >= CURDATE())
             ORDER BY jobs.published_at DESC LIMIT 4",
            [$job['id'], $job['category_id'], $job['district']]
        );

        view('jobs.show', [
            'pageTitle'   => $job['title'],
            'metaDescription' => str_excerpt($job['description'], 155),
            'job'         => $job,
            'application' => $application,
            'eligibility' => $eligibility,
            'saved'       => $saved,
            'similar'     => $similar,
            'resumes'     => $seekerId ? DB::all('SELECT id, title, file_name, is_primary FROM seeker_resumes WHERE seeker_id = ? ORDER BY is_primary DESC', [$seekerId]) : [],
        ]);
    }

    /* ------------------------------------------------------------- apply */

    public function apply(string $id): void
    {
        verify_csrf();
        $job = DB::first("SELECT * FROM jobs WHERE id = ?", [(int) $id]);
        if (!$job) {
            abort(404, 'That vacancy is no longer listed.');
        }

        // Not signed in: park the job and send the visitor to the login modal.
        if (!Auth::check('seeker')) {
            $_SESSION['pending_apply'] = (int) $job['id'];
            $_SESSION['intended']      = '/jobs/' . $job['id'];
            flash('info', 'Please sign in to apply. We have kept this vacancy for you.');
            redirect('/login?job=' . $job['id']);
        }

        $seekerId = Auth::id('seeker');

        if ($job['status'] !== 'published' || ($job['last_date'] && strtotime($job['last_date']) < strtotime('today'))) {
            flash('error', 'Applications for this vacancy are closed.');
            redirect('/jobs/' . $job['id']);
        }
        if (DB::value('SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?', [$job['id'], $seekerId])) {
            flash('info', 'You have already applied to this vacancy.');
            redirect('/jobs/' . $job['id']);
        }

        $eligibility = $this->checkEligibility($job, $seekerId);
        if (!$eligibility['eligible']) {
            flash('error', 'You do not meet the criteria for this vacancy yet: ' . implode(' ', $eligibility['blocking']));
            redirect('/jobs/' . $job['id']);
        }

        $resumeId = (int) input('resume_id') ?: null;
        if ($resumeId && !DB::value('SELECT id FROM seeker_resumes WHERE id = ? AND seeker_id = ?', [$resumeId, $seekerId])) {
            $resumeId = null;
        }
        if (!$resumeId) {
            $resumeId = DB::value('SELECT id FROM seeker_resumes WHERE seeker_id = ? ORDER BY is_primary DESC, id DESC LIMIT 1', [$seekerId]) ?: null;
        }

        DB::insert('applications', [
            'job_id'      => $job['id'],
            'seeker_id'   => $seekerId,
            'resume_id'   => $resumeId,
            'cover_note'  => mb_substr((string) input('cover_note'), 0, 2000) ?: null,
            'match_score' => $eligibility['score'],
        ]);
        DB::delete('wishlists', 'seeker_id = :sid AND job_id = :jid', ['sid' => $seekerId, 'jid' => $job['id']]);
        log_activity('seeker', $seekerId, 'job_applied', 'Applied to ' . $job['title'], 'jobs', (int) $job['id']);

        flash('success', 'Your application for ' . $job['title'] . ' has been submitted.');
        redirect('/dashboard/applications');
    }

    /** Save / unsave, called over fetch from the job cards. */
    public function save(string $id): void
    {
        verify_csrf();
        $jobId = (int) $id;
        if (!Auth::check('seeker')) {
            if (wants_json()) {
                json_response(['ok' => false, 'message' => 'Please sign in to save jobs.', 'login' => true], 401);
            }
            $_SESSION['pending_apply'] = $jobId;
            redirect('/login?job=' . $jobId);
        }
        $seekerId = Auth::id('seeker');
        if (!DB::value("SELECT id FROM jobs WHERE id = ? AND status = 'published'", [$jobId])) {
            if (wants_json()) {
                json_response(['ok' => false, 'message' => 'That vacancy is no longer listed.'], 404);
            }
            abort(404);
        }

        $existing = DB::value('SELECT id FROM wishlists WHERE seeker_id = ? AND job_id = ?', [$seekerId, $jobId]);
        if ($existing) {
            DB::delete('wishlists', 'id = :id', ['id' => $existing]);
            $saved = false;
            $message = 'Removed from your saved jobs.';
        } else {
            DB::run('INSERT IGNORE INTO wishlists (seeker_id, job_id) VALUES (?, ?)', [$seekerId, $jobId]);
            $saved = true;
            $message = 'Saved. You will find it under Saved jobs.';
        }

        if (wants_json()) {
            json_response(['ok' => true, 'saved' => $saved, 'message' => $message]);
        }
        flash('success', $message);
        back();
    }

    /* ------------------------------------------------------------ helpers */

    /**
     * Compare the seeker's profile with the vacancy's criteria.
     * @return array{eligible:bool,score:int,blocking:array,warnings:array,checks:array}
     */
    private function checkEligibility(array $job, int $seekerId): array
    {
        $seeker   = DB::first('SELECT * FROM job_seekers WHERE id = ?', [$seekerId]);
        $checks   = [];
        $blocking = [];
        $warnings = [];

        // Qualification
        $ladder = ['below_10' => 0, 'sslc' => 1, 'plus_two' => 2, 'iti' => 3, 'diploma' => 4, 'ug' => 5, 'pg' => 6, 'phd' => 7];
        if ($job['min_qualification'] === 'any') {
            $checks[] = ['ok' => true, 'label' => 'Open to any qualification'];
        } else {
            $held = Profile::highestQualification($seekerId);
            $ok   = $held !== null && ($ladder[$held] ?? -1) >= ($ladder[$job['min_qualification']] ?? 99);
            $checks[] = [
                'ok'    => $ok,
                'label' => 'Minimum qualification: ' . Lookup::label(Lookup::QUALIFICATIONS, $job['min_qualification']),
                'note'  => $held ? 'You hold ' . Lookup::label(Lookup::QUALIFICATIONS, $held) : 'No qualification recorded',
            ];
            if (!$ok) {
                $blocking[] = $held
                    ? 'the vacancy needs ' . Lookup::label(Lookup::QUALIFICATIONS, $job['min_qualification']) . '.'
                    : 'add your qualification to your profile first.';
            }
        }

        // Experience
        $years = Profile::totalExperienceYears($seekerId);
        $needs = (float) $job['experience_min'];
        $okExp = $years >= $needs;
        $checks[] = [
            'ok'    => $okExp,
            'label' => $needs > 0 ? 'Minimum experience: ' . rtrim(rtrim((string) $needs, '0'), '.') . ' year(s)' : 'No experience required',
            'note'  => $years > 0 ? 'You have recorded ' . $years . ' year(s)' : 'No experience recorded',
        ];
        if (!$okExp) {
            $blocking[] = 'the vacancy needs ' . rtrim(rtrim((string) $needs, '0'), '.') . ' year(s) of experience.';
        }

        // Age
        if ($job['age_min'] || $job['age_max']) {
            if (!$seeker['dob']) {
                $checks[] = ['ok' => false, 'label' => 'Age criteria apply', 'note' => 'Add your date of birth'];
                $blocking[] = 'add your date of birth to your profile.';
            } else {
                $age = (int) (new \DateTime($seeker['dob']))->diff(new \DateTime())->y;
                $ok  = (!$job['age_min'] || $age >= $job['age_min']) && (!$job['age_max'] || $age <= $job['age_max']);
                $checks[] = [
                    'ok'    => $ok,
                    'label' => 'Age between ' . ($job['age_min'] ?: '—') . ' and ' . ($job['age_max'] ?: '—'),
                    'note'  => 'You are ' . $age,
                ];
                if (!$ok) {
                    $blocking[] = 'your age is outside the range for this vacancy.';
                }
            }
        }

        // Gender preference
        if ($job['gender_preference'] !== 'any') {
            $ok = $seeker['gender'] === $job['gender_preference'];
            $checks[] = ['ok' => $ok, 'label' => 'Reserved for ' . $job['gender_preference'] . ' candidates'];
            if (!$ok) {
                $blocking[] = 'this vacancy is reserved for ' . $job['gender_preference'] . ' candidates.';
            }
        }

        // Soft checks — warn but never block.
        $hasResume = (bool) DB::value('SELECT id FROM seeker_resumes WHERE seeker_id = ?', [$seekerId]);
        if (!$hasResume) {
            $warnings[] = 'You have not uploaded a resume. Applications with a resume are shortlisted faster.';
        }
        if ($seeker['kyc_status'] !== 'verified') {
            $warnings[] = 'Your e-KYC is not complete. Verified profiles rank higher with employers.';
        }

        $passed = count(array_filter($checks, static fn($c) => $c['ok']));
        $score  = $checks ? (int) round($passed / count($checks) * 100) : 100;

        return [
            'eligible' => $blocking === [],
            'score'    => $score,
            'blocking' => $blocking,
            'warnings' => $warnings,
            'checks'   => $checks,
        ];
    }

    private function savedJobIds(): array
    {
        if (!Auth::check('seeker')) {
            return [];
        }
        $rows = DB::all('SELECT job_id FROM wishlists WHERE seeker_id = ?', [Auth::id('seeker')]);
        return array_map(static fn($r) => (int) $r['job_id'], $rows);
    }
}
