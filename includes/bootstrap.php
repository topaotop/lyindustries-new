<?php
declare(strict_types=1);

/**
 * Shared site settings and helpers. Every page requires this file first.
 */

const SITE_PHONE         = '02-517-0768';
const SITE_PHONE_EXT     = '120, 121';
const SITE_PHONE_TEL     = '025170768';
const SITE_EMAIL         = 'sales@lyindustries.com';
const SITE_LINE_URL      = 'https://line.me/R/ti/p/@lyindustries';
const SITE_TRIMRITE_URL  = 'https://www.trimrite.com/';
const SITE_INSPIRATION_URL = 'https://www.lyindustries.com/lyinspirationhub/';

/** Shown wherever a real photo has not been uploaded yet. */
const SITE_PLACEHOLDER_IMG = 'assets/img/placeholder.svg';

/** Image path, or the "image pending" placeholder when none is set. */
function img_src(string $path): string
{
    return $path !== '' ? $path : SITE_PLACEHOLDER_IMG;
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
        ['key' => 'trimrite', 'label' => 'TRIMRITE®',      'href' => SITE_TRIMRITE_URL, 'external' => true],
        ['key' => 'about',    'label' => 'เกี่ยวกับเรา',      'href' => 'about.php'],
        ['key' => 'contact',  'label' => 'ติดต่อเรา',        'href' => 'contact.php'],
    ];
}

/** target/rel attributes for links that leave the site. */
function external_attrs(array $link): string
{
    return empty($link['external']) ? '' : ' target="_blank" rel="noopener"';
}
