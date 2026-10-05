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
