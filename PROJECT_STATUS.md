# PROJECT_STATUS.md — L.Y. Industries website

อัปเดตล่าสุด: 6 ต.ค. 2026 · branch `main` · dev: https://lysystems.sytes.net/lyindustries-dev/ (ภายใน http://192.168.0.70/lyindustries-dev/) · **version บน dev: v1.0.1** (ดู [DEPLOY_LOG.md](DEPLOY_LOG.md))

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
| 6 ต.ค. 2026 | `connectgrp.php` เลือก DB ตามโดเมน: เฉพาะ www.lyindustries.com → `LYI`, อื่นๆ (รวม .70 / lysystems.sytes.net) → `test_LYI` (ไฟล์อยู่ใน .gitignore — ไม่มี commit) | — |
| 6 ต.ค. 2026 | รูป "Image pending" (`assets/img/placeholder.svg`) + `img_src()` แทนรูปที่ยังไม่มี: process ขั้น 03, gallery หน้าแรก 6, ตัวอย่างสินค้า catalog 6 | `1194b22` |
| 6 ต.ค. 2026 | สร้างตาราง `lyiweb_*` 12 ตาราง + ข้อมูลตั้งต้น ทั้งใน `test_LYI` และ `LYI` (ผู้ใช้รัน `docs/sql/001_lyiweb_schema.sql` ผ่าน Navicat) — ตรวจแล้ว: คอลัมน์, constraint 39, index 5, roles 4, permissions 10, settings 2, pages 4 ครบ | `8c73d4d` (script) |
| 6 ต.ค. 2026 | **ขั้นที่ 2a:** หน้าแรกอ่านรายการ 7 list จาก `lyiweb_items` ผ่าน content layer (cache 5 นาที → DB → cache เก่า → fallback ในโค้ด) · `tools/build-seed-home.php` สร้าง `002_lyiweb_seed_home.sql` · seed 43 แถวลง `test_LYI` · บล็อก `cache/ tools/ docs/` จากเว็บ · ตรวจ: HTML ตรง baseline ทุกไบต์ทุกทาง (DB, cache, cache เก่า, ไม่มี DB, Apache, PHP 8.2) + พิสูจน์ว่าแก้ใน DB แล้วหน้าเว็บเปลี่ยนจริง | `e932ed2` |
| 6 ต.ค. 2026 | ผู้ใช้รัน `002_lyiweb_seed_home.sql` ใน `LYI` ผ่าน Navicat — ตรวจแล้ว 43 แถวตรงกับ `test_LYI` ทุกแถว, ภาษาไทยไม่เพี้ยน, `test_LYI` ไม่มีแถวซ้ำ | — |
| 6 ต.ค. 2026 | **ขั้นที่ 2b-1:** ข้อมูลติดต่อ (โทร/แฟกซ์/อีเมล/LINE/บริษัท/ที่อยู่/เวลาทำการ/URL ภายนอก) → `lyiweb_settings` ผ่าน `site()` และ title/meta description → `lyiweb_pages` ผ่าน `page_meta()` ทั้ง 4 หน้า รวม schema JSON-LD · ลบค่าคงที่ `SITE_*` (เหลือ `SITE_PLACEHOLDER_IMG`) · `tools/build-seed-site.php` → `003_lyiweb_seed_site.sql` (seed `test_LYI` แล้ว) · ตรวจ: ตรง baseline ทุกไบต์ทุกทาง ยกเว้น `<title>` หน้า about ที่ `&` → `&amp;` (ถูกต้องตามมาตรฐาน HTML) · ทดสอบเปลี่ยนเบอร์ 1 จุด → ข้อความ, `tel:` และ schema เปลี่ยนครบทุกหน้า | `16577af` |
| 6 ต.ค. 2026 | ผู้ใช้รัน `003_lyiweb_seed_site.sql` ใน `LYI` — ตรวจแล้ว settings 17 / pages ตรงกับ `test_LYI` | — |
| 6 ต.ค. 2026 | **ขั้นที่ 2b-2:** ข้อความหน้าแรก 129 ชิ้น → `lyiweb_blocks` ผ่าน `b('home.<section>.nn')` (ยกเว้นเมนู header/side) · `tools/extract-blocks.php` + `build-seed-blocks.php` → `004_lyiweb_seed_blocks.sql` (seed `test_LYI` แล้ว) · ตรวจ: ตรง baseline ทุกไบต์ (DB, cache, ไม่มี DB, Apache, PHP 8.2) + แก้ข้อความใน DB แล้วหน้าเปลี่ยนและ escape `< & "` ถูกต้อง | `022c439` |
| 6 ต.ค. 2026 | **ขั้นที่ 2b-3:** ข้อความ about (52), catalog (49), contact (28) และ footer ร่วม (14) → `lyiweb_blocks` ด้วย `tools/extract-blocks.php` · `004` regenerate เป็น 272 block และ seed `test_LYI` · ตรวจ: ทุกหน้าตรงผลเดิมทุกไบต์ (DB, cache, ไม่มี DB, Apache, PHP 8.2) + แก้ footer 1 จุดเปลี่ยนทั้ง catalog/contact | `c29e15f` |
| 6 ต.ค. 2026 | ผู้ใช้รัน `004_lyiweb_seed_blocks.sql` ใน `LYI` — ตรวจแล้ว 272 block ตรงกับ `test_LYI` | — |
| 6 ต.ค. 2026 | **ขั้นที่ 3a — หลังบ้าน:** `admin/` login ด้วย `sysmnuser` (อ่านอย่างเดียว), ล็อกหลังผิด 5 ครั้ง/username หรือ 20/IP, session/CSRF/audit, สิทธิ์ตาม role + level ≥ 5 = admin · หน้า: แดชบอร์ด, ข้อความหน้าเว็บ & SEO (ไทย/อังกฤษ, translator แก้ได้แค่อังกฤษ), ข้อมูลติดต่อ & ลิงก์ (validate) · ทดสอบด้วย session จำลองบน `test_LYI`: redirect/CSRF/ล็อก/สิทธิ์/บันทึก/validate ผ่าน แล้วคืนค่า — ข้อมูล `test_LYI` ตรงกับ `LYI` ทุกแถว | `0dd4ef8` |
| 6 ต.ค. 2026 | หลังบ้าน: กดบันทึกแล้วอยู่ตำแหน่งเดิม (ไม่เด้งขึ้นบนสุด) · ข้อความยืนยันลอยมุมขวาบน 4 วินาที · ถ้ามีช่องผิด เลื่อนไปช่องนั้นให้ (ผู้ใช้ขอ) | `590888c` |
| 6 ต.ค. 2026 | หลังบ้าน: ออกแบบ sidebar ใหม่ — พื้นเข้มต่อกับแถบบน, ไอคอน, จัดกลุ่มเมนู (เนื้อหาเว็บไซต์ / ตั้งค่าเว็บไซต์), เมนูที่เลือกมีแถบลายเทปถักสีส้ม, ลิงก์ "ดูหน้าเว็บไซต์" ย้ายมาล่าง sidebar, มือถือเป็นแถบเลื่อนแนวนอน · ผู้ใช้ทดสอบ login ด้วยบัญชีจริงแล้ว (มีประวัติแก้ `home.trust.02`) | `56bb7a8` |
| 6 ต.ค. 2026 | ภาพพื้นหลังขั้นตอนผลิต: รูปที่แบนกว่า 2:1 (รูปเก่าจากเว็บเดิม มีพื้นดำในไฟล์) แสดงเต็มรูปตรงกลาง + ขอบบน/ล่างจางเข้าพื้น แทนการขยายเต็มจอ (เดิมเห็นก้อนดำครึ่งจอ และขั้น 05 เบลอ) — รูป 16:9 ปกติยังเต็มจอเหมือนเดิม (ตามที่ผู้ใช้เสนอ) | `215024a` |
| 6 ต.ค. 2026 | แก้ "รูปยังไม่ center" — สาเหตุคือเบราว์เซอร์ใช้ home.js เก่าจาก cache: เพิ่ม `asset()` ต่อ `?v=เวลาแก้ไฟล์` ให้ CSS/JS ทุกไฟล์ (หน้าเว็บ + หลังบ้าน) · ปรับเกณฑ์เป็น > 2.5:1 ให้ขั้น 01 (2.35:1 ไม่มีพื้นดำ) กลับมาเต็มจอ · ตรวจในหน้าจริง: 01 เต็มจอ, 02/04/05/06 เต็มรูปตรงกลาง | `5acc544` |
| 6 ต.ค. 2026 | redirect 301 หน้าเว็บเก่า 8 หน้า (`innovation`, `shop`, `products_detail`, `braiding`, `crochet`, `finishing`, `needle_loom`, `raschel`) → หน้าแรก ใน `.htaccess` + `web.config` | `1194b22` |
| 6 ต.ค. 2026 | แก้ภาพ process 5 ภาพ + วิดีโอ hero โหลดไม่ขึ้นเมื่อเปิดจากในบริษัท: เปลี่ยน URL จาก `lyindustries.com` เป็น `www.lyindustries.com` (สาเหตุ: DNS ของ AD ในบริษัท ชี้ `lyindustries.com` ไป DC1) | `5d3b8e4` |

