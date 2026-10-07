<?php
declare(strict_types=1);

require __DIR__ . '/../includes/admin/init.php';

admin_require('settings.edit');

/** Editable settings: key => [label, type, help]. Order = order on the form. */
$fields = [
    'ติดต่อ' => [
        'phone'     => ['เบอร์โทร', 'phone', 'รูปแบบ 0X-XXX-XXXX — ลิงก์ tel: และ +66 ใน schema สร้างจากเบอร์นี้อัตโนมัติ'],
        'phone_ext' => ['เบอร์ต่อ', 'text', 'เช่น 120, 121 (ว่างได้)'],
        'fax'       => ['แฟกซ์', 'phone', ''],
        'email'     => ['อีเมล', 'email', 'ใช้ในลิงก์ mailto ทุกหน้า + ฟอร์มขอใบเสนอราคา'],
        'line_id'   => ['LINE ID', 'text', 'ข้อความที่แสดง เช่น @lyindustries'],
        'line_url'  => ['ลิงก์ LINE', 'url', 'เช่น https://line.me/R/ti/p/@lyindustries'],
    ],
    'บริษัท & ที่อยู่' => [
        'company_th'  => ['ชื่อบริษัท (ไทย)', 'text', ''],
        'address1_th' => ['ที่อยู่ บรรทัด 1', 'text', 'เลขที่ ซอย ถนน แขวง'],
        'address2_th' => ['ที่อยู่ บรรทัด 2', 'text', 'เขต จังหวัด รหัสไปรษณีย์ (หน้าแรกขึ้นบรรทัดใหม่ตรงนี้)'],
        'company_en'  => ['ชื่อบริษัท (English)', 'text', 'แสดงบนหน้าเว็บภาษาอังกฤษ (/en/)'],
        'address1_en' => ['ที่อยู่ บรรทัด 1 (English)', 'text', 'เช่น 124 Soi Ram Inthra 109, Phraya Suren Road, Bang Chan'],
        'address2_en' => ['ที่อยู่ บรรทัด 2 (English)', 'text', 'เช่น Khlong Sam Wa, Bangkok 10510'],
    ],
    'เวลาทำการ' => [
        'hours_weekday_open'  => ['จันทร์–ศุกร์ เปิด', 'time', ''],
        'hours_weekday_close' => ['จันทร์–ศุกร์ ปิด', 'time', ''],
        'hours_sat_open'      => ['เสาร์ เปิด', 'time', ''],
        'hours_sat_close'     => ['เสาร์ ปิด', 'time', ''],
    ],
    'ลิงก์ภายนอก' => [
        'trimrite_url'    => ['TRIMRITE®', 'url', ''],
        'inspiration_url' => ['Inspiration Hub', 'url', 'ใช้ www. เสมอ (ในบริษัท lyindustries.com แบบไม่มี www ชี้ไป Domain Controller)'],
    ],
];

$validate = static function (string $type, string $v): ?string {
    if ($v === '') {
        return null;
    }
    return match ($type) {
        'email' => filter_var($v, FILTER_VALIDATE_EMAIL) ? null : 'อีเมลไม่ถูกต้อง',
        'url'   => (preg_match('#^https://#i', $v) && filter_var($v, FILTER_VALIDATE_URL)) ? null : 'ต้องเป็นลิงก์ที่ขึ้นต้นด้วย https://',
        'time'  => preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v) ? null : 'รูปแบบ HH:MM เช่น 08:30',
        'phone' => preg_match('/^0\d{1,2}-\d{3}-\d{4}$/', $v) ? null : 'รูปแบบ 0X-XXX-XXXX เช่น 02-517-0768',
        default => mb_strlen($v) > 300 ? 'ยาวเกิน 300 ตัวอักษร' : null,
    };
};

$current = [];
foreach (db_rows('SELECT setting_key, value FROM dbo.lyiweb_settings') as $r) {
    $current[$r['setting_key']] = (string) $r['value'];
}
$errors = [];
$values = $current;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_check_post();
    $changes = [];
    foreach ($fields as $group) {
        foreach ($group as $key => [$label, $type]) {
            $v = trim((string) ($_POST['s'][$key] ?? ''));
            $values[$key] = $v;
            if (($err = $validate($type, $v)) !== null) {
                $errors[$key] = $err;
            } elseif ($v !== ($current[$key] ?? '')) {
                $changes[$key] = $v;
            }
        }
    }
    if ($errors === [] && $changes !== []) {
        db_transaction(static function () use ($changes, $current): void {
            foreach ($changes as $key => $v) {
                $uid = auth_user()['id'];
                if (array_key_exists($key, $current)) {
                    db_exec('UPDATE dbo.lyiweb_settings SET value = ?, updated_at = GETDATE(), updated_by = ? WHERE setting_key = ?', [$v, $uid, $key]);
                } else {
                    db_exec('INSERT dbo.lyiweb_settings (setting_key, value, updated_by) VALUES (?, ?, ?)', [$key, $v, $uid]);
                }
                audit_log('update', 'lyiweb_settings', $key, $current[$key] ?? null, $v);
            }
        });
        content_cache_clear();
        flash('ok', 'บันทึกแล้ว ' . count($changes) . ' รายการ — หน้าเว็บอัปเดตทันที');
        header('Location: settings.php');
        exit;
    }
    if ($errors === []) {
        flash('info', 'ไม่มีอะไรเปลี่ยน');
        header('Location: settings.php');
        exit;
    }
}

admin_page_start('ข้อมูลติดต่อ & ลิงก์', 'settings.php');
?>
<h1>ข้อมูลติดต่อ & ลิงก์</h1>
<p class="muted">แก้ที่นี่ที่เดียว เปลี่ยนทุกหน้าเว็บ รวมข้อมูล schema สำหรับ Google · ⚠️ meta description ของหน้า "ติดต่อ" มีเบอร์/อีเมลอยู่ในประโยค — ถ้าเปลี่ยนเบอร์ ให้แก้ที่เมนู <a href="seo.php?tab=meta">SEO &amp; AEO</a> ด้วย</p>
<?php if ($errors !== []): ?><div class="flash flash-error">ยังไม่ได้บันทึก — มีช่องที่ต้องแก้ <?= count($errors) ?> ช่อง</div><?php endif; ?>
<form method="post" class="form">
  <?= csrf_field() ?>
<?php foreach ($fields as $groupName => $group): ?>
  <fieldset class="card">
    <legend><?= e($groupName) ?></legend>
<?php foreach ($group as $key => [$label, $type, $help]): ?>
    <label class="field<?= isset($errors[$key]) ? ' has-error' : '' ?>">
      <span><?= e($label) ?></span>
      <input name="s[<?= e($key) ?>]" value="<?= e($values[$key] ?? '') ?>"<?= $type === 'email' ? ' type="email"' : ($type === 'url' ? ' type="url"' : '') ?><?= $type === 'time' ? ' pattern="[0-2][0-9]:[0-5][0-9]" placeholder="08:30"' : '' ?>>
<?php if (isset($errors[$key])): ?><small class="err"><?= e($errors[$key]) ?></small><?php elseif ($help !== ''): ?><small class="muted"><?= e($help) ?></small><?php endif; ?>
    </label>
<?php endforeach; ?>
  </fieldset>
<?php endforeach; ?>
  <div class="actions"><button class="btn btn-primary" type="submit">บันทึก</button></div>
</form>
<?php
admin_page_end();
