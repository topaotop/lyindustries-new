<?php
declare(strict_types=1);

/**
 * Content layer: lists from lyiweb_items, with a file cache so public pages rarely touch the DB.
 *
 * Read order:  fresh cache  →  database (then refresh cache)  →  stale cache  →  built-in data
 * (includes/home-data.php). The site keeps working when the office DB / internet is down.
 * After a failed DB attempt we stop retrying for CONTENT_RETRY_SECONDS so pages don't wait on
 * connection timeouts. Set env LYIWEB_DB=off to force the "DB unavailable" path (testing).
 */

require_once __DIR__ . '/db.php';

const CONTENT_CACHE_TTL     = 300;
const CONTENT_RETRY_SECONDS = 60;

function content_cache_file(): string
{
    return APP_ROOT . '/cache/content.json';
}

function content_fail_marker(): string
{
    return APP_ROOT . '/cache/db-unavailable';
}

/** Drop the cache so the next request reloads from the DB (call after every admin save). */
function content_cache_clear(): void
{
    @unlink(content_cache_file());
    @unlink(content_fail_marker());
}

/**
 * Raw active rows grouped by list_key, or null when neither DB nor cache is available.
 *
 * @return array<string, list<array<string, mixed>>>|null
 */
function content_raw(): ?array
{
    static $memo = false;
    if ($memo !== false) {
        return $memo;
    }

    $cache = content_cache_file();
    $stale = null;
    if (is_file($cache)) {
        $stale = json_decode((string) file_get_contents($cache), true);
        if (is_array($stale) && filemtime($cache) > time() - CONTENT_CACHE_TTL) {
            return $memo = $stale;
        }
    }

    $marker = content_fail_marker();
    $dbOff = getenv('LYIWEB_DB') === 'off'
        || (is_file($marker) && filemtime($marker) > time() - CONTENT_RETRY_SECONDS);
    if ($dbOff) {
        return $memo = (is_array($stale) ? $stale : null);
    }

    try {
        $rows = db_rows(
            'SELECT i.list_key, i.data_th, i.data_en, i.data_common, m.file_path
               FROM dbo.lyiweb_items i
               LEFT JOIN dbo.lyiweb_media m ON m.id = i.image_id
              WHERE i.is_active = 1
              ORDER BY i.list_key, i.sort_order, i.id'
        );
    } catch (Throwable $e) {
        error_log('[lyiweb] content DB unavailable: ' . $e->getMessage());
        content_write_file($marker, (string) time());
        return $memo = (is_array($stale) ? $stale : null);
    }

    $grouped = [];
    foreach ($rows as $row) {
        $grouped[$row['list_key']][] = [
            'th'     => json_decode((string) $row['data_th'], true) ?: [],
            'en'     => json_decode((string) $row['data_en'], true) ?: [],
            'common' => json_decode((string) $row['data_common'], true) ?: [],
            'media'  => $row['file_path'],
        ];
    }
    content_write_file($cache, json_encode($grouped, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    return $memo = $grouped;
}

/**
 * One content list in the shape templates expect (fields/order from includes/schema/lists.php).
 * Lists with no rows in the DB fall back to the built-in data.
 *
 * @return list<array<string, mixed>>
 */
function content_list(string $key, string $lang = 'th'): array
{
    static $schema = null, $builtin = null;
    $schema ??= require APP_ROOT . '/includes/schema/lists.php';
    $fields = $schema[$key]['fields'] ?? null;
    if ($fields === null) {
        throw new InvalidArgumentException("Unknown content list: $key");
    }

    $rows = content_raw()[$key] ?? [];
    if ($rows === []) {
        $builtin ??= require APP_ROOT . '/includes/home-data.php';
        return $builtin[$key] ?? [];
    }

    $out = [];
    foreach ($rows as $row) {
        $item = [];
        foreach ($fields as $name => $def) {
            if ($def['i18n']) {
                $value = ($lang !== 'th' && ($row[$lang][$name] ?? '') !== '') ? $row[$lang][$name] : ($row['th'][$name] ?? '');
            } elseif ($def['type'] === 'image' && $row['media'] !== null) {
                $value = $row['media'];
            } else {
                $value = $row['common'][$name] ?? null;
            }
            $item[$name] = match ($def['type']) {
                'int'   => (int) $value,
                'bool'  => (bool) $value,
                default => (string) $value,
            };
        }
        $out[] = $item;
    }

    return $out;
}

/** Write a file atomically; silently skipped when the folder is not writable. */
function content_write_file(string $path, string $data): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return;
    }
    $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, $data, LOCK_EX) !== false) {
        @rename($tmp, $path) || @unlink($tmp);
    }
}
