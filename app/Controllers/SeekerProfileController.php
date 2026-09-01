<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\Lookup;
use App\Core\Profile;
use App\Core\Sections;

class SeekerProfileController extends SeekerBaseController
{
    /* ------------------------------------------------------ basic details */

    public function profile(): void
    {
        $this->shell('jobseeker.profile', [
            'pageTitle' => 'Basic details',
            'summary'   => Profile::completeness($this->id()),
        ]);
    }

    public function profileUpdate(): void
    {
        verify_csrf();
        $data = [
            'name'     => input('name'),
            'mobile'   => preg_replace('/\D/', '', (string) input('mobile')),
            'headline' => input('headline'),
            'gender'   => input('gender'),
            'dob'      => input('dob'),
            'about'    => input('about'),
        ];
        $errors = validate([
            'name'     => 'required|min:3|max:120',
            'mobile'   => 'required|mobile',
            'headline' => 'max:160',
            'gender'   => 'in:male,female,other',
            'dob'      => 'date',
            'about'    => 'max:2000',
        ], $data);

        if (!$errors && $data['dob']) {
            $age = (int) (new \DateTime($data['dob']))->diff(new \DateTime())->y;
            if ($age < 14 || $age > 100) {
                $errors['dob'] = 'Enter a date of birth between 14 and 100 years ago.';
            }
        }
        if (!$errors && DB::value('SELECT id FROM job_seekers WHERE mobile = ? AND id <> ?', [$data['mobile'], $this->id()])) {
            $errors['mobile'] = 'This mobile number is registered to another account.';
        }

        $photo = null;
        if (!$errors) {
            $uploadError = null;
            $photo = store_upload('photo', 'photos', ['jpg', 'jpeg', 'png', 'webp'], $uploadError);
            if ($uploadError) {
                $errors['photo'] = $uploadError;
            }
        }
        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/dashboard/profile');
        }

        // Mobile changes reset the verified flag.
        if ($data['mobile'] !== $this->seeker['mobile']) {
            $data['mobile_verified'] = 0;
        }
        if ($photo) {
            delete_upload($this->seeker['photo']);
            $data['photo'] = $photo;
        }
        $data['dob']    = $data['dob'] ?: null;
        $data['gender'] = $data['gender'] ?: null;

        DB::update('job_seekers', $data, 'id = :id', ['id' => $this->id()]);
        Profile::refreshScore($this->id());
        Auth::refresh('seeker', ['name' => $data['name'], 'photo' => $photo ?: $this->seeker['photo']]);
        log_activity('seeker', $this->id(), 'profile_update', 'Basic details updated');

