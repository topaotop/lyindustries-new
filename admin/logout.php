<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';

// POST + CSRF only, so a link or image elsewhere cannot log people out.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
    auth_logout();
}
header('Location: login.php');
