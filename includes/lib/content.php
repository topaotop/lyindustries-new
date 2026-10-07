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
const CONTENT_CACHE_VERSION = 6;

/**
 * Everything public pages need from the DB in one round trip, or null when neither DB nor cache
 * is available: ['items' => list_key => rows, 'settings' => key => value, 'pages' => slug => row,
 * 'blocks' => 'page.key' => ['th' => …, 'en' => …]].
 *
 * @return array{v: int, items: array<string, list<array<string, mixed>>>, settings: array<string, string>, pages: array<string, array<string, ?string>>, blocks: array<string, array{th: ?string, en: ?string}>}|null
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
            'SELECT i.id, i.list_key, i.is_active, i.data_th, i.data_en, i.data_common, m.file_path
               FROM dbo.lyiweb_items i
               LEFT JOIN dbo.lyiweb_media m ON m.id = i.image_id
              ORDER BY i.list_key, i.sort_order, i.id'
        );
        $settingRows = db_rows('SELECT setting_key, value FROM dbo.lyiweb_settings');
        $pageRows = db_rows('SELECT p.slug, p.title_th, p.title_en, p.meta_desc_th, p.meta_desc_en, m.file_path AS og_image
                               FROM dbo.lyiweb_pages p LEFT JOIN dbo.lyiweb_media m ON m.id = p.og_image_id');
        $blockRows = db_rows('SELECT page_slug, block_key, value_th, value_en FROM dbo.lyiweb_blocks');
    } catch (Throwable $e) {
        error_log('[lyiweb] content DB unavailable: ' . $e->getMessage());
        content_write_file($marker, (string) time());
        return $memo = $stale;
    }

    $data = ['v' => CONTENT_CACHE_VERSION, 'items' => [], 'settings' => [], 'pages' => [], 'blocks' => []];
    foreach ($blockRows as $row) {
        $data['blocks'][$row['page_slug'] . '.' . $row['block_key']] = ['th' => $row['value_th'], 'en' => $row['value_en']];
    }
    foreach ($settingRows as $row) {
        $data['settings'][$row['setting_key']] = (string) $row['value'];
    }
    foreach ($pageRows as $row) {
        $data['pages'][$row['slug']] = $row;
    }
    foreach ($rows as $row) {
        // hidden rows are kept so a list whose items are all hidden shows nothing (not the built-in data)
        $data['items'][$row['list_key']][] = [
            'id'     => (int) $row['id'],
            'active' => (bool) $row['is_active'],
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
        if (!($row['active'] ?? true)) {
            continue;
        }
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
                'image' => content_local_image((string) $value),
                default => (string) $value,
            };
        }
        if (defined('LYIWEB_PREVIEW') && isset($row['id'])) {
            foreach ($fields as $name => $def) {
                if (in_array($def['type'], ['text', 'textarea'], true) && empty($def['css']) && $item[$name] !== '') {
                    $item[$name] = content_preview_mark($key, (int) $row['id'], $name) . $item[$name];
                }
            }
        }
        $out[] = $item;
    }

    return $out;
}

/**
 * Admin preview only: an invisible zero-width tag put in front of a list text so the preview
 * script can tell which item/field each text on the page comes from (no template changes needed).
 * Tag n is listed in $GLOBALS['lyiweb_preview_marks'][n] = [list, item id, field].
 */
function content_preview_mark(string $list, int $id, string $field): string
{
    $n = count($GLOBALS['lyiweb_preview_marks'] ??= []);
    $GLOBALS['lyiweb_preview_marks'][] = [$list, $id, $field];
    $digits = ["\u{200B}", "\u{200C}", "\u{200D}", "\u{2060}"];
    $code = '';
    do {
        $code = $digits[$n % 4] . $code;
        $n = intdiv($n, 4);
    } while ($n > 0);

    return "\u{2063}" . $code . "\u{2064}";
}

