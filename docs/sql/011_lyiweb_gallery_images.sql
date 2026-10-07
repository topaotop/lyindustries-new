/* =====================================================================
   011_lyiweb_gallery_images.sql — รูปตัวอย่างสินค้าหน้าแรก (home.gallery) 3 รหัส จาก Inspiration Hub (7 ต.ค. 2026)
     LY2086  → assets/img/gallery/ly2086.webp
     RLY1319 → assets/img/gallery/rly1319.webp
     RLY1452 → assets/img/gallery/rly1452.webp
   รูปชั่วคราว — ผู้ใช้เปลี่ยนเองได้ในหลังบ้าน "รายการ & รูปภาพ"
   (LY2101, RLY1377, LY2144 ไม่มีใน Hub → ยังเป็น Image pending)

   • รันใน test_LYI ก่อน แล้วค่อย LYI
   • แก้เฉพาะแถวที่ยังไม่มีรูป (img ว่าง และยังไม่ได้อัปโหลดรูปในหลังบ้าน) — ไม่ทับรูปที่ใส่เองแล้ว · รันซ้ำได้
   ===================================================================== */

SET NOCOUNT ON;
SET XACT_ABORT ON;
BEGIN TRANSACTION;

UPDATE dbo.lyiweb_items
   SET data_common = REPLACE(data_common, N'"img":""', N'"img":"assets/img/gallery/ly2086.webp"'), updated_at = GETDATE()
 WHERE list_key = N'home.gallery' AND image_id IS NULL
   AND data_common LIKE N'%"code":"LY2086"%' AND data_common LIKE N'%"img":""%';

UPDATE dbo.lyiweb_items
   SET data_common = REPLACE(data_common, N'"img":""', N'"img":"assets/img/gallery/rly1319.webp"'), updated_at = GETDATE()
 WHERE list_key = N'home.gallery' AND image_id IS NULL
   AND data_common LIKE N'%"code":"RLY1319"%' AND data_common LIKE N'%"img":""%';

UPDATE dbo.lyiweb_items
   SET data_common = REPLACE(data_common, N'"img":""', N'"img":"assets/img/gallery/rly1452.webp"'), updated_at = GETDATE()
 WHERE list_key = N'home.gallery' AND image_id IS NULL
   AND data_common LIKE N'%"code":"RLY1452"%' AND data_common LIKE N'%"img":""%';

COMMIT TRANSACTION;

/* ตรวจผล — 3 แถวแรกควรมี path รูป */
SELECT sort_order, image_id, data_common FROM dbo.lyiweb_items
 WHERE list_key = N'home.gallery' ORDER BY sort_order;
