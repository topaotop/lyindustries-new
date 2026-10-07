<?php
declare(strict_types=1);

/**
 * Texts shared by every page (menus, buttons, contact-detail labels) — block page "site".
 * {phone} {ext} {open} {close} are filled in from ข้อมูลติดต่อ (lyiweb_settings) — keep them.
 *
 * @return array<string, string>
 */
return [
    // nav: top menu, side menu (☰) and shared buttons
    'site.nav.01' => 'หน้าแรก',
    'site.nav.02' => 'กระบวนการผลิต',
    'site.nav.03' => 'แคตาล็อกสินค้า',
    'site.nav.04' => 'เกี่ยวกับเรา',
    'site.nav.05' => 'ติดต่อเรา',
    'site.nav.06' => 'ขอใบเสนอราคา',
    'site.nav.07' => 'ติดต่อเรา / ขอใบเสนอราคา',
    'site.nav.08' => 'ติดต่อทีมฝ่ายขาย',
    'site.nav.09' => 'เมนู',
    'site.nav.10' => 'ปิดเมนู',
    // labels: contact details shown on every page
    'site.labels.01' => 'โทร:',
    'site.labels.02' => 'อีเมล:',
    'site.labels.03' => '{phone} ต่อ {ext}',
    'site.labels.04' => 'จันทร์ – ศุกร์: {open} – {close} น.',
    'site.labels.05' => 'เสาร์: {open} – {close} น.',
];
