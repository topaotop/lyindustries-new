<?php
declare(strict_types=1);

/**
 * Field definitions for every repeatable content list (lyiweb_items.list_key).
 *
 * - Field order = key order of the arrays handed to templates (the colour swatches are
 *   json_encode()d into a data attribute, so order matters).
 * - 'i18n' => true  : stored per language in data_th / data_en (empty English falls back to Thai)
 * - 'i18n' => false : stored once in data_common
 * - type 'image'    : path string in data_common; lyiweb_items.image_id (uploaded media) overrides it
 * - 'label'         : field name shown in the admin form
 * - 'css' => true   : value is used inside a style attribute (never marked in the admin preview)
 * - 'where'         : plain description of where the list shows on the page (admin help text;
 *                     a screenshot admin/assets/where/<name after the dot>.webp goes with it)
 * - 'scope'         : page.section the list belongs to — who may edit it follows that section's
 *                     role scope (lyiweb_role_scopes), same as the page text
 *
 * @return array<string, array{title: string, where: string, scope: string, fields: array<string, array{type: string, i18n: bool, label: string}>}>
 */
return [
    'home.partners' => [
        'title'  => 'หน้าแรก · แถบคำเลื่อนใต้ภาพหลัก',
        'where'  => "ใต้ภาพใหญ่ด้านบนสุด — แถบสีเข้มที่มีคำว่า SPORTSWEAR, ACTIVEWEAR … เลื่อนไปทางซ้ายเรื่อยๆ ใต้ข้อความ \"Trusted by global sportswear\" (ในกรอบสีส้ม)",
        'scope'  => 'home.trust',
        'fields' => [
            'name' => ['type' => 'text', 'i18n' => false, 'label' => 'กลุ่มสินค้า'],
            'tag'  => ['type' => 'text', 'i18n' => false, 'label' => 'ป้ายกำกับ'],
        ],
    ],
    'home.tiles' => [
        'title'  => 'หน้าแรก · การ์ดจุดใช้งานบนเสื้อผ้า',
        'where'  => "ส่วนที่ 01 \"ทุกจุดบนเสื้อผ้ากีฬาที่ต้องใช้ Trims\" — การ์ดรูป 8 ช่อง (เทปคอหลัง, ขอบเอว, เชือกรูด …) ที่บินเข้ามาตอนเลื่อนหน้าจอ",
        'scope'  => 'home.story',
        'fields' => [
            'label' => ['type' => 'text',  'i18n' => false, 'label' => 'ป้ายภาษาอังกฤษ (ตัวเล็กสีส้ม)'],
            'icon'  => ['type' => 'icon',  'i18n' => false, 'label' => 'ไอคอน'],
            'title' => ['type' => 'text',  'i18n' => true,  'label' => 'ชื่อ'],
            'img'   => ['type' => 'image', 'i18n' => false, 'label' => 'รูป'],
            'dx'    => ['type' => 'int',   'i18n' => false, 'label' => 'ระยะบินเข้า แนวนอน (px)'],
            'dy'    => ['type' => 'int',   'i18n' => false, 'label' => 'ระยะบินเข้า แนวตั้ง (px)'],
            'rot'   => ['type' => 'int',   'i18n' => false, 'label' => 'มุมหมุนตอนบินเข้า (องศา)'],
            'more'  => ['type' => 'bool',  'i18n' => false, 'label' => 'เป็นช่อง "อื่นๆ" (ลายไอคอน ไม่ใช้รูป)'],
        ],
    ],
    'home.steps' => [
        'title'  => 'หน้าแรก · ขั้นตอนการผลิต',
        'where'  => "ส่วนที่ 03 \"จากเส้นด้ายสู่ชิ้นงานสำเร็จ\" — เลื่อนหน้าจอแล้วขั้นตอนเปลี่ยนทีละขั้น พร้อมรูปพื้นหลังเต็มจอ และแถบชื่อขั้นตอนด้านล่าง",
        'scope'  => 'home.process',
        'fields' => [
            'n'     => ['type' => 'text',     'i18n' => false, 'label' => 'เลขขั้น'],
            'title' => ['type' => 'text',     'i18n' => true,  'label' => 'หัวข้อ'],
            'short' => ['type' => 'text',     'i18n' => true,  'label' => 'ชื่อสั้น (แถบด้านล่าง)'],
            'desc'  => ['type' => 'textarea', 'i18n' => true,  'label' => 'คำอธิบาย'],
            'tag'   => ['type' => 'text',     'i18n' => false, 'label' => 'ป้ายกำกับ'],
            'img'   => ['type' => 'image',    'i18n' => false, 'label' => 'รูปพื้นหลัง'],
        ],
    ],
    'home.swatches' => [
        'title'  => 'หน้าแรก · ปุ่มสี Pantone',
        'where'  => "ส่วนที่ 06 \"โรงย้อมมาตรฐาน สีตรงแม่นยำทุกล็อต\" — ปุ่มสี 5 สีทางขวา (ในกรอบสีส้ม) กดแล้วแถบตัวอย่างด้านบนเปลี่ยนสี",
        'scope'  => 'home.colorlab',
        'fields' => [
            'name' => ['type' => 'text',  'i18n' => false, 'label' => 'ชื่อสี'],
            'hex'  => ['type' => 'color', 'i18n' => false, 'label' => 'สี (hex)'],
            'glow' => ['type' => 'text',  'i18n' => false, 'label' => 'สีเงา (rgba)', 'css' => true],
            'code' => ['type' => 'text',  'i18n' => false, 'label' => 'รหัส Pantone'],
            'text' => ['type' => 'color', 'i18n' => false, 'label' => 'สีตัวอักษรบนแถบ'],
        ],
    ],
    'home.products' => [
        'title'  => 'หน้าแรก · การ์ดหมวดสินค้า',
        'where'  => "ส่วนที่ 04 \"Narrow Fabric & Trims ครบทุกประเภท\" — การ์ดรูปสินค้า 6 หมวด (ยางยืด, เทปทอ, เทปถัก …)",
        'scope'  => 'home.specimens',
        'fields' => [
            'title' => ['type' => 'text',     'i18n' => true,  'label' => 'ชื่อหมวด'],
            'label' => ['type' => 'text',     'i18n' => false, 'label' => 'ป้ายภาษาอังกฤษ'],
            'desc'  => ['type' => 'textarea', 'i18n' => true,  'label' => 'คำอธิบาย'],
            'spec'  => ['type' => 'text',     'i18n' => false, 'label' => 'สเปก'],
            'code'  => ['type' => 'text',     'i18n' => false, 'label' => 'รหัส'],
            'img'   => ['type' => 'image',    'i18n' => false, 'label' => 'รูป'],
        ],
    ],
    'home.gallery' => [
        'title'  => 'หน้าแรก · ตัวอย่างสินค้า (รหัส LY…)',
        'where'  => "ส่วนที่ 07 \"ตัวอย่างสินค้าของเรา\" — การ์ดรูปพร้อมรหัสสินค้า (ตอนนี้ยังเป็น Image pending รอใส่รูป)",
        'scope'  => 'home.gallery',
        'fields' => [
            'code' => ['type' => 'text',  'i18n' => false, 'label' => 'รหัสสินค้า'],
            'type' => ['type' => 'text',  'i18n' => false, 'label' => 'ประเภท'],
            'img'  => ['type' => 'image', 'i18n' => false, 'label' => 'รูป'],
        ],
    ],
    'home.faq' => [
        'title'  => 'หน้าแรก · คำถามที่พบบ่อย',
        'where'  => "ส่วนที่ 08 \"คำถามที่พบบ่อย\" — คำถามด้านขวาที่กดเปิดดูคำตอบได้ (ใช้ตอบคำถามบน Google ด้วย)",
        'scope'  => 'home.faq',
        'fields' => [
            'q' => ['type' => 'text',     'i18n' => true, 'label' => 'คำถาม'],
            'a' => ['type' => 'textarea', 'i18n' => true, 'label' => 'คำตอบ'],
        ],
    ],
];
