<?php
declare(strict_types=1);

/**
 * Loaded first by every admin/*.php page: bootstrap + auth + security headers + layout helpers.
 * Admin pages always read the DB directly (never the public content cache).
 */

require __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';

header('Cache-Control: no-store, max-age=0');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Robots-Tag: noindex, nofollow');

auth_session_start();

/** Admin menu: [file, label, required permission (null = any logged-in user)]. */
function admin_menu(): array
{
    return [
        ['index.php',    'แดชบอร์ด',          null],
        ['blocks.php',   'ข้อความหน้าเว็บ & SEO', 'content.translate'],
        ['settings.php', 'ข้อมูลติดต่อ & ลิงก์',  'settings.edit'],
    ];
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
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · LYI Website Admin</title>
<link rel="icon" type="image/svg+xml" href="../assets/img/logo-lyi.svg">
<link rel="stylesheet" href="../assets/css/fonts.css">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<?php if ($user): ?>
<header class="topbar">
  <a class="brand" href="index.php"><img src="../assets/img/logo-lyi.svg" alt="" width="30" height="30"><span>LYI Website <b>Admin</b></span></a>
  <span class="env <?= $env['is_prod'] ? 'env-prod' : 'env-test' ?>">DB: <?= e($env['db']) ?><?= $env['is_prod'] ? ' · PRODUCTION' : ' · ทดสอบ' ?></span>
  <span class="spacer"></span>
  <a class="view-site" href="../index.php" target="_blank" rel="noopener">ดูหน้าเว็บ ↗</a>
  <span class="who"><?= e($user['name']) ?></span>
  <form method="post" action="logout.php"><?= csrf_field() ?><button class="btn btn-ghost" type="submit">ออกจากระบบ</button></form>
</header>
<div class="shell">
  <nav class="side">
<?php foreach (admin_menu() as [$file, $label, $perm]): ?>
<?php if ($perm === null || can($perm) || ($perm === 'content.translate' && can('content.edit'))): ?>
    <a href="<?= e($file) ?>"<?= $active === $file ? ' class="on"' : '' ?>><?= e($label) ?></a>
<?php endif; ?>
<?php endforeach; ?>
  </nav>
  <main class="main">
<?php else: ?>
<div class="center">
<?php endif; ?>
<?php if ($f = flash()): ?>
    <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endif;
}

function admin_page_end(): void
{
    if (auth_user()) {
        echo "  </main>\n</div>\n";
    } else {
        echo "</div>\n";
    }
    echo "<script src=\"assets/admin.js\" defer></script>\n</body>\n</html>\n";
}
