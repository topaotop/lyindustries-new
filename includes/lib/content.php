<?php
declare(strict_types=1);

/**
 * Content layer: lists (lyiweb_items), site settings (lyiweb_settings) and page SEO (lyiweb_pages), with a file cache so public pages rarely touch the DB.
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

/** Bump when the cached structure changes so old cache files are ignored. */
const CONTENT_CACHE_VERSION = 2;

/**
 * Everything public pages need from the DB in one round trip, or null when neither DB nor cache
 * is available: ['items' => list_key => rows, 'settings' => key => value, 'pages' => slug => row].
 *
 * @return array{v: int, items: array<string, list<array<string, mixed>>>, settings: array<string, string>, pages: array<string, array<string, ?string>>}|null
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
        if (!is_array($stale) || ($stale['v'] ?? 0) !== CONTENT_CACHE_VERSION) {
            $stale = null;
        } elseif (filemtime($cache) > time() - CONTENT_CACHE_TTL) {
            return $memo = $stale;
        }
    }

    $marker = content_fail_marker();
    $dbOff = getenv('LYIWEB_DB') === 'off'
        || (is_file($marker) && filemtime($marker) > time() - CONTENT_RETRY_SECONDS);
    if ($dbOff) {
        return $memo = $stale;
    }

    try {
        $rows = db_rows(
            'SELECT i.list_key, i.data_th, i.data_en, i.data_common, m.file_path
               FROM dbo.lyiweb_items i
               LEFT JOIN dbo.lyiweb_media m ON m.id = i.image_id
              WHERE i.is_active = 1
              ORDER BY i.list_key, i.sort_order, i.id'
        );
        $settingRows = db_rows('SELECT setting_key, value FROM dbo.lyiweb_settings');
        $pageRows = db_rows('SELECT slug, title_th, title_en, meta_desc_th, meta_desc_en FROM dbo.lyiweb_pages');
    } catch (Throwable $e) {
        error_log('[lyiweb] content DB unavailable: ' . $e->getMessage());
        content_write_file($marker, (string) time());
        return $memo = $stale;
    }

    $data = ['v' => CONTENT_CACHE_VERSION, 'items' => [], 'settings' => [], 'pages' => []];
    foreach ($settingRows as $row) {
        $data['settings'][$row['setting_key']] = (string) $row['value'];
    }
    foreach ($pageRows as $row) {
        $data['pages'][$row['slug']] = $row;
    }
    foreach ($rows as $row) {
        $data['items'][$row['list_key']][] = [
            'th'     => json_decode((string) $row['data_th'], true) ?: [],
            'en'     => json_decode((string) $row['data_en'], true) ?: [],
            'common' => json_decode((string) $row['data_common'], true) ?: [],
            'media'  => $row['file_path'],
        ];
    }
    content_write_file($cache, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    return $memo = $data;
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

    $rows = content_raw()['items'][$key] ?? [];
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

/**
 * Site setting (contact data, links, hours) from lyiweb_settings; empty or missing values fall
 * back to includes/site-defaults.php.
 */
function site(string $key): string
{
    static $defaults = null;
    $defaults ??= (require APP_ROOT . '/includes/site-defaults.php')['settings'];
    $value = content_raw()['settings'][$key] ?? '';
    if ($value === '') {
        $value = $defaults[$key] ?? '';
    }

    return $value;
}

/**
 * Page <title> and meta description for a page slug (home/about/catalog/contact).
 * English falls back to Thai until it is translated.
 *
 * @return array{title: string, meta_desc: string}
 */
function page_meta(string $slug, string $lang = 'th'): array
{
    static $defaults = null;
    $defaults ??= (require APP_ROOT . '/includes/site-defaults.php')['pages'];
    $row = content_raw()['pages'][$slug] ?? [];
    $pick = static function (string $field) use ($row, $lang, $defaults, $slug): string {
        foreach ([$field . '_' . $lang, $field . '_th'] as $col) {
            if ((string) ($row[$col] ?? '') !== '') {
                return (string) $row[$col];
            }
        }
        return $defaults[$slug][$field . '_th'] ?? '';
    };

    return ['title' => $pick('title'), 'meta_desc' => $pick('meta_desc')];
}

/* ---- Formats derived from the settings (one value in the admin → every place on the site) ---- */

/** "02-517-0768" → "tel:025170768" */
function tel_href_local(): string
{
    return 'tel:' . preg_replace('/\D+/', '', site('phone'));
}

/** "02-517-0768" → "tel:+6625170768" */
function tel_href_intl(): string
{
    return 'tel:+66' . substr((string) preg_replace('/\D+/', '', site('phone')), 1);
}

/** Thai number "02-517-0768" → schema.org "+66-2-517-0768" */
function phone_schema(string $number): string
{
    return '+66-' . substr($number, 1);
}

/** "02-517-0768 ต่อ 120, 121" */
function phone_display_th(): string
{
    return site('phone') . (site('phone_ext') !== '' ? ' ต่อ ' . site('phone_ext') : '');
}

/** Full Thai address; $separator is the raw HTML between the two lines (e.g. "<br>"). */
function address_th_html(string $separator = ' '): string
{
    return e(site('address1_th')) . $separator . e(site('address2_th'));
}

/** "จันทร์ – ศุกร์: 08:30 – 17:30 น.<br>เสาร์: 08:30 – 12:00 น." (escaped HTML) */
function hours_th_html(): string
{
    return 'จันทร์ – ศุกร์: ' . e(site('hours_weekday_open')) . ' – ' . e(site('hours_weekday_close')) . ' น.<br>'
         . 'เสาร์: ' . e(site('hours_sat_open')) . ' – ' . e(site('hours_sat_close')) . ' น.';
}

/** Value safe to print inside a JSON string in <script type="application/ld+json"> (without quotes). */
function json_inner(string $value): string
{
    return substr((string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG), 1, -1);
}
