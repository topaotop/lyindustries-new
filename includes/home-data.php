<?php
declare(strict_types=1);

/**
 * Built-in homepage content — used when the database (lyiweb_items) has no rows for a list
 * or cannot be reached, and as the source for docs/sql/002_lyiweb_seed_home.sql
 * (php tools/build-seed-home.php). Once the DB is seeded, edit content in the admin, not here.
 * Field names/order follow includes/schema/lists.php.
 *
 * @return array<string, list<array<string, mixed>>>
 */
return [
    // Application segments served (no brand names by policy). Rendered twice for the seamless marquee.
    'home.partners' => [
        ['name' => 'SPORTSWEAR', 'tag' => 'PERFORMANCE'],
        ['name' => 'ACTIVEWEAR', 'tag' => 'STRETCH'],
        ['name' => 'COMPRESSION', 'tag' => 'SUPPORT'],
        ['name' => 'TEAMWEAR', 'tag' => 'DURABILITY'],
        ['name' => 'UNDERWEAR', 'tag' => 'COMFORT'],
        ['name' => 'FOOTWEAR', 'tag' => 'GRIP'],
        ['name' => 'OUTDOOR', 'tag' => 'WEBBING'],
        ['name' => 'LIFESTYLE', 'tag' => 'FASHION'],
    ],
    // Exploded assembly tiles; dx/dy/rot are the fly-in offsets used by assets/js/home.js; icon = key in includes/icons.php.
    'home.tiles' => [
        ['label' => 'BACK NECK TAPE', 'icon' => 'neck', 'title' => 'เทปคอหลัง', 'img' => 'assets/app-back-neck.jpg', 'dx' => -240, 'dy' => 120, 'rot' => -4, 'more' => false],
        ['label' => 'WAISTBAND', 'icon' => 'band', 'title' => 'ขอบเอว', 'img' => 'assets/app-waistband.jpg', 'dx' => -80, 'dy' => 140, 'rot' => 2, 'more' => false],
        ['label' => 'DECORATIVE TAPE', 'icon' => 'stripe', 'title' => 'เทปตกแต่ง / แถบข้าง', 'img' => 'assets/app-decorative.jpg', 'dx' => 80, 'dy' => 140, 'rot' => -2, 'more' => false],
        ['label' => 'DRAWCORD', 'icon' => 'cord', 'title' => 'เชือกรูด', 'img' => 'assets/app-drawcord.jpg', 'dx' => 240, 'dy' => 120, 'rot' => 4, 'more' => false],
        ['label' => 'NECKLINE & ARMHOLE', 'icon' => 'collar', 'title' => 'เทปกุ๊นคอและวงแขน', 'img' => 'assets/app-neckline.jpg', 'dx' => -240, 'dy' => 200, 'rot' => 3, 'more' => false],
        ['label' => 'UNDERBUST / BRA', 'icon' => 'collar', 'title' => 'ยางยืดใต้อก / สปอร์ตบรา', 'img' => 'assets/app-underbust.jpg', 'dx' => -80, 'dy' => 220, 'rot' => -2, 'more' => false],
        ['label' => 'JACKET HEM / CUFF', 'icon' => 'band', 'title' => 'ขอบชายเสื้อและปลายแขน', 'img' => 'assets/app-jacket-hem.jpg', 'dx' => 80, 'dy' => 220, 'rot' => 2, 'more' => false],
        ['label' => 'SPORTS ACCESSORIES', 'icon' => 'cap', 'title' => 'ผ้าคาดหัว ริสแบนด์ และอุปกรณ์กีฬา', 'img' => '', 'dx' => 240, 'dy' => 200, 'rot' => -3, 'more' => true],
    ],
    // 6-step process journey; empty img shows placeholder.svg.
    'home.steps' => [
        ['n' => '01', 'title' => 'เส้นด้าย & วัตถุดิบคุณภาพ', 'short' => 'เส้นด้าย', 'desc' => 'คัดเลือกเส้นด้าย ทั้งโพลีเอสเตอร์ ไนลอน และสแปนเดกซ์ ให้เหมาะกับโครงสร้าง ความยืดหยุ่น และสัมผัสที่ต้องการของแต่ละชิ้นงาน', 'tag' => 'OEKO-TEX® STANDARD', 'img' => 'assets/img/process/bgvideo1.jpg'],
        ['n' => '02', 'title' => 'ทอ / ถัก / ถักเชือก', 'short' => 'ทอ/ถัก/ถักเชือก', 'desc' => 'เครื่องจักรรองรับโครงสร้างงานทอ งานถัก และงานถักเชือกหลากหลายรูปแบบ ตั้งแต่งานละเอียด เนื้อนุ่ม ไปจนถึงงานที่ต้องการรับแรงสูง', 'tag' => 'WEAVING · KNITTING · BRAIDING', 'img' => 'assets/img/process/nl.jpg'],
        ['n' => '03', 'title' => 'โรงย้อมและห้องแล็บภายใน', 'short' => 'ย้อมสีและแล็บ', 'desc' => 'ระบบจ่ายสีย้อมอัตโนมัติ ควบคุมสูตรด้วยคอมพิวเตอร์ เทียบสีตามผ้าตัวอย่างและรหัส Pantone พร้อมจัดทำ Lab dip เพื่ออนุมัติสี ควบคุมคุณภาพสีตามมาตรฐานที่กำหนดในทุกล็อตการสั่งซื้อ', 'tag' => 'PANTONE MATCHING', 'img' => ''],
        ['n' => '04', 'title' => 'Finishing — งานตกแต่งสำเร็จ', 'short' => 'Finishing', 'desc' => 'งานสกรีน พิมพ์ Sublimation ทำปลายเชือก (Tipping) ด้วยซิลิโคน โลหะ และฟิล์ม เคลือบซิลิโคนกันลื่น ตัดตามความยาว และปั๊มนูน–จม ทั้งตัวอักษรและโลโก้ พร้อมนำไปเย็บประกอบบนชิ้นงาน', 'tag' => 'VERSATILE FINISHING', 'img' => 'assets/img/process/BRAIDING.jpg'],
        ['n' => '05', 'title' => 'QC ตรวจสอบคุณภาพทุกล็อต', 'short' => 'QC ทุกล็อต', 'desc' => 'ตรวจสอบคุณภาพและทดสอบคุณสมบัติด้วยห้องแล็บภายใน ทั้งแรงดึง ความยืดหยุ่นหลังซัก และความคงทนของสี (Colorfastness) เพื่อให้ชิ้นงานผ่านเกณฑ์มาตรฐานที่กำหนดก่อนส่งมอบ', 'tag' => 'IN-HOUSE LAB & QC', 'img' => 'assets/img/process/FINISHING.jpg'],
        ['n' => '06', 'title' => 'ส่งมอบตรงเวลา ซัพพลายเออร์เดียว', 'short' => 'ส่งมอบตรงเวลา', 'desc' => 'ซัพพลายเออร์รายเดียวรับผิดชอบคุณภาพตลอดสาย ลด lead time และตัดปัญหาความผิดพลาดในการประสานงานระหว่างโรงงานย่อย', 'tag' => 'RELIABLE DELIVERY', 'img' => 'assets/img/process/CROCHET.jpg'],
    ],
    // Color-lab swatches; the first one is selected on load.
    'home.swatches' => [
        ['name' => 'Flame Orange (CI)', 'hex' => '#ff5a1f', 'glow' => 'rgba(255,90,31,0.4)', 'code' => 'PANTONE 16-1454 TCX', 'text' => '#fff'],
        ['name' => 'Cyber Sport Blue', 'hex' => '#1d63ff', 'glow' => 'rgba(29,99,255,0.4)', 'code' => 'PANTONE 19-4052 TCX', 'text' => '#fff'],
        ['name' => 'Neon Acid Lime', 'hex' => '#bfff00', 'glow' => 'rgba(191,255,0,0.4)', 'code' => 'PANTONE 13-0630 TCX', 'text' => '#000'],
        ['name' => 'Stealth Obsidian', 'hex' => '#1c1b20', 'glow' => 'rgba(28,27,32,0.4)', 'code' => 'PANTONE 19-3911 TCX', 'text' => '#fff'],
        ['name' => 'Crimson Racing Red', 'hex' => '#e61e38', 'glow' => 'rgba(230,30,56,0.4)', 'code' => 'PANTONE 18-1662 TCX', 'text' => '#fff'],
    ],
    // Product category cards.
    'home.products' => [
        ['title' => 'ยางยืด / สายยืด (Elastic Webbing)', 'label' => 'ELASTIC WEBBING', 'desc' => 'ยืดหยุ่นสม่ำเสมอ คืนรูปยอดเยี่ยม ไม่ย้วยหลังผ่านการซักนับร้อยครั้ง สำหรับขอบเอว สายบ่า และงาน activewear', 'spec' => '10mm – 120mm · Custom Elasticity', 'code' => 'PROD-01', 'img' => 'assets/prod-elastic.jpg?v=2'],
        ['title' => 'เทปทอ (Woven Tape)', 'label' => 'WOVEN TAPE', 'desc' => 'โครงสร้างแน่น ทนทานต่อแรงดึงสูง คงรูปได้ดีเยี่ยม สำหรับสายรัดกระเป๋า แถบตกแต่ง และชิ้นส่วนโครงสร้าง', 'spec' => 'High-Tensile Poly/Nylon', 'code' => 'PROD-02', 'img' => 'assets/prod-woven.jpg'],
        ['title' => 'เทปถัก Raschel / Crochet', 'label' => 'RASCHEL & CROCHET', 'desc' => 'น้ำหนักเบา ผิวสัมผัสนุ่มเป็นพิเศษ ระบายอากาศได้ดี เหมาะสำหรับ overlay และชิ้นงานสัมผัสผิวหนังโดยตรง', 'spec' => 'Soft-Touch · Breathable Mesh', 'code' => 'PROD-03', 'img' => 'assets/prod-knit.jpg'],
        ['title' => 'เชือก เชือกยางยืด (Cords & Elastic Cords)', 'label' => 'CORDS & ELASTIC CORDS', 'desc' => 'เชือกกลม เชือกแบน เชือกยางยืด ถักเปีย พร้อมงาน tipping หัวเชือกครบทุกเทคนิค (ซิลิโคนจุ่ม, โลหะสลักโลโก้, ฟิล์มหด)', 'spec' => 'Silicone / Metal / Shrink Tube', 'code' => 'PROD-04', 'img' => 'assets/prod-cord.jpg'],
        ['title' => 'ขอบเอว (Engineered Waistbands)', 'label' => 'WAISTBANDS', 'desc' => 'จุดที่ผู้สวมใส่รู้สึกในทุกวินาที ควบคุมทั้งความนุ่มนวลต่อผิวและแรงกระชับที่พอดีตัวสำหรับกางเกงกีฬา', 'spec' => 'Jacquard / Brushed Soft Finish', 'code' => 'PROD-05', 'img' => 'assets/prod-waistband.jpg?v=2'],
        ['title' => 'งาน Finish หลากหลายแบบ (Finishing & Branding)', 'label' => 'FINISHING', 'desc' => 'ต่อยอดเทปให้ครบทั้งฟังก์ชันและแบรนด์ — ซิลิโคนกันลื่น พิมพ์ลาย heat transfer ปั๊มนูน เลเซอร์ ตัดร้อน/ตัดเย็น ไปจนถึงงานป้ายเลเบล เลือกผสมได้ตามการใช้งาน', 'spec' => 'Silicone / Print / Emboss / Laser / Labels', 'code' => 'PROD-06', 'img' => 'assets/prod-finishing.jpg'],
    ],
    // Inspiration Hub samples; empty img shows placeholder.svg until photos are uploaded.
    'home.gallery' => [
        ['code' => 'LY2086', 'type' => 'Elastic Jacquard', 'img' => ''],
        ['code' => 'RLY1319', 'type' => 'Raschel Knit Tape', 'img' => ''],
        ['code' => 'RLY1452', 'type' => 'Braided Cord Tipped', 'img' => ''],
        ['code' => 'LY2101', 'type' => 'Silicone Grip Tape', 'img' => ''],
        ['code' => 'RLY1377', 'type' => 'Engineered Waistband', 'img' => ''],
        ['code' => 'LY2144', 'type' => 'Woven High-Tensile', 'img' => ''],
    ],
    // FAQ accordion; the first item starts open.
    'home.faq' => [
        ['q' => 'สั่งผลิตขั้นต่ำ (MOQ) อยู่ที่เท่าไหร่?', 'a' => 'เรารองรับการสั่งผลิตจำนวนน้อยได้ ขั้นต่ำขึ้นอยู่กับประเภทชิ้นงาน โครงสร้าง และสี — ทั้งงานทดลองตลาด คอลเลกชันขนาดเล็ก ไปจนถึงออเดอร์ปริมาณมาก ส่วนงานพัฒนาใหม่ (R&D) เราจัดทำชิ้นงานตัวอย่าง (Sample Run) เพื่อทดสอบฟังก์ชันและสีก่อนผลิตจริงได้ ส่งแบบหรือโจทย์การใช้งานมาให้ทีมฝ่ายขายประเมินได้เลย'],
        ['q' => 'เทียบสีตามรหัส Pantone หรือตัวอย่างจริงได้ไหม?', 'a' => 'ได้แน่นอนครับ โรงงานมีโรงย้อมมาตรฐานในตัวและห้องแล็บเทียบสีอัตโนมัติ รองรับทั้งระบบ Pantone Matching System (TCX), การทำ Lab-dip และการเทียบสีตามชิ้นงานจริงของลูกค้า พร้อมการันตีค่าความต่างของสี (Delta-E) ให้อยู่ในเกณฑ์มาตรฐานที่แบรนด์กีฬาระดับโลกยอมรับ'],
        ['q' => 'ระยะเวลาในการพัฒนาตัวอย่างและผลิตจริงใช้เวลากี่วัน?', 'a' => 'การทำชิ้นงานตัวอย่าง (Sample Development) ใช้เวลาประมาณ 7 – 14 วันทำการหลังยืนยันแบบและเฉดสี สำหรับการผลิตล็อตจริงใช้เวลาประมาณ 20 – 30 วันทำการ ขึ้นอยู่กับปริมาณและความซับซ้อนของเทคนิคการตกแต่ง (Finishing)'],
        ['q' => 'รับบริการพัฒนาสินค้าตามแบบเฉพาะ (OEM / ODM) หรือไม่?', 'a' => 'รับครับ ทีมงาน R&D ของเรามีประสบการณ์กว่า 40 ปีในการร่วมงานกับดีไซเนอร์และผู้จัดการฝ่ายจัดซื้อของแบรนด์กีฬาระดับโลก ตั้งแต่การให้คำปรึกษาเรื่องโครงสร้างเส้นด้าย การเลือกใช้วัสดุรีไซเคิล (Recycled Yarn) การปรับแรงดึง ไปจนถึงการขึ้นตัวอย่างเพื่อทดสอบการเย็บจริง'],
    ],
];
