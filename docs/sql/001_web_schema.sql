/* =====================================================================
   001_web_schema.sql — ตารางของเว็บ lyindustries (หลังบ้าน + 2 ภาษา)
   Design: docs/design/admin-i18n-api.md (ข้อ 4)

   • รันใน test_LYI ก่อน → ทดสอบ → ค่อยรันใน LYI (production) ด้วยไฟล์เดียวกัน
   • SQL Server 2012, compatibility level 100 → ไม่ใช้ JSON functions / OFFSET-FETCH / IIF / TRY_CONVERT
   • รันซ้ำได้ปลอดภัย: สร้างเฉพาะสิ่งที่ยังไม่มี, ไม่ลบ/ไม่แก้ข้อมูลเดิม
   • ไม่แตะ sysmnuser (ตาราง ERP) — อ้างถึง sysmnuser.id ด้วยตัวเลขเท่านั้น ไม่มี foreign key ข้ามไป
   ===================================================================== */

SET NOCOUNT ON;
SET XACT_ABORT ON;
BEGIN TRANSACTION;

/* ---------- ค่าตั้งของเว็บ ---------- */
IF OBJECT_ID(N'dbo.web_settings', N'U') IS NULL
CREATE TABLE dbo.web_settings (
    setting_key  NVARCHAR(100) NOT NULL CONSTRAINT PK_web_settings PRIMARY KEY,
    value        NVARCHAR(MAX) NULL,
    updated_at   DATETIME      NOT NULL CONSTRAINT DF_web_settings_updated_at DEFAULT GETDATE(),
    updated_by   INT           NULL          -- sysmnuser.id
);

/* ---------- หน้า + SEO ---------- */
IF OBJECT_ID(N'dbo.web_pages', N'U') IS NULL
CREATE TABLE dbo.web_pages (
    id            INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_web_pages PRIMARY KEY,
    slug          NVARCHAR(50)  NOT NULL CONSTRAINT UQ_web_pages_slug UNIQUE,
    title_th      NVARCHAR(200) NULL,
    title_en      NVARCHAR(200) NULL,
    meta_desc_th  NVARCHAR(400) NULL,
    meta_desc_en  NVARCHAR(400) NULL,
    og_image_id   INT           NULL,         -- web_media.id
    is_published  BIT           NOT NULL CONSTRAINT DF_web_pages_is_published DEFAULT 1,
    updated_at    DATETIME      NOT NULL CONSTRAINT DF_web_pages_updated_at DEFAULT GETDATE(),
    updated_by    INT           NULL
);

/* ---------- รูป/สื่อที่อัปโหลด ---------- */
IF OBJECT_ID(N'dbo.web_media', N'U') IS NULL
CREATE TABLE dbo.web_media (
    id             INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_web_media PRIMARY KEY,
    file_path      NVARCHAR(300) NOT NULL,    -- relative เช่น uploads/2026/10/ab12cd.webp
    original_name  NVARCHAR(255) NULL,
    mime           NVARCHAR(100) NOT NULL,
    width          INT           NULL,
    height         INT           NULL,
    bytes          INT           NULL,
    alt_th         NVARCHAR(300) NULL,
    alt_en         NVARCHAR(300) NULL,
    uploaded_at    DATETIME      NOT NULL CONSTRAINT DF_web_media_uploaded_at DEFAULT GETDATE(),
    uploaded_by    INT           NULL
);

/* ---------- ข้อความ/รูปชิ้นเดี่ยวในแต่ละหน้า ---------- */
IF OBJECT_ID(N'dbo.web_blocks', N'U') IS NULL
CREATE TABLE dbo.web_blocks (
    id          INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_web_blocks PRIMARY KEY,
    page_slug   NVARCHAR(50)  NOT NULL,
    block_key   NVARCHAR(100) NOT NULL,
    block_type  NVARCHAR(20)  NOT NULL CONSTRAINT CK_web_blocks_type CHECK (block_type IN (N'text', N'richtext', N'image', N'link')),
    value_th    NVARCHAR(MAX) NULL,
    value_en    NVARCHAR(MAX) NULL,           -- ว่าง = แสดงภาษาไทยแทน
    updated_at  DATETIME      NOT NULL CONSTRAINT DF_web_blocks_updated_at DEFAULT GETDATE(),
    updated_by  INT           NULL,
    CONSTRAINT UQ_web_blocks_page_key UNIQUE (page_slug, block_key)
);

