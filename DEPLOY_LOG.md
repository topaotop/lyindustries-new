# DEPLOY_LOG.md — ประวัติการ deploy

บันทึกทุกครั้งที่ deploy (ใหม่สุดอยู่บน) — วิธี deploy ดู [CLAUDE.md](CLAUDE.md#deploy--versioning)

## Version scheme

`vMAJOR.MINOR.PATCH` (Semantic Versioning) — 1 deploy = 1 version = 1 git tag ที่ชี้ไป commit ที่ deploy

| เลข | ขึ้นเมื่อ | ตัวอย่าง |
|---|---|---|
| MAJOR | เปลี่ยนใหญ่ / ขึ้น production ครั้งแรก / เปลี่ยนโครงสร้าง URL | v2.0.0 = launch บน www.lyindustries.com |
| MINOR | เพิ่มฟีเจอร์/หน้า/section, เปลี่ยนเนื้อหาที่ Pack อนุมัติ | ฟอร์ม contact ส่งได้จริง |
| PATCH | แก้บั๊ก, แก้ config, แก้ลิงก์/คำผิด, เปลี่ยนรูป | แก้ web.config |

---

## v1.10.1 — 2026-10-07 · dev

- **Commit:** `0ad32f9` · **Tag:** `v1.10.1`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:** แก้บั๊กหน้าเว็บ (front-end)
  - มือถือ: "SCROLL TO EXPLORE" ไม่ทับปุ่มส่วนบนสุดหน้าแรกแล้ว
  - header หน้าย่อยไม่ตัดบรรทัด (ชื่อบริษัท/ปุ่มขอใบเสนอราคาบนมือถือ, เมนูอังกฤษที่ 1024px)
  - ปุ่ม EN บนหน้า catalog มองเห็นแล้ว (สีตามธีม)
- **DB:** ไม่มี
- **Files:** เพิ่ม 0 · แก้ 5 · ลบ 0 (`about.php`, `catalog.php`, `contact.php`, `assets/css/home.css`, `includes/lib/i18n.php`)
- **Backup:** 5 ไฟล์ที่ถูกทับ เก็บใน scratchpad ของ session
- **Verify:** ✅ 8 หน้า (ไทย+อังกฤษ) 200 · robots/sitemap 200 · admin → 302 · `en/about.html` → 301 · `includes/…`, `connectgrp.php` → 404 · CSS ใหม่อยู่บน server (header breakpoints, `.scroll-cue`) · HTML จาก server ตรงกับ local (ต่างเฉพาะรูปโรงย้อมที่อัปโหลดเฉพาะ local) · URL สาธารณะ 200
- **Note:** รอ Pack — รูป prod-05 มีโลโก้ LAKERS (แบรนด์ลูกค้า), หน้าย่อยบนมือถือ/แท็บเล็ตยังไม่มีเมนู ☰

---

## v1.10.0 — 2026-10-07 · dev

- **Commit:** `1fdefd3` · **Tag:** `v1.10.0`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:**
  - หลังบ้าน: หน้า **SEO & AEO ใหม่ทุกแท็บ** (คะแนน, การ์ดรายหน้า, ตัวอย่างแชท, การ์ดข้อมูลธุรกิจสด, ขั้นตอน Search Console)
  - **Role/สิทธิ์** ตรงกับเมนู SEO (เมนูขึ้นเฉพาะคนมีสิทธิ์, ป้าย scope, ปุ่มติ๊ก SEO ทุกหน้า)
  - หน้าตัวอย่าง (ข้อความหน้าเว็บ / รายการ) **สลับ TH | EN** ได้
  - **แก้บั๊กจากการตรวจทั้งระบบ** — คนถูกล็อกหลุดทันที, แก้ role ตัวเองไม่ได้, SEO POST นอกสิทธิ์ 403, timezone Bangkok, ฟอร์มติดต่อ (อีเมลสำรอง/แผนที่อังกฤษ, error ตามชนิด, รอ 3 วิ, หมดอายุ 24 ชม.), track.php จำกัด 30/นาที, uploads รับเฉพาะรูป, ปิด directory listing, `/en/*.html` + หน้าเก่าใต้ `/en/` บน IIS ฯลฯ (รายละเอียดใน PROJECT_STATUS)
- **DB:** `004` + `010` (ป้าย `contact.form.32–39`) รันใน `test_LYI` แล้ว — `LYI` ยังไม่ได้รัน (ไม่กระทบ .70)
- **Files:** เพิ่ม 0 · แก้ 22 · ลบ 0
- **Backup:** 22 ไฟล์ที่ถูกทับ เก็บใน scratchpad ของ session
- **Verify:** ✅ 8 หน้า (ไทย+อังกฤษ) 200 · robots/sitemap 200 · admin → 302 login, หน้า login ไม่มี error · `index.html`, `en/about.html` → 301 ไปหน้าภาษาเดียวกัน · `braiding.php` → หน้าแรก, `en/braiding.php` → `/en/` · `includes/…`, `connectgrp.php` → 404 · `assets/img/`, `api/v1/` (ดูรายการไฟล์) → 403 · uploads: รูป 200, ไฟล์อื่น 404 · `.webp`/`.woff2` MIME ถูก · `/en/contact.php` อีเมลสำรอง + แผนที่ภาษาอังกฤษ · HTML จาก server ตรงกับ local (ต่างเฉพาะรูปโรงย้อมที่อัปโหลดเฉพาะ local) · URL สาธารณะ 200
- **Note:** รอบแรก IIS redirect `/en/about.html` ไป `/about.php` (ไทย) → แก้ `web.config` ใช้ path เต็ม (commit `1fdefd3`) แล้ว copy ซ้ำ ตรวจผ่าน · หน้าหลังบ้านบน server ต้อง login จริงจึงจะเปิดดูได้ (agent ไม่มีรหัส)

---

## v1.9.0 — 2026-10-07 · dev

- **Commit:** `f978925` · **Tag:** `v1.9.0`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:**
  - หลังบ้าน: **แดชบอร์ดใหม่** — การ์ดตัวเลขมีไอคอน (คำขอใหม่, กดติดต่อ 7 วัน + เส้นแนวโน้ม, % แปลอังกฤษ, เนื้อหา), "สิ่งที่ควรทำ" พร้อมปุ่มไปแก้, "แก้ไขล่าสุด" เป็นภาษาคน + ลิงก์ + รวมรายการซ้ำ, ทางลัด, กล่อง cache เล็กลง · เวลาเทียบกับนาฬิกาของ DB
- **DB:** ไม่มี
- **Files:** เพิ่ม 0 · แก้ 5 · ลบ 0 (`admin/index.php`, `admin/seo.php`, `admin/assets/admin.css`, `includes/admin/init.php`, `includes/lib/seo.php`)
- **Backup:** 5 ไฟล์ที่ถูกทับ เก็บใน scratchpad ของ session
- **Verify:** ✅ หน้าเว็บ (ไทย+อังกฤษ) 200 · robots/sitemap 200 · admin → 302 login, หน้า login 200 ไม่มี error · `index.html` → 301 · `includes/…`, `connectgrp.php` → 404 · HTML หน้าเว็บจาก server ตรงกับ local (ต่างเฉพาะรูปโรงย้อมที่อัปโหลดเฉพาะ local และเวลาในฟอร์ม) · URL สาธารณะ 200
- **Note:** แดชบอร์ดหลัง login ทดสอบใน local (Chrome, `test_LYI`) — บน server ต้อง login จริงจึงจะเปิดดูได้ (agent ไม่มีรหัส)

---

## v1.8.0 — 2026-10-07 · dev

- **Commit:** `981c3e0` · **Tag:** `v1.8.0`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:**
  - หลังบ้าน: ซ่อน/แสดง sidebar (☰) และ **ย่อเหลือไอคอน** (ปุ่ม "ย่อเมนู") บนจอคอม — จำค่าข้ามหน้า
  - SEO & AEO: ป้ายมีคำอังกฤษในวงเล็บ (Title tag, Description, Preview, Keyword …), แถบความยาว, **คำค้นหาหลัก (Keyword)** + ตรวจว่าอยู่ใน title/description/H1/เนื้อหา, แท็บ **เชื่อมต่อ Google (Search Console)** โค้ดยืนยัน Google/Bing (ใส่เฉพาะหน้าแรก production)
  - หน้าแรก: รูปตัวอย่างสินค้า 3 รหัสจาก Inspiration Hub (`assets/img/gallery/`, ชั่วคราว ผู้ใช้เปลี่ยนในหลังบ้าน) + `loading="lazy"`
- **DB:** seed 003 (36 settings) รันแล้วทั้ง `test_LYI` และ `LYI` · `011` (รูป gallery) รันใน `test_LYI` แล้ว — `LYI` ยังไม่ได้รัน (ไม่กระทบ .70)
- **Files:** เพิ่ม 3 · แก้ 8 · ลบ 0
- **Backup:** 8 ไฟล์ที่ถูกทับ เก็บใน scratchpad ของ session
- **Verify:** ✅ 8 หน้า (ไทย+อังกฤษ) 200 · robots.txt / sitemap.xml 200 · admin → 302 login · `index.html` → 301 · `includes/…`, `connectgrp.php` → 404 · รูป gallery `image/webp` · HTML จาก server ตรงกับ local (ต่างเฉพาะ host/path, `?v=`, เวลาในฟอร์ม, รูปโรงย้อมที่อัปโหลดเฉพาะ local) · หน้าแรกบน server แสดงรูป gallery 3 รูป · URL สาธารณะ 200

---

## v1.7.0 — 2026-10-07 · dev

- **Commit:** `588c3d2` · **Tag:** `v1.7.0`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:**
  - หลังบ้าน: เมนูใหม่ **SEO & AEO** (`admin/seo.php`) — ภาพรวม, ชื่อหน้า & คำอธิบาย (ตัวอย่าง Google + ตัวนับ), รูปแชร์ลิงก์ (ทั้งเว็บ/รายหน้า ครอป 1200×630), ข้อมูลธุรกิจสำหรับ Google, ตัวอย่าง FAQ
  - ชื่อหน้า/คำอธิบายย้ายออกจากหน้า "ข้อความหน้าเว็บ" (เปลี่ยนชื่อเมนูจาก "ข้อความหน้าเว็บ & SEO")
  - schema ทุกหน้าอ่านข้อมูลธุรกิจจาก settings · about เพิ่ม "Soi Ram Inthra 109" ในที่อยู่
- **DB:** seed 003 (settings 34 แถว รวม 14 ใหม่) รันแล้วทั้ง `test_LYI` และ `LYI` (ตรวจแล้ว)
- **Files:** เพิ่ม 1 · แก้ 12 · ลบ 0
- **Backup:** 12 ไฟล์ที่ถูกทับ เก็บใน scratchpad ของ session
- **Verify:** ✅ 8 หน้า (ไทย+อังกฤษ) 200 · robots.txt / sitemap.xml 200 · admin (รวม seo.php) → 302 login · `index.html` → 301 · `includes/…`, `connectgrp.php` → 404 · HTML จาก server ตรงกับ local (ต่างเฉพาะ host/path, `?v=`, เวลาในฟอร์ม และรูปโรงย้อมที่อัปโหลดไว้เฉพาะเครื่อง local) · URL สาธารณะ 200
- **Note:** รูปโรงย้อมที่ผู้ใช้อัปโหลดบนเครื่อง local แสดงเป็น Image pending บน .70 (uploads แยกต่อ server — ต้องอัปโหลดซ้ำในหลังบ้านของ .70 ถ้าต้องการให้เห็น)

---

## v1.6.0 — 2026-10-07 13:19 · dev

- **Commit:** `795b357` · **Tag:** `v1.6.0`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:**
  - **3c** ฟอร์มขอใบเสนอราคาบันทึกลง DB (`api/v1/contact.php`, กันสแปม, server ล่ม → เปิดอีเมลแทน) + นับคลิกอีเมล/LINE/โทร (`assets/js/track.js` → `api/v1/track.php`) · หลังบ้าน **คำขอจากลูกค้า** + **สถิติการติดต่อ**
  - **Phase 3 เว็บภาษาอังกฤษ** `/en/…` (rewrite) · ข้อความทุกชิ้นแปลได้ (แท็บใหม่ "เมนู & คำที่ใช้ทุกหน้า", ชื่อ/ที่อยู่ภาษาอังกฤษ) · ปุ่มเปลี่ยนภาษาแบบ B · ร่างคำแปลอังกฤษครบ (รอตรวจ)
  - **Phase 4 SEO/AEO** dev/test = noindex + robots.txt ปิด · `/robots.txt` `/sitemap.xml` อัตโนมัติ · canonical + Open Graph · schema FAQPage/Organization หน้าแรก · รูป Color Lab/R&D เป็น WebP (14.3MB → 229KB)
  - แก้บั๊ก: กล่อง "ส่งคำขอเรียบร้อยแล้ว" โผล่ก่อนส่ง (จาก 3c, ไม่เคยขึ้น server)
- **DB:** seed 003 / 004 / 010 รันแล้วทั้ง `test_LYI` และ `LYI` (ตรวจครบ)
- **Files:** เพิ่ม 17 · แก้ 21 · ลบ 4 (PNG Color Lab ×3 + R&D → แทนด้วย WebP)
- **Backup:** 26 ไฟล์ (ที่ถูกทับ + ที่ลบ) เก็บใน scratchpad ของ session
- **Verify:** ✅ 8 หน้า (ไทย+อังกฤษ) 200 · `/en` → 301 `/en/` · admin (รวม contacts/clicks) → 302 login · `*.html`/หน้าเก่า → 301 · `includes/ tools/ docs/ connectgrp.php` → 404 · `robots.txt` text/plain (Disallow ทั้งหมด), `sitemap.xml` 8 URL, header `X-Robots-Tag: noindex` · `.webp` = image/webp, PNG เก่า → 404 · JSON-LD หน้าแรก valid (Organization+LocalBusiness, WebSite, FAQPage) · HTML จาก server ตรงกับ local (ต่างเฉพาะ `?v=` ของ asset ในการ render เทียบ) · **ส่งฟอร์มจริงผ่าน IIS → บันทึกภาษาไทยถูก** แล้วลบแถวทดสอบ · URL สาธารณะผลเหมือนกัน
- **Note:** `/en/braiding.php` (URL ที่ไม่เคยมีจริง) IIS = 404, Apache = 301 → `/en/` — ไม่กระทบ · ตาราง contact/clicks มีข้อมูลที่ไม่ได้มาจากการทดสอบของ agent 1 + 2 แถว (ไม่แตะ)

## v1.5.0 — 2026-10-07 11:51 · dev

- **Commit:** `7b3886a` · **Tag:** `v1.5.0`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:**
  - **รายการ & รูปภาพ** (`admin/lists.php`): แก้ 7 list หน้าแรก — เพิ่ม/ลบ/เรียง/ซ่อน, อัปโหลดรูป (ย่อในเบราว์เซอร์ → แปลง WebP → `uploads/` + `lyiweb_media`), ชื่อแท็บภาษาคน + ภาพตำแหน่ง
  - **คลิกแก้บนหน้าเว็บจริง** ทั้งหน้าข้อความและหน้ารายการ (`admin/preview.php`) — คลิกบนหน้า → เปิดช่องแก้, พิมพ์แล้วเห็นผลทันที, เลือกช่อง → กรอบส้ม/เลื่อนไปขั้นตอนผลิตที่ถูกขั้น · ป้ายชนิดข้อความ · ตัวอย่างผลค้นหา Google
  - บล็อกสคริปต์ใน `uploads/` (`.htaccess` + `web.config`) · **IIS: เพิ่ม MIME `.webp`** (พบระหว่าง verify — เดิมได้ 404, แก้แล้ว commit `7b3886a` deploy `web.config` ซ้ำ)
  - หน้าเว็บสาธารณะ: `require_once` bootstrap (output เท่าเดิม)
- **Files:** เพิ่ม 10 (`admin/lists.php`, `admin/preview.php`, `includes/lib/media.php`, `admin/assets/where/*.webp` ×7) · แก้ 13 (`.htaccess`, `web.config`, `index/about/catalog/contact.php`, `admin/assets/admin.css|js`, `admin/blocks.php`, `includes/admin/access.php|init.php`, `includes/lib/content.php`, `includes/schema/lists.php`) · ลบ 0 · DB ไม่เปลี่ยน
- **Backup:** 13 ไฟล์ที่ถูกทับ เก็บใน scratchpad ของ session
- **Verify:** ✅ 4 หน้า + login 200 · admin (lists/preview) → 302 login · `*.html` / หน้าเก่า → 301 · `includes/ connectgrp.php` → 404 · `uploads/`: `.php`/`.PHP`/`.svg` → 404, `.png`/`.webp` → 200 (ทดสอบด้วยไฟล์ชั่วคราวแล้วลบ) · `.webp` = `image/webp`, `.woff2` = `font/woff2` · ไฟล์บน server ตรงกับ HEAD ทุกไฟล์ · HTML: about/catalog/contact ตรงกับ local, index ต่างที่รูปขั้น 03 (local มีรูปที่ผู้ใช้อัปโหลดในเครื่อง dev → server แสดง Image pending ตามออกแบบ) · ไฟล์ admin ชุดที่ deploy รันด้วย session จำลองไม่มี error · URL สาธารณะผลเหมือนกัน
- **Note:** `uploads/` บน server ยังไม่มี — PHP จะสร้างเองตอนอัปโหลดครั้งแรก (ให้ผู้ใช้ลองอัปโหลด 1 รูปบน .70 เพื่อยืนยันสิทธิ์เขียน) · รูปที่อัปโหลดบนเครื่อง dev ไม่ตามมาที่ server (uploads แยกต่อเครื่อง)

## v1.4.0 — 2026-10-07 10:41 · dev

- **Commit:** `3ef2413` · **Tag:** `v1.4.0`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:** หลังบ้านรองรับมือถือ/แท็บเล็ต — < 1024px เมนูเป็น drawer เปิดจากปุ่ม ☰ · มือถือ: แถบบนย่อ, ตารางเป็นการ์ด, แท็บเลื่อนแนวนอน, toolbar ไม่ค้างบังจอ, ช่องกรอก 16px กัน iPhone ซูม · แก้หน้าเนื้อหาสั้นถูกดันลงล่างบนจอแคบ
- **Files:** แก้ 6 (`admin/assets/admin.css`, `admin/assets/admin.js`, `admin/index.php`, `admin/roles.php`, `admin/users.php`, `includes/admin/init.php`) · DB ไม่เปลี่ยน
- **Backup:** 6 ไฟล์ที่ถูกทับ เก็บใน scratchpad ของ session
- **Verify:** ✅ 4 หน้า + login 200 · หน้า admin → 302 login · `*.html` / หน้าเก่า → 301 · `includes/` และ `connectgrp.php` → 404 · HTML จาก server ตรงกับ local ทุกหน้า · `admin.css` บน server เป็นชุดใหม่ · ไฟล์ admin ชุดที่ deploy รันด้วย session จำลอง 5 หน้า มีปุ่ม ☰ ไม่มี error · URL สาธารณะผลเหมือนกัน
- **Note:** ตรวจหน้าตาด้วย headless Chrome ที่ 390 / 820 / 1400px — การแตะเปิด/ปิด drawer บนมือถือจริงให้ผู้ใช้ลอง

## v1.3.1 — 2026-10-07 10:31 · dev

- **Commit:** `82da73e` · **Tag:** `v1.3.1`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:** คนลาออก (บัญชี `sysmnuser` ถูกล็อก) ถูกถอน role ในเว็บอัตโนมัติเมื่อเปิดหน้าผู้ใช้/role + บันทึก audit + แจ้งครั้งเดียว
- **Files:** แก้ 3 (`admin/roles.php`, `admin/users.php`, `includes/admin/access.php`) · DB ไม่เปลี่ยน
- **Backup:** 3 ไฟล์ที่ถูกทับ เก็บใน scratchpad ของ session
- **Verify:** ✅ 4 หน้า + login 200 · users/roles → 302 login · `includes/` และ `connectgrp.php` → 404 · HTML จาก server ตรงกับ local ทุกหน้า · ไฟล์ admin ชุดที่ deploy รันด้วย session จำลองไม่มี error · URL สาธารณะผลเหมือนกัน

## v1.3.0 — 2026-10-07 10:16 · dev

- **Commit:** `a0a206e` · **Tag:** `v1.3.0`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:**
  - **login หลังบ้านได้เฉพาะคนที่ถูกเลือก** (มี role) — ยกเลิกสิทธิ์อัตโนมัติ level ≥ 5 · รหัสถูกแต่ไม่มี role = ปฏิเสธ · ถอน role หมด = หลุดทันที · ห้ามถอนผู้ดูแลคนสุดท้าย
  - หน้าผู้ใช้ & สิทธิ์: dropdown เลือกผู้ใช้ จัดกลุ่มตามแผนก เรียง A→Z (แสดงชื่ออย่างเดียว) · กดบันทึกแล้วกลับหน้ารายการ · ปุ่มกลับทรงแคปซูล
  - สลับตำแหน่ง: "ดูหน้าเว็บไซต์" ไปขวาแถบบน, ชื่อผู้ใช้ + ออกจากระบบ ลงล่าง sidebar · แก้หลังบ้านบนมือถือกว้างเกินจอ
- **DB:** `009_lyiweb_login_selected_only.sql` — รันแล้วทั้ง `test_LYI` และ `LYI` (ตรวจแล้ว: `admin_min_level` = 0, itti.p = ผู้ดูแลระบบ) · `test_LYI` มีผู้ดูแล 3 คน (itti.p, user 71, Tadsanai — ผู้ใช้เพิ่มเอง)
- **Files:** เพิ่ม 0 · แก้ 6 (`admin/assets/admin.css`, `admin/assets/admin.js`, `admin/roles.php`, `admin/users.php`, `includes/admin/init.php`, `includes/lib/auth.php`) · ลบ 0
- **Backup:** 6 ไฟล์ที่ถูกทับ เก็บใน scratchpad ของ session
- **Verify:** ✅ 4 หน้า + login 200 · หน้า admin → 302 login · `*.html` / หน้าเก่า → 301 · `includes/ docs/ connectgrp.php` → 404 · HTML จาก server ตรงกับ local ทุกหน้า · รันไฟล์ admin ชุดที่ deploy ด้วย session จำลอง: ผู้ดูแลเปิดได้ทุกหน้าไม่มี error, ผู้ใช้ที่ไม่มี role ถูกส่งไปหน้า login · URL สาธารณะผลเหมือนกัน
- **Note:** ผู้ใช้อื่นที่ login ค้างบน .70 และไม่มี role จะหลุดใน request ถัดไป (ตั้งใจ)

## v1.2.0 — 2026-10-07 09:37 · dev

- **Commit:** `c7fb7e7` · **Tag:** `v1.2.0`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:**
  - หลังบ้าน: หน้า **ผู้ใช้ & สิทธิ์** (ค้นหาผู้ใช้จาก sysmnuser, ให้/ถอน role) และ **บทบาท (Role)** (สร้าง/แก้/ลบ role, ติ๊กหน้า/ส่วนที่แก้ได้)
  - หน้าแก้ข้อความแสดงและรับเฉพาะส่วนที่ผู้ใช้มีสิทธิ์ · POST ไปหน้าที่ไม่มีสิทธิ์ = 403 · แดชบอร์ดแสดงส่วนที่ดูแล
- **DB:** `008_lyiweb_role_scopes.sql` — รันแล้วทั้ง `test_LYI` (agent) และ `LYI` (ผู้ใช้ผ่าน Navicat, ตรวจแล้ว: admin/editor/translator = `*`)
- **Files:** เพิ่ม 3 (`admin/roles.php`, `admin/users.php`, `includes/admin/access.php`) · แก้ 6 (`admin/assets/admin.css`, `admin/assets/admin.js`, `admin/blocks.php`, `admin/index.php`, `includes/admin/init.php`, `includes/lib/auth.php`) · ลบ 0
- **Backup:** 6 ไฟล์ที่ถูกทับ เก็บใน scratchpad ของ session
- **Verify:** ✅ 4 หน้า + login 200 · หน้า admin (รวม users/roles) → 302 login เมื่อยังไม่ login · `*.html` / หน้าเก่า → 301 · `includes/ docs/ connectgrp.php` → 404 · HTML จาก server ตรงกับ local ทุกหน้า · รันไฟล์ admin ชุดที่ deploy ด้วย session จำลอง (users/roles/blocks/dashboard) ไม่มี error · URL สาธารณะผลเหมือนกัน
- **Note:** หน้า admin ที่ต้อง login ยังไม่ได้ทดสอบผ่าน IIS ด้วยบัญชีจริง — ผู้ใช้ควรเข้าลองที่ `/admin/users.php`

## v1.1.0 — 2026-10-06 17:52 · dev

- **Commit:** `aa5a4cd` · **Tag:** `v1.1.0` (ข้ามจาก v1.0.1 — มีฟีเจอร์ใหม่ = MINOR)
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:**
  - เนื้อหาทั้งเว็บอ่านจาก DB (`test_LYI`): รายการหน้าแรก, ข้อความ 272 ชิ้น, ข้อมูลติดต่อ, ชื่อหน้า/SEO — มี cache + fallback ในโค้ด
  - **หลังบ้าน `/admin/`**: login ด้วย `sysmnuser`, สิทธิ์, แดชบอร์ด, แก้ข้อความ/SEO (เมนูย่อยรายส่วน), แก้ข้อมูลติดต่อ
  - วิดีโอ YouTube หน้า About, รูป "Image pending", redirect 301 หน้าเว็บเก่า 8 หน้า → หน้าแรก
  - รูปทั้งหมดเป็นไฟล์ในเว็บ จัดหมวดใน `assets/img/` (ขั้นตอนผลิตเป็นไฟล์จริงจาก FTP แทนลิงก์), CSS/JS มี `?v=` กัน cache
  - บล็อก `includes/ cache/ tools/ docs/` และ `connectgrp.php` จากเว็บ
- **Files:** เพิ่ม 44 · แก้ 11 · ลบ 22 (ไฟล์ `.md` 3, รูปที่ย้ายโฟลเดอร์ 19) · วาง `connectgrp.php` (manual, ไม่อยู่ใน git) — `.htaccess` + `web.config` deploy ซ้ำหลังพบว่า `connectgrp.php` เปิดจากเว็บได้
- **Backup:** สำเนาโฟลเดอร์ server ก่อน deploy 63 ไฟล์ (17MB) เก็บใน scratchpad ของ session (ไม่ได้วางบน N: เพราะเปิดผ่านเว็บได้)
- **Verify:** ✅ 4 หน้า + admin login 200 · `/admin/` → 302 login · `*.html` / หน้าเก่า → 301 · `includes/ cache/ tools/ docs/ connectgrp.php README.md` → 404 · `.woff2` = `font/woff2` · รูป/CSS/JS 45/45 โหลดได้ · HTML จาก server ตรงกับ local ทุกหน้า · IIS เขียน `cache/` ได้ · URL สาธารณะ 200
- **Note:** server กับเครื่อง dev ใช้ `test_LYI` ร่วมกัน — แก้ในหลังบ้านเครื่องหนึ่ง cache อีกเครื่องไม่ถูกล้าง (เห็นผลช้าสุด 5 นาที)
## v1.0.1 — 2026-10-05 14:56 · dev

- **Commit:** `c2db765` · **Tag:** `v1.0.1`
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev` → http://192.168.0.70/lyindustries-dev/ · https://lysystems.sytes.net/lyindustries-dev/
- **Changes:**
  - กู้ `web.config` ของเว็บนี้กลับมา — ไฟล์บน server ถูกแทนด้วย `web.config` ของ lyi-dashboard (rewrite ไป `public/`) ทำให้ทุกหน้า 404
  - เปลี่ยน handler เป็น `PHP84_lyi`, verb `GET,HEAD,POST,DELETE`
- **Files:** `web.config`
- **Verify:** ✅ ทุกหน้า 200 · `*.html` → 301 · `includes/` → 404 · HTML จาก server ตรงกับ local
- **Note:** สำรอง web.config ที่ถูกทับไว้ใน scratchpad ของ session (ไม่ได้อยู่ใน repo)

## v1.0.0 — 2026-10-05 14:50 · dev

- **Commit:** ไม่มี — deploy จาก working tree ก่อน commit; source เท่ากับ `c2db765` ทุกไฟล์ ยกเว้น `web.config` (handler ชื่อ `PHP84_lyindustries`, verb `GET,HEAD,POST`) · ไม่มี tag
- **Target:** `\\192.168.0.70\wwwroot\lyindustries-dev`
- **Changes:** deploy เว็บ PHP 8.4 ครั้งแรก แทนเว็บ static HTML เดิม
  - ลบ `index.html`, `about.html`, `catalog.html`, `contact.html` (เวอร์ชันเดิม = commit `dfa86e1`)
  - เพิ่ม `*.php`, `includes/`, `assets/css|fonts|img|js/`, `web.config`, `.htaccess`, `*.md`
- **Verify:** ✅ ทุกหน้า 200 · `*.html` → 301 · `includes/` → 404 · `.woff2` = `font/woff2` · HTML จาก server ตรงกับ local
- **Note:** `README.md` ที่ deploy ในรอบนี้ถูกลบออกจาก repo แล้ว (`e816e03`) — ยังค้างบน server จนกว่าจะ deploy รอบถัดไป
