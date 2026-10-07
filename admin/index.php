<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';

$user = admin_require();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_cache') {
    admin_check_post();
    if (can('content.edit') || can('settings.edit')) {
        content_cache_clear();
        audit_log('update', 'cache', null, null, ['cleared' => true]);
        flash('ok', 'ล้าง cache แล้ว — หน้าเว็บจะโหลดข้อมูลล่าสุดจาก DB');
    }
    header('Location: index.php');
    exit;
}

$stats = db_rows(
    "SELECT
        (SELECT COUNT(*) FROM dbo.lyiweb_blocks) AS blocks,
        (SELECT COUNT(*) FROM dbo.lyiweb_blocks WHERE value_en IS NOT NULL AND value_en <> N'') AS blocks_en,
        (SELECT COUNT(*) FROM dbo.lyiweb_items WHERE is_active = 1) AS items,
        (SELECT COUNT(*) FROM dbo.lyiweb_contact_requests WHERE status = N'new') AS new_requests,
        (SELECT COUNT(*) FROM dbo.lyiweb_pages WHERE meta_desc_th IS NULL OR meta_desc_th = N'') AS pages_no_desc"
)[0];
$recent = db_rows(
    "SELECT TOP 10 a.created_at, a.action, a.entity, a.entity_id, u.name, u.username
       FROM dbo.lyiweb_audit_log a
       LEFT JOIN dbo.sysmnuser u ON u.id = a.user_id
      WHERE a.action <> 'login'
      ORDER BY a.id DESC"
);
$cacheFile = content_cache_file();
$cacheAge = is_file($cacheFile) ? time() - filemtime($cacheFile) : null;
$enPct = $stats['blocks'] > 0 ? (int) round($stats['blocks_en'] * 100 / $stats['blocks']) : 0;
$labels = array_map(static fn(array $l): string => $l[0], admin_permission_labels());
$scopes = auth_content_scopes();

admin_page_start('แดชบอร์ด', 'index.php');
?>
<h1>สวัสดี <?= e($user['name']) ?></h1>
<p class="muted">สิทธิ์ของคุณ:
<?php if (auth_permissions() === []): ?>
  <b>ยังไม่มีสิทธิ์ใดๆ</b> — ติดต่อผู้ดูแลระบบเพื่อขอสิทธิ์
<?php else: ?>
  <?= e(implode(' · ', array_map(fn($p) => $labels[$p] ?? $p, auth_permissions()))) ?>
<?php endif; ?>
</p>
<?php if ($scopes['edit'] !== [] && !in_array('*', $scopes['edit'], true)): ?>
<p class="muted small">แก้เนื้อหาได้ที่: <?= e(admin_scope_summary($scopes['edit'])) ?></p>
<?php endif; ?>
<?php if (array_diff($scopes['translate'], $scopes['edit']) !== [] && !in_array('*', $scopes['translate'], true)): ?>
<p class="muted small">แปลภาษาอังกฤษได้ที่: <?= e(admin_scope_summary($scopes['translate'])) ?></p>
<?php endif; ?>

<div class="stats">
  <div class="stat"><b><?= (int) $stats['new_requests'] ?></b><span>ข้อความขอใบเสนอราคาใหม่</span></div>
  <div class="stat"><b><?= (int) $stats['blocks'] ?></b><span>ข้อความบนหน้าเว็บ</span></div>
  <div class="stat"><b><?= $enPct ?>%</b><span>แปลอังกฤษแล้ว (<?= (int) $stats['blocks_en'] ?>/<?= (int) $stats['blocks'] ?>)</span></div>
  <div class="stat"><b><?= (int) $stats['items'] ?></b><span>รายการ (สินค้า, FAQ, ขั้นตอน …)</span></div>
</div>

<?php if ((int) $stats['pages_no_desc'] > 0): ?>
<div class="flash flash-warn">มี <?= (int) $stats['pages_no_desc'] ?> หน้าที่ยังไม่มี meta description (สำคัญต่อ SEO) — แก้ได้ที่ "ข้อความหน้าเว็บ & SEO"</div>
<?php endif; ?>

<div class="grid2">
  <section class="card">
    <h2>แก้ไขล่าสุด</h2>
<?php if ($recent === []): ?>
    <p class="muted">ยังไม่มีการแก้ไข</p>
<?php else: ?>
    <table class="table rtable">
      <thead><tr><th>เวลา</th><th>ผู้แก้</th><th>อะไร</th></tr></thead>
      <tbody>
<?php foreach ($recent as $r): ?>
        <tr><td class="nowrap muted small"><?= e($r['created_at'] instanceof DateTimeInterface ? $r['created_at']->format('d/m/Y H:i') : (string) $r['created_at']) ?></td><td><b><?= e($r['name'] ?: $r['username'] ?: '—') ?></b></td><td data-label="อะไร"><?= e($r['action'] . ' · ' . $r['entity'] . ($r['entity_id'] ? ' · ' . $r['entity_id'] : '')) ?></td></tr>
<?php endforeach; ?>
      </tbody>
    </table>
<?php endif; ?>
  </section>

  <section class="card">
    <h2>Cache หน้าเว็บ</h2>
    <p>หน้าเว็บอ่านข้อมูลจาก cache เพื่อความเร็ว (อายุสูงสุด <?= CONTENT_CACHE_TTL / 60 ?> นาที) — การบันทึกในหลังบ้านล้าง cache ให้อัตโนมัติ</p>
    <p class="muted">สถานะ: <?= $cacheAge === null ? 'ยังไม่มี cache' : 'สร้างเมื่อ ' . e((string) intdiv($cacheAge, 60)) . ' นาที ' . e((string) ($cacheAge % 60)) . ' วินาทีที่แล้ว' ?></p>
<?php if (can('content.edit') || can('settings.edit')): ?>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="clear_cache"><button class="btn" type="submit">ล้าง cache ตอนนี้</button></form>
    <p class="muted small">ใช้เมื่อแก้ข้อมูลตรงใน DB (เช่น Navicat) แล้วอยากเห็นผลทันที</p>
<?php endif; ?>
  </section>
</div>
<?php
admin_page_end();
