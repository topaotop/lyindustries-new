<?php // connectgrp.php — LYI (GRP) DB connection of THIS server only · ไฟล์ตัวอย่าง (ไม่มีรหัส) — ไฟล์จริงไม่อยู่ใน git
// วิธีใช้: copy ไฟล์นี้ไปเป็น connectgrp.php ที่ root ของเว็บบน server นั้น แล้วใส่ค่าของ server นั้นเท่านั้น
//   • local dev / company server 192.168.0.70  → $env = 'dev' / 'test',  test_LYI @ 192.168.0.22
//   • production (www.lyindustries.com, z.com) → $env = 'production',    LYI @ 183.89.245.21
// ห้ามใส่หลาย environment ในไฟล์เดียว และห้ามเลือก DB จากชื่อโดเมน ($_SERVER['HTTP_HOST'] ปลอมได้)
// แนะนำ: เครื่องทดสอบใช้ SQL login ที่เข้าได้เฉพาะ test_LYI (ไม่ใช้ login เดียวกับ production)
$env = 'production';

$serverName = "SQL-SERVER-ADDRESS";
$connectionInfo = [
    "Database" => "DATABASE-NAME",
    "UID" => "SQL-LOGIN",
    "PWD" => "SQL-PASSWORD",
    "MultipleActiveResultSets" => true,
    "CharacterSet" => 'UTF-8',
    "TrustServerCertificate" => true,
];

$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'msg' => 'DB connect failed (' . $env . ')'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    throw new Exception("DB connection failed [$env]: " . print_r(sqlsrv_errors(), true));
}
