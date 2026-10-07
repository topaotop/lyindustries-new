<?php
declare(strict_types=1);

/**
 * What can be granted: permission labels and the page → section tree that role scopes point at.
 * Used by blocks.php (what to show), roles.php (scope checkboxes) and users.php (summaries).
 */

/** @return array<string, array{0: string, 1: string}> permission => [label, description] */
function admin_permission_labels(): array
{
    return [
        'content.edit'      => ['แก้เนื้อหา', 'แก้ข้อความไทยและอังกฤษ เฉพาะหน้า/ส่วนที่กำหนดด้านล่าง'],
        'content.translate' => ['แปลภาษาอังกฤษ', 'แก้ได้เฉพาะภาษาอังกฤษ เฉพาะหน้า/ส่วนที่กำหนดด้านล่าง'],
        'media.upload'      => ['อัปโหลดรูป', 'อัปโหลด/เปลี่ยนรูปภาพ'],
        'contact.view'      => ['ดูข้อความติดต่อ', 'ดูคำขอใบเสนอราคาจากฟอร์มหน้าเว็บ'],
        'settings.edit'     => ['ตั้งค่าเว็บ', 'แก้ข้อมูลติดต่อ เบอร์โทร อีเมล ที่อยู่ ลิงก์'],
        'users.manage'      => ['จัดการผู้ใช้ & สิทธิ์', 'ให้/ถอนสิทธิ์ผู้ใช้ และแก้ role — เท่ากับผู้ดูแลระบบ ให้เฉพาะคนที่ไว้ใจ'],
    ];
}

/**
 * Editable pages and their sections, in page order. Sections come from the built-in text file
 * (includes/blocks/<page>.php) so a new section shows up without touching this list.
 *
 * @return array<string, array{label: string, url: string, icon: string, sections: array<string, string>}>
 */
function admin_content_pages(): array
{
    static $pages = null;
    if ($pages !== null) {
        return $pages;
    }
    $defs = [
        'home'    => ['หน้าแรก', 'index.php', 'home', [
            'hero' => 'ส่วนบนสุด (Hero)', 'trust' => 'แถบความน่าเชื่อถือ + ตัวเลข', 'story' => 'จุดใช้งานบนเสื้อผ้า', 'why' => 'ทำไมต้องเรา',
            'process' => 'ขั้นตอนการผลิต', 'specimens' => 'หมวดสินค้า', 'rnd' => 'บริการ R&D', 'colorlab' => 'โรงย้อม & Color Lab',
            'gallery' => 'ตัวอย่างสินค้า', 'faq' => 'คำถามที่พบบ่อย', 'contact' => 'ติดต่อ + footer หน้าแรก']],
        'about'   => ['เกี่ยวกับเรา', 'about.php', 'factory', [
            'hero' => 'ส่วนบนสุด', 'video' => 'วิดีโอ + ตัวเลข', 'story' => 'เรื่องราวบริษัท', 'facilities' => 'โรงงาน (Facilities)', 'cta' => 'ปุ่มท้ายหน้า + footer']],
        'catalog' => ['แคตตาล็อกสินค้า', 'catalog.php', 'tape', [
            'hero' => 'ส่วนบนสุด', 'categories' => 'หมวดสินค้าหลัก', 'samples' => 'ตัวอย่างสินค้า + Inspiration Hub']],
        'contact' => ['ติดต่อเรา', 'contact.php', 'mail', [
            'hero' => 'ส่วนบนสุด', 'form' => 'ฟอร์ม + ข้อมูลติดต่อ', 'map' => 'แผนที่']],
        'footer'  => ['Footer (แคตตาล็อก + ติดต่อ)', 'catalog.php#footer', 'footer', ['main' => 'Footer']],
    ];
    $pages = [];
    foreach ($defs as $slug => [$label, $url, $icon, $names]) {
        // 'seo' = page title + meta description (lyiweb_pages); footer is not a page of its own
        $sections = $slug === 'footer' ? [] : ['seo' => 'SEO (ชื่อหน้า + คำอธิบาย)'];
        $file = APP_ROOT . "/includes/blocks/$slug.php";
        foreach (is_file($file) ? array_keys(require $file) : [] as $full) {
            $sec = explode('.', $full)[1] ?? '';
            if ($sec !== '' && !isset($sections[$sec])) {
                $sections[$sec] = $names[$sec] ?? $sec;
            }
        }
        $pages[$slug] = ['label' => $label, 'url' => $url, 'icon' => $icon, 'sections' => $sections + $names];
    }

    return $pages;
}

