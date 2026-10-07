# CLAUDE.md — L.Y. Industries website (lyindustries-new)

คู่มือสำหรับ agent ที่ทำงานในโปรเจกต์นี้ อ่านคู่กับ [PROJECT_STATUS.md](PROJECT_STATUS.md) (สถานะงาน/สิ่งที่ค้าง) ก่อนเริ่มงานทุกครั้ง

## Project overview

เว็บไซต์บริษัท **L.Y. Industries Co., Ltd. (LYI)** ผู้ผลิต Narrow Fabric & Trims (ยางยืด เทปทอ เทปถัก เชือก ขอบเอว งาน finishing) ในกรุงเทพฯ ก่อตั้ง 1978
- เป้าหมายโปรเจกต์: เว็บใหม่ที่ทำ **AEO (Answer Engine Optimization)** สำหรับ Narrow Fabric — เนื้อหาตอบคำถามได้ตรง + schema.org
- ภาษาหลักของเนื้อหา: ไทย (มี eyebrow/label ภาษาอังกฤษ)
- 4 หน้า: หน้าแรก, เกี่ยวกับเรา, แคตาล็อกสินค้า, ติดต่อเรา
- ดีไซน์และเนื้อหา **LOCKED** แล้ว (23 ก.ย. 2026) โดย Pack — ดู [DESIGN-LOCK.md](DESIGN-LOCK.md)
- ต้นทางคือไฟล์ HTML ที่ออกแบบด้วยเครื่องมือออกแบบ (หน้าแรกเป็น bundle 20MB) → แปลงเป็น PHP 8.4 เมื่อ 5 ต.ค. 2026

## Tech stack

| ส่วน | ใช้อะไร |
|---|---|
| Server-side | PHP ล้วน (พัฒนาบน 8.4, **ต้องรันได้บน 8.2** ของ production) — ไม่มี framework, ไม่มี Composer, ยังไม่มี database (มี `connectgrp.php` เตรียมไว้สำหรับหลังบ้าน) |
| Front-end | HTML + CSS + vanilla JS (ไม่มี build step, ไม่มี npm) |
| ฟอนต์ | Anuphan (body), Kanit (heading), JetBrains Mono (mono) — หน้าแรก self-host ใน `assets/fonts/`; หน้าย่อยโหลดจาก Google Fonts |
| Web server (local) | XAMPP Apache + PHP 8.4 (mod_fcgid) → ใช้ `.htaccess` |
| Web server (company server) | IIS 8.5 บน 192.168.0.70 → ใช้ `web.config` |
| Web server (production) | LiteSpeed + PHP 8.2 บน z.com → ใช้ `.htaccess` |
| Schema | JSON-LD ในแต่ละหน้าย่อย (AboutPage/Organization, CollectionPage/ItemList/Product, ContactPage/ContactPoint/OpeningHours) |

## Architecture

เว็บ PHP แบบ page-per-file ไม่มี routing ทุกหน้า `require includes/bootstrap.php` ก่อน

```
request → index.php / about.php / catalog.php / contact.php
            └─ includes/bootstrap.php   APP_ROOT, SITE_PLACEHOLDER_IMG, e(), img_src(), site_nav(), external_attrs() แล้ว require includes/lib/content.php ให้ทุกหน้า
            └─ includes/lib/content.php content_list() รายการ · site() ค่าติดต่อ/ลิงก์ · page_meta() ชื่อหน้า+meta · b() ข้อความ · lang() · helper รูปแบบเบอร์/ที่อยู่/เวลา
            └─ includes/icons.php       ไอคอนไทล์ (content เก็บแค่ key, template เรียก icon_uri())
            └─ includes/site-header.php (about/catalog/contact) ตั้ง $activeNav, $quoteHref ก่อน require
            └─ includes/site-footer.php (catalog/contact) footer 4 คอลัมน์ — about ใช้ footer สั้นของตัวเอง
```

**หน้าแรก (`index.php`)**
- markup มาจาก design export → ใช้ inline `style="..."` เกือบทั้งหมด (ตั้งใจเก็บไว้ให้ตรงดีไซน์ที่ล็อก)
- รายการที่วนซ้ำ 7 list (marquee 8, ไทล์ 8, process 6, สี Pantone 5, สินค้า 6, gallery 6, FAQ 4) มาจาก `content_list()` ส่วนเมนูมือถือยังกำหนดในหัว `index.php`

