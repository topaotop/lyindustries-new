/* =====================================================================
   006_lyiweb_process_images_original.sql — คืนรูปขั้นตอนผลิตเป็นชุดเดิมของดีไซน์ (Pack)
   ใช้ไฟล์จริงจาก source เว็บ (FTP) ที่เก็บไว้ใน assets/img/process/ แทนการลิงก์ไป www.lyindustries.com
   ขั้น 03 ยังไม่มีรูป (dye-yarn-machine.png ไม่มีในทุก source) → แสดง "Image pending"

   • รันใน test_LYI ก่อน แล้วค่อย LYI · แก้เฉพาะแถวที่ยังชี้รูปจาก 005 · รันซ้ำได้
   ===================================================================== */

SET NOCOUNT ON;
SET XACT_ABORT ON;
BEGIN TRANSACTION;

UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"assets/img/process/step-01.jpg"', N'"img":"assets/img/process/bgvideo1.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"img":"assets/img/process/step-01.jpg"%';
UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"assets/img/process/step-02.jpg"', N'"img":"assets/img/process/nl.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"img":"assets/img/process/step-02.jpg"%';
UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"assets/img/process/step-03.jpg"', N'"img":""'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"img":"assets/img/process/step-03.jpg"%';
UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"assets/img/process/step-04.jpg"', N'"img":"assets/img/process/BRAIDING.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"img":"assets/img/process/step-04.jpg"%';
UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"assets/img/process/step-05.jpg"', N'"img":"assets/img/process/FINISHING.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"img":"assets/img/process/step-05.jpg"%';
UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"assets/img/process/step-06.jpg"', N'"img":"assets/img/process/CROCHET.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"img":"assets/img/process/step-06.jpg"%';

COMMIT TRANSACTION;

/* ตรวจผล */
SELECT sort_order, data_common FROM dbo.lyiweb_items WHERE list_key = N'home.steps' ORDER BY sort_order;
