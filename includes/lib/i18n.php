<?php
declare(strict_types=1);

/**
 * Two languages by URL: /about.php = Thai (default), /en/about.php = English. There is no en/
 * folder — .htaccess / web.config rewrite /en/<page> to the same page with ?lang=en, and
 * /en/assets|uploads|api/... to the real files, so relative links keep working in both languages.
 *
 * An English page is only offered to search engines (hreflang) once it is fully translated;
 * until then /en/... shows Thai where English is missing and carries noindex.
 */

const SITE_LANGS = ['th', 'en'];
const SITE_PAGE_FILES = ['home' => 'index.php', 'about' => 'about.php', 'catalog' => 'catalog.php', 'contact' => 'contact.php'];

/** Pick the language of this request (called once from bootstrap.php). */
function i18n_init(): void
{
    if (($_GET['lang'] ?? '') === 'en') {
        lang('en');
    }
}

/** Site root path for links, e.g. "/lyindustries-dev/" (works under any sub-folder). */
function site_base_path(): string
{
    $dir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
    $dir = preg_replace('#/(admin|api/v1)$#', '', $dir) ?? $dir;

    return rtrim($dir, '/') . '/';
}

/** Path of a page in a language: page_url('en', 'about.php') → "/…/en/about.php". */
function page_url(string $lang, string $file): string
{
    return site_base_path() . ($lang === 'en' ? 'en/' : '') . ($file === 'index.php' ? '' : $file);
}

/** Absolute URL (production always on https://www.lyindustries.com). */
function page_abs_url(string $lang, string $file): string
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    if ($host === 'lyindustries.com' || $host === 'www.lyindustries.com') {
        return 'https://www.lyindustries.com' . page_url($lang, $file);
    }
    $https = ($_SERVER['HTTPS'] ?? '') !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off';

    return ($https ? 'https://' : 'http://') . $host . page_url($lang, $file);
}

/**
 * Is the English version of a page complete? Every page text, the page title + description and
 * (for the homepage) every translatable list field must have English.
 */
function page_translated(string $slug): bool
{
    static $memo = [];
    if (isset($memo[$slug])) {
        return $memo[$slug];
    }
    $raw = content_raw();
    if ($raw === null) {
        return $memo[$slug] = false;
    }
    $pages = [$slug];
    if ($slug === 'catalog' || $slug === 'contact') {
        $pages[] = 'footer';   // shared footer of these two pages
    }
    foreach ($pages as $p) {
        $file = APP_ROOT . "/includes/blocks/$p.php";
        foreach (is_file($file) ? array_keys(require $file) : [] as $key) {
            if ((string) ($raw['blocks'][$key]['en'] ?? '') === '') {
                return $memo[$slug] = false;
            }
        }
    }
    $meta = $raw['pages'][$slug] ?? [];
    if ((string) ($meta['title_en'] ?? '') === '' || (string) ($meta['meta_desc_en'] ?? '') === '') {
        return $memo[$slug] = false;
    }
    if ($slug === 'home') {
        $schema = require APP_ROOT . '/includes/schema/lists.php';
        foreach ($schema as $listKey => $list) {
            foreach ($raw['items'][$listKey] ?? [] as $row) {
                if (!($row['active'] ?? true)) {
                    continue;
                }
                foreach ($list['fields'] as $f => $def) {
                    if ($def['i18n'] && (string) ($row['th'][$f] ?? '') !== '' && (string) ($row['en'][$f] ?? '') === '') {
                        return $memo[$slug] = false;
                    }
                }
            }
        }
    }

    return $memo[$slug] = true;
}

/**
 * Tags for <head>: hreflang alternates once the English page is complete; noindex on an English
 * page that is not complete yet (it still shows Thai text). Empty for an untranslated Thai page,
 * so the Thai pages stay exactly as before until English goes live.
 */
function lang_head_tags(string $slug): string
{
    $file = SITE_PAGE_FILES[$slug] ?? 'index.php';
    if (!page_translated($slug)) {
        return lang() === 'en' ? '<meta name="robots" content="noindex, follow">' . "\n" : '';
    }
    $out = '';
    foreach (['th' => 'th', 'en' => 'en', 'x-default' => 'th'] as $hreflang => $l) {
        $out .= '<link rel="alternate" hreflang="' . $hreflang . '" href="' . e(page_abs_url($l, $file)) . '">' . "\n";
    }

    return $out;
}
