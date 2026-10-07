<?php
declare(strict_types=1);

/**
 * /sitemap.xml (rewritten here by .htaccess / web.config). Thai pages always; an English page only
 * once it is fully translated (page_translated) — with hreflang alternates so search engines pair
 * the two languages. lastmod = the latest edit of the page's texts / SEO / lists in the admin.
 */

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$lastmod = [];
try {
    foreach (db_rows('SELECT page_slug, MAX(updated_at) AS t FROM dbo.lyiweb_blocks GROUP BY page_slug') as $r) {
        $lastmod[$r['page_slug']] = $r['t'];
    }
    foreach (db_rows('SELECT slug, updated_at AS t FROM dbo.lyiweb_pages') as $r) {
        $lastmod[$r['slug']] = max($lastmod[$r['slug']] ?? $r['t'], $r['t']);
    }
    $items = db_rows("SELECT MAX(updated_at) AS t FROM dbo.lyiweb_items WHERE list_key LIKE 'home.%'")[0]['t'] ?? null;
    if ($items !== null) {
        $lastmod['home'] = max($lastmod['home'] ?? $items, $items);
    }
    // texts shared by every page (menus) and the shared footer count for the pages that show them
    foreach (SITE_PAGE_FILES as $slug => $file) {
        foreach (array_merge(['site'], in_array($slug, ['catalog', 'contact'], true) ? ['footer'] : []) as $shared) {
            if (isset($lastmod[$shared])) {
                $lastmod[$slug] = max($lastmod[$slug] ?? $lastmod[$shared], $lastmod[$shared]);
            }
        }
    }
} catch (Throwable) {
    // DB down: sitemap without lastmod
}

$x = static fn(string $s): string => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n",
     '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">', "\n";
foreach (SITE_PAGE_FILES as $slug => $file) {
    $both = page_translated($slug);
    foreach ($both ? ['th', 'en'] : ['th'] as $l) {
        echo "  <url>\n    <loc>", $x(page_abs_url($l, $file)), "</loc>\n";
        if (isset($lastmod[$slug]) && $lastmod[$slug] instanceof DateTimeInterface) {
            echo '    <lastmod>', $lastmod[$slug]->format('Y-m-d'), "</lastmod>\n";
        }
        if ($both) {
            foreach (['th' => 'th', 'en' => 'en', 'x-default' => 'th'] as $hl => $target) {
                echo '    <xhtml:link rel="alternate" hreflang="', $hl, '" href="', $x(page_abs_url($target, $file)), "\"/>\n";
            }
        }
        echo "  </url>\n";
    }
}
echo "</urlset>\n";
