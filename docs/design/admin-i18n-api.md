# Design: หลังบ้าน admin + เว็บ 2 ภาษา + API

สถานะ: **ร่างรอผู้ใช้ตรวจ** · 6 ต.ค. 2026 · อนุมัติขอบเขตจาก Pack แล้ว (ปุ่มเปลี่ยนภาษา, เนื้อหาภาษาอังกฤษ, แก้เนื้อหาผ่านหลังบ้าน)

## 1. เป้าหมาย

1. แก้/เพิ่ม/ลบ ข้อความและรูปทุกส่วนของ 4 หน้าได้จากหลังบ้าน โดยไม่ต้องแก้โค้ด
2. เว็บ 2 ภาษา ไทย/อังกฤษ — default ไทย และเปลี่ยน default ได้จากหลังบ้าน
3. API (JSON) สำหรับอ่านเนื้อหา/ส่งฟอร์ม ใช้ชั้นข้อมูลเดียวกับหลังบ้าน
4. รองรับ SEO/AEO (meta 2 ภาษา, hreflang, sitemap, schema) — ข้อมูล SEO แก้ได้จากหลังบ้านเช่นกัน
5. หน้าตาเว็บ (ดีไซน์ที่ล็อก) ไม่เปลี่ยน ยกเว้นปุ่มเปลี่ยนภาษาที่อนุมัติแล้ว

**นอกขอบเขตรอบนี้:** workflow อนุมัติก่อนเผยแพร่ (draft → review → publish), ภาษาที่ 3, ร้านค้า/ตะกร้า

## 2. ข้อจำกัดที่เจอ (ต้องออกแบบรอบมัน)

| ข้อจำกัด | ผลต่อการออกแบบ |
|---|---|
| **SQL Server 2012, compatibility level 100** (ทั้ง `LYI` และ `test_LYI`) | ใช้ฟังก์ชัน JSON ของ SQL ไม่ได้, `OFFSET…FETCH` ไม่ได้, `STRING_AGG` ไม่ได้ → เก็บ JSON เป็น `NVARCHAR(MAX)` แล้ว parse ใน PHP, แบ่งหน้าด้วย `ROW_NUMBER()` |
| Production อยู่ z.com แต่ DB อยู่ในบริษัท (ผ่าน 183.89.245.21) | ถ้าทุก page view query DB ข้ามอินเทอร์เน็ต → เว็บช้า และ **ถ้าเน็ตบริษัทล่ม เว็บล่มทั้งเว็บ** → หน้าเว็บสาธารณะอ่านจาก **cache ไฟล์** บน server เว็บ (ข้อ 5) |
| PHP 8.2 บน production | โค้ดต้องไม่ใช้ฟีเจอร์ 8.3+ |
| `sysmnuser` เป็นตารางกลางของระบบ ERP | **อ่านอย่างเดียว** — เว็บไม่เขียนลง `sysmnuser` (ต่างจาก peakconnect ที่อัปเดต `logintime`) |
| รหัสผ่านใน `sysmnuser` เป็น plaintext (คอลัมน์ `pass` / `password`) | ตรวจแบบเดียวกับระบบอื่น: `hash_equals()` หรือ `password_verify()` — ไม่แก้ตาราง |
| DB แยก test/production | เนื้อหาที่แก้บน .70 (`test_LYI`) **ไม่ไป** production — คนแก้เนื้อหาจริงต้องใช้หลังบ้านบน production (มีเครื่องมือ export/import ไว้ย้ายระหว่าง env — ข้อ 9) |

## 3. โครงสร้างไฟล์ใหม่

