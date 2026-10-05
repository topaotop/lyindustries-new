<?php
declare(strict_types=1);

/**
 * Content for index.php. Edit the arrays here to change the homepage lists.
 */

/** Orange line-icon (24×24) as a data: URI for <img src>. */
function tile_icon(string $shapes): string
{
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='#f26b1d'"
         . " stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'>$shapes</svg>";

    return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
}

$icons = [
    'neck'   => tile_icon("<path d='M4 7c3 3 13 3 16 0'/><path d='M7 9l1 8h8l1-8'/>"),
    'band'   => tile_icon("<rect x='3' y='8' width='18' height='8' rx='2'/><path d='M3 12h18' stroke-dasharray='2.5 2.5'/>"),
    'stripe' => tile_icon("<path d='M4 20L13 4'/><path d='M9 20L18 4'/><path d='M14 20L21 7.5'/>"),
    'cord'   => tile_icon("<path d='M5.5 7c7-3 6 10 13 7'/><circle cx='5.5' cy='7' r='1.7'/><circle cx='18.5' cy='14' r='1.7'/>"),
    'collar' => tile_icon("<path d='M7 4v6a5 5 0 0 0 10 0V4'/><path d='M7 4h3M14 4h3'/>"),
    'cap'    => tile_icon("<path d='M3 12c0-4 4-6 9-6s9 2 9 6'/><path d='M3 12c0 2 4 3 9 3s9-1 9-3'/>"),
];

// Application segments served (no brand names by policy). Rendered twice for the seamless marquee.
$partnerLogos = [
    ['name' => 'SPORTSWEAR',  'tag' => 'PERFORMANCE'],
    ['name' => 'ACTIVEWEAR',  'tag' => 'STRETCH'],
    ['name' => 'COMPRESSION', 'tag' => 'SUPPORT'],
    ['name' => 'TEAMWEAR',    'tag' => 'DURABILITY'],
    ['name' => 'UNDERWEAR',   'tag' => 'COMFORT'],
    ['name' => 'FOOTWEAR',    'tag' => 'GRIP'],
    ['name' => 'OUTDOOR',     'tag' => 'WEBBING'],
    ['name' => 'LIFESTYLE',   'tag' => 'FASHION'],
];

// Exploded assembly tiles; dx/dy/rot are the fly-in offsets used by assets/js/home.js.
$tiles = [
    ['en' => 'BACK NECK TAPE',     'ic' => $icons['neck'],   'th' => 'เทปคอหลัง',                       'img' => 'assets/app-back-neck.jpg',  'dx' => -240, 'dy' => 120, 'rot' => -4, 'isMore' => false],
    ['en' => 'WAISTBAND',          'ic' => $icons['band'],   'th' => 'ขอบเอว',                          'img' => 'assets/app-waistband.jpg',  'dx' => -80,  'dy' => 140, 'rot' => 2,  'isMore' => false],
    ['en' => 'DECORATIVE TAPE',    'ic' => $icons['stripe'], 'th' => 'เทปตกแต่ง / แถบข้าง',              'img' => 'assets/app-decorative.jpg', 'dx' => 80,   'dy' => 140, 'rot' => -2, 'isMore' => false],
    ['en' => 'DRAWCORD',           'ic' => $icons['cord'],   'th' => 'เชือกรูด',                         'img' => 'assets/app-drawcord.jpg',   'dx' => 240,  'dy' => 120, 'rot' => 4,  'isMore' => false],
    ['en' => 'NECKLINE & ARMHOLE', 'ic' => $icons['collar'], 'th' => 'เทปกุ๊นคอและวงแขน',                'img' => 'assets/app-neckline.jpg',   'dx' => -240, 'dy' => 200, 'rot' => 3,  'isMore' => false],
    ['en' => 'UNDERBUST / BRA',    'ic' => $icons['collar'], 'th' => 'ยางยืดใต้อก / สปอร์ตบรา',           'img' => 'assets/app-underbust.jpg',  'dx' => -80,  'dy' => 220, 'rot' => -2, 'isMore' => false],
    ['en' => 'JACKET HEM / CUFF',  'ic' => $icons['band'],   'th' => 'ขอบชายเสื้อและปลายแขน',             'img' => 'assets/app-jacket-hem.jpg', 'dx' => 80,   'dy' => 220, 'rot' => 2,  'isMore' => false],
    ['en' => 'SPORTS ACCESSORIES', 'ic' => $icons['cap'],    'th' => 'ผ้าคาดหัว ริสแบนด์ และอุปกรณ์กีฬา', 'img' => '',                          'dx' => 240,  'dy' => 200, 'rot' => -3, 'isMore' => true],
];

