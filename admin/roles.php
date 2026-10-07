<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';

admin_require('users.manage');

$labels = admin_permission_labels();
$pages = admin_content_pages();
$roles = admin_roles();
$contentPerms = ['content.edit', 'content.translate'];

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$isNew = isset($_GET['new']) || ($_POST['id'] ?? '') === 'new';
$role = $isNew ? ['id' => 0, 'role_key' => '', 'name_th' => '', 'is_system' => false, 'perms' => [], 'scopes' => [], 'members' => 0] : ($roles[$id] ?? null);
// the built-in admin role always has everything: shown, never edited
$locked = $role !== null && $role['role_key'] === 'admin';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role !== null && !$locked) {
    admin_check_post();
    $uid = auth_user()['id'];

    if (($_POST['action'] ?? '') === 'delete' && !$isNew) {
        if ($role['is_system']) {
            flash('error', 'role ของระบบลบไม่ได้');
            header('Location: roles.php?id=' . $role['id']);
            exit;
        }
        db_transaction(static function () use ($role): void {
            foreach (['lyiweb_user_roles', 'lyiweb_role_scopes', 'lyiweb_role_permissions'] as $table) {
                db_exec("DELETE FROM dbo.$table WHERE role_id = ?", [$role['id']]);
            }
            db_exec('DELETE FROM dbo.lyiweb_roles WHERE id = ?', [$role['id']]);
            audit_log('delete', 'lyiweb_roles', (string) $role['id'], $role, null);
        });
        flash('ok', 'ลบ role "' . $role['name_th'] . '" แล้ว' . ($role['members'] > 0 ? ' — ถอนสิทธิ์จากผู้ใช้ ' . $role['members'] . ' คน' : ''));
        header('Location: roles.php');
        exit;
    }

    $name = trim(preg_replace('/\s+/u', ' ', (string) ($_POST['name_th'] ?? '')));
    $perms = array_values(array_intersect(AUTH_ALL_PERMISSIONS, array_map('strval', (array) ($_POST['perms'] ?? []))));
    $scopes = admin_normalize_scopes((array) ($_POST['scopes'] ?? []));
    if ($name === '') {
        $errors['name_th'] = 'กรุณาตั้งชื่อ role';
    } elseif (mb_strlen($name) > 100) {
        $errors['name_th'] = 'ยาวเกิน 100 ตัวอักษร';
    } else {
        foreach ($roles as $other) {
            if ($other['id'] !== $role['id'] && mb_strtolower($other['name_th']) === mb_strtolower($name)) {
                $errors['name_th'] = 'มี role ชื่อนี้แล้ว';
            }
        }
    }
    if (array_intersect($perms, $contentPerms) === []) {
        $scopes = [];   // scopes only mean something with a content permission
    } elseif ($scopes === []) {
        $errors['scopes'] = 'เลือกอย่างน้อย 1 หน้า/ส่วน ที่ role นี้แก้ได้';
    }

    $role['name_th'] = $name;
    $role['perms'] = $perms;
    $role['scopes'] = $scopes;

    if ($errors === []) {
        $before = $isNew ? null : $roles[$role['id']];
        $roleId = db_transaction(static function () use ($role, $isNew, $perms, $scopes, $name): int {
            if ($isNew) {
                $key = 'custom_' . bin2hex(random_bytes(4));
                db_exec('INSERT dbo.lyiweb_roles (role_key, name_th, is_system) VALUES (?, ?, 0)', [$key, $name]);
                $roleId = (int) db_rows('SELECT id FROM dbo.lyiweb_roles WHERE role_key = ?', [$key])[0]['id'];
            } else {
                $roleId = $role['id'];
                db_exec('UPDATE dbo.lyiweb_roles SET name_th = ? WHERE id = ?', [$name, $roleId]);
                db_exec('DELETE FROM dbo.lyiweb_role_permissions WHERE role_id = ?', [$roleId]);
                db_exec('DELETE FROM dbo.lyiweb_role_scopes WHERE role_id = ?', [$roleId]);
            }
            foreach ($perms as $p) {
                db_exec('INSERT dbo.lyiweb_role_permissions (role_id, permission) VALUES (?, ?)', [$roleId, $p]);
            }
            foreach ($scopes as $sc) {
                db_exec('INSERT dbo.lyiweb_role_scopes (role_id, scope) VALUES (?, ?)', [$roleId, $sc]);
            }
            return $roleId;
        });
        audit_log($isNew ? 'create' : 'update', 'lyiweb_roles', (string) $roleId,
            $before === null ? null : ['name_th' => $before['name_th'], 'perms' => $before['perms'], 'scopes' => $before['scopes']],
            ['name_th' => $name, 'perms' => $perms, 'scopes' => $scopes]);
        flash('ok', 'บันทึก role "' . $name . '" แล้ว — มีผลกับผู้ใช้ทันที');
        header('Location: roles.php?id=' . $roleId);
        exit;
    }
}

