<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';

$me = admin_require('users.manage');

$labels = admin_permission_labels();
$roles = admin_roles();
$minLevel = auth_admin_min_level();
$contentPerms = ['content.edit', 'content.translate'];

/** Escape a user search term for LIKE (SQL Server bracket syntax). */
$like = static fn(string $q): string => '%' . strtr($q, ['[' => '[[]', '%' => '[%]', '_' => '[_]']) . '%';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$user = $id > 0 ? (db_rows('SELECT id, username, name, department, [level], locked FROM dbo.sysmnuser WHERE id = ?', [$id])[0] ?? null) : null;

if ($user !== null) {
    $current = array_map('intval', array_column(db_rows('SELECT role_id FROM dbo.lyiweb_user_roles WHERE user_id = ?', [$id]), 'role_id'));
    $isFull = $minLevel > 0 && (int) $user['level'] >= $minLevel;
    $isMe = (int) $user['id'] === $me['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_check_post();
        if ($isMe) {
            flash('error', 'แก้สิทธิ์ของตัวเองไม่ได้ — ให้ผู้ดูแลคนอื่นทำ');
        } else {
            $wanted = array_values(array_intersect(array_keys($roles), array_map('intval', (array) ($_POST['roles'] ?? []))));
            $add = array_diff($wanted, $current);
            $remove = array_diff($current, $wanted);
            if ($add === [] && $remove === []) {
                flash('info', 'ไม่มีอะไรเปลี่ยน');
            } else {
                db_transaction(static function () use ($id, $add, $remove, $me): void {
                    foreach ($remove as $rid) {
                        db_exec('DELETE FROM dbo.lyiweb_user_roles WHERE user_id = ? AND role_id = ?', [$id, $rid]);
                    }
                    foreach ($add as $rid) {
                        db_exec('INSERT dbo.lyiweb_user_roles (user_id, role_id, granted_by) VALUES (?, ?, ?)', [$id, $rid, $me['id']]);
                    }
                });
                $keys = static fn(array $ids): array => array_values(array_map(static fn(int $r): string => $roles[$r]['name_th'] ?? (string) $r, $ids));
                audit_log('update', 'lyiweb_user_roles', (string) $id, $keys($current), $keys($wanted));
                flash('ok', 'บันทึกสิทธิ์ของ ' . ($user['name'] ?: $user['username']) . ' แล้ว — มีผลทันที');
            }
        }
        header('Location: users.php?id=' . $id);
        exit;
    }

    admin_page_start('สิทธิ์ของ ' . ($user['name'] ?: $user['username']), 'users.php');
    ?>
<p class="small"><a href="users.php">← ผู้ใช้ทั้งหมด</a></p>
<h1><?= e($user['name'] ?: $user['username']) ?></h1>
<p class="muted"><?= e($user['username']) ?><?= $user['department'] ? ' · ' . e($user['department']) : '' ?> · level <?= (int) $user['level'] ?></p>

<?php if ((int) $user['locked'] === 1): ?><div class="flash flash-warn">บัญชีนี้ถูกล็อกในระบบบริษัท — login ไม่ได้จนกว่า IT จะปลดล็อก (สิทธิ์ที่ให้ไว้จะมีผลเมื่อปลดล็อก)</div><?php endif; ?>
<?php if ($isFull): ?><div class="flash flash-info">level <?= (int) $user['level'] ?> ≥ <?= $minLevel ?> — ได้ทุกสิทธิ์ทุกส่วนอัตโนมัติ role ด้านล่างไม่มีผล</div><?php endif; ?>
<?php if ($isMe): ?><div class="flash flash-info">นี่คือบัญชีของคุณ — แก้สิทธิ์ตัวเองไม่ได้</div><?php endif; ?>

<form method="post" class="form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
  <fieldset class="card"<?= $isMe ? ' disabled' : '' ?>>
    <legend>Role</legend>
    <div class="perm-list">
<?php foreach ($roles as $r): ?>
      <label class="perm"><input type="checkbox" name="roles[]" value="<?= $r['id'] ?>"<?= in_array($r['id'], $current, true) ? ' checked' : '' ?>>
        <span><b><?= e($r['name_th']) ?></b>
          <small class="muted"><?= e(implode(' · ', array_map(static fn($p) => $labels[$p][0] ?? $p, $r['perms'])) ?: 'ไม่มีสิทธิ์') ?><?= array_intersect($r['perms'], $contentPerms) !== [] ? ' — ' . e(admin_scope_summary($r['scopes'])) : '' ?></small></span></label>
<?php endforeach; ?>
    </div>
    <p class="small muted">ต้องการชุดสิทธิ์ที่ไม่มีในรายการ? สร้างได้ที่ <a href="roles.php?new=1">บทบาท (Role)</a></p>
  </fieldset>
<?php if (!$isMe): ?>
  <div class="actions sticky"><button class="btn btn-primary" type="submit">บันทึก</button></div>
<?php endif; ?>
</form>
<?php
    admin_page_end();
    exit;
}

