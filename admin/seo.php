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
 */

$user = admin_require();
$pages = array_intersect_key(admin_content_pages(), SITE_PAGE_FILES);
$canMeta = static fn(string $slug, string $l): bool => can_content($l, $slug, 'seo');
$anyMeta = (bool) array_filter(array_keys($pages), static fn($s) => $canMeta($s, 'en'));
$canSettings = can('settings.edit');
if (!$anyMeta && !$canSettings) {
    admin_require('settings.edit');   // shows the "no permission" page
}

$tabs = ['overview' => 'ภาพรวม', 'meta' => 'ชื่อหน้า & คำอธิบาย', 'share' => 'รูปตอนแชร์ลิงก์', 'business' => 'ข้อมูลธุรกิจสำหรับ Google', 'faq' => 'คำถาม-คำตอบ (AEO)'];
$tab = (string) ($_GET['tab'] ?? $_POST['tab'] ?? 'overview');
if (!isset($tabs[$tab])) {
    $tab = 'overview';
}
const SEO_TITLE_RANGE = [30, 60];   // what fits a Google result line
const SEO_DESC_RANGE = [70, 160];

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
        }
        if ($errors === []) {
            db_transaction(static function () use ($todo, $pageRows, $uid, &$changes): void {
                foreach ($todo as [$slug, $col, $v]) {
                    // $col comes from the fixed list above, never from the request
                    db_exec("UPDATE dbo.lyiweb_pages SET $col = ?, updated_at = GETDATE(), updated_by = ? WHERE slug = ?", [$v === '' ? null : $v, $uid, $slug]);
                    audit_log('update', 'lyiweb_pages', "$slug.$col", $pageRows[$slug][$col] ?? null, $v);
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
$label = ['ok' => 'ดี', 'warn' => 'ควรปรับ', 'bad' => 'ยังไม่มี'];

admin_page_start('SEO & AEO', 'seo.php');
?>
<h1>SEO &amp; AEO</h1>
<p class="muted">สิ่งที่ Google และ AI (เช่น Google AI Overview, ChatGPT) อ่านจากเว็บ — ชื่อหน้า คำอธิบาย รูปตอนแชร์ ข้อมูลธุรกิจ และคำถาม-คำตอบ</p>
<?php if (!is_production_host()): ?><div class="flash flash-info">นี่คือเว็บทดสอบ — ตั้งค่าไว้ไม่ให้ Google เก็บ (ปกติ) · ค่าที่แก้ที่นี่ใช้กับเว็บจริงเมื่อขึ้น www.lyindustries.com ด้วยฐานข้อมูลเดียวกัน</div><?php endif; ?>
<nav class="tabs">
<?php foreach ($tabs as $k => $t): ?>
  <a href="?tab=<?= e($k) ?>"<?= $k === $tab ? ' class="on" aria-current="page"' : '' ?>><span><?= e($t) ?></span></a>
<?php endforeach; ?>
</nav>
<?php if ($errors !== []): ?><div class="flash flash-error">ยังไม่ได้บันทึก — มีช่องที่ต้องแก้ <?= count($errors) ?> ช่อง</div><?php endif; ?>

<?php if ($tab === 'overview'): ?>
<section class="card">
  <h2>ตรวจแต่ละหน้า</h2>
  <table class="table rtable seo-check">
    <thead><tr><th>หน้า</th><th>ชื่อหน้า</th><th>คำอธิบาย</th><th>อังกฤษ</th><th>รูปตอนแชร์</th><th>ทดสอบกับ Google</th></tr></thead>
    <tbody>
<?php foreach ($pages as $slug => $p): $r = $pageRows[$slug] ?? []; $tl = $len($r['title_th'] ?? ''); $dl = $len($r['meta_desc_th'] ?? '');
      $tr = page_translated($slug); $file = SITE_PAGE_FILES[$slug]; ?>
      <tr>
        <td><b><?= e($p['label']) ?></b></td>
        <td data-label="ชื่อหน้า"><span class="dot dot-<?= $rate($tl, SEO_TITLE_RANGE) ?>"></span><?= $tl ?> ตัวอักษร</td>
        <td data-label="คำอธิบาย"><span class="dot dot-<?= $rate($dl, SEO_DESC_RANGE) ?>"></span><?= $dl ?> ตัวอักษร</td>
        <td data-label="อังกฤษ"><span class="dot dot-<?= $tr ? 'ok' : 'warn' ?>"></span><?= $tr ? 'ครบ — Google เห็นทั้งสองภาษา' : 'ยังแปลไม่ครบ (Google ยังไม่เก็บหน้าอังกฤษ)' ?></td>
        <td data-label="รูปตอนแชร์"><span class="dot dot-ok"></span><?= ($r['og_path'] ?? '') !== '' ? 'รูปเฉพาะหน้านี้' : 'รูปหลักของเว็บ' ?></td>
        <td class="small act"><a href="https://search.google.com/test/rich-results?url=<?= e(rawurlencode($prodUrl('th', $file))) ?>" target="_blank" rel="noopener">Rich Results ↗</a> · <a href="https://pagespeed.web.dev/analysis?url=<?= e(rawurlencode($prodUrl('th', $file))) ?>" target="_blank" rel="noopener">ความเร็ว ↗</a></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
  <p class="small muted">ชื่อหน้าแนะนำ <?= SEO_TITLE_RANGE[0] ?>–<?= SEO_TITLE_RANGE[1] ?> ตัวอักษร · คำอธิบาย <?= SEO_DESC_RANGE[0] ?>–<?= SEO_DESC_RANGE[1] ?> ตัวอักษร (ยาวกว่านี้ Google ตัดท้าย) · ปุ่มทดสอบกับ Google ใช้กับเว็บจริง www.lyindustries.com (ใช้ได้หลัง launch)</p>
</section>
<div class="grid2">
  <section class="card">
    <h2>คำถาม-คำตอบ (AEO)</h2>
    <p><span class="dot dot-<?= count($faq) >= 3 ? 'ok' : 'warn' ?>"></span><?= count($faq) ?> คำถามบนหน้าแรก · ภาษาอังกฤษ <?= count($faqEn) ?>/<?= count($faq) ?></p>
    <p class="small muted">Google และ AI ใช้คำถาม-คำตอบนี้ตอบผู้ค้นหาโดยตรง ยิ่งตอบคำถามที่ลูกค้าถามจริงได้ชัด ยิ่งมีโอกาสถูกนำไปแสดง</p>
    <a class="btn btn-sm" href="?tab=faq">ดูรายละเอียด →</a>
  </section>
  <section class="card">
    <h2>ไฟล์สำหรับ search engine</h2>
    <p><a href="../sitemap.xml" target="_blank" rel="noopener">sitemap.xml ↗</a> — รายการหน้าเว็บทั้งหมด (สร้างอัตโนมัติ · หน้าอังกฤษใส่เมื่อแปลครบ)</p>
    <p><a href="../robots.txt" target="_blank" rel="noopener">robots.txt ↗</a> — <?= is_production_host() ? 'เปิดให้ search engine เก็บ' : 'เว็บทดสอบ: ปิดไม่ให้เก็บทั้งเว็บ' ?></p>
    <p class="small muted">หลัง launch: ส่ง sitemap ใน Google Search Console → https://www.lyindustries.com/sitemap.xml</p>
  </section>
</div>

<?php elseif ($tab === 'meta'): ?>
<p class="muted">ชื่อหน้า = หัวข้อสีน้ำเงินในผลค้นหาและชื่อแท็บเบราว์เซอร์ · คำอธิบาย = ข้อความสีเทาใต้หัวข้อ · ภาษาอังกฤษว่าง = ใช้ภาษาไทย</p>
<form method="post" class="form" data-seo-form>
  <?= csrf_field() ?>
  <input type="hidden" name="tab" value="meta">
<?php foreach ($pages as $slug => $p): $r = $pageRows[$slug] ?? []; if (!$canMeta($slug, 'en')) { continue; } $file = SITE_PAGE_FILES[$slug]; ?>
  <fieldset class="card">
    <legend><?= admin_icon($p['icon']) ?> <?= e($p['label']) ?></legend>
    <div class="seo-langs">
<?php foreach (['th' => 'ภาษาไทย', 'en' => 'English'] as $l => $lname): $ro = !$canMeta($slug, $l); ?>
      <div class="seo-lang" data-serp-group>
        <h3><?= e($lname) ?></h3>
        <div class="serp">
          <span class="serp-site"><img src="../assets/img/brand/logo-lyi.svg" alt="" width="18" height="18"><span>L.Y. Industries<small><?= e(preg_replace('#^https://#', '', $prodUrl($l, $file))) ?></small></span></span>
          <span class="serp-title" data-serp-title></span>
          <span class="serp-desc" data-serp-desc></span>
        </div>
<?php foreach (['title' => ['ชื่อหน้า', 200, 2], 'meta_desc' => ['คำอธิบาย', 400, 4]] as $f => [$fl, $max, $rows]): $col = "{$f}_$l"; ?>
        <label class="field<?= isset($errors["$slug.$col"]) ? ' has-error' : '' ?>"><span><?= e($fl) ?></span>
          <textarea name="seo[<?= e($slug) ?>][<?= $col ?>]" rows="<?= $rows ?>" maxlength="<?= $max ?>" data-count data-range="<?= e(implode('-', $f === 'title' ? SEO_TITLE_RANGE : SEO_DESC_RANGE)) ?>"
            <?= $f === 'title' ? 'data-serp-title-src' : 'data-serp-desc-src' ?><?= $ro ? ' readonly' : '' ?><?= $l === 'en' ? ' placeholder="(ว่าง = ใช้ภาษาไทย)"' : '' ?>><?= e((string) ($_POST['seo'][$slug][$col] ?? $r[$col] ?? '')) ?></textarea>
<?php if (isset($errors["$slug.$col"])): ?><small class="err"><?= e($errors["$slug.$col"]) ?></small><?php endif; ?>
        </label>
<?php endforeach; ?>
      </div>
<?php endforeach; ?>
    </div>
  </fieldset>
<?php endforeach; ?>
  <div class="actions sticky"><button class="btn btn-primary" type="submit">บันทึก</button></div>
</form>

<?php elseif ($tab === 'share'): ?>
<p class="muted">รูปที่ขึ้นเมื่อมีคนแชร์ลิงก์เว็บใน LINE, Facebook, Messenger ฯลฯ · อัปโหลดแล้วระบบตัดเป็น 1200×630 ให้อัตโนมัติ (ส่วนกลางของรูป)</p>
<?php if (!$canSettings): ?><div class="flash flash-info">เปลี่ยนรูปได้เฉพาะผู้มีสิทธิ์ "ตั้งค่าเว็บ"</div><?php endif; ?>
<form method="post" class="form" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="tab" value="share">
  <fieldset class="card"<?= $canSettings ? '' : ' disabled' ?>>
    <legend>รูปหลักของเว็บ (ใช้ทุกหน้าที่ไม่ได้ตั้งรูปเอง)</legend>
    <div class="og-row">
      <div class="og-card"><img src="../<?= e(asset(og_image_path(['og_image' => '']))) ?>" alt="" data-og-preview><span class="og-host">lyindustries.com</span><b><?= e(page_meta('home', 'th')['title']) ?></b></div>
      <div class="img-actions" data-img-field>
        <label class="btn btn-sm"><input type="file" name="og[site]" accept="image/jpeg,image/png,image/webp" hidden data-og-input>เลือกรูปใหม่…</label>
<?php if (($settings['og_image'] ?? SITE_OG_IMAGE) !== SITE_OG_IMAGE): ?>
        <label class="check small"><input type="checkbox" name="og_clear[site]" value="1"> กลับไปใช้รูปเริ่มต้น</label>
<?php endif; ?>
<?php if (isset($errors['og.site'])): ?><small class="err"><?= e($errors['og.site']) ?></small><?php endif; ?>
        <small class="muted">แนะนำรูปแนวนอน กว้างอย่างน้อย 1200px มีโลโก้/ข้อความอยู่กลางภาพ</small>
      </div>
    </div>
  </fieldset>
  <fieldset class="card"<?= $canSettings ? '' : ' disabled' ?>>
    <legend>รูปเฉพาะหน้า (ไม่บังคับ)</legend>
<?php foreach ($pages as $slug => $p): $own = (string) ($pageRows[$slug]['og_path'] ?? ''); ?>
    <div class="og-row og-row-page">
      <div class="og-card og-small"><img src="../<?= e(asset($own !== '' && content_local_image($own) !== '' ? $own : og_image_path(['og_image' => '']))) ?>" alt="" data-og-preview><b><?= e($p['label']) ?></b></div>
      <div class="img-actions" data-img-field>
        <span class="small muted"><?= $own !== '' ? 'ใช้รูปเฉพาะหน้านี้' : 'ใช้รูปหลักของเว็บ' ?></span>
        <label class="btn btn-sm"><input type="file" name="og[<?= e($slug) ?>]" accept="image/jpeg,image/png,image/webp" hidden data-og-input>เลือกรูปสำหรับหน้านี้…</label>
<?php if ($own !== ''): ?><label class="check small"><input type="checkbox" name="og_clear[<?= e($slug) ?>]" value="1"> ใช้รูปหลักของเว็บแทน</label><?php endif; ?>
<?php if (isset($errors["og.$slug"])): ?><small class="err"><?= e($errors["og.$slug"]) ?></small><?php endif; ?>
      </div>
    </div>
<?php endforeach; ?>
  </fieldset>
<?php if ($canSettings): ?><div class="actions sticky"><button class="btn btn-primary" type="submit">บันทึก</button></div><?php endif; ?>
</form>

<?php elseif ($tab === 'business'): ?>
<p class="muted">ข้อมูลที่ฝังในหน้าเว็บให้ Google และ AI รู้จักบริษัท (ไม่แสดงบนหน้า) — ชื่อ ที่อยู่ เบอร์ อีเมล เวลาทำการ ใช้จาก <a href="settings.php">ข้อมูลติดต่อ &amp; ลิงก์</a> อยู่แล้ว</p>
<form method="post" class="form">
  <?= csrf_field() ?>
  <input type="hidden" name="tab" value="business">
<?php
$groups = [
    'ชื่อและประวัติ' => [
        'org_alt_names' => ['ชื่ออื่นของบริษัท', 'คั่นด้วยจุลภาค — ช่วยให้ค้นด้วยชื่อย่อแล้วเจอ เช่น LY Industries, LYI'],
        'org_founding'  => ['ปีที่ก่อตั้ง (ค.ศ.)', ''],
    ],
    'ที่อยู่ (ภาษาอังกฤษ สำหรับ Google)' => [
        'addr_locality' => ['เขต / อำเภอ', 'เช่น Khlong Sam Wa — ถนนและเลขที่ใช้ "ที่อยู่ บรรทัด 1 (English)" ในหน้าข้อมูลติดต่อ'],
        'addr_region'   => ['จังหวัด', 'เช่น Bangkok'],
        'addr_postal'   => ['รหัสไปรษณีย์', ''],
        'addr_country'  => ['รหัสประเทศ', 'TH'],
        'geo_lat'       => ['ละติจูด (ไม่บังคับ)', 'จาก Google Maps: คลิกขวาที่ตำแหน่งโรงงาน → ตัวเลขแรก เช่น 13.8569'],
        'geo_lng'       => ['ลองจิจูด (ไม่บังคับ)', 'ตัวเลขที่สอง เช่น 100.7123'],
    ],
    'โซเชียลมีเดียของบริษัท (ไม่บังคับ)' => [
        'social_facebook'  => ['Facebook', 'https://www.facebook.com/…'],
        'social_instagram' => ['Instagram', 'https://www.instagram.com/…'],
        'social_linkedin'  => ['LinkedIn', 'https://www.linkedin.com/company/…'],
        'social_youtube'   => ['YouTube', 'https://www.youtube.com/@…'],
        'social_tiktok'    => ['TikTok', 'https://www.tiktok.com/@…'],
    ],
];
foreach ($groups as $gname => $fields): ?>
  <fieldset class="card"<?= $canSettings ? '' : ' disabled' ?>>
    <legend><?= e($gname) ?></legend>
<?php foreach ($fields as $k => [$fl, $help]): ?>
    <label class="field<?= isset($errors["s.$k"]) ? ' has-error' : '' ?>"><span><?= e($fl) ?></span>
      <input name="s[<?= e($k) ?>]" value="<?= e((string) ($_POST['s'][$k] ?? $setting($k))) ?>"<?= str_starts_with($k, 'social_') ? ' type="url" placeholder="' . e($help) . '"' : '' ?>>
<?php if (isset($errors["s.$k"])): ?><small class="err"><?= e($errors["s.$k"]) ?></small><?php elseif ($help !== '' && !str_starts_with($k, 'social_')): ?><small class="muted"><?= e($help) ?></small><?php endif; ?>
    </label>
<?php endforeach; ?>
  </fieldset>
<?php endforeach; ?>
  <p class="small muted">LINE (<?= e(site('line_url')) ?>) ใส่ให้อัตโนมัติจากหน้าข้อมูลติดต่อ</p>
<?php if ($canSettings): ?><div class="actions sticky"><button class="btn btn-primary" type="submit">บันทึก</button></div><?php endif; ?>
</form>

<?php else: /* faq */ ?>
<section class="card">
  <h2>คำถาม-คำตอบที่ Google และ AI อ่าน</h2>
  <p class="muted">ฝังในหน้าแรกเป็นข้อมูลแบบ FAQPage — เวลามีคนถามเรื่องที่ตรงกับคำถามเหล่านี้ Google/AI อาจยกคำตอบของเราไปแสดง พร้อมลิงก์กลับมาที่เว็บ</p>
  <div class="actions" style="justify-content:flex-start"><a class="btn btn-primary" href="lists.php?list=home.faq">แก้คำถาม-คำตอบ →</a></div>
</section>
<div class="grid2">
<?php foreach (['th' => 'ภาษาไทย', 'en' => 'English'] as $l => $lname): $list = content_list('home.faq', $l); ?>
  <section class="card">
    <h2><?= e($lname) ?> · <?= count($list) ?> คำถาม</h2>
<?php foreach ($list as $i => $f): $same = $l === 'en' && $f['q'] === ($faq[$i]['q'] ?? null); ?>
    <details class="faq-preview"<?= $i === 0 ? ' open' : '' ?>>
      <summary><?= $same ? '<span class="chip chip-dim">ยังไม่แปล</span> ' : '' ?><?= e($f['q']) ?></summary>
      <p><?= e($f['a']) ?></p>
    </details>
<?php endforeach; ?>
  </section>
<?php endforeach; ?>
</div>
<section class="card">
  <h2>เขียนคำถาม-คำตอบให้ AI หยิบไปใช้</h2>
  <ul class="tips">
    <li>เขียนคำถามแบบที่ลูกค้าพิมพ์ค้นจริง เช่น "สั่งผลิตขั้นต่ำเท่าไหร่" มากกว่า "เงื่อนไขการสั่งซื้อ"</li>
    <li>ประโยคแรกของคำตอบควรตอบตรงคำถามทันที (มีตัวเลข/ข้อเท็จจริง) แล้วค่อยขยายความ</li>
    <li>1 คำถาม = 1 เรื่อง · คำตอบยาวพอดี 2–4 ประโยค</li>
    <li>คำถามที่ควรมีเพิ่ม: ส่งออกต่างประเทศไหม, รับงานแบรนด์เล็กไหม, มาตรฐานที่ผ่าน (OEKO-TEX/RSL), ขอตัวอย่างฟรีไหม</li>
  </ul>
</section>
<?php endif; ?>
<?php
admin_page_end();
