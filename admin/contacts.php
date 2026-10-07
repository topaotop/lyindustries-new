<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';

$me = admin_require('contact.view');

/*
 * Quote requests from the contact page form (lyiweb_contact_requests). Opening a new request marks
 * it "อ่านแล้ว"; the sales team moves it to "ดำเนินการแล้ว" (or "สแปม") when handled.
 */

const CONTACT_STATUSES = ['new' => 'ใหม่', 'read' => 'อ่านแล้ว', 'done' => 'ดำเนินการแล้ว', 'spam' => 'สแปม'];
const CONTACTS_PER_PAGE = 30;

$fmt = static fn(mixed $d): string => $d instanceof DateTimeInterface ? $d->format('d/m/Y H:i') : (string) $d;
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

/* ---------- one request ---------- */
if ($id > 0) {
    $req = db_rows('SELECT r.*, u.name AS handler FROM dbo.lyiweb_contact_requests r LEFT JOIN dbo.sysmnuser u ON u.id = r.handled_by WHERE r.id = ?', [$id])[0] ?? null;
    if ($req === null) {
        flash('error', 'ไม่พบคำขอนี้');
        header('Location: contacts.php');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_check_post();
        $status = (string) ($_POST['status'] ?? '');
        if (isset(CONTACT_STATUSES[$status]) && $status !== $req['status']) {
            db_exec('UPDATE dbo.lyiweb_contact_requests SET status = ?, handled_by = ?, handled_at = GETDATE() WHERE id = ?', [$status, $me['id'], $id]);
            audit_log('update', 'lyiweb_contact_requests', (string) $id, ['status' => $req['status']], ['status' => $status]);
            flash('ok', 'เปลี่ยนสถานะเป็น "' . CONTACT_STATUSES[$status] . '" แล้ว');
        }
        header('Location: contacts.php' . ($status === 'done' || $status === 'spam' ? '' : '?id=' . $id));
        exit;
    }
    if ($req['status'] === 'new') {   // opened = read
        db_exec("UPDATE dbo.lyiweb_contact_requests SET status = N'read', handled_by = ?, handled_at = GETDATE() WHERE id = ? AND status = N'new'", [$me['id'], $id]);
        $req['status'] = 'read';
        $req['handler'] = $me['name'];
        $req['handled_at'] = new DateTime();
    }
    $subject = 'ใบเสนอราคา — ' . ($req['product'] ?: 'L.Y. Industries');
    $greeting = 'เรียน คุณ' . $req['name'] . "\n\nขอบคุณที่ติดต่อ L.Y. Industries\n\n";

    admin_page_start('คำขอจาก ' . $req['name'], 'contacts.php');
    ?>
<a class="back" href="contacts.php"><?= admin_icon('back') ?><span>คำขอทั้งหมด</span></a>
<h1><?= e($req['name']) ?></h1>
<p class="muted"><?= e($fmt($req['created_at'])) ?> · <span class="chip status-<?= e($req['status']) ?>"><?= e(CONTACT_STATUSES[$req['status']] ?? $req['status']) ?></span>
  <?php if ($req['handler']): ?> · <?= e(CONTACT_STATUSES[$req['status']] ?? '') ?>โดย <?= e($req['handler']) ?> <?= e($fmt($req['handled_at'])) ?><?php endif; ?></p>

<div class="grid2">
  <section class="card">
    <h2>ข้อมูลผู้ติดต่อ</h2>
    <dl class="kv">
      <dt>ชื่อ</dt><dd><?= e($req['name']) ?></dd>
      <dt>บริษัท</dt><dd><?= e((string) $req['company']) ?: '<span class="muted">—</span>' ?></dd>
      <dt>อีเมล</dt><dd><a href="mailto:<?= e($req['email']) ?>"><?= e($req['email']) ?></a></dd>
      <dt>โทร</dt><dd><?= $req['phone'] ? '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', (string) $req['phone'])) . '">' . e($req['phone']) . '</a>' : '<span class="muted">—</span>' ?></dd>
      <dt>สินค้าที่สนใจ</dt><dd><?= e((string) $req['product']) ?: '<span class="muted">—</span>' ?></dd>
      <dt>ภาษา / หน้า</dt><dd><?= e(strtoupper((string) $req['lang'])) ?> · <?= e((string) $req['page']) ?></dd>
    </dl>
    <div class="actions" style="justify-content:flex-start;flex-wrap:wrap">
      <a class="btn btn-primary" href="mailto:<?= e($req['email']) ?>?subject=<?= e(rawurlencode($subject)) ?>&amp;body=<?= e(rawurlencode($greeting)) ?>">ตอบกลับทางอีเมล</a>
<?php if ($req['phone']): ?>
      <a class="btn" href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $req['phone'])) ?>">โทรหา <?= e($req['phone']) ?></a>
<?php endif; ?>
    </div>
  </section>
  <section class="card">
    <h2>รายละเอียด</h2>
    <div class="msg"><?= nl2br(e((string) $req['message'])) ?></div>
    <form method="post" class="status-form">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $id ?>">
      <span class="muted small">เปลี่ยนสถานะ:</span>
<?php foreach (CONTACT_STATUSES as $s => $label): if ($s === 'new' || $s === $req['status']) { continue; } ?>
      <button class="btn btn-sm<?= $s === 'done' ? ' btn-primary' : ($s === 'spam' ? ' btn-danger' : '') ?>" type="submit" name="status" value="<?= e($s) ?>"><?= e($label) ?></button>
<?php endforeach; ?>
    </form>
  </section>
</div>
<?php
    admin_page_end();
    exit;
}