// 6-step process journey (scroll-driven in assets/js/home.js — keep the count in sync there via data attributes).
$steps = [
    ['n' => '01', 'th' => 'เส้นด้าย & วัตถุดิบคุณภาพ', 'short' => 'เส้นด้าย', 'desc' => 'คัดเลือกเส้นด้าย ทั้งโพลีเอสเตอร์ ไนลอน และสแปนเดกซ์ ให้เหมาะกับโครงสร้าง ความยืดหยุ่น และสัมผัสที่ต้องการของแต่ละชิ้นงาน', 'tag' => 'OEKO-TEX® STANDARD', 'img' => 'https://lyindustries.com/img/bgvideo1.jpg'],
    ['n' => '02', 'th' => 'ทอ / ถัก / ถักเชือก', 'short' => 'ทอ/ถัก/ถักเชือก', 'desc' => 'เครื่องจักรรองรับโครงสร้างงานทอ งานถัก และงานถักเชือกหลากหลายรูปแบบ ตั้งแต่งานละเอียด เนื้อนุ่ม ไปจนถึงงานที่ต้องการรับแรงสูง', 'tag' => 'WEAVING · KNITTING · BRAIDING', 'img' => 'https://lyindustries.com/img/nl.jpg'],
    ['n' => '03', 'th' => 'โรงย้อมและห้องแล็บภายใน', 'short' => 'ย้อมสีและแล็บ', 'desc' => 'ระบบจ่ายสีย้อมอัตโนมัติ ควบคุมสูตรด้วยคอมพิวเตอร์ เทียบสีตามผ้าตัวอย่างและรหัส Pantone พร้อมจัดทำ Lab dip เพื่ออนุมัติสี ควบคุมคุณภาพสีตามมาตรฐานที่กำหนดในทุกล็อตการสั่งซื้อ', 'tag' => 'PANTONE MATCHING', 'img' => 'assets/dye-yarn-machine.png'], // file still missing — awaiting image from Pack
    ['n' => '04', 'th' => 'Finishing — งานตกแต่งสำเร็จ', 'short' => 'Finishing', 'desc' => 'งานสกรีน พิมพ์ Sublimation ทำปลายเชือก (Tipping) ด้วยซิลิโคน โลหะ และฟิล์ม เคลือบซิลิโคนกันลื่น ตัดตามความยาว และปั๊มนูน–จม ทั้งตัวอักษรและโลโก้ พร้อมนำไปเย็บประกอบบนชิ้นงาน', 'tag' => 'VERSATILE FINISHING', 'img' => 'https://lyindustries.com/img/BRAIDING.jpg'],
    ['n' => '05', 'th' => 'QC ตรวจสอบคุณภาพทุกล็อต', 'short' => 'QC ทุกล็อต', 'desc' => 'ตรวจสอบคุณภาพและทดสอบคุณสมบัติด้วยห้องแล็บภายใน ทั้งแรงดึง ความยืดหยุ่นหลังซัก และความคงทนของสี (Colorfastness) เพื่อให้ชิ้นงานผ่านเกณฑ์มาตรฐานที่กำหนดก่อนส่งมอบ', 'tag' => 'IN-HOUSE LAB & QC', 'img' => 'https://lyindustries.com/img/FINISHING.jpg'],
    ['n' => '06', 'th' => 'ส่งมอบตรงเวลา ซัพพลายเออร์เดียว', 'short' => 'ส่งมอบตรงเวลา', 'desc' => 'ซัพพลายเออร์รายเดียวรับผิดชอบคุณภาพตลอดสาย ลด lead time และตัดปัญหาความผิดพลาดในการประสานงานระหว่างโรงงานย่อย', 'tag' => 'RELIABLE DELIVERY', 'img' => 'https://lyindustries.com/img/CROCHET.jpg'],
];

