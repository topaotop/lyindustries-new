<?php
declare(strict_types=1);

/**
 * /robots.txt (rewritten here by .htaccess / web.config). Production allows everything except
 * the back office and endpoints and points to the sitemap; every other host (dev, test) is closed.
 */

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');
if (!is_production_host()) {
    echo "# Test copy of www.lyindustries.com — not for search engines\nUser-agent: *\nDisallow: /\n";
    exit;
}
echo "User-agent: *\n",
     "Disallow: /admin/\n",
     "Disallow: /api/\n",
     "Disallow: /en/api/\n",
     "\n",
     'Sitemap: ', SITE_PROD_ORIGIN, "/sitemap.xml\n";
