<?php
declare(strict_types=1);

/**
 * Helpers for the public endpoints (api/v1/*): server secret, anonymous visitor id, bot check.
 * Raw IP addresses are never stored for click stats — only a daily hash.
 */

function visitor_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

/** Random per-server secret kept in cache/secret.key (falls back to a derived value if not writable). */
function lyiweb_secret(): string
{
    static $secret = null;
    if ($secret !== null) {
        return $secret;
    }
    $file = APP_ROOT . '/cache/secret.key';
    $value = is_file($file) ? trim((string) file_get_contents($file)) : '';
    if (strlen($value) < 32) {
        $value = bin2hex(random_bytes(32));
        if (!is_dir(dirname($file))) {
            @mkdir(dirname($file), 0775, true);
        }
        if (@file_put_contents($file, $value, LOCK_EX) === false) {
            $value = hash('sha256', APP_ROOT . '|' . php_uname('n') . '|lyiweb');
        }
    }

    return $secret = $value;
}

/** Same visitor on the same day → same 16-char id; cannot be turned back into the IP. */
function visitor_hash(): string
{
    return substr(hash_hmac('sha256', visitor_ip() . '|' . date('Y-m-d'), lyiweb_secret()), 0, 16);
}

function is_bot_ua(?string $ua = null): bool
{
    $ua = $ua ?? (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

    return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|headless|lighthouse|curl|wget|python|httpclient|java\/|go-http|facebookexternalhit|preview/i', $ua);
}

/** Signed timestamp for the contact form (proves the form was loaded at least a few seconds earlier). */
function form_stamp(): string
{
    $t = (string) time();

    return $t . '.' . substr(hash_hmac('sha256', 'form|' . $t, lyiweb_secret()), 0, 20);
}

/** Seconds since the stamp was issued, or null when it is missing/forged. */
function form_stamp_age(string $stamp): ?int
{
    if (!preg_match('/^(\d{9,11})\.([0-9a-f]{20})$/', $stamp, $m)) {
        return null;
    }
    $ok = hash_equals(substr(hash_hmac('sha256', 'form|' . $m[1], lyiweb_secret()), 0, 20), $m[2]);

    return $ok ? time() - (int) $m[1] : null;
}
