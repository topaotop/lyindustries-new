<?php
declare(strict_types=1);

/**
 * Admin login against sysmnuser (company ERP users — READ ONLY, never written) + website
 * permissions from lyiweb_roles / lyiweb_role_permissions / lyiweb_user_roles.
 * Same password check as the company's other systems: plaintext in [pass] or [password],
 * compared with hash_equals(), or a password_hash() value via password_verify().
 */

const AUTH_MAX_FAILS       = 5;      // failed attempts for one username …
const AUTH_MAX_FAILS_IP    = 20;     // … or from one IP (higher: the whole office shares one public IP)
const AUTH_LOCK_MINUTES    = 15;     // … within this window lock the username / IP
const AUTH_IDLE_SECONDS    = 7200;   // log out after 2 hours without activity
const AUTH_ALL_PERMISSIONS = ['content.edit', 'content.translate', 'media.upload', 'contact.view', 'settings.edit', 'users.manage'];

function auth_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = ($_SERVER['HTTPS'] ?? '') !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    session_name('LYIWEBADMIN');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => rtrim(dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')), '/\\') . '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'cli'), 0, 45);
}

/**
 * @return array{0: bool, 1: string} [ok, error message]
 */
function auth_attempt(string $username, string $password): array
{
    $username = trim($username);
    if ($username === '' || $password === '') {
        return [false, 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน'];
    }

    $fails = db_rows(
        'SELECT SUM(CASE WHEN username = ? THEN 1 ELSE 0 END) AS by_user, SUM(CASE WHEN ip = ? THEN 1 ELSE 0 END) AS by_ip
           FROM dbo.lyiweb_login_attempts
          WHERE success = 0 AND attempted_at > DATEADD(minute, ?, GETDATE()) AND (username = ? OR ip = ?)',
        [$username, client_ip(), -AUTH_LOCK_MINUTES, $username, client_ip()]
    )[0];
    if ((int) $fails['by_user'] >= AUTH_MAX_FAILS || (int) $fails['by_ip'] >= AUTH_MAX_FAILS_IP) {
        return [false, 'ลองผิดหลายครั้งเกินไป กรุณารอ ' . AUTH_LOCK_MINUTES . ' นาทีแล้วลองใหม่'];
    }

    $row = db_rows(
        'SELECT TOP 1 id, username, name, department, [pass], [password], locked, [level]
           FROM dbo.sysmnuser WHERE username = ?',
        [$username]
    )[0] ?? null;

    $ok = false;
    $allowed = false;
    if ($row !== null && (int) ($row['locked'] ?? 0) !== 1) {
        foreach (['pass', 'password'] as $col) {
            $stored = (string) ($row[$col] ?? '');
            if ($stored !== '' && (hash_equals($stored, $password) || password_verify($password, $stored))) {
                $ok = true;
                break;
            }
        }
    }

    // only people picked in the admin (holding a role) may sign in; refused logins count as failures
    $allowed = $ok && auth_has_access((int) $row['id'], (int) ($row['level'] ?? 0));
    db_exec(
        'INSERT dbo.lyiweb_login_attempts (username, ip, success) VALUES (?, ?, ?)',
        [$username, client_ip(), $allowed ? 1 : 0]
    );

    if ($row !== null && (int) ($row['locked'] ?? 0) === 1) {
        return [false, 'บัญชีนี้ถูกล็อก กรุณาติดต่อ IT'];
    }
    if (!$ok) {
        return [false, 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'];
    }
    if (!$allowed) {
        return [false, 'บัญชีนี้ยังไม่ได้รับสิทธิ์เข้าหลังบ้านเว็บไซต์ — ติดต่อผู้ดูแลระบบ'];
    }

    session_regenerate_id(true);
    $_SESSION['lyiweb_user'] = [
        'id'         => (int) $row['id'],
        'username'   => (string) $row['username'],
        'name'       => (string) ($row['name'] ?: $row['username']),
        'department' => (string) ($row['department'] ?? ''),
        'level'      => (int) ($row['level'] ?? 0),
    ];
    $_SESSION['lyiweb_last'] = time();
    audit_log('login', 'session', (string) $row['id']);

    return [true, ''];
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** @return array{id: int, username: string, name: string, department: string, level: int}|null */
function auth_user(): ?array
{
    $user = $_SESSION['lyiweb_user'] ?? null;
    if ($user === null) {
        return null;
    }
    if (time() - (int) ($_SESSION['lyiweb_last'] ?? 0) > AUTH_IDLE_SECONDS) {
        auth_logout();
        return null;
    }
    // access removed (last role revoked) → signed out on the next request
    static $checked = false;
    if (!$checked) {
        $checked = true;
        if (!auth_has_access($user['id'], $user['level'])) {
            auth_logout();
            return null;
        }
    }
    $_SESSION['lyiweb_last'] = time();

    return $user;
}

/**
 * Permissions of the logged-in user, re-read from the DB on every request so a revoked role
 * takes effect immediately. sysmnuser.level >= setting admin_min_level = every permission (0 = off, the default since 009).
 *
 * @return list<string>
 */
function auth_permissions(): array
{
    static $perms = null;
    if ($perms !== null) {
        return $perms;
    }
    $user = auth_user();
    if ($user === null) {
        return $perms = [];
    }
    if (auth_is_full_admin($user['level'])) {
        return $perms = AUTH_ALL_PERMISSIONS;
    }
    $rows = db_rows(
        'SELECT DISTINCT rp.permission FROM dbo.lyiweb_user_roles ur
           JOIN dbo.lyiweb_role_permissions rp ON rp.role_id = ur.role_id
          WHERE ur.user_id = ?',
        [$user['id']]
    );

    return $perms = array_values(array_intersect(AUTH_ALL_PERMISSIONS, array_column($rows, 'permission')));
}

function can(string $permission): bool
{
    return in_array($permission, auth_permissions(), true);
}

/** sysmnuser.level at or above setting admin_min_level (0 = off) gets every permission on every section. */
function auth_admin_min_level(): int
{
    static $min = null;

    return $min ??= (int) (db_rows("SELECT value FROM dbo.lyiweb_settings WHERE setting_key = 'admin_min_level'")[0]['value'] ?? 0);
}

/** May this sysmnuser sign in? Needs at least one website role (or the level rule, when enabled). */
function auth_has_access(int $userId, int $level): bool
{
    return auth_is_full_admin($level)
        || db_rows('SELECT TOP 1 1 AS x FROM dbo.lyiweb_user_roles WHERE user_id = ?', [$userId]) !== [];
}

function auth_is_full_admin(int $level): bool
{
    $min = auth_admin_min_level();

    return $min > 0 && $level >= $min;
}

/**
 * Where the logged-in user may edit content, per permission (from lyiweb_role_scopes).
 * Scope = '*' (everything) | 'page' | 'page.section' — scopes only count together with the
 * content permission of the same role, so mixing roles never widens either one.
 *
 * @return array{edit: list<string>, translate: list<string>}
 */
function auth_content_scopes(): array
{
    static $scopes = null;
    if ($scopes !== null) {
        return $scopes;
    }
    $user = auth_user();
    if ($user === null) {
        return $scopes = ['edit' => [], 'translate' => []];
    }
    if (auth_is_full_admin($user['level'])) {
        return $scopes = ['edit' => ['*'], 'translate' => ['*']];
    }
    $scopes = ['edit' => [], 'translate' => []];
    $rows = db_rows(
        "SELECT DISTINCT rp.permission, s.scope FROM dbo.lyiweb_user_roles ur
           JOIN dbo.lyiweb_role_permissions rp ON rp.role_id = ur.role_id AND rp.permission IN ('content.edit', 'content.translate')
           JOIN dbo.lyiweb_role_scopes s ON s.role_id = ur.role_id
          WHERE ur.user_id = ?",
        [$user['id']]
    );
    foreach ($rows as $r) {
        $scopes[$r['permission'] === 'content.edit' ? 'edit' : 'translate'][] = (string) $r['scope'];
    }

    return $scopes;
}

function scope_covers(array $scopes, string $page, string $section): bool
{
    return in_array('*', $scopes, true) || in_array($page, $scopes, true) || in_array("$page.$section", $scopes, true);
}

/** May the user edit $lang ('th' | 'en') text of this page section? Editing implies translating. */
function can_content(string $lang, string $page, string $section): bool
{
    $s = auth_content_scopes();

    return scope_covers($s['edit'], $page, $section) || ($lang === 'en' && scope_covers($s['translate'], $page, $section));
}

/* ---- CSRF ---- */

function csrf_token(): string
{
    if (empty($_SESSION['lyiweb_csrf'])) {
        $_SESSION['lyiweb_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['lyiweb_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = (string) ($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    return $sent !== '' && hash_equals(csrf_token(), $sent);
}

/* ---- audit ---- */

function audit_log(string $action, string $entity, ?string $entityId, mixed $before = null, mixed $after = null): void
{
    $json = static fn(mixed $v): ?string => $v === null ? null : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    db_exec(
        'INSERT dbo.lyiweb_audit_log (user_id, action, entity, entity_id, before_json, after_json, ip) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [($_SESSION['lyiweb_user']['id'] ?? null), $action, $entity, $entityId, $json($before), $json($after), client_ip()]
    );
}
