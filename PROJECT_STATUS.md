# PROJECT_STATUS.md — L.Y. Industries website

อัปเดตล่าสุด: 5 ต.ค. 2026 · branch `main` · dev: https://lysystems.sytes.net/lyindustries-dev/ (ภายใน http://192.168.0.70/lyindustries-dev/) · **version บน dev: v1.0.1** (ดู [DEPLOY_LOG.md](DEPLOY_LOG.md))

## ✅ งานที่ทำเสร็จแล้ว

| วันที่ | งาน | commit |
|---|---|---|
| 23 ก.ย. 2026 | ดีไซน์/เนื้อหา 4 หน้า LOCKED โดย Pack (ไฟล์ HTML ต้นแบบ) | — |
| 5 ต.ค. 2026 | นำไฟล์ต้นแบบ static HTML เข้า git | `dfa86e1` |
| 5 ต.ค. 2026 | แปลงเว็บเป็น PHP 8.4: แตก `index.html` (bundle 20MB) เป็น `index.php` ที่ render ฝั่ง server, ไม่ใช้ React/runtime แล้ว; ข้อมูลรายการย้ายไป `includes/home-data.php`; interaction เขียนใหม่เป็น vanilla JS | `c2db765` |
| 5 ต.ค. 2026 | แยกฟอนต์ 26 ไฟล์ + ภาพ 4 ภาพออกจาก bundle มาไว้ `assets/` | `c2db765` |
| 5 ต.ค. 2026 | หน้า about/catalog/contact → `.php` + header/footer partial (HTML ผลลัพธ์ตรงกับต้นฉบับทุกไบต์ ยกเว้นลิงก์ .html→.php) | `c2db765` |
| 5 ต.ค. 2026 | ใส่โลโก้ `logo-lyi.svg` ที่ nav ทุกหน้า + favicon + apple-touch-icon | `c2db765` |
| 5 ต.ค. 2026 | แก้ URL Inspiration Hub เป็น `lyinspirationhub` (8 ลิงก์) ใช้ค่าคงที่ `SITE_INSPIRATION_URL` | `c2db765` |
| 5 ต.ค. 2026 | `.htaccess` + `web.config` (PHP 8.4 handler `PHP84_lyi`, 301 *.html→*.php, บล็อก includes/, MIME .woff2) | `c2db765` |
| 5 ต.ค. 2026 | Deploy **v1.0.0** ขึ้น dev `\\192.168.0.70\wwwroot\lyindustries-dev` — ทุกหน้า 200, output ตรงกับ local | — |
| 5 ต.ค. 2026 | Deploy **v1.0.1**: กู้ `web.config` บน dev หลังถูกแทนด้วยไฟล์ของ lyi-dashboard (ทำให้ 404) | `c2db765` (tag `v1.0.1`) |
| 5 ต.ค. 2026 | สร้าง `CLAUDE.md` + `PROJECT_STATUS.md` | `90b7942` |
| 5 ต.ค. 2026 | ย้ายข้อมูลที่ยังใช้ได้จาก `README.md` (ลิงก์/ข้อมูลติดต่อ, ข้อขัดแย้งเมนู, schema) เข้า `CLAUDE.md` แล้วลบ `README.md` | `e816e03` |
| 5 ต.ค. 2026 | ระบบ deploy log แบบมี version: `DEPLOY_LOG.md` (SemVer, 1 deploy = 1 git tag) + ขั้นตอนใน `CLAUDE.md` | `4b03aba` |
| 5 ต.ค. 2026 | ใส่วิดีโอ YouTube หน้า About: https://www.youtube.com/watch?v=Lr59gy7RcWo (embed แบบ youtube-nocookie, `start=6`) | `080b64f` |
| 5 ต.ค. 2026 | เพิ่ม `.gitignore` (กัน `connectgrp.php` ที่มีรหัส DB + ไฟล์ขยะ OS/editor), เอา `.DS_Store` ออกจาก repo | `a1dd48b` |

## 🔄 งานที่กำลังทำ

- รอผู้ใช้ตรวจ `CLAUDE.md` / `PROJECT_STATUS.md`
- รอทดสอบหน้าเว็บบน dev ด้วยเบราว์เซอร์จริง (โดยเฉพาะ scroll animation หน้าแรก)

