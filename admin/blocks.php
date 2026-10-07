<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';

admin_require('content.translate');

// Only pages/sections granted by the user's roles are shown or accepted (lyiweb_role_scopes)
$pages = array_filter(admin_content_pages(), static function (array $p, string $s): bool {
    foreach (array_keys($p['sections']) as $sec) {
        if (can_content('en', $s, $sec)) {
            return true;
        }
    }
    return false;
}, ARRAY_FILTER_USE_BOTH);

if ($pages === []) {
    admin_page_start('ข้อความหน้าเว็บ', 'blocks.php');
    echo '<h1>ข้อความหน้าเว็บ</h1><div class="card"><h2>ยังไม่ได้รับมอบหมายให้แก้หน้าใด</h2><p class="muted">ติดต่อผู้ดูแลระบบเพื่อกำหนดหน้า/ส่วนที่คุณดูแล</p></div>';
    admin_page_end();
    exit;
}

$slug = (string) ($_GET['page'] ?? $_POST['page'] ?? '');
if (!isset($pages[$slug])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // never apply a form meant for another page to the fallback page
        http_response_code(403);
        exit('ไม่มีสิทธิ์แก้หน้านี้');
    }
    $slug = (string) array_key_first($pages);
}
$canEn = static fn(string $sec): bool => can_content('en', $slug, $sec);
$canTh = static fn(string $sec): bool => can_content('th', $slug, $sec);

