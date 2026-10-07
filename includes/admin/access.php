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
