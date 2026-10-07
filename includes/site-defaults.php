<?php
declare(strict_types=1);

/**
 * Built-in site settings and page SEO — fallback when lyiweb_settings / lyiweb_pages are empty or
 * the DB is unreachable, and the source for docs/sql/003_lyiweb_seed_site.sql
 * (php tools/build-seed-site.php). Once seeded, edit these in the admin, not here.
 *
 * @return array{settings: array<string, string>, pages: array<string, array{title_th: string, meta_desc_th: string}>}
 */
return [
    'settings' => [
        'phone'                  => '02-517-0768', // display format; tel: links and schema +66 formats are derived from it
        'phone_ext'              => '120, 121',
        'fax'                    => '02-517-4888',
        'email'                  => 'sales@lyindustries.com',
        'line_id'                => '@lyindustries',
        'line_url'               => 'https://line.me/R/ti/p/@lyindustries',
        'company_th'             => 'บริษัท แอล วาย อินดัสตรีย์ จำกัด',
        'address1_th'            => '124 ซอยรามอินทรา 109 ถนนพระยาสุเรนทร์ แขวงบางชัน', // address is split in two so the homepage footer can break the line
        'address2_th'            => 'เขตคลองสามวา กรุงเทพฯ 10510',
        'company_en'             => 'L.Y. Industries Co., Ltd.',            // English pages (/en/)
        'address1_en'            => '124 Soi Ram Inthra 109, Phraya Suren Road, Bang Chan',
        'address2_en'            => 'Khlong Sam Wa, Bangkok 10510',
        'hours_weekday_open'     => '08:30', // HH:MM — used in the visible text and in schema.org opening hours
        'hours_weekday_close'    => '17:30',
        'hours_sat_open'         => '08:30',
        'hours_sat_close'        => '12:00',
        'trimrite_url'           => 'https://www.trimrite.com/',
        'inspiration_url'        => 'https://www.lyindustries.com/lyinspirationhub/', // spelling confirmed 5 Oct 2026
    ],
    'pages' => [
        'home' => [
            'title_th'     => 'L.Y. Industries (Hybrid) — Narrow Fabrics & Trims ครบวงจร มาตรฐานระดับโลก',
            'meta_desc_th' => '',
        ],
        'about' => [
            'title_th'     => 'เกี่ยวกับ L.Y. Industries — ผู้ผลิต Narrow Fabrics & Trims ตั้งแต่ปี 1978',
            'meta_desc_th' => 'L.Y. Industries Co., Ltd. ผู้ผลิต Narrow Fabrics และ Trims ครบวงจรในกรุงเทพฯ ตั้งแต่ปี 1978 — ยางยืด เทปทอ เทปถัก เชือก ขอบเอว และงาน finishing สำหรับแบรนด์กีฬาและแฟชั่นระดับโลก',
        ],
        'catalog' => [
            'title_th'     => 'แคตตาล็อกสินค้า Narrow Fabric & Trims — ยางยืด เทปทอ เทปถัก เชือก | L.Y. Industries',
            'meta_desc_th' => 'แคตตาล็อกสินค้า L.Y. Industries ผู้ผลิต Narrow Fabric & Trims ครบวงจรในกรุงเทพฯ — ยางยืด เทปทอ เทปถัก Raschel/Crochet เชือกรูด เชือกยางยืด ขอบเอว และงานพิมพ์โลโก้ สำหรับเสื้อผ้ากีฬา ชุดชั้นใน และแฟชั่น พร้อมรับพัฒนาตามสเปก',
        ],
        'contact' => [
            'title_th'     => 'ติดต่อ L.Y. Industries — ผู้ผลิต Narrow Fabric & Trims กรุงเทพฯ',
            'meta_desc_th' => 'ติดต่อ L.Y. Industries ผู้ผลิต Narrow Fabric และ Trims ครบวงจร (ยางยืด เทปทอ เทปถัก เชือกรูด ขอบเอว) ที่ 124 ซอยรามอินทรา 109 ถนนพระยาสุเรนทร์ แขวงบางชัน เขตคลองสามวา กรุงเทพฯ 10510 โทร 02-517-0768 ต่อ 120, 121 อีเมล sales@lyindustries.com LINE @lyindustries',
        ],
    ],
];
