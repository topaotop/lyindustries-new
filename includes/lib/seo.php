<?php
declare(strict_types=1);

/**
 * SEO / AEO (Phase 4): canonical URL, Open Graph, structured data, and keeping every
 * non-production copy (local dev, the company test server) out of search engines.
 * Production = www.lyindustries.com (lyindustries.com without www counts too; it should 301 to www).
 */

const SITE_PROD_ORIGIN = 'https://www.lyindustries.com';
const SITE_OG_IMAGE = 'assets/img/brand/og-image.jpg';   // 1200×630 link-preview picture
const SEO_TITLE_RANGE = [30, 60];   // recommended lengths (admin hints): what fits a Google result line
const SEO_DESC_RANGE = [70, 160];

/** Settings key of a page's focus keyword — admin-only, never printed on the site. */
function seo_kw_key(string $slug, string $l): string
{
    return "seo_kw_{$slug}_$l";
}

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

/** Business names for structured data: the company name first, then the alternate names from the settings. */
function org_alternate_names(): array
{
    return array_values(array_filter(array_map('trim', explode(',', site('org_alt_names')))));
}

/** Postal address for structured data (English street from ข้อมูลติดต่อ, the rest from SEO & AEO). */
function org_postal_address(): array
{
    return [
        '@type' => 'PostalAddress',
        'streetAddress' => site('address1_en'),
        'addressLocality' => site('addr_locality'),
        'addressRegion' => site('addr_region'),
        'postalCode' => site('addr_postal'),
        'addressCountry' => site('addr_country'),
    ];
}

/** Profiles of the business elsewhere (LINE + social pages set in SEO & AEO) — schema "sameAs". */
function org_same_as(): array
{
    $urls = [site('line_url')];
    foreach (['facebook', 'instagram', 'linkedin', 'youtube', 'tiktok'] as $k) {
        $urls[] = site('social_' . $k);
    }

    return array_values(array_unique(array_filter($urls)));
}

/** JSON fragment (no braces) for an object member, e.g. json_member('address', [...]) → "address":{...} */
function json_member(string $name, mixed $value): string
{
    return json_encode($name) . ':' . json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
}

/** Link-preview picture of a page: its own (SEO & AEO) → the site picture → the built-in one. */
function og_image_path(array $meta): string
{
    foreach ([(string) ($meta['og_image'] ?? ''), site('og_image'), SITE_OG_IMAGE] as $path) {
        if ($path !== '' && content_local_image($path) !== '') {
            return $path;
        }
    }

    return SITE_OG_IMAGE;
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
        '<meta property="og:image" content="' . e(site_abs_url(og_image_path($meta))) . '">',
        '<meta property="og:image:width" content="1200">',
        '<meta property="og:image:height" content="630">',
        '<meta property="og:locale" content="' . $locale . '">',
        page_translated($slug) ? '<meta property="og:locale:alternate" content="' . (lang() === 'en' ? 'th_TH' : 'en_US') . '">' : '',
        '<meta name="twitter:card" content="summary_large_image">',
    ];
    // ownership proof for Google Search Console / Bing Webmaster Tools — only the real site needs it
    if ($slug === 'home' && is_production_host()) {
        foreach (['google-site-verification' => 'verify_google', 'msvalidate.01' => 'verify_bing'] as $name => $key) {
            if (site($key) !== '') {
                $tags[] = '<meta name="' . $name . '" content="' . e(site($key)) . '">';
            }
        }
    }

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
        'alternateName' => org_alternate_names(),
        'url' => schema_site_url(),
        'logo' => SITE_PROD_ORIGIN . '/assets/img/brand/apple-touch-icon.png',
        'image' => SITE_PROD_ORIGIN . '/' . SITE_OG_IMAGE,
        'foundingDate' => site('org_founding'),
        'email' => site('email'),
        'telephone' => phone_schema(site('phone')),
        'address' => org_postal_address(),
        'sameAs' => org_same_as(),
    ];
    if (site('geo_lat') !== '' && site('geo_lng') !== '') {
        $org['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) site('geo_lat'), 'longitude' => (float) site('geo_lng')];
    }
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
