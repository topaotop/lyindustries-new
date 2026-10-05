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
| Server-side | PHP 8.4 ล้วน — ไม่มี framework, ไม่มี Composer, ไม่มี database |
| Front-end | HTML + CSS + vanilla JS (ไม่มี build step, ไม่มี npm) |
| ฟอนต์ | Anuphan (body), Kanit (heading), JetBrains Mono (mono) — หน้าแรก self-host ใน `assets/fonts/`; หน้าย่อยโหลดจาก Google Fonts |
| Web server (local) | XAMPP Apache + PHP 8.4 (mod_fcgid) → ใช้ `.htaccess` |
| Web server (dev) | IIS 8.5 บน 192.168.0.70 → ใช้ `web.config` |
| Schema | JSON-LD ในแต่ละหน้าย่อย (AboutPage/Organization, CollectionPage/ItemList/Product, ContactPage/ContactPoint/OpeningHours) |

## Architecture

เว็บ PHP แบบ page-per-file ไม่มี routing ทุกหน้า `require includes/bootstrap.php` ก่อน

```
request → index.php / about.php / catalog.php / contact.php
            └─ includes/bootstrap.php   ค่าคงที่ของเว็บ (โทร, อีเมล, LINE, URL ภายนอก), site_nav(), e(), external_attrs()
            └─ includes/home-data.php   (เฉพาะ index.php) array เนื้อหารายการทั้งหมดของหน้าแรก
            └─ includes/site-header.php (about/catalog/contact) ตั้ง $activeNav, $quoteHref ก่อน require
            └─ includes/site-footer.php (catalog/contact) footer 4 คอลัมน์ — about ใช้ footer สั้นของตัวเอง
```

**หน้าแรก (`index.php`)**
- markup มาจาก design export → ใช้ inline `style="..."` เกือบทั้งหมด (ตั้งใจเก็บไว้ให้ตรงดีไซน์ที่ล็อก)
- รายการที่วนซ้ำ (เมนูมือถือ, marquee, ไทล์ 8 ช่อง, process 6 ขั้น, สินค้า 6, สี Pantone 5, gallery 6, FAQ 4) render ด้วย `foreach` จาก `includes/home-data.php`
- hover effect = class `.hv-1` … `.hv-16` ใน `assets/css/home.css` (แปลงจาก attribute `style-hover` เดิม ใช้ `!important` เพราะต้องชนะ inline style)
- state ฝั่ง client (เมนูเปิด, FAQ ที่เปิด, สีที่เลือก) ใช้ class/data attribute: `html.menu-open`, `.faq-item.is-open`, `[data-swatch]`, `[data-menu-toggle]`, `[data-faq-toggle]`
- `assets/js/home.js` = interaction ทั้งหมด + scroll engine (requestAnimationFrame) ที่ขยับ `#lyProgress`, `#heroContent`, `[data-tile]`, `[data-step-*]` — จำนวนขั้น process อ่านจากจำนวน `[data-step-text]` ใน DOM
- section ธีมสว่างใช้ `data-theme="light"` + override CSS variables inline บน `<section>`

**หน้าย่อย (`about.php`, `catalog.php`, `contact.php`)**
- แต่ละหน้ามี `<style>` ของตัวเองใน `<head>` (CSS ซ้ำกันบางส่วน เช่น `.nav`, `.brand .mark`) — แก้ส่วนที่ใช้ร่วมต้องแก้ทั้ง 3 หน้า
- ฟอร์มขอใบเสนอราคาใน `contact.php` ยังเป็น JS เปิด `mailto:` (ยังไม่มี backend)

## Folder structure

```
/
├─ index.php, about.php, catalog.php, contact.php   หน้าเว็บ
├─ includes/            PHP partial/ข้อมูล (ห้ามเปิดตรงจากเว็บ — บล็อกทั้งใน .htaccess และ web.config)
├─ assets/
│  ├─ app-*.jpg, prod-*.jpg   ภาพประกอบเดิม 13 ไฟล์
│  ├─ img/                    โลโก้ (logo-lyi.svg), apple-touch-icon.png, ภาพหน้าแรกที่แตกจาก bundle (PNG ใหญ่)
│  ├─ css/home.css, fonts.css สไตล์หน้าแรก / @font-face
│  ├─ fonts/                  woff2 ที่ self-host
│  └─ js/home.js              JS หน้าแรก
├─ .htaccess            Apache: DirectoryIndex, 301 *.html → *.php, บล็อก includes/
├─ web.config           IIS: handler PHP 8.4, defaultDocument, MIME .woff2, hiddenSegments includes, 301 *.html → *.php
├─ DESIGN-LOCK.md       สเปกดีไซน์ที่ล็อก — แหล่งอ้างอิงหลัก
├─ CONTENT-DRAFT.md     ร่างเนื้อหาก่อนล็อก (อ้างอิงเท่านั้น ขัดกับ DESIGN-LOCK ให้ยึด DESIGN-LOCK)
├─ README.md            ข้อมูลส่งมอบสำหรับ dev
├─ CLAUDE.md            ไฟล์นี้
└─ PROJECT_STATUS.md    สถานะงาน
```

