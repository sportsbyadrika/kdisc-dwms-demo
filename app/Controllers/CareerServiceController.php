<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\Search;

class CareerServiceController
{
    private const BASE_WHERE = "career_services.status = 'published'";

    private function spec(): array
    {
        $categories = [];
        foreach (DB::all('SELECT id, name FROM career_service_categories WHERE is_active = 1 ORDER BY name') as $c) {
            $categories[(string) $c['id']] = $c['name'];
        }
        $districts = [];
        foreach (DB::all("SELECT DISTINCT district FROM career_services WHERE status = 'published' AND district <> '' ORDER BY district") as $d) {
            $districts[$d['district']] = $d['district'];
        }

        return [
            'q' => [
                'label' => 'Keyword',
                'sql' => static function ($v) {
                    $like = '%' . $v . '%';
                    return ['(career_services.title LIKE ? OR career_services.summary LIKE ? OR career_services.description LIKE ? OR career_services.provider LIKE ?)',
                            [$like, $like, $like, $like]];
                },
            ],
            'category' => ['label' => 'Service type', 'column' => 'career_services.category_id', 'options' => $categories, 'multiple' => true, 'facet' => 'career_services.category_id'],
            'district' => ['label' => 'District', 'column' => 'career_services.district', 'options' => $districts, 'multiple' => true, 'facet' => 'career_services.district'],
            'mode'     => ['label' => 'Delivery', 'column' => 'career_services.service_mode', 'options' => ['online' => 'Online', 'offline' => 'In person', 'hybrid' => 'Hybrid'], 'multiple' => true, 'facet' => 'career_services.service_mode'],
            'fee'      => ['label' => 'Fee', 'options' => ['free' => 'No fee', 'paid' => 'Paid'],
                           'sql' => static fn($v) => [$v === 'free' ? 'career_services.is_free = 1' : 'career_services.is_free = 0', []]],
            'sort'     => ['label' => 'Sort', 'options' => ['popular' => 'Most used', 'recent' => 'Recently added', 'title' => 'A to Z']],
        ];
    }

    public function index(): void
    {
        $spec    = $this->spec();
        $active  = Search::filters($spec);
        $sortKey = $active['sort'] ?? 'popular';
        $filters = $active;
        unset($filters['sort']);

        [$where, $params] = Search::where($spec, $filters);
        $order = [
            'popular' => 'career_services.views DESC, career_services.id ASC',
            'recent'  => 'career_services.id DESC',
            'title'   => 'career_services.title ASC',
        ][$sortKey] ?? 'career_services.views DESC';

        $result = Search::paginate(
            "SELECT career_services.*, career_service_categories.name AS category_name
             FROM career_services
             LEFT JOIN career_service_categories ON career_service_categories.id = career_services.category_id
             WHERE " . self::BASE_WHERE . "$where ORDER BY $order",
            $params,
            'SELECT COUNT(*) FROM career_services WHERE ' . self::BASE_WHERE . $where,
            $params,
            9
        );

        $facets = [];
        foreach ($spec as $key => $def) {
            if (empty($def['facet'])) {
                continue;
            }
            $facets[$key] = Search::facet('career_services', self::BASE_WHERE, [], $spec, $filters, $key, $def['facet']);
        }

        view('career.index', [
            'pageTitle' => 'Career services',
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
        $s = DB::first(
            "SELECT career_services.*, career_service_categories.name AS category_name
             FROM career_services
             LEFT JOIN career_service_categories ON career_service_categories.id = career_services.category_id
             WHERE career_services.id = ? AND career_services.status = 'published'",
            [(int) $id]
        );
        if (!$s) {
            abort(404, 'That service is no longer listed.');
        }
        DB::run('UPDATE career_services SET views = views + 1 WHERE id = ?', [$s['id']]);

        $request = null;
        if (Auth::check('seeker')) {
            $request = DB::first('SELECT * FROM career_service_requests WHERE service_id = ? AND seeker_id = ?', [$s['id'], Auth::id('seeker')]);
        }

        view('career.show', [
            'pageTitle'   => $s['title'],
            'metaDescription' => str_excerpt($s['summary'] ?: $s['description'], 155),
            'service'     => $s,
            'request'     => $request,
            'similar'     => DB::all(
                "SELECT id, title, summary, service_mode, is_free, icon FROM career_services
                 WHERE status = 'published' AND id <> ? ORDER BY (category_id = ?) DESC, views DESC LIMIT 4",
                [$s['id'], $s['category_id']]
            ),
        ]);
    }

    public function request(string $id): void
    {
        verify_csrf();
        $serviceId = (int) $id;
        if (!Auth::check('seeker')) {
            $_SESSION['intended'] = '/career-services/' . $serviceId;
            flash('info', 'Please sign in to request this service.');
            redirect('/login');
        }
        if (!DB::value("SELECT id FROM career_services WHERE id = ? AND status = 'published'", [$serviceId])) {
            abort(404, 'That service is no longer listed.');
        }

        $note = mb_substr((string) input('note'), 0, 500);
        $existing = DB::value('SELECT id FROM career_service_requests WHERE service_id = ? AND seeker_id = ?', [$serviceId, Auth::id('seeker')]);
        if ($existing) {
            flash('info', 'You have already requested this service. The team will contact you.');
            redirect('/career-services/' . $serviceId);
        }

        DB::insert('career_service_requests', [
            'service_id' => $serviceId,
            'seeker_id'  => Auth::id('seeker'),
            'note'       => $note ?: null,
        ]);
        log_activity('seeker', Auth::id('seeker'), 'career_service_request', 'Requested a career service', 'career_services', $serviceId);
        flash('success', 'Your request has been sent. The service desk will contact you on your registered mobile number.');
        redirect('/career-services/' . $serviceId);
    }
}
