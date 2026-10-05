# LY Website — FINAL (สำหรับ dev พัฒนาต่อ)

โฟลเดอร์นี้คือต้นแบบ final ของเว็บ L.Y. Industries 
ห้ามเปลี่ยนดีไซน์/เนื้อหาโดยไม่ได้รับอนุมัติจาก Pack

## ไฟล์
| ไฟล์ | คืออะไร |
|---|---|
| `index.html` | หน้าแรก (เดิมชื่อ LY-Homepage-v8.html) — เป็นไฟล์ bundle ขนาด ~20MB |
| `about.html` | เกี่ยวกับเรา |
| `catalog.html` | แคตาล็อกสินค้า |
| `contact.html` | ติดต่อเรา / ขอใบเสนอราคา |
| `assets/` | ภาพประกอบ 13 ไฟล์ (app-*.jpg, prod-*.jpg) |
| `DESIGN-LOCK.md` | สเปกดีไซน์ที่ล็อกแล้ว: navigation, หลักเนื้อหา, โครงแต่ละหน้า — **ยึดไฟล์นี้เป็นหลัก** |
| `CONTENT-DRAFT.md` | สรุปร่างเนื้อหา 6 หน้า (ฉบับก่อนล็อก ใช้อ้างอิงเนื้อหา/AEO) |

## ลิงก์ระหว่างหน้า
- ทุกหน้าลิงก์กลับหน้าแรกด้วย `index.html` และ `index.html#process`
- TRIMRITE® → https://www.trimrite.com/ (เปิดแท็บใหม่)
- Inspiration Hub → https://www.lyindustries.com/lyinspiratonhub/ (สะกดตามที่ได้รับมา รอยืนยัน)
- LINE → https://line.me/R/ti/p/@lyindustries · อีเมลในหน้า: sales@lyindustries.com · โทร 02-517-0768

## สิ่งที่ dev ต้องทำ/ระวัง
1. **ภาพหาย:** `index.html` อ้าง `./assets/dye-yarn-machine.png` (กระบวนการผลิต ขั้น 03 ย้อมสี) แต่ยังไม่มีไฟล์นี้ — ต้องขอภาพจาก Pack
2. **ฟอร์ม** หน้า contact ยังเป็น `mailto:` — ต้องต่อระบบรับฟอร์มจริง
3. **YouTube video ID** หน้า About ยังเป็น placeholder
4. **เวลาทำการ** ยังไม่ยืนยัน (08:30–17:30 + ส. 08:30–12:00 หรือ 09:00–18:00)
5. ภาพขั้นตอนผลิตบางภาพใน `index.html` ดึงตรงจาก lyindustries.com/img/ — ควรย้ายมาเก็บใน `assets/`
6. ภาพจริงแต่ละหมวด/Facilities และรหัสสินค้าตัวอย่าง 6 รายการ ยังรอจาก Pack
7. `DESIGN-LOCK.md` กับ `CONTENT-DRAFT.md` ขัดกันเรื่องเมนู (ฉบับร่างมีหน้า /sample และ /process แยก) — ให้ยึดตาม DESIGN-LOCK
8. Schema: หน้า contact ใช้ ContactPage/LocalBusiness — คงไว้ตอนย้ายขึ้นระบบจริง (เป้าหมายโปรเจกต์คือ AEO สำหรับ Narrow Fabric)