**Content layer (`includes/lib/content.php`) — ข้อมูลรายการอยู่ใน DB ตาราง `lyiweb_items`**
- ลำดับการอ่าน: cache สด (`cache/content.json`, อายุ 5 นาที) → DB → cache เก่า → ข้อมูลในโค้ด `includes/home-data.php` (fallback ราย list — list ที่ไม่มีแถวใน DB ก็ใช้ fallback)
- DB ต่อไม่ได้ → เขียน `cache/db-unavailable` แล้วไม่ลองใหม่ 60 วินาที (กันหน้าเว็บรอ timeout) · env `LYIWEB_DB=off` = บังคับทางไม่มี DB (ใช้ทดสอบ)
- ฟิลด์/ลำดับฟิลด์/แปลได้ไหม กำหนดที่ `includes/schema/lists.php` (หลังบ้านจะสร้างฟอร์มจากไฟล์นี้) — ลำดับสำคัญเพราะสี swatch ถูก `json_encode` ลง `data-swatch`
- เครื่อง dev (local) กับ company server (.70) ใช้ `test_LYI` ร่วมกันแต่ cache แยกเครื่อง — แก้ในหลังบ้านของเครื่องหนึ่ง อีกเครื่องเห็นผลช้าสุด 5 นาที (หรือกด "ล้าง cache" ในแดชบอร์ดของเครื่องนั้น) · production ไม่มีปัญหานี้ (DB `LYI` + หลังบ้านอยู่เครื่องเดียวกับเว็บ)
- แก้ข้อมูลใน DB แล้วต้องเรียก `content_cache_clear()` (หลังบ้านจะทำให้) — ถ้าแก้ตรงใน Navicat หน้าเว็บจะเปลี่ยนภายใน 5 นาที
- **แก้ `includes/home-data.php` แล้วต้องรัน `php tools/build-seed-home.php`** ให้ `docs/sql/002_lyiweb_seed_home.sql` ตรงกันเสมอ (seed ใส่เฉพาะ list ที่ยังไม่มีแถว — ไม่ทับเนื้อหาที่แก้ในหลังบ้านแล้ว)
- **ข้อความของหน้า (`lyiweb_blocks`):** ทุกข้อความในหน้าเขียนเป็น `<?= b('page.section.nn') ?>` (escape ให้แล้ว) — key เช่น `home.hero.04`; ค่าเริ่มต้น/fallback อยู่ที่ `includes/blocks/<page>.php` · ภาษาอังกฤษว่าง → ใช้ไทย · ยังไม่ย้าย: เมนู `<header>`/`<aside>` (ใช้ร่วมทุกหน้า), คำนำหน้าที่ติดกับค่า settings เช่น "โทร: ", `alt`/`aria-label`
- **เพิ่มข้อความใหม่ในหน้า:** เพิ่ม key ใน `includes/blocks/<page>.php` + ใช้ `b()` ใน template แล้วรัน `php tools/build-seed-blocks.php` (ห้ามพิมพ์ข้อความตรงๆ ลง template) · ย้ายหน้าเดิมทั้งหน้า: `php tools/extract-blocks.php <file.php> <slug> [old=new]` (แทนที่ทุก text node + เขียนไฟล์ default; ตรวจ diff HTML หลังรันเสมอ)
- **ห้ามเขียน `?>` ในคอมเมนต์ `//` ของไฟล์ PHP** — PHP ถือว่าจบโค้ดตรงนั้น (เคยทำให้สคริปต์ parse error)
- `includes/lib/db.php`: `db()` (เปิด connection ผ่าน `connectgrp.php` เมื่อจำเป็นเท่านั้น), `db_rows($sql, $params)` — **ใช้ parameter `?` เสมอ ห้ามต่อ string เข้า SQL**
- hover effect = class `.hv-1` … `.hv-16` ใน `assets/css/home.css` (แปลงจาก attribute `style-hover` เดิม ใช้ `!important` เพราะต้องชนะ inline style)
- state ฝั่ง client (เมนูเปิด, FAQ ที่เปิด, สีที่เลือก) ใช้ class/data attribute: `html.menu-open`, `.faq-item.is-open`, `[data-swatch]`, `[data-menu-toggle]`, `[data-faq-toggle]`
- `assets/js/home.js` = interaction ทั้งหมด + scroll engine (requestAnimationFrame) ที่ขยับ `#lyProgress`, `#heroContent`, `[data-tile]`, `[data-step-*]` — จำนวนขั้น process อ่านจากจำนวน `[data-step-text]` ใน DOM
- **Section 03 Process pipeline = ดีไซน์เดิมของ Pack** (รูปเต็มจอเป็นพื้นหลัง `[data-step-bg]` object-fit: cover + เงามืด) — **ห้ามเปลี่ยนรูปแบบการแสดงผลส่วนนี้จนกว่าผู้ใช้สั่ง** (เคยลองแบบกรอบรูป 4:5 แล้วผู้ใช้ให้คืนแบบเดิม 6 ต.ค. 2026) · รูปเป็นไฟล์จริงจาก source เว็บ (FTP) ที่ `assets/img/process/` (bgvideo1, nl, BRAIDING, FINISHING, CROCHET .jpg) แทนการลิงก์ไป lyindustries.com · ขั้น 03 ยังไม่มีรูป (`dye-yarn-machine.png` ไม่มีในทุก source) → Image pending
- section ธีมสว่างใช้ `data-theme="light"` + override CSS variables inline บน `<section>`

**หน้าย่อย (`about.php`, `catalog.php`, `contact.php`)**
- ข้อความทุกชิ้นเป็น `b('about|catalog|contact.<section>.nn')` และ footer ร่วมของ catalog/contact เป็น `b('footer.main.nn')` (`includes/site-footer.php`) — footer สั้นของ about อยู่ใน `about.cta.*`
- แต่ละหน้ามี `<style>` ของตัวเองใน `<head>` (CSS ซ้ำกันบางส่วน เช่น `.nav`, `.brand .mark`) — แก้ส่วนที่ใช้ร่วมต้องแก้ทั้ง 3 หน้า
- ฟอร์มขอใบเสนอราคาใน `contact.php` ยังเป็น JS เปิด `mailto:` (ยังไม่มี backend)

## หลังบ้าน (Admin) — `admin/`

