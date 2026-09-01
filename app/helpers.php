<?php

use App\Core\Auth;
use App\Core\Database;
use App\Core\Env;

/** Read a value from .env (or a real environment variable). */
function env(string $key, $default = null)
{
    return Env::get($key, $default);
}

function config(?string $key = null, $default = null)
{
    static $config = null;
    if ($config === null) {
        $file   = dirname(__DIR__) . '/app/config.php';
        $sample = dirname(__DIR__) . '/app/config.sample.php';
        $config = require (is_file($file) ? $file : $sample);
    }
    if ($key === null) {
        return $config;
    }
    $parts = explode('.', $key);
    $value = $config;
    foreach ($parts as $p) {
        if (!is_array($value) || !array_key_exists($p, $value)) {
            return $default;
        }
        $value = $value[$p];
    }
    return $value;
}

function base_url(): string
{
    return rtrim(config('app.base_url', ''), '/');
}

/** Build an extension-less application URL. */
function url(string $path = '/', array $query = []): string
{
    $path = '/' . ltrim($path, '/');
    $u    = base_url() . ($path === '/' ? '/' : rtrim($path, '/'));
    if ($query) {
        $u .= '?' . http_build_query(array_filter($query, static fn($v) => $v !== null && $v !== ''));
    }
    return $u;
}

function asset(string $path): string
{
    return base_url() . '/assets/' . ltrim($path, '/');
}

function upload_url(?string $path, ?string $fallback = null): ?string
{
    if (!$path) {
        return $fallback;
    }
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return base_url() . '/uploads/' . ltrim($path, '/');
}

function current_path(): string
{
    $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = base_url();
    if ($base && strpos($uri, $base) === 0) {
        $uri = substr($uri, strlen($base));
    }
    $uri = '/' . trim($uri, '/');
    return $uri === '/' ? '/' : rtrim($uri, '/');
}

function redirect(string $path, int $code = 302): void
{
    $target = preg_match('#^https?://#', $path) ? $path : url($path);
    header('Location: ' . $target, true, $code);
    exit;
}

function back(): void
{
    redirect($_SERVER['HTTP_REFERER'] ?? url('/'));
}

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function input(string $key, $default = null)
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function request_is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function wants_json(): bool
{
    $h = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    return strtolower($h) === 'xmlhttprequest'
        || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
}

function json_response(array $payload, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

/* ------------------------------------------------------------------ CSRF */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals($_SESSION['_csrf'] ?? '', (string) $token)) {
        abort(419, 'Your session expired. Please refresh the page and try again.');
    }
}

/* ------------------------------------------------------------- flash/old */

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function flash_errors(array $errors, array $old = []): void
{
    $_SESSION['_errors'] = $errors;
    $_SESSION['_old']    = $old;
}

function errors(): array
{
    static $errors = null;
    if ($errors === null) {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
    }
    return $errors;
}

function error_for(string $field): ?string
{
    $all = errors();
    return $all[$field] ?? null;
}

function has_errors(): bool
{
    return errors() !== [];
}

function old(string $key, $default = '')
{
    static $old = null;
    if ($old === null) {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
    }
    return $old[$key] ?? $default;
}

/* ---------------------------------------------------------------- views */

function view(string $template, array $data = [], string $layout = 'app'): void
{
    echo render($template, $data, $layout);
}

/**
 * Render a view, optionally wrapped in a layout.
 *
 * Internal variables are underscore-prefixed so that a view variable named
 * "template", "name" or "data" cannot collide with them during extract().
 */
function render(string $_template, array $_data = [], ?string $_layout = 'app'): string
{
    $_file = dirname(__DIR__) . '/app/Views/' . str_replace('.', '/', $_template) . '.php';
    if (!is_file($_file)) {
        abort(500, 'View not found: ' . $_template);
    }
    extract($_data, EXTR_SKIP);
    ob_start();
    require $_file;
    $content = ob_get_clean();

    if ($_layout === null) {
        return $content;
    }
    ob_start();
    require dirname(__DIR__) . '/app/Views/layouts/' . $_layout . '.php';
    return ob_get_clean();
}

function partial(string $_partial, array $_data = []): void
{
    extract($_data, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/partials/' . $_partial . '.php';
}

function abort(int $code, string $message = ''): void
{
    http_response_code($code);
    $titles = [
        403 => 'Access denied',
        404 => 'Page not found',
        419 => 'Session expired',
        500 => 'Something went wrong',
    ];
    echo render('errors.error', [
        'code'    => $code,
        'title'   => $titles[$code] ?? 'Error',
        'message' => $message,
    ]);
    exit;
}

/* --------------------------------------------------------------- format */

function fdate(?string $d, string $format = 'd M Y'): string
{
    if (!$d || $d === '0000-00-00' || $d === '0000-00-00 00:00:00') {
        return '—';
    }
    $ts = strtotime($d);
    return $ts ? date($format, $ts) : '—';
}

function money(?float $n): string
{
    return $n === null ? '—' : '₹' . number_format((float) $n);
}

function salary_range(?float $min, ?float $max): string
{
    if (!$min && !$max) {
        return 'Not disclosed';
    }
    if ($min && $max) {
        return money($min) . ' – ' . money($max);
    }
    return money($min ?: $max);
}

function str_excerpt(?string $text, int $len = 140): string
{
    $text = trim(strip_tags((string) $text));
    if (mb_strlen($text) <= $len) {
        return $text;
    }
    return mb_substr($text, 0, $len) . '…';
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $out   = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out ?: 'U';
}

function is_active(string $path): bool
{
    $c = current_path();
    return $c === rtrim($path, '/') || ($path !== '/' && strpos($c, rtrim($path, '/') . '/') === 0);
}

function otp(int $len = 6): string
{
    $o = '';
    for ($i = 0; $i < $len; $i++) {
        $o .= random_int(0, 9);
    }
    return $o;
}

function setting(string $key, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        if (Database::tableExists('settings')) {
            foreach (Database::all('SELECT setting_key, setting_value FROM settings') as $r) {
                $cache[$r['setting_key']] = $r['setting_value'];
            }
        }
    }
    return $cache[$key] ?? $default;
}