```
/
├─ index.php, about.php, catalog.php, contact.php   หน้าเว็บ (เหมือนเดิม แต่ดึงข้อความจาก content layer)
├─ en/                       ไม่มีไฟล์จริง — rewrite /en/... → หน้าเดิม + lang=en
├─ admin/                    หลังบ้าน (login, dashboard, แก้เนื้อหา, สื่อ, ข้อความติดต่อ, ผู้ใช้/สิทธิ์, ตั้งค่า)
├─ api/v1/                   endpoint JSON
├─ includes/
│  ├─ bootstrap.php          (เดิม) + เลือกภาษา, โหลด content
│  ├─ lib/Db.php             wrapper ของ sqlsrv: query/exec แบบ parameter เท่านั้น, transaction
│  ├─ lib/Auth.php           login ด้วย sysmnuser, session, CSRF
│  ├─ lib/Perm.php           ตรวจสิทธิ์ (role → permission)
│  ├─ lib/Content.php        อ่านเนื้อหา (cache → DB → ค่า fallback ในโค้ด)
│  ├─ lib/I18n.php           ภาษาปัจจุบัน, URL ของแต่ละภาษา, hreflang
│  ├─ lib/Media.php          อัปโหลด/ย่อ/WebP
│  └─ schema/                field definition ของแต่ละ list (ใช้สร้างฟอร์มหลังบ้าน + validate)
├─ uploads/                  ไฟล์ที่อัปโหลด (ไม่อยู่ใน git, แต่ละ server มีของตัวเอง)
├─ cache/                    content cache JSON (ไม่อยู่ใน git, เว็บอ่านได้ ห้ามเปิดจาก URL)
└─ connectgrp.php            (เดิม) เลือก DB ตามโดเมน
```

`includes/`, `cache/`, `uploads/*.php` ต้องบล็อกทั้งใน `.htaccess` และ `web.config`

## 4. ฐานข้อมูล (ตารางใหม่ทั้งหมดขึ้นต้น `web_`)

สร้างใน `test_LYI` ก่อน → ทดสอบ → สร้างใน `LYI` ด้วยสคริปต์เดียวกัน (`docs/sql/001_web_schema.sql`)

### 4.1 เนื้อหา

**`web_settings`** — ค่าตั้งของเว็บ
| คอลัมน์ | ชนิด | หมายเหตุ |
|---|---|---|
| `setting_key` | NVARCHAR(100) PK | เช่น `default_lang`, `site_phone`, `site_email`, `line_url`, `hours_th` |
| `value` | NVARCHAR(MAX) | |
| `updated_at` / `updated_by` | DATETIME / INT | `updated_by` = `sysmnuser.id` |

**`web_pages`** — 1 แถวต่อหน้า × ข้อมูล SEO
| คอลัมน์ | ชนิด |
|---|---|
| `id` INT IDENTITY PK, `slug` NVARCHAR(50) UNIQUE (`home`, `about`, `catalog`, `contact`) |
| `title_th`, `title_en` NVARCHAR(200) · `meta_desc_th`, `meta_desc_en` NVARCHAR(400) |
| `og_image` NVARCHAR(300) · `is_published` BIT · `updated_at`, `updated_by` |

**`web_blocks`** — ข้อความ/รูป "ชิ้นเดี่ยว" ในแต่ละหน้า (หัวข้อ hero, ย่อหน้า AEO, ปุ่ม ฯลฯ)
| คอลัมน์ | ชนิด | หมายเหตุ |
|---|---|---|
| `id` INT IDENTITY PK | | |
| `page_slug` NVARCHAR(50), `block_key` NVARCHAR(100) | UNIQUE(page_slug, block_key) | เช่น `home` / `hero.title` |
| `block_type` NVARCHAR(20) | `text` · `richtext` · `image` · `link` | richtext อนุญาตแค่ `<br><strong><em><a>` (sanitize ตอนบันทึก) |
| `value_th`, `value_en` | NVARCHAR(MAX) | `value_en` ว่าง → แสดงภาษาไทยแทน (fallback) |
| `updated_at`, `updated_by` | | |

**`web_items`** — รายการที่วนซ้ำ (ไทล์ 8, process 6, สินค้า 6, gallery 6, FAQ 4, สี 5, marquee, หมวด catalog, Facilities …)
| คอลัมน์ | ชนิด | หมายเหตุ |
|---|---|---|
| `id` INT IDENTITY PK | | |
| `list_key` NVARCHAR(50) | | เช่น `home.steps`, `home.faq`, `catalog.samples` |
| `sort_order` INT, `is_active` BIT | | ลากเรียง/ซ่อนได้ |
| `data_th`, `data_en` | NVARCHAR(MAX) | JSON ของฟิลด์ที่แปลได้ (เช่น `{"title":…,"desc":…}`) — parse ใน PHP |
| `data_common` | NVARCHAR(MAX) | JSON ของฟิลด์ที่ไม่ขึ้นกับภาษา (รหัสสินค้า, hex สี, dx/dy) |
| `image_id` | INT NULL → `web_media.id` | |
| `updated_at`, `updated_by` | | |