- เข้าใช้: `<site>/admin/` เช่น http://localhost/lyindustries-new-dev/admin/ · https://lysystems.sytes.net/lyindustries-dev/admin/
- Login ด้วยบัญชี `sysmnuser` (อ่านอย่างเดียว ห้ามเขียนตารางนี้) — ตรวจรหัสแบบเดียวกับระบบอื่นของบริษัท (plaintext `pass`/`password` ด้วย `hash_equals` หรือ `password_verify`), `locked = 1` เข้าไม่ได้ · session ชื่อ `LYIWEBADMIN` cookie จำกัด path `/…/admin/`, HttpOnly, SameSite=Lax, Secure เมื่อเป็น HTTPS · ไม่ใช้งาน 2 ชม. หลุด
- กันเดารหัส: ผิด 5 ครั้ง/username หรือ 20 ครั้ง/IP ใน 15 นาที → ล็อก (IP ตั้งสูงเพราะทั้งออฟฟิศออก internet ด้วย IP เดียว)
- **login ได้เฉพาะคนที่ถูกเลือก** (มี role ใน `lyiweb_user_roles` อย่างน้อย 1 — ผู้ใช้สั่ง 7 ต.ค. 2026, SQL 009) — รหัสถูกแต่ไม่มี role = ปฏิเสธ + นับเป็นครั้งที่ผิด · ถอน role หมด = หลุดใน request ถัดไป (`auth_has_access()` ใน `auth_user()`) · ห้ามถอน role ผู้ดูแลระบบจากคนสุดท้าย · คนที่ลาออก (`sysmnuser.locked = 1`) ถูกถอน role อัตโนมัติเมื่อเปิดหน้า users/roles (`admin_revoke_locked_users()`, บันทึก audit) — ไม่มีปุ่มลบแยก (ผู้ใช้ไม่อยากให้หน้ารก) · สิทธิ์อัตโนมัติจาก `sysmnuser.level >= admin_min_level` **ปิดแล้ว** (`admin_min_level` = 0; ตั้ง > 0 เพื่อเปิดกลับ)
- สิทธิ์: ตาม role ใน `lyiweb_user_roles` · permission: `content.edit` (แก้ไทย+อังกฤษ), `content.translate` (แก้อังกฤษอย่างเดียว), `media.upload`, `contact.view`, `settings.edit`, `users.manage` — อ่านใหม่ทุก request (ถอนสิทธิ์มีผลทันที)
- **ขอบเขตรายหน้า/รายส่วน** (`lyiweb_role_scopes`, SQL 008): `content.edit`/`content.translate` ใช้ได้เฉพาะ scope ของ role เดียวกัน — `*` ทุกส่วน · `home` ทั้งหน้า (รวมส่วนที่เพิ่มในอนาคต) · `home.hero` ส่วนเดียว · `home.seo` ชื่อหน้า/meta · role ที่มีสิทธิ์เนื้อหาแต่ไม่มี scope = แก้ไม่ได้สักส่วน · ตรวจด้วย `can_content('th'|'en', $page, $section)` (`auth.php`) — **ทุกหน้าที่แก้เนื้อหา (รวม 3b แก้รายการ) ต้องเช็กทั้งตอนแสดงและตอน POST** และ POST ไปหน้าที่ไม่มีสิทธิ์ต้อง 403 ห้าม fallback ไปหน้าอื่น · รายการหน้า/ส่วน + ชื่อไทยอยู่ที่ `admin_content_pages()` (`includes/admin/access.php`, ส่วนอ่านจาก `includes/blocks/<page>.php` อัตโนมัติ) · role `admin` แก้ไม่ได้, role ระบบลบไม่ได้, แก้สิทธิ์ตัวเองไม่ได้
- ทุกฟอร์ม POST ต้องมี CSRF (`csrf_field()` / `admin_check_post()`), ทุกการบันทึก → `audit_log()` + `content_cache_clear()`, header no-store / X-Frame-Options DENY / noindex
- หน้ายาว (แก้ข้อความ) ใช้ **เมนูย่อยรายส่วน** (`[data-subnav]` + fieldset `data-section-key`) แสดงทีละส่วน — หน้าใหม่ที่มีหลายกลุ่ม (เช่น แก้รายการ 3b) ให้ใช้แบบเดียวกัน แทนการให้เลื่อนยาว
- เมนู sidebar กำหนดที่ `admin_menu()` ใน `includes/admin/init.php` (จัดกลุ่ม + ไอคอนจาก `admin_icon()` + permission) — หน้าใหม่ให้เพิ่มที่นี่ · ดีไซน์: พื้นเข้มต่อแถบบน, เมนูที่เลือกมีแถบลายเทปถัก (`--tape`) — ไม่ใช้ตัวพิมพ์ใหญ่ทั้งคำ/ลูกศรท้ายเมนู · **Responsive** (7 ต.ค. 2026): < 1024px sidebar เป็น drawer เปิดจากปุ่ม ☰ (`[data-nav-toggle]`, `html.nav-open`), ≤ 640px ตารางที่มี class `rtable` กลายเป็นการ์ด (ใส่ `data-label` ที่ `<td>`; ช่องปุ่มใช้ class `act`), แท็บเลื่อนแนวนอน, ช่องกรอก 16px กัน iPhone ซูม — กฎ responsive อยู่ท้าย `admin.css` (ต้องอยู่หลังกฎปกติ) · ตารางใหม่ในหลังบ้านให้ใส่ `rtable` + `data-label` เสมอ · ปุ่ม "ดูหน้าเว็บไซต์" อยู่ขวาของแถบบน, ชื่อผู้ใช้ + ออกจากระบบ อยู่ล่าง sidebar (ผู้ใช้สั่งสลับ 7 ต.ค. 2026)
- UX: หลังบันทึกต้องกลับมาตำแหน่งเดิม (เก็บ scroll ใน sessionStorage ตอน submit, `admin.js`) — **ห้ามทำให้หน้าเด้งขึ้นบนสุด** (ผู้ใช้ไม่ชอบ) · ข้อความผลการบันทึก (`flash()` → `[data-flash]`) แสดงเป็น toast มุมขวาบน · มี error → เลื่อนไปช่องแรกที่ผิด
- แถบด้านบนแสดง DB ที่ต่ออยู่ (`test_LYI` เขียว / `LYI` แดง = PRODUCTION) กันแก้ผิดที่
- ไฟล์: `admin/*.php` (หน้า), `admin/assets/` (CSS/JS), `includes/admin/init.php` (require ก่อนทุกหน้า: auth, header, layout `admin_page_start/end()`, `admin_require(perm)`), `includes/admin/access.php` (ป้ายสิทธิ์, หน้า/ส่วน, `admin_roles()`, สรุป scope), `includes/lib/auth.php`
- หน้าที่มี: แดชบอร์ด (สถิติ, แก้ไขล่าสุด, ล้าง cache) · ข้อความหน้าเว็บ & SEO (`blocks.php` แท็บต่อหน้า, ไทย/อังกฤษ, ค้นหา, กรองที่ยังไม่แปล) · ข้อมูลติดต่อ & ลิงก์ (`settings.php` มี validate อีเมล/URL/เวลา/เบอร์) · ผู้ใช้ & สิทธิ์ (`users.php` dropdown ค้นหาผู้ใช้ sysmnuser ที่ไม่ถูกล็อก จัดกลุ่มตามแผนก เรียง A→Z — ไทยเรียงแบบพจนานุกรม (สระหน้า เ แ โ ใ ไ นับตามพยัญชนะ), `-` = ไม่ระบุแผนก ไว้ท้าย · ให้/ถอน role = เลือกคนที่ login ได้) · บทบาท (`roles.php` สร้าง/แก้/ลบ role: สิทธิ์ + ติ๊กหน้า/ส่วนที่แก้ได้)
- ทดสอบหน้าที่ต้อง login โดยไม่มีรหัสจริง: จำลอง session ใน CLI (ตั้ง `$_SESSION['lyiweb_user']` + `lyiweb_csrf` แล้ว require หน้า) — เขียนได้เฉพาะ `test_LYI` และต้องคืนค่าหลังทดสอบ

