<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';

$next = basename((string) ($_GET['next'] ?? $_POST['next'] ?? 'index.php'));
if (!preg_match('/^[a-z0-9_-]+\.php$/', $next)) {
    $next = 'index.php';
}
if (auth_user()) {
    header('Location: ' . $next);
    exit;
}

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_check_post();
    $username = (string) ($_POST['username'] ?? '');
    try {
        [$ok, $error] = auth_attempt($username, (string) ($_POST['password'] ?? ''));
        if ($ok) {
            header('Location: ' . $next);
            exit;
        }
    } catch (Throwable $e) {
        error_log('[lyiweb] login error: ' . $e->getMessage());
        $error = 'เชื่อมต่อฐานข้อมูลไม่ได้ กรุณาลองใหม่ภายหลัง';
    }
}

admin_page_start('เข้าสู่ระบบ', '');
?>
<form class="card login" method="post" action="login.php" autocomplete="on">
  <img src="../assets/img/logo-lyi.svg" alt="L.Y. Industries" width="56" height="56">
  <h1>LYI Website Admin</h1>
  <p class="muted">เข้าสู่ระบบด้วยบัญชีเดียวกับระบบอื่นของบริษัท</p>
  <?php if ($error !== ''): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <input type="hidden" name="next" value="<?= e($next) ?>">
  <label>ชื่อผู้ใช้<input name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus></label>
  <label>รหัสผ่าน<input name="password" type="password" autocomplete="current-password" required></label>
  <button class="btn btn-primary" type="submit">เข้าสู่ระบบ</button>
</form>
<?php
admin_page_end();
