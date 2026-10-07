<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';
require_once APP_ROOT . '/includes/lib/media.php';
require_once APP_ROOT . '/includes/icons.php';

admin_require('content.translate');

/*
 * Repeatable lists (lyiweb_items): products, FAQ, process steps … — fields come from
 * includes/schema/lists.php. Who may edit follows the list's page section (role scopes):
 * content.edit there = everything (Thai, order, show/hide, add/delete, images with media.upload);
 * content.translate only = English fields.
 */

$schema = require APP_ROOT . '/includes/schema/lists.php';
$lists = array_filter($schema, static fn(array $l): bool => can_content('en', ...explode('.', $l['scope'], 2)));
if ($lists === []) {
    admin_page_start('รายการ & รูปภาพ', 'lists.php');
    echo '<h1>รายการ & รูปภาพ</h1><div class="card"><h2>ยังไม่ได้รับมอบหมายให้แก้รายการใด</h2><p class="muted">ติดต่อผู้ดูแลระบบเพื่อกำหนดหน้า/ส่วนที่คุณดูแล</p></div>';
    admin_page_end();
    exit;
}

$key = (string) ($_GET['list'] ?? $_POST['list'] ?? '');
if (!isset($lists[$key])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(403);
        exit('ไม่มีสิทธิ์แก้รายการนี้');
    }
    $key = (string) array_key_first($lists);
}
$list = $lists[$key];
$fields = $list['fields'];
[$scopePage, $scopeSection] = explode('.', $list['scope'], 2);
$canEdit = can_content('th', $scopePage, $scopeSection);          // structure + Thai + shared fields
$canUpload = $canEdit && can('media.upload');
$pageLabel = admin_content_pages()[$scopePage]['label'] ?? $scopePage;
$sectionLabel = admin_content_pages()[$scopePage]['sections'][$scopeSection] ?? $scopeSection;

/** Current rows, keyed by id, in display order. */
$load = static function () use ($key): array {
    $rows = [];
    foreach (db_rows(
        'SELECT i.id, i.sort_order, i.is_active, i.data_th, i.data_en, i.data_common, i.image_id, m.file_path
           FROM dbo.lyiweb_items i LEFT JOIN dbo.lyiweb_media m ON m.id = i.image_id
          WHERE i.list_key = ? ORDER BY i.sort_order, i.id',
        [$key]
    ) as $r) {
        $rows[(string) $r['id']] = [
            'sort' => (int) $r['sort_order'],
            'active' => (bool) $r['is_active'],
            'th' => json_decode((string) $r['data_th'], true) ?: [],
            'en' => json_decode((string) $r['data_en'], true) ?: [],
            'c' => json_decode((string) $r['data_common'], true) ?: [],
            'image_id' => $r['image_id'] === null ? null : (int) $r['image_id'],
            'media' => $r['file_path'],
        ];
    }
    return $rows;
};
$items = $load();

$clean = static fn(mixed $v, bool $multiline = false): string => trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $multiline
    ? str_replace(["\r\n", "\r"], "\n", (string) $v)
    : str_replace(["\r\n", "\r", "\n"], ' ', (string) $v)));

/** Validate one value; returns [clean value, error|null]. */
$check = static function (array $def, mixed $raw) use ($clean): array {
    $v = $clean($raw, $def['type'] === 'textarea');
    return match ($def['type']) {
        'int'   => preg_match('/^-?\d{1,4}$/', $v) ? [(int) $v, null] : [$v, 'ต้องเป็นตัวเลข'],
        'bool'  => [$v === '1', null],
        'color' => preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $v) ? [$v, null] : [$v, 'รูปแบบสี #RRGGBB หรือ #RGB'],
        'icon'  => isset(icon_shapes()[$v]) ? [$v, null] : [$v, 'เลือกไอคอน'],
        'image' => [$v, null],
        'textarea' => mb_strlen($v) > 2000 ? [$v, 'ยาวเกิน 2,000 ตัวอักษร'] : [$v, null],
        default => mb_strlen($v) > 300 ? [$v, 'ยาวเกิน 300 ตัวอักษร'] : [$v, null],
    };
};

