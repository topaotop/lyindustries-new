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
