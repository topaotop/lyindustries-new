<?php
declare(strict_types=1);

/**
 * Built-in copy for catalog.php — fallback when lyiweb_blocks has no row/value, and the source
 * for docs/sql (php tools/build-seed-blocks.php). Keys: page.section.nn. Once seeded, edit text in the
 * admin, not here.
 *
 * @return array<string, string>
 */
return [
    // hero
    'catalog.hero.01' => 'PRODUCT CATALOG',
    'catalog.hero.02' => 'แคตตาล็อกสินค้า',
    'catalog.hero.03' => 'Narrow Fabric & Trims ครบทุกประเภท ผลิตในโรงงานเดียวที่กรุงเทพฯ — ทุกรายการพัฒนาตามสเปกของแบรนด์ได้ ทั้งขนาด สี ความยืด และงาน finishing',

    // categories
    'catalog.categories.01' => '01 — PRODUCT CATEGORIES',
    'catalog.categories.02' => 'หมวดสินค้าหลัก',
    'catalog.categories.03' => 'PROD-01',
    'catalog.categories.04' => 'ELASTIC WEBBING',
    'catalog.categories.05' => 'ยางยืด / สายยืด (Elastic Webbing)',
    'catalog.categories.06' => 'สินค้าเพิ่มเติม →',
    'catalog.categories.07' => 'PROD-02',
    'catalog.categories.08' => 'WOVEN TAPE',
    'catalog.categories.09' => 'เทปทอ (Woven Tape)',
    'catalog.categories.10' => 'สินค้าเพิ่มเติม →',
    'catalog.categories.11' => 'PROD-03',
    'catalog.categories.12' => 'RASCHEL & CROCHET',
    'catalog.categories.13' => 'เทปถัก Raschel / Crochet',
    'catalog.categories.14' => 'สินค้าเพิ่มเติม →',
    'catalog.categories.15' => 'PROD-04',
    'catalog.categories.16' => 'CORDS & ELASTIC CORDS',
    'catalog.categories.17' => 'เชือก เชือกยางยืด (Cords & Elastic Cords)',
    'catalog.categories.18' => 'สินค้าเพิ่มเติม →',
    'catalog.categories.19' => 'PROD-05',
    'catalog.categories.20' => 'WAISTBANDS',
    'catalog.categories.21' => 'ขอบเอว (Engineered Waistbands)',
    'catalog.categories.22' => 'สินค้าเพิ่มเติม →',
    'catalog.categories.23' => 'PROD-06',
    'catalog.categories.24' => 'FINISHING',
    'catalog.categories.25' => 'งาน Finish ต่างๆ และพิมพ์โลโก้ (Finishing & Branding)',
    'catalog.categories.26' => 'สินค้าเพิ่มเติม →',

    // samples
    'catalog.samples.01' => '02 — SAMPLE SPECIMENS',
    'catalog.samples.02' => 'ตัวอย่างสินค้าของเรา',
    'catalog.samples.03' => 'ทุกชิ้นมีรหัสอ้างอิงเฉพาะ ขอตัวอย่างจริงเพื่อเทียบสัมผัส หรือสั่งพัฒนาต่อยอดได้ทันที',
    'catalog.samples.04' => 'LY2086',
    'catalog.samples.05' => 'Elastic Jacquard',
    'catalog.samples.06' => 'RLY1319',
    'catalog.samples.07' => 'Raschel Knit Tape',
    'catalog.samples.08' => 'RLY1452',
    'catalog.samples.09' => 'Braided Cord Tipped',
    'catalog.samples.10' => 'LY2101',
    'catalog.samples.11' => 'Silicone Grip Tape',
    'catalog.samples.12' => 'RLY1377',
    'catalog.samples.13' => 'Engineered Waistband',
    'catalog.samples.14' => 'LY2144',
    'catalog.samples.15' => 'Woven High-Tensile',
    'catalog.samples.16' => 'INSPIRATION HUB',
    'catalog.samples.17' => 'ดูสินค้าทั้งหมดแบบ 3D',
    'catalog.samples.18' => 'สำรวจแคตตาล็อกเต็มรูปแบบของเราบน Inspiration Hub หรือส่งสเปกมาให้ทีมช่วยเลือกวัสดุที่เหมาะกับงานของคุณ',
    'catalog.samples.19' => 'ดูแคตตาล็อกทั้งหมด ↗',
    'catalog.samples.20' => 'ขอตัวอย่าง / ใบเสนอราคา →',
    // alt: image descriptions (Google Images, screen readers)
    'catalog.alt.01' => 'ยางยืด / สายยืด (Elastic Webbing) — L.Y. Industries',
    'catalog.alt.02' => 'เทปทอ (Woven Tape) — L.Y. Industries',
    'catalog.alt.03' => 'เทปถัก Raschel / Crochet — L.Y. Industries',
    'catalog.alt.04' => 'เชือก เชือกยางยืด (Cords & Elastic Cords) — L.Y. Industries',
    'catalog.alt.05' => 'ขอบเอว (Engineered Waistbands) — L.Y. Industries',
    'catalog.alt.06' => 'งาน Finish ต่างๆ และพิมพ์โลโก้ (Finishing & Branding) — L.Y. Industries',
    'catalog.alt.07' => 'LY2086 Elastic Jacquard — ยางยืดทอลาย Jacquard',
    'catalog.alt.08' => 'RLY1319 Raschel Knit Tape — เทปถัก Raschel',
    'catalog.alt.09' => 'RLY1452 Braided Cord Tipped — เชือกถักเปียพร้อมหัวเชือก',
    'catalog.alt.10' => 'LY2101 Silicone Grip Tape — เทปซิลิโคนกันลื่น',
    'catalog.alt.11' => 'RLY1377 Engineered Waistband — ขอบเอวกางเกงกีฬา',
    'catalog.alt.12' => 'LY2144 Woven High-Tensile — เทปทอรับแรงดึงสูง',
    // schema: name/description in the page data for Google (JSON-LD, not shown on the page)
    'catalog.schema.01' => 'แคตตาล็อกสินค้า Narrow Fabric & Trims — L.Y. Industries',
    'catalog.schema.02' => 'ยางยืด / สายยืด (Elastic Webbing)',
    'catalog.schema.03' => 'ยืดหยุ่นสม่ำเสมอ คืนรูปยอดเยี่ยม ไม่ย้วยหลังผ่านการซักนับร้อยครั้ง สำหรับขอบเอว สายบ่า และงาน activewear',
    'catalog.schema.04' => 'เทปทอ (Woven Tape)',
    'catalog.schema.05' => 'โครงสร้างแน่น ทนทานต่อแรงดึงสูง คงรูปได้ดีเยี่ยม สำหรับสายรัดกระเป๋า แถบตกแต่ง และชิ้นส่วนโครงสร้าง',
    'catalog.schema.06' => 'เทปถัก Raschel / Crochet',
    'catalog.schema.07' => 'น้ำหนักเบา ผิวสัมผัสนุ่มเป็นพิเศษ ระบายอากาศได้ดี เหมาะสำหรับ overlay และชิ้นงานสัมผัสผิวหนังโดยตรง',
    'catalog.schema.08' => 'เชือก เชือกยางยืด (Cords & Elastic Cords)',
    'catalog.schema.09' => 'เชือกกลม เชือกแบน เชือกยางยืด ถักเปีย พร้อมงาน tipping หัวเชือกครบทุกเทคนิค (ซิลิโคนจุ่ม, โลหะสลักโลโก้, ฟิล์มหด)',
    'catalog.schema.10' => 'ขอบเอว (Engineered Waistbands)',
    'catalog.schema.11' => 'จุดที่ผู้สวมใส่รู้สึกในทุกวินาที ควบคุมทั้งความนุ่มนวลต่อผิวและแรงกระชับที่พอดีตัวสำหรับกางเกงกีฬา',
    'catalog.schema.12' => 'งาน Finish ต่างๆ และพิมพ์โลโก้ (Finishing & Branding)',
    'catalog.schema.13' => 'ต่อยอดเทปให้ครบทั้งฟังก์ชันและแบรนด์ — ซิลิโคนกันลื่น พิมพ์ลาย heat transfer ปั๊มนูน เลเซอร์ ตัดร้อน/ตัดเย็น ไปจนถึงงานป้ายเลเบล เลือกผสมได้ตามการใช้งาน',
];
