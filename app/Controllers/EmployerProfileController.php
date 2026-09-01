<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\Lookup;

/**
 * Four-step organisation profile wizard. Each step is saved as it is completed,
 * so a half-finished profile is never lost.
 */
class EmployerProfileController extends EmployerBaseController
{
    private const STEPS = [
        1 => ['Organisation', 'building'],
        2 => ['Statutory details', 'shield'],
        3 => ['Address & contact', 'map-pin'],
        4 => ['Review & submit', 'check-circle'],
    ];

    /** Fields captured at each step, with their validation rules. */
    private function fields(int $step): array
    {
        $all = [
            1 => [
                'company_name'     => 'required|min:3|max:180',
                'industry'         => 'required|max:120',
                'ownership_type'   => 'required|in:' . implode(',', array_keys(Lookup::OWNERSHIP_TYPES)),
                'employee_range'   => 'required|in:' . implode(',', Lookup::EMPLOYEE_RANGES),
                'established_year' => 'numeric',
                'website'          => 'max:180',
                'about'            => 'max:2000',
            ],
            2 => [
                'pan'               => 'required|max:15',
                'gstin'             => 'max:20',
                'cin'               => 'max:30',
                'registration_no'   => 'max:60',
                'labour_licence_no' => 'max:60',
            ],
            3 => [
                'address_line1'       => 'required|max:180',
                'address_line2'       => 'max:180',
                'city'                => 'max:100',
                'district'            => 'required|max:100',
                'state'               => 'required|max:100',
                'pincode'             => 'required|digits:6',
                'contact_person'      => 'required|max:120',
                'contact_designation' => 'max:120',
                'contact_mobile'      => 'required|mobile',
                'contact_email'       => 'required|email|max:150',
            ],
        ];
        return $all[$step] ?? [];
    }

    public function show(): void
    {
        $step = (int) (input('step') ?: $this->employer['profile_step'] ?: 1);
        $step = max(1, min(4, $step));

        $this->shell('employer.profile', [
            'pageTitle' => 'Company profile',
            'step'      => $step,
            'steps'     => self::STEPS,
            'ownership' => Lookup::OWNERSHIP_TYPES,
            'ranges'    => Lookup::EMPLOYEE_RANGES,
            'districts' => Lookup::DISTRICTS,
        ]);
    }

