# LY Website — Design Lock (23 ก.ย. 2026)

สถานะ: **LOCKED** — ใช้เป็นต้นแบบสำหรับขึ้นเว็บจริง/Framer ห้ามเปลี่ยนโดยไม่ได้รับอนุมัติจาก Pack

ไฟล์ต้นแบบ (เครื่อง Pack): `CLAUDE FILE/LY/AEO Project/FINAL-LOCKED-2026-09-23/`
- index.html (หน้าแรก, เดิม LY-Homepage-v8.html) · about.html · catalog.html · contact.html

## Navigation
- Top bar: หน้าแรก · กระบวนการผลิต (#process) · แคตาล็อกสินค้า (catalog) · TRIMRITE® (trimrite.com, แท็บใหม่) · เกี่ยวกับเรา · ติดต่อเรา (contact) + ปุ่มส้ม "ขอใบเสนอราคา"
- Side menu (☰): หน้าแรก · แคตาล็อกสินค้า · TRIMRITE® ↗ · เกี่ยวกับเรา · ติดต่อเรา / ขอใบเสนอราคา
- Footer sitemap: เหมือน side menu + คำถามพบบ่อย

## หลักเนื้อหา
- ไม่ระบุชื่อแบรนด์ลูกค้าบนเว็บ (TRUSTED BY แสดงเป็นกลุ่มสินค้าแทน, RSL = "ของแบรนด์กีฬาระดับโลก")
- ที่อยู่: 124 ซอยรามอินทรา 109 ถนนพระยาสุเรนทร์ แขวงบางชัน เขตคลองสามวา กรุงเทพฯ 10510 · โทร 02-517-0768 ต่อ 120, 121 · LINE @lyindustries
- Inspiration Hub: https://www.lyindustries.com/lyinspirationhub/
- Tagline About: "Small parts bring great impact."

## หน้าแรก
- 02 Why it matters: พื้นดำ, คงเนื้อหาเดิม (eyebrow EN + pill สั้น)
- 04 Products: หัวข้อ "Narrow Fabric & Trims ครบทุกประเภท"
- 07 Inspiration Hub: หัวข้อ "ตัวอย่างสินค้าของเรา", การ์ดสี่เหลี่ยมเล็ก 5 ต่อแถว
- GET IN TOUCH: "เริ่มงาน Trims กับเรา" / "...ทีมงานฝ่ายเทคนิคและฝ่าย Support..."
- ลบ "CUSTOM TRIMS DEVELOPMENT"

## About (สลับขาว-ดำ)
- Hero ดำ → Video ขาว → Stats+Our Story ดำ → Facilities ขาว → CTA ดำ
- Stats: 1978 · 45+ · OEKO-TEX® · Color Lab (hover glow ส้ม)
- Our Story: "LYI สู่ผู้ผลิต Narrow Fabric & Trims ครบวงจร"; ย่อหน้า Weaving, Knitting, Braiding แบบครบวงจร
- Facilities 6 การ์ด (ภาพ+กล่องซ้อน): ทีมพัฒนาสินค้า · ย้อมเส้นด้าย/ชิ้นงาน · Lab คุณภาพ · งานโครงสร้าง ทอ ถัก เชือก · งาน Finish หลากหลายแบบ · QC ทุกล็อตการผลิต
- ลบ section "Why brands trust us"

## Catalog (ธีมสว่าง)
- หมวดสินค้าหลัก: การ์ดแบบหน้าแรก (ภาพ + PROD-0x + ชื่อ) ไม่มีคำอธิบาย, ลิงก์ "สินค้าเพิ่มเติม →" ไป Inspiration Hub; ไม่มี filter tab
- ตัวอย่างสินค้าของเรา: การ์ดสี่เหลี่ยมแบบหน้าแรก
- Block Inspiration Hub ท้ายหน้า

## Contact
- หัว "ติดต่อเรา" → ฟอร์มขอใบเสนอราคา + กล่องข้อมูลติดต่อ → "ที่อยู่ของเรา" + Google Maps → footer
- Schema ContactPage/LocalBusiness

## ค้างยืนยัน
- เวลาทำการ (08:30–17:30 + ส. 08:30–12:00 vs 09:00–18:00)
- YouTube video ID หน้า About (ตอนนี้เป็น placeholder)
- ภาพจริงแต่ละหมวด/Facilities, รหัสสินค้าตัวอย่าง 6 รายการ
- ฟอร์มยังเป็น mailto — ต้องต่อระบบรับฟอร์มตอนขึ้นจริง

## อัปเดต 5 ต.ค. 2026 — ยืนยันโฟลเดอร์ FINAL-LOCKED เป็นตัว final ปัจจุบัน
- index.html ตรงกับ LY-Homepage-v8.html ล่าสุด (แก้ 2 ต.ค.) ทุกไบต์
- about.html / contact.html ตรงกับฉบับแก้ 2 ต.ค., catalog.html ตรงกับฉบับ 23 ก.ย. — ต่างจากไฟล์ทำงานเฉพาะลิงก์หน้าแรก (index.html แทน LY-Homepage-v8.html)
- assets 13 ไฟล์ตรงกับโฟลเดอร์ทำงาน
- ค้าง: ภาพ `assets/dye-yarn-machine.png` (หน้าแรก กระบวนการผลิตขั้น 03 ย้อมสี) ยังไม่มีไฟล์ในเครื่อง — ภาพขั้นนี้จะไม่ขึ้น