ฟิลด์ของแต่ละ `list_key` กำหนดใน `includes/schema/*.php` (ชื่อ, ชนิด, แปลได้ไหม, จำเป็นไหม) — หลังบ้านสร้างฟอร์มจากไฟล์นี้ และ validate ก่อนบันทึก จึงเพิ่ม list ใหม่ได้โดยไม่แก้ตาราง

**`web_media`** — รูปที่อัปโหลด
| `id` PK · `file_path` NVARCHAR(300) (relative เช่น `uploads/2026/10/abc123.webp`) · `original_name` · `mime` · `width`, `height`, `bytes` · `alt_th`, `alt_en` · `uploaded_at`, `uploaded_by` |

### 4.2 ผู้ใช้และสิทธิ์ (ผูกกับ `sysmnuser` แบบอ่านอย่างเดียว)

**`web_roles`** — `id` PK · `role_key` NVARCHAR(50) UNIQUE · `name_th` · `is_system` BIT
**`web_role_permissions`** — `role_id` + `permission` NVARCHAR(50) (PK คู่)
**`web_user_roles`** — `user_id` (= `sysmnuser.id`) + `role_id` (PK คู่) · `granted_at`, `granted_by`

Permission ที่มี (กำหนดในโค้ด, ให้สิทธิ์ผ่าน role):
| permission | ทำอะไรได้ |
|---|---|
| `content.edit` | แก้ข้อความ/รายการ/SEO ทุกหน้า |
| `content.translate` | แก้เฉพาะช่องภาษาอังกฤษ (สำหรับคนแปล) |
| `media.upload` | อัปโหลด/ลบรูป |
| `contact.view` | ดูข้อความจากฟอร์มติดต่อ |
| `settings.edit` | ตั้งค่าเว็บ รวมถึงเปลี่ยนภาษา default |
| `users.manage` | ให้/ถอนสิทธิ์ผู้ใช้ |

Role เริ่มต้น: `admin` (ทุกสิทธิ์), `editor` (content.edit, media.upload), `translator` (content.translate), `sales` (contact.view)

**กติกา login**
- ทุกคนที่มีใน `sysmnuser` และ `locked <> 1` **login ได้** (ตามที่ผู้ใช้กำหนด) — แต่ถ้าไม่มี role เลยจะเห็นแค่หน้า "ยังไม่มีสิทธิ์ ติดต่อผู้ดูแล"
- **bootstrap admin คนแรก:** ผู้ใช้ `sysmnuser.level >= 5` ได้สิทธิ์ `admin` อัตโนมัติ (ใช้เกณฑ์เดียวกับ price-quote) — ปรับตัวเลขได้ใน `web_settings.admin_min_level`
- กันเดารหัส: ผิด 5 ครั้งใน 15 นาที → ล็อก username+IP 15 นาที (ตาราง **`web_login_attempts`**: `username`, `ip`, `attempted_at`, `success`)
- session: `session_regenerate_id()` หลัง login, cookie `HttpOnly` + `Secure` + `SameSite=Lax`, หมดอายุเมื่อไม่ใช้งาน 2 ชม.
- ทุกฟอร์ม/คำขอที่เขียนข้อมูลต้องมี CSRF token

**`web_audit_log`** — `id` · `user_id` · `action` (`update`/`create`/`delete`/`login`/`grant`) · `entity` · `entity_id` · `before_json`, `after_json` NVARCHAR(MAX) · `ip` · `created_at` — ดูย้อนหลังและกู้ค่าเดิมได้

### 4.3 ฟอร์มติดต่อ

**`web_contact_requests`** — `id` · `name` · `company` · `email` · `phone` · `product` · `message` · `lang` · `page` · `ip` · `user_agent` · `status` (`new`/`read`/`done`) · `created_at` · `handled_by`
- กัน spam: honeypot field + จำกัด 5 ครั้ง/ชม./IP
- แจ้งเตือนทีมขาย: อีเมล (ต้องมี SMTP ของ z.com) และ/หรือ LINE — **ยังต้องเลือก**

## 5. การแสดงผลหน้าเว็บ + cache