/* ---------- list + search ---------- */
$q = trim((string) ($_GET['q'] ?? ''));
$found = $q === '' ? [] : db_rows(
    'SELECT TOP 30 id, username, name, department, [level], locked FROM dbo.sysmnuser
      WHERE username LIKE ? OR name LIKE ? OR department LIKE ? ORDER BY locked, name',
    [$like($q), $like($q), $like($q)]
);
$granted = [];
foreach (db_rows('SELECT ur.user_id, ur.role_id, u.username, u.name, u.department, u.locked
                    FROM dbo.lyiweb_user_roles ur JOIN dbo.sysmnuser u ON u.id = ur.user_id ORDER BY u.name') as $r) {
    $granted[(int) $r['user_id']] ??= $r + ['roles' => []];
    $granted[(int) $r['user_id']]['roles'][] = $roles[(int) $r['role_id']]['name_th'] ?? '?';
}
$admins = $minLevel > 0 ? db_rows(
    'SELECT id, username, name, department, [level] FROM dbo.sysmnuser WHERE [level] >= ? AND ISNULL(locked, 0) <> 1 ORDER BY name',
    [$minLevel]
) : [];

admin_page_start('ผู้ใช้ & สิทธิ์', 'users.php');
?>
<h1>ผู้ใช้ & สิทธิ์</h1>
<p class="muted">ผู้ใช้มาจากระบบบริษัท (sysmnuser) — ทุกคนที่ไม่ถูกล็อก login ได้ แต่จะแก้อะไรไม่ได้จนกว่าจะได้ role · ชุดสิทธิ์แก้ได้ที่ <a href="roles.php">บทบาท (Role)</a></p>

<form method="get" class="card search-user">
  <label class="field" style="margin:0"><span>ค้นหาผู้ใช้เพื่อให้สิทธิ์</span>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="ชื่อผู้ใช้ ชื่อ หรือแผนก" autofocus></label>
  <button class="btn btn-primary" type="submit">ค้นหา</button>
</form>

<?php if ($q !== ''): ?>
<section class="card">
  <h2>ผลการค้นหา "<?= e($q) ?>" (<?= count($found) ?><?= count($found) === 30 ? '+' : '' ?>)</h2>
<?php if ($found === []): ?>
  <p class="muted">ไม่พบ</p>
<?php else: ?>
  <table class="table">
    <thead><tr><th>ชื่อ</th><th>ชื่อผู้ใช้</th><th>แผนก</th><th>level</th><th>สิทธิ์ในเว็บ</th><th></th></tr></thead>
    <tbody>
<?php foreach ($found as $u): $uid = (int) $u['id']; ?>
      <tr<?= (int) $u['locked'] === 1 ? ' class="muted"' : '' ?>>
        <td><?= e($u['name'] ?: '—') ?></td><td><?= e($u['username']) ?></td><td><?= e((string) $u['department']) ?></td><td><?= (int) $u['level'] ?></td>
        <td class="small"><?= (int) $u['locked'] === 1 ? 'ถูกล็อก' : ($minLevel > 0 && (int) $u['level'] >= $minLevel ? 'ทุกสิทธิ์ (level)' : (e(implode(', ', $granted[$uid]['roles'] ?? [])) ?: '<span class="muted">—</span>')) ?></td>
        <td class="nowrap"><a href="?id=<?= $uid ?>">กำหนดสิทธิ์</a></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</section>
<?php endif; ?>

<div class="grid2">
  <section class="card">
    <h2>ผู้ที่ได้รับ role (<?= count($granted) ?>)</h2>
<?php if ($granted === []): ?>
    <p class="muted">ยังไม่มี — ค้นหาผู้ใช้ด้านบนแล้วกด "กำหนดสิทธิ์"</p>
<?php else: ?>
    <table class="table">
      <thead><tr><th>ชื่อ</th><th>Role</th><th></th></tr></thead>
      <tbody>
<?php foreach ($granted as $uid => $g): ?>
        <tr><td><?= e($g['name'] ?: $g['username']) ?><br><span class="muted small"><?= e($g['username']) ?><?= $g['department'] ? ' · ' . e($g['department']) : '' ?><?= (int) $g['locked'] === 1 ? ' · ถูกล็อก' : '' ?></span></td>
          <td class="small"><?= e(implode(', ', $g['roles'])) ?></td><td class="nowrap"><a href="?id=<?= $uid ?>">แก้ไข</a></td></tr>
<?php endforeach; ?>
      </tbody>
    </table>
<?php endif; ?>
  </section>

  <section class="card">
    <h2>ผู้ดูแลอัตโนมัติ (level ≥ <?= $minLevel ?>) · <?= count($admins) ?> คน</h2>
    <p class="muted small">ได้ทุกสิทธิ์ทุกส่วนจาก level ในระบบบริษัท — เปลี่ยนเกณฑ์ได้ที่ค่า <code>admin_min_level</code> ในตาราง lyiweb_settings</p>
    <ul class="plain">
<?php foreach ($admins as $a): ?>
      <li><?= e($a['name'] ?: $a['username']) ?> <span class="muted small"><?= e($a['username']) ?><?= $a['department'] ? ' · ' . e($a['department']) : '' ?> · level <?= (int) $a['level'] ?></span></li>
<?php endforeach; ?>
    </ul>
  </section>
</div>
<?php
admin_page_end();
