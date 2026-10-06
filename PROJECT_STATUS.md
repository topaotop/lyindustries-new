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
| 6 ต.ค. 2026 | รูป "รอใส่รูป" (`assets/img/placeholder.svg`) + `img_src()` แทนรูปที่ยังไม่มี: process ขั้น 03, gallery หน้าแรก 6, ตัวอย่างสินค้า catalog 6 | `1194b22` |
| 6 ต.ค. 2026 | redirect 301 หน้าเว็บเก่า 8 หน้า (`innovation`, `shop`, `products_detail`, `braiding`, `crochet`, `finishing`, `needle_loom`, `raschel`) → หน้าแรก ใน `.htaccess` + `web.config` | `1194b22` |

## 🔄 งานที่กำลังทำ

- **ออกแบบหลังบ้าน admin + เว็บ 2 ภาษา + API** — Pack อนุมัติแล้ว (6 ต.ค. 2026) → ร่าง design ให้ผู้ใช้ตรวจก่อนลงมือ
- รอทดสอบหน้าเว็บบน dev ด้วยเบราว์เซอร์จริง (โดยเฉพาะ scroll animation หน้าแรก)

## 🐞 Bug / ปัญหาที่รู้อยู่

| ปัญหา | ผลกระทบ | สถานะ |
|---|---|---|
| ภาพ PNG ใน `assets/img/` รวม ~15MB (lab-dip 6.6MB, dye-dispenser 4.1MB, spectrophotometer 3.4MB) | หน้าแรกโหลดช้า | จะแก้ในหลังบ้าน (อัปโหลดแล้วย่อ/แปลง WebP อัตโนมัติ) |
| Scroll animation (ไทล์บินเข้าที่, process pipeline) ยืนยันใน headless Chrome ได้แค่ว่า loop ทำงาน/ไม่มี JS error | อาจมีจุดต่างจากต้นฉบับที่ยังไม่เห็น | รอเช็กในเบราว์เซอร์จริง |
| ภาพ `assets/prod-waistband.jpg` (PROD-05) มีโลโก้ "LAKERS" | อาจขัดกฎ "ห้ามแสดงชื่อแบรนด์ลูกค้า" | รอผู้ใช้/Pack ตัดสิน — เปลี่ยนรูปผ่านหลังบ้านได้ |

## 📋 งานที่ต้องทำต่อ

**Roadmap (ลำดับที่ตกลงกัน 6 ต.ค. 2026)**
1. **Phase 1 — ย้ายเนื้อหาเข้า DB** (ตาราง `web_*` มีคอลัมน์ th/en) ให้หน้าเว็บอ่านจาก DB โดย output เท่าเดิม
2. **Phase 2 — หลังบ้าน admin**: login ด้วย `sysmnuser`, ตารางสิทธิ์ของเว็บ, แก้ข้อความ/รูป 2 ภาษา, อัปโหลดรูป (ย่อ/WebP), ฟอร์ม contact บันทึกลง DB (แทน `mailto:`), เปลี่ยนภาษา default ได้
3. **Phase 3 — หน้าภาษาอังกฤษ** `/` = ไทย (default), `/en/` = อังกฤษ, ปุ่มเปลี่ยนภาษา, `hreflang`
4. **Phase 4 — SEO/AEO**: meta description/canonical/OG ทุกหน้า 2 ภาษา, `sitemap.xml` + `robots.txt`, schema `FAQPage`, ความเร็ว — วัดผลด้วย Ahrefs Site Audit
5. **API** (JSON) ใช้ชั้นข้อมูลเดียวกับหลังบ้าน — ออกแบบพร้อม Phase 2

**งานย่อย**
- เปลี่ยน LINE URL / `mailto:` ที่ยัง hard-code ใน `index.php`, `about.php`, `contact.php` ให้ใช้ค่าคงที่ `SITE_*`
- หน้าย่อยโหลดฟอนต์จาก Google Fonts ส่วนหน้าแรก self-host — พิจารณาให้เหมือนกัน
- Deploy ครั้งถัดไป: ลบไฟล์ `*.md` ที่ค้างบน dev server (`README.md`, `DESIGN-LOCK.md`, `CONTENT-DRAFT.md`) และทดสอบ redirect หน้าเว็บเก่าบน IIS (`web.config` ยังไม่ได้ทดสอบบน server จริง)
- รัน Ahrefs Site Audit ครั้งแรกที่ https://lysystems.sytes.net/lyindustries-dev/ เก็บคะแนนตั้งต้น (ผู้ใช้ทำ)

**Launch ขึ้น production** (www.lyindustries.com ยังเป็นเว็บเก่า)
- ผู้ใช้สำรองเว็บเก่าจาก z.com แล้ว (6 ต.ค. 2026)
- redirect 301 หน้าเก่า 8 หน้า → หน้าแรก: ✅ ทำแล้วใน `.htaccess` + `web.config`
- ต้องเก็บ `/lyinspirationhub/`, `/img/`, `/media/` บน server ไว้ (หน้าแรกยังดึงภาพ/วิดีโอจากที่นั่นจนกว่าจะเปลี่ยนผ่านหลังบ้าน)
- วิธี upload ไป z.com — ยังไม่ได้กำหนด

### รอยืนยัน
- เวลาทำการ: 08:30–17:30 + ส. 08:30–12:00 (ที่ใช้อยู่ตอนนี้) หรือ 09:00–18:00
- ภาพจริง (process ขั้น 03, ตัวอย่างสินค้า 6 รายการ, หมวดสินค้า) — ตอนนี้แสดง "รอใส่รูป" จะอัปโหลดผ่านหลังบ้าน
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
| 6 ต.ค. 2026 | รูปที่ยังไม่มีแสดง `placeholder.svg` "รอใส่รูป" ผ่าน `img_src()` (แทนกรอบเส้นประเดิม) | ให้เห็นชัดว่าต้องอัปโหลดรูป และไม่มี `<img>` ที่ 404 |
| 6 ต.ค. 2026 | `connectgrp.php` แยก environment จาก HTTP_HOST แทนชื่อเครื่อง; โดเมนที่ไม่รู้จัก → DB ทดสอบ | เดิม .70 ถูกนับเป็น production; fail-safe ไปทาง DB ทดสอบ |
| 6 ต.ค. 2026 | หน้าเว็บเก่าทั้ง 8 หน้า redirect 301 ไปหน้าแรก (ไม่ส่ง query string ต่อ) | ผู้ใช้เลือก; ไม่มีหน้าเทียบเท่า 1:1 ในเว็บใหม่ |
| 6 ต.ค. 2026 | ใช้ driver `sqlsrv` (เหมือน `connectgrp.php` และระบบอื่นของบริษัท) · ตารางใหม่ขึ้นต้น `web_` · login ด้วย `sysmnuser` + ตารางสิทธิ์ของเว็บเอง | ตามมาตรฐานระบบในบริษัท |