/* ---------- รายการที่วนซ้ำ (process, สินค้า, FAQ, gallery …) ---------- */
IF OBJECT_ID(N'dbo.web_items', N'U') IS NULL
CREATE TABLE dbo.web_items (
    id           INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_web_items PRIMARY KEY,
    list_key     NVARCHAR(50)  NOT NULL,       -- เช่น home.steps, home.faq, catalog.samples
    sort_order   INT           NOT NULL CONSTRAINT DF_web_items_sort_order DEFAULT 0,
    is_active    BIT           NOT NULL CONSTRAINT DF_web_items_is_active DEFAULT 1,
    data_th      NVARCHAR(MAX) NULL,           -- JSON ฟิลด์ที่แปลได้ (parse ใน PHP)
    data_en      NVARCHAR(MAX) NULL,
    data_common  NVARCHAR(MAX) NULL,           -- JSON ฟิลด์ที่ไม่ขึ้นกับภาษา
    image_id     INT           NULL CONSTRAINT FK_web_items_media REFERENCES dbo.web_media(id),
    updated_at   DATETIME      NOT NULL CONSTRAINT DF_web_items_updated_at DEFAULT GETDATE(),
    updated_by   INT           NULL
);
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = N'IX_web_items_list' AND object_id = OBJECT_ID(N'dbo.web_items'))
CREATE INDEX IX_web_items_list ON dbo.web_items (list_key, sort_order);

/* ---------- สิทธิ์ (ผูกกับ sysmnuser.id) ---------- */
IF OBJECT_ID(N'dbo.web_roles', N'U') IS NULL
CREATE TABLE dbo.web_roles (
    id         INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_web_roles PRIMARY KEY,
    role_key   NVARCHAR(50)  NOT NULL CONSTRAINT UQ_web_roles_key UNIQUE,
    name_th    NVARCHAR(100) NOT NULL,
    is_system  BIT           NOT NULL CONSTRAINT DF_web_roles_is_system DEFAULT 0   -- role ที่ระบบสร้าง ลบไม่ได้
);

IF OBJECT_ID(N'dbo.web_role_permissions', N'U') IS NULL
CREATE TABLE dbo.web_role_permissions (
    role_id     INT          NOT NULL CONSTRAINT FK_web_role_perm_role REFERENCES dbo.web_roles(id),
    permission  NVARCHAR(50) NOT NULL,
    CONSTRAINT PK_web_role_permissions PRIMARY KEY (role_id, permission)
);

IF OBJECT_ID(N'dbo.web_user_roles', N'U') IS NULL
CREATE TABLE dbo.web_user_roles (
    user_id     INT      NOT NULL,             -- sysmnuser.id (ไม่มี FK ข้ามไปตาราง ERP)
    role_id     INT      NOT NULL CONSTRAINT FK_web_user_roles_role REFERENCES dbo.web_roles(id),
    granted_at  DATETIME NOT NULL CONSTRAINT DF_web_user_roles_granted_at DEFAULT GETDATE(),
    granted_by  INT      NULL,
    CONSTRAINT PK_web_user_roles PRIMARY KEY (user_id, role_id)
);

/* ---------- กันเดารหัสผ่าน ---------- */
IF OBJECT_ID(N'dbo.web_login_attempts', N'U') IS NULL
CREATE TABLE dbo.web_login_attempts (
    id            BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_web_login_attempts PRIMARY KEY,
    username      NVARCHAR(150) NOT NULL,
    ip            VARCHAR(45)   NOT NULL,
    success       BIT           NOT NULL,
    attempted_at  DATETIME      NOT NULL CONSTRAINT DF_web_login_attempts_at DEFAULT GETDATE()
);
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = N'IX_web_login_attempts_recent' AND object_id = OBJECT_ID(N'dbo.web_login_attempts'))
CREATE INDEX IX_web_login_attempts_recent ON dbo.web_login_attempts (username, ip, attempted_at);

/* ---------- ประวัติการแก้ไข ---------- */
IF OBJECT_ID(N'dbo.web_audit_log', N'U') IS NULL
CREATE TABLE dbo.web_audit_log (
    id           BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_web_audit_log PRIMARY KEY,
    user_id      INT           NULL,
    action       NVARCHAR(20)  NOT NULL,       -- create / update / delete / login / grant / revoke
    entity       NVARCHAR(50)  NOT NULL,       -- web_blocks / web_items / web_settings …
    entity_id    NVARCHAR(100) NULL,
    before_json  NVARCHAR(MAX) NULL,
    after_json   NVARCHAR(MAX) NULL,
    ip           VARCHAR(45)   NULL,
    created_at   DATETIME      NOT NULL CONSTRAINT DF_web_audit_log_created_at DEFAULT GETDATE()
);
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = N'IX_web_audit_log_entity' AND object_id = OBJECT_ID(N'dbo.web_audit_log'))
CREATE INDEX IX_web_audit_log_entity ON dbo.web_audit_log (entity, entity_id, created_at);

