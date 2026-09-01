<?php
namespace App\Core;

/**
 * Declarative definition of the repeating profile sections (qualifications,
 * experience, certifications, achievements, skills, documents).
 *
 * One definition drives the list, the add/edit form, validation, uploads and
 * the delete confirmation, so a new section needs no new controller code.
 */
class Sections
{
    public static function all(): array
    {
        return [
            'qualifications' => [
                'table'    => 'seeker_qualifications',
                'title'    => 'Qualifications',
                'sub'      => 'Add every qualification you hold, starting with the highest.',
                'singular' => 'qualification',
                'icon'     => 'graduation',
                'order'    => 'year_of_pass DESC, id DESC',
                'empty'    => 'Employers filter candidates by qualification — add yours so your profile is picked up.',
                'fields'   => [
                    'level'          => ['label' => 'Level', 'type' => 'select', 'options' => Lookup::QUALIFICATIONS, 'rules' => 'required', 'half' => true],
                    'course'         => ['label' => 'Course / programme', 'type' => 'text', 'rules' => 'required|max:150', 'half' => true, 'placeholder' => 'B.Tech Computer Science'],
                    'specialisation' => ['label' => 'Specialisation', 'type' => 'text', 'rules' => 'max:150', 'half' => true],
                    'institution'    => ['label' => 'Institution', 'type' => 'text', 'rules' => 'max:180', 'half' => true],
                    'board'          => ['label' => 'Board / university', 'type' => 'text', 'rules' => 'max:150', 'half' => true],
                    'year_of_pass'   => ['label' => 'Year of passing', 'type' => 'number', 'rules' => 'numeric', 'half' => true, 'min' => 1950, 'max' => 2100],
                    'mark_type'      => ['label' => 'Marks recorded as', 'type' => 'select', 'options' => ['percentage' => 'Percentage', 'cgpa' => 'CGPA', 'grade' => 'Grade'], 'half' => true],
                    'mark_value'     => ['label' => 'Marks / grade', 'type' => 'text', 'rules' => 'max:20', 'half' => true, 'placeholder' => '78.5'],
                    'certificate'    => ['label' => 'Certificate', 'type' => 'file', 'folder' => 'documents', 'accept' => ['pdf', 'jpg', 'jpeg', 'png']],
                ],
                'display' => static function (array $r): array {
                    $marks = $r['mark_value'] ? ($r['mark_value'] . ' ' . str_replace(['percentage', 'cgpa', 'grade'], ['%', 'CGPA', 'grade'], (string) $r['mark_type'])) : null;
                    return [
                        'title'    => $r['course'] . ($r['specialisation'] ? ' — ' . $r['specialisation'] : ''),
                        'subtitle' => trim(($r['institution'] ?: '') . ($r['board'] ? ' · ' . $r['board'] : ''), ' ·'),
                        'meta'     => array_filter([
                            Lookup::label(Lookup::QUALIFICATIONS, $r['level']),
                            $r['year_of_pass'] ? 'Passed ' . $r['year_of_pass'] : null,
                            $marks,
                        ]),
                        'file'     => $r['certificate'],
                    ];
                },
            ],

            'experience' => [
                'table'    => 'seeker_experiences',
                'title'    => 'Work experience',
                'sub'      => 'List the roles you have held, including internships and apprenticeships.',
                'singular' => 'experience',
                'icon'     => 'briefcase',
                'order'    => 'is_current DESC, from_date DESC, id DESC',
                'empty'    => 'No experience recorded yet. Fresh graduates can skip this and add internships instead.',
                'fields'   => [
                    'designation'     => ['label' => 'Designation', 'type' => 'text', 'rules' => 'required|max:150', 'half' => true],
                    'organisation'    => ['label' => 'Organisation', 'type' => 'text', 'rules' => 'required|max:180', 'half' => true],
                    'employment_type' => ['label' => 'Employment type', 'type' => 'select', 'options' => Lookup::EMPLOYMENT_TYPES, 'half' => true],
                    'location'        => ['label' => 'Location', 'type' => 'text', 'rules' => 'max:150', 'half' => true],
                    'from_date'       => ['label' => 'From', 'type' => 'date', 'rules' => 'date', 'half' => true],
                    'to_date'         => ['label' => 'To', 'type' => 'date', 'rules' => 'date', 'half' => true, 'hint' => 'Leave blank if this is your current role.'],
                    'is_current'      => ['label' => 'I currently work here', 'type' => 'checkbox'],
                    'last_salary'     => ['label' => 'Last drawn salary (per month)', 'type' => 'number', 'rules' => 'numeric', 'half' => true],
                    'responsibilities'=> ['label' => 'Key responsibilities', 'type' => 'textarea', 'rules' => 'max:2000'],
                    'document'        => ['label' => 'Experience certificate', 'type' => 'file', 'folder' => 'documents', 'accept' => ['pdf', 'jpg', 'jpeg', 'png']],
                ],
                'display' => static function (array $r): array {
                    $period = fdate($r['from_date'], 'M Y') . ' – ' . ($r['is_current'] ? 'Present' : fdate($r['to_date'], 'M Y'));
                    return [
                        'title'    => $r['designation'],
                        'subtitle' => $r['organisation'] . ($r['location'] ? ' · ' . $r['location'] : ''),
                        'meta'     => array_filter([
                            $period,
                            Lookup::label(Lookup::EMPLOYMENT_TYPES, $r['employment_type'], ''),
                            $r['last_salary'] ? money((float) $r['last_salary']) . ' / month' : null,
                        ]),
                        'body'     => $r['responsibilities'],
                        'file'     => $r['document'],
                    ];
                },
            ],

            'certifications' => [
                'table'    => 'seeker_certifications',
                'title'    => 'Certifications',
                'sub'      => 'Professional certifications, licences and course completions.',
                'singular' => 'certification',
                'icon'     => 'shield-check',
                'order'    => 'issued_on DESC, id DESC',
                'empty'    => 'Certifications make a profile stand out — add any you hold.',
                'fields'   => [
                    'title'          => ['label' => 'Certification', 'type' => 'text', 'rules' => 'required|max:180', 'half' => true],
                    'issued_by'      => ['label' => 'Issued by', 'type' => 'text', 'rules' => 'max:180', 'half' => true],
                    'credential_id'  => ['label' => 'Credential ID', 'type' => 'text', 'rules' => 'max:120', 'half' => true],
                    'credential_url' => ['label' => 'Credential URL', 'type' => 'text', 'rules' => 'max:255', 'half' => true, 'placeholder' => 'https://'],
                    'issued_on'      => ['label' => 'Issued on', 'type' => 'date', 'rules' => 'date', 'half' => true],
                    'valid_upto'     => ['label' => 'Valid up to', 'type' => 'date', 'rules' => 'date', 'half' => true, 'hint' => 'Leave blank if it does not expire.'],
                    'file_path'      => ['label' => 'Certificate', 'type' => 'file', 'folder' => 'documents', 'accept' => ['pdf', 'jpg', 'jpeg', 'png']],
                ],
                'display' => static function (array $r): array {
                    return [
                        'title'    => $r['title'],
                        'subtitle' => $r['issued_by'],
                        'meta'     => array_filter([
                            $r['issued_on'] ? 'Issued ' . fdate($r['issued_on'], 'M Y') : null,
                            $r['valid_upto'] ? 'Valid to ' . fdate($r['valid_upto'], 'M Y') : null,
                            $r['credential_id'] ? 'ID ' . $r['credential_id'] : null,
                        ]),
                        'link'     => $r['credential_url'],
                        'file'     => $r['file_path'],
                    ];
                },
            ],

            'achievements' => [
                'table'    => 'seeker_achievements',
                'title'    => 'Achievements',
                'sub'      => 'Awards, publications, sports, arts and other recognitions.',
                'singular' => 'achievement',
                'icon'     => 'star',
                'order'    => 'awarded_on DESC, id DESC',
                'empty'    => 'Recognitions of any kind help a shortlisting panel remember you.',
                'fields'   => [
                    'title'       => ['label' => 'Achievement', 'type' => 'text', 'rules' => 'required|max:180', 'half' => true],
                    'category'    => ['label' => 'Category', 'type' => 'select', 'options' => Lookup::ACHIEVEMENT_CATEGORIES, 'rules' => 'required', 'half' => true],
                    'awarded_by'  => ['label' => 'Awarded by', 'type' => 'text', 'rules' => 'max:180', 'half' => true],
                    'awarded_on'  => ['label' => 'Awarded on', 'type' => 'date', 'rules' => 'date', 'half' => true],
                    'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'max:2000'],
                    'file_path'   => ['label' => 'Supporting document', 'type' => 'file', 'folder' => 'documents', 'accept' => ['pdf', 'jpg', 'jpeg', 'png']],
                ],
                'display' => static function (array $r): array {
                    return [
                        'title'    => $r['title'],
                        'subtitle' => $r['awarded_by'],
                        'meta'     => array_filter([
                            Lookup::label(Lookup::ACHIEVEMENT_CATEGORIES, $r['category'], ''),
                            $r['awarded_on'] ? fdate($r['awarded_on'], 'M Y') : null,
                        ]),
                        'body'     => $r['description'],
                        'file'     => $r['file_path'],
                    ];
                },
            ],

            'skills' => [
                'table'    => 'seeker_skills',
                'title'    => 'Skills',
                'sub'      => 'Add at least three skills — they are matched against the skills employers ask for.',
                'singular' => 'skill',
                'icon'     => 'sparkles',
                'order'    => 'FIELD(proficiency, "expert","advanced","intermediate","beginner"), skill_name',
                'empty'    => 'Add the skills you want to be found for.',
                'compact'  => true,
                'fields'   => [
                    'skill_name'  => ['label' => 'Skill', 'type' => 'text', 'rules' => 'required|max:120', 'half' => true, 'placeholder' => 'PHP, Tally, Welding…'],
                    'proficiency' => ['label' => 'Proficiency', 'type' => 'select', 'options' => Lookup::PROFICIENCY, 'rules' => 'required', 'half' => true],
                    'years'       => ['label' => 'Years of practice', 'type' => 'number', 'rules' => 'numeric', 'half' => true, 'step' => '0.5'],
                ],
                'display' => static function (array $r): array {
                    return [
                        'title'    => $r['skill_name'],
                        'subtitle' => null,
                        'meta'     => array_filter([
                            Lookup::label(Lookup::PROFICIENCY, $r['proficiency']),
                            $r['years'] ? rtrim(rtrim((string) $r['years'], '0'), '.') . ' yr' : null,
                        ]),
                    ];
                },
            ],

            'documents' => [
                'table'    => 'seeker_documents',
                'title'    => 'Documents & proofs',
                'sub'      => 'Driving licence, PAN card, passport-size photograph and any other proof of identity.',
                'singular' => 'document',
                'icon'     => 'id-card',
                'order'    => 'id DESC',
                'empty'    => 'Upload at least one identity proof so a verification officer can confirm your details.',
                'fields'   => [
                    'doc_type'   => ['label' => 'Document type', 'type' => 'select', 'options' => Lookup::DOC_TYPES, 'rules' => 'required', 'half' => true],
                    'doc_number' => ['label' => 'Document number', 'type' => 'text', 'rules' => 'max:60', 'half' => true, 'hint' => 'Leave blank for a photograph.'],
                    'issued_by'  => ['label' => 'Issuing authority', 'type' => 'text', 'rules' => 'max:120', 'half' => true],
                    'valid_upto' => ['label' => 'Valid up to', 'type' => 'date', 'rules' => 'date', 'half' => true],
                    'file_path'  => ['label' => 'Scanned copy', 'type' => 'file', 'folder' => 'documents', 'accept' => ['pdf', 'jpg', 'jpeg', 'png'], 'rules' => 'required'],
                ],
                'display' => static function (array $r): array {
                    return [
                        'title'    => Lookup::label(Lookup::DOC_TYPES, $r['doc_type']),
                        'subtitle' => $r['doc_number'] ? self::mask((string) $r['doc_number']) : null,
                        'meta'     => array_filter([
                            $r['issued_by'] ?: null,
                            $r['valid_upto'] ? 'Valid to ' . fdate($r['valid_upto'], 'M Y') : null,
                            $r['is_verified'] ? 'Verified' : 'Awaiting verification',
                        ]),
                        'file'     => $r['file_path'],
                        'verified' => (bool) $r['is_verified'],
                    ];
                },
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /** Show only the last four characters of an identifier. */
    public static function mask(string $value): string
    {
        $len = strlen($value);
        return $len <= 4 ? $value : str_repeat('X', $len - 4) . substr($value, -4);
    }
}