/**
 * What kind of text each block is ("หัวข้อ", "ปุ่ม / ลิงก์", "ป้ายเล็ก" …), worked out from the element
 * that holds it in the page template, so editors see more than "hero.04".
 *
 * @return array<string, string> block key without the page prefix (e.g. "hero.04") => label
 */
function admin_block_kinds(string $slug): array
{
    $templates = ['home' => 'index.php', 'about' => 'about.php', 'catalog' => 'catalog.php', 'contact' => 'contact.php', 'footer' => 'includes/site-footer.php'];
    $src = isset($templates[$slug]) && is_file(APP_ROOT . '/' . $templates[$slug]) ? (string) file_get_contents(APP_ROOT . '/' . $templates[$slug]) : '';
    $kinds = [];
    if (!preg_match_all("/<\?= b\('" . preg_quote($slug, '/') . "\.([a-z]+\.\d+)'\) \?>/", $src, $m, PREG_OFFSET_CAPTURE)) {
        return $kinds;
    }
    foreach ($m[1] as [$key, $pos]) {
        $before = substr($src, max(0, $pos - 1500), min($pos, 1500));
        preg_match_all('/<([a-zA-Z][a-zA-Z0-9]*)\b([^>]*)>/', $before, $tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $tag = '';
        $attrs = '';
        $inLink = false;
        // nearest opening tag that is still open where the text sits (+ is it inside a link/button?)
        foreach (array_reverse($tags) as $t) {
            $name = strtolower($t[1][0]);
            if (in_array($name, ['br', 'img', 'input', 'meta', 'link', 'source', 'path', 'svg', 'circle', 'rect', 'line'], true)
                || stripos(substr($before, $t[0][1]), "</$name") !== false) {
                continue;
            }
            if ($tag === '') {
                [$tag, $attrs] = [$name, $t[2][0]];
            }
            if (in_array($name, ['a', 'button'], true)) {
                $inLink = true;
                break;
            }
            if (in_array($name, ['section', 'body', 'main', 'footer', 'form'], true)) {
                break;
            }
        }
        $size = preg_match('/font-size:\s*(\d+(?:\.\d+)?)px/', $attrs, $fs) ? (float) $fs[1] : (preg_match('/font-size:\s*clamp\((\d+)px/', $attrs, $fs) ? (float) $fs[1] : 0.0);
        $mono = str_contains($attrs, 'font-mono');
        $kinds[$key] = match (true) {
            $tag === 'h1' => 'หัวข้อหลัก',
            $tag === 'h2' => 'หัวข้อ',
            in_array($tag, ['h3', 'h4', 'h5', 'h6'], true) => 'หัวข้อย่อย',
            $inLink || in_array($tag, ['a', 'button'], true) => 'ปุ่ม / ลิงก์',
            $tag === 'label' => 'ชื่อช่องกรอก',
            $tag === 'option' => 'ตัวเลือก',
            $tag === 'li' => 'รายการ',
            in_array($tag, ['th', 'td'], true) => 'ตาราง',
            $size >= 26 => 'ตัวเลข / หัวข้อใหญ่',
            $mono || ($size > 0 && $size <= 12) => 'ป้ายเล็ก',
            in_array($tag, ['strong', 'b'], true) => 'ข้อความเน้น',
            default => 'ข้อความ',
        };
    }

    return $kinds;
}

/**
 * Clean a submitted scope list: keep only known scopes, drop what a wider scope already covers.
 *
 * @param list<mixed> $scopes
 * @return list<string>
 */
function admin_normalize_scopes(array $scopes): array
{
    $scopes = array_values(array_unique(array_map('strval', $scopes)));
    if (in_array('*', $scopes, true)) {
        return ['*'];
    }
    $out = [];
    foreach (admin_content_pages() as $slug => $page) {
        if (in_array($slug, $scopes, true)) {
            $out[] = $slug;
            continue;
        }
        foreach (array_keys($page['sections']) as $sec) {
            if (in_array("$slug.$sec", $scopes, true)) {
                $out[] = "$slug.$sec";
            }
        }
    }

    return $out;
}

/** Human summary of a scope list, e.g. "หน้าแรก: Hero, FAQ · เกี่ยวกับเรา (ทั้งหน้า)". */
function admin_scope_summary(array $scopes): string
{
    if (in_array('*', $scopes, true)) {
        return 'ทุกหน้า ทุกส่วน';
    }
    $parts = [];
    foreach (admin_content_pages() as $slug => $page) {
        if (in_array($slug, $scopes, true)) {
            $parts[] = $page['label'] . ' (ทั้งหน้า)';
            continue;
        }
        $secs = [];
        foreach ($page['sections'] as $sec => $label) {
            if (in_array("$slug.$sec", $scopes, true)) {
                $secs[] = $label;
            }
        }
        if ($secs !== []) {
            $parts[] = $page['label'] . ': ' . implode(', ', $secs);
        }
    }

    return $parts === [] ? 'ยังไม่ได้กำหนด' : implode(' · ', $parts);
}

/**
 * Housekeeping: people whose company account is locked (left the company) lose every website role,
 * so they drop off the "who can sign in" list and do not get access back if the account is unlocked.
 * Each removal is written to the audit log. Returns how many people were cleaned up.
 */
function admin_revoke_locked_users(): int
{
    $rows = db_rows(
        'SELECT ur.user_id, r.name_th FROM dbo.lyiweb_user_roles ur
           JOIN dbo.sysmnuser u ON u.id = ur.user_id
           JOIN dbo.lyiweb_roles r ON r.id = ur.role_id
          WHERE u.locked = 1'
    );
    $byUser = [];
    foreach ($rows as $r) {
        $byUser[(int) $r['user_id']][] = (string) $r['name_th'];
    }
    foreach ($byUser as $userId => $names) {
        db_exec('DELETE FROM dbo.lyiweb_user_roles WHERE user_id = ?', [$userId]);
        audit_log('update', 'lyiweb_user_roles', (string) $userId, $names, ['auto' => 'account locked in sysmnuser']);
    }

    return count($byUser);
}

/**
 * All roles with their permissions, scopes and member count.
 *
 * @return array<int, array{id: int, role_key: string, name_th: string, is_system: bool, perms: list<string>, scopes: list<string>, members: int}>
 */
function admin_roles(): array
{
    $roles = [];
    foreach (db_rows('SELECT r.id, r.role_key, r.name_th, r.is_system, (SELECT COUNT(*) FROM dbo.lyiweb_user_roles ur WHERE ur.role_id = r.id) AS members
                        FROM dbo.lyiweb_roles r ORDER BY r.is_system DESC, r.id') as $r) {
        $roles[(int) $r['id']] = ['id' => (int) $r['id'], 'role_key' => (string) $r['role_key'], 'name_th' => (string) $r['name_th'],
                                  'is_system' => (bool) $r['is_system'], 'perms' => [], 'scopes' => [], 'members' => (int) $r['members']];
    }
    foreach (db_rows('SELECT role_id, permission FROM dbo.lyiweb_role_permissions') as $r) {
        if (isset($roles[(int) $r['role_id']])) {
            $roles[(int) $r['role_id']]['perms'][] = (string) $r['permission'];
        }
    }
    foreach (db_rows('SELECT role_id, scope FROM dbo.lyiweb_role_scopes') as $r) {
        if (isset($roles[(int) $r['role_id']])) {
            $roles[(int) $r['role_id']]['scopes'][] = (string) $r['scope'];
        }
    }
    // show permissions in the fixed order
    foreach ($roles as &$role) {
        $role['perms'] = array_values(array_intersect(AUTH_ALL_PERMISSIONS, $role['perms']));
    }
    unset($role);

    return $roles;
}