## 🐞 Bug / ปัญหาที่รู้อยู่

| ปัญหา | ผลกระทบ | สถานะ |
|---|---|---|
| `assets/dye-yarn-machine.png` ยังไม่มีไฟล์ (process ขั้น 03 ย้อมสี) | ภาพพื้นหลังขั้น 03 ไม่ขึ้น (404) | รอภาพจาก Pack |
| ภาพ PNG ใน `assets/img/` รวม ~15MB (lab-dip 6.6MB, dye-dispenser 4.1MB, spectrophotometer 3.4MB) | หน้าแรกโหลดช้า | ยังไม่แก้ — ควรแปลงเป็น JPG/WebP (ต้องไม่เปลี่ยนหน้าตา) |
| Scroll animation (ไทล์บินเข้าที่, process pipeline) ยืนยันใน headless Chrome ได้แค่ว่า loop ทำงาน/ไม่มี JS error | อาจมีจุดต่างจากต้นฉบับที่ยังไม่เห็น | รอเช็กในเบราว์เซอร์จริง |

## 📋 งานที่ต้องทำต่อ

1. ฟอร์มขอใบเสนอราคา (`contact.php`) ยังเป็น `mailto:` → ทำ backend รับฟอร์มจริง (PHP) — ต้องเลือกวิธีส่งเมล/เก็บข้อมูล
2. ~~YouTube video ID หน้า About~~ ✅ ใส่แล้ว (`Lr59gy7RcWo`, เริ่มที่วินาที 6)
3. ภาพ process บางขั้นดึงตรงจาก `lyindustries.com/img/` และวิดีโอ hero จาก `lyindustries.com/media/` → ย้ายมาเก็บใน `assets/`
4. ภาพจริงแต่ละหมวด/Facilities + ภาพตัวอย่างสินค้า 6 รายการ (gallery หน้าแรกตอนนี้เป็นกรอบเส้นประ — ใส่ path ที่ `img` ใน `$galleryItems`)
5. Optimize ภาพ PNG ใหญ่ใน `assets/img/`
6. หน้าย่อยโหลดฟอนต์จาก Google Fonts ส่วนหน้าแรก self-host — พิจารณาให้เหมือนกัน
7. ~~หา URL สาธารณะสำหรับ dev~~ ✅ https://lysystems.sytes.net/lyindustries-dev/ — เหลือ: วางแผนขึ้น production บน z.com (วิธี upload, ทดสอบบน PHP 8.2)
11. **แก้ `connectgrp.php`** ให้ company server (192.168.0.70) ใช้ DB `test_LYI` — ตอนนี้ถูกนับเป็น production และจะต่อ DB `LYI` ตัวจริง (ต้องทำก่อนเริ่มงานหลังบ้าน)
12. ยืนยันกับ z.com ว่ามี extension `sqlsrv`/`pdo_sqlsrv` และต่อออกไป SQL Server 183.89.245.21 ได้ — ถ้าไม่ได้ ต้องเปลี่ยนแผนหลังบ้าน
13. ยืนยันว่า `C:\PHP84` บน 192.168.0.70 เปิด extension `sqlsrv` แล้ว (คอมเมนต์ของ bkkkids ระบุว่ามีแค่ `pdo_sqlsrv`)
14. **แผน launch ขึ้น production** (www.lyindustries.com ตอนนี้ยังเป็นเว็บเก่า): สำรองเว็บเก่า, map + redirect 301 จากหน้าเก่า 8 หน้า (`innovation`, `shop`, `products_detail`, `braiding`, `crochet`, `finishing`, `needle_loom`, `raschel`), สำรองจาก z.com โดยตรง (source บน 192.168.0.73 เก่ากว่าเว็บจริง), เก็บ `/lyinspirationhub/`, `/img/`, `/media/` ไว้ (หรือย้ายภาพ/วิดีโอที่หน้าแรกใช้มาไว้ใน `assets/` ก่อน — ดูข้อ 3), เพิ่ม `robots.txt` + `sitemap.xml` — รายละเอียดใน CLAUDE.md หัวข้อ Production
8. ~~เพิ่ม `.gitignore`~~ ✅ ทำแล้ว
9. เปลี่ยน LINE URL / `mailto:` ที่ยัง hard-code ใน `index.php`, `about.php`, `contact.php` ให้ใช้ค่าคงที่ `SITE_*`
10. Deploy ครั้งถัดไป: ลบไฟล์ `*.md` ที่ค้างบน dev server (`README.md`, `DESIGN-LOCK.md`, `CONTENT-DRAFT.md`) — ตามกฎใหม่เอกสารไม่ขึ้น server

