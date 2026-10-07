<?php
declare(strict_types=1);

/**
 * Loaded first by every admin/*.php page: bootstrap + auth + security headers + layout helpers.
 * Admin pages always read the DB directly (never the public content cache).
 */

require __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/access.php';

header('Cache-Control: no-store, max-age=0');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Robots-Tag: noindex, nofollow');

auth_session_start();

/**
 * Admin menu grouped by task: group label (null = no heading) => list of
 * [file, label, icon key, required permission (null = any logged-in user)].
 */
function admin_menu(): array
{
    return [
        null => [
            ['index.php', 'แดชบอร์ด', 'home', null],
        ],
        'เนื้อหาเว็บไซต์' => [
            ['blocks.php', 'ข้อความหน้าเว็บ', 'text', 'content.translate'],
            ['lists.php', 'รายการ & รูปภาพ', 'list', 'content.translate'],
            ['seo.php', 'SEO & AEO', 'search', 'seo'],
        ],
        'ลูกค้า' => [
            ['contacts.php', 'คำขอจากลูกค้า', 'inbox', 'contact.view'],
            ['clicks.php', 'สถิติการติดต่อ', 'chart', 'contact.view'],
        ],
        'ตั้งค่าเว็บไซต์' => [
            ['settings.php', 'ข้อมูลติดต่อ & ลิงก์', 'phone', 'settings.edit'],
        ],
        'ผู้ใช้งาน' => [
            ['users.php', 'ผู้ใช้ & สิทธิ์', 'users', 'users.manage'],
            ['roles.php', 'บทบาท (Role)', 'shield', 'users.manage'],
        ],
    ];
}

/** 20×20 line icons for the sidebar (stroke = currentColor). */
function admin_icon(string $key): string
{
    $paths = [
        'home'     => '<path d="M3 9.5 10 4l7 5.5V16a1 1 0 0 1-1 1h-3.5v-4.5h-5V17H4a1 1 0 0 1-1-1z"/>',
        'text'     => '<path d="M4 5h12M4 9.5h12M4 14h7"/>',
        'phone'    => '<path d="M5.5 3.5h2l1.2 3-1.6 1.1a8 8 0 0 0 4.3 4.3l1.1-1.6 3 1.2v2a1.5 1.5 0 0 1-1.6 1.5A12.5 12.5 0 0 1 4 5.1 1.5 1.5 0 0 1 5.5 3.5z"/>',
        'external' => '<path d="M11 4h5v5M16 4l-7 7M14 11.5V15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h3.5"/>',
        'search'   => '<circle cx="8.5" cy="8.5" r="5"/><path d="m12.3 12.3 4.2 4.2"/><path d="M6.5 8.5h4M8.5 6.5v4"/>',
        'inbox'    => '<path d="M3.5 11.5 5.6 5a1.5 1.5 0 0 1 1.4-1h6a1.5 1.5 0 0 1 1.4 1l2.1 6.5V15a1.5 1.5 0 0 1-1.5 1.5h-10A1.5 1.5 0 0 1 3.5 15z"/><path d="M3.5 11.5h3.6l1 2h3.8l1-2h3.6"/>',
        'chart'    => '<path d="M3.5 16.5h13"/><path d="M6 13.5v-3M10 13.5v-7M14 13.5v-5"/>',
        'list'     => '<rect x="3.5" y="4" width="4" height="4" rx="1"/><rect x="3.5" y="12" width="4" height="4" rx="1"/><path d="M10.5 6h6M10.5 14h6"/>',
        'menu'     => '<path d="M3.5 6h13M3.5 10h13M3.5 14h13"/>',
        'back'     => '<path d="M12 4.5 6.5 10l5.5 5.5"/>',
        'user'     => '<circle cx="10" cy="7" r="3.2"/><path d="M4 17a6 6 0 0 1 12 0"/>',
        'users'    => '<circle cx="8" cy="7" r="3"/><path d="M2.5 16.5a5.5 5.5 0 0 1 11 0"/><path d="M13 4.3a3 3 0 0 1 0 5.4M15 12a5.5 5.5 0 0 1 2.5 4.5"/>',
        'shield'   => '<path d="M10 2.8 16 5v4.6c0 3.7-2.5 6.4-6 7.6-3.5-1.2-6-3.9-6-7.6V5z"/><path d="m7.3 10 2 2 3.6-3.8"/>',
        // page tabs
        'factory'  => '<path d="M3 17V9l4 2.5V9l4 2.5V6h3v3l3-1.5V17z"/><path d="M3 17h14M7 14h1.5M11 14h1.5"/>',
        'tape'     => '<ellipse cx="8" cy="10" rx="5" ry="5"/><circle cx="8" cy="10" r="1.6"/><path d="M8 15h9v-3.2"/>',
        'mail'     => '<rect x="3" y="5" width="14" height="10.5" rx="1.5"/><path d="m3.5 6 6.5 5 6.5-5"/>',
        'footer'   => '<rect x="3.5" y="3.5" width="13" height="13" rx="1.5"/><path d="M3.5 12.5h13M6 14.5h3M11 14.5h3"/>',
    ];

    return '<svg class="ic" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         . ($paths[$key] ?? '') . '</svg>';
}

/** May the current user see this menu entry? (content.edit implies content.translate) */
function admin_can_see(?string $perm): bool
{
    if ($perm === 'seo') {   // SEO & AEO: anyone who may edit some page's SEO, or the site settings
        return can('settings.edit') || can('content.edit') || can('content.translate');
    }
    return $perm === null || can($perm) || ($perm === 'content.translate' && can('content.edit'));
}