```
request → I18n (ภาษาจาก URL) → Content::page('home', lang)
             ├─ มี cache/content-{lang}.json ที่สดอยู่ → ใช้เลย (ไม่แตะ DB)
             ├─ ไม่มี → query DB → เขียน cache → ใช้
             └─ DB ต่อไม่ได้ → ใช้ cache เก่า (ถ้ามี) → ถ้าไม่มี ใช้ค่า fallback ในโค้ด (ข้อมูลปัจจุบันใน home-data.php)
```
- หลังบ้านบันทึกเมื่อไหร่ → ลบ cache ทันที → page view ถัดไปสร้างใหม่
- ผล: หน้าเว็บสาธารณะแทบไม่ query DB, **เน็ตบริษัทล่มเว็บยังขึ้น** (แสดงเนื้อหาล่าสุดที่ cache ไว้)
- markup/inline style ของดีไซน์ที่ล็อก **ไม่เปลี่ยน** — แค่เปลี่ยนแหล่งข้อความจาก array ในโค้ดเป็น content layer; ตรวจด้วยการ diff HTML ก่อน/หลังต้องตรงกันทุกไบต์ (เหมือนตอนแปลงเป็น PHP)

## 6. สองภาษา

**URL**
| ภาษา | default = ไทย (ค่าเริ่มต้น) | ถ้าเปลี่ยน default = อังกฤษ |
|---|---|---|
| ไทย | `/about.php` | `/th/about.php` |
| อังกฤษ | `/en/about.php` | `/about.php` |

- rewrite ใน `.htaccess` + `web.config`: `^(en|th)/(.*)$` → `$2?lang=$1` (ไม่ต้องมีโฟลเดอร์จริง)
- ทุกหน้ามี `<html lang>`, `<link rel="alternate" hreflang="th|en|x-default">`, canonical ของภาษานั้น
- ปุ่มเปลี่ยนภาษาที่ nav (ดีไซน์ต้องทำให้กลืนกับ nav เดิม — จะส่ง screenshot ให้ดูก่อน)
- `value_en` ว่าง → แสดงไทยแทน และ**ไม่ใส่หน้านั้นใน sitemap ภาษาอังกฤษ** จนกว่าจะแปลครบ (กัน Google เห็นหน้า "อังกฤษ" ที่เป็นไทย)

**เปลี่ยนภาษา default จากหลังบ้าน — ทำได้ แต่มีเงื่อนไข**
- เปลี่ยนแล้ว URL ของทั้งสองภาษาสลับกัน (ตารางด้านบน) → ระบบต้อง redirect 301 URL เก่าให้อัตโนมัติ ซึ่งทำได้
- **ข้อเสียด้าน SEO:** ทุกครั้งที่สลับ Google ต้องเรียน URL ใหม่ทั้งเว็บ อันดับอาจตกชั่วคราว → ควรตั้งครั้งเดียวแล้วไม่เปลี่ยนบ่อย; หลังบ้านจะขึ้นคำเตือน + ให้ยืนยันก่อนสลับ และเปลี่ยนได้เฉพาะ `settings.edit`

## 7. API (`/api/v1/`, JSON)

| Method | Endpoint | สิทธิ์ | ใช้ทำอะไร |
|---|---|---|---|
| GET | `/api/v1/pages/{slug}?lang=th` | สาธารณะ | SEO + blocks ของหน้า |
| GET | `/api/v1/lists/{list_key}?lang=en` | สาธารณะ | รายการ (สินค้า, FAQ, …) เฉพาะ `is_active` |
| POST | `/api/v1/contact` | สาธารณะ + rate limit + honeypot | ส่งฟอร์มติดต่อ |
| POST | `/api/v1/auth/login` · `/logout` | — | login หลังบ้าน (session) |
| GET/PUT | `/api/v1/admin/blocks/{id}` | `content.edit` / `content.translate` | แก้ block |
| GET/POST/PUT/DELETE | `/api/v1/admin/items[/{id}]` | `content.edit` | CRUD รายการ |
| POST | `/api/v1/admin/items/reorder` | `content.edit` | เรียงลำดับ |
| POST/DELETE | `/api/v1/admin/media[/{id}]` | `media.upload` | อัปโหลด/ลบรูป |
| GET/PUT | `/api/v1/admin/contact[/{id}]` | `contact.view` | ดู/เปลี่ยนสถานะข้อความ |
| GET/PUT | `/api/v1/admin/settings` | `settings.edit` | ค่าตั้ง |
| GET/PUT | `/api/v1/admin/users/{id}/roles` | `users.manage` | ให้/ถอนสิทธิ์ |

