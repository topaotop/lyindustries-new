<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';

admin_require('content.translate');
$canTh = can('content.edit');   // translators (content.translate only) may edit English only

$pages = [
    'home'    => ['หน้าแรก', 'index.php', 'home'],
    'about'   => ['เกี่ยวกับเรา', 'about.php', 'factory'],
    'catalog' => ['แคตตาล็อกสินค้า', 'catalog.php', 'tape'],
    'contact' => ['ติดต่อเรา', 'contact.php', 'mail'],
    'footer'  => ['Footer (แคตตาล็อก + ติดต่อ)', 'catalog.php#footer', 'footer'],
];
$sectionNames = [
    'home'    => ['hero' => 'ส่วนบนสุด (Hero)', 'trust' => 'แถบความน่าเชื่อถือ + ตัวเลข', 'story' => 'จุดใช้งานบนเสื้อผ้า', 'why' => 'ทำไมต้องเรา',
                  'process' => 'ขั้นตอนการผลิต (หัวข้อ)', 'specimens' => 'หมวดสินค้า (หัวข้อ/ปุ่ม)', 'rnd' => 'บริการ R&D', 'colorlab' => 'โรงย้อม & Color Lab',
                  'gallery' => 'ตัวอย่างสินค้า (หัวข้อ)', 'faq' => 'คำถามที่พบบ่อย (หัวข้อ)', 'contact' => 'ติดต่อ + footer หน้าแรก'],
    'about'   => ['hero' => 'ส่วนบนสุด', 'video' => 'วิดีโอ + ตัวเลข', 'story' => 'เรื่องราวบริษัท', 'facilities' => 'โรงงาน (Facilities)', 'cta' => 'ปุ่มท้ายหน้า + footer'],
    'catalog' => ['hero' => 'ส่วนบนสุด', 'categories' => 'หมวดสินค้าหลัก', 'samples' => 'ตัวอย่างสินค้า + Inspiration Hub'],
    'contact' => ['hero' => 'ส่วนบนสุด', 'form' => 'ฟอร์ม + ข้อมูลติดต่อ', 'map' => 'แผนที่'],
    'footer'  => ['main' => 'Footer'],
];

$slug = (string) ($_GET['page'] ?? $_POST['page'] ?? 'home');
if (!isset($pages[$slug])) {
    $slug = 'home';
}
$hasSeo = $slug !== 'footer';