// Color-lab swatches; the first one is selected on load.
$swatches = [
    ['name' => 'Flame Orange (CI)',  'hex' => '#ff5a1f', 'glow' => 'rgba(255,90,31,0.4)',  'code' => 'PANTONE 16-1454 TCX', 'text' => '#fff'],
    ['name' => 'Cyber Sport Blue',   'hex' => '#1d63ff', 'glow' => 'rgba(29,99,255,0.4)',  'code' => 'PANTONE 19-4052 TCX', 'text' => '#fff'],
    ['name' => 'Neon Acid Lime',     'hex' => '#bfff00', 'glow' => 'rgba(191,255,0,0.4)',  'code' => 'PANTONE 13-0630 TCX', 'text' => '#000'],
    ['name' => 'Stealth Obsidian',   'hex' => '#1c1b20', 'glow' => 'rgba(28,27,32,0.4)',   'code' => 'PANTONE 19-3911 TCX', 'text' => '#fff'],
    ['name' => 'Crimson Racing Red', 'hex' => '#e61e38', 'glow' => 'rgba(230,30,56,0.4)',  'code' => 'PANTONE 18-1662 TCX', 'text' => '#fff'],
];
$activeSwatch = $swatches[0];

$productCards = [
    ['th' => 'ยางยืด / สายยืด (Elastic Webbing)', 'en' => 'ELASTIC WEBBING', 'desc' => 'ยืดหยุ่นสม่ำเสมอ คืนรูปยอดเยี่ยม ไม่ย้วยหลังผ่านการซักนับร้อยครั้ง สำหรับขอบเอว สายบ่า และงาน activewear', 'spec' => '10mm – 120mm · Custom Elasticity', 'code' => 'PROD-01', 'img' => 'assets/prod-elastic.jpg?v=2'],
    ['th' => 'เทปทอ (Woven Tape)', 'en' => 'WOVEN TAPE', 'desc' => 'โครงสร้างแน่น ทนทานต่อแรงดึงสูง คงรูปได้ดีเยี่ยม สำหรับสายรัดกระเป๋า แถบตกแต่ง และชิ้นส่วนโครงสร้าง', 'spec' => 'High-Tensile Poly/Nylon', 'code' => 'PROD-02', 'img' => 'assets/prod-woven.jpg'],
    ['th' => 'เทปถัก Raschel / Crochet', 'en' => 'RASCHEL & CROCHET', 'desc' => 'น้ำหนักเบา ผิวสัมผัสนุ่มเป็นพิเศษ ระบายอากาศได้ดี เหมาะสำหรับ overlay และชิ้นงานสัมผัสผิวหนังโดยตรง', 'spec' => 'Soft-Touch · Breathable Mesh', 'code' => 'PROD-03', 'img' => 'assets/prod-knit.jpg'],
    ['th' => 'เชือก เชือกยางยืด (Cords & Elastic Cords)', 'en' => 'CORDS & ELASTIC CORDS', 'desc' => 'เชือกกลม เชือกแบน เชือกยางยืด ถักเปีย พร้อมงาน tipping หัวเชือกครบทุกเทคนิค (ซิลิโคนจุ่ม, โลหะสลักโลโก้, ฟิล์มหด)', 'spec' => 'Silicone / Metal / Shrink Tube', 'code' => 'PROD-04', 'img' => 'assets/prod-cord.jpg'],
    ['th' => 'ขอบเอว (Engineered Waistbands)', 'en' => 'WAISTBANDS', 'desc' => 'จุดที่ผู้สวมใส่รู้สึกในทุกวินาที ควบคุมทั้งความนุ่มนวลต่อผิวและแรงกระชับที่พอดีตัวสำหรับกางเกงกีฬา', 'spec' => 'Jacquard / Brushed Soft Finish', 'code' => 'PROD-05', 'img' => 'assets/prod-waistband.jpg?v=2'],
    ['th' => 'งาน Finish หลากหลายแบบ (Finishing & Branding)', 'en' => 'FINISHING', 'desc' => 'ต่อยอดเทปให้ครบทั้งฟังก์ชันและแบรนด์ — ซิลิโคนกันลื่น พิมพ์ลาย heat transfer ปั๊มนูน เลเซอร์ ตัดร้อน/ตัดเย็น ไปจนถึงงานป้ายเลเบล เลือกผสมได้ตามการใช้งาน', 'spec' => 'Silicone / Print / Emboss / Laser / Labels', 'code' => 'PROD-06', 'img' => 'assets/prod-finishing.jpg'],
];