## Folder structure

```
/
├─ index.php, about.php, catalog.php, contact.php   หน้าเว็บ
├─ includes/            PHP partial/ข้อมูล (ห้ามเปิดตรงจากเว็บ — บล็อกทั้งใน .htaccess และ web.config)
│  ├─ lib/db.php, lib/content.php   ชั้น DB + content (cache/fallback)
│  ├─ schema/lists.php              นิยามฟิลด์ของแต่ละ list
│  ├─ home-data.php                 ข้อมูลรายการหน้าแรกในโค้ด = fallback + ต้นทางของ seed 002
│  ├─ site-defaults.php             ค่าติดต่อ/ลิงก์ + ชื่อหน้า/meta = fallback + ต้นทางของ seed 003
│  ├─ blocks/<page>.php             ข้อความแต่ละหน้า (home 129, about 52, catalog 49, contact 28, footer 14 = footer ร่วมของ catalog/contact) = fallback + ต้นทางของ seed 004
│  └─ icons.php                     ไอคอน SVG ของไทล์
├─ admin/               หลังบ้าน (ดูหัวข้อ "หลังบ้าน") — deploy ด้วย
├─ includes/admin/      init.php ของหลังบ้าน (บล็อกจากเว็บเพราะอยู่ใต้ includes/)
├─ tools/               สคริปต์ CLI (build-seed-home/site/blocks.php, extract-blocks.php + lib-textnodes.php) — บล็อกจากเว็บ, ไม่ deploy
├─ cache/               สร้างอัตโนมัติตอนรัน (content.json) — อยู่ใน .gitignore, บล็อกจากเว็บ, ไม่ deploy
├─ assets/
│  ├─ img/                    รูปทั้งหมด แยกโฟลเดอร์ตามหมวด (จัด 7 ต.ค. 2026) — รูปใหม่ให้วางในหมวดที่ตรง ห้ามวางที่ assets/ หรือ img/ ตรงๆ
│  │  ├─ brand/               logo-lyi.svg, apple-touch-icon.png, placeholder.svg ("Image pending")
│  │  ├─ applications/        app-*.jpg — ไทล์จุดใช้งานบนเสื้อผ้า (home.tiles)
│  │  ├─ products/            prod-*.jpg — หมวดสินค้า (home.products + catalog.php)
│  │  ├─ process/             bgvideo1, nl, BRAIDING, FINISHING, CROCHET .jpg — ขั้นตอนผลิต (home.steps)
│  │  ├─ rnd/                 rnd-team.png — ส่วน R&D หน้าแรก
│  │  └─ colorlab/            dye-dispenser, spectrophotometer, lab-dip .png — ส่วนโรงย้อม/Color Lab หน้าแรก (PNG ใหญ่)
│  ├─ css/home.css, fonts.css สไตล์หน้าแรก / @font-face
│  ├─ fonts/                  woff2 ที่ self-host
│  └─ js/home.js              JS หน้าแรก
├─ connectgrp.php       การเชื่อมต่อ SQL Server (sqlsrv) — มีรหัสผ่าน, อยู่ใน .gitignore (ไม่อยู่ใน git — สำรองเอง); `includes/lib/db.php` require เมื่อต้อง query — ถ้าไม่มีไฟล์นี้บน server เว็บยังขึ้นด้วย fallback
├─ .gitignore           connectgrp.php, /cache/, .DS_Store, Thumbs.db, desktop.ini, .vscode/, .idea/
├─ .htaccess            Apache/LiteSpeed: DirectoryIndex, 301 *.html → *.php, 301 หน้าเว็บเก่า 8 หน้า → หน้าแรก, บล็อก includes/ cache/ tools/ docs/ และ connectgrp.php
├─ web.config           IIS: handler PHP 8.4, defaultDocument, MIME .woff2, hiddenSegments includes/cache/tools/docs, 301 *.html → *.php, 301 หน้าเว็บเก่า 8 หน้า → หน้าแรก
├─ DESIGN-LOCK.md       สเปกดีไซน์ที่ล็อก — แหล่งอ้างอิงหลัก
├─ CONTENT-DRAFT.md     ร่างเนื้อหาก่อนล็อก (อ้างอิงเท่านั้น ขัดกับ DESIGN-LOCK ให้ยึด DESIGN-LOCK)
├─ docs/design/         เอกสารออกแบบ — admin-i18n-api.md (หลังบ้าน + 2 ภาษา + API)
├─ docs/sql/            สคริปต์ SQL Server เรียงเลข (001_lyiweb_schema.sql, 002_lyiweb_seed_home.sql, 003_lyiweb_seed_site.sql, 004_lyiweb_seed_blocks.sql ← generated, 005/006 รูปขั้นตอนผลิต, 007 ย้ายโฟลเดอร์รูป, 008 ขอบเขต role …) · **ในคอมเมนต์ SQL ห้ามมี `/*` ซ้อน** (SQL Server นับเป็น comment ซ้อน) — รันใน test_LYI ก่อนเสมอ แล้วค่อย LYI; ทุกไฟล์ต้องรันซ้ำได้ปลอดภัย
├─ CLAUDE.md            ไฟล์นี้
├─ PROJECT_STATUS.md    สถานะงาน
└─ DEPLOY_LOG.md        ประวัติ deploy พร้อม version
```

## Key links & contact data

ทั้งหมดอยู่ในตาราง `lyiweb_settings` (แก้ได้ในหลังบ้าน) — ในโค้ดเรียก `site('key')` · ค่าเริ่มต้น/fallback อยู่ที่ `includes/site-defaults.php`

| อะไร | key ใน settings | ค่าปัจจุบัน | ใช้ที่ไหน |
|---|---|---|---|
| โทร | `phone`, `phone_ext` | 02-517-0768 ต่อ 120, 121 | ข้อความ + `tel_href_local()` (`tel:025170768` หน้าแรก) / `tel_href_intl()` (`tel:+6625170768`) + `phone_schema()` (`+66-2-517-0768` ใน JSON-LD) — แก้เบอร์ครั้งเดียวเปลี่ยนทุกรูปแบบ |
| แฟกซ์ | `fax` | 02-517-4888 | หน้า contact + schema |
| อีเมล | `email` | sales@lyindustries.com | ลิงก์ `mailto:` ทุกหน้า, footer, schema, ฟอร์ม contact |
| LINE OA | `line_id`, `line_url` | @lyindustries · https://line.me/R/ti/p/@lyindustries | ปุ่ม/ลิงก์ LINE, schema `sameAs` |
| บริษัท/ที่อยู่ | `company_th`, `address1_th`, `address2_th` | บริษัท แอล วาย อินดัสตรีย์ จำกัด · 124 ซอยรามอินทรา 109 ถนนพระยาสุเรนทร์ แขวงบางชัน · เขตคลองสามวา กรุงเทพฯ 10510 | `address_th_html($sep)` — หน้าแรกขึ้นบรรทัดใหม่ระหว่าง 2 บรรทัด ที่อื่นคั่นด้วยเว้นวรรค |
| เวลาทำการ | `hours_weekday_open/close`, `hours_sat_open/close` | 08:30–17:30 · ส. 08:30–12:00 | `hours_th_html()` + schema OpeningHours |
| TRIMRITE® | `trimrite_url` | https://www.trimrite.com/ | เมนู (เปิดแท็บใหม่) |
| Inspiration Hub | `inspiration_url` | https://www.lyindustries.com/lyinspirationhub/ | ปุ่ม "สินค้าเพิ่มเติม" / "ดูแคตตาล็อกทั้งหมด" |

- ชื่อหน้า `<title>` + meta description อยู่ในตาราง `lyiweb_pages` → `page_meta('slug')` · meta description ของหน้า contact มีเบอร์/อีเมลเขียนอยู่ในประโยค — **เปลี่ยนเบอร์ใน settings แล้วต้องแก้ meta description เองด้วย**
- ที่อยู่ภาษาอังกฤษใน schema (JSON-LD) และลิงก์ Google Maps ยังเขียนตรงในหน้า (จะย้ายตอนทำ 2 ภาษา)

- ลิงก์กลับหน้าแรกจากหน้าย่อย: `index.php` และ `index.php#process` (กระบวนการผลิตเป็น section ในหน้าแรก ไม่ใช่หน้าแยก)
- **เมนูให้ยึด DESIGN-LOCK:** CONTENT-DRAFT ฉบับร่างมีหน้า `/sample` และ `/process` แยก — ไม่มีในเว็บจริง อย่าสร้างเพิ่มเอง
- Schema หน้า contact = `ContactPage` + `["Organization","LocalBusiness"]` (มี ContactPoint, OpeningHoursSpecification, PostalAddress) ต้องคงไว้ตอนย้ายขึ้นระบบจริง

## Coding conventions

- ทุกไฟล์ PHP ขึ้นต้น `declare(strict_types=1);` (ยกเว้น partial ที่เป็น template ล้วน)
- **escape ทุกค่าที่ echo ด้วย `e()`** (`htmlspecialchars` ENT_QUOTES|ENT_HTML5 UTF-8) ใช้ `<?= e($x) ?>` ใน template
- template ใช้ alternative syntax: `<?php foreach (...): ?> … <?php endforeach; ?>`, `<?php if (...): ?> … <?php endif; ?>`
- array ข้อมูลใช้ key ภาษาอังกฤษสั้นๆ ตามของเดิม (`th`, `en`, `img`, `code`, …) — ค่า `img` ว่าง `''` = ยังไม่มีรูป → ใช้ `img_src($img)` ซึ่งคืน `assets/img/placeholder.svg` ("Image pending") ให้คนเห็นว่าต้องอัปโหลด; ห้ามปล่อย `<img>` ชี้ไฟล์ที่ไม่มี
- ลิงก์ออกนอกเว็บใช้ `'external' => true` + `external_attrs()` → `target="_blank" rel="noopener"`
- ค่าที่ใช้หลายที่ (โทร, อีเมล, LINE, ที่อยู่, เวลา, URL ภายนอก) **ห้าม hard-code** — ใช้ `site('key')` (เพิ่ม key ใหม่ใน `includes/site-defaults.php` แล้วรัน `php tools/build-seed-site.php`) · ใน JSON-LD ใช้ `json_inner()` แทน `e()`
- **ไฟล์ CSS/JS ของเราต้องใส่ผ่าน `asset()` เสมอ** เช่น `<link href="<?= e(asset('assets/css/home.css')) ?>">` → ต่อท้าย `?v=<เวลาแก้ไฟล์>` ให้เบราว์เซอร์โหลดไฟล์ใหม่ทันที (เซิร์ฟเวอร์ไม่ส่ง Cache-Control — ถ้าไม่ใส่ เบราว์เซอร์ใช้ไฟล์เก่าค้าง เคยทำให้ผู้ใช้ไม่เห็นการแก้)
- ลิงก์ภายในใช้ path แบบ relative (`about.php`, `index.php#process`) — เว็บต้องทำงานได้ใต้ sub-folder (`/lyindustries-dev/`, `/lyindustries-new-dev/`)
- JS: vanilla ES2017+, IIFE + `'use strict'`, เลือก element ด้วย `data-*` attribute; ไม่เพิ่ม library
- CSS หน้าแรก: เก็บสไตล์แบบ inline ตามเดิม; hover/state ใหม่ให้เพิ่มเป็น class ใน `home.css`
- คอมเมนต์ในโค้ดเป็นภาษาอังกฤษ สั้น; เอกสาร (.md) และคอมเมนต์ใน config ภาษาไทยได้
- Git ตั้ง `core.autocrlf` (warning LF→CRLF เป็นเรื่องปกติ)

## Development environment

- โฟลเดอร์งาน: `C:\xampp\htdocs\lyindustries-new-dev` บนเครื่อง `COM-CPU-055` (192.168.0.125)
- เปิดดู: http://localhost/lyindustries-new-dev/ (Apache ของ XAMPP รัน **PHP 8.4.12** ผ่าน fcgid)
- ⚠️ `/tmp` ของ Git Bash ≠ `/tmp` ของ PHP/Windows (PHP อ่าน `C:	mp`) — ไฟล์ที่ส่งต่อให้ PHP ให้ใช้ path Windows (`cygpath -w`) หรือ scratchpad และ**ตรวจทุกครั้งว่าอ่านไฟล์ได้ก่อนเขียนทับ** (เคยทำให้ section หายจาก index.php)
- PHP CLI: `php` ใน PATH = 8.4 (Laragon) ใช้เป็นหลัก · `C:\xampp\php\php.exe` = **8.2** ใช้เช็กความเข้ากันได้กับ production
- ไม่มี automated test — วิธีตรวจหลังแก้:
  1. `php -l <file>` ทุกไฟล์ที่แก้
  2. render `php index.php > out.html` แล้วดูว่าไม่มี warning/notice
  3. ถ้าเป็นการ refactor ที่ไม่ควรเปลี่ยนผลลัพธ์ ให้ diff HTML ก่อน/หลัง
  4. **เช็ก PHP 8.2:** render ทุกหน้าด้วย `C:\xampp\php\php.exe` แล้ว `cmp` กับผลของ 8.4 ต้องตรงกัน
  5. ตรวจหน้าตาด้วย headless Chrome (`chrome.exe --headless=new --screenshot=...`) — ข้อจำกัด: ความกว้างขั้นต่ำ 500px และ rAF/scroll animation ทดสอบใน headless ได้ไม่ครบ ต้องให้คนเช็กในเบราว์เซอร์จริง

## Environments

| | 1. Local dev (เครื่องผู้พัฒนา) | 2. Company server (dev/test สาธารณะ) | 3. Production |
|---|---|---|---|
| เครื่อง | `COM-CPU-055` · 192.168.0.125 | 192.168.0.70 (ในบริษัท) | Web hosting **z.com** |
| URL | http://localhost/lyindustries-new-dev/ · http://192.168.0.125/lyindustries-new-dev/ | ภายใน: http://192.168.0.70/lyindustries-dev/ · **ภายนอก: https://lysystems.sytes.net/lyindustries-dev/** (รูปแบบ `https://lysystems.sytes.net/<ชื่อโฟลเดอร์>/`) | https://www.lyindustries.com/ — ตอนนี้ยังเป็นเว็บเดิม เว็บนี้ยังไม่ได้ขึ้น |
| Path | `C:\xampp\htdocs\lyindustries-new-dev` | `N:\lyindustries-dev` = `\\192.168.0.70\wwwroot\lyindustries-dev` | — (ยังไม่ทราบวิธี upload) |
| Web server | XAMPP Apache + PHP 8.4.12 (fcgid) → `.htaccess` | IIS 8.5 + URL Rewrite → `web.config`; PHP default ของ IIS = **7.1** ต้องสลับเป็น `C:\PHP84` | **LiteSpeed + PHP 8.2** (เช็ก 5 ต.ค. 2026) → อ่าน `.htaccess` |
| DB (เมื่อมีหลังบ้าน) | SQL Server 192.168.0.22 · `test_LYI` | ควรใช้ `test_LYI` (192.168.0.22) — ดูข้อควรระวังด้านล่าง | SQL Server ในบริษัทผ่าน IP สาธารณะ **183.89.245.21** · `LYI` |

**ผลต่อโค้ด**
- **โค้ดต้องรันได้บน PHP 8.2** เพราะ production เป็น 8.2 ห้ามใช้ฟีเจอร์ที่มีเฉพาะ 8.3/8.4 (เช่น typed class constants, `#[\Override]`, `json_validate()`, property hooks, asymmetric visibility, `new` แบบไม่ใส่วงเล็บแล้ว chain) — ตรวจด้วยการรันกับ PHP 8.2 ก่อน deploy production
- ต้องมีกฎทั้ง `.htaccess` (local + production LiteSpeed) และ `web.config` (company server IIS) ให้ตรงกันเสมอ
- `connectgrp.php` เลือก environment จาก**โดเมนที่เข้าเว็บ** (`$_SERVER['HTTP_HOST']`, แก้ 6 ต.ค. 2026): `www.lyindustries.com` / `lyindustries.com` = `production` (DB `LYI` @ 183.89.245.21) · `lysystems.sytes.net` / `192.168.0.70` = `test` · อื่นๆ ทั้งหมดรวม CLI = `dev` (ทั้ง test/dev ใช้ `test_LYI` @ 192.168.0.22) — โดเมนที่ไม่รู้จักจะตกไป DB ทดสอบเสมอ ถ้าเพิ่มโดเมน production ใหม่ต้องเพิ่มใน `$PROD_HOSTS`
- **SQL Server 2012 (v11), compatibility level 100** ทั้ง `LYI` และ `test_LYI` → ห้ามใช้ฟังก์ชัน JSON ของ SQL (`JSON_VALUE`, `OPENJSON`), `OFFSET…FETCH`, `STRING_AGG`, `TRY_CONVERT`/`IIF` ของ level 110+ — แบ่งหน้าด้วย `ROW_NUMBER()`, JSON parse ใน PHP
- ⚠️ **DNS ในบริษัท: `lyindustries.com` (ไม่มี www) ชี้ไป Domain Controller** — โดเมน Active Directory ของบริษัทชื่อ `LYINDUSTRIES.COM` (DC1 = 192.168.0.14) เครื่องในบริษัทจึง resolve `lyindustries.com` เป็น 192.168.0.14 แทน web host (118.27.156.238) → โหลดไม่ได้ (timeout) ส่วนคนนอกบริษัทโหลดได้ปกติ · **ทุก URL ที่ browser ต้องโหลด (img, video, css, js, link) ให้ใช้ `https://www.lyindustries.com/…` เสมอ ห้ามใช้แบบไม่มี www** (แก้ไม่ได้ฝั่ง DNS เพราะเป็นข้อบังคับของ AD)
- ตารางผู้ใช้ `sysmnuser` มีทั้งใน `LYI` และ `test_LYI` · ตารางใหม่ของเว็บให้ขึ้นต้น **`lyiweb_`** (ผู้ใช้เลือก 6 ต.ค. 2026 · เช็กแล้วยังไม่มี object ชื่อ `lyiweb%` ในทั้งสอง DB)
### Production ตอนนี้ = เว็บเวอร์ชันเดิม (สำรวจ 5 ต.ค. 2026)

https://www.lyindustries.com/ ยังเป็น**เว็บเก่า** — ห้ามแตะ/deploy ทับจนกว่าจะมีแผน launch ที่ผู้ใช้อนุมัติ

**สำเนาเว็บจริงล่าสุด (ผู้ใช้ดึงผ่าน FTP 6 ต.ค. 2026):** `D:\@ReferanceData\@ LYI Company\lyindustries.com` (อยู่ใน VS Code workspace `lyindustries-new-dev.code-workspace` ในโฟลเดอร์เดียวกัน — path ใน workspace เป็นแบบ relative ให้ดูจากไฟล์ .code-workspace ก่อนค้นหา) — มี `img/` รูปความละเอียดสูง (~2100×1230 ส่วนใหญ่เป็นภาพสินค้า 2 ช่องคั่นเส้นขาว + `11f.JPG` โรงย้อม, `beemmc.png` เครื่องถัก), `media/`, `lyinspirationhub/` · ใช้เป็นแหล่งรูปได้ (อ่านอย่างเดียว)

**Source ของเว็บเก่า (เก่ากว่า FTP):** `\\192.168.0.73\htdocs\lyindustries` (= `P:\lyindustries`) — ไม่ใช่ git repo, PHP + Tailwind, ไม่ใช้ database, ไฟล์ลงวันที่ 1 เม.ย. 2026
- ⚠️ **ไม่ตรงกับเว็บจริง 100%** — production ใหม่กว่า (มีแก้ responsive/มือถือที่ไม่อยู่ใน .73) → ตอน launch ต้องสำรองจาก server z.com โดยตรง ห้ามถือว่า .73 เป็น backup
- ใช้ .73 เป็นแหล่งอ้างอิง/ดึงไฟล์ภาพ-วิดีโอได้ (อ่านอย่างเดียว ห้ามแก้ — เป็นของระบบเดิม)
- ไฟล์: `index.php`, `about.php`, `contact.php`, `innovation.php`, `shop.php`, `products_detail.php`, `braiding.php`, `crochet.php`, `finishing.php`, `needle_loom.php`, `raschel.php` + `header.php`/`footer.php` (include) · โฟลเดอร์ `img/` (122MB), `media/` (114MB), `cert/`, `partners/`

สิ่งที่ต้องรู้ก่อน launch:
- **ชื่อไฟล์ชนกัน:** เว็บเก่ามี `index.php`, `about.php`, `contact.php` ชื่อเดียวกับเว็บใหม่ → ต้องสำรองเว็บเก่าทั้งหมดก่อนขึ้น
- **URL เก่าที่ต้อง redirect 301** (ไม่งั้นเสีย SEO / ลิงก์เดิมพัง) — ทุกหน้ายังเปิดได้ (200) บน production: `innovation.php`, `shop.php`, `products_detail.php`, `braiding.php`, `crochet.php`, `finishing.php`, `needle_loom.php`, `raschel.php` → หน้าเทียบเท่าในเว็บใหม่ (ยังไม่ได้กำหนด)
- **ต้องเก็บไว้ ห้ามลบ:** `/lyinspirationhub/` (อยู่บน production แต่**ไม่อยู่**ใน source .73) (Inspiration Hub — ปุ่ม "สินค้าเพิ่มเติม" ของเว็บใหม่ลิงก์ไปที่นี่), `/img/` และ `/media/` (หน้าแรกเว็บใหม่ยังดึงภาพ process + วิดีโอ hero จาก `lyindustries.com/img/…` และ `/media/header/…` — ถ้าย้ายมาเก็บใน `assets/` ก่อน launch จะตัดการพึ่งพานี้ได้)
- เว็บเก่าไม่มี `robots.txt`, `sitemap.xml` และ `<title>` หน้าแรกว่าง
- PHP 8.2 / LiteSpeed ที่วัดได้คือ server ของเว็บเก่า — เว็บใหม่จะรันบน server เดียวกัน

- Production (z.com) ต้องมี extension `sqlsrv`/`pdo_sqlsrv` และออกพอร์ต SQL Server ไป 183.89.245.21 ได้ — ยังไม่ได้ยืนยันกับ hosting

### Deploy & versioning

**ทุก deploy ต้องมี version และบันทึกใน [DEPLOY_LOG.md](DEPLOY_LOG.md)** — scheme `vMAJOR.MINOR.PATCH` (เกณฑ์การขึ้นเลขอยู่หัวไฟล์ DEPLOY_LOG) และ 1 deploy = 1 git tag

ขั้นตอน (copy ไฟล์ ไม่มี pipeline):
1. **deploy เฉพาะสิ่งที่ commit แล้ว** — `git status` ต้องสะอาด; commit ที่ deploy = `HEAD`
2. เลือก version ถัดไปจาก entry ล่าสุดใน DEPLOY_LOG.md
3. เทียบไฟล์ local ↔ server ด้วย `cmp` เพื่อรู้ว่ามีไฟล์ไหนเพิ่ม/เปลี่ยน/ต้องลบ
4. สำรองไฟล์บน server ที่จะถูกทับ/ลบ แล้ว copy: `*.php`, `includes/`, `assets/`, `admin/`, `web.config`, `.htaccess` → `N:\lyindustries-dev\` (ห้าม copy `.git`, `*.md`, `docs/`, `tools/`, `cache/` และ `connectgrp.php` — ไฟล์ connectgrp ของแต่ละ server วางเองครั้งเดียว ห้ามทับ) · โฟลเดอร์ `cache/` บน server ต้องให้ PHP เขียนได้ (IIS: สิทธิ์ Modify ให้ IUSR/app pool) — ถ้าเขียนไม่ได้เว็บยังทำงานแต่จะ query DB ทุกครั้ง
5. ถ้าลบ/เปลี่ยนชื่อไฟล์ในโปรเจกต์ ต้องลบไฟล์เก่าบน server ด้วย (ใช้ path แบบ literal ไม่ใช้ตัวแปรใน `rm`)
6. ตรวจ: `curl` ทุกหน้าได้ 200, `*.html` ได้ 301, `includes/…` ได้ 404, `.woff2` ได้ `font/woff2` และ diff HTML จาก server กับ `php <page>.php` ในเครื่องต้องตรงกัน
7. `git tag -a vX.Y.Z -m "Deploy vX.Y.Z to dev"` ที่ commit ที่ deploy
8. เพิ่ม entry บนสุดของ DEPLOY_LOG.md: version, วันเวลา, environment, commit + tag, target, changes, files, ผล verify, note — แล้วอัปเดต PROJECT_STATUS.md และ commit (`Deploy log: vX.Y.Z`)
9. ถ้า verify ไม่ผ่าน: rollback ไฟล์ที่สำรองไว้ และบันทึก entry เป็น ❌ พร้อมสาเหตุ (ไม่สร้าง tag)

## Important rules (agent ต้องปฏิบัติ)

1. **ห้ามเปลี่ยนดีไซน์หรือเนื้อหา** ที่ล็อกใน DESIGN-LOCK.md โดยไม่ได้รับอนุมัติ (ผู้อนุมัติ: Pack) — งาน technical ที่ไม่เปลี่ยนหน้าตา/ข้อความทำได้
2. **ห้ามใส่ชื่อแบรนด์ลูกค้า** บนเว็บ (นโยบาย) — ใช้คำกลางเช่น "แบรนด์กีฬาระดับโลก"
3. `web.config` บน dev ต้องมี handler PHP 8.4 (`PHP84_lyi` → `C:\PHP84\php-cgi.exe`) เสมอ — ถ้าไม่มี IIS จะใช้ PHP 7.1 แล้วเว็บพัง; **ห้ามเอา web.config ของโปรเจกต์อื่น (เช่น lyi-dashboard ที่ rewrite ไป `public/`) มาใช้** — เคยทำให้ทั้งเว็บ 404
4. กฎ redirect/บล็อกโฟลเดอร์ต้องตรงกันทั้ง `.htaccess` (Apache) และ `web.config` (IIS) — แก้ที่หนึ่งต้องแก้อีกที่
5. ห้ามแก้ไฟล์ของโปรเจกต์อื่นบน `N:\` (wwwroot ใช้ร่วมหลายระบบ)
6. คง schema.org JSON-LD ของทุกหน้าไว้ (ContactPage/LocalBusiness ฯลฯ) — เป็นหัวใจของ AEO
7. **Git:** ทำงานเสร็จแต่ละชิ้น → commit ให้ทุกครั้ง บน `main` (ผู้ใช้ push เองผ่าน SourceTree — **ห้าม push**) ท้าย commit message ใส่ `Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>`
8. **ทุกครั้งที่แก้ source code** ให้ตรวจว่าต้องอัปเดต `CLAUDE.md` (โครงสร้าง/กฎ/วิธีทำงาน) และ `PROJECT_STATUS.md` (งานเสร็จ/ค้าง/บั๊ก/decision) ด้วยหรือไม่ แล้วรวมไว้ใน commit เดียวกัน
9. ก่อน deploy/ลบ/เขียนทับไฟล์บน server ให้ดูไฟล์ปลายทางก่อน และสำรองไฟล์ที่จะถูกทับ
10. **ทุกครั้งที่ deploy ต้องมี version + git tag + entry ใน DEPLOY_LOG.md** (ขั้นตอนใน [Deploy & versioning](#deploy--versioning)) — tag ไม่ต้อง push ผู้ใช้จัดการเองใน SourceTree
11. **ห้าม commit ความลับ** (รหัสผ่าน DB, API key) — `connectgrp.php` อยู่ใน `.gitignore`; ถ้าต้องเพิ่มไฟล์ config ที่มีความลับให้ใส่ `.gitignore` ก่อนสร้าง และ deploy ไฟล์พวกนี้ไป server แบบ manual (ค่าต่างกันตาม environment)