/* --------------------------------------------------------------- upload */

/**
 * Store an uploaded file under /uploads/{folder}. Returns "folder/name.ext" or null.
 */
function store_upload(string $field, string $folder, array $allowed = ['jpg', 'jpeg', 'png', 'webp'], ?string &$error = null): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed (code ' . $f['error'] . ').';
        return null;
    }
    $maxBytes = (int) config('security.max_upload_mb', 5) * 1024 * 1024;
    if ($f['size'] > $maxBytes) {
        $error = 'File is larger than ' . config('security.max_upload_mb', 5) . ' MB.';
        return null;
    }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        $error = 'Allowed file types: ' . implode(', ', $allowed) . '.';
        return null;
    }
    $dir = dirname(__DIR__) . '/uploads/' . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        $error = 'Upload folder is not writable.';
        return null;
    }
    $name = date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        $error = 'Could not save the uploaded file.';
        return null;
    }
    return $folder . '/' . $name;
}

function delete_upload(?string $path): void
{
    if (!$path) {
        return;
    }
    $full = dirname(__DIR__) . '/uploads/' . ltrim($path, '/');
    if (is_file($full)) {
        @unlink($full);
    }
}

/* ------------------------------------------------------------ validation */

function validate(array $rules, array $data): array
{
    $errors = [];
    foreach ($rules as $field => $ruleSet) {
        $value = $data[$field] ?? null;
        foreach (explode('|', $ruleSet) as $rule) {
            [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
            $label = ucwords(str_replace('_', ' ', $field));
            if ($name === 'required' && ($value === null || $value === '' || $value === [])) {
                $errors[$field] = $label . ' is required.';
                break;
            }
            // "accepted" must also fail on an absent value — an unchecked box
            // submits nothing at all.
            if ($name === 'accepted' && !in_array((string) $value, ['1', 'on', 'yes', 'true'], true)) {
                $errors[$field] = $label . ' must be accepted.';
                break;
            }
            if ($value === null || $value === '') {
                continue;
            }
            switch ($name) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = 'Enter a valid e-mail address.';
                    }
                    break;
                case 'mobile':
                    if (!preg_match('/^[6-9][0-9]{9}$/', preg_replace('/\D/', '', $value))) {
                        $errors[$field] = 'Enter a valid 10-digit mobile number.';
                    }
                    break;
                case 'min':
                    if (mb_strlen((string) $value) < (int) $arg) {
                        $errors[$field] = $label . ' must be at least ' . $arg . ' characters.';
                    }
                    break;
                case 'max':
                    if (mb_strlen((string) $value) > (int) $arg) {
                        $errors[$field] = $label . ' may not exceed ' . $arg . ' characters.';
                    }
                    break;
                case 'digits':
                    if (!preg_match('/^\d{' . (int) $arg . '}$/', preg_replace('/\s/', '', $value))) {
                        $errors[$field] = $label . ' must be ' . $arg . ' digits.';
                    }
                    break;
                case 'numeric':
                    if (!is_numeric($value)) {
                        $errors[$field] = $label . ' must be a number.';
                    }
                    break;
                case 'date':
                    if (!strtotime($value)) {
                        $errors[$field] = $label . ' must be a valid date.';
                    }
                    break;
                case 'in':
                    if (!in_array((string) $value, explode(',', (string) $arg), true)) {
                        $errors[$field] = 'Choose a valid ' . strtolower($label) . '.';
                    }
                    break;
                case 'same':
                    if ((string) $value !== (string) ($data[$arg] ?? '')) {
                        $errors[$field] = $label . ' does not match.';
                    }
                    break;
            }
            if (isset($errors[$field])) {
                break;
            }
        }
    }
    return $errors;
}

function auth_seeker(): ?array
{
    return Auth::user('seeker');
}

function auth_employer(): ?array
{
    return Auth::user('employer');
}

function auth_official(): ?array
{
    return Auth::user('official');
}

/* ---------------------------------------------------------------- icons */

/**
 * Inline SVG icon set (24x24, stroke based) so the UI needs no icon font.
 */
function icon(string $name, string $class = 'h-5 w-5', array $attrs = []): string
{
    static $paths = null;
    if ($paths === null) {
        $paths = require dirname(__DIR__) . '/app/Core/icons.php';
    }
    $body = $paths[$name] ?? $paths['dot'];
    $extra = '';
    foreach ($attrs as $k => $v) {
        $extra .= ' ' . $k . '="' . e($v) . '"';
    }
    $fill = str_starts_with($body, '<path fill=') ? 'currentColor' : 'none';
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="' . $fill . '" stroke="currentColor" '
        . 'stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"'
        . $extra . '>' . $body . '</svg>';
}

/* -------------------------------------------------------------- activity */

function log_activity(string $actorType, ?int $actorId, string $action, string $description = '', ?string $subject = null, ?int $subjectId = null): void
{
    if (!Database::tableExists('activity_log')) {
        return;
    }
    Database::insert('activity_log', [
        'actor_type'  => $actorType,
        'actor_id'    => $actorId,
        'action'      => $action,
        'subject'     => $subject,
        'subject_id'  => $subjectId,
        'description' => mb_substr($description, 0, 255),
        'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}
