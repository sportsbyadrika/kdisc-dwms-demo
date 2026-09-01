<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\Lookup;
use App\Core\Search;

class SkillController
{
    private const BASE_WHERE = "skill_programmes.status = 'published'";

    private function spec(): array
    {
        $categories = [];
        foreach (DB::all('SELECT id, name FROM skill_categories WHERE is_active = 1 ORDER BY name') as $c) {
            $categories[(string) $c['id']] = $c['name'];
        }
        $districts = [];
        foreach (DB::all("SELECT DISTINCT district FROM skill_programmes WHERE status = 'published' AND district <> '' ORDER BY district") as $d) {
            $districts[$d['district']] = $d['district'];
        }

        return [
            'q' => [
                'label' => 'Keyword',
                'sql' => static function ($v) {
                    $like = '%' . $v . '%';
                    return ['(skill_programmes.title LIKE ? OR skill_programmes.description LIKE ? OR skill_programmes.provider LIKE ?)', [$like, $like, $like]];
                },
            ],
            'category' => ['label' => 'Category', 'column' => 'skill_programmes.category_id', 'options' => $categories, 'multiple' => true, 'facet' => 'skill_programmes.category_id'],
            'district' => ['label' => 'District', 'column' => 'skill_programmes.district', 'options' => $districts, 'multiple' => true, 'facet' => 'skill_programmes.district'],
            'mode'     => ['label' => 'Mode', 'column' => 'skill_programmes.mode', 'options' => ['online' => 'Online', 'offline' => 'Classroom', 'hybrid' => 'Hybrid'], 'multiple' => true, 'facet' => 'skill_programmes.mode'],
            'level'    => ['label' => 'Level', 'column' => 'skill_programmes.level', 'options' => ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'], 'multiple' => true, 'facet' => 'skill_programmes.level'],
            'fee'      => ['label' => 'Fee', 'options' => ['free' => 'No fee', 'paid' => 'Paid'],
                           'sql' => static fn($v) => [$v === 'free' ? 'skill_programmes.is_free = 1' : 'skill_programmes.is_free = 0', []]],
            'certified'=> ['label' => 'Certification', 'options' => ['1' => 'Certificate awarded'],
                           'sql' => static fn($v) => ['skill_programmes.is_certified = 1', []]],
            'sort'     => ['label' => 'Sort', 'options' => ['starting' => 'Starting soonest', 'recent' => 'Recently added', 'fee' => 'Lowest fee']],
        ];
    }

    public function index(): void
    {
        $spec    = $this->spec();
        $active  = Search::filters($spec);
        $sortKey = $active['sort'] ?? 'starting';
        $filters = $active;
        unset($filters['sort']);

        [$where, $params] = Search::where($spec, $filters);
        $order = [
            'starting' => 'skill_programmes.start_date IS NULL, skill_programmes.start_date ASC',
            'recent'   => 'skill_programmes.id DESC',
            'fee'      => 'skill_programmes.is_free DESC, skill_programmes.fee ASC',
        ][$sortKey] ?? 'skill_programmes.start_date ASC';

        $result = Search::paginate(
            "SELECT skill_programmes.*, skill_categories.name AS category_name
             FROM skill_programmes
             LEFT JOIN skill_categories ON skill_categories.id = skill_programmes.category_id
             WHERE " . self::BASE_WHERE . "$where ORDER BY $order",
            $params,
            'SELECT COUNT(*) FROM skill_programmes WHERE ' . self::BASE_WHERE . $where,
            $params,
            9
        );

        $facets = [];
        foreach ($spec as $key => $def) {
            if (empty($def['facet'])) {
                continue;
            }
            $facets[$key] = Search::facet('skill_programmes', self::BASE_WHERE, [], $spec, $filters, $key, $def['facet']);
        }

        view('skills.index', [
            'pageTitle' => 'Skilling programmes',
            'spec'      => $spec,
            'active'    => $active,
            'filters'   => $filters,
            'facets'    => $facets,
            'result'    => $result,
            'sortKey'   => $sortKey,
        ]);
    }

    public function show(string $id): void
    {
        $p = DB::first(
            "SELECT skill_programmes.*, skill_categories.name AS category_name
             FROM skill_programmes
             LEFT JOIN skill_categories ON skill_categories.id = skill_programmes.category_id
             WHERE skill_programmes.id = ? AND skill_programmes.status = 'published'",
            [(int) $id]
        );
        if (!$p) {
            abort(404, 'That programme is no longer listed.');
        }
        DB::run('UPDATE skill_programmes SET views = views + 1 WHERE id = ?', [$p['id']]);

        $enrolment = null;
        if (Auth::check('seeker')) {
            $enrolment = DB::first('SELECT * FROM skill_enrolments WHERE programme_id = ? AND seeker_id = ?', [$p['id'], Auth::id('seeker')]);
        }

        view('skills.show', [
            'pageTitle'   => $p['title'],
            'metaDescription' => str_excerpt($p['description'], 155),
            'programme'   => $p,
            'enrolment'   => $enrolment,
            'similar'     => DB::all(
                "SELECT id, title, provider, mode, is_free, fee, district FROM skill_programmes
                 WHERE status = 'published' AND id <> ? AND (category_id = ? OR district = ?)
                 ORDER BY start_date IS NULL, start_date LIMIT 4",
                [$p['id'], $p['category_id'], $p['district']]
            ),
        ]);
    }

    public function enrol(string $id): void
    {
        verify_csrf();
        $programmeId = (int) $id;
        if (!Auth::check('seeker')) {
            $_SESSION['intended'] = '/skills/' . $programmeId;
            flash('info', 'Please sign in to register your interest.');
            redirect('/login');
        }
        if (!DB::value("SELECT id FROM skill_programmes WHERE id = ? AND status = 'published'", [$programmeId])) {
            abort(404, 'That programme is no longer listed.');
        }
        DB::run(
            'INSERT IGNORE INTO skill_enrolments (programme_id, seeker_id, status) VALUES (?, ?, ?)',
            [$programmeId, Auth::id('seeker'), 'interested']
        );
        log_activity('seeker', Auth::id('seeker'), 'skill_interest', 'Registered interest in a skilling programme', 'skill_programmes', $programmeId);
        flash('success', 'Your interest has been recorded. The training provider will be in touch with the next steps.');
        redirect('/skills/' . $programmeId);
    }
}