$errors = [];
$posted = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_check_post();
    $uid = auth_user()['id'];
    $in = (array) ($_POST['items'] ?? []);
    $files = $_FILES['img_file'] ?? null;
    $file = static function (string $k) use ($files): ?array {
        if (!is_array($files) || !isset($files['error'][$k]) || (int) $files['error'][$k] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return ['name' => (string) $files['name'][$k], 'tmp_name' => (string) $files['tmp_name'][$k], 'error' => (int) $files['error'][$k], 'size' => (int) $files['size'][$k]];
    };

    // 1) work out the wanted state of every item and validate it
    $wanted = [];
    foreach ($in as $k => $row) {
        $k = (string) $k;
        $isNew = !isset($items[$k]);
        if ($isNew && (!$canEdit || !preg_match('/^n\d{1,4}$/', $k))) {
            continue;   // translators cannot add; ignore forged keys
        }
        $old = $items[$k] ?? ['active' => true, 'th' => [], 'en' => [], 'c' => [], 'image_id' => null, 'media' => null];
        $row = (array) $row;
        if ($canEdit && ($row['delete'] ?? '') === '1') {
            if (!$isNew) {
                $wanted[$k] = ['delete' => true];
            }
            continue;
        }
        $new = $old + ['order' => 0];
        // only editors may reorder; a translator's form keeps the stored order
        $new['order'] = $canEdit ? (int) ($row['order'] ?? 0) : (int) ($old['sort'] ?? 0);
        if ($canEdit) {
            $new['active'] = ($row['active'] ?? '') === '1';
        }
        foreach ($fields as $f => $def) {
            if ($def['i18n']) {
                if ($canEdit) {
                    [$v, $err] = $check($def, $row['th'][$f] ?? '');
                    $new['th'][$f] = $v;
                    if ($err === null && $v === '') {
                        $err = 'ภาษาไทยห้ามว่าง';
                    }
                    if ($err !== null) {
                        $errors["$k.th.$f"] = $err;
                    }
                }
                [$v, $err] = $check($def, $row['en'][$f] ?? '');
                $new['en'][$f] = $v;
                if ($err !== null) {
                    $errors["$k.en.$f"] = $err;
                }
            } elseif ($canEdit && $def['type'] === 'image') {
                if (($row['img_clear'] ?? '') === '1') {
                    $new['c'][$f] = '';
                    $new['image_id'] = null;
                    $new['media'] = null;
                }
                $new['upload'] = $canUpload ? $file($k) : null;
            } elseif ($canEdit) {
                [$v, $err] = $check($def, $row['c'][$f] ?? '');
                // keep the stored spelling of a colour when only the letter case differs
                if ($def['type'] === 'color' && is_string($old['c'][$f] ?? null) && strcasecmp($old['c'][$f], (string) $v) === 0) {
                    $v = $old['c'][$f];
                }
                $new['c'][$f] = $v;
                if ($err !== null) {
                    $errors["$k.c.$f"] = $err;
                }
            }
        }
        $wanted[$k] = $new;
    }
    // items missing from the form are left untouched (e.g. another person's view was older)
    $posted = $wanted;

    // 2) uploads (only when everything else is valid, so nothing is stored for a form that fails)
    if ($errors === []) {
        foreach ($wanted as $k => &$w) {
            if (!empty($w['upload'])) {
                try {
                    $alt = (string) ($w['th']['title'] ?? $w['c']['code'] ?? $w['c']['label'] ?? '');
                    $m = media_store_upload($w['upload'], $uid, $alt);
                    $w['image_id'] = $m['id'];
                    $w['media'] = $m['path'];
                } catch (RuntimeException $e) {
                    $errors["$k.img"] = $e->getMessage();
                }
            }
            unset($w['upload']);
        }
        unset($w);
    }

    // 3) write
    if ($errors === []) {
        $enc = static function (array $data, array $only) use ($fields): ?string {
            $out = [];
            foreach ($fields as $f => $def) {
                if (in_array($f, $only, true) && array_key_exists($f, $data)) {
                    $out[$f] = $data[$f];
                }
            }
            return $out === [] ? null : json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        };
        $i18n = array_keys(array_filter($fields, static fn($d) => $d['i18n']));
        $common = array_keys(array_filter($fields, static fn($d) => !$d['i18n']));
        // order: by the position the user arranged, then rewrite as 10, 20, 30 …
        $keep = array_filter($wanted, static fn($w) => empty($w['delete']));
        uasort($keep, static fn($a, $b) => $a['order'] <=> $b['order']);
        $pos = 0;
        foreach ($keep as &$w) {
            $w['sort'] = ($pos += 10);
        }
        unset($w);

        $changes = 0;
        db_transaction(static function () use ($wanted, $keep, $items, $key, $uid, $enc, $i18n, $common, &$changes): void {
            foreach ($wanted as $k => $w) {
                if (!empty($w['delete'])) {
                    db_exec('DELETE FROM dbo.lyiweb_items WHERE id = ? AND list_key = ?', [(int) $k, $key]);
                    audit_log('delete', 'lyiweb_items', "$key#$k", $items[$k], null);
                    $changes++;
                }
            }
            foreach ($keep as $k => $w) {
                $en = array_filter($w['en'], static fn($v) => $v !== '');
                $row = [
                    'sort' => $w['sort'], 'active' => $w['active'] ? 1 : 0,
                    'th' => $enc($w['th'], $i18n), 'en' => $enc($en, $i18n), 'c' => $enc($w['c'], $common), 'image_id' => $w['image_id'],
                ];
                $old = $items[$k] ?? null;
                if ($old === null) {
                    db_exec(
                        'INSERT dbo.lyiweb_items (list_key, sort_order, is_active, data_th, data_en, data_common, image_id, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                        [$key, $row['sort'], $row['active'], $row['th'], $row['en'], $row['c'], $row['image_id'], $uid]
                    );
                    audit_log('create', 'lyiweb_items', $key, null, $row);
                    $changes++;
                    continue;
                }
                $before = ['active' => $old['active'] ? 1 : 0, 'th' => $enc($old['th'], $i18n), 'en' => $enc(array_filter($old['en'], static fn($v) => $v !== ''), $i18n), 'c' => $enc($old['c'], $common), 'image_id' => $old['image_id']];
                $after = ['active' => $row['active'], 'th' => $row['th'], 'en' => $row['en'], 'c' => $row['c'], 'image_id' => $row['image_id']];
                $moved = ($old['sort'] ?? null) !== $row['sort'];
                if ($before === $after && !$moved) {
                    continue;
                }
                db_exec(
                    'UPDATE dbo.lyiweb_items SET sort_order = ?, is_active = ?, data_th = ?, data_en = ?, data_common = ?, image_id = ?, updated_at = GETDATE(), updated_by = ?
                      WHERE id = ? AND list_key = ?',
                    [$row['sort'], $row['active'], $row['th'], $row['en'], $row['c'], $row['image_id'], $uid, (int) $k, $key]
                );
                if ($before !== $after) {
                    audit_log('update', 'lyiweb_items', "$key#$k", $before, $after);
                    $changes++;
                }
            }
        });
        content_cache_clear();
        flash($changes > 0 ? 'ok' : 'info', $changes > 0 ? "บันทึกแล้ว $changes รายการ — หน้าเว็บอัปเดตทันที" : 'บันทึกลำดับแล้ว / ไม่มีอะไรเปลี่ยน');
        header('Location: lists.php?list=' . rawurlencode($key));
        exit;
    }
}