$load = static function () use ($slug): array {
    $blocks = [];
    foreach (db_rows('SELECT block_key, value_th, value_en FROM dbo.lyiweb_blocks WHERE page_slug = ?', [$slug]) as $r) {
        $blocks[$r['block_key']] = ['th' => (string) $r['value_th'], 'en' => (string) $r['value_en']];
    }
    // natural order: hero.01, hero.02 … by first appearance in the built-in file
    $defaults = is_file(APP_ROOT . "/includes/blocks/$slug.php") ? require APP_ROOT . "/includes/blocks/$slug.php" : [];
    $ordered = [];
    foreach (array_keys($defaults) as $full) {
        $k = substr($full, strlen($slug) + 1);
        if (isset($blocks[$k])) {
            $ordered[$k] = $blocks[$k];
        }
    }
    return $ordered + $blocks;
};
$blocks = $load();
$seo = $hasSeo ? (db_rows('SELECT title_th, title_en, meta_desc_th, meta_desc_en FROM dbo.lyiweb_pages WHERE slug = ?', [$slug])[0] ?? []) : [];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_check_post();
    $uid = auth_user()['id'];
    $clean = static fn(mixed $v): string => trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', str_replace(["\r\n", "\r", "\n"], ' ', (string) $v)));
    $blockChanges = [];
    foreach ($blocks as $key => $old) {
        $new = ['th' => $old['th'], 'en' => $old['en']];
        if ($canTh && isset($_POST['th'][$key])) {
            $new['th'] = $clean($_POST['th'][$key]);
            if ($new['th'] === '') {
                $errors[$key] = 'ภาษาไทยห้ามว่าง';
            }
        }
        if (isset($_POST['en'][$key])) {
            $new['en'] = $clean($_POST['en'][$key]);
        }
        foreach (['th', 'en'] as $l) {
            if (mb_strlen($new[$l]) > 2000) {
                $errors[$key] = 'ยาวเกิน 2,000 ตัวอักษร';
            }
        }
        if ($new !== $old) {
            $blockChanges[$key] = [$old, $new];
        }
    }
    $seoChanges = [];
    if ($hasSeo) {
        foreach (['title_th' => 200, 'title_en' => 200, 'meta_desc_th' => 400, 'meta_desc_en' => 400] as $col => $max) {
            if (!isset($_POST['seo'][$col]) || (!$canTh && str_ends_with($col, '_th'))) {
                continue;
            }
            $v = $clean($_POST['seo'][$col]);
            if (mb_strlen($v) > $max) {
                $errors['seo.' . $col] = "ยาวเกิน $max ตัวอักษร";
            } elseif ($col === 'title_th' && $v === '') {
                $errors['seo.' . $col] = 'ชื่อหน้าภาษาไทยห้ามว่าง';
            } elseif ($v !== (string) ($seo[$col] ?? '')) {
                $seoChanges[$col] = $v;
            }
        }
    }
    if ($errors === []) {
        if ($blockChanges !== [] || $seoChanges !== []) {
            db_transaction(static function () use ($blockChanges, $seoChanges, $slug, $uid, $seo): void {
                foreach ($blockChanges as $key => [$old, $new]) {
                    db_exec(
                        'UPDATE dbo.lyiweb_blocks SET value_th = ?, value_en = ?, updated_at = GETDATE(), updated_by = ? WHERE page_slug = ? AND block_key = ?',
                        [$new['th'], $new['en'] === '' ? null : $new['en'], $uid, $slug, $key]
                    );
                    audit_log('update', 'lyiweb_blocks', "$slug.$key", $old, $new);
                }
                foreach ($seoChanges as $col => $v) {
                    // $col comes from the fixed whitelist above, never from user input
                    db_exec("UPDATE dbo.lyiweb_pages SET $col = ?, updated_at = GETDATE(), updated_by = ? WHERE slug = ?", [$v === '' ? null : $v, $uid, $slug]);
                    audit_log('update', 'lyiweb_pages', "$slug.$col", $seo[$col] ?? null, $v);
                }
            });
            content_cache_clear();
            flash('ok', 'บันทึกแล้ว ' . (count($blockChanges) + count($seoChanges)) . ' รายการ — หน้าเว็บอัปเดตทันที');
        } else {
            flash('info', 'ไม่มีอะไรเปลี่ยน');
        }
        header('Location: blocks.php?page=' . rawurlencode($slug));
        exit;
    }
    // keep what the user typed so they can fix the errors
    foreach ($blocks as $key => $b) {
        $blocks[$key] = ['th' => $canTh ? (string) ($_POST['th'][$key] ?? $b['th']) : $b['th'], 'en' => (string) ($_POST['en'][$key] ?? $b['en'])];
    }
    foreach (['title_th', 'title_en', 'meta_desc_th', 'meta_desc_en'] as $col) {
        if (isset($_POST['seo'][$col])) {
            $seo[$col] = (string) $_POST['seo'][$col];
        }
    }
}

$bySection = [];
foreach ($blocks as $key => $b) {
    $bySection[strstr($key, '.', true)][$key] = $b;
}
$missingEn = count(array_filter($blocks, static fn($b) => $b['en'] === ''));

admin_page_start('ข้อความหน้าเว็บ & SEO', 'blocks.php');
?>
<h1>ข้อความหน้าเว็บ & SEO</h1>
<nav class="tabs">
<?php foreach ($pages as $s => [$label, , $icon]): ?>
  <a href="?page=<?= e($s) ?>"<?= $s === $slug ? ' class="on" aria-current="page"' : '' ?>><?= admin_icon($icon) ?><span><?= e($label) ?></span></a>
<?php endforeach; ?>
</nav>

<?php if ($errors !== []): ?><div class="flash flash-error">ยังไม่ได้บันทึก — มีช่องที่ต้องแก้ <?= count($errors) ?> ช่อง</div><?php endif; ?>
<?php if (!$canTh): ?><div class="flash flash-info">คุณมีสิทธิ์แปลภาษาอังกฤษเท่านั้น — ช่องภาษาไทยแก้ไม่ได้</div><?php endif; ?>