## 🔄 งานที่กำลังทำ

- **ออกแบบหลังบ้าน admin + เว็บ 2 ภาษา + API** — Pack อนุมัติแล้ว (6 ต.ค. 2026) → [docs/design/admin-i18n-api.md](docs/design/admin-i18n-api.md) ผู้ใช้ตอบคำถาม 5 ข้อแล้ว (ฟอร์ม = บันทึก DB + ดูในหลังบ้าน ไม่แจ้งเตือน · นับคลิกอีเมล/LINE/โทร/ฟอร์ม ด้วย `lyiweb_channel_clicks` · คำถามตอบครบแล้ว) · **ขั้นที่ 1 ✅** ตาราง `lyiweb_*` · **ขั้นที่ 2a ✅** รายการหน้าแรก 7 list อ่านจาก DB (seed แล้วทั้ง `test_LYI` และ `LYI`) · **2b-1 ✅** ค่าติดต่อ/ลิงก์ (`lyiweb_settings`) + ชื่อหน้า/meta (`lyiweb_pages`) ทุกหน้าอ่านจาก DB (seed แล้วทั้ง `test_LYI` และ `LYI`) · **2b-2 ✅ / 2b-3 ✅** ข้อความทุกหน้า 272 block (`lyiweb_blocks`: home 129, about 52, catalog 49, contact 28, footer 14 — seed แล้วทั้ง `test_LYI` และ `LYI`) · **3a ✅** หลังบ้าน: login `sysmnuser` + สิทธิ์ + แดชบอร์ด + แก้ข้อความ/SEO + ข้อมูลติดต่อ (ผู้ใช้ login ด้วยบัญชีจริงได้แล้ว) · **ถัดไป 3b:** แก้รายการ (สินค้า/FAQ/ขั้นตอน…) + อัปโหลดรูป · 3c: ฟอร์มขอใบเสนอราคา → DB, นับคลิก, จัดการสิทธิ์ผู้ใช้
- รอทดสอบหน้าเว็บบน dev ด้วยเบราว์เซอร์จริง (โดยเฉพาะ scroll animation หน้าแรก)

