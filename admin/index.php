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

/* ---------- numbers ---------- */
// "now" from the DB server — PHP may run in UTC while DB dates are Bangkok time
$now = db_rows('SELECT GETDATE() AS n')[0]['n'];
$now = $now instanceof DateTimeInterface ? DateTimeImmutable::createFromInterface($now) : new DateTimeImmutable((string) $now);
$stats = db_rows(
    "SELECT
        (SELECT COUNT(*) FROM dbo.lyiweb_blocks) AS blocks,
        (SELECT COUNT(*) FROM dbo.lyiweb_items WHERE is_active = 1) AS items,
        (SELECT COUNT(*) FROM dbo.lyiweb_contact_requests WHERE status = N'new') AS new_requests"
)[0];
// translated = has English, or the text is already English (no Thai letters) — same rule as the editor
$stats['blocks_en'] = count(array_filter(db_rows('SELECT value_th, value_en FROM dbo.lyiweb_blocks'),
    static fn(array $r): bool => !needs_translation((string) $r['value_th'], (string) $r['value_en'])));
$enPct = $stats['blocks'] > 0 ? (int) floor($stats['blocks_en'] * 100 / $stats['blocks']) : 0;

// contact clicks per day, last 14 days (bots excluded) → this week vs the week before + sparkline
$days = [];
for ($i = 13; $i >= 0; $i--) {
    $days[$now->modify("-$i day")->format('Y-m-d')] = 0;
}
foreach (db_rows("SELECT CONVERT(date, clicked_at) AS d, COUNT(*) AS n FROM dbo.lyiweb_channel_clicks
                   WHERE is_bot = 0 AND clicked_at >= DATEADD(day, -13, CONVERT(date, GETDATE()))
                   GROUP BY CONVERT(date, clicked_at)") as $r) {
    $d = $r['d'] instanceof DateTimeInterface ? $r['d']->format('Y-m-d') : substr((string) $r['d'], 0, 10);
    if (isset($days[$d])) {
        $days[$d] = (int) $r['n'];
    }
}
$week = array_sum(array_slice($days, 7));
$prevWeek = array_sum(array_slice($days, 0, 7));

/* ---------- to-do: what is worth doing next ---------- */
$todo = [];
if (can('contact.view') && (int) $stats['new_requests'] > 0) {
    $todo[] = ['new', 'inbox', 'คำขอใบเสนอราคาใหม่ ' . (int) $stats['new_requests'] . ' รายการ รอเปิดอ่าน', 'contacts.php?status=new', 'เปิดอ่าน'];
}
if (admin_can_see('seo')) {
    $pageRows = db_rows('SELECT slug, title_th, meta_desc_th FROM dbo.lyiweb_pages');
    $out = static fn(string $s, array $r): bool => mb_strlen($s) < $r[0] || mb_strlen($s) > $r[1];
    $badTitle = count(array_filter($pageRows, static fn($p) => $out((string) $p['title_th'], SEO_TITLE_RANGE)));
    $badDesc = count(array_filter($pageRows, static fn($p) => $out((string) $p['meta_desc_th'], SEO_DESC_RANGE)));
    if ($badTitle + $badDesc > 0) {
        $parts = array_filter([$badTitle > 0 ? "ชื่อหน้า $badTitle หน้า" : '', $badDesc > 0 ? "คำอธิบาย $badDesc หน้า" : '']);
        $todo[] = ['warn', 'search', implode(' และ', $parts) . ' ยาวหรือสั้นกว่าที่ Google แนะนำ', 'seo.php?tab=meta', 'ปรับ'];
    }
    $kwSet = count(array_filter(array_keys($pageRows), static fn($i) => site(seo_kw_key((string) $pageRows[$i]['slug'], 'th')) !== ''));
    if ($kwSet < count($pageRows)) {
        $todo[] = ['warn', 'search', 'ยังไม่ได้ตั้งคำค้นหาหลัก (Keyword) ' . (count($pageRows) - $kwSet) . ' หน้า', 'seo.php?tab=meta', 'ตั้ง'];
    }
    $notEn = count(array_filter(array_keys(SITE_PAGE_FILES), static fn($s) => !page_translated($s)));
    if ($notEn > 0 && admin_can_see('content.translate')) {
        $todo[] = ['warn', 'globe', "หน้าภาษาอังกฤษยังแปลไม่ครบ $notEn หน้า (Google ยังไม่เก็บหน้านั้น)", 'blocks.php', 'แปล'];
    }
}
if (can('content.edit') || can('content.translate')) {
    $schema = require APP_ROOT . '/includes/schema/lists.php';
    $pending = 0;
    $pendingList = null;
    foreach ($schema as $key => $def) {
        $imgFields = array_keys(array_filter($def['fields'], static fn($f) => $f['type'] === 'image'));
        [$scopePage, $scopeSec] = array_pad(explode('.', (string) ($def['scope'] ?? $key)), 2, '');
        if ($imgFields === [] || !can_content('th', $scopePage, $scopeSec)) {
            continue;   // pictures are changed by content editors of that part only
        }
        foreach (content_list($key, 'th') as $item) {
            if (!empty($item['more'])) {
                continue;   // the "more categories" tile has no picture by design
            }
            foreach ($imgFields as $f) {
                if (($item[$f] ?? '') === '') {
                    $pending++;
                    $pendingList ??= $key;
                }
            }
        }
    }
    if ($pending > 0) {
        $todo[] = ['warn', 'list', "รูปที่ยังเป็น \"Image pending\" $pending รูป", 'lists.php?list=' . rawurlencode((string) $pendingList), 'ใส่รูป'];
    }
}
if (can('settings.edit') && site('verify_google') === '') {
    $todo[] = ['info', 'search', 'ยังไม่ได้เชื่อม Google Search Console (ทำตอนขึ้นเว็บจริง)', 'seo.php?tab=console', 'ดูวิธี'];
}

/* ---------- recent edits, in words people use ---------- */
$recent = db_rows(
    "SELECT TOP 40 a.created_at, a.action, a.entity, a.entity_id, u.name, u.username
       FROM dbo.lyiweb_audit_log a
       LEFT JOIN dbo.sysmnuser u ON u.id = a.user_id
      WHERE a.action NOT IN ('login', 'export')
      ORDER BY a.id DESC"
);
$pages = admin_content_pages();
$lists = require APP_ROOT . '/includes/schema/lists.php';
$verb = static fn(string $a): string => ['create' => 'เพิ่ม', 'update' => 'แก้', 'delete' => 'ลบ'][$a] ?? $a;
/** @return array{0: string, 1: string, 2: ?string} icon, text, link */
$describe = static function (array $r) use ($pages, $lists, $verb): array {
    $id = (string) $r['entity_id'];
    switch ($r['entity']) {
        case 'lyiweb_blocks':
            [$pg, $sec] = array_pad(explode('.', $id), 2, '');
            $where = ($pages[$pg]['label'] ?? $pg) . (isset($pages[$pg]['sections'][$sec]) ? ' › ' . $pages[$pg]['sections'][$sec] : '');
            return ['text', 'แก้ข้อความ · ' . $where, 'blocks.php?page=' . rawurlencode($pg)];
        case 'lyiweb_items':
            $key = explode('#', $id)[0];
            return ['list', $verb((string) $r['action']) . 'รายการ · ' . ($lists[$key]['title'] ?? $key), 'lists.php?list=' . rawurlencode($key)];
        case 'lyiweb_media':
            return ['list', 'อัปโหลดรูป', null];
        case 'lyiweb_pages':
            [$pg, $col] = array_pad(explode('.', $id, 2), 2, '');
            $what = str_starts_with($col, 'title') ? 'ชื่อหน้า' : (str_starts_with($col, 'meta_desc') ? 'คำอธิบาย' : (str_starts_with($col, 'og_image') ? 'รูปแชร์ลิงก์' : $col));
            return ['search', 'แก้ SEO · ' . ($pages[$pg]['label'] ?? $pg) . ' (' . $what . ')', 'seo.php?tab=' . ($what === 'รูปแชร์ลิงก์' ? 'share' : 'meta')];
        case 'lyiweb_settings':
            return match (true) {
                str_starts_with($id, 'seo_kw_') => ['search', 'แก้คำค้นหาหลัก (Keyword)', 'seo.php?tab=meta'],
                str_starts_with($id, 'verify_') => ['search', 'แก้โค้ด Google Search Console', 'seo.php?tab=console'],
                $id === 'og_image' => ['search', 'เปลี่ยนรูปแชร์ลิงก์หลัก', 'seo.php?tab=share'],
                (bool) preg_match('/^(org_|addr_|geo_|social_)/', $id) => ['search', 'แก้ข้อมูลธุรกิจสำหรับ Google', 'seo.php?tab=business'],
                default => ['phone', 'แก้ข้อมูลติดต่อ & ลิงก์', 'settings.php'],
            };
        case 'lyiweb_user_roles':
            return ['users', 'แก้สิทธิ์ผู้ใช้', 'users.php'];
        case 'lyiweb_roles':
        case 'lyiweb_role_scopes':
            return ['shield', $verb((string) $r['action']) . 'บทบาท (Role)', 'roles.php'];
        case 'lyiweb_contact_requests':
            return ['inbox', 'อัปเดตคำขอจากลูกค้า', 'contacts.php' . ($id !== '' ? '?id=' . rawurlencode($id) : '')];
        case 'cache':
            return ['db', 'ล้าง cache หน้าเว็บ', null];
        default:
            return ['text', $verb((string) $r['action']) . ' · ' . $r['entity'], null];
    }
};
/** "5 นาทีที่แล้ว" for an age in seconds; older than a week → the date. */
$ago = static function (int $s, ?DateTimeInterface $at = null): string {
    $s = max(0, $s);
    return match (true) {
        $s < 60 => 'เมื่อสักครู่',
        $s < 3600 => intdiv($s, 60) . ' นาทีที่แล้ว',
        $s < 86400 => intdiv($s, 3600) . ' ชั่วโมงที่แล้ว',
        $s < 7 * 86400 => intdiv($s, 86400) . ' วันที่แล้ว',
        default => $at?->format('d/m/Y') ?? '',
    };
};
// one line per run of the same edit by the same person (saving a page writes one row per field)
$feed = [];
foreach ($recent as $r) {
    [$icon, $text, $href] = $describe($r);
    $who = (string) ($r['name'] ?: $r['username'] ?: '—');
    $at = $r['created_at'] instanceof DateTimeInterface ? $r['created_at'] : new DateTimeImmutable((string) $r['created_at']);
    $last = array_key_last($feed);
    if ($last !== null && $feed[$last]['text'] === $text && $feed[$last]['who'] === $who) {
        $feed[$last]['n']++;
        continue;
    }
    if (count($feed) === 8) {
        break;
    }
    $feed[] = ['icon' => $icon, 'text' => $text, 'href' => $href, 'who' => $who, 'at' => $at, 'n' => 1];
}

/* ---------- shortcuts the user may open ---------- */
$shortcuts = array_values(array_filter([
    ['blocks.php?page=home', 'text', 'แก้ข้อความหน้าแรก', 'content.translate'],
    ['lists.php', 'list', 'รายการ & รูปภาพ', 'content.translate'],
    ['seo.php', 'search', 'SEO & AEO', 'seo'],
    ['contacts.php', 'inbox', 'คำขอจากลูกค้า', 'contact.view'],
    ['clicks.php', 'chart', 'สถิติการติดต่อ', 'contact.view'],
    ['settings.php', 'phone', 'ข้อมูลติดต่อ & ลิงก์', 'settings.edit'],
], static fn(array $s): bool => admin_can_see($s[3])));

$cacheFile = content_cache_file();
$cacheAge = is_file($cacheFile) ? time() - filemtime($cacheFile) : null;
$labels = array_map(static fn(array $l): string => $l[0], admin_permission_labels());
$scopes = auth_content_scopes();
$thDays = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
$thMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
$today = 'วัน' . $thDays[(int) $now->format('w')] . 'ที่ ' . $now->format('j') . ' ' . $thMonths[(int) $now->format('n')] . ' ' . ((int) $now->format('Y') + 543);

// sparkline points (0..100 × 0..28)
$max = max(1, max($days));
$pts = [];
foreach (array_values($days) as $i => $n) {
    $pts[] = round($i * 100 / 13, 1) . ',' . round(28 - $n * 24 / $max, 1);
}

admin_page_start('แดชบอร์ด', 'index.php');
?>
<header class="dash-head">
  <p class="dash-date"><?= e($today) ?></p>
  <h1>สวัสดี, <?= e($user['name']) ?></h1>
  <p class="muted">ภาพรวมเว็บไซต์ L.Y. Industries และสิ่งที่ควรทำต่อ</p>
</header>

<div class="kpis">
  <?= can('contact.view') ? '<a class="kpi kpi-brand" href="contacts.php?status=new">' : '<div class="kpi kpi-brand">' ?>
    <span class="kpi-ic"><?= admin_icon('inbox') ?></span>
    <span class="kpi-label">คำขอใบเสนอราคาใหม่</span>
    <b class="kpi-num"><?= (int) $stats['new_requests'] ?></b>
    <span class="kpi-sub"><?= (int) $stats['new_requests'] > 0 ? 'รอเปิดอ่าน' : 'อ่านครบแล้ว' ?></span>
  <?= can('contact.view') ? '</a>' : '</div>' ?>
<?php if (can('contact.view')): ?>
  <a class="kpi" href="clicks.php?range=7">
    <span class="kpi-ic"><?= admin_icon('chart') ?></span>
    <span class="kpi-label">กดติดต่อ 7 วัน</span>
    <b class="kpi-num"><?= $week ?></b>
    <span class="kpi-sub"><?php if ($prevWeek > 0): $chg = (int) round(($week - $prevWeek) * 100 / $prevWeek); ?><span class="trend trend-<?= $chg >= 0 ? 'up' : 'down' ?>"><?= $chg >= 0 ? '▲' : '▼' ?> <?= abs($chg) ?>%</span> จาก 7 วันก่อน<?php else: ?>อีเมล · LINE · โทร · ฟอร์ม<?php endif; ?></span>
    <svg class="spark" viewBox="0 0 100 30" preserveAspectRatio="none" aria-hidden="true"><polygon points="0,30 <?= implode(' ', $pts) ?> 100,30"/><polyline points="<?= implode(' ', $pts) ?>"/></svg>
  </a>
<?php endif; ?>
<?php $kpiTag = admin_can_see('content.translate') ? 'a' : 'div'; ?>
  <<?= $kpiTag ?> class="kpi"<?= $kpiTag === 'a' ? ' href="blocks.php"' : '' ?>>
    <span class="kpi-ic"><?= admin_icon('globe') ?></span>
    <span class="kpi-label">แปลภาษาอังกฤษแล้ว</span>
    <b class="kpi-num"><?= $enPct ?>%</b>
    <span class="kpi-sub"><?= (int) $stats['blocks_en'] ?> / <?= (int) $stats['blocks'] ?> ข้อความ</span>
    <span class="kpi-bar"><i style="width:<?= $enPct ?>%"></i></span>
  </<?= $kpiTag ?>>
  <<?= $kpiTag ?> class="kpi"<?= $kpiTag === 'a' ? ' href="lists.php"' : '' ?>>
    <span class="kpi-ic"><?= admin_icon('list') ?></span>
    <span class="kpi-label">เนื้อหาบนเว็บ</span>
    <b class="kpi-num"><?= (int) $stats['blocks'] ?></b>
    <span class="kpi-sub">ข้อความ · <?= (int) $stats['items'] ?> รายการ (สินค้า, FAQ, ขั้นตอน …)</span>
  </<?= $kpiTag ?>>
</div>

<div class="dash-grid">
  <div class="dash-main">
    <section class="card">
      <h2>สิ่งที่ควรทำ</h2>
<?php if ($todo === []): ?>
      <p class="todo-done"><?= admin_icon('shield') ?> เรียบร้อยดี — ไม่มีอะไรค้าง</p>
<?php else: ?>
      <ul class="todo">
<?php foreach ($todo as [$level, $icon, $text, $href, $cta]): ?>
        <li class="todo-<?= e($level) ?>"><span class="todo-ic"><?= admin_icon($icon) ?></span><span class="todo-text"><?= e($text) ?></span><a class="btn btn-sm" href="<?= e($href) ?>"><?= e($cta) ?> →</a></li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
    </section>

    <section class="card">
      <h2>แก้ไขล่าสุด</h2>
<?php if ($feed === []): ?>
      <p class="muted">ยังไม่มีการแก้ไข</p>
<?php else: ?>
      <ul class="feed">
<?php foreach ($feed as $f): ?>
        <li>
          <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($f['who'], 0, 1))) ?></span>
          <span class="feed-body">
            <span class="feed-what"><?= admin_icon($f['icon']) ?><?php if ($f['href'] !== null): ?><a href="<?= e($f['href']) ?>"><?= e($f['text']) ?></a><?php else: ?><?= e($f['text']) ?><?php endif; ?><?php if ($f['n'] > 1): ?> <span class="chip chip-dim">×<?= $f['n'] ?></span><?php endif; ?></span>
            <span class="feed-meta"><?= e($f['who']) ?> · <time title="<?= e($f['at']->format('d/m/Y H:i')) ?>"><?= e($ago($now->getTimestamp() - $f['at']->getTimestamp(), $f['at'])) ?></time></span>
          </span>
        </li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
    </section>
  </div>

  <aside class="dash-side">
