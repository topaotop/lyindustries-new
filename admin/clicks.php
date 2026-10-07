<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';

admin_require('contact.view');

/*
 * Contact-channel stats (lyiweb_channel_clicks): clicks on email / LINE / phone links counted by
 * assets/js/track.js, plus quote forms actually sent (api/v1/contact.php). Bots are left out unless
 * asked for. Only clicks are known — not whether the visitor really sent the email or called.
 */

const CLICK_CHANNELS = ['email' => 'อีเมล', 'line' => 'LINE', 'tel' => 'โทร', 'form' => 'ส่งฟอร์ม'];
const CLICK_PAGES = ['home' => 'หน้าแรก', 'about' => 'เกี่ยวกับเรา', 'catalog' => 'แคตตาล็อก', 'contact' => 'ติดต่อเรา'];
const CLICK_POSITIONS = ['side-menu' => 'เมนูด้านข้าง', 'header' => 'แถบเมนูบน', 'footer' => 'footer', 'quote-form' => 'ฟอร์มขอใบเสนอราคา',
                         'contact' => 'ส่วนติดต่อ', 'form' => 'ส่วนฟอร์ม/ข้อมูลติดต่อ', 'hero' => 'ส่วนบนสุด', 'page' => 'อื่นๆ'];

$today = new DateTimeImmutable('today');
$presets = ['7' => '7 วัน', '30' => '30 วัน', '90' => '90 วัน', 'month' => 'เดือนนี้', 'last-month' => 'เดือนที่แล้ว'];
$range = (string) ($_GET['range'] ?? '30');
$from = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($_GET['from'] ?? '')) ?: null;
$to = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($_GET['to'] ?? '')) ?: null;
if ($from && $to && $from <= $to) {
    $range = 'custom';
} else {
    [$from, $to] = match ($range) {
        '7'          => [$today->modify('-6 days'), $today],
        '90'         => [$today->modify('-89 days'), $today],
        'month'      => [$today->modify('first day of this month'), $today],
        'last-month' => [$today->modify('first day of last month'), $today->modify('last day of last month')],
        default      => [$today->modify('-29 days'), $today],
    };
    $range = isset($presets[$range]) ? $range : '30';
}
if ($from->diff($to)->days > 366) {
    $from = $to->modify('-366 days');
}
$withBots = isset($_GET['bots']);
$cond = 'clicked_at >= ? AND clicked_at < ?' . ($withBots ? '' : ' AND is_bot = 0');
$args = [$from->format('Y-m-d'), $to->modify('+1 day')->format('Y-m-d')];

// CSV: one row per day × channel × page × position × language
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db_rows(
        "SELECT CONVERT(char(10), clicked_at, 120) AS day, channel, page, ISNULL(position, '') AS position, lang, COUNT(*) AS clicks, COUNT(DISTINCT visitor) AS visitors
           FROM dbo.lyiweb_channel_clicks WHERE $cond
          GROUP BY CONVERT(char(10), clicked_at, 120), channel, page, ISNULL(position, ''), lang ORDER BY day, channel, page",
        $args
    );
    audit_log('export', 'lyiweb_channel_clicks', null, null, ['from' => $args[0], 'to' => $to->format('Y-m-d'), 'rows' => count($rows)]);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="lyi-contact-clicks-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");   // BOM so Excel reads Thai correctly
    fputcsv($out, ['date', 'channel', 'page', 'position', 'lang', 'clicks', 'unique_visitors']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['day'], $r['channel'], $r['page'], $r['position'], $r['lang'], $r['clicks'], $r['visitors']]);
    }
    exit;
}

$totals = array_fill_keys(array_keys(CLICK_CHANNELS), ['clicks' => 0, 'visitors' => 0]);
foreach (db_rows("SELECT channel, COUNT(*) AS clicks, COUNT(DISTINCT visitor) AS visitors FROM dbo.lyiweb_channel_clicks WHERE $cond GROUP BY channel", $args) as $r) {
    $totals[$r['channel']] = ['clicks' => (int) $r['clicks'], 'visitors' => (int) $r['visitors']];
}
$allVisitors = (int) db_rows("SELECT COUNT(DISTINCT visitor) AS n FROM dbo.lyiweb_channel_clicks WHERE $cond", $args)[0]['n'];
$byDay = [];
foreach (db_rows("SELECT CONVERT(char(10), clicked_at, 120) AS day, channel, COUNT(*) AS n FROM dbo.lyiweb_channel_clicks WHERE $cond GROUP BY CONVERT(char(10), clicked_at, 120), channel", $args) as $r) {
    $byDay[$r['day']][$r['channel']] = (int) $r['n'];
}
$byPage = db_rows("SELECT page, channel, COUNT(*) AS n FROM dbo.lyiweb_channel_clicks WHERE $cond GROUP BY page, channel", $args);
$byPos = db_rows("SELECT TOP 15 page, ISNULL(position, '') AS position, channel, COUNT(*) AS n FROM dbo.lyiweb_channel_clicks WHERE $cond GROUP BY page, ISNULL(position, ''), channel ORDER BY COUNT(*) DESC", $args);
$pageTable = [];
foreach ($byPage as $r) {
    $pageTable[$r['page']][$r['channel']] = (int) $r['n'];
}
$maxDay = max([1, ...array_map('array_sum', $byDay ?: [[0]])]);
$grand = array_sum(array_column($totals, 'clicks'));
$qs = static fn(array $over = []): string => http_build_query(array_merge(
    $range === 'custom' ? ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')] : ['range' => $range],
    $withBots ? ['bots' => 1] : [],
    $over
));