## 🐞 Bug / ปัญหาที่รู้อยู่

| ปัญหา | ผลกระทบ | สถานะ |
|---|---|---|
| ภาพ PNG ใน `assets/img/` รวม ~15MB (lab-dip 6.6MB, dye-dispenser 4.1MB, spectrophotometer 3.4MB) | หน้าแรกโหลดช้า | จะแก้ในหลังบ้าน (อัปโหลดแล้วย่อ/แปลง WebP อัตโนมัติ) |
| Scroll animation (ไทล์บินเข้าที่, process pipeline) ยืนยันใน headless Chrome ได้แค่ว่า loop ทำงาน/ไม่มี JS error | อาจมีจุดต่างจากต้นฉบับที่ยังไม่เห็น | รอเช็กในเบราว์เซอร์จริง |
| ภาพ `assets/prod-waistband.jpg` (PROD-05) มีโลโก้ "LAKERS" | อาจขัดกฎ "ห้ามแสดงชื่อแบรนด์ลูกค้า" | รอผู้ใช้/Pack ตัดสิน — เปลี่ยนรูปผ่านหลังบ้านได้ |

## 📋 งานที่ต้องทำต่อ

**Roadmap (ลำดับที่ตกลงกัน 6 ต.ค. 2026)**
1. **Phase 1 — ย้ายเนื้อหาเข้า DB** (ตาราง `lyiweb_*` มีคอลัมน์ th/en) ให้หน้าเว็บอ่านจาก DB โดย output เท่าเดิม
2. **Phase 2 — หลังบ้าน admin**: login ด้วย `sysmnuser`, ตารางสิทธิ์ของเว็บ, แก้ข้อความ/รูป 2 ภาษา, อัปโหลดรูป (ย่อ/WebP), ฟอร์ม contact บันทึกลง DB (แทน `mailto:`), เปลี่ยนภาษา default ได้
3. **Phase 3 — หน้าภาษาอังกฤษ** `/` = ไทย (default), `/en/` = อังกฤษ, ปุ่มเปลี่ยนภาษา, `hreflang`
4. **Phase 4 — SEO/AEO**: meta description/canonical/OG ทุกหน้า 2 ภาษา, `sitemap.xml` + `robots.txt`, schema `FAQPage`, ความเร็ว — วัดผลด้วย Ahrefs Site Audit
5. **API** (JSON) ใช้ชั้นข้อมูลเดียวกับหลังบ้าน — ออกแบบพร้อม Phase 2