/**
 * A site-relative image path only when the file exists on this server, else '' (= "Image pending").
 * Uploads live per server (dev and the company server share test_LYI but not their uploads/ folder),
 * so a row can point to a file this machine does not have — never emit a broken <img>.
 */
function content_local_image(string $path): string
{
    if ($path === '' || preg_match('#^(https?:)?//#i', $path)) {
        return $path;
    }

    $file = strtok($path, '?#');   // paths may carry a cache-buster such as ?v=2

    return is_file(APP_ROOT . '/' . ltrim((string) $file, '/')) ? $path : '';
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

    return ['title' => $pick('title'), 'meta_desc' => $pick('meta_desc'), 'og_image' => (string) ($row['og_image'] ?? '')];
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
/** Phone with extension in the page language, e.g. "02-517-0768 ต่อ 120, 121" (pattern site.labels.03). */
function phone_display(): string
{
    $ext = site('phone_ext');

    return $ext === '' ? site('phone') : strtr(block_text('site.labels.03'), ['{phone}' => site('phone'), '{ext}' => $ext]);
}

/** Company name in the page language (company_en on English pages when set). */
function company_name(): string
{
    return lang() === 'en' && site('company_en') !== '' ? site('company_en') : site('company_th');
}

/** Full address in the page language; $separator is the raw HTML between the two lines (e.g. "<br>"). */
function address_html(string $separator = ' '): string
{
    $l = lang() === 'en' && site('address1_en') !== '' ? 'en' : 'th';

    return e(site('address1_' . $l)) . $separator . e(site('address2_' . $l));
}

/** Opening hours in the page language (patterns site.labels.04/05), two lines as escaped HTML. */
function hours_html(): string
{
    $line = static fn(string $key, string $open, string $close): string
        => e(strtr(block_text($key), ['{open}' => site($open), '{close}' => site($close)]));

    return $line('site.labels.04', 'hours_weekday_open', 'hours_weekday_close') . '<br>'
         . $line('site.labels.05', 'hours_sat_open', 'hours_sat_close');
}

/** Value safe to print inside a JSON string in <script type="application/ld+json"> (without quotes). */
function json_inner(string $value): string
{
    return substr((string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG), 1, -1);
}

/* ---- Page copy (lyiweb_blocks) ---- */

/** Language of the current request (set once per page; "th" until the English site exists). */
function lang(?string $set = null): string
{
    static $lang = 'th';
    if ($set !== null) {
        $lang = $set;
    }

    return $lang;
}

/**
 * Raw text of a copy block, key "page.section.nn" (e.g. "home.hero.01"). English falls back to
 * Thai; a block missing from the DB falls back to includes/blocks/<page>.php.
 */
function block_text(string $key): string
{
    static $defaults = [];
    $value = content_raw()['blocks'][$key] ?? null;
    $lang = lang();
    if ($value !== null) {
        if ($lang !== 'th' && (string) ($value[$lang] ?? '') !== '') {
            return (string) $value[$lang];
        }
        if ((string) ($value['th'] ?? '') !== '') {
            return (string) $value['th'];
        }
    }
    $page = strstr($key, '.', true);
    if (!isset($defaults[$page])) {
        $file = APP_ROOT . '/includes/blocks/' . basename((string) $page) . '.php';
        $defaults[$page] = is_file($file) ? require $file : [];
    }

    return $defaults[$page][$key] ?? '';
}

/** Escaped copy block for templates: <?= b('home.hero.01') ?> */
/** Copy block for use inside an HTML attribute (escaped, never wrapped by the admin preview). */
function ba(string $key): string
{
    return e(block_text($key));
}

function b(string $key): string
{
    $text = e(block_text($key));

    // admin preview (admin/preview.php) marks each text so it can be clicked and edited in place
    return defined('LYIWEB_PREVIEW') ? '<span data-lyiweb-b="' . e($key) . '">' . $text . '</span>' : $text;
}
