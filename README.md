# LY Website — FINAL (สำหรับ dev พัฒนาต่อ)

โฟลเดอร์นี้คือต้นแบบ final ของเว็บ L.Y. Industries 
ห้ามเปลี่ยนดีไซน์/เนื้อหาโดยไม่ได้รับอนุมัติจาก Pack

## ไฟล์ (PHP 8.4)
| ไฟล์ | คืออะไร |
|---|---|
| `index.php` | หน้าแรก — แตกมาจากไฟล์ bundle เดิม (`index.html` ~20MB) เป็น PHP ธรรมดา ไม่ต้องใช้ React/runtime แล้ว |
| `about.php` | เกี่ยวกับเรา |
| `catalog.php` | แคตาล็อกสินค้า |
| `contact.php` | ติดต่อเรา / ขอใบเสนอราคา |
| `includes/bootstrap.php` | ค่ากลาง (โทร, อีเมล, LINE, ลิงก์ภายนอก), เมนูหลัก, helper `e()` สำหรับ escape |
| `includes/home-data.php` | ข้อมูลรายการในหน้าแรก (ไทล์, ขั้นตอนผลิต, สินค้า, สี Pantone, ตัวอย่างสินค้า, FAQ, เมนูมือถือ) — แก้เนื้อหารายการที่นี่ |
| `includes/site-header.php` / `site-footer.php` | แถบเมนูบนและ footer ของหน้า about / catalog / contact |
| `assets/css/home.css`, `assets/js/home.js` | สไตล์และ interaction ของหน้าแรก (เมนู ☰, FAQ, เลือกสี, scroll animation) |
| `assets/css/fonts.css`, `assets/fonts/` | ฟอนต์ Anuphan / Kanit / JetBrains Mono แบบ self-host (เดิมฝังอยู่ใน bundle) |
| `assets/img/` | ภาพหน้าแรกที่แตกออกมาจาก bundle (R&D, เครื่องจ่ายสี, spectrophotometer, lab-dip) |
| `assets/` | ภาพประกอบ 13 ไฟล์ (app-*.jpg, prod-*.jpg) |
| `.htaccess` | `DirectoryIndex index.php`, redirect 301 จาก `*.html` เดิมไป `*.php`, กันการเปิด `includes/` ตรง |
| `DESIGN-LOCK.md` | สเปกดีไซน์ที่ล็อกแล้ว: navigation, หลักเนื้อหา, โครงแต่ละหน้า — **ยึดไฟล์นี้เป็นหลัก** |
| `CONTENT-DRAFT.md` | สรุปร่างเนื้อหา 6 หน้า (ฉบับก่อนล็อก ใช้อ้างอิงเนื้อหา/AEO) |

ต้องการ PHP 8.4 + Apache (mod_rewrite) — ไม่มี database ไม่มี dependency ภายนอก
## ลิงก์ระหว่างหน้า
- ทุกหน้าลิงก์กลับหน้าแรกด้วย `index.php` และ `index.php#process`
- TRIMRITE® → https://www.trimrite.com/ (เปิดแท็บใหม่)
- Inspiration Hub → https://www.lyindustries.com/lyinspirationhub/
- LINE → https://line.me/R/ti/p/@lyindustries · อีเมลในหน้า: sales@lyindustries.com · โทร 02-517-0768

## สิ่งที่ dev ต้องทำ/ระวัง
1. **ภาพหาย:** `index.php` (ข้อมูลใน `includes/home-data.php`) อ้าง `./assets/dye-yarn-machine.png` (กระบวนการผลิต ขั้น 03 ย้อมสี) แต่ยังไม่มีไฟล์นี้ — ต้องขอภาพจาก Pack
2. **ฟอร์ม** หน้า contact ยังเป็น `mailto:` — ต้องต่อระบบรับฟอร์มจริง
3. **YouTube video ID** หน้า About ยังเป็น placeholder
4. **เวลาทำการ** ยังไม่ยืนยัน (08:30–17:30 + ส. 08:30–12:00 หรือ 09:00–18:00)
5. ภาพขั้นตอนผลิตบางภาพใน `index.php` ดึงตรงจาก lyindustries.com/img/ — ควรย้ายมาเก็บใน `assets/`
6. ภาพจริงแต่ละหมวด/Facilities และรหัสสินค้าตัวอย่าง 6 รายการ ยังรอจาก Pack
7. `DESIGN-LOCK.md` กับ `CONTENT-DRAFT.md` ขัดกันเรื่องเมนู (ฉบับร่างมีหน้า /sample และ /process แยก) — ให้ยึดตาม DESIGN-LOCK
8. Schema: หน้า contact ใช้ ContactPage/LocalBusiness — คงไว้ตอนย้ายขึ้นระบบจริง (เป้าหมายโปรเจกต์คือ AEO สำหรับ Narrow Fabric)