**งานย่อย**
- หน้าย่อยโหลดฟอนต์จาก Google Fonts ส่วนหน้าแรก self-host — พิจารณาให้เหมือนกัน
- Deploy ครั้งถัดไป: ลบไฟล์ `*.md` ที่ค้างบน dev server (`README.md`, `DESIGN-LOCK.md`, `CONTENT-DRAFT.md`) และทดสอบ redirect หน้าเว็บเก่าบน IIS (`web.config` ยังไม่ได้ทดสอบบน server จริง)
- SEO: ใช้ `https://www.lyindustries.com` เป็น URL หลัก (canonical) ให้ทั้งเว็บ — schema JSON-LD ของ about/catalog/contact ยังใช้ `https://lyindustries.com` (ไม่มี www) ให้ปรับใน Phase 4 · ตอน launch เพิ่ม 301 `lyindustries.com` → `www.lyindustries.com` บน production
- Deploy ครั้งถัดไปขึ้น .70: ตั้งสิทธิ์ให้ IIS เขียนโฟลเดอร์ `cache/` ได้ และตรวจว่ามี `connectgrp.php` บน server (ถ้าไม่มี หน้าแรกใช้ fallback)
- ภาพพื้นหลังขั้นตอนผลิตจริง 6 รูป (แนวนอน 16:9 กว้าง ≥ 1920px ไม่มีพื้นดำ/ตัวหนังสือในรูป จุดสำคัญค่อนไปทางขวา) — อัปโหลดผ่านหลังบ้าน (ขั้น 3b) · ตอนนี้ขั้น 02/04/06 ยังเห็นขอบกรอบรูปที่อยู่ในไฟล์เก่า
- รัน Ahrefs Site Audit ครั้งแรกที่ https://lysystems.sytes.net/lyindustries-dev/ เก็บคะแนนตั้งต้น (ผู้ใช้ทำ)

**Launch ขึ้น production** (www.lyindustries.com ยังเป็นเว็บเก่า)
- ผู้ใช้สำรองเว็บเก่าจาก z.com แล้ว (6 ต.ค. 2026)
- redirect 301 หน้าเก่า 8 หน้า → หน้าแรก: ✅ ทำแล้วใน `.htaccess` + `web.config`
- ต้องเก็บ `/lyinspirationhub/`, `/img/`, `/media/` บน server ไว้ (หน้าแรกยังดึงภาพ/วิดีโอจากที่นั่นจนกว่าจะเปลี่ยนผ่านหลังบ้าน)
- วิธี upload ไป z.com — ยังไม่ได้กำหนด