/* ---------- list ---------- */
if ($role === null) {
    admin_page_start('บทบาท (Role)', 'roles.php');
    ?>
<h1>บทบาท (Role)</h1>
<p class="muted">role = ชุดสิทธิ์ + หน้า/ส่วนที่แก้ได้ แล้วนำไปให้ผู้ใช้ที่หน้า <a href="users.php">ผู้ใช้ & สิทธิ์</a> · ผู้ใช้ที่ไม่มี role เลย login ไม่ได้<?= auth_admin_min_level() > 0 ? ' · ผู้ใช้ที่ level ≥ ' . auth_admin_min_level() . ' ในระบบบริษัทได้ทุกสิทธิ์อัตโนมัติ' : '' ?></p>
<div class="actions" style="justify-content:flex-start;margin:14px 0"><a class="btn btn-primary" href="?new=1">+ เพิ่ม role</a></div>
<div class="card">
  <table class="table">
    <thead><tr><th>Role</th><th>สิทธิ์</th><th>หน้า/ส่วนที่แก้ได้</th><th class="nowrap">ผู้ใช้</th><th></th></tr></thead>
    <tbody>
<?php foreach ($roles as $r): ?>
      <tr>
        <td><b><?= e($r['name_th']) ?></b><?= $r['is_system'] ? ' <span class="chip chip-dim">ระบบ</span>' : '' ?></td>
        <td><?php foreach ($r['perms'] as $p): ?><span class="chip"><?= e($labels[$p][0] ?? $p) ?></span><?php endforeach; ?><?= $r['perms'] === [] ? '<span class="muted">—</span>' : '' ?></td>
        <td class="small"><?= array_intersect($r['perms'], $contentPerms) === [] ? '<span class="muted">—</span>' : e(admin_scope_summary($r['scopes'])) ?></td>
        <td><?= $r['members'] ?></td>
        <td class="nowrap"><a href="?id=<?= $r['id'] ?>"><?= $r['role_key'] === 'admin' ? 'ดู' : 'แก้ไข' ?></a></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
    admin_page_end();
    exit;
}

/* ---------- edit / new ---------- */
$members = $isNew ? [] : db_rows(
    'SELECT u.id, u.username, u.name, u.department FROM dbo.lyiweb_user_roles ur JOIN dbo.sysmnuser u ON u.id = ur.user_id WHERE ur.role_id = ? ORDER BY u.name',
    [$role['id']]
);
$all = in_array('*', $role['scopes'], true);
$title = $isNew ? 'เพิ่ม role' : $role['name_th'];

admin_page_start($title, 'roles.php');
?>
<a class="back" href="roles.php"><?= admin_icon('back') ?><span>บทบาททั้งหมด</span></a>
<h1><?= e($title) ?></h1>
<?php if ($locked): ?><div class="flash flash-info">role ผู้ดูแลระบบได้ทุกสิทธิ์ทุกส่วนเสมอ — แก้ไม่ได้</div><?php endif; ?>
<?php if ($errors !== []): ?><div class="flash flash-error">ยังไม่ได้บันทึก — มีช่องที่ต้องแก้ <?= count($errors) ?> ช่อง</div><?php endif; ?>

<form method="post" class="form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= $isNew ? 'new' : $role['id'] ?>">
  <fieldset class="card"<?= $locked ? ' disabled' : '' ?>>
    <legend>ชื่อ</legend>
    <label class="field<?= isset($errors['name_th']) ? ' has-error' : '' ?>">
      <span>ชื่อ role</span>
      <input name="name_th" value="<?= e($role['name_th']) ?>" maxlength="100" required placeholder="เช่น ทีม R&D, ฝ่ายขาย, ผู้แปลหน้าแคตตาล็อก">