## Coding conventions

- ทุกไฟล์ PHP ขึ้นต้น `declare(strict_types=1);` (ยกเว้น partial ที่เป็น template ล้วน)
- **escape ทุกค่าที่ echo ด้วย `e()`** (`htmlspecialchars` ENT_QUOTES|ENT_HTML5 UTF-8) ใช้ `<?= e($x) ?>` ใน template
- template ใช้ alternative syntax: `<?php foreach (...): ?> … <?php endforeach; ?>`, `<?php if (...): ?> … <?php endif; ?>`
- array ข้อมูลใช้ key ภาษาอังกฤษสั้นๆ ตามของเดิม (`th`, `en`, `img`, `code`, …) — ค่า `img` ว่าง `''` = ไม่มีรูป (template เช็ก `!== ''`)
- ลิงก์ออกนอกเว็บใช้ `'external' => true` + `external_attrs()` → `target="_blank" rel="noopener"`
- ค่าที่ใช้หลายที่ (โทร, อีเมล, URL) ให้ใช้/เพิ่มเป็นค่าคงที่ `SITE_*` ใน `includes/bootstrap.php` ไม่ hard-code ซ้ำ
- ลิงก์ภายในใช้ path แบบ relative (`about.php`, `index.php#process`) — เว็บต้องทำงานได้ใต้ sub-folder (`/lyindustries-dev/`, `/lyindustries-new-dev/`)
- JS: vanilla ES2017+, IIFE + `'use strict'`, เลือก element ด้วย `data-*` attribute; ไม่เพิ่ม library
- CSS หน้าแรก: เก็บสไตล์แบบ inline ตามเดิม; hover/state ใหม่ให้เพิ่มเป็น class ใน `home.css`
- คอมเมนต์ในโค้ดเป็นภาษาอังกฤษ สั้น; เอกสาร (.md) และคอมเมนต์ใน config ภาษาไทยได้
- Git ตั้ง `core.autocrlf` (warning LF→CRLF เป็นเรื่องปกติ)

## Development environment

- โฟลเดอร์งาน: `C:\xampp\htdocs\lyindustries-new-dev`
- เปิดดู: http://localhost/lyindustries-new-dev/ (Apache ของ XAMPP รัน **PHP 8.4.12** ผ่าน fcgid)
- PHP CLI: `php` ใน PATH = 8.4 (Laragon) — **อย่าใช้** `C:\xampp\php\php.exe` (เป็น 8.2)
- ไม่มี automated test — วิธีตรวจหลังแก้:
  1. `php -l <file>` ทุกไฟล์ที่แก้
  2. render `php index.php > out.html` แล้วดูว่าไม่มี warning/notice
  3. ถ้าเป็นการ refactor ที่ไม่ควรเปลี่ยนผลลัพธ์ ให้ diff HTML ก่อน/หลัง
  4. ตรวจหน้าตาด้วย headless Chrome (`chrome.exe --headless=new --screenshot=...`) — ข้อจำกัด: ความกว้างขั้นต่ำ 500px และ rAF/scroll animation ทดสอบใน headless ได้ไม่ครบ ต้องให้คนเช็กในเบราว์เซอร์จริง

## Dev server (public test) / Production

| | Dev (ทดสอบ) | Production |
|---|---|---|
| URL | http://192.168.0.70/lyindustries-dev/ (LAN; URL สาธารณะยังไม่ทราบ) | ยังไม่ได้ขึ้น — เว็บจริงปัจจุบัน www.lyindustries.com เป็นระบบเดิม |
| Path | `N:\lyindustries-dev` = `\\192.168.0.70\wwwroot\lyindustries-dev` | — |
| Server | IIS 8.5 + URL Rewrite, PHP default ของ IIS = **7.1** | — |

**วิธี deploy ไป dev** (copy ไฟล์ ไม่มี pipeline)
1. copy: `*.php`, `includes/`, `assets/`, `web.config`, `.htaccess`, `*.md` → `N:\lyindustries-dev\` (ห้าม copy `.git`)
2. ถ้าลบ/เปลี่ยนชื่อไฟล์ในโปรเจกต์ ต้องลบไฟล์เก่าบน server ด้วย (ใช้ path แบบ literal ไม่ใช้ตัวแปรใน `rm`)
3. ตรวจ: `curl` ทุกหน้าได้ 200, `*.html` ได้ 301, `includes/…` ได้ 404, `.woff2` ได้ `font/woff2` และ diff HTML จาก server กับ `php <page>.php` ในเครื่องต้องตรงกัน
4. เทียบไฟล์ local ↔ server ด้วย `cmp` เพื่อดูว่ามีอะไรต้อง deploy

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
