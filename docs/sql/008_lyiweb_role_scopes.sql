/* ============================================================================
   008 — ขอบเขตของ role: แก้เนื้อหาได้เฉพาะหน้า/ส่วนที่กำหนด
   SQL Server 2012 (compatibility level 100) · รันซ้ำได้ปลอดภัย
   รันใน test_LYI ก่อน แล้วค่อย LYI

   scope:  '*'            = ทุกหน้า ทุกส่วน (รวมที่เพิ่มในอนาคต)
           'home'         = ทั้งหน้า (ทุกส่วนของหน้านั้น)
           'home.hero'    = ส่วนเดียว · 'home.seo' = ชื่อหน้า/meta description
   ใช้กับสิทธิ์ content.edit และ content.translate เท่านั้น
   role ที่มีสิทธิ์เนื้อหาแต่ไม่มี scope เลย = แก้เนื้อหาไม่ได้สักส่วน
   ============================================================================ */

IF OBJECT_ID(N'dbo.lyiweb_role_scopes', N'U') IS NULL
CREATE TABLE dbo.lyiweb_role_scopes (
    role_id  INT           NOT NULL CONSTRAINT FK_lyiweb_role_scopes_role REFERENCES dbo.lyiweb_roles(id),
    scope    NVARCHAR(100) NOT NULL,
    CONSTRAINT PK_lyiweb_role_scopes PRIMARY KEY (role_id, scope)
);

/* role เดิมที่มีสิทธิ์เนื้อหา → ทุกส่วน (พฤติกรรมเท่าเดิม) */
INSERT dbo.lyiweb_role_scopes (role_id, scope)
SELECT r.id, N'*'
FROM dbo.lyiweb_roles r
WHERE r.role_key IN (N'admin', N'editor', N'translator')
  AND NOT EXISTS (SELECT 1 FROM dbo.lyiweb_role_scopes s WHERE s.role_id = r.id);

/* ตรวจผล */
SELECT r.role_key, r.name_th, s.scope
FROM dbo.lyiweb_roles r
LEFT JOIN dbo.lyiweb_role_scopes s ON s.role_id = r.id
ORDER BY r.id, s.scope;
