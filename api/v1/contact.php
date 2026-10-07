<?php
declare(strict_types=1);

/**
 * POST api/v1/contact.php — the quote request form (contact.php) → lyiweb_contact_requests.
 * JSON reply for the page script ({"ok":true} / {"ok":false,"error":…}); without JavaScript the
 * form posts here directly and is redirected back to contact.php?sent=1 (or ?err=1).
 * Spam guards: hidden honeypot field, signed load time (≥ 3 s), 5 requests / 10 min / IP.
 * Nobody is notified — the sales team reads requests in the admin (decision 6 Oct 2026).
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once APP_ROOT . '/includes/lib/visitor.php';

const CONTACT_MAX_PER_IP = 5;
const CONTACT_WINDOW_MIN = 10;

$wantsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
$reply = static function (bool $ok, string $error = '', int $code = 200) use ($wantsJson): never {
    if ($wantsJson) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => $ok, 'error' => $error], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ../../contact.php?' . ($ok ? 'sent=1' : 'err=1') . '#form', true, 303);
    }
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    $reply(false, 'method', 405);
}

$field = static fn(string $k, int $max, bool $multiline = false): string => mb_substr(trim(preg_replace(
    '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '',
    $multiline ? str_replace(["\r\n", "\r"], "\n", (string) ($_POST[$k] ?? '')) : str_replace(["\r", "\n"], ' ', (string) ($_POST[$k] ?? ''))
) ?? ''), 0, $max);

$data = [
    'name'    => $field('name', 150),
    'company' => $field('company', 200),
    'email'   => $field('email', 200),
    'phone'   => $field('phone', 50),
    'product' => $field('product', 200),
    'message' => $field('msg', 5000, true),
];

// honeypot filled or form sent faster than a person can type → pretend success, store nothing
$age = form_stamp_age((string) ($_POST['t'] ?? ''));
if (($_POST['website'] ?? '') !== '' || $age === null || $age < 3) {
    $reply(true);
}
if ($data['name'] === '' || $data['message'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $reply(false, 'invalid', 422);
}

try {
    $recent = (int) db_rows(
        'SELECT COUNT(*) AS n FROM dbo.lyiweb_contact_requests WHERE ip = ? AND created_at > DATEADD(minute, ?, GETDATE())',
        [visitor_ip(), -CONTACT_WINDOW_MIN]
    )[0]['n'];
    if ($recent >= CONTACT_MAX_PER_IP) {
        $reply(false, 'rate', 429);
    }
    // many links in the message is typical spam: keep it, but file it under "สแปม"
    $status = preg_match_all('#https?://#i', $data['message']) > 4 ? 'spam' : 'new';
    db_exec(
        'INSERT dbo.lyiweb_contact_requests (name, company, email, phone, product, message, lang, page, ip, user_agent, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$data['name'], $data['company'] ?: null, $data['email'], $data['phone'] ?: null, $data['product'] ?: null, $data['message'],
         lang(), 'contact', visitor_ip(), mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 400), $status]
    );
    // a sent form also counts in the contact-channel stats
    db_exec(
        'INSERT dbo.lyiweb_channel_clicks (channel, page, position, lang, visitor, is_bot) VALUES (?, ?, ?, ?, ?, ?)',
        ['form', 'contact', 'quote-form', lang(), visitor_hash(), is_bot_ua() ? 1 : 0]
    );
} catch (Throwable $e) {
    error_log('[lyiweb] contact form not saved: ' . $e->getMessage());
    $reply(false, 'server', 503);
}

$reply(true);