- รูปแบบตอบกลับ: `{"ok": true, "data": …}` / `{"ok": false, "error": {"code": "…", "message": "…"}}` + HTTP status ที่ถูกต้อง
- หลังบ้านเองก็เรียก API ชุดนี้ (ฟอร์ม + fetch) → มีโค้ดเขียนข้อมูลชุดเดียว
- endpoint admin ใช้ session + CSRF header; ถ้าอนาคตมีระบบอื่น (เช่นแอป) เรียก จะเพิ่ม API token ทีหลังได้

## 8. รูปภาพ (Media)

- รับ JPG/PNG/WebP ≤ 10MB → ย่อด้านยาวสุด 2000px + สร้าง WebP คุณภาพ ~82 (ใช้ GD ของ PHP — **ต้องเช็กว่า z.com เปิด GD + WebP**)
- ตั้งชื่อไฟล์ใหม่แบบสุ่ม, ตรวจ MIME จากเนื้อไฟล์จริง, ห้าม `.php` ใน `uploads/` (บล็อกทั้ง `.htaccess`/`web.config`)
- ช่องที่ยังไม่มีรูปแสดง `placeholder.svg` "รอใส่รูป" (ทำแล้ว)
- โควตา z.com เหลือ ~8.4GB — พอแน่นอน (รูป WebP ~200–400KB/รูป)

## 9. ย้ายข้อมูลระหว่าง environment

- เครื่องมือ **Export/Import** ในหลังบ้าน (`settings.edit`): ส่งออก `web_pages/blocks/items/settings` เป็นไฟล์ JSON + zip รูป → นำเข้าอีก env
- ใช้ครั้งแรกตอน launch: เตรียมเนื้อหาบน .70 (`test_LYI`) → export → import เข้า production (`LYI`)

## 10. ลำดับงาน (แตกจาก roadmap)

| ขั้น | งาน | ตรวจยังไง |
|---|---|---|
| 1 | สคริปต์สร้างตาราง `web_*` + seed ข้อมูลปัจจุบัน (จาก home-data.php + ข้อความในหน้า) ลง `test_LYI` | นับแถว/เทียบค่ากับ array เดิม |
| 2 | `Db`, `Content` (+cache, fallback), `I18n` — หน้าเว็บอ่านจาก DB | **diff HTML ก่อน/หลัง ต้องตรงทุกไบต์** ทั้ง 4 หน้า, ทดสอบตัด DB แล้วเว็บยังขึ้น |
| 3 | `Auth`, `Perm`, หน้า login/dashboard, audit log | login ถูก/ผิด/ถูกล็อก/ไม่มีสิทธิ์ |
| 4 | API + หน้าแก้ blocks/items/SEO, Media | แก้แล้วหน้าเว็บเปลี่ยน, cache ถูกล้าง |
| 5 | ฟอร์มติดต่อ → DB + แจ้งเตือน | ส่งจริง, spam ถูกกัน |
| 6 | `/en/` + ปุ่มภาษา + hreflang + default lang | ทั้งสองภาษา, สลับ default แล้ว redirect ถูก |
| 7 | SEO/AEO: sitemap, robots, FAQPage, OG, meta | Ahrefs Site Audit |

แต่ละขั้น deploy ขึ้น .70 เป็น version ใหม่ตาม DEPLOY_LOG

## 11. คำถามที่ยังต้องตอบ

1. **API ใครจะเรียกใช้นอกจากหลังบ้านของเว็บนี้?** (แอปมือถือ, ระบบอื่นในบริษัท, Inspiration Hub?) — ถ้ามีระบบภายนอก ต้องทำ API token ตั้งแต่แรก
2. ฟอร์มติดต่อให้แจ้งทีมขายทางไหน: อีเมล (ต้องมีข้อมูล SMTP ของ z.com) / LINE / ทั้งสอง / ดูในหลังบ้านอย่างเดียว
3. เกณฑ์ `level >= 5` = admin อัตโนมัติ ใช้ได้ไหม หรือจะกำหนด admin คนแรกเอง (ระบุ username)
4. 183.89.245.21 คือ SQL Server ตัวเดียวกับ 192.168.0.22 (NAT) ใช่ไหม — ถ้าใช่ สคริปต์สร้างตารางรันที่ .22 ได้ทั้ง `LYI` และ `test_LYI`
5. z.com เปิด PHP extension `gd` (รองรับ WebP) ไหม — ใช้ย่อรูป