<form method="post" class="form" id="blocks-form">
  <?= csrf_field() ?>
  <input type="hidden" name="page" value="<?= e($slug) ?>">

  <div class="toolbar">
    <input type="search" class="filter" placeholder="ค้นหาข้อความ…" data-filter>
    <label class="check"><input type="checkbox" data-only-missing> แสดงเฉพาะที่ยังไม่แปล (<?= $missingEn ?>)</label>
    <a class="btn btn-ghost" href="../<?= e($pages[$slug][1]) ?>" target="_blank" rel="noopener">ดูหน้านี้ ↗</a>
    <button class="btn btn-primary" type="submit">บันทึก</button>
  </div>

<?php if ($hasSeo): ?>
  <fieldset class="card">
    <legend>SEO — ชื่อหน้า (title) และคำอธิบาย (meta description)</legend>
    <div class="row head"><span></span><span>ภาษาไทย</span><span>English</span></div>
<?php foreach (['title' => ['ชื่อหน้า', 200, 'แสดงบนแท็บเบราว์เซอร์และหัวข้อผลการค้นหา Google — แนะนำ 50–60 ตัวอักษร'], 'meta_desc' => ['คำอธิบาย', 400, 'ข้อความใต้หัวข้อในผลการค้นหา — แนะนำ 120–160 ตัวอักษร']] as $f => [$label, $max, $help]): ?>
    <div class="row<?= isset($errors["seo.{$f}_th"]) || isset($errors["seo.{$f}_en"]) ? ' has-error' : '' ?>">
      <span class="key"><?= e($label) ?><small class="muted"><?= e($help) ?></small></span>
      <textarea name="seo[<?= $f ?>_th]" maxlength="<?= $max ?>" rows="<?= $f === 'title' ? 2 : 4 ?>" data-count<?= $canTh ? '' : ' readonly' ?>><?= e((string) ($seo[$f . '_th'] ?? '')) ?></textarea>
      <textarea name="seo[<?= $f ?>_en]" maxlength="<?= $max ?>" rows="<?= $f === 'title' ? 2 : 4 ?>" data-count placeholder="(ว่าง = ใช้ภาษาไทย)"><?= e((string) ($seo[$f . '_en'] ?? '')) ?></textarea>
<?php foreach (['th', 'en'] as $l): if (isset($errors["seo.{$f}_$l"])): ?><small class="err"><?= e($errors["seo.{$f}_$l"]) ?></small><?php endif; endforeach; ?>
    </div>
<?php endforeach; ?>
  </fieldset>
<?php endif; ?>

<?php foreach ($bySection as $section => $items): ?>
  <fieldset class="card" data-section>
    <legend><?= e($sectionNames[$slug][$section] ?? $section) ?> <small class="muted"><?= e($slug . '.' . $section) ?></small></legend>
    <div class="row head"><span></span><span>ภาษาไทย</span><span>English</span></div>
<?php foreach ($items as $key => $b): $rows = max(1, min(6, (int) ceil(mb_strlen($b['th']) / 60))); ?>
    <div class="row<?= isset($errors[$key]) ? ' has-error' : '' ?>" data-row data-missing="<?= $b['en'] === '' ? '1' : '0' ?>">
      <span class="key"><?= e(substr($key, strlen($section) + 1)) ?></span>
      <textarea name="th[<?= e($key) ?>]" rows="<?= $rows ?>"<?= $canTh ? '' : ' readonly' ?>><?= e($b['th']) ?></textarea>
      <textarea name="en[<?= e($key) ?>]" rows="<?= $rows ?>" placeholder="(ว่าง = ใช้ภาษาไทย)"><?= e($b['en']) ?></textarea>
<?php if (isset($errors[$key])): ?><small class="err"><?= e($errors[$key]) ?></small><?php endif; ?>
    </div>
<?php endforeach; ?>
  </fieldset>
<?php endforeach; ?>

  <div class="actions sticky"><button class="btn btn-primary" type="submit">บันทึก</button></div>
</form>
<?php
admin_page_end();