// Inspiration Hub samples. 'img' is empty until real product photos arrive — a placeholder is shown instead.
$galleryItems = [
    ['code' => 'LY2086',  'type' => 'Elastic Jacquard',     'placeholder' => 'LY2086 Elastic',    'img' => ''],
    ['code' => 'RLY1319', 'type' => 'Raschel Knit Tape',    'placeholder' => 'RLY1319 Raschel',   'img' => ''],
    ['code' => 'RLY1452', 'type' => 'Braided Cord Tipped',  'placeholder' => 'RLY1452 Cord',      'img' => ''],
    ['code' => 'LY2101',  'type' => 'Silicone Grip Tape',   'placeholder' => 'LY2101 Silicone',   'img' => ''],
    ['code' => 'RLY1377', 'type' => 'Engineered Waistband', 'placeholder' => 'RLY1377 Waistband', 'img' => ''],
    ['code' => 'LY2144',  'type' => 'Woven High-Tensile',   'placeholder' => 'LY2144 Woven',      'img' => ''],
];

// FAQ accordion; the first item starts open.
$faqList = [
    ['q' => 'สั่งผลิตขั้นต่ำ (MOQ) อยู่ที่เท่าไหร่?', 'a' => 'เรารองรับการสั่งผลิตจำนวนน้อยได้ ขั้นต่ำขึ้นอยู่กับประเภทชิ้นงาน โครงสร้าง และสี — ทั้งงานทดลองตลาด คอลเลกชันขนาดเล็ก ไปจนถึงออเดอร์ปริมาณมาก ส่วนงานพัฒนาใหม่ (R&D) เราจัดทำชิ้นงานตัวอย่าง (Sample Run) เพื่อทดสอบฟังก์ชันและสีก่อนผลิตจริงได้ ส่งแบบหรือโจทย์การใช้งานมาให้ทีมฝ่ายขายประเมินได้เลย'],
    ['q' => 'เทียบสีตามรหัส Pantone หรือตัวอย่างจริงได้ไหม?', 'a' => 'ได้แน่นอนครับ โรงงานมีโรงย้อมมาตรฐานในตัวและห้องแล็บเทียบสีอัตโนมัติ รองรับทั้งระบบ Pantone Matching System (TCX), การทำ Lab-dip และการเทียบสีตามชิ้นงานจริงของลูกค้า พร้อมการันตีค่าความต่างของสี (Delta-E) ให้อยู่ในเกณฑ์มาตรฐานที่แบรนด์กีฬาระดับโลกยอมรับ'],
    ['q' => 'ระยะเวลาในการพัฒนาตัวอย่างและผลิตจริงใช้เวลากี่วัน?', 'a' => 'การทำชิ้นงานตัวอย่าง (Sample Development) ใช้เวลาประมาณ 7 – 14 วันทำการหลังยืนยันแบบและเฉดสี สำหรับการผลิตล็อตจริงใช้เวลาประมาณ 20 – 30 วันทำการ ขึ้นอยู่กับปริมาณและความซับซ้อนของเทคนิคการตกแต่ง (Finishing)'],
    ['q' => 'รับบริการพัฒนาสินค้าตามแบบเฉพาะ (OEM / ODM) หรือไม่?', 'a' => 'รับครับ ทีมงาน R&D ของเรามีประสบการณ์กว่า 40 ปีในการร่วมงานกับดีไซเนอร์และผู้จัดการฝ่ายจัดซื้อของแบรนด์กีฬาระดับโลก ตั้งแต่การให้คำปรึกษาเรื่องโครงสร้างเส้นด้าย การเลือกใช้วัสดุรีไซเคิล (Recycled Yarn) การปรับแรงดึง ไปจนถึงการขึ้นตัวอย่างเพื่อทดสอบการเย็บจริง'],
];

// Mobile side-menu (☰).
$menuItems = [
    ['n' => '01', 'label' => 'หน้าแรก',                 'href' => '#hero'],
    ['n' => '02', 'label' => 'แคตาล็อกสินค้า',            'href' => 'catalog.php'],
    ['n' => '03', 'label' => 'TRIMRITE® ↗',             'href' => SITE_TRIMRITE_URL, 'external' => true],
    ['n' => '04', 'label' => 'เกี่ยวกับเรา',               'href' => 'about.php'],
    ['n' => '05', 'label' => 'ติดต่อเรา / ขอใบเสนอราคา',   'href' => 'contact.php'],
];
