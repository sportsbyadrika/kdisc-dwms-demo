<?php
namespace App\Core;

/**
 * Builds the WHERE clause and the facet counts behind the public search pages.
 * Each filter is declared once, so the query, the side panel and the "active
 * filter" chips all stay in step.
 */
class Search
{
    /** Read the requested filters from the query string, keeping only known keys. */
    public static function filters(array $spec): array
    {
        $out = [];
        foreach ($spec as $key => $def) {
            $raw = $_GET[$key] ?? null;
            if ($raw === null || $raw === '' || $raw === []) {
                continue;
            }
            if (!empty($def['multiple'])) {
                $values = array_filter(array_map('trim', (array) $raw), static fn($v) => $v !== '');
                if (!empty($def['options'])) {
                    $values = array_values(array_intersect($values, array_keys($def['options'])));
                }
                if ($values) {
                    $out[$key] = $values;
                }
            } else {
                $value = is_array($raw) ? reset($raw) : trim((string) $raw);
                if (!empty($def['options']) && !array_key_exists($value, $def['options'])) {
                    continue;
                }
                if ($value !== '') {
                    $out[$key] = $value;
                }
            }
        }
        return $out;
    }

    /**
     * Turn the active filters into SQL.
     * @return array{0:string,1:array} [sql fragment, bound params]
     */
    public static function where(array $spec, array $active): array
    {
        $sql    = [];
        $params = [];
        foreach ($active as $key => $value) {
            $def = $spec[$key] ?? null;
            if (!$def) {
                continue;
            }
            if (!empty($def['sql'])) {
                [$fragment, $bound] = $def['sql']($value);
                if ($fragment) {
                    $sql[]  = $fragment;
                    $params = array_merge($params, $bound);
                }
                continue;
            }
            $column = $def['column'];
            if (!empty($def['multiple'])) {
                $place  = implode(',', array_fill(0, count($value), '?'));
                $sql[]  = "$column IN ($place)";
                $params = array_merge($params, $value);
            } else {
                $sql[]    = "$column = ?";
                $params[] = $value;
            }
        }
        return [$sql ? ' AND ' . implode(' AND ', $sql) : '', $params];
    }

    /**
     * Facet counts for one filter, computed with every OTHER filter applied so
     * the numbers reflect what a click would actually return.
     */
    public static function facet(string $table, string $baseWhere, array $baseParams, array $spec, array $active, string $key, string $column): array
    {
        $others = $active;
        unset($others[$key]);
        [$where, $params] = self::where($spec, $others);

        $rows = Database::all(
            "SELECT $column AS value, COUNT(*) AS total
             FROM $table
             WHERE $baseWhere$where AND $column IS NOT NULL AND $column <> ''
             GROUP BY $column ORDER BY total DESC, value ASC",
            array_merge($baseParams, $params)
        );
        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r['value']] = (int) $r['total'];
        }
        return $out;
    }

    /** Current query string with one filter value toggled on or off. */
    public static function toggleUrl(string $path, array $active, string $key, string $value, bool $multiple = true): string
    {
        $q = $active;
        if ($multiple) {
            $current = (array) ($q[$key] ?? []);
            $q[$key] = in_array($value, $current, true)
                ? array_values(array_diff($current, [$value]))
                : array_merge($current, [$value]);
            if (!$q[$key]) {
                unset($q[$key]);
            }
        } else {
            if (($q[$key] ?? null) === $value) {
                unset($q[$key]);
            } else {
                $q[$key] = $value;
            }
        }
        unset($q['page']);
        return url($path) . (self::queryString($q) ? '?' . self::queryString($q) : '');
    }

    public static function removeUrl(string $path, array $active, string $key, ?string $value = null): string
    {
        $q = $active;
        if ($value !== null && is_array($q[$key] ?? null)) {
            $q[$key] = array_values(array_diff($q[$key], [$value]));
            if (!$q[$key]) {
                unset($q[$key]);
            }
        } else {
            unset($q[$key]);
        }
        unset($q['page']);
        return url($path) . (self::queryString($q) ? '?' . self::queryString($q) : '');
    }

    public static function pageUrl(string $path, array $active, int $page): string
    {
        $q = $active;
        $q['page'] = $page;
        return url($path) . '?' . self::queryString($q);
    }

    /** Build a query string, using key[]=value for repeated filters. */
    public static function queryString(array $q): string
    {
        $parts = [];
        foreach ($q as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            foreach ((array) $value as $v) {
                $parts[] = rawurlencode((string) $key) . (is_array($value) ? '%5B%5D' : '') . '=' . rawurlencode((string) $v);
            }
        }
        return implode('&', $parts);
    }

    public static function isActive(array $active, string $key, string $value): bool
    {
        $current = $active[$key] ?? null;
        return is_array($current) ? in_array($value, $current, true) : (string) $current === $value;
    }

    /** @return array{rows:array,total:int,page:int,pages:int,perPage:int} */
    public static function paginate(string $sql, array $params, string $countSql, array $countParams, int $perPage = 10): array
    {
        $total = (int) Database::value($countSql, $countParams);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = max(1, min($pages, (int) ($_GET['page'] ?? 1)));
        $rows  = Database::all($sql . ' LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage), $params);
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages, 'perPage' => $perPage];
    }
}