/* ---------- list ---------- */
$status = (string) ($_GET['status'] ?? 'open');
$q = trim((string) ($_GET['q'] ?? ''));
$pageNo = max(1, (int) ($_GET['p'] ?? 1));
$where = [];
$params = [];
if ($status === 'open') {
    $where[] = "status IN (N'new', N'read')";
} elseif (isset(CONTACT_STATUSES[$status])) {
    $where[] = 'status = ?';
    $params[] = $status;
} else {
    $status = 'all';
}
if ($q !== '') {
    $like = '%' . strtr($q, ['[' => '[[]', '%' => '[%]', '_' => '[_]']) . '%';
    $where[] = '(name LIKE ? OR company LIKE ? OR email LIKE ? OR phone LIKE ? OR product LIKE ? OR message LIKE ?)';
    array_push($params, $like, $like, $like, $like, $like, $like);
}
$sqlWhere = $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
$total = (int) db_rows("SELECT COUNT(*) AS n FROM dbo.lyiweb_contact_requests $sqlWhere", $params)[0]['n'];
$pages = max(1, (int) ceil($total / CONTACTS_PER_PAGE));
$pageNo = min($pageNo, $pages);
// SQL Server 2012 compat 100: no OFFSET/FETCH → ROW_NUMBER()
$rows = db_rows(
    "SELECT * FROM (SELECT id, name, company, email, phone, product, LEFT(message, 140) AS snippet, status, created_at,
            ROW_NUMBER() OVER (ORDER BY created_at DESC, id DESC) AS rn
       FROM dbo.lyiweb_contact_requests $sqlWhere) x WHERE rn BETWEEN ? AND ? ORDER BY rn",
    [...$params, ($pageNo - 1) * CONTACTS_PER_PAGE + 1, $pageNo * CONTACTS_PER_PAGE]
);
$counts = [];
foreach (db_rows('SELECT status, COUNT(*) AS n FROM dbo.lyiweb_contact_requests GROUP BY status') as $r) {
    $counts[$r['status']] = (int) $r['n'];
}
$tabs = ['open' => ['ยังไม่ดำเนินการ', ($counts['new'] ?? 0) + ($counts['read'] ?? 0)], 'new' => ['ใหม่', $counts['new'] ?? 0],
         'done' => ['ดำเนินการแล้ว', $counts['done'] ?? 0], 'spam' => ['สแปม', $counts['spam'] ?? 0], 'all' => ['ทั้งหมด', array_sum($counts)]];

admin_page_start('คำขอจากลูกค้า', 'contacts.php');
?>
<h1>คำขอจากลูกค้า</h1>
<p class="muted">ข้อความจากฟอร์ม "ขอใบเสนอราคา" ในหน้าติดต่อเรา · เปิดอ่านแล้วสถานะเปลี่ยนเป็น "อ่านแล้ว" เอง · ติดต่อกลับแล้วกด "ดำเนินการแล้ว"</p>
<nav class="tabs">
<?php foreach ($tabs as $s => [$label, $n]): ?>
  <a href="?status=<?= e($s) ?><?= $q !== '' ? '&amp;q=' . e(rawurlencode($q)) : '' ?>"<?= $s === $status ? ' class="on" aria-current="page"' : '' ?>><span><?= e($label) ?></span><small class="tab-n"><?= $n ?></small></a>
<?php endforeach; ?>
</nav>
<form method="get" class="toolbar" style="position:static">
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <input type="search" name="q" class="filter" value="<?= e($q) ?>" placeholder="ค้นหาชื่อ บริษัท อีเมล เบอร์ สินค้า หรือข้อความ">
  <button class="btn" type="submit">ค้นหา</button>
</form>

<section class="card">
<?php if ($rows === []): ?>
  <p class="muted">ไม่มีคำขอ<?= $q !== '' ? 'ที่ตรงกับ "' . e($q) . '"' : 'ในหมวดนี้' ?></p>
<?php else: ?>
  <table class="table rtable contacts">
    <thead><tr><th>วันที่</th><th>ผู้ติดต่อ</th><th>สินค้า / ข้อความ</th><th>สถานะ</th><th></th></tr></thead>
    <tbody>
<?php foreach ($rows as $r): ?>
      <tr class="<?= $r['status'] === 'new' ? 'is-new' : '' ?>">
        <td class="nowrap small muted"><?= e($fmt($r['created_at'])) ?></td>
        <td><a href="?id=<?= (int) $r['id'] ?>"><b><?= e($r['name']) ?></b></a><br><span class="small muted"><?= e(implode(' · ', array_filter([(string) $r['company'], (string) $r['email'], (string) $r['phone']]))) ?></span></td>
        <td class="small" data-label="ข้อความ"><?= $r['product'] ? '<b>' . e($r['product']) . '</b><br>' : '' ?><span class="muted"><?= e(mb_strimwidth(preg_replace('/\s+/u', ' ', (string) $r['snippet']), 0, 110, '…')) ?></span></td>
        <td data-label="สถานะ"><span class="chip status-<?= e($r['status']) ?>"><?= e(CONTACT_STATUSES[$r['status']] ?? $r['status']) ?></span></td>
        <td class="nowrap act"><a href="?id=<?= (int) $r['id'] ?>">เปิด</a></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php if ($pages > 1): ?>
  <nav class="pager">
<?php for ($i = 1; $i <= $pages; $i++): ?>
    <a href="?status=<?= e($status) ?>&amp;p=<?= $i ?><?= $q !== '' ? '&amp;q=' . e(rawurlencode($q)) : '' ?>"<?= $i === $pageNo ? ' class="on" aria-current="page"' : '' ?>><?= $i ?></a>
<?php endfor; ?>
  </nav>
<?php endif; ?>
<?php endif; ?>
</section>
<?php
admin_page_end();
