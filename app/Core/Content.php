<?php
namespace App\Core;

/**
 * Definitions for the content that departmental users maintain: the home page
 * hero panel, skilling programmes and career services. One definition drives
 * the list, the form, validation, uploads and the permission check.
 */
class Content
{
    public static function all(): array
    {
        $districts = ['Statewide' => 'Statewide'];
        foreach (Lookup::DISTRICTS as $d) {
            $districts[$d] = $d;
        }

        return [
            'hero' => [
                'table'      => 'hero_slides',
                'permission' => 'hero.manage',
                'title'      => 'Home page hero panel',
                'sub'        => 'The rotating panel at the top of the home page. Slides show in the order below.',
                'singular'   => 'slide',
                'icon'       => 'layers',
                'order'      => 'sort_order, id',
                'statusColumn' => null,
                'empty'      => 'The home page falls back to a plain header until a slide is added.',
                'fields'     => [
                    'title'      => ['label' => 'Headline', 'type' => 'text', 'rules' => 'required|max:150', 'half' => true],
                    'cta_label'  => ['label' => 'Button label', 'type' => 'text', 'rules' => 'max:60', 'half' => true, 'placeholder' => 'Create your profile'],
                    'subtitle'   => ['label' => 'Supporting line', 'type' => 'textarea', 'rules' => 'max:255', 'rows' => 2],
                    'cta_url'    => ['label' => 'Button link', 'type' => 'text', 'rules' => 'max:255', 'half' => true, 'placeholder' => '/register'],
                    'sort_order' => ['label' => 'Order', 'type' => 'number', 'rules' => 'numeric', 'half' => true, 'hint' => 'Lower numbers show first.'],
                    'image'      => ['label' => 'Background image', 'type' => 'file', 'folder' => 'slides', 'accept' => ['jpg', 'jpeg', 'png', 'webp'],
                                     'hint' => 'Wide images work best, around 1600 x 700 pixels. A gradient is used when empty.'],
                    'is_active'  => ['label' => 'Show this slide on the home page', 'type' => 'checkbox'],
                ],
                'display' => static function (array $r): array {
                    return [
                        'title'    => $r['title'],
                        'subtitle' => $r['subtitle'],
                        'meta'     => array_filter([
                            'Order ' . (int) $r['sort_order'],
                            $r['cta_label'] ? 'Button: ' . $r['cta_label'] : null,
                            $r['is_active'] ? 'Visible' : 'Hidden',
                        ]),
                        'image'    => $r['image'],
                        'active'   => (bool) $r['is_active'],
                    ];
                },
            ],

            'skills' => [
                'table'      => 'skill_programmes',
                'permission' => 'skills.manage',
                'title'      => 'Skilling programmes',
                'sub'        => 'Programmes shown on the public Skills page.',
                'singular'   => 'programme',
                'icon'       => 'graduation',
                'order'      => "FIELD(status,'published','draft','archived'), start_date IS NULL, start_date",
                'statusColumn' => 'status',
                'categoryTable' => 'skill_categories',
                'empty'      => 'No programmes yet. Add one so job seekers can find it.',
                'fields'     => [
                    'title'        => ['label' => 'Programme title', 'type' => 'text', 'rules' => 'required|max:180'],
                    'provider'     => ['label' => 'Training provider', 'type' => 'text', 'rules' => 'max:180', 'half' => true],
                    'category_id'  => ['label' => 'Category', 'type' => 'select', 'options' => [], 'half' => true],
                    'description'  => ['label' => 'About the programme', 'type' => 'textarea', 'rules' => 'required|max:3000'],
                    'outcomes'     => ['label' => 'What the learner will be able to do', 'type' => 'textarea', 'rules' => 'max:2000', 'rows' => 3],
                    'eligibility'  => ['label' => 'Who can apply', 'type' => 'text', 'rules' => 'max:255'],
                    'mode'         => ['label' => 'Mode', 'type' => 'select', 'options' => ['offline' => 'Classroom', 'online' => 'Online', 'hybrid' => 'Hybrid'], 'rules' => 'required', 'half' => true],
                    'level'        => ['label' => 'Level', 'type' => 'select', 'options' => ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'], 'rules' => 'required', 'half' => true],
                    'duration_value' => ['label' => 'Duration', 'type' => 'number', 'rules' => 'numeric', 'half' => true],
                    'duration_unit'  => ['label' => 'Duration unit', 'type' => 'select', 'options' => ['hours' => 'Hours', 'days' => 'Days', 'weeks' => 'Weeks', 'months' => 'Months'], 'half' => true],
                    'is_free'      => ['label' => 'This programme is free', 'type' => 'checkbox'],
                    'fee'          => ['label' => 'Fee (₹)', 'type' => 'number', 'rules' => 'numeric', 'half' => true],
                    'seats'        => ['label' => 'Seats', 'type' => 'number', 'rules' => 'numeric', 'half' => true],
                    'district'     => ['label' => 'District', 'type' => 'select', 'options' => $districts, 'half' => true],
                    'venue'        => ['label' => 'Venue', 'type' => 'text', 'rules' => 'max:180', 'half' => true],
                    'start_date'   => ['label' => 'Start date', 'type' => 'date', 'rules' => 'date', 'half' => true],
                    'apply_url'    => ['label' => "Provider's application link", 'type' => 'text', 'rules' => 'max:255', 'half' => true],
                    'contact_email'=> ['label' => 'Contact e-mail', 'type' => 'text', 'rules' => 'email|max:150', 'half' => true],
                    'contact_phone'=> ['label' => 'Contact phone', 'type' => 'text', 'rules' => 'max:30', 'half' => true],
                    'is_certified' => ['label' => 'A certificate is awarded on completion', 'type' => 'checkbox'],
                    'image'        => ['label' => 'Cover image', 'type' => 'file', 'folder' => 'slides', 'accept' => ['jpg', 'jpeg', 'png', 'webp']],
                ],
                'display' => static function (array $r): array {
                    return [
                        'title'    => $r['title'],
                        'subtitle' => $r['provider'],
                        'meta'     => array_filter([
                            ucfirst($r['mode']),
                            ucfirst($r['level']),
                            $r['district'] ?: null,
                            $r['is_free'] ? 'Free' : ($r['fee'] ? money((float) $r['fee']) : null),
                            $r['start_date'] ? 'From ' . fdate($r['start_date']) : null,
                            $r['views'] . ' views',
                        ]),
                        'image'    => $r['image'],
                        'status'   => $r['status'],
                        'publicPath' => '/skills/' . $r['id'],
                    ];
                },
            ],

            'careers' => [
                'table'      => 'career_services',
                'permission' => 'careers.manage',
                'title'      => 'Career services',
                'sub'        => 'Services shown on the public Career Services page.',
                'singular'   => 'service',
                'icon'       => 'compass',
                'order'      => "FIELD(status,'published','draft','archived'), title",
                'statusColumn' => 'status',
                'categoryTable' => 'career_service_categories',
                'empty'      => 'No services yet. Add one so job seekers can book it.',
                'fields'     => [
                    'title'        => ['label' => 'Service title', 'type' => 'text', 'rules' => 'required|max:180'],
                    'category_id'  => ['label' => 'Service type', 'type' => 'select', 'options' => [], 'half' => true],
                    'provider'     => ['label' => 'Provided by', 'type' => 'text', 'rules' => 'max:180', 'half' => true],
                    'summary'      => ['label' => 'One-line summary', 'type' => 'text', 'rules' => 'required|max:255'],
                    'description'  => ['label' => 'What the service covers', 'type' => 'textarea', 'rules' => 'required|max:3000'],
                    'audience'     => ['label' => 'Who it is for', 'type' => 'text', 'rules' => 'max:180', 'half' => true],
                    'service_mode' => ['label' => 'Delivery', 'type' => 'select', 'options' => ['online' => 'Online', 'offline' => 'In person', 'hybrid' => 'Hybrid'], 'rules' => 'required', 'half' => true],
                    'is_free'      => ['label' => 'This service is free', 'type' => 'checkbox'],
                    'fee'          => ['label' => 'Fee (₹)', 'type' => 'number', 'rules' => 'numeric', 'half' => true],
                    'district'     => ['label' => 'District', 'type' => 'select', 'options' => $districts, 'half' => true],
                    'venue'        => ['label' => 'Venue', 'type' => 'text', 'rules' => 'max:180', 'half' => true],
                    'schedule_note'=> ['label' => 'When it runs', 'type' => 'text', 'rules' => 'max:180', 'half' => true, 'placeholder' => 'Weekdays, 10 am to 4 pm'],
                    'booking_url'  => ['label' => 'Booking link', 'type' => 'text', 'rules' => 'max:255', 'half' => true],
                    'contact_email'=> ['label' => 'Contact e-mail', 'type' => 'text', 'rules' => 'email|max:150', 'half' => true],
                    'contact_phone'=> ['label' => 'Contact phone', 'type' => 'text', 'rules' => 'max:30', 'half' => true],
                    'icon'         => ['label' => 'Icon', 'type' => 'select', 'half' => true, 'options' => [
                        'compass' => 'Compass', 'document' => 'Document', 'users' => 'People', 'globe' => 'Globe',
                        'briefcase' => 'Briefcase', 'chart' => 'Chart', 'graduation' => 'Graduation', 'heart' => 'Heart',
                    ]],
                    'image'        => ['label' => 'Cover image', 'type' => 'file', 'folder' => 'slides', 'accept' => ['jpg', 'jpeg', 'png', 'webp']],
                ],
                'display' => static function (array $r): array {
                    return [
                        'title'    => $r['title'],
                        'subtitle' => $r['summary'],
                        'meta'     => array_filter([
                            ucfirst($r['service_mode']),
                            $r['district'] ?: null,
                            $r['is_free'] ? 'No fee' : ($r['fee'] ? money((float) $r['fee']) : null),
                            $r['views'] . ' views',
                        ]),
                        'image'    => $r['image'],
                        'status'   => $r['status'],
                        'publicPath' => '/career-services/' . $r['id'],
                    ];
                },
            ],
        ];
    }

    /** Definition with the category options filled in from the database. */
    public static function get(string $key): ?array
    {
        $spec = self::all()[$key] ?? null;
        if (!$spec) {
            return null;
        }
        if (!empty($spec['categoryTable'])) {
            $options = [];
            foreach (Database::all('SELECT id, name FROM `' . $spec['categoryTable'] . '` WHERE is_active = 1 ORDER BY name') as $c) {
                $options[(string) $c['id']] = $c['name'];
            }
            $spec['fields']['category_id']['options'] = $options;
        }
        return $spec;
    }
}