        flash('success', 'Your basic details have been saved.');
        redirect('/dashboard/profile');
    }

    /* ---------------------------------------------------------- addresses */

    public function address(): void
    {
        $rows = DB::all('SELECT * FROM seeker_addresses WHERE seeker_id = ?', [$this->id()]);
        $addresses = ['communication' => null, 'permanent' => null];
        foreach ($rows as $r) {
            $addresses[$r['address_type']] = $r;
        }
        $this->shell('jobseeker.address', [
            'pageTitle' => 'Addresses',
            'addresses' => $addresses,
            'districts' => Lookup::DISTRICTS,
        ]);
    }

    public function addressUpdate(): void
    {
        verify_csrf();
        $sameAsAbove = input('same_as_communication') === '1';

        $errors = [];
        $payloads = [];
        foreach (['communication', 'permanent'] as $type) {
            $prefix = $type === 'communication' ? 'c_' : 'p_';
            if ($type === 'permanent' && $sameAsAbove) {
                continue;
            }
            $data = [
                'line1'    => input($prefix . 'line1'),
                'line2'    => input($prefix . 'line2'),
                'city'     => input($prefix . 'city'),
                'district' => input($prefix . 'district'),
                'state'    => input($prefix . 'state') ?: 'Kerala',
                'country'  => input($prefix . 'country') ?: 'India',
                'pincode'  => preg_replace('/\D/', '', (string) input($prefix . 'pincode')),
                'landmark' => input($prefix . 'landmark'),
            ];
            // The permanent address is optional; the communication one is not.
            $isEmpty = ($data['line1'] === '' && $data['district'] === '' && $data['pincode'] === '');
            if ($type === 'permanent' && $isEmpty) {
                continue;
            }
            $rules = [
                'line1'    => 'required|max:180',
                'line2'    => 'max:180',
                'city'     => 'max:100',
                'district' => 'required|max:100',
                'state'    => 'required|max:100',
                'pincode'  => 'required|digits:6',
                'landmark' => 'max:150',
            ];
            foreach (validate($rules, $data) as $field => $message) {
                $errors[$prefix . $field] = $message;
            }
            $payloads[$type] = $data;
        }

        if ($errors) {
            flash_errors($errors);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/dashboard/address');
        }

        foreach ($payloads as $type => $data) {
            $this->upsertAddress($type, $data);
        }
        if ($sameAsAbove && isset($payloads['communication'])) {
            $this->upsertAddress('permanent', $payloads['communication']);
        }

        Profile::refreshScore($this->id());
        flash('success', 'Your addresses have been saved.');
        redirect('/dashboard/address');
    }

    private function upsertAddress(string $type, array $data): void
    {
        $existing = DB::value(
            'SELECT id FROM seeker_addresses WHERE seeker_id = ? AND address_type = ?',
            [$this->id(), $type]
        );
        if ($existing) {
            DB::update('seeker_addresses', $data, 'id = :id', ['id' => $existing]);
        } else {
            DB::insert('seeker_addresses', $data + ['seeker_id' => $this->id(), 'address_type' => $type]);
        }
    }

    /* ------------------------------------------------------------- resume */

    public function resume(): void
    {
        $this->shell('jobseeker.resume', [
            'pageTitle' => 'Resume',
            'resumes'   => DB::all('SELECT * FROM seeker_resumes WHERE seeker_id = ? ORDER BY is_primary DESC, id DESC', [$this->id()]),
        ]);
    }

    public function resumeUpload(): void
    {
        verify_csrf();
        $error = null;
        $path  = store_upload('resume', 'resumes', ['pdf', 'doc', 'docx'], $error);
        if (!$path) {
            flash('error', $error ?: 'Choose a resume file to upload.');
            redirect('/dashboard/resume');
        }

        $count = (int) DB::value('SELECT COUNT(*) FROM seeker_resumes WHERE seeker_id = ?', [$this->id()]);
        if ($count >= 5) {
            delete_upload($path);
            flash('error', 'You can keep up to five resumes. Delete one before uploading another.');
            redirect('/dashboard/resume');
        }

        $id = DB::insert('seeker_resumes', [
            'seeker_id'  => $this->id(),
            'title'      => input('title') ?: 'Resume ' . ($count + 1),
            'file_path'  => $path,
            'file_name'  => $_FILES['resume']['name'] ?? null,
            'file_size'  => $_FILES['resume']['size'] ?? null,
            'is_primary' => $count === 0 ? 1 : 0,
            // Parsing is a later phase; the row is queued so nothing is lost.
            'parse_status' => 'pending',
        ]);

        Profile::refreshScore($this->id());
        log_activity('seeker', $this->id(), 'resume_upload', 'Resume uploaded', 'seeker_resumes', $id);
        flash('success', 'Resume uploaded. Employers you apply to will see the resume marked as primary.');
        redirect('/dashboard/resume');
    }

    public function resumePrimary(string $id): void
    {
        verify_csrf();
        $row = DB::first('SELECT * FROM seeker_resumes WHERE id = ? AND seeker_id = ?', [(int) $id, $this->id()]);
        if (!$row) {
            abort(404, 'That resume no longer exists.');
        }
        DB::run('UPDATE seeker_resumes SET is_primary = 0 WHERE seeker_id = ?', [$this->id()]);
        DB::update('seeker_resumes', ['is_primary' => 1], 'id = :id', ['id' => $row['id']]);
        flash('success', 'Primary resume updated.');
        redirect('/dashboard/resume');
    }

    public function resumeDelete(string $id): void
    {
        verify_csrf();
        $row = DB::first('SELECT * FROM seeker_resumes WHERE id = ? AND seeker_id = ?', [(int) $id, $this->id()]);
        if (!$row) {
            abort(404, 'That resume no longer exists.');
        }
        delete_upload($row['file_path']);
        DB::delete('seeker_resumes', 'id = :id', ['id' => $row['id']]);

        // Keep exactly one primary resume.
        if ($row['is_primary']) {
            $next = DB::value('SELECT id FROM seeker_resumes WHERE seeker_id = ? ORDER BY id DESC LIMIT 1', [$this->id()]);
            if ($next) {
                DB::update('seeker_resumes', ['is_primary' => 1], 'id = :id', ['id' => $next]);
            }
        }
        Profile::refreshScore($this->id());
        flash('success', 'Resume deleted.');
        redirect('/dashboard/resume');
    }

    /* --------------------------------------------- generic record sections */

    public function records(string $section): void
    {
        $spec = Sections::get($section);
        if (!$spec) {
            abort(404, 'That profile section does not exist.');
        }
        $rows = DB::all(
            'SELECT * FROM `' . $spec['table'] . '` WHERE seeker_id = ? ORDER BY ' . $spec['order'],
            [$this->id()]
        );
        $editing = null;
        if ($editId = (int) input('edit')) {
            $editing = DB::first('SELECT * FROM `' . $spec['table'] . '` WHERE id = ? AND seeker_id = ?', [$editId, $this->id()]);
        }

        $this->shell('jobseeker.records', [
            'pageTitle' => $spec['title'],
            'section'   => $section,
            'spec'      => $spec,
            'rows'      => $rows,
            'editing'   => $editing,
        ]);
    }

    public function recordSave(string $section): void
    {
        verify_csrf();
        $spec = Sections::get($section);
        if (!$spec) {
            abort(404);
        }

        $editId  = (int) input('record_id');
        $current = null;
        if ($editId) {
            $current = DB::first('SELECT * FROM `' . $spec['table'] . '` WHERE id = ? AND seeker_id = ?', [$editId, $this->id()]);
            if (!$current) {
                abort(404, 'That record no longer exists.');
            }
        }

        [$data, $rules, $files] = [[], [], []];
        foreach ($spec['fields'] as $name => $f) {
            if ($f['type'] === 'file') {
                $files[$name] = $f;
                continue;
            }
            if ($f['type'] === 'checkbox') {
                $data[$name] = input($name) ? 1 : 0;
                continue;
            }
            $value = input($name);
            $data[$name] = ($value === '' ? null : $value);
            if (!empty($f['rules'])) {
                $rules[$name] = $f['rules'];
            }
        }

        $errors = validate($rules, $data);

        // Uploads: keep the existing file when no new one is chosen.
        foreach ($files as $name => $f) {
            $uploadError = null;
            $path = store_upload($name, $f['folder'], $f['accept'], $uploadError);
            if ($uploadError) {
                $errors[$name] = $uploadError;
                continue;
            }
            if ($path) {
                if ($current && !empty($current[$name])) {
                    delete_upload($current[$name]);
                }
                $data[$name] = $path;
            } elseif ($current) {
                $data[$name] = $current[$name];
            } elseif (strpos((string) ($f['rules'] ?? ''), 'required') !== false) {
                $errors[$name] = $f['label'] . ' is required.';
            }
        }

        // Cross-field checks that the generic rules cannot express.
        if ($section === 'experience' && !empty($data['from_date']) && !empty($data['to_date'])
            && strtotime($data['to_date']) < strtotime($data['from_date'])) {
            $errors['to_date'] = 'The end date cannot be before the start date.';
        }
        if ($section === 'experience' && !empty($data['is_current'])) {
            $data['to_date'] = null;
        }

        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/dashboard/' . $section . ($editId ? '?edit=' . $editId : ''));
        }

        if ($editId) {
            DB::update($spec['table'], $data, 'id = :id AND seeker_id = :sid', ['id' => $editId, 'sid' => $this->id()]);
            $message = ucfirst($spec['singular']) . ' updated.';
        } else {
            DB::insert($spec['table'], $data + ['seeker_id' => $this->id()]);
            $message = ucfirst($spec['singular']) . ' added.';
        }

        Profile::refreshScore($this->id());
        log_activity('seeker', $this->id(), 'profile_' . $section, $message);
        flash('success', $message);
        redirect('/dashboard/' . $section);
    }

    public function recordDelete(string $section, string $id): void
    {
        verify_csrf();
        $spec = Sections::get($section);
        if (!$spec) {
            abort(404);
        }
        $row = DB::first('SELECT * FROM `' . $spec['table'] . '` WHERE id = ? AND seeker_id = ?', [(int) $id, $this->id()]);
        if (!$row) {
            abort(404, 'That record no longer exists.');
        }
        foreach ($spec['fields'] as $name => $f) {
            if ($f['type'] === 'file' && !empty($row[$name])) {
                delete_upload($row[$name]);
            }
        }
        DB::delete($spec['table'], 'id = :id AND seeker_id = :sid', ['id' => $row['id'], 'sid' => $this->id()]);
        Profile::refreshScore($this->id());
        flash('success', ucfirst($spec['singular']) . ' deleted.');
        redirect('/dashboard/' . $section);
    }
}
