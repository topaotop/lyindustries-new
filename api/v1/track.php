<?php
declare(strict_types=1);

/**
 * POST api/v1/track.php — one click on an email / LINE / phone link (sent by assets/js/track.js
 * with navigator.sendBeacon) → lyiweb_channel_clicks. Always answers 204 so a visitor's click is
 * never slowed down or shown an error. The same visitor + channel + page within 10 s counts once.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once APP_ROOT . '/includes/lib/visitor.php';

const TRACK_MAX_PER_MIN = 30;

header('Cache-Control: no-store');
http_response_code(204);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$channel  = (string) ($_POST['channel'] ?? '');
$page     = (string) ($_POST['page'] ?? '');
$position = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_POST['position'] ?? ''))) ?? '';
$lang     = (string) ($_POST['lang'] ?? 'th');
if (!in_array($channel, ['email', 'line', 'tel'], true)          // 'form' is counted by api/v1/contact.php when saved
    || !in_array($page, ['home', 'about', 'catalog', 'contact'], true)) {
    exit;
}
$lang = in_array($lang, ['th', 'en'], true) ? $lang : 'th';

try {
    $visitor = visitor_hash();
    // same visitor + channel + page within 10 s counts once; more than 30 clicks a minute is a script
    $seen = db_rows(
        'SELECT COUNT(*) AS n,
                SUM(CASE WHEN channel = ? AND page = ? AND clicked_at > DATEADD(second, -10, GETDATE()) THEN 1 ELSE 0 END) AS dup
           FROM dbo.lyiweb_channel_clicks WHERE visitor = ? AND clicked_at > DATEADD(minute, -1, GETDATE())',
        [$channel, $page, $visitor]
    )[0];
    if ((int) $seen['dup'] === 0 && (int) $seen['n'] < TRACK_MAX_PER_MIN) {
        db_exec(
            'INSERT dbo.lyiweb_channel_clicks (channel, page, position, lang, visitor, is_bot) VALUES (?, ?, ?, ?, ?, ?)',
            [$channel, $page, substr($position, 0, 50) ?: null, $lang, $visitor, is_bot_ua() ? 1 : 0]
        );
    }
} catch (Throwable $e) {
    error_log('[lyiweb] click not counted: ' . $e->getMessage());
}