/* ---------- ฟอร์มติดต่อ / ขอใบเสนอราคา ---------- */
IF OBJECT_ID(N'dbo.web_contact_requests', N'U') IS NULL
CREATE TABLE dbo.web_contact_requests (
    id            INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_web_contact_requests PRIMARY KEY,
    name          NVARCHAR(150) NOT NULL,
    company       NVARCHAR(200) NULL,
    email         NVARCHAR(200) NULL,
    phone         NVARCHAR(50)  NULL,
    product       NVARCHAR(200) NULL,
    message       NVARCHAR(MAX) NULL,
    lang          CHAR(2)       NOT NULL CONSTRAINT DF_web_contact_lang DEFAULT 'th',
    page          NVARCHAR(100) NULL,
    ip            VARCHAR(45)   NULL,
    user_agent    NVARCHAR(400) NULL,
    status        NVARCHAR(20)  NOT NULL CONSTRAINT DF_web_contact_status DEFAULT N'new'
                  CONSTRAINT CK_web_contact_status CHECK (status IN (N'new', N'read', N'done', N'spam')),
    created_at    DATETIME      NOT NULL CONSTRAINT DF_web_contact_created_at DEFAULT GETDATE(),
    handled_by    INT           NULL,
    handled_at    DATETIME      NULL
);
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = N'IX_web_contact_status' AND object_id = OBJECT_ID(N'dbo.web_contact_requests'))
CREATE INDEX IX_web_contact_status ON dbo.web_contact_requests (status, created_at);

/* =====================================================================
   ข้อมูลตั้งต้น (เพิ่มเฉพาะที่ยังไม่มี)
   ===================================================================== */

/* roles */
IF NOT EXISTS (SELECT 1 FROM dbo.web_roles WHERE role_key = N'admin')
    INSERT dbo.web_roles (role_key, name_th, is_system) VALUES (N'admin', N'ผู้ดูแลระบบ', 1);
IF NOT EXISTS (SELECT 1 FROM dbo.web_roles WHERE role_key = N'editor')
    INSERT dbo.web_roles (role_key, name_th, is_system) VALUES (N'editor', N'ผู้แก้ไขเนื้อหา', 1);
IF NOT EXISTS (SELECT 1 FROM dbo.web_roles WHERE role_key = N'translator')
    INSERT dbo.web_roles (role_key, name_th, is_system) VALUES (N'translator', N'ผู้แปล (ภาษาอังกฤษ)', 1);
IF NOT EXISTS (SELECT 1 FROM dbo.web_roles WHERE role_key = N'sales')
    INSERT dbo.web_roles (role_key, name_th, is_system) VALUES (N'sales', N'ฝ่ายขาย (ดูข้อความติดต่อ)', 1);

/* role → permission */
INSERT dbo.web_role_permissions (role_id, permission)
SELECT r.id, p.permission
FROM dbo.web_roles r
JOIN (
              SELECT N'admin' AS role_key, N'content.edit'      AS permission
    UNION ALL SELECT N'admin',      N'content.translate'
    UNION ALL SELECT N'admin',      N'media.upload'
    UNION ALL SELECT N'admin',      N'contact.view'
    UNION ALL SELECT N'admin',      N'settings.edit'
    UNION ALL SELECT N'admin',      N'users.manage'
    UNION ALL SELECT N'editor',     N'content.edit'
    UNION ALL SELECT N'editor',     N'media.upload'
    UNION ALL SELECT N'translator', N'content.translate'
    UNION ALL SELECT N'sales',      N'contact.view'
) p ON p.role_key = r.role_key
WHERE NOT EXISTS (SELECT 1 FROM dbo.web_role_permissions x WHERE x.role_id = r.id AND x.permission = p.permission);

/* settings */
IF NOT EXISTS (SELECT 1 FROM dbo.web_settings WHERE setting_key = N'default_lang')
    INSERT dbo.web_settings (setting_key, value) VALUES (N'default_lang', N'th');
IF NOT EXISTS (SELECT 1 FROM dbo.web_settings WHERE setting_key = N'admin_min_level')
    INSERT dbo.web_settings (setting_key, value) VALUES (N'admin_min_level', N'5');   -- sysmnuser.level >= 5 = admin อัตโนมัติ

/* pages */
INSERT dbo.web_pages (slug)
SELECT s.slug FROM (
              SELECT N'home' AS slug
    UNION ALL SELECT N'about'
    UNION ALL SELECT N'catalog'
    UNION ALL SELECT N'contact'
) s
WHERE NOT EXISTS (SELECT 1 FROM dbo.web_pages p WHERE p.slug = s.slug);

COMMIT TRANSACTION;

/* ตรวจผล */
SELECT t.name AS table_name, SUM(p.rows) AS row_count
FROM sys.tables t
JOIN sys.partitions p ON p.object_id = t.object_id AND p.index_id IN (0, 1)
WHERE t.name LIKE N'web[_]%'
GROUP BY t.name
ORDER BY t.name;
