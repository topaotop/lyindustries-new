/* =====================================================================
   007_lyiweb_image_folders.sql — ย้ายรูปเข้าโฟลเดอร์ตามหมวด (7 ต.ค. 2026)
     assets/app-*.jpg  → assets/img/applications/app-*.jpg   (home.tiles)
     assets/prod-*.jpg → assets/img/products/prod-*.jpg      (home.products)

   • รันใน test_LYI ก่อน แล้วค่อย LYI · แก้เฉพาะแถวที่ยังใช้ path เดิม · รันซ้ำได้
   ===================================================================== */

SET NOCOUNT ON;
SET XACT_ABORT ON;
BEGIN TRANSACTION;

UPDATE dbo.lyiweb_items
   SET data_common = REPLACE(data_common, N'"img":"assets/app-', N'"img":"assets/img/applications/app-'), updated_at = GETDATE()
 WHERE list_key = N'home.tiles' AND data_common LIKE N'%"img":"assets/app-%';

UPDATE dbo.lyiweb_items
   SET data_common = REPLACE(data_common, N'"img":"assets/prod-', N'"img":"assets/img/products/prod-'), updated_at = GETDATE()
 WHERE list_key = N'home.products' AND data_common LIKE N'%"img":"assets/prod-%';

COMMIT TRANSACTION;

/* ตรวจผล — ทุกแถวควรขึ้นต้นด้วย assets/img/ (หรือว่าง) */
SELECT list_key, sort_order, data_common FROM dbo.lyiweb_items
 WHERE list_key IN (N'home.tiles', N'home.products') ORDER BY list_key, sort_order;