/** Stop unless logged in (and holding $permission). */
function admin_require(?string $permission = null): array
{
    $user = auth_user();
    if ($user === null) {
        header('Location: login.php?next=' . rawurlencode(basename((string) $_SERVER['SCRIPT_NAME'])));
        exit;
    }
    if ($permission !== null && !can($permission) && !($permission === 'content.translate' && can('content.edit'))) {
        http_response_code(403);
        admin_page_start('ไม่มีสิทธิ์', '');
        echo '<div class="card"><h2>ไม่มีสิทธิ์ใช้งานหน้านี้</h2><p class="muted">ติดต่อผู้ดูแลระบบเพื่อขอสิทธิ์</p></div>';
        admin_page_end();
        exit;
    }

    return $user;
}

/** Reject POSTs without a valid CSRF token. */
function admin_check_post(): void
{
    // a POST bigger than post_max_size arrives empty — say so instead of "invalid token"
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        http_response_code(413);
        exit('ข้อมูลที่ส่งใหญ่เกินที่ server รับได้ (' . ini_get('post_max_size') . ') — ลองเลือกรูปให้น้อยลงต่อการบันทึกหนึ่งครั้ง');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valid()) {
        http_response_code(400);
        exit('Invalid or expired form token — please reload the page and try again.');
    }
}

function flash(?string $type = null, ?string $message = null): ?array
{
    if ($type !== null) {
        $_SESSION['lyiweb_flash'] = ['type' => $type, 'message' => (string) $message];
        return null;
    }
    $f = $_SESSION['lyiweb_flash'] ?? null;
    unset($_SESSION['lyiweb_flash']);

    return $f;
}

/** Quote requests not opened yet (sidebar badge); 0 when the table is unavailable. */
function admin_new_requests(): int
{
    try {
        return (int) db_rows("SELECT COUNT(*) AS n FROM dbo.lyiweb_contact_requests WHERE status = N'new'")[0]['n'];
    } catch (Throwable) {
        return 0;
    }
}

/** Environment label shown in the top bar so nobody edits production by mistake. */
function admin_env(): array
{
    $db = 'ไม่ทราบ';
    try {
        $db = (string) db_rows('SELECT DB_NAME() AS db')[0]['db'];
    } catch (Throwable) {
    }

    return ['db' => $db, 'is_prod' => $db === 'LYI'];
}

function admin_page_start(string $title, string $active): void
{
    $user = auth_user();
    $env = $user ? admin_env() : null;
    ?>
<!DOCTYPE html>
<html lang="th"<?= ($_COOKIE['lyiweb_side'] ?? '') === 'hidden' ? ' class="side-hidden"' : '' ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · LYI Website Admin</title>
<link rel="icon" type="image/svg+xml" href="../assets/img/brand/logo-lyi.svg">
<link rel="stylesheet" href="<?= e(asset('../assets/css/fonts.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/admin.css')) ?>">
</head>
<body>
<?php if ($user): ?>
<header class="topbar">
  <button class="nav-toggle" type="button" data-nav-toggle aria-controls="admin-nav" aria-expanded="false" aria-label="เมนู" title="ซ่อน/แสดงเมนู"><?= admin_icon('menu') ?></button>
  <a class="brand" href="index.php"><img src="../assets/img/brand/logo-lyi.svg" alt="" width="30" height="30"><span>LYI <span class="brand-web">Website </span><b>Admin</b></span></a>
  <span class="env <?= $env['is_prod'] ? 'env-prod' : 'env-test' ?>">DB: <?= e($env['db']) ?><?= $env['is_prod'] ? ' · PRODUCTION' : '<span class="env-note"> · ทดสอบ</span>' ?></span>
  <span class="spacer"></span>
  <a class="btn btn-ghost top-site" href="../index.php" target="_blank" rel="noopener"><?= admin_icon('external') ?><span>ดูหน้าเว็บไซต์</span></a>
</header>
<div class="shell">
  <div class="nav-backdrop" data-nav-close></div>
  <nav class="side" id="admin-nav" tabindex="-1" aria-label="เมนูหลังบ้าน">
<?php foreach (admin_menu() as $group => $links):
        $visible = array_filter($links, static fn(array $l): bool => admin_can_see($l[3]));
        if ($visible === []) {
            continue;
        } ?>
    <div class="side-group">
<?php if ($group !== '' && $group !== null): ?>
      <span class="side-label"><?= e((string) $group) ?></span>
<?php endif; ?>
<?php foreach ($visible as [$file, $label, $icon]):
        $badge = $file === 'contacts.php' ? admin_new_requests() : 0; ?>
      <a href="<?= e($file) ?>"<?= $active === $file ? ' class="on" aria-current="page"' : '' ?>><?= admin_icon($icon) ?><span><?= e($label) ?></span><?php if ($badge > 0): ?><small class="side-badge" title="คำขอใหม่ที่ยังไม่ได้เปิดอ่าน"><?= $badge ?></small><?php endif; ?></a>
<?php endforeach; ?>
    </div>
<?php endforeach; ?>
    <div class="side-user">
      <span class="who"><?= admin_icon('user') ?><span><?= e($user['name']) ?></span></span>
      <form method="post" action="logout.php"><?= csrf_field() ?><button class="btn btn-ghost" type="submit">ออกจากระบบ</button></form>
    </div>
  </nav>
  <main class="main">
<?php else: ?>
<div class="center">
<?php endif; ?>
<?php if ($f = flash()): ?>
    <div class="flash flash-<?= e($f['type']) ?>" data-flash><?= e($f['message']) ?></div>
<?php endif;
}

function admin_page_end(): void
{
    if (auth_user()) {
        echo "  </main>\n</div>\n";
    } else {
        echo "</div>\n";
    }
    echo '<script src="' . e(asset('assets/admin.js')) . "\" defer></script>\n</body>\n</html>\n";
}