    public function save(): void
    {
        verify_csrf();
        $step   = max(1, min(4, (int) input('step')));
        $rules  = $this->fields($step);
        $data   = [];
        foreach (array_keys($rules) as $field) {
            $value = input($field);
            if ($field === 'contact_mobile') {
                $value = preg_replace('/\D/', '', (string) $value);
            }
            if (in_array($field, ['pan', 'gstin', 'cin'], true)) {
                $value = strtoupper(trim((string) $value));
            }
            $data[$field] = $value === '' ? null : $value;
        }
        $errors = validate($rules, $data);

        // Format checks that the generic rules cannot express.
        if ($step === 2) {
            if (!empty($data['pan']) && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $data['pan'])) {
                $errors['pan'] = 'Enter a valid PAN, for example AABCT1234F.';
            }
            if (!empty($data['gstin']) && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]{3}$/', $data['gstin'])) {
                $errors['gstin'] = 'Enter a valid 15-character GSTIN.';
            }
            if (!empty($data['pan']) && DB::value('SELECT id FROM employers WHERE pan = ? AND id <> ?', [$data['pan'], $this->id()])) {
                $errors['pan'] = 'This PAN is already registered to another organisation.';
            }
        }
        if ($step === 1 && !empty($data['established_year'])
            && ((int) $data['established_year'] < 1800 || (int) $data['established_year'] > (int) date('Y'))) {
            $errors['established_year'] = 'Enter a year between 1800 and ' . date('Y') . '.';
        }

        if ($step === 1) {
            $uploadError = null;
            $logo = store_upload('logo', 'logos', ['jpg', 'jpeg', 'png', 'webp', 'svg'], $uploadError);
            if ($uploadError) {
                $errors['logo'] = $uploadError;
            } elseif ($logo) {
                delete_upload($this->employer['logo']);
                $data['logo'] = $logo;
            }
        }

        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/employer/profile?step=' . $step);
        }

        // Step 4 is the declaration; it stores no new fields.
        if ($step === 4) {
            if (!$this->readyToSubmit()) {
                flash('error', 'Please complete steps 1 to 3 before submitting.');
                redirect('/employer/profile?step=1');
            }
            DB::update('employers', [
                'profile_completed' => 1,
                'profile_step'      => 4,
                'status'            => $this->employer['status'] === 'rejected' ? 'pending' : $this->employer['status'],
            ], 'id = :id', ['id' => $this->id()]);
            log_activity('employer', $this->id(), 'profile_submitted', 'Organisation profile submitted for verification');
            flash('success', 'Your profile has been submitted. The verification desk usually responds within three working days.');
            redirect('/employer/dashboard');
        }

        $data['profile_step'] = max((int) $this->employer['profile_step'], $step + 1);
        DB::update('employers', $data, 'id = :id', ['id' => $this->id()]);
        if ($step === 1) {
            Auth::refresh('employer', ['name' => $data['company_name'], 'photo' => $data['logo'] ?? $this->employer['logo']]);
        }

        flash('success', 'Step ' . $step . ' saved.');
        redirect('/employer/profile?step=' . ($step + 1));
    }

    private function readyToSubmit(): bool
    {
        $row = DB::first('SELECT * FROM employers WHERE id = ?', [$this->id()]);
        foreach (['company_name', 'industry', 'ownership_type', 'employee_range', 'pan',
                  'address_line1', 'district', 'pincode', 'contact_person', 'contact_mobile'] as $f) {
            if (empty($row[$f])) {
                return false;
            }
        }
        return true;
    }

    /* -------------------------------------------------------- documents */

    public function documents(): void
    {
        $this->shell('employer.documents', [
            'pageTitle' => 'Documents',
            'documents' => DB::all('SELECT * FROM employer_documents WHERE employer_id = ? ORDER BY id DESC', [$this->id()]),
            'types'     => [
                'pan'           => 'PAN card',
                'gst'           => 'GST registration',
                'incorporation' => 'Certificate of incorporation / registration',
                'licence'       => 'Trade or labour licence',
                'other'         => 'Other supporting document',
            ],
        ]);
    }

    public function documentStore(): void
    {
        verify_csrf();
        $type  = (string) input('doc_type');
        $types = ['pan', 'gst', 'incorporation', 'licence', 'other'];
        if (!in_array($type, $types, true)) {
            flash('error', 'Choose a document type.');
            redirect('/employer/documents');
        }
        $error = null;
        $path  = store_upload('document', 'documents', ['pdf', 'jpg', 'jpeg', 'png'], $error);
        if (!$path) {
            flash('error', $error ?: 'Choose a file to upload.');
            redirect('/employer/documents');
        }
        DB::insert('employer_documents', [
            'employer_id' => $this->id(),
            'doc_type'    => $type,
            'file_path'   => $path,
            'label'       => mb_substr((string) input('label'), 0, 150) ?: null,
        ]);
        flash('success', 'Document uploaded. The verification desk can now see it.');
        redirect('/employer/documents');
    }

    public function documentDelete(string $id): void
    {
        verify_csrf();
        $row = DB::first('SELECT * FROM employer_documents WHERE id = ? AND employer_id = ?', [(int) $id, $this->id()]);
        if (!$row) {
            abort(404, 'That document no longer exists.');
        }
        delete_upload($row['file_path']);
        DB::delete('employer_documents', 'id = :id', ['id' => $row['id']]);
        flash('success', 'Document deleted.');
        redirect('/employer/documents');
    }
}