/* ---------- view ---------- */
// after a failed save show what the user typed (new items included), in their order
$view = [];
if ($posted !== null) {
    foreach ($posted as $k => $w) {
        if (empty($w['delete'])) {
            $view[$k] = $w;
        }
    }
    foreach ($items as $k => $w) {
        $view[$k] ??= $w + ['order' => PHP_INT_MAX];
    }
    uasort($view, static fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
} else {
    $view = $items;
}
// field used as the card title: first translatable field, else the first plain text field
$titleField = array_key_first(array_filter($fields, static fn($d) => $d['i18n']))
    ?? array_key_first(array_filter($fields, static fn($d) => $d['type'] === 'text'));
$imageOf = static fn(array $w): string => (string) ($w['media'] ?? '') !== '' ? (string) $w['media'] : (string) ($w['c']['img'] ?? '');
$preview = static function (array $w) use ($fields, $titleField): string {
    $v = $titleField === null ? '' : (string) (($fields[$titleField]['i18n'] ? $w['th'] : $w['c'])[$titleField] ?? '');
    return $v !== '' ? mb_strimwidth($v, 0, 70, '…') : '(รายการใหม่)';
};
$missingEn = 0;
foreach ($view as $w) {
    foreach ($fields as $f => $def) {
        if ($def['i18n'] && needs_translation((string) ($w['th'][$f] ?? ''), (string) ($w['en'][$f] ?? ''))) {
            $missingEn++;
            break;
        }
    }
}

/** One item card. $k = id or 'n1'…; '__KEY__' for the template. */
$listKey = $key;
$card = static function (string $k, array $w, int $n) use ($fields, $canEdit, $canUpload, $errors, $imageOf, $preview, $titleField, $listKey): void {
    $err = static fn(string $p): ?string => $errors[$p] ?? null;
    $hasErr = (bool) array_filter(array_keys($errors), static fn($e) => str_starts_with($e, "$k."));
    $img = $imageOf($w);
    $imgShown = $img !== '' ? content_local_image($img) : '';
    $isNew = !ctype_digit($k);
    ?>
  <details class="item card<?= $w['active'] ? '' : ' is-hidden' ?><?= $hasErr ? ' has-error' : '' ?>" data-item data-key="<?= e($k) ?>" data-ref="<?= e($listKey . '#' . $k) ?>"<?= $isNew || $hasErr ? ' open' : '' ?>>
    <summary>
      <span class="item-n" data-item-n><?= $n ?></span>
<?php if ($imgShown !== ''): ?><img class="item-thumb" src="../<?= e($imgShown) ?>" alt=""><?php endif; ?>
      <span class="item-title" data-item-title><?= e($preview($w)) ?></span>
      <span class="chip chip-dim item-off">ซ่อนอยู่</span>
      <span class="chip item-del">จะลบเมื่อบันทึก</span>
<?php if ($canEdit): ?>
      <span class="item-move">
        <button type="button" class="icon-btn" data-move="-1" aria-label="เลื่อนขึ้น" title="เลื่อนขึ้น">↑</button>
        <button type="button" class="icon-btn" data-move="1" aria-label="เลื่อนลง" title="เลื่อนลง">↓</button>
      </span>
<?php endif; ?>
    </summary>
    <input type="hidden" name="items[<?= e($k) ?>][order]" value="<?= $n * 10 ?>" data-order>
    <input type="hidden" name="items[<?= e($k) ?>][delete]" value="0" data-delete>
<?php if ($canEdit): ?>
    <div class="item-bar">
      <label class="check"><input type="hidden" name="items[<?= e($k) ?>][active]" value="0"><input type="checkbox" name="items[<?= e($k) ?>][active]" value="1" data-active<?= $w['active'] ? ' checked' : '' ?>> แสดงบนเว็บ</label>
      <span class="spacer"></span>
      <button type="button" class="btn btn-danger btn-sm" data-remove>ลบรายการนี้</button>
      <button type="button" class="btn btn-sm" data-undo>ยกเลิกการลบ</button>
    </div>
<?php endif; ?>
    <div class="row head"><span></span><span>ภาษาไทย</span><span>English</span></div>
<?php foreach ($fields as $f => $def):
        $base = "items[$k]";
        if ($def['i18n']):
            $tag = $def['type'] === 'textarea' ? 'textarea' : 'input';
            $e1 = $err("$k.th.$f");
            $e2 = $err("$k.en.$f"); ?>
    <div class="row<?= $e1 || $e2 ? ' has-error' : '' ?>"<?= needs_translation((string) ($w['th'][$f] ?? ''), (string) ($w['en'][$f] ?? '')) ? ' data-missing="1"' : '' ?>>
      <span class="key"><?= e($def['label']) ?></span>
<?php if ($tag === 'textarea'): ?>
      <textarea name="<?= e("{$base}[th][$f]") ?>" rows="3" data-live="<?= e($f) ?>"<?= $canEdit ? '' : ' readonly' ?><?= $f === $titleField ? ' data-title-src' : '' ?>><?= e((string) ($w['th'][$f] ?? '')) ?></textarea>
      <textarea name="<?= e("{$base}[en][$f]") ?>" rows="3" placeholder="(ว่าง = ใช้ภาษาไทย)" data-live-en="<?= e($f) ?>"><?= e((string) ($w['en'][$f] ?? '')) ?></textarea>
<?php else: ?>
      <input name="<?= e("{$base}[th][$f]") ?>" value="<?= e((string) ($w['th'][$f] ?? '')) ?>" maxlength="300" data-live="<?= e($f) ?>"<?= $canEdit ? '' : ' readonly' ?><?= $f === $titleField ? ' data-title-src' : '' ?>>
      <input name="<?= e("{$base}[en][$f]") ?>" value="<?= e((string) ($w['en'][$f] ?? '')) ?>" maxlength="300" placeholder="(ว่าง = ใช้ภาษาไทย)" data-live-en="<?= e($f) ?>">
<?php endif; ?>
<?php if ($e1 || $e2): ?><small class="err"><?= e((string) ($e1 ?? $e2)) ?></small><?php endif; ?>
    </div>
<?php elseif ($canEdit):
            $v = $w['c'][$f] ?? ($def['type'] === 'bool' ? false : '');
            $e1 = $err("$k.c.$f") ?? ($def['type'] === 'image' ? $err("$k.img") : null); ?>
    <div class="row row-common<?= $e1 ? ' has-error' : '' ?>">
      <span class="key"><?= e($def['label']) ?></span>
      <div class="common">
<?php if ($def['type'] === 'image'): ?>
        <div class="img-field" data-img-field>
          <div class="img-preview"><?php if ($imgShown !== ''): ?><img src="../<?= e($imgShown) ?>" alt="" data-img-preview><?php else: ?><span class="muted small" data-img-preview><?= $img !== '' ? 'ไฟล์อยู่บน server อื่น' : 'Image pending' ?></span><?php endif; ?></div>
          <div class="img-actions">
<?php if ($canUpload): ?>
            <label class="btn btn-sm"><input type="file" name="img_file[<?= e($k) ?>]" accept="image/jpeg,image/png,image/webp" data-img-input hidden>เลือกรูปใหม่…</label>
<?php endif; ?>
<?php if ($img !== ''): ?>
            <label class="check small"><input type="checkbox" name="<?= e("{$base}[img_clear]") ?>" value="1"> ไม่ใช้รูป (แสดง Image pending)</label>
<?php endif; ?>
            <small class="muted" data-img-note><?= $canUpload ? 'JPG/PNG/WebP — ระบบย่อและแปลงเป็น WebP ให้อัตโนมัติ' : 'ไม่มีสิทธิ์อัปโหลดรูป' ?></small>
          </div>
        </div>
<?php elseif ($def['type'] === 'bool'): ?>
        <label class="check"><input type="hidden" name="<?= e("{$base}[c][$f]") ?>" value="0"><input type="checkbox" name="<?= e("{$base}[c][$f]") ?>" value="1"<?= $v ? ' checked' : '' ?>> ใช่</label>
<?php elseif ($def['type'] === 'icon'): ?>
        <select name="<?= e("{$base}[c][$f]") ?>">
<?php foreach (array_keys(icon_shapes()) as $ico): ?>
          <option value="<?= e($ico) ?>"<?= $ico === $v ? ' selected' : '' ?>><?= e($ico) ?></option>
<?php endforeach; ?>
        </select>
<?php elseif ($def['type'] === 'color'):
            // the picker needs #rrggbb; the stored text (may be short #fff) is what gets saved
            $hex6 = preg_match('/^#([0-9a-f])([0-9a-f])([0-9a-f])$/i', (string) $v, $m3) ? "#$m3[1]$m3[1]$m3[2]$m3[2]$m3[3]$m3[3]" : (string) $v; ?>
        <span class="color-field"><input type="color" value="<?= e(preg_match('/^#[0-9a-f]{6}$/i', $hex6) ? strtolower($hex6) : '#000000') ?>" data-color-pick aria-label="เลือกสี"><input name="<?= e("{$base}[c][$f]") ?>" value="<?= e((string) $v) ?>" maxlength="7" pattern="#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})" data-color-text></span>
<?php elseif ($def['type'] === 'int'): ?>
        <input type="number" name="<?= e("{$base}[c][$f]") ?>" value="<?= e((string) $v) ?>" step="1">
<?php else: ?>
        <input name="<?= e("{$base}[c][$f]") ?>" value="<?= e((string) $v) ?>" maxlength="300"<?= empty($def['css']) ? ' data-live="' . e($f) . '"' : '' ?><?= $f === $titleField ? ' data-title-src' : '' ?>>
<?php endif; ?>
<?php if ($e1): ?><small class="err"><?= e($e1) ?></small><?php endif; ?>
      </div>
    </div>
<?php endif;
    endforeach; ?>
  </details>
<?php
};

admin_page_start('รายการ & รูปภาพ', 'lists.php');
?>
<h1>รายการ & รูปภาพ</h1>
<nav class="tabs">
<?php foreach ($lists as $lk => $l): ?>
  <a href="?list=<?= e($lk) ?>"<?= $lk === $key ? ' class="on" aria-current="page"' : '' ?>><span><?= e(preg_replace('/^หน้าแรก · /u', '', $l['title'])) ?></span></a>
<?php endforeach; ?>
</nav>
<?php $shot = 'assets/where/' . substr($key, strpos($key, '.') + 1) . '.webp';
      $pageUrl = '../' . ($scopePage === 'home' ? 'index.php' : $scopePage . '.php') . ($scopeSection !== 'trust' ? '#' . $scopeSection : ''); ?>
<section class="card where list-only">
<?php if (is_file(__DIR__ . '/' . $shot)): ?>
  <a class="where-shot" href="<?= e(asset($shot)) ?>" target="_blank" rel="noopener" title="ดูภาพใหญ่"><img src="<?= e(asset($shot)) ?>" alt="ตำแหน่งบนหน้าเว็บ: <?= e($list['title']) ?>" loading="lazy"></a>
<?php endif; ?>
  <div class="where-text">
    <span class="where-label">อยู่ตรงไหนบนหน้าเว็บ</span>
    <p><b><?= e($pageLabel) ?></b> — <?= e($list['where'] ?? $sectionLabel) ?></p>
    <p class="muted small"><?= count($view) ?> รายการ · กดที่รายการเพื่อเปิดแก้ไข<?= $canEdit ? ' · ใช้ ↑ ↓ จัดลำดับ' : '' ?></p>
    <a class="btn btn-sm" href="<?= e($pageUrl) ?>" target="_blank" rel="noopener">เปิดดูบนหน้าเว็บจริง ↗</a>
  </div>
</section>

<?php if ($errors !== []): ?><div class="flash flash-error">ยังไม่ได้บันทึก — มีช่องที่ต้องแก้ <?= count($errors) ?> ช่อง<?= $canUpload && array_filter($fields, static fn($d) => $d['type'] === 'image') ? ' · รูปที่เลือกไว้ต้องเลือกใหม่อีกครั้ง' : '' ?></div><?php endif; ?>
<?php if (!$canEdit): ?><div class="flash flash-info">คุณมีสิทธิ์แปลภาษาอังกฤษเท่านั้นในส่วนนี้ — แก้ได้เฉพาะช่อง English</div><?php endif; ?>

<form method="post" class="form" id="list-form" enctype="multipart/form-data" data-list-form data-list="<?= e($key) ?>"
      data-list-names="<?= e(json_encode(array_map(static fn($l) => preg_replace('/^หน้าแรก · /u', '', $l['title']), $schema), JSON_UNESCAPED_UNICODE)) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="list" value="<?= e($key) ?>">
  <div class="blocks-split" data-split>
    <!-- the real page: click a card/item to edit it (wide screens, "เห็นหน้าเว็บ" view) -->
    <div class="preview-pane" data-preview-pane>
      <div class="preview-bar">
        <b>หน้าเว็บจริง</b><div class="view-switch view-switch-sm" role="group" aria-label="ภาษาของหน้าตัวอย่าง" data-preview-lang><button type="button" data-plang="th">TH</button><button type="button" data-plang="en">EN</button></div><span class="muted small"><?= e($list['where'] ?? '') ?> · คลิกรายการบนหน้าเว็บเพื่อแก้ · ข้อความที่พิมพ์เห็นผลทันที (ลำดับ/ซ่อน/รูป เห็นหลังกดบันทึก)</span>
        <a class="btn btn-sm btn-ghost" href="<?= e($pageUrl) ?>" target="_blank" rel="noopener">เปิดแท็บใหม่ ↗</a>
      </div>
      <div class="preview-box" data-preview-box><iframe data-preview title="ตัวอย่างหน้าเว็บ" data-src="preview.php?page=<?= e($scopePage) ?>"></iframe></div>
      <div class="preview-note" data-preview-note hidden></div>
    </div>

    <div class="edit-pane" data-edit-pane>
  <div class="toolbar">
    <div class="view-switch" role="group" aria-label="รูปแบบการแก้ไข" data-view-switch>
      <button type="button" data-view="visual">เห็นหน้าเว็บ</button>
      <button type="button" data-view="list">รายการ</button>
    </div>
    <input type="search" class="filter" placeholder="ค้นหาในรายการ…" data-filter>
    <label class="check"><input type="checkbox" data-only-missing> ยังไม่แปล (<?= $missingEn ?>)</label>
    <button class="btn btn-primary" type="submit">บันทึก</button>
  </div>

  <div class="items" data-items>
<?php $n = 0; foreach ($view as $k => $w) { $card((string) $k, $w, ++$n); } ?>
  </div>

<?php if ($canEdit): ?>
  <template data-item-template><?php $card('__KEY__', ['active' => true, 'th' => [], 'en' => [], 'c' => [], 'image_id' => null, 'media' => null], 0); ?></template>
  <button type="button" class="btn add-item" data-add-item>+ เพิ่มรายการ</button>
<?php endif; ?>

  <div class="actions sticky"><button class="btn btn-primary" type="submit">บันทึก</button></div>
    </div>
  </div>
</form>
<?php
admin_page_end();
