<?php
declare(strict_types=1);

/**
 * Image uploads → uploads/YYYY/MM/<random>.webp + a lyiweb_media row.
 *
 * The browser already shrinks photos before sending (admin.js), so uploads stay small even where
 * PHP allows only 2 MB. Here every file is checked to be a real JPEG/PNG/WebP, then re-encoded
 * with GD (drops metadata and anything hidden in the file) to WebP, longest side ≤ MEDIA_MAX_SIDE.
 * Without GD WebP support the checked file is kept as is.
 */

const MEDIA_MAX_BYTES = 15 * 1024 * 1024;
const MEDIA_MAX_SIDE  = 2400;
const MEDIA_MAX_PIXELS = 40_000_000;   // refuse huge images before GD tries to load them
const MEDIA_WEBP_QUALITY = 82;

/** Readable message for a PHP upload error code, or null when the upload is fine. */
function media_upload_error(int $code): ?string
{
    return match ($code) {
        UPLOAD_ERR_OK => null,
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'ไฟล์ใหญ่เกินที่ server รับได้ (' . ini_get('upload_max_filesize') . ')',
        UPLOAD_ERR_PARTIAL => 'อัปโหลดไม่ครบ — ลองใหม่อีกครั้ง',
        UPLOAD_ERR_NO_FILE => 'ไม่ได้เลือกไฟล์',
        default => 'อัปโหลดไม่สำเร็จ (รหัส ' . $code . ')',
    };
}

/**
 * Store one uploaded image. $file is one entry shaped like $_FILES['x'].
 *
 * @param array{name: string, type?: string, tmp_name: string, error: int, size: int} $file
 * @return array{id: int, path: string}
 * @throws RuntimeException with a Thai message for the admin
 */
function media_store_upload(array $file, int $userId, string $altTh = '', array $opts = []): array
{
    $cover = $opts['cover'] ?? null;          // [w, h] → crop to exactly this size (centre)
    $asJpeg = ($opts['format'] ?? '') === 'jpg';
    if (($err = media_upload_error((int) $file['error'])) !== null) {
        throw new RuntimeException($err);
    }
    if (!is_uploaded_file($file['tmp_name']) && PHP_SAPI !== 'cli') {
        throw new RuntimeException('ไฟล์ไม่ถูกต้อง');
    }
    if ((int) $file['size'] <= 0 || (int) $file['size'] > MEDIA_MAX_BYTES) {
        throw new RuntimeException('ไฟล์ต้องไม่เกิน ' . (MEDIA_MAX_BYTES >> 20) . ' MB');
    }
    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if ($info === false || !isset($types[$info[2]])) {
        throw new RuntimeException('รองรับเฉพาะรูป JPG, PNG หรือ WebP');
    }
    [$w, $h, $type] = $info;
    if ($w < 1 || $h < 1 || $w * $h > MEDIA_MAX_PIXELS) {
        throw new RuntimeException('ขนาดรูปใหญ่เกินไป (' . $w . '×' . $h . ')');
    }

    $relDir = 'uploads/' . date('Y/m');
    $absDir = APP_ROOT . '/' . $relDir;
    if (!is_dir($absDir) && !@mkdir($absDir, 0775, true) && !is_dir($absDir)) {
        throw new RuntimeException('server ไม่อนุญาตให้บันทึกไฟล์ (โฟลเดอร์ uploads/ เขียนไม่ได้)');
    }
    $name = bin2hex(random_bytes(10));

    if ($cover !== null || $asJpeg) {
        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file['tmp_name']),
            IMAGETYPE_PNG  => @imagecreatefrompng($file['tmp_name']),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file['tmp_name']) : false,
        };
        if ($src === false) {
            throw new RuntimeException('อ่านไฟล์รูปไม่ได้ — ไฟล์อาจเสีย');
        }
        [$nw, $nh] = $cover ?? [min($w, MEDIA_MAX_SIDE), (int) round($h * min($w, MEDIA_MAX_SIDE) / $w)];
        $scale = max($nw / $w, $nh / $h);
        $cw = (int) round($nw / $scale);
        $ch = (int) round($nh / $scale);
        $dst = imagecreatetruecolor($nw, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, (int) (($w - $cw) / 2), (int) (($h - $ch) / 2), $nw, $nh, $cw, $ch);
        $relPath = "$relDir/$name.jpg";
        $ok = imagejpeg($dst, APP_ROOT . '/' . $relPath, 86);
        imagedestroy($src);
        imagedestroy($dst);
        if (!$ok) {
            throw new RuntimeException('บันทึกรูปไม่สำเร็จ');
        }
        [$w, $h, $mime] = [$nw, $nh, 'image/jpeg'];
    } elseif (function_exists('imagewebp')) {
        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file['tmp_name']),
            IMAGETYPE_PNG  => @imagecreatefrompng($file['tmp_name']),
            IMAGETYPE_WEBP => @imagecreatefromwebp($file['tmp_name']),
        };
        if ($src === false) {
            throw new RuntimeException('อ่านไฟล์รูปไม่ได้ — ไฟล์อาจเสีย');
        }
        $scale = min(1, MEDIA_MAX_SIDE / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        $relPath = "$relDir/$name.webp";
        $ok = imagewebp($dst, APP_ROOT . '/' . $relPath, MEDIA_WEBP_QUALITY);
        imagedestroy($src);
        imagedestroy($dst);
        if (!$ok) {
            throw new RuntimeException('บันทึกรูปไม่สำเร็จ');
        }
        [$w, $h, $mime] = [$nw, $nh, 'image/webp'];
    } else {
        $relPath = "$relDir/$name." . $types[$type];
        $moved = PHP_SAPI === 'cli' ? copy($file['tmp_name'], APP_ROOT . '/' . $relPath) : move_uploaded_file($file['tmp_name'], APP_ROOT . '/' . $relPath);
        if (!$moved) {
            throw new RuntimeException('บันทึกรูปไม่สำเร็จ');
        }
        $mime = (string) $info['mime'];
    }

    $bytes = (int) filesize(APP_ROOT . '/' . $relPath);
    $id = (int) db_rows(
        'INSERT dbo.lyiweb_media (file_path, original_name, mime, width, height, bytes, alt_th, uploaded_by)
         OUTPUT INSERTED.id AS id VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [$relPath, mb_substr((string) $file['name'], 0, 255), $mime, $w, $h, $bytes, $altTh === '' ? null : mb_substr($altTh, 0, 300), $userId]
    )[0]['id'];
    audit_log('create', 'lyiweb_media', (string) $id, null, ['path' => $relPath, 'name' => (string) $file['name'], 'bytes' => $bytes]);

    return ['id' => $id, 'path' => $relPath];
}