$load = static function () use ($slug, $canEn): array {
    $blocks = [];
    foreach (db_rows('SELECT block_key, value_th, value_en FROM dbo.lyiweb_blocks WHERE page_slug = ?', [$slug]) as $r) {
        if (!$canEn((string) strstr($r['block_key'], '.', true))) {
            continue;
        }
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

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_check_post();
    $uid = auth_user()['id'];
    $clean = static fn(mixed $v): string => trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', str_replace(["\r\n", "\r", "\n"], ' ', (string) $v)));
    $blockChanges = [];
    // placeholders each text must keep, taken from the built-in default text
    $tokens = [];
    $defaultsFile = APP_ROOT . "/includes/blocks/$slug.php";
    foreach (is_file($defaultsFile) ? require $defaultsFile : [] as $full => $text) {
        if (preg_match_all('/\{[a-z]+\}/', (string) $text, $m)) {
            $tokens[substr($full, strlen($slug) + 1)] = array_unique($m[0]);
        }
    }
    foreach ($blocks as $key => $old) {
        $new = ['th' => $old['th'], 'en' => $old['en']];
        $sec = (string) strstr($key, '.', true);
        if ($canTh($sec) && isset($_POST['th'][$key])) {
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
            // {phone} {open} … are filled in from ข้อมูลติดต่อ — a translation must keep every one of them
            $missing = array_diff($tokens[$key] ?? [], $new[$l] === '' ? $tokens[$key] ?? [] : (preg_match_all('/\{[a-z]+\}/', $new[$l], $m) ? $m[0] : []));
            if ($new[$l] !== '' && $missing !== []) {
                $errors[$key] = 'ต้องมี ' . implode(' ', $missing) . ' อยู่ในข้อความ (ระบบใส่ค่าจริงให้)';
            }
        }
        if ($new !== $old) {
            $blockChanges[$key] = [$old, $new];
        }
    }
    if ($errors === []) {
        if ($blockChanges !== []) {
            db_transaction(static function () use ($blockChanges, $slug, $uid): void {
                foreach ($blockChanges as $key => [$old, $new]) {
                    db_exec(
                        'UPDATE dbo.lyiweb_blocks SET value_th = ?, value_en = ?, updated_at = GETDATE(), updated_by = ? WHERE page_slug = ? AND block_key = ?',
                        [$new['th'], $new['en'] === '' ? null : $new['en'], $uid, $slug, $key]
                    );
                    audit_log('update', 'lyiweb_blocks', "$slug.$key", $old, $new);
                }
            });
            content_cache_clear();
            flash('ok', 'บันทึกแล้ว ' . count($blockChanges) . ' รายการ — หน้าเว็บอัปเดตทันที');
        } else {
            flash('info', 'ไม่มีอะไรเปลี่ยน');
        }
        header('Location: blocks.php?page=' . rawurlencode($slug));
        exit;
    }
    // keep what the user typed so they can fix the errors
    foreach ($blocks as $key => $b) {
        $blocks[$key] = ['th' => $canTh((string) strstr($key, '.', true)) ? (string) ($_POST['th'][$key] ?? $b['th']) : $b['th'], 'en' => (string) ($_POST['en'][$key] ?? $b['en'])];
    }
}

$bySection = [];
foreach ($blocks as $key => $b) {
    $bySection[strstr($key, '.', true)][$key] = $b;
}
$missingEn = count(array_filter($blocks, static fn($b) => needs_translation($b['th'], $b['en'])));
$sectionNames = $pages[$slug]['sections'];
$enOnly = array_filter(array_keys($bySection), static fn($sec) => !$canTh((string) $sec));

$kinds = admin_block_kinds($slug);

admin_page_start('ข้อความหน้าเว็บ', 'blocks.php');
?>
<h1>ข้อความหน้าเว็บ</h1>
<nav class="tabs">
<?php foreach ($pages as $s => $p): ?>
  <a href="?page=<?= e($s) ?>"<?= $s === $slug ? ' class="on" aria-current="page"' : '' ?>><?= admin_icon($p['icon']) ?><span><?= e($p['label']) ?></span></a>
<?php endforeach; ?>
</nav>

<p class="small muted">ชื่อหน้าและคำอธิบายบน Google ย้ายไปที่เมนู <a href="seo.php?tab=meta">SEO &amp; AEO</a></p>
<?php if ($errors !== []): ?><div class="flash flash-error">ยังไม่ได้บันทึก — มีช่องที่ต้องแก้ <?= count($errors) ?> ช่อง</div><?php endif; ?>
<?php if ($enOnly !== []): ?><div class="flash flash-info"><?= count($enOnly) === count($bySection) ? 'คุณมีสิทธิ์แปลภาษาอังกฤษเท่านั้น' : 'บางส่วนคุณมีสิทธิ์แปลภาษาอังกฤษเท่านั้น' ?> — ช่องภาษาไทยสีเทาแก้ไม่ได้</div><?php endif; ?>

<form method="post" class="form" id="blocks-form" data-blocks-form data-page="<?= e($slug) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="page" value="<?= e($slug) ?>">

  <div class="blocks-split" data-split>
    <!-- the real page: click a text to edit it (shown on wide screens in the "เห็นหน้าเว็บ" view) -->
    <div class="preview-pane" data-preview-pane>
      <div class="preview-bar">
        <b>หน้าเว็บจริง</b><span class="muted small">ชี้แล้วคลิกข้อความที่ต้องการแก้ — กรอบสีส้ม = ข้อความที่กำลังแก้ · พิมพ์แล้วเห็นผลทันที (ยังไม่บันทึกจนกว่าจะกดบันทึก)</span>
        <a class="btn btn-sm btn-ghost" href="../<?= e($pages[$slug]['url']) ?>" target="_blank" rel="noopener">เปิดแท็บใหม่ ↗</a>
      </div>
      <div class="preview-box" data-preview-box><iframe data-preview title="ตัวอย่างหน้าเว็บ" data-src="preview.php?page=<?= e($slug) ?>"></iframe></div>
      <div class="preview-note" data-preview-note hidden></div>
    </div>

    <div class="edit-pane" data-edit-pane>
      <div class="toolbar">
        <div class="view-switch" role="group" aria-label="รูปแบบการแก้ไข" data-view-switch>
          <button type="button" data-view="visual">เห็นหน้าเว็บ</button>
          <button type="button" data-view="list">รายการ</button>
        </div>
        <nav class="subnav" aria-label="ส่วนของหน้า" data-subnav data-page="<?= e($slug) ?>">
          <button type="button" class="on" data-sec="all" aria-pressed="true">ทั้งหมด</button>
<?php foreach ($bySection as $section => $items): ?>
          <button type="button" data-sec="<?= e($section) ?>" aria-pressed="false"><?= e($sectionNames[$section] ?? $section) ?><small><?= count($items) ?></small></button>
<?php endforeach; ?>
        </nav>
        <input type="search" class="filter" placeholder="ค้นหาข้อความ…" data-filter>
        <label class="check"><input type="checkbox" data-only-missing> ยังไม่แปล (<?= $missingEn ?>)</label>
        <a class="btn btn-ghost list-only" href="../<?= e($pages[$slug]['url']) ?>" target="_blank" rel="noopener">ดูหน้านี้ ↗</a>
        <button class="btn btn-primary" type="submit">บันทึก</button>
      </div>


<?php foreach ($bySection as $section => $items): ?>
      <fieldset class="card" data-section data-section-key="<?= e($section) ?>">
        <legend><?= e($sectionNames[$section] ?? $section) ?></legend>
        <div class="row head"><span></span><span>ภาษาไทย</span><span>English</span></div>
<?php foreach ($items as $key => $b): $rows = max(1, min(6, (int) ceil(mb_strlen($b['th']) / 60))); ?>
        <div class="row<?= isset($errors[$key]) ? ' has-error' : '' ?>" data-row data-key="<?= e("$slug.$key") ?>" data-missing="<?= needs_translation($b['th'], $b['en']) ? '1' : '0' ?>">
          <span class="key"><b><?= e($kinds[$key] ?? 'ข้อความ') ?></b><small><?= e(substr($key, strlen($section) + 1)) ?></small></span>
          <textarea name="th[<?= e($key) ?>]" rows="<?= $rows ?>" aria-label="ภาษาไทย" data-th<?= $canTh($section) ? '' : ' readonly' ?>><?= e($b['th']) ?></textarea>
          <textarea name="en[<?= e($key) ?>]" rows="<?= $rows ?>" aria-label="English" placeholder="English (ว่าง = ใช้ภาษาไทย)"><?= e($b['en']) ?></textarea>
<?php if (isset($errors[$key])): ?><small class="err"><?= e($errors[$key]) ?></small><?php endif; ?>
        </div>
<?php endforeach; ?>
      </fieldset>
<?php endforeach; ?>

      <div class="actions sticky"><button class="btn btn-primary" type="submit">บันทึก</button></div>
    </div>
  </div>
</form>
<?php
admin_page_end();
