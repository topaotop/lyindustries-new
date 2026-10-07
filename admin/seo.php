<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';
require_once APP_ROOT . '/includes/lib/media.php';

/*
 * SEO & AEO — everything search engines and answer engines read, in one place:
 *   overview  health check of every page + links to Google's own test tools
 *   meta      page title + description (TH/EN) with a Google result preview   (role scope page.seo)
 *   share     link-preview picture for LINE/Facebook (site + per page)        (settings.edit + media.upload)
 *   business  business data in the structured data (names, address, social)  (settings.edit)
 *   faq       the FAQ answer engines quote (edited in รายการ & รูปภาพ)
 *   console   Google Search Console / Bing verification codes + sitemap address (settings.edit)
 * Labels carry the usual English SEO term in brackets (Title tag, Description …) so staff can match
 * them with guides and other tools.
 */

$user = admin_require();
$pages = array_intersect_key(admin_content_pages(), SITE_PAGE_FILES);
$canMeta = static fn(string $slug, string $l): bool => can_content($l, $slug, 'seo');
$anyMeta = (bool) array_filter(array_keys($pages), static fn($s) => $canMeta($s, 'en'));
$canSettings = can('settings.edit');
if (!$anyMeta && !$canSettings) {
    admin_require('settings.edit');   // shows the "no permission" page
}

$tabs = [
    'overview' => 'ภาพรวม',
    'meta'     => 'ชื่อหน้า & คำอธิบาย (Title tag & Description)',
    'share'    => 'รูปตอนแชร์ลิงก์ (Open Graph)',
    'business' => 'ข้อมูลธุรกิจสำหรับ Google (Schema)',
    'faq'      => 'คำถาม-คำตอบ (AEO)',
    'console'  => 'เชื่อมต่อ Google (Google Search Console)',
];
$tab = (string) ($_GET['tab'] ?? $_POST['tab'] ?? 'overview');
if (!isset($tabs[$tab])) {
    $tab = 'overview';
}
const SEO_KW_MAX = 100;
/** Verification code from a pasted meta tag or the bare code; '' = empty; null = not a valid code. */
function seo_verify_code(string $raw, string $metaName): ?string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    if (str_contains($raw, '<')) {
        if (preg_match('/name\s*=\s*["\']([^"\']+)["\']/i', $raw, $n) && strcasecmp($n[1], $metaName) !== 0) {
            return null;   // a tag for another service pasted into this box
        }
        if (!preg_match('/content\s*=\s*["\']([^"\']*)["\']/i', $raw, $m)) {
            return null;
        }
        $raw = trim($m[1]);
    }

    return preg_match('/^[A-Za-z0-9_\-]{8,120}$/', $raw) ? $raw : null;
}