<?php if (isset($errors['name_th'])): ?><small class="err"><?= e($errors['name_th']) ?></small><?php endif; ?>
    </label>
  </fieldset>

  <fieldset class="card"<?= $locked ? ' disabled' : '' ?>>
    <legend>ทำอะไรได้บ้าง</legend>
    <div class="perm-list">
<?php foreach ($labels as $p => [$label, $desc]): ?>
      <label class="perm"><input type="checkbox" name="perms[]" value="<?= e($p) ?>"<?= in_array($p, $role['perms'], true) ? ' checked' : '' ?><?= in_array($p, $contentPerms, true) ? ' data-content-perm' : '' ?>>
        <span><b><?= e($label) ?></b><small class="muted"><?= e($desc) ?></small></span></label>
<?php endforeach; ?>
    </div>
  </fieldset>

  <fieldset class="card<?= isset($errors['scopes']) ? ' has-error' : '' ?>" data-scope-tree<?= $locked ? ' disabled' : '' ?>>
    <legend>หน้า/ส่วนที่แก้ได้</legend>
    <p class="muted small" style="margin-top:0">ใช้กับสิทธิ์ "แก้เนื้อหา" และ "แปลภาษาอังกฤษ" — ติ๊กทั้งหน้า = รวมส่วนที่จะเพิ่มในอนาคตด้วย</p>
<?php if (isset($errors['scopes'])): ?><p class="err small"><?= e($errors['scopes']) ?></p><?php endif; ?>
    <label class="perm scope-all"><input type="checkbox" name="scopes[]" value="*" data-scope-all<?= $all ? ' checked' : '' ?>><span><b>ทุกหน้า ทุกส่วน</b></span></label>
    <div class="scope-pages">
<?php foreach ($pages as $slug => $page): $whole = in_array($slug, $role['scopes'], true); ?>
      <div class="scope-page" data-scope-page>
        <label class="scope-head"><input type="checkbox" name="scopes[]" value="<?= e($slug) ?>" data-scope-whole<?= $whole ? ' checked' : '' ?>><?= admin_icon($page['icon']) ?><b><?= e($page['label']) ?></b><small class="muted">ทั้งหน้า</small></label>
        <div class="scope-secs">
<?php foreach ($page['sections'] as $sec => $label): ?>
          <label class="check"><input type="checkbox" name="scopes[]" value="<?= e("$slug.$sec") ?>"<?= in_array("$slug.$sec", $role['scopes'], true) ? ' checked' : '' ?>><?= e($label) ?></label>
<?php endforeach; ?>
        </div>
      </div>
<?php endforeach; ?>
    </div>
  </fieldset>

<?php if (!$locked): ?>
  <div class="actions sticky">
<?php if (!$isNew && !$role['is_system']): ?>
    <button class="btn btn-danger" type="submit" name="action" value="delete" formnovalidate data-confirm="ลบ role นี้? ผู้ใช้ <?= $role['members'] ?> คนที่มี role นี้จะเสียสิทธิ์ทันที (ถ้าไม่มี role อื่นจะ login ไม่ได้)">ลบ role</button>
    <span class="spacer"></span>
<?php endif; ?>
    <button class="btn btn-primary" type="submit" name="action" value="save">บันทึก</button>
  </div>
<?php endif; ?>
</form>

<?php if (!$isNew): ?>
<section class="card">
  <h2>ผู้ใช้ที่มี role นี้ (<?= count($members) ?>)</h2>
<?php if ($members === []): ?>
  <p class="muted">ยังไม่มี — ให้ role ได้ที่หน้า <a href="users.php">ผู้ใช้ & สิทธิ์</a></p>
<?php else: ?>
  <ul class="plain">
<?php foreach ($members as $m): ?>
    <li><a href="users.php?id=<?= (int) $m['id'] ?>"><?= e($m['name'] ?: $m['username']) ?></a> <span class="muted small"><?= e($m['username']) ?><?= $m['department'] ? ' · ' . e($m['department']) : '' ?></span></li>
<?php endforeach; ?>
  </ul>
<?php endif; ?>
</section>
<?php endif; ?>
<?php
admin_page_end();