### รอยืนยันจาก Pack
- เวลาทำการ: 08:30–17:30 + ส. 08:30–12:00 (ที่ใช้อยู่ตอนนี้) หรือ 09:00–18:00
- ภาพจริง, รหัสสินค้าตัวอย่าง 6 รายการ (YouTube video ID ได้แล้ว — DESIGN-LOCK.md ยังเขียนว่ารออยู่)
- (จาก CONTENT-DRAFT) เลข certificate OEKO-TEX, ชื่อมาตรฐานแล็บ, ISO/GRS/Higg, ตัวเลขโรงงาน

## 🧭 Technical decisions

| วันที่ | การตัดสินใจ | เหตุผล |
|---|---|---|
| 5 ต.ค. 2026 | ใช้ PHP 8.4 ล้วน ไม่มี framework/Composer/DB | เว็บ 4 หน้า เนื้อหาคงที่ — ต้องการแค่ include ร่วมและแยกข้อมูลออกจาก markup |
| 5 ต.ค. 2026 | แตก bundle หน้าแรกเป็น HTML ที่ render ฝั่ง server แทนการให้ React แกะตอนเปิดหน้า | ไฟล์ 20MB → HTML ~125KB, SEO/AEO อ่านเนื้อหาได้โดยไม่ต้องรัน JS |
| 5 ต.ค. 2026 | เก็บ inline style ของหน้าแรกไว้ตามต้นฉบับ, แปลง `style-hover` เป็น class `.hv-N` | ดีไซน์ล็อก — ลดความเสี่ยงหน้าตาเพี้ยน |
| 5 ต.ค. 2026 | ข้อมูลรายการหน้าแรกอยู่ใน `includes/home-data.php`, ค่าคงที่ใน `includes/bootstrap.php` | แก้เนื้อหาได้ที่เดียว ไม่ต้องแตะ markup |
| 5 ต.ค. 2026 | ไม่ refactor CSS ของหน้าย่อย (แต่ละหน้ามี `<style>` ของตัวเอง) — แยกแค่ header/footer | output ตรงกับต้นฉบับทุกไบต์ |
| 5 ต.ค. 2026 | ลบไฟล์ `.html` และ redirect 301 → `.php` (ทั้ง Apache และ IIS) | ลิงก์/บุ๊กมาร์กเดิมไม่พัง |
| 5 ต.ค. 2026 | dev server ใช้ `web.config` สลับเป็น PHP 8.4 (`C:\PHP84`) แบบเดียวกับ lyi-dashboard | IIS default เป็น PHP 7.1 รันโค้ด PHP 8 ไม่ได้ |
| 5 ต.ค. 2026 | ทุก deploy มี version SemVer + git tag + entry ใน `DEPLOY_LOG.md`, deploy เฉพาะ commit ที่สะอาด, ไม่ deploy `*.md` | ย้อนดูได้ว่าบน server เป็นโค้ดชุดไหน และ rollback ได้ |
| 5 ต.ค. 2026 | 3 environment: local (COM-CPU-055) → company server .70 (IIS, public ผ่าน lysystems.sytes.net) → production z.com (LiteSpeed PHP 8.2) ซึ่งหลังบ้านจะต่อ DB ในบริษัทผ่าน 183.89.245.21 | ตามโครงสร้างที่มีอยู่ของบริษัท; ผลคือโค้ดต้องรองรับ PHP 8.2 และต้องมีทั้ง `.htaccess` + `web.config` |
| 5 ต.ค. 2026 | gallery ตัวอย่างสินค้าแสดงกรอบ placeholder เมื่อยังไม่มีรูป | เดิมเป็นช่องลากวางรูปของเครื่องมือออกแบบ ซึ่งใช้บนเว็บจริงไม่ได้ |