$pageRows = [];
foreach (db_rows('SELECT p.slug, p.title_th, p.title_en, p.meta_desc_th, p.meta_desc_en, p.og_image_id, m.file_path AS og_path
                    FROM dbo.lyiweb_pages p LEFT JOIN dbo.lyiweb_media m ON m.id = p.og_image_id') as $r) {
    $pageRows[$r['slug']] = $r;
}
$settings = [];
foreach (db_rows('SELECT setting_key, value FROM dbo.lyiweb_settings') as $r) {
    $settings[$r['setting_key']] = (string) $r['value'];
}
$setting = static fn(string $k): string => ($settings[$k] ?? '') !== '' ? $settings[$k] : site($k);
$uid = $user['id'];
$clean = static fn(mixed $v): string => trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', str_replace(["\r\n", "\r", "\n"], ' ', (string) $v)));
$saveSetting = static function (string $key, string $value) use ($settings, $uid): void {
    if (array_key_exists($key, $settings)) {
        db_exec('UPDATE dbo.lyiweb_settings SET value = ?, updated_at = GETDATE(), updated_by = ? WHERE setting_key = ?', [$value, $uid, $key]);
    } else {
        db_exec('INSERT dbo.lyiweb_settings (setting_key, value, updated_by) VALUES (?, ?, ?)', [$key, $value, $uid]);
    }
    audit_log('update', 'lyiweb_settings', $key, $settings[$key] ?? null, $value);
};
$errors = [];

/* ---------- save ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_check_post();
    $changes = 0;

    if ($tab === 'meta') {
        $todo = [];
        $kwTodo = [];
        foreach ($pages as $slug => $p) {
            foreach (['title_th' => 200, 'meta_desc_th' => 400, 'title_en' => 200, 'meta_desc_en' => 400] as $col => $max) {
                $l = substr($col, -2);
                if (!isset($_POST['seo'][$slug][$col]) || !$canMeta($slug, $l)) {
                    continue;
                }
                $v = $clean($_POST['seo'][$slug][$col]);
                if (mb_strlen($v) > $max) {
                    $errors["$slug.$col"] = "ยาวเกิน $max ตัวอักษร";
                } elseif ($col === 'title_th' && $v === '') {
                    $errors["$slug.$col"] = 'ชื่อหน้าภาษาไทยห้ามว่าง';
                } elseif ($v !== (string) ($pageRows[$slug][$col] ?? '')) {
                    $todo[] = [$slug, $col, $v];
                }
            }
            foreach (['th', 'en'] as $l) {
                if (!isset($_POST['seo_kw'][$slug][$l]) || !$canMeta($slug, $l)) {
                    continue;
                }
                $v = $clean($_POST['seo_kw'][$slug][$l]);
                if (mb_strlen($v) > SEO_KW_MAX) {
                    $errors["kw.$slug.$l"] = 'ยาวเกิน ' . SEO_KW_MAX . ' ตัวอักษร';
                } elseif ($v !== ($settings[seo_kw_key($slug, $l)] ?? '')) {
                    $kwTodo[seo_kw_key($slug, $l)] = $v;
                }
            }
        }
        if ($errors === []) {
            db_transaction(static function () use ($todo, $kwTodo, $pageRows, $uid, $saveSetting, &$changes): void {
                foreach ($todo as [$slug, $col, $v]) {
                    // $col comes from the fixed list above, never from the request
                    db_exec("UPDATE dbo.lyiweb_pages SET $col = ?, updated_at = GETDATE(), updated_by = ? WHERE slug = ?", [$v === '' ? null : $v, $uid, $slug]);
                    audit_log('update', 'lyiweb_pages', "$slug.$col", $pageRows[$slug][$col] ?? null, $v);
                    $changes++;
                }
                foreach ($kwTodo as $key => $v) {
                    $saveSetting($key, $v);
                    $changes++;
                }
            });
        }
    }

    if ($tab === 'share' && $canSettings) {
        $files = $_FILES['og'] ?? null;
        $file = static function (string $k) use ($files): ?array {
            if (!is_array($files) || !isset($files['error'][$k]) || (int) $files['error'][$k] === UPLOAD_ERR_NO_FILE) {
                return null;
            }
            return ['name' => (string) $files['name'][$k], 'tmp_name' => (string) $files['tmp_name'][$k], 'error' => (int) $files['error'][$k], 'size' => (int) $files['size'][$k]];
        };
        foreach (array_merge(['site'], array_keys($pages)) as $k) {
            $f = $file($k);
            try {
                if ($f !== null) {
                    if (!can('media.upload')) {
                        throw new RuntimeException('ไม่มีสิทธิ์อัปโหลดรูป');
                    }
                    $m = media_store_upload($f, $uid, 'L.Y. Industries', ['cover' => [1200, 630], 'format' => 'jpg']);
                    if ($k === 'site') {
                        $saveSetting('og_image', $m['path']);
                    } else {
                        db_exec('UPDATE dbo.lyiweb_pages SET og_image_id = ?, updated_at = GETDATE(), updated_by = ? WHERE slug = ?', [$m['id'], $uid, $k]);
                        audit_log('update', 'lyiweb_pages', "$k.og_image", $pageRows[$k]['og_image_id'] ?? null, $m['id']);
                    }
                    $changes++;
                } elseif ($k !== 'site' && ($_POST['og_clear'][$k] ?? '') === '1' && ($pageRows[$k]['og_image_id'] ?? null) !== null) {
                    db_exec('UPDATE dbo.lyiweb_pages SET og_image_id = NULL, updated_at = GETDATE(), updated_by = ? WHERE slug = ?', [$uid, $k]);
                    audit_log('update', 'lyiweb_pages', "$k.og_image", $pageRows[$k]['og_image_id'], null);
                    $changes++;
                } elseif ($k === 'site' && ($_POST['og_clear']['site'] ?? '') === '1' && ($settings['og_image'] ?? '') !== SITE_OG_IMAGE) {
                    $saveSetting('og_image', SITE_OG_IMAGE);
                    $changes++;
                }
            } catch (RuntimeException $e) {
                $errors["og.$k"] = $e->getMessage();
            }
        }
    }

    if ($tab === 'business' && $canSettings) {
        $fields = [
            'org_alt_names' => 300, 'org_founding' => 4, 'addr_locality' => 100, 'addr_region' => 100, 'addr_postal' => 10, 'addr_country' => 2,
            'geo_lat' => 20, 'geo_lng' => 20,
            'social_facebook' => 300, 'social_instagram' => 300, 'social_linkedin' => 300, 'social_youtube' => 300, 'social_tiktok' => 300,
        ];
        $new = [];
        foreach ($fields as $k => $max) {
            $v = $clean($_POST['s'][$k] ?? '');
            $err = match (true) {
                mb_strlen($v) > $max => "ยาวเกิน $max ตัวอักษร",
                $k === 'org_founding' && $v !== '' && !preg_match('/^(19|20)\d\d$/', $v) => 'ปี ค.ศ. 4 หลัก เช่น 1978',
                $k === 'addr_country' && $v !== '' && !preg_match('/^[A-Z]{2}$/', $v) => 'รหัสประเทศ 2 ตัวอักษรพิมพ์ใหญ่ เช่น TH',
                in_array($k, ['geo_lat', 'geo_lng'], true) && $v !== '' && !is_numeric($v) => 'ตัวเลข เช่น 13.8569',
                $k === 'geo_lat' && $v !== '' && abs((float) $v) > 90, $k === 'geo_lng' && $v !== '' && abs((float) $v) > 180 => 'พิกัดไม่ถูกต้อง',
                str_starts_with($k, 'social_') && $v !== '' && !(preg_match('#^https://#i', $v) && filter_var($v, FILTER_VALIDATE_URL)) => 'ลิงก์ต้องขึ้นต้นด้วย https://',
                default => null,
            };
            if ($err !== null) {
                $errors["s.$k"] = $err;
            }
            $new[$k] = $v;
        }
        if (($new['geo_lat'] === '') !== ($new['geo_lng'] === '')) {
            $errors['s.geo_lng'] = 'ใส่ให้ครบทั้งละติจูดและลองจิจูด (หรือเว้นว่างทั้งคู่)';
        }
        if ($errors === []) {
            db_transaction(static function () use ($new, $settings, $saveSetting, &$changes): void {
                foreach ($new as $k => $v) {
                    if ($v !== ($settings[$k] ?? '')) {
                        $saveSetting($k, $v);
                        $changes++;
                    }
                }
            });
        }
    }

    if ($tab === 'console' && $canSettings) {
        $new = [];
        foreach (['verify_google' => 'google-site-verification', 'verify_bing' => 'msvalidate.01'] as $k => $metaName) {
            $code = seo_verify_code((string) ($_POST['s'][$k] ?? ''), $metaName);
            if ($code === null) {
                $errors["s.$k"] = 'รูปแบบไม่ถูกต้อง — คัดลอก meta tag ทั้งบรรทัดจาก ' . ($k === 'verify_google' ? 'Google Search Console' : 'Bing Webmaster Tools') . ' มาวาง';
            } else {
                $new[$k] = $code;
            }
        }
        if ($errors === []) {
            db_transaction(static function () use ($new, $settings, $saveSetting, &$changes): void {
                foreach ($new as $k => $v) {
                    if ($v !== ($settings[$k] ?? '')) {
                        $saveSetting($k, $v);
                        $changes++;
                    }
                }
            });
        }
    }

    if ($errors === []) {
        content_cache_clear();
        flash($changes > 0 ? 'ok' : 'info', $changes > 0 ? "บันทึกแล้ว $changes รายการ — หน้าเว็บอัปเดตทันที" : 'ไม่มีอะไรเปลี่ยน');
        header('Location: seo.php?tab=' . rawurlencode($tab));
        exit;
    }
}

/* ---------- health check ---------- */
$len = static fn(?string $s): int => mb_strlen((string) $s);
$rate = static function (int $n, array $range): string {
    return $n === 0 ? 'bad' : ($n < $range[0] || $n > $range[1] ? 'warn' : 'ok');
};
$faq = content_list('home.faq', 'th');
$faqEn = array_filter(content_list('home.faq', 'en'), static fn($f, $i) => $f['q'] !== ($faq[$i]['q'] ?? null), ARRAY_FILTER_USE_BOTH);
$prodUrl = static fn(string $l, string $file): string => SITE_PROD_ORIGIN . '/' . ($l === 'en' ? 'en/' : '') . ($file === 'index.php' ? '' : $file);

// every check on the overview → one score (each page: title, description, keyword, English; site: FAQ, Search Console, business data)
$pageChecks = [];
foreach ($pages as $slug => $p) {
    $r = $pageRows[$slug] ?? [];
    $tl = $len($r['title_th'] ?? '');
    $dl = $len($r['meta_desc_th'] ?? '');
    $kw = $settings[seo_kw_key($slug, 'th')] ?? '';
    $tr = page_translated($slug);
    $pageChecks[$slug] = [
        ['ชื่อหน้า', 'Title tag', $rate($tl, SEO_TITLE_RANGE), "$tl ตัวอักษร · แนะนำ " . implode('–', SEO_TITLE_RANGE)],
        ['คำอธิบาย', 'Description', $rate($dl, SEO_DESC_RANGE), "$dl ตัวอักษร · แนะนำ " . implode('–', SEO_DESC_RANGE)],
        ['คำค้นหาหลัก', 'Keyword', $kw !== '' ? 'ok' : 'warn', $kw !== '' ? $kw : 'ยังไม่ตั้ง'],
        ['ภาษาอังกฤษ', 'hreflang', $tr ? 'ok' : 'warn', $tr ? 'ครบ — Google เห็นทั้งสองภาษา' : 'ยังแปลไม่ครบ'],
        ['รูปตอนแชร์', 'Open Graph', 'ok', ($r['og_path'] ?? '') !== '' ? 'รูปเฉพาะหน้านี้' : 'ใช้รูปหลักของเว็บ'],
    ];
}
$siteChecks = [
    ['คำถาม-คำตอบ', 'FAQPage', count($faq) >= 3 && count($faqEn) === count($faq) ? 'ok' : 'warn', count($faq) . ' คำถาม · อังกฤษ ' . count($faqEn) . '/' . count($faq)],
    ['Google Search Console', 'Verification', $setting('verify_google') !== '' ? 'ok' : 'warn', $setting('verify_google') !== '' ? 'ใส่โค้ดยืนยันแล้ว' : 'ยังไม่ได้เชื่อม'],
    ['ข้อมูลธุรกิจ', 'Schema', $setting('org_alt_names') !== '' && $setting('addr_postal') !== '' ? 'ok' : 'warn', $setting('geo_lat') !== '' ? 'ครบ รวมพิกัด' : 'ครบ (ยังไม่มีพิกัดแผนที่)'],
];
$all = array_merge(array_merge(...array_values($pageChecks)), $siteChecks);
$passed = count(array_filter($all, static fn($c) => $c[2] === 'ok'));
$score = $all === [] ? 0 : (int) round($passed * 100 / count($all));
$scoreState = $score >= 80 ? 'ok' : ($score >= 50 ? 'warn' : 'bad');

$tabsMeta = [
    'overview' => ['ภาพรวม', 'Overview', 'chart'],
    'meta'     => ['ชื่อหน้า & คำอธิบาย', 'Title tag & Description', 'text'],
    'share'    => ['รูปตอนแชร์ลิงก์', 'Open Graph', 'image'],
    'business' => ['ข้อมูลธุรกิจ', 'Schema', 'factory'],
    'faq'      => ['คำถาม-คำตอบ', 'AEO · FAQPage', 'chat'],
    'console'  => ['เชื่อมต่อ Google', 'Google Search Console', 'link'],
];
$pill = static fn(string $state): string => '<span class="pill pill-' . $state . '">' . (['ok' => 'ดี', 'warn' => 'ควรปรับ', 'bad' => 'ยังไม่มี'][$state] ?? $state) . '</span>';

admin_page_start('SEO & AEO', 'seo.php');
?>
<div class="seo-page">
<header class="page-head">
  <span class="page-head-ic"><?= admin_icon('search') ?></span>
  <div>
    <h1>SEO &amp; AEO</h1>
    <p class="muted">สิ่งที่ Google และ AI (Google AI Overview, ChatGPT) อ่านจากเว็บ — ชื่อหน้า คำอธิบาย รูปตอนแชร์ ข้อมูลธุรกิจ และคำถาม-คำตอบ</p>
  </div>
<?php if (!is_production_host()): ?>
  <span class="env-chip" title="ค่าที่แก้ที่นี่ใช้กับเว็บจริงเมื่อขึ้น www.lyindustries.com ด้วยฐานข้อมูลเดียวกัน"><span class="dot dot-warn"></span>เว็บทดสอบ · Google ไม่เก็บ</span>
<?php endif; ?>
</header>

<nav class="seo-tabs" aria-label="หัวข้อ SEO">
<?php foreach ($tabsMeta as $k => [$th, $en, $icon]): ?>
  <a href="?tab=<?= e($k) ?>"<?= $k === $tab ? ' class="on" aria-current="page"' : '' ?>><?= admin_icon($icon) ?><span><b><?= e($th) ?></b><small><?= e($en) ?></small></span></a>
<?php endforeach; ?>
</nav>
<?php if ($errors !== []): ?><div class="flash flash-error">ยังไม่ได้บันทึก — มีช่องที่ต้องแก้ <?= count($errors) ?> ช่อง</div><?php endif; ?>

<?php if ($tab === 'overview'): ?>
<div class="score-row">
  <section class="card score-card">
    <div class="ring ring-<?= $scoreState ?>" style="--p:<?= $score ?>"><b><?= $score ?></b><small>/ 100</small></div>
    <div>
      <h2>คะแนน SEO &amp; AEO</h2>
      <p>ผ่าน <b><?= $passed ?></b> จาก <?= count($all) ?> ข้อ</p>
      <p class="muted small">นับจากชื่อหน้า คำอธิบาย คำค้นหาหลัก ภาษาอังกฤษของทุกหน้า + FAQ, Search Console และข้อมูลธุรกิจ</p>
    </div>
  </section>
<?php foreach ($siteChecks as $i => [$th, $en, $state, $detail]): $href = ['?tab=faq', '?tab=console', '?tab=business'][$i]; ?>
  <a class="card status-tile" href="<?= e($href) ?>">
    <span class="status-top"><span class="status-name"><?= e($th) ?><small><?= e($en) ?></small></span><?= $pill($state) ?></span>
    <span class="status-detail"><?= e($detail) ?></span>
    <span class="status-go">ดูรายละเอียด →</span>
  </a>
<?php endforeach; ?>
</div>

<h2 class="section-title">ตรวจแต่ละหน้า</h2>
<div class="page-cards">
<?php foreach ($pages as $slug => $p): $file = SITE_PAGE_FILES[$slug]; $ok = count(array_filter($pageChecks[$slug], static fn($c) => $c[2] === 'ok')); ?>
  <section class="card page-card">
    <header>
      <span class="page-card-ic"><?= admin_icon($p['icon']) ?></span>
      <span class="page-card-name"><b><?= e($p['label']) ?></b><small><?= e(preg_replace('#^https://#', '', $prodUrl('th', $file))) ?></small></span>
      <span class="page-card-score"><?= $ok ?>/<?= count($pageChecks[$slug]) ?></span>
    </header>
    <ul class="checks">
<?php foreach ($pageChecks[$slug] as [$th, $en, $state, $detail]): ?>
      <li><span class="dot dot-<?= $state ?>"></span><span class="check-name"><?= e($th) ?> <small>(<?= e($en) ?>)</small></span><span class="check-detail"><?= e($detail) ?></span></li>
<?php endforeach; ?>
    </ul>
    <footer>
      <a class="btn btn-sm" href="?tab=meta#page-<?= e($slug) ?>">แก้ชื่อหน้า &amp; คำอธิบาย</a>
      <a class="btn btn-sm btn-ghost" href="https://search.google.com/test/rich-results?url=<?= e(rawurlencode($prodUrl('th', $file))) ?>" target="_blank" rel="noopener">Rich Results ↗</a>
      <a class="btn btn-sm btn-ghost" href="https://pagespeed.web.dev/analysis?url=<?= e(rawurlencode($prodUrl('th', $file))) ?>" target="_blank" rel="noopener">ความเร็ว ↗</a>
    </footer>
  </section>
<?php endforeach; ?>
</div>
<p class="small muted">ปุ่ม Rich Results / ความเร็ว ทดสอบกับเว็บจริง www.lyindustries.com (ใช้ได้หลัง launch)</p>

<section class="card files-card">
  <h2>ไฟล์สำหรับ search engine <small class="muted">(XML Sitemap / Robots.txt)</small></h2>
  <div class="files-grid">
    <div><a class="file-link" href="../sitemap.xml" target="_blank" rel="noopener"><?= admin_icon('list') ?> sitemap.xml ↗</a><p class="muted small">รายการหน้าเว็บทั้งหมด อัปเดตเองทุกครั้งที่แก้ — ไม่ต้องตั้งรอบหรือกดปุ่ม · หน้าอังกฤษใส่เมื่อแปลครบ</p></div>
    <div><a class="file-link" href="../robots.txt" target="_blank" rel="noopener"><?= admin_icon('shield') ?> robots.txt ↗</a><p class="muted small"><?= is_production_host() ? 'เปิดให้ search engine เก็บ' : 'เว็บทดสอบ: ปิดไม่ให้เก็บทั้งเว็บ' ?> · ระบบสร้างให้ ไม่เปิดให้แก้เอง (พิมพ์ผิดบรรทัดเดียวเว็บหายจาก Google ได้)</p></div>
  </div>
</section>

<?php elseif ($tab === 'meta'): ?>
<p class="tab-intro">ชื่อหน้า (Title tag) = หัวข้อสีน้ำเงินในผลค้นหาและชื่อแท็บเบราว์เซอร์ · คำอธิบาย (Description) = ข้อความสีเทาใต้หัวข้อ · คำค้นหาหลัก (Keyword) = ใช้ตรวจเนื้อหาในหลังบ้านเท่านั้น ไม่ใส่ลงหน้าเว็บ · ภาษาอังกฤษว่าง = ใช้ภาษาไทย</p>
<nav class="jump">
<?php foreach ($pages as $slug => $p): if (!$canMeta($slug, 'en')) { continue; } ?>
  <a href="#page-<?= e($slug) ?>"><?= admin_icon($p['icon']) ?><?= e($p['label']) ?></a>
<?php endforeach; ?>
</nav>
<form method="post" class="form" data-seo-form>
  <?= csrf_field() ?>
  <input type="hidden" name="tab" value="meta">
<?php foreach ($pages as $slug => $p): $r = $pageRows[$slug] ?? []; if (!$canMeta($slug, 'en')) { continue; } $file = SITE_PAGE_FILES[$slug]; ?>
  <fieldset class="card seo-card" id="page-<?= e($slug) ?>">
    <legend><span class="legend-ic"><?= admin_icon($p['icon']) ?></span><?= e($p['label']) ?> <a class="legend-link" href="../<?= e($file === 'index.php' ? '' : $file) ?>" target="_blank" rel="noopener">ดูหน้าเว็บ ↗</a></legend>
    <div class="seo-langs">
<?php foreach (['th' => 'ภาษาไทย', 'en' => 'English'] as $l => $lname): $ro = !$canMeta($slug, $l); ?>
      <div class="seo-lang" data-serp-group>
        <h3><span class="lang-badge"><?= strtoupper($l) ?></span><?= e($lname) ?><?= $ro ? ' <span class="chip chip-dim">อ่านอย่างเดียว</span>' : '' ?></h3>
        <span class="serp-label">ตัวอย่างบน Google (Preview)</span>
        <div class="serp">
          <span class="serp-site"><img src="../assets/img/brand/logo-lyi.svg" alt="" width="18" height="18"><span>L.Y. Industries<small><?= e(preg_replace('#^https://#', '', $prodUrl($l, $file))) ?></small></span></span>
          <span class="serp-title" data-serp-title></span>
          <span class="serp-desc" data-serp-desc></span>
        </div>
<?php foreach (['title' => ['ชื่อหน้า (Title tag)', 200, 2], 'meta_desc' => ['คำอธิบาย (Description)', 400, 4]] as $f => [$fl, $max, $rows]): $col = "{$f}_$l"; ?>
        <label class="field<?= isset($errors["$slug.$col"]) ? ' has-error' : '' ?>"><span><?= e($fl) ?></span>
          <textarea name="seo[<?= e($slug) ?>][<?= $col ?>]" rows="<?= $rows ?>" maxlength="<?= $max ?>" data-count data-range="<?= e(implode('-', $f === 'title' ? SEO_TITLE_RANGE : SEO_DESC_RANGE)) ?>"
            <?= $f === 'title' ? 'data-serp-title-src' : 'data-serp-desc-src' ?><?= $ro ? ' readonly' : '' ?><?= $l === 'en' ? ' placeholder="(ว่าง = ใช้ภาษาไทย)"' : '' ?>><?= e((string) ($_POST['seo'][$slug][$col] ?? $r[$col] ?? '')) ?></textarea>
<?php if (isset($errors["$slug.$col"])): ?><small class="err"><?= e($errors["$slug.$col"]) ?></small><?php endif; ?>
        </label>
<?php endforeach; ?>
<?php $kwKey = seo_kw_key($slug, $l); $kwPage = '../' . ($l === 'en' ? 'en/' : '') . ($file === 'index.php' ? '' : $file); ?>
        <label class="field<?= isset($errors["kw.$slug.$l"]) ? ' has-error' : '' ?>"><span>คำค้นหาหลัก (Keyword)</span>
          <input name="seo_kw[<?= e($slug) ?>][<?= $l ?>]" maxlength="<?= SEO_KW_MAX ?>" value="<?= e((string) ($_POST['seo_kw'][$slug][$l] ?? $settings[$kwKey] ?? '')) ?>"
            data-kw data-kw-page="<?= e($kwPage) ?>" placeholder="<?= $l === 'en' ? 'e.g. elastic tape manufacturer' : 'เช่น โรงงานผลิตยางยืด' ?>"<?= $ro ? ' readonly' : '' ?>>
<?php if (isset($errors["kw.$slug.$l"])): ?><small class="err"><?= e($errors["kw.$slug.$l"]) ?></small><?php else: ?><small class="muted">คำที่อยากให้ลูกค้าค้นแล้วเจอหน้านี้ — ระบบตรวจว่ามีคำนี้ในจุดสำคัญครบหรือยัง</small><?php endif; ?>
          <ul class="kw-check" data-kw-check hidden></ul>
        </label>
      </div>
<?php endforeach; ?>
    </div>
  </fieldset>
<?php endforeach; ?>
  <div class="actions sticky"><button class="btn btn-primary" type="submit">บันทึก</button></div>
</form>

<?php elseif ($tab === 'share'): ?>
<p class="tab-intro">รูปที่ขึ้นเมื่อมีคนแชร์ลิงก์เว็บใน LINE, Facebook, Messenger ฯลฯ · อัปโหลดแล้วระบบตัดเป็น 1200×630 ให้อัตโนมัติ (ส่วนกลางของรูป)</p>
<?php if (!$canSettings): ?><div class="flash flash-info">เปลี่ยนรูปได้เฉพาะผู้มีสิทธิ์ "ตั้งค่าเว็บ"</div><?php endif; ?>
<form method="post" class="form" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="tab" value="share">
  <fieldset class="card seo-card"<?= $canSettings ? '' : ' disabled' ?>>
    <legend><span class="legend-ic"><?= admin_icon('image') ?></span>รูปหลักของเว็บ <small class="muted">ใช้ทุกหน้าที่ไม่ได้ตั้งรูปเอง</small></legend>
    <div class="og-main og-row">
      <div class="chat-mock">
        <span class="chat-bubble">ลองดูเว็บนี้ครับ lyindustries.com</span>
        <div class="og-card"><img src="../<?= e(asset(og_image_path(['og_image' => '']))) ?>" alt="" data-og-preview><span class="og-host">lyindustries.com</span><b><?= e(page_meta('home', 'th')['title']) ?></b></div>
      </div>
      <div class="og-help" data-img-field>
        <h3>เปลี่ยนรูปหลัก</h3>
        <ul class="tips">
          <li>รูปแนวนอน กว้างอย่างน้อย 1200px</li>
          <li>โลโก้/ข้อความไว้กลางภาพ — ขอบบน-ล่างอาจถูกตัด</li>
          <li>ไฟล์ JPG, PNG หรือ WebP</li>
        </ul>
        <label class="btn"><input type="file" name="og[site]" accept="image/jpeg,image/png,image/webp" hidden data-og-input><?= admin_icon('image') ?> เลือกรูปใหม่…</label>
<?php if (($settings['og_image'] ?? SITE_OG_IMAGE) !== SITE_OG_IMAGE): ?>
        <label class="check small"><input type="checkbox" name="og_clear[site]" value="1"> กลับไปใช้รูปเริ่มต้น</label>
<?php endif; ?>
<?php if (isset($errors['og.site'])): ?><small class="err"><?= e($errors['og.site']) ?></small><?php endif; ?>
      </div>
    </div>
  </fieldset>
  <fieldset class="card seo-card"<?= $canSettings ? '' : ' disabled' ?>>
    <legend><span class="legend-ic"><?= admin_icon('grid') ?></span>รูปเฉพาะหน้า <small class="muted">ไม่บังคับ</small></legend>
    <div class="og-grid">
<?php foreach ($pages as $slug => $p): $own = (string) ($pageRows[$slug]['og_path'] ?? ''); ?>
      <div class="og-tile og-row" data-img-field>
        <div class="og-card og-small"><img src="../<?= e(asset($own !== '' && content_local_image($own) !== '' ? $own : og_image_path(['og_image' => '']))) ?>" alt="" data-og-preview><b><?= e($p['label']) ?></b></div>
        <span class="og-state"><?= $own !== '' ? '<span class="pill pill-ok">รูปเฉพาะหน้านี้</span>' : '<span class="pill pill-dim">ใช้รูปหลักของเว็บ</span>' ?></span>
        <label class="btn btn-sm"><input type="file" name="og[<?= e($slug) ?>]" accept="image/jpeg,image/png,image/webp" hidden data-og-input>เลือกรูป…</label>
<?php if ($own !== ''): ?><label class="check small"><input type="checkbox" name="og_clear[<?= e($slug) ?>]" value="1"> ใช้รูปหลักแทน</label><?php endif; ?>
<?php if (isset($errors["og.$slug"])): ?><small class="err"><?= e($errors["og.$slug"]) ?></small><?php endif; ?>
      </div>
<?php endforeach; ?>
    </div>
  </fieldset>
<?php if ($canSettings): ?><div class="actions sticky"><button class="btn btn-primary" type="submit">บันทึก</button></div><?php endif; ?>
</form>

<?php elseif ($tab === 'business'): ?>
<p class="tab-intro">ข้อมูลที่ฝังในหน้าเว็บให้ Google และ AI รู้จักบริษัท (ไม่แสดงบนหน้า) — ชื่อ ที่อยู่ เบอร์ อีเมล เวลาทำการ ใช้จาก <a href="settings.php">ข้อมูลติดต่อ &amp; ลิงก์</a> อยู่แล้ว</p>
<?php
$groups = [
    ['factory', 'ชื่อและประวัติ', [
        'org_alt_names' => ['ชื่ออื่นของบริษัท', 'คั่นด้วยจุลภาค — ช่วยให้ค้นด้วยชื่อย่อแล้วเจอ', 'wide'],
        'org_founding'  => ['ปีที่ก่อตั้ง (ค.ศ.)', '', ''],
    ]],
    ['home', 'ที่อยู่ (ภาษาอังกฤษ สำหรับ Google)', [
        'addr_locality' => ['เขต / อำเภอ', 'ถนนและเลขที่ใช้ "ที่อยู่ บรรทัด 1 (English)" ในหน้าข้อมูลติดต่อ', ''],
        'addr_region'   => ['จังหวัด', '', ''],
        'addr_postal'   => ['รหัสไปรษณีย์', '', ''],
        'addr_country'  => ['รหัสประเทศ', 'เช่น TH', ''],
        'geo_lat'       => ['ละติจูด (ไม่บังคับ)', 'Google Maps: คลิกขวาที่โรงงาน → ตัวเลขแรก', ''],
        'geo_lng'       => ['ลองจิจูด (ไม่บังคับ)', 'ตัวเลขที่สอง', ''],
    ]],
    ['users', 'โซเชียลมีเดียของบริษัท (ไม่บังคับ)', [
        'social_facebook'  => ['Facebook', 'https://www.facebook.com/…', 'wide'],
        'social_instagram' => ['Instagram', 'https://www.instagram.com/…', 'wide'],
        'social_linkedin'  => ['LinkedIn', 'https://www.linkedin.com/company/…', 'wide'],
        'social_youtube'   => ['YouTube', 'https://www.youtube.com/@…', 'wide'],
        'social_tiktok'    => ['TikTok', 'https://www.tiktok.com/@…', 'wide'],
    ]],
];
$val = static fn(string $k): string => (string) ($_POST['s'][$k] ?? $setting($k));
?>
<div class="biz-layout">
<form method="post" class="form" data-biz-form>
  <?= csrf_field() ?>
  <input type="hidden" name="tab" value="business">
<?php foreach ($groups as [$icon, $gname, $fields]): ?>
  <fieldset class="card seo-card"<?= $canSettings ? '' : ' disabled' ?>>
    <legend><span class="legend-ic"><?= admin_icon($icon) ?></span><?= e($gname) ?></legend>
    <div class="field-grid">
<?php foreach ($fields as $k => [$fl, $help, $wide]): $social = str_starts_with($k, 'social_'); ?>
      <label class="field<?= $wide ? ' span-2' : '' ?><?= isset($errors["s.$k"]) ? ' has-error' : '' ?>"><span><?= e($fl) ?></span>
        <input name="s[<?= e($k) ?>]" value="<?= e($val($k)) ?>" data-bind="<?= e($k) ?>"<?= $social ? ' type="url" placeholder="' . e($help) . '"' : '' ?>>
<?php if (isset($errors["s.$k"])): ?><small class="err"><?= e($errors["s.$k"]) ?></small><?php elseif ($help !== '' && !$social): ?><small class="muted"><?= e($help) ?></small><?php endif; ?>
      </label>
<?php endforeach; ?>
    </div>
  </fieldset>
<?php endforeach; ?>
<?php if ($canSettings): ?><div class="actions sticky"><button class="btn btn-primary" type="submit">บันทึก</button></div><?php endif; ?>
</form>
<aside class="card biz-preview" aria-label="ตัวอย่างข้อมูลที่ Google เห็น">
  <span class="serp-label">Google รู้จักบริษัทนี้ว่า</span>
  <h3><?= e(site('company_en')) ?></h3>
  <p class="biz-alt" data-bind-text="org_alt_names"><?= e($val('org_alt_names')) ?></p>
  <dl>
    <dt>ก่อตั้ง</dt><dd data-bind-text="org_founding"><?= e($val('org_founding')) ?></dd>
    <dt>ที่อยู่</dt><dd><?= e(site('address1_en')) ?>, <span data-bind-text="addr_locality"><?= e($val('addr_locality')) ?></span>, <span data-bind-text="addr_region"><?= e($val('addr_region')) ?></span> <span data-bind-text="addr_postal"><?= e($val('addr_postal')) ?></span>, <span data-bind-text="addr_country"><?= e($val('addr_country')) ?></span></dd>
    <dt>โทร</dt><dd><?= e(phone_schema(site('phone'))) ?></dd>
    <dt>อีเมล</dt><dd><?= e(site('email')) ?></dd>
    <dt>โปรไฟล์</dt><dd class="biz-links"><span class="chip">LINE</span><?php foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'] as $k => $name): ?><span class="chip" data-bind-show="social_<?= $k ?>"<?= $val("social_$k") === '' ? ' hidden' : '' ?>><?= e($name) ?></span><?php endforeach; ?></dd>
  </dl>
  <p class="muted small">LINE ใส่ให้อัตโนมัติจากหน้าข้อมูลติดต่อ · ตัวอย่างนี้อัปเดตตามที่พิมพ์</p>
</aside>
</div>

<?php elseif ($tab === 'console'): ?>
<p class="tab-intro">เชื่อมเว็บกับ Google Search Console (และ Bing) เพื่อดูว่าคนค้นคำไหนแล้วเจอเว็บเรา อันดับเท่าไหร่ หน้าไหนมีปัญหา และส่ง sitemap ให้ Google เก็บหน้าใหม่เร็วขึ้น</p>
<?php if (!$canSettings): ?><div class="flash flash-info">แก้ได้เฉพาะผู้มีสิทธิ์ "ตั้งค่าเว็บ"</div><?php endif; ?>
<div class="console-status">
<?php foreach (['verify_google' => 'Google Search Console', 'verify_bing' => 'Bing Webmaster Tools'] as $k => $name): $on = $setting($k) !== ''; ?>
  <div class="card status-tile"><span class="status-top"><span class="status-name"><?= e($name) ?></span><span class="pill pill-<?= $on ? 'ok' : ($k === 'verify_bing' ? 'dim' : 'warn') ?>"><?= $on ? 'ใส่โค้ดแล้ว' : ($k === 'verify_bing' ? 'ไม่บังคับ' : 'ยังไม่เชื่อม') ?></span></span></div>
<?php endforeach; ?>
</div>
<div class="grid2 console-grid">
  <section class="card">
    <h2>ขั้นตอน</h2>
    <ol class="steps">
      <li><b>เพิ่มเว็บใน Search Console</b><span>เปิด <a href="https://search.google.com/search-console" target="_blank" rel="noopener">Google Search Console ↗</a> → เพิ่ม property แบบ <b>URL prefix</b> ใส่ <code>https://www.lyindustries.com/</code></span></li>
      <li><b>คัดลอกโค้ดยืนยัน</b><span>เลือกวิธี <b>HTML tag</b> → คัดลอก meta tag มาวางในช่องด้านขวา → บันทึก</span></li>
      <li><b>กด Verify</b><span>กลับไปกด Verify ใน Search Console <span class="muted">(ใช้ได้หลังเว็บใหม่ขึ้น www.lyindustries.com แล้ว)</span></span></li>
      <li><b>ส่ง sitemap</b><span>เมนู Sitemaps → ใส่ <code>sitemap.xml</code> → Submit</span></li>
    </ol>
    <p class="small muted">Bing: ที่ <a href="https://www.bing.com/webmasters" target="_blank" rel="noopener">Bing Webmaster Tools ↗</a> เลือก "Import from Google Search Console" ได้เลย ไม่ต้องใส่โค้ด · ถ้าฝ่าย IT ยืนยันด้วย DNS (property แบบ Domain) ไม่ต้องใส่โค้ดในหน้านี้</p>
  </section>
  <div>
    <form method="post" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="tab" value="console">
      <fieldset class="card seo-card"<?= $canSettings ? '' : ' disabled' ?>>
        <legend><span class="legend-ic"><?= admin_icon('shield') ?></span>ยืนยันความเป็นเจ้าของเว็บ <small class="muted">(Meta Tag Code Settings)</small></legend>
<?php foreach (['verify_google' => ['Google Search Console', 'google-site-verification'], 'verify_bing' => ['Bing Webmaster Tools (ไม่บังคับ)', 'msvalidate.01']] as $k => [$fl, $metaName]): ?>
        <label class="field<?= isset($errors["s.$k"]) ? ' has-error' : '' ?>"><span><?= e($fl) ?></span>
          <textarea name="s[<?= e($k) ?>]" rows="2" placeholder="<meta name=&quot;<?= e($metaName) ?>&quot; content=&quot;…&quot; />"><?= e((string) ($_POST['s'][$k] ?? $setting($k))) ?></textarea>
<?php if (isset($errors["s.$k"])): ?><small class="err"><?= e($errors["s.$k"]) ?></small><?php else: ?><small class="muted">วาง meta tag ทั้งบรรทัดหรือเฉพาะรหัสก็ได้ — ใส่ในหน้าแรกของ www.lyindustries.com เท่านั้น</small><?php endif; ?>
        </label>
<?php endforeach; ?>
<?php if ($canSettings): ?><div class="actions"><button class="btn btn-primary" type="submit">บันทึก</button></div><?php endif; ?>
      </fieldset>
    </form>
    <section class="card">
      <h2>ที่อยู่ sitemap <small class="muted">(Your Sitemap URL)</small></h2>
      <div class="copy-row"><code data-copy-src><?= e(SITE_PROD_ORIGIN) ?>/sitemap.xml</code><button class="btn btn-sm" type="button" data-copy>คัดลอก</button></div>
      <p class="small muted">อัปเดตอัตโนมัติ (Automatic Sitemap Update) ทุกครั้งที่แก้เนื้อหา — ไม่ต้องตั้งรอบรายสัปดาห์/รายเดือน และไม่ต้องกดสร้างใหม่</p>
      <h3>robots.txt <small class="muted">(Robots.txt)</small></h3>
      <p class="small muted">ระบบสร้างให้: เว็บจริงเปิดให้ทุก search engine เก็บและบอกที่อยู่ sitemap · เว็บทดสอบปิดทั้งหมด — <a href="../robots.txt" target="_blank" rel="noopener">ดูไฟล์ ↗</a></p>
    </section>
  </div>
</div>

<?php else: /* faq */ ?>
<section class="card faq-hero">
  <span class="page-head-ic"><?= admin_icon('chat') ?></span>
  <div>
    <h2>คำถาม-คำตอบที่ Google และ AI อ่าน</h2>
    <p class="muted">ฝังในหน้าแรกเป็นข้อมูลแบบ FAQPage — เวลามีคนถามเรื่องที่ตรงกับคำถามเหล่านี้ Google/AI อาจยกคำตอบของเราไปแสดง พร้อมลิงก์กลับมาที่เว็บ</p>
  </div>
  <a class="btn btn-primary" href="lists.php?list=home.faq">แก้คำถาม-คำตอบ →</a>
</section>
<div class="grid2">
<?php foreach (['th' => 'ภาษาไทย', 'en' => 'English'] as $l => $lname): $list = content_list('home.faq', $l); ?>
  <section class="card">
    <h2><span class="lang-badge"><?= strtoupper($l) ?></span><?= e($lname) ?> <small class="muted">· <?= count($list) ?> คำถาม</small></h2>
    <span class="serp-label">ตัวอย่างแบบ "คำถามที่คนอื่นถาม" บน Google</span>
    <div class="paa">
<?php foreach ($list as $i => $f): $same = $l === 'en' && $f['q'] === ($faq[$i]['q'] ?? null); ?>
      <details class="faq-preview"<?= $i === 0 ? ' open' : '' ?>>
        <summary><?= $same ? '<span class="chip chip-dim">ยังไม่แปล</span> ' : '' ?><?= e($f['q']) ?></summary>
        <p><?= e($f['a']) ?></p>
      </details>
<?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>
</div>
<section class="card">
  <h2>เขียนคำถาม-คำตอบให้ AI หยิบไปใช้</h2>
  <div class="tip-grid">
    <div><b>ใช้คำแบบที่ลูกค้าพิมพ์</b><span>"สั่งผลิตขั้นต่ำเท่าไหร่" ดีกว่า "เงื่อนไขการสั่งซื้อ"</span></div>
    <div><b>ตอบตรงในประโยคแรก</b><span>มีตัวเลข/ข้อเท็จจริงก่อน แล้วค่อยขยายความ</span></div>
    <div><b>1 คำถาม = 1 เรื่อง</b><span>คำตอบยาวพอดี 2–4 ประโยค</span></div>
    <div><b>คำถามที่ควรมีเพิ่ม</b><span>ส่งออกต่างประเทศไหม, รับงานแบรนด์เล็กไหม, มาตรฐานที่ผ่าน (OEKO-TEX/RSL), ขอตัวอย่างฟรีไหม</span></div>
  </div>
</section>
<?php endif; ?>
</div>
<?php
admin_page_end();
