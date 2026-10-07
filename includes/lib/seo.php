<?php
declare(strict_types=1);

/**
 * SEO / AEO (Phase 4): canonical URL, Open Graph, structured data, and keeping every
 * non-production copy (local dev, the company test server) out of search engines.
 * Production = www.lyindustries.com (lyindustries.com without www counts too; it should 301 to www).
 */

const SITE_PROD_ORIGIN = 'https://www.lyindustries.com';
const SITE_OG_IMAGE = 'assets/img/brand/og-image.jpg';   // 1200×630 link-preview picture

function is_production_host(): bool
{
    return in_array(strtolower((string) ($_SERVER['HTTP_HOST'] ?? '')), ['www.lyindustries.com', 'lyindustries.com'], true);
}

/** Dev/test copies answer every request with noindex (robots.php also disallows them). */
function seo_init(): void
{
    if (PHP_SAPI !== 'cli' && !is_production_host() && !headers_sent()) {
        header('X-Robots-Tag: noindex, nofollow');
    }
}

/** Absolute URL of a site file (image …) on this host. */
function site_abs_url(string $path): string
{
    return preg_replace('#/[^/]*$#', '/', page_abs_url('th', 'index.php')) . ltrim($path, '/');
}

/** Organisation URL for structured data — always the production site (schema describes the real business). */
function schema_site_url(): string
{
    return SITE_PROD_ORIGIN . '/' . (lang() === 'en' ? 'en/' : '');
}

/**
 * Everything SEO in <head> after <title>: canonical, hreflang/noindex (lang_head_tags), Open Graph and
 * Twitter card. $meta = page_meta() of the page.
 */
function seo_head_tags(string $slug, array $meta): string
{
    $file = SITE_PAGE_FILES[$slug] ?? 'index.php';
    $url = page_abs_url(lang(), $file);
    $locale = lang() === 'en' ? 'en_US' : 'th_TH';
    $tags = [
        '<link rel="canonical" href="' . e($url) . '">',
        rtrim(lang_head_tags($slug)),
        '<meta property="og:type" content="website">',
        '<meta property="og:site_name" content="L.Y. Industries">',
        '<meta property="og:title" content="' . e($meta['title']) . '">',
        $meta['meta_desc'] !== '' ? '<meta property="og:description" content="' . e($meta['meta_desc']) . '">' : '',
        '<meta property="og:url" content="' . e($url) . '">',
        '<meta property="og:image" content="' . e(site_abs_url(SITE_OG_IMAGE)) . '">',
        '<meta property="og:image:width" content="1200">',
        '<meta property="og:image:height" content="630">',
        '<meta property="og:locale" content="' . $locale . '">',
        page_translated($slug) ? '<meta property="og:locale:alternate" content="' . (lang() === 'en' ? 'th_TH' : 'en_US') . '">' : '',
        '<meta name="twitter:card" content="summary_large_image">',
    ];

    return implode("\n", array_filter($tags, static fn(string $t): bool => $t !== '')) . "\n";
}

/** Strip the admin-preview zero-width tags from text that goes into structured data. */
function seo_plain(string $text): string
{
    return trim(preg_replace('/\x{2063}[\x{200B}\x{200C}\x{200D}\x{2060}]+\x{2064}/u', '', $text) ?? $text);
}

/**
 * Homepage structured data (AEO): Organization + WebSite + FAQPage built from the FAQ list in the
 * admin, so answer engines quote what the page itself says.
 *
 * @param list<array{q: string, a: string}> $faq
 */
function home_schema_json(array $faq): string
{
    $org = [
        '@type' => ['Organization', 'LocalBusiness'],
        '@id' => SITE_PROD_ORIGIN . '/#organization',
        'name' => company_name(),
        'alternateName' => ['L.Y. Industries', 'LY Industries', 'LYI'],
        'url' => schema_site_url(),
        'logo' => SITE_PROD_ORIGIN . '/assets/img/brand/apple-touch-icon.png',
        'image' => SITE_PROD_ORIGIN . '/' . SITE_OG_IMAGE,
        'foundingDate' => '1978',
        'email' => site('email'),
        'telephone' => phone_schema(site('phone')),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '124 Soi Ram Inthra 109, Phraya Suren Road, Bang Chan',
            'addressLocality' => 'Khlong Sam Wa',
            'addressRegion' => 'Bangkok',
            'postalCode' => '10510',
            'addressCountry' => 'TH',
        ],
        'sameAs' => array_values(array_filter([site('line_url')])),
    ];
    $graph = [
        $org,
        ['@type' => 'WebSite', '@id' => SITE_PROD_ORIGIN . '/#website', 'url' => schema_site_url(), 'name' => 'L.Y. Industries',
         'inLanguage' => lang() === 'en' ? 'en' : 'th', 'publisher' => ['@id' => SITE_PROD_ORIGIN . '/#organization']],
    ];
    $questions = [];
    foreach ($faq as $item) {
        $q = seo_plain((string) ($item['q'] ?? ''));
        $a = seo_plain((string) ($item['a'] ?? ''));
        if ($q !== '' && $a !== '') {
            $questions[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
        }
    }
    if ($questions !== []) {
        $graph[] = ['@type' => 'FAQPage', 'inLanguage' => lang() === 'en' ? 'en' : 'th', 'mainEntity' => $questions];
    }

    return json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
}
