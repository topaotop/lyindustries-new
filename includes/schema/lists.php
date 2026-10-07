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
 * - 'scope'         : page.section the list belongs to — who may edit it follows that section's
 *                     role scope (lyiweb_role_scopes), same as the page text
 *
 * @return array<string, array{title: string, scope: string, fields: array<string, array{type: string, i18n: bool, label: string}>}>
 */
return [
    'home.partners' => [
        'title'  => 'หน้าแรก · แถบกลุ่มสินค้า (marquee)',
        'scope'  => 'home.trust',
        'fields' => [
            'name' => ['type' => 'text', 'i18n' => false, 'label' => 'กลุ่มสินค้า'],
            'tag'  => ['type' => 'text', 'i18n' => false, 'label' => 'ป้ายกำกับ'],
        ],
    ],
    'home.tiles' => [
        'title'  => 'หน้าแรก · ไทล์จุดใช้งานบนเสื้อผ้า',
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
        'title'  => 'หน้าแรก · สี Pantone ตัวอย่าง',
        'scope'  => 'home.colorlab',
        'fields' => [
            'name' => ['type' => 'text',  'i18n' => false, 'label' => 'ชื่อสี'],
            'hex'  => ['type' => 'color', 'i18n' => false, 'label' => 'สี (hex)'],
            'glow' => ['type' => 'text',  'i18n' => false, 'label' => 'สีเงา (rgba)'],
            'code' => ['type' => 'text',  'i18n' => false, 'label' => 'รหัส Pantone'],
            'text' => ['type' => 'color', 'i18n' => false, 'label' => 'สีตัวอักษรบนแถบ'],
        ],
    ],
    'home.products' => [
        'title'  => 'หน้าแรก · หมวดสินค้า',
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
        'title'  => 'หน้าแรก · ตัวอย่างสินค้า (Inspiration Hub)',
        'scope'  => 'home.gallery',
        'fields' => [
            'code' => ['type' => 'text',  'i18n' => false, 'label' => 'รหัสสินค้า'],
            'type' => ['type' => 'text',  'i18n' => false, 'label' => 'ประเภท'],
            'img'  => ['type' => 'image', 'i18n' => false, 'label' => 'รูป'],
        ],
    ],
    'home.faq' => [
        'title'  => 'หน้าแรก · คำถามที่พบบ่อย',
        'scope'  => 'home.faq',
        'fields' => [
            'q' => ['type' => 'text',     'i18n' => true, 'label' => 'คำถาม'],
            'a' => ['type' => 'textarea', 'i18n' => true, 'label' => 'คำตอบ'],
        ],
    ],
];
