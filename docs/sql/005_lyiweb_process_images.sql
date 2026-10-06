/* =====================================================================
   005_lyiweb_process_images.sql — รูปขั้นตอนการผลิต 6 ขั้น → ไฟล์ในเว็บ assets/img/process/step-0N.jpg
   (เดิมดึงจาก www.lyindustries.com/img/… ซึ่งเป็นรูปแบนมีพื้นดำ / ขั้น 03 ไม่มีรูป)

   • รันใน test_LYI ก่อน แล้วค่อย LYI
   • แก้เฉพาะแถวที่ยังชี้รูปเดิม — ถ้าเปลี่ยนรูปในหลังบ้านไปแล้วจะไม่ทับ · รันซ้ำได้
   ===================================================================== */

SET NOCOUNT ON;
SET XACT_ABORT ON;
BEGIN TRANSACTION;

UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"https://www.lyindustries.com/img/bgvideo1.jpg"', N'"img":"assets/img/process/step-01.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"n":"01"%' AND data_common LIKE N'%"img":"https://www.lyindustries.com/img/bgvideo1.jpg"%';
UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"https://www.lyindustries.com/img/nl.jpg"', N'"img":"assets/img/process/step-02.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"n":"02"%' AND data_common LIKE N'%"img":"https://www.lyindustries.com/img/nl.jpg"%';
UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":""', N'"img":"assets/img/process/step-03.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"n":"03"%' AND data_common LIKE N'%"img":""%';
UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"https://www.lyindustries.com/img/BRAIDING.jpg"', N'"img":"assets/img/process/step-04.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"n":"04"%' AND data_common LIKE N'%"img":"https://www.lyindustries.com/img/BRAIDING.jpg"%';
UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"https://www.lyindustries.com/img/FINISHING.jpg"', N'"img":"assets/img/process/step-05.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"n":"05"%' AND data_common LIKE N'%"img":"https://www.lyindustries.com/img/FINISHING.jpg"%';
UPDATE dbo.lyiweb_items SET data_common = REPLACE(data_common, N'"img":"https://www.lyindustries.com/img/CROCHET.jpg"', N'"img":"assets/img/process/step-06.jpg"'), updated_at = GETDATE()
 WHERE list_key = N'home.steps' AND data_common LIKE N'%"n":"06"%' AND data_common LIKE N'%"img":"https://www.lyindustries.com/img/CROCHET.jpg"%';

COMMIT TRANSACTION;

/* ตรวจผล */
SELECT sort_order, data_common FROM dbo.lyiweb_items WHERE list_key = N'home.steps' ORDER BY sort_order;
