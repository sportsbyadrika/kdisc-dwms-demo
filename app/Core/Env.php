<?php
namespace App\Core;

/**
 * A minimal .env reader — no Composer, no dependencies.
 *
 * Values are kept in this class rather than pushed through putenv(), because
 * many shared hosts disable putenv() and because getenv() is not thread safe.
 * $_ENV and $_SERVER are still consulted so real server variables win.
 */
class Env
{
    /** @var array<string,string> */
    private static array $vars = [];
    private static bool $loaded = false;

    public static function load(string $file): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        if (!is_file($file) || !is_readable($file)) {
            return;
        }
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                continue;
            }
            if (strpos($line, 'export ') === 0) {
                $line = trim(substr($line, 7));
            }
            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                continue;
            }
            self::$vars[$key] = self::parseValue(trim($value));
        }
    }

    private static function parseValue(string $value): string
    {
        $len = strlen($value);
        if ($len > 1 && $value[0] === '"' && $value[$len - 1] === '"') {
            $value = substr($value, 1, -1);
            return str_replace(['\\n', '\\r', '\\t', '\\"', '\\\\'], ["\n", "\r", "\t", '"', '\\'], $value);
        }
        if ($len > 1 && $value[0] === "'" && $value[$len - 1] === "'") {
            return substr($value, 1, -1);
        }
        // Trailing comment on an unquoted value: FOO=bar # note
        $hash = strpos($value, ' #');
        if ($hash !== false) {
            $value = rtrim(substr($value, 0, $hash));
        }
        return $value;
    }

    /**
     * Read a value, casting the usual literals. Real environment variables
     * (set by the host or by a cPanel/Apache SetEnv) take precedence.
     */
    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, $_SERVER) && !is_array($_SERVER[$key])) {
            return self::cast((string) $_SERVER[$key]);
        }
        if (array_key_exists($key, $_ENV) && !is_array($_ENV[$key])) {
            return self::cast((string) $_ENV[$key]);
        }
        if (array_key_exists($key, self::$vars)) {
            return self::cast(self::$vars[$key]);
        }
        return $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$vars)
            || array_key_exists($key, $_ENV)
            || array_key_exists($key, $_SERVER);
    }

    /** True when a .env file was found and read. */
    public static function fileLoaded(): bool
    {
        return self::$vars !== [];
    }

    private static function cast(string $value)
    {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
        return $value;
    }
}