<?php if ($shortcuts !== []): ?>
    <section class="card">
      <h2>ทางลัด</h2>
      <div class="shortcuts">
<?php foreach ($shortcuts as [$href, $icon, $label]): ?>
        <a href="<?= e($href) ?>"><?= admin_icon($icon) ?><span><?= e($label) ?></span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

    <section class="card">
      <h2>สิทธิ์ของคุณ</h2>
<?php if (auth_permissions() === []): ?>
      <p class="muted">ยังไม่มีสิทธิ์ใดๆ — ติดต่อผู้ดูแลระบบเพื่อขอสิทธิ์</p>
<?php else: ?>
      <div class="chips"><?php foreach (auth_permissions() as $p): ?><span class="chip"><?= e($labels[$p] ?? $p) ?></span><?php endforeach; ?></div>
<?php endif; ?>
<?php if ($scopes['edit'] !== [] && !in_array('*', $scopes['edit'], true)): ?>
      <p class="muted small">แก้เนื้อหาได้ที่: <?= e(admin_scope_summary($scopes['edit'])) ?></p>
<?php endif; ?>
<?php if (array_diff($scopes['translate'], $scopes['edit']) !== [] && !in_array('*', $scopes['translate'], true)): ?>
      <p class="muted small">แปลภาษาอังกฤษได้ที่: <?= e(admin_scope_summary($scopes['translate'])) ?></p>
<?php endif; ?>
    </section>

    <section class="card cache-card">
      <h2>ข้อมูลบนหน้าเว็บ (cache)</h2>
      <p class="small"><span class="dot dot-ok"></span><?= $cacheAge === null ? 'ยังไม่มี cache — หน้าเว็บอ่านจาก DB โดยตรง' : 'อัปเดตล่าสุด ' . e($ago($cacheAge)) ?></p>
      <p class="muted small">บันทึกในหลังบ้านแล้วหน้าเว็บเปลี่ยนทันที · ล้าง cache เฉพาะเมื่อแก้ตรงใน DB (เช่น Navicat)</p>
<?php if (can('content.edit') || can('settings.edit')): ?>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="clear_cache"><button class="btn btn-sm" type="submit"><?= admin_icon('refresh') ?> ล้าง cache ตอนนี้</button></form>
<?php endif; ?>
    </section>
  </aside>
</div>
<?php
admin_page_end();