admin_page_start('สถิติการติดต่อ', 'clicks.php');
?>
<h1>สถิติการติดต่อ</h1>
<p class="muted">จำนวนครั้งที่คนกดอีเมล / LINE / โทร บนหน้าเว็บ และจำนวนฟอร์มขอใบเสนอราคาที่ส่งสำเร็จ · "คน" = ผู้เข้าชมไม่ซ้ำต่อวัน (ไม่เก็บ IP จริง) · นับได้แค่การกด ไม่รู้ว่าส่งอีเมล/โทรจริงหรือไม่</p>

<form method="get" class="toolbar clicks-bar" style="position:static">
  <nav class="subnav" aria-label="ช่วงเวลา" style="flex:0 1 auto">
<?php foreach ($presets as $k => $label): ?>
    <a class="btn btn-sm<?= $range === (string) $k ? ' on' : '' ?>" href="?<?= e(http_build_query(['range' => $k] + ($withBots ? ['bots' => 1] : []))) ?>"><?= e($label) ?></a>
<?php endforeach; ?>
  </nav>
  <label class="check">ตั้งแต่ <input type="date" name="from" value="<?= e($from->format('Y-m-d')) ?>" max="<?= e($today->format('Y-m-d')) ?>"></label>
  <label class="check">ถึง <input type="date" name="to" value="<?= e($to->format('Y-m-d')) ?>" max="<?= e($today->format('Y-m-d')) ?>"></label>
  <label class="check"><input type="checkbox" name="bots" value="1"<?= $withBots ? ' checked' : '' ?>> รวม bot</label>
  <button class="btn" type="submit">ดู</button>
  <a class="btn btn-ghost" href="?<?= e($qs(['export' => 'csv'])) ?>">ดาวน์โหลด CSV</a>
</form>
<p class="small muted"><?= e($from->format('d/m/Y')) ?> – <?= e($to->format('d/m/Y')) ?> · รวม <?= $grand ?> ครั้ง จาก <?= $allVisitors ?> คน</p>

<div class="stats">
<?php foreach (CLICK_CHANNELS as $ch => $label): ?>
  <div class="stat ch-<?= e($ch) ?>"><b><?= $totals[$ch]['clicks'] ?></b><span><?= e($label) ?> · <?= $totals[$ch]['visitors'] ?> คน</span></div>
<?php endforeach; ?>
</div>

<?php if ($grand === 0): ?>
<div class="card"><p class="muted">ยังไม่มีข้อมูลในช่วงนี้ — เริ่มนับตั้งแต่ติดตั้งระบบนับคลิก (คลิกจากหลังบ้าน/หน้าตัวอย่างไม่นับ)</p></div>
<?php else: ?>
<div class="grid2">
  <section class="card">
    <h2>แยกตามหน้า</h2>
    <table class="table rtable">
      <thead><tr><th>หน้า</th><?php foreach (CLICK_CHANNELS as $label): ?><th><?= e($label) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
<?php foreach (CLICK_PAGES as $pg => $label): if (!isset($pageTable[$pg])) { continue; } ?>
        <tr><td><b><?= e($label) ?></b></td><?php foreach (CLICK_CHANNELS as $ch => $cl): ?><td data-label="<?= e($cl) ?>"><?= $pageTable[$pg][$ch] ?? 0 ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </section>
  <section class="card">
    <h2>ปุ่มที่ถูกกดมากที่สุด</h2>
    <table class="table rtable">
      <thead><tr><th>ปุ่ม</th><th>ช่องทาง</th><th>ครั้ง</th></tr></thead>
      <tbody>
<?php foreach ($byPos as $r): ?>
        <tr><td><?= e(CLICK_PAGES[$r['page']] ?? $r['page']) ?> · <?= e(CLICK_POSITIONS[$r['position']] ?? ($r['position'] ?: '—')) ?></td><td data-label="ช่องทาง"><?= e(CLICK_CHANNELS[$r['channel']] ?? $r['channel']) ?></td><td data-label="ครั้ง"><?= (int) $r['n'] ?></td></tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>

<section class="card">
  <h2>รายวัน</h2>
  <div class="legend"><?php foreach (CLICK_CHANNELS as $ch => $label): ?><span class="ch-<?= e($ch) ?>"><i></i><?= e($label) ?></span><?php endforeach; ?></div>
  <div class="days">
<?php for ($d = $to; $d >= $from; $d = $d->modify('-1 day')): $key = $d->format('Y-m-d'); $row = $byDay[$key] ?? []; $sum = array_sum($row); ?>
    <div class="day<?= $sum === 0 ? ' is-zero' : '' ?>">
      <span class="day-date"><?= e($d->format('d/m')) ?></span>
      <span class="day-bar"><?php foreach (CLICK_CHANNELS as $ch => $label): if (!empty($row[$ch])): ?><i class="ch-<?= e($ch) ?>" style="width:<?= round($row[$ch] / $maxDay * 100, 2) ?>%" title="<?= e($label . ' ' . $row[$ch]) ?>"></i><?php endif; endforeach; ?></span>
      <span class="day-n"><?= $sum ?: '' ?></span>
    </div>
<?php endfor; ?>
  </div>
</section>
<?php endif; ?>
<?php
admin_page_end();