### รอยืนยัน
- เวลาทำการ: 08:30–17:30 + ส. 08:30–12:00 (ที่ใช้อยู่ตอนนี้) หรือ 09:00–18:00
- ภาพจริง (process ขั้น 03, ตัวอย่างสินค้า 6 รายการ, หมวดสินค้า) — ตอนนี้แสดง "Image pending" จะอัปโหลดผ่านหลังบ้าน
- ผู้รับผิดชอบเนื้อหาแต่ละส่วน (รวมคำแปลภาษาอังกฤษ) — ยังไม่กำหนด
- (จาก CONTENT-DRAFT) เลข certificate OEKO-TEX, ชื่อมาตรฐานแล็บ, ISO/GRS/Higg, ตัวเลขโรงงาน

### ยืนยันแล้ว (6 ต.ค. 2026)
- z.com มี `sqlsrv`/`pdo_sqlsrv` และต่อ SQL Server 183.89.245.21 ได้ (Inspiration Hub ใช้อยู่) · โควตา hosting ~10GB ใช้ไป 1.6GB
- `C:\PHP84` บน 192.168.0.70 มี `sqlsrv` (ระบบอื่นใช้อยู่)
- ภาพ/วิดีโอเดิมยังไม่ต้องย้าย — จะเปลี่ยนใหม่ผ่านหลังบ้าน

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
| 6 ต.ค. 2026 | รูปที่ยังไม่มีแสดง `placeholder.svg` "Image pending" ผ่าน `img_src()` (แทนกรอบเส้นประเดิม) | ให้เห็นชัดว่าต้องอัปโหลดรูป และไม่มี `<img>` ที่ 404 |
| 6 ต.ค. 2026 | `connectgrp.php` แยก environment จาก HTTP_HOST แทนชื่อเครื่อง; โดเมนที่ไม่รู้จัก → DB ทดสอบ | เดิม .70 ถูกนับเป็น production; fail-safe ไปทาง DB ทดสอบ |
| 6 ต.ค. 2026 | หน้าเว็บเก่าทั้ง 8 หน้า redirect 301 ไปหน้าแรก (ไม่ส่ง query string ต่อ) | ผู้ใช้เลือก; ไม่มีหน้าเทียบเท่า 1:1 ในเว็บใหม่ |
| 6 ต.ค. 2026 | ล็อกการ login ผิดแยก username (5 ครั้ง) กับ IP (20 ครั้ง) | production มองเห็นทั้งออฟฟิศเป็น IP เดียว — ล็อกตาม IP 5 ครั้งจะล็อกทั้งบริษัท |
| 6 ต.ค. 2026 | หลังบ้านไม่เขียน `sysmnuser` เลย (ไม่อัปเดต `logintime` แบบระบบอื่น) | เป็นตาราง ERP ส่วนกลาง — เว็บเก็บประวัติ login ใน `lyiweb_login_attempts` เอง |
| 6 ต.ค. 2026 | หน้าเว็บสาธารณะอ่านเนื้อหาผ่าน file cache + fallback ในโค้ด ไม่ query DB ทุก request | production อยู่ z.com แต่ DB อยู่ในบริษัท — ช้าและล่มตามเน็ตบริษัทถ้า query ตรง |
| 6 ต.ค. 2026 | seed SQL generate จาก `includes/home-data.php` (ไม่เขียนมือ) | fallback กับข้อมูลใน DB ตั้งต้นตรงกันเสมอ |
| 6 ต.ค. 2026 | URL ที่ browser โหลดจากโดเมนบริษัทต้องมี `www.` เสมอ | AD domain ของบริษัทชื่อ lyindustries.com ทำให้ DNS ภายในชี้โดเมนเปล่าไป Domain Controller — แก้ฝั่ง DNS ไม่ได้ |
| 6 ต.ค. 2026 | ฟอร์มขอใบเสนอราคาบันทึกลง `lyiweb_contact_requests` แล้วดูในหลังบ้านอย่างเดียว ไม่ส่งอีเมล/LINE | ผู้ใช้เลือก — ไม่ต้องมี SMTP/LINE token, ลดจุดที่พังได้ |
| 6 ต.ค. 2026 | ใช้ driver `sqlsrv` (เหมือน `connectgrp.php` และระบบอื่นของบริษัท) · ตารางใหม่ขึ้นต้น `lyiweb_` · login ด้วย `sysmnuser` + ตารางสิทธิ์ของเว็บเอง | ตามมาตรฐานระบบในบริษัท |
