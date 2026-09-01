<?php
namespace App\Controllers;

use App\Core\Content;
use App\Core\Database as DB;

/**
 * One CRUD engine for every kind of admin-managed content, driven by
 * App\Core\Content: hero slides, skilling programmes and career services.
 */
class OfficialContentController extends OfficialBaseController
{
    public function index(string $section): void
    {
        $spec = $this->spec($section);
        $rows = DB::all('SELECT * FROM `' . $spec['table'] . '` ORDER BY ' . $spec['order']);

        $editing = null;
        if ($editId = (int) input('edit')) {
            $editing = DB::first('SELECT * FROM `' . $spec['table'] . '` WHERE id = ?', [$editId]);
            if (!$editing) {
                abort(404, 'That record no longer exists.');
            }
        }

        $this->shell('official.content', [
            'pageTitle' => $spec['title'],
            'section'   => $section,
            'spec'      => $spec,
            'rows'      => $rows,
            'editing'   => $editing,
        ]);
    }

    public function save(string $section): void
    {
        verify_csrf();
        $spec = $this->spec($section);

        $editId  = (int) input('record_id');
        $current = null;
        if ($editId) {
            $current = DB::first('SELECT * FROM `' . $spec['table'] . '` WHERE id = ?', [$editId]);
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

        foreach ($files as $name => $f) {
            $uploadError = null;
            $path = store_upload($name, $f['folder'], $f['accept'], $uploadError);
            if ($uploadError) {
                $errors[$name] = $uploadError;
            } elseif ($path) {
                if ($current && !empty($current[$name])) {
                    delete_upload($current[$name]);
                }
                $data[$name] = $path;
            } elseif ($current) {
                $data[$name] = $current[$name];
            }
        }

        // A free programme or service carries no fee.
        if (array_key_exists('is_free', $data) && $data['is_free']) {
            $data['fee'] = null;
        }

        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/official/' . $section . ($editId ? '?edit=' . $editId : ''));
        }

        if ($editId) {
            DB::update($spec['table'], $data, 'id = :id', ['id' => $editId]);
            $message = ucfirst($spec['singular']) . ' updated.';
        } else {
            if (!empty($spec['statusColumn'])) {
                $data[$spec['statusColumn']] = 'draft';
            }
            if (DB::tableExists($spec['table']) && $this->hasColumn($spec['table'], 'created_by')) {
                $data['created_by'] = $this->id();
            }
            $editId  = DB::insert($spec['table'], $data);
            $message = ucfirst($spec['singular']) . ' created' . (!empty($spec['statusColumn']) ? ' as a draft.' : '.');
        }

        log_activity('official', $this->id(), $section . '_saved', $message, $spec['table'], $editId);
        flash('success', $message);
        redirect('/official/' . $section);
    }

    /** Publish, unpublish or archive a record that carries a status column. */
    public function status(string $section, string $id): void
    {
        verify_csrf();
        $spec = $this->spec($section);
        if (empty($spec['statusColumn'])) {
            abort(404);
        }
        $row = DB::first('SELECT * FROM `' . $spec['table'] . '` WHERE id = ?', [(int) $id]);
        if (!$row) {
            abort(404, 'That record no longer exists.');
        }
        $status = (string) input('status');
        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            flash('error', 'Choose a valid status.');
            back();
        }
        DB::update($spec['table'], [$spec['statusColumn'] => $status], 'id = :id', ['id' => $row['id']]);
        log_activity('official', $this->id(), $section . '_' . $status, $row['title'] . ' marked ' . $status, $spec['table'], (int) $row['id']);
        flash('success', ucfirst($spec['singular']) . ' marked ' . $status . '.');
        redirect('/official/' . $section);
    }

    public function delete(string $section, string $id): void
    {
        verify_csrf();
        $spec = $this->spec($section);
        $row  = DB::first('SELECT * FROM `' . $spec['table'] . '` WHERE id = ?', [(int) $id]);
        if (!$row) {
            abort(404, 'That record no longer exists.');
        }
        foreach ($spec['fields'] as $name => $f) {
            if ($f['type'] === 'file' && !empty($row[$name])) {
                delete_upload($row[$name]);
            }
        }
        DB::delete($spec['table'], 'id = :id', ['id' => $row['id']]);
        log_activity('official', $this->id(), $section . '_deleted', ($row['title'] ?? 'Record') . ' deleted');
        flash('success', ucfirst($spec['singular']) . ' deleted.');
        redirect('/official/' . $section);
    }

    private function spec(string $section): array
    {
        $spec = Content::get($section);
        if (!$spec) {
            abort(404, 'That content section does not exist.');
        }
        $this->authorise($spec['permission']);
        return $spec;
    }

    /** SHOW COLUMNS does not accept placeholders, so ask information_schema. */
    private function hasColumn(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (!array_key_exists($key, $cache)) {
            $cache[$key] = (bool) DB::value(
                'SELECT COUNT(*) FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
                [$table, $column]
            );
        }
        return $cache[$key];
    }
}
