/* ============================================================================
   009 — login หลังบ้านได้เฉพาะคนที่ถูกเลือก (มี role ใน lyiweb_user_roles)
   ยกเลิกสิทธิ์อัตโนมัติจาก sysmnuser.level (admin_min_level = 0)
   SQL Server 2012 (compatibility level 100) · รันซ้ำได้ปลอดภัย
   รันใน test_LYI ก่อน แล้วค่อย LYI

   กันล็อกตัวเองออก: ให้ role ผู้ดูแลระบบกับ itti.p ก่อน (เฉพาะเมื่อยังไม่มีใครมี role นี้)
   ============================================================================ */

INSERT dbo.lyiweb_user_roles (user_id, role_id, granted_by)
SELECT u.id, r.id, NULL
FROM dbo.sysmnuser u
JOIN dbo.lyiweb_roles r ON r.role_key = N'admin'
WHERE u.username = N'itti.p'
  AND NOT EXISTS (SELECT 1 FROM dbo.lyiweb_user_roles ur WHERE ur.role_id = r.id);

UPDATE dbo.lyiweb_settings
SET value = N'0', updated_at = GETDATE()
WHERE setting_key = N'admin_min_level' AND value <> N'0';

/* ตรวจผล: ต้องมีผู้ดูแลอย่างน้อย 1 คน และ admin_min_level = 0 */
SELECT u.username, u.name, r.name_th
FROM dbo.lyiweb_user_roles ur
JOIN dbo.lyiweb_roles r ON r.id = ur.role_id
JOIN dbo.sysmnuser u ON u.id = ur.user_id;

SELECT setting_key, value FROM dbo.lyiweb_settings WHERE setting_key = N'admin_min_level';
