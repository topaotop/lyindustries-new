<?php
declare(strict_types=1);

/**
 * Built-in copy for contact.php — fallback when lyiweb_blocks has no row/value, and the source
 * for docs/sql (php tools/build-seed-blocks.php). Keys: page.section.nn. Once seeded, edit text in the
 * admin, not here.
 *
 * @return array<string, string>
 */
return [
    // hero
    'contact.hero.01' => 'CONTACT L.Y. INDUSTRIES',
    'contact.hero.02' => 'ติดต่อเรา',

    // form
    'contact.form.01' => '01 — SEND AN INQUIRY',
    'contact.form.02' => 'ขอใบเสนอราคา / สอบถามข้อมูล',
    'contact.form.03' => 'กรอกรายละเอียดคร่าว ๆ ทีมขายจะติดต่อกลับภายใน 24 ชั่วโมงทำการ',
    'contact.form.04' => 'ชื่อ-นามสกุล *',
    'contact.form.05' => 'บริษัท / แบรนด์',
    'contact.form.06' => 'อีเมล *',
    'contact.form.07' => 'เบอร์โทร',
    'contact.form.08' => 'สินค้าที่สนใจ',
    'contact.form.09' => 'ยางยืด / Elastic',
    'contact.form.10' => 'เทปทอ / Woven Tape',
    'contact.form.11' => 'เทปถัก Raschel / Crochet',
    'contact.form.12' => 'เชือกรูด / Drawcord',
    'contact.form.13' => 'ขอบเอว / Waistband',
    'contact.form.14' => 'งานพิมพ์โลโก้ / Finishing',
    'contact.form.15' => 'อื่น ๆ',
    'contact.form.16' => 'รายละเอียด (ขนาด สี จำนวน การใช้งาน) *',
    'contact.form.17' => 'ส่งคำขอ →',
    'contact.form.18' => 'ADDRESS',
    'contact.form.19' => 'PHONE',
    'contact.form.20' => 'FAX',
    'contact.form.21' => 'EMAIL',
    'contact.form.22' => 'LINE',
    'contact.form.23' => 'HOURS',
    // quote form: result messages + note under the button (form saved to the database, 3c)
    'contact.form.24' => 'ส่งคำขอเรียบร้อยแล้ว',
    'contact.form.25' => 'ขอบคุณที่ติดต่อเรา ทีมขายจะติดต่อกลับโดยเร็วที่สุด',
    'contact.form.26' => 'ระบบส่งไม่สำเร็จ — กำลังเปิดอีเมลให้ส่งแทน',
    'contact.form.27' => 'กำลังส่ง…',
    'contact.form.28' => 'กรุณากรอกชื่อ อีเมล และรายละเอียดให้ครบถ้วน',
    'contact.form.29' => 'ส่งบ่อยเกินไป กรุณารอสักครู่แล้วลองใหม่',
    'contact.form.30' => 'มีรูปหรือไฟล์ tech pack? ส่งทาง LINE',
    'contact.form.31' => 'หรือแนบมากับอีเมลถึง',

    // map
    'contact.map.01' => '02 — LOCATION',
    'contact.map.02' => 'ที่อยู่ของเรา',
    'contact.map.03' => 'เปิดใน Google Maps ↗',
    'contact.map.04' => 'แผนที่ L.Y. Industries',
    // schema: name/description in the page data for Google (JSON-LD, not shown on the page)
    'contact.schema.01' => 'ติดต่อ L.Y. Industries',
];
