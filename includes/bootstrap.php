<?php
declare(strict_types=1);

/**
 * Shared site settings and helpers. Every page requires this file first.
 */

define('APP_ROOT', dirname(__DIR__));

/** Shown wherever a real photo has not been uploaded yet. */
const SITE_PLACEHOLDER_IMG = 'assets/img/brand/placeholder.svg';

/** Image path, or the "image pending" placeholder when none is set. */
function img_src(string $path): string
{
    return $path !== '' ? $path : SITE_PLACEHOLDER_IMG;
}

/**
 * URL of a local CSS/JS file with ?v=<modified time>, so browsers fetch the new file as soon as it
 * changes instead of reusing a cached copy. $url is relative to the page being served.
 */
function asset(string $url): string
{
    $file = dirname((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) . '/' . $url;
    $mtime = is_file($file) ? filemtime($file) : false;

    return $mtime === false ? $url : $url . '?v=' . $mtime;
}

/** Escape a value for HTML text or attribute output. */
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/**
 * Top navigation links shared by the sub-pages (about, catalog, contact).
 *
 * @return list<array{key: string, label: string, href: string, external?: bool}>
 */
function site_nav(): array
{
    return [
        ['key' => 'home',     'label' => 'หน้าแรก',        'href' => 'index.php'],
        ['key' => 'process',  'label' => 'กระบวนการผลิต',   'href' => 'index.php#process'],
        ['key' => 'catalog',  'label' => 'แคตาล็อกสินค้า',   'href' => 'catalog.php'],
        ['key' => 'trimrite', 'label' => 'TRIMRITE®',      'href' => site('trimrite_url'), 'external' => true],
        ['key' => 'about',    'label' => 'เกี่ยวกับเรา',      'href' => 'about.php'],
        ['key' => 'contact',  'label' => 'ติดต่อเรา',        'href' => 'contact.php'],
    ];
}

/** target/rel attributes for links that leave the site. */
function external_attrs(array $link): string
{
    return empty($link['external']) ? '' : ' target="_blank" rel="noopener"';
}

// Content layer: lists, site settings (contact data, links) and page SEO — see includes/lib/content.php
require_once __DIR__ . '/lib/content.php';
