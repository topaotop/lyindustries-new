<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/icons.php';
require __DIR__ . '/includes/lib/content.php';

$lang = 'th';
$partnerLogos = content_list('home.partners', $lang);
$tiles        = content_list('home.tiles', $lang);
$steps        = content_list('home.steps', $lang);
$swatches     = content_list('home.swatches', $lang);
$productCards = content_list('home.products', $lang);
$galleryItems = content_list('home.gallery', $lang);
$faqList      = content_list('home.faq', $lang);
$activeSwatch = $swatches[0];

// Mobile side-menu (☰).
$menuItems = [
    ['n' => '01', 'label' => 'หน้าแรก',                 'href' => '#hero'],
    ['n' => '02', 'label' => 'แคตาล็อกสินค้า',            'href' => 'catalog.php'],
    ['n' => '03', 'label' => 'TRIMRITE® ↗',             'href' => SITE_TRIMRITE_URL, 'external' => true],
    ['n' => '04', 'label' => 'เกี่ยวกับเรา',               'href' => 'about.php'],
    ['n' => '05', 'label' => 'ติดต่อเรา / ขอใบเสนอราคา',   'href' => 'contact.php'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>L.Y. Industries (Hybrid) — Narrow Fabrics &amp; Trims ครบวงจร มาตรฐานระดับโลก</title>
<link rel="icon" type="image/svg+xml" href="assets/img/logo-lyi.svg">
<link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
<link rel="stylesheet" href="assets/css/fonts.css">
<link rel="stylesheet" href="assets/css/home.css">
</head>
<body>


<div style="width:100%;min-height:100vh;background:var(--bg-primary);color:var(--text-primary);position:relative">

<!-- TOP GLOBAL SCROLL PROGRESS BAR -->
<div id="lyProgress" style="position:fixed;top:0;left:0;height:2.5px;width:0%;background:linear-gradient(90deg,var(--brand-orange),var(--brand-amber));z-index:999;transition:width 0.05s linear;pointer-events:none"></div>

<!-- ========================================================================
     NAVIGATION: FLOATING APPLE GLASS CAPSULE
     ======================================================================== -->
<header style="position:sticky;top:16px;z-index:100;max-width:1240px;margin:0 auto;padding:0 20px">
  <nav style="background:var(--bg-glass);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid var(--border-glass);border-radius:100px;padding:10px 22px;display:flex;align-items:center;justify-content:space-between;gap:24px;box-shadow:0 12px 32px rgba(0,0,0,0.45)">
    <!-- Brand Logo -->
    <a href="#" style="display:flex;align-items:center;gap:12px">
      <img src="assets/img/logo-lyi.svg" alt="L.Y. Industries" width="40" height="40" style="display:block;width:40px;height:40px">
      <div class="brand-text" style="display:flex;flex-direction:column;line-height:1.15;white-space:nowrap">
        <span style="font-family:var(--font-heading);font-weight:600;font-size:15px;letter-spacing:0.08em;color:#fff">L.Y. INDUSTRIES</span>
        <span style="font-family:var(--font-mono);font-size:9px;color:var(--text-secondary);letter-spacing:0.12em">BANGKOK · EST. 1978</span>
      </div>
    </a>

    <!-- Desktop Navigation Links -->
    <div style="display:flex;align-items:center;gap:26px;font-size:13.5px;font-weight:500;color:var(--text-secondary);white-space:nowrap" class="desktop-nav">
      <a class="hv-1" href="#hero" style="transition:color 0.25s">หน้าแรก</a>
      <a class="hv-1" href="#process" style="transition:color 0.25s">กระบวนการผลิต</a>
      <a class="hv-1" href="catalog.php" style="transition:color 0.25s">แคตาล็อกสินค้า</a>
      <a class="hv-1" href="https://www.trimrite.com/" target="_blank" rel="noopener" style="transition:color 0.25s">TRIMRITE®</a>
      <a class="hv-1" href="about.php" style="transition:color 0.25s">เกี่ยวกับเรา</a>
      <a class="hv-1" href="contact.php" style="transition:color 0.25s">ติดต่อเรา</a>
    </div>

    <!-- Actions -->
    <div style="display:flex;align-items:center;gap:12px">
      <a href="#contact" class="nav-cta hv-2" style="background:linear-gradient(135deg,var(--brand-orange),var(--brand-orange-light));color:#fff;padding:9px 20px;border-radius:100px;font-weight:600;font-size:13px;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;box-shadow:0 4px 18px var(--brand-orange-glow);transition:transform 0.2s,box-shadow 0.2s">
        <span>ขอใบเสนอราคา</span>
        <span style="font-size:14px">→</span>
      </a>
      <!-- Mobile Hamburger Button -->
      <button type="button" data-menu-toggle aria-label="เมนู" aria-expanded="false" aria-controls="sideMenu" class="mobile-burger hv-3" style="width:38px;height:38px;border-radius:50%;border:1px solid var(--border-light);background:rgba(255,255,255,0.05);cursor:pointer;display:grid;place-items:center;padding:0;transition:border-color 0.2s">
        <div style="display:flex;flex-direction:column;gap:4.5px;width:16px">
          <span style="height:1.5px;background:#fff;border-radius:1px;transition:transform 0.3s" class="burger-bar-1"></span>
          <span style="height:1.5px;background:#fff;border-radius:1px;transition:transform 0.3s" class="burger-bar-2"></span>
        </div>
      </button>
    </div>
  </nav>
</header>

<!-- SIDEBAR DRAWER MENU -->
<div data-menu-toggle class="menu-overlay" style="position:fixed;inset:0;z-index:89;background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);transition:opacity 0.35s"></div>
<aside id="sideMenu" class="menu-drawer" style="position:fixed;top:0;right:0;bottom:0;z-index:90;width:min(420px,88vw);background:rgba(10,10,13,0.97);border-left:1px solid var(--border-light);box-shadow:-30px 0 80px rgba(0,0,0,0.5);display:flex;flex-direction:column;transition:transform 0.45s cubic-bezier(.22,.8,.3,1);padding:28px 28px 32px;overflow-y:auto">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px">
    <span style="font-family:var(--font-mono);font-size:12px;letter-spacing:0.18em;color:var(--brand-orange);font-weight:700">MENU</span>
    <button type="button" data-menu-toggle aria-label="ปิดเมนู" style="background:none;border:1px solid var(--border-light);width:40px;height:40px;border-radius:50%;color:#fff;font-size:22px;cursor:pointer;display:grid;place-items:center">×</button>
  </div>
  <div style="display:flex;flex-direction:column">
    <?php foreach ($menuItems as $m): ?>
      <a class="hv-4" href="<?= e($m['href']) ?>"<?= external_attrs($m) ?> data-menu-toggle style="font-family:var(--font-heading);font-size:22px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;justify-content:space-between;gap:16px;border-bottom:1px solid var(--border-light);padding:16px 0;transition:color 0.2s">
        <span><?= e($m['label']) ?></span>
        <span style="font-family:var(--font-mono);font-size:12px;color:var(--text-tertiary)"><?= e($m['n']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
  <div style="margin-top:auto;padding-top:24px;display:flex;flex-direction:column;gap:12px">
    <div style="font-size:14px;line-height:1.7;color:var(--text-secondary)">โทร: <?= e(SITE_PHONE) ?> ต่อ <?= e(SITE_PHONE_EXT) ?><br>อีเมล: <?= e(SITE_EMAIL) ?></div>
    <a href="#contact" data-menu-toggle style="background:var(--brand-orange);color:#fff;padding:14px;border-radius:12px;text-align:center;font-weight:600;font-size:15px">ติดต่อทีมฝ่ายขาย →</a>
  </div>
</aside>

<!-- ========================================================================
     HERO SECTION: CINEMATIC FACTORY & PRECISION HEADLINE
     ======================================================================== -->
<section id="hero" style="position:relative;min-height:96vh;overflow:hidden;display:flex;align-items:center;padding:120px 0 60px">
  <!-- Cinematic Background Video with Vignette & Ambient Radial Light -->
  <div style="position:absolute;inset:0;z-index:1;overflow:hidden">
    <video id="heroVideo" src="https://www.lyindustries.com/media/header/PASSION%20(1).mp4" poster="https://www.lyindustries.com/img/bgvideo1.jpg" autoplay muted loop playsinline style="width:100%;height:100%;object-fit:cover;opacity:0.78;filter:contrast(1.08) saturate(1);will-change:transform"></video>
    <!-- Multi-stage Dark Gradient Overlays for Apple Cinematic Depth -->
    <div style="position:absolute;inset:0;background:radial-gradient(circle at 50% 40%, rgba(8,8,10,0.05) 0%, rgba(8,8,10,0.6) 80%, var(--bg-primary) 100%)"></div>
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(8,8,10,0.55) 0%, transparent 35%, rgba(8,8,10,0.85) 85%, var(--bg-primary) 100%)"></div>
    
    <!-- Ambient Glow Orbs -->
    <div style="position:absolute;top:15%;left:20%;width:50vw;height:50vw;max-width:600px;max-height:600px;border-radius:50%;background:radial-gradient(circle,rgba(255,90,31,0.18),transparent 70%);filter:blur(80px);animation:ly-pulse-glow 8s ease-in-out infinite alternate;pointer-events:none"></div>
    <div style="position:absolute;bottom:10%;right:15%;width:40vw;height:40vw;max-width:500px;max-height:500px;border-radius:50%;background:radial-gradient(circle,rgba(255,170,64,0.12),transparent 70%);filter:blur(80px);pointer-events:none"></div>
  </div>

  <!-- Hero Content Stage -->
  <div id="heroContent" style="position:relative;z-index:2;max-width:1240px;margin:0 auto;padding:0 24px;width:100%;display:flex;flex-direction:column;align-items:center;text-align:center;gap:32px;will-change:transform,opacity">
    
    <!-- Micro Badge Pill -->
    <div style="display:inline-flex;align-items:center;gap:10px;padding:8px 18px;border-radius:100px;background:rgba(255,90,31,0.1);border:1px solid rgba(255,90,31,0.3);backdrop-filter:blur(12px)">
      <span style="width:7px;height:7px;border-radius:50%;background:var(--brand-orange);box-shadow:0 0 10px var(--brand-orange)"></span>
      <span style="font-family:var(--font-mono);font-size:11.5px;font-weight:600;letter-spacing:0.14em;color:#ff9e75">ONE-STOP NARROW FABRICS &amp; TRIMS · EST. 1978 · BANGKOK</span>
    </div>

    <!-- Massive Precision Headline -->
    <h1 class="text-gradient-silver" style="font-size:clamp(42px,6.2vw,92px);line-height:1.18;font-weight:700;max-width:1080px;margin:0">
      Trims ที่ไม่เคยทำให้<br>ไลน์ผลิตของคุณ<span class="text-gradient-orange">สะดุด</span>
    </h1>

    <!-- 50-word AEO Paragraph -->
    <p style="font-size:clamp(16px,1.3vw,19px);line-height:1.75;color:var(--text-secondary);max-width:720px;font-weight:400">
      <strong style="color:#fff;font-weight:500">LY Industries</strong> คือผู้ผลิต Narrow Fabric และ Trims ครบวงจรในกรุงเทพฯ ก่อตั้งปี 1978 ผลิตยางยืด เทปทอ เชือกรูด ขอบเอว และงานซิลิโคน ให้แบรนด์กีฬาระดับโลกมากว่า 40 ปี ด้วยการทอ ถัก ย้อม และตกแต่งสำเร็จในโรงงานเดียว — คุณภาพสม่ำเสมอทุกล็อต
    </p>

    <!-- CTAs -->
    <div style="display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;margin-top:8px">
      <a class="hv-5" href="#contact" style="background:linear-gradient(135deg,var(--brand-orange),var(--brand-orange-light));color:#fff;padding:16px 36px;border-radius:100px;font-weight:600;font-size:16px;box-shadow:0 8px 28px var(--brand-orange-glow);transition:all 0.3s cubic-bezier(0.16,1,0.3,1);display:inline-flex;align-items:center;gap:8px">
        <span>ขอใบเสนอราคา / Request a Quote</span>
        <span style="font-size:18px">→</span>
      </a>
      <a class="hv-6" href="#process" style="background:rgba(255,255,255,0.06);border:1px solid var(--border-glass);color:var(--text-primary);padding:16px 32px;border-radius:100px;font-weight:500;font-size:16px;backdrop-filter:blur(16px);transition:all 0.3s">
        ขั้นตอนการผลิตของเรา ↓
      </a>
    </div>

  </div>

  <!-- Bottom Scroll Cue -->
  <div class="scroll-cue" style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);z-index:2;display:flex;flex-direction:column;align-items:center;gap:8px;pointer-events:none">
    <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.25em;color:var(--text-tertiary)">SCROLL TO EXPLORE</span>
    <div style="width:1.5px;height:32px;background:rgba(255,255,255,0.1);position:relative;overflow:hidden;border-radius:1px">
      <div style="position:absolute;inset:0;background:var(--brand-orange);animation:ly-scroll-line 2s cubic-bezier(0.65,0,0.35,1) infinite"></div>
    </div>
  </div>
</section>

<!-- ========================================================================
     TRUST RIBBON: GLOBAL BRAND CREDIBILITY & ANIMATED COUNTERS
     ======================================================================== -->
<section style="position:relative;z-index:10;background:var(--bg-secondary);border-top:1px solid var(--border-light);border-bottom:1px solid var(--border-light);padding:44px 0 36px;overflow:hidden">
  
  <div style="max-width:1240px;margin:0 auto;padding:0 24px;display:flex;flex-direction:column;gap:28px">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
      <span style="font-family:var(--font-mono);font-size:11.5px;letter-spacing:0.18em;color:var(--text-secondary);font-weight:600">
        TRUSTED BY GLOBAL SPORTSWEAR &amp; APPAREL LEADERS
      </span>
    </div>

    <!-- Infinite Seamless Segment Marquee -->
    <div style="position:relative;width:100%;overflow:hidden;mask-image:linear-gradient(90deg,transparent 0%,#000 12%,#000 88%,transparent 100%);-webkit-mask-image:linear-gradient(90deg,transparent 0%,#000 12%,#000 88%,transparent 100%)">
      <div style="display:flex;width:max-content;animation:ly-marquee 32s linear infinite;align-items:center;gap:64px;padding:10px 0">
        <?php foreach ([...$partnerLogos, ...$partnerLogos] as $p): ?>
          <div class="hv-7" style="display:flex;align-items:center;gap:14px;opacity:0.75;filter:grayscale(1) brightness(1.2);transition:all 0.3s;cursor:default">
            <span style="font-family:var(--font-heading);font-weight:700;font-size:22px;letter-spacing:0.04em;color:#f5f5f7"><?= e($p['name']) ?></span>
            <span style="font-family:var(--font-mono);font-size:9.5px;color:var(--brand-orange);border:1px solid rgba(255,90,31,0.3);padding:2px 6px;border-radius:4px"><?= e($p['tag']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 4 Key Proof Metric Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-top:12px">
      <div class="apple-card" style="padding:22px 24px">
        <span style="font-family:var(--font-heading);font-weight:700;font-size:34px;line-height:1.1;color:#fff;display:block" class="text-gradient-orange">40+</span>
        <div style="font-size:14px;font-weight:500;color:#fff;margin-top:6px">ปีแห่งประสบการณ์</div>
        <div style="font-size:12.5px;color:var(--text-secondary);margin-top:2px">ผลิต narrow fabrics &amp; trims ตั้งแต่ปี 1978</div>
      </div>
      <div class="apple-card" style="padding:22px 24px">
        <span style="font-family:var(--font-heading);font-weight:700;font-size:34px;line-height:1.1;color:#fff;display:block">100%</span>
        <div style="font-size:14px;font-weight:500;color:#fff;margin-top:6px">One-Stop Solution</div>
        <div style="font-size:12.5px;color:var(--text-secondary);margin-top:2px">ทอ ถัก ย้อม Finishing จบในโรงงานเดียว</div>
      </div>
      <div class="apple-card" style="padding:22px 24px">
        <span style="font-family:var(--font-heading);font-weight:700;font-size:34px;line-height:1.1;color:#fff;display:block;letter-spacing:-0.03em">OEKO-TEX®</span>
        <div style="font-size:14px;font-weight:500;color:#fff;margin-top:6px">Standard 100 Certified</div>
        <div style="font-size:12.5px;color:var(--text-secondary);margin-top:2px">ปลอดภัย ไร้สารอันตราย ผ่านข้อกำหนด RSL</div>
      </div>
      <div class="apple-card" style="padding:22px 24px">
        <span style="font-family:var(--font-heading);font-weight:700;font-size:34px;line-height:1.1;color:#fff;display:block">Custom Trims</span>
        <div style="font-size:14px;font-weight:500;color:#fff;margin-top:6px">พัฒนาตามแบบแบรนด์คุณ</div>
        <div style="font-size:12.5px;color:var(--text-secondary);margin-top:2px">เลือกวัสดุ สี และรายละเอียดให้เหมาะกับงาน</div>
      </div>
    </div>

  </div>
</section>

<!-- ========================================================================
     SCENE 1: THE ANATOMY OF TRIMS (PINNED EXPLODED ASSEMBLY SCROLLYTELLING)
     ======================================================================== -->
<section id="story" data-theme="light" style="--bg-primary:#f5f5f7;--bg-secondary:#ffffff;--bg-card:rgba(255,255,255,0.8);--bg-glass:rgba(255,255,255,0.72);--text-primary:#1d1d1f;--text-secondary:#515154;--text-tertiary:#86868b;--border-light:rgba(0,0,0,0.08);--border-glass:rgba(0,0,0,0.1);--brand-orange:#ee5417;color:var(--text-primary);position:relative;background:var(--bg-primary);color:var(--text-primary)">
  <!-- Pinned Scroll Container (Height 260vh for smooth control) -->
  <div id="assembleContainer" style="height:200vh;position:relative">
    <div style="position:sticky;top:0;height:100vh;overflow:hidden;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:clamp(24px,4vh,44px);padding:96px 0 32px">
      
      <!-- Subtle Tech Grid Matrix Background -->
      <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(0,0,0,0.05) 1px,transparent 1px),linear-gradient(90deg,rgba(0,0,0,0.05) 1px,transparent 1px);background-size:64px 64px;mask-image:radial-gradient(60% 60% at 50% 50%,#000,transparent);pointer-events:none"></div>
      
      <!-- Stage Title: Smooth In & Out -->
      <div id="assembleTitle" style="position:relative;text-align:center;max-width:880px;padding:0 24px;display:flex;flex-direction:column;gap:10px;will-change:transform,opacity;z-index:2">
        <span style="font-family:var(--font-mono);font-size:clamp(18px,1.6vw,22px);letter-spacing:0.16em;color:var(--brand-orange);font-weight:700">01 — THE ANATOMY OF A GARMENT</span>
        <h2 style="font-size:clamp(28px,3.6vw,52px);line-height:1.2;font-weight:700;letter-spacing:-0.015em">
          ทุกจุดบนเสื้อผ้ากีฬาที่ต้องใช้ Trims — เรามีให้ครบ
        </h2>
        <p style="font-size:15px;color:var(--text-secondary);line-height:1.6;max-width:640px;margin:0 auto">
          คอหลัง · ขอบเอว · เทปตกแต่ง · เชือกรูด · กุ๊นคอและวงแขน · ใต้อก · ชายเสื้อและปลายแขน · อุปกรณ์กีฬา
        </p>
      </div>

      <!-- Exploded Grid of 8 Application Tiles flying into precise layout -->
      <div id="assembleGrid" style="position:relative;width:min(1240px,94vw,calc((100vh - 330px) * 2.6));display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;padding:0 20px;z-index:3">
        <?php foreach ($tiles as $i => $t): ?>
          <div data-tile="<?= $i ?>" data-dx="<?= $t['dx'] ?>" data-dy="<?= $t['dy'] ?>" data-rot="<?= $t['rot'] ?>" class="apple-card" style="position:relative;aspect-ratio:4/3;overflow:hidden;opacity:0;will-change:transform,opacity">
            <?php if ($t['img'] !== ''): ?><img class="hv-8" src="<?= e($t['img']) ?>" alt="<?= e($t['label']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;opacity:0.85;transition:transform 0.5s"><?php endif; ?>
            <?php if ($t['more']): ?><div style="position:absolute;inset:0;background:#0e0e11;background-image:linear-gradient(rgba(255,255,255,0.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.05) 1px,transparent 1px);background-size:28px 28px"><div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;gap:30px;padding-bottom:34px"><img src="data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%20fill%3D%27none%27%20stroke%3D%27%23f26b1d%27%20stroke-width%3D%271.6%27%20stroke-linecap%3D%27round%27%20stroke-linejoin%3D%27round%27%3E%3Cpath%20d%3D%27M3%2012c0-4%204-6%209-6s9%202%209%206%27%2F%3E%3Cpath%20d%3D%27M3%2012c0%202%204%203%209%203s9-1%209-3%27%2F%3E%3C%2Fsvg%3E" alt="Headband" style="width:46px;height:46px;opacity:0.75"><img src="data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%20fill%3D%27none%27%20stroke%3D%27%23f26b1d%27%20stroke-width%3D%271.6%27%20stroke-linecap%3D%27round%27%20stroke-linejoin%3D%27round%27%3E%3Crect%20x%3D%276%27%20y%3D%275%27%20width%3D%2712%27%20height%3D%2714%27%20rx%3D%274%27%2F%3E%3Cpath%20d%3D%27M6%209h12M6%2015h12%27%2F%3E%3C%2Fsvg%3E" alt="Wristband" style="width:46px;height:46px;opacity:0.75"><img src="data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2024%2024%27%20fill%3D%27none%27%20stroke%3D%27%23f26b1d%27%20stroke-width%3D%271.6%27%20stroke-linecap%3D%27round%27%20stroke-linejoin%3D%27round%27%3E%3Cpath%20d%3D%27M4%2018L14%206%27%2F%3E%3Cpath%20d%3D%27M8%2020l12-12%27%2F%3E%3Crect%20x%3D%2712%27%20y%3D%274%27%20width%3D%276%27%20height%3D%274%27%20rx%3D%271%27%20transform%3D%27rotate%2840%2015%206%29%27%2F%3E%3C%2Fsvg%3E" alt="Straps" style="width:46px;height:46px;opacity:0.75"></div></div><?php endif; ?>
            <div style="position:absolute;inset:0;background:linear-gradient(180deg,transparent 45%,rgba(8,8,10,0.92) 100%)"></div>
            <div style="position:absolute;left:18px;right:18px;bottom:16px;display:flex;flex-direction:column;gap:5px">
              <div style="display:flex;align-items:center;gap:8px"><img src="<?= e(icon_uri($t['icon'])) ?>" alt="" style="width:18px;height:18px;display:block;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.5))"><span style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;color:var(--brand-orange)"><?= e($t['label']) ?></span></div>
              <span style="font-family:var(--font-heading);font-weight:600;font-size:clamp(14px,1.4vw,19px);color:#fff;line-height:1.25"><?= e($t['title']) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Caption Bottom Glow -->
      <div id="assembleCaption" style="position:relative;text-align:center;opacity:0;padding:0 24px;will-change:opacity,transform;z-index:4">
        <p style="font-family:var(--font-heading);font-weight:600;font-size:clamp(17px,2vw,24px);color:var(--text-primary);background:rgba(255,255,255,0.88);backdrop-filter:blur(16px);border:1px solid var(--border-light);padding:10px 28px;border-radius:100px;box-shadow:0 8px 30px rgba(0,0,0,0.1);display:inline-block;white-space:nowrap;margin-bottom:24px">
          <span style="color:var(--brand-orange)">พร้อมพัฒนาตามแบบของคุณ</span>
        </p>
      </div>

    </div>
  </div>
</section>

<!-- ========================================================================
     SCENE 3: WHY BRANDS CHOOSE US (INTERACTIVE SPLIT STAGE — ZERO CLIPPING)
     ======================================================================== -->
<section id="why" aria-labelledby="why-heading" data-theme="dark" style="--why-bg:#08080a;color:#f5f5f7;background:var(--why-bg);padding:120px 0">
  
  <div style="max-width:1240px;margin:0 auto;padding:0 24px;display:flex;flex-direction:column;gap:20px;margin-bottom:56px">
    <span style="font-family:var(--font-mono);font-size:clamp(18px,1.6vw,22px);letter-spacing:0.16em;color:var(--brand-orange);font-weight:700">02 — WHY IT MATTERS TO YOU</span>
    <h2 id="why-heading" style="font-size:clamp(30px,3.6vw,50px);font-weight:700;line-height:1.2;max-width:920px">
      เพราะ Trims ชิ้นเล็ก ๆ ที่ไม่ได้คุณภาพ คือต้นทุนก้อนใหญ่ที่มองไม่เห็น
    </h2>
  </div>

  <style>
    #why { scroll-margin-top: 96px; }
    #why .why-grid { max-width:1240px; margin:0 auto; padding:0 24px; display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:0; position:relative; }
    #why .why-grid::before { content:""; position:absolute; left:24px; right:24px; top:42px; height:1px; background:linear-gradient(90deg,transparent,rgba(255,255,255,.18) 10%,rgba(255,255,255,.18) 90%,transparent); }
    #why .why-item { min-width:0; position:relative; padding:0 28px 8px 0; display:flex; flex-direction:column; align-items:flex-start; gap:14px; }
    #why .why-item + .why-item { padding-left:28px; border-left:1px solid rgba(255,255,255,.08); }
    #why .why-icon { position:relative; z-index:1; width:84px; height:84px; border-radius:50%; background:var(--why-bg); border:1px solid rgba(255,255,255,.12); box-shadow:0 0 0 8px var(--why-bg), 0 10px 30px rgba(255,90,31,.2); display:grid; place-items:center; color:#ff5a1f; margin-bottom:10px; }
    #why .why-icon svg { width:52px; height:52px; fill:none; stroke:currentColor; stroke-width:1.7; stroke-linecap:round; stroke-linejoin:round; }
    #why .why-icon .soft { stroke:rgba(255,255,255,.3); }
    #why .why-icon .fill { fill:#ff5a1f; stroke:none; }
    #why .why-number { color:#ff5a1f; font:600 12px var(--font-mono); letter-spacing:.14em; }
    #why .why-number::after { content:""; display:inline-block; width:22px; height:1px; background:#ff5a1f; margin-left:10px; vertical-align:middle; }
    #why .why-eyebrow { color:#86868b; font:600 10.5px/1.6 var(--font-mono); letter-spacing:.09em; margin-top:-8px; }
    #why .why-item h3 { font:600 clamp(20px,1.7vw,24px)/1.35 var(--font-heading); color:#f5f5f7; margin:0; }
    #why .why-item p { color:#a1a1a6; font-size:14.5px; line-height:1.8; margin:0; }
    #why .why-highlight { margin-top:4px; padding:6px 11px; border-radius:100px; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); color:#a1a1a6; font-size:12px; line-height:1.6; }
    @media(max-width:980px) { #why .why-grid { grid-template-columns:repeat(2,minmax(0,1fr)); row-gap:48px; } #why .why-grid::before { display:none; } #why .why-item:nth-child(3) { padding-left:0; border-left:0; } }
    @media(max-width:700px) { #why { padding:72px 0!important; } #why .why-grid { grid-template-columns:minmax(0,1fr); row-gap:40px; padding:0 20px; } #why .why-item { padding:0!important; border-left:0!important; } #why .why-icon { box-shadow:0 0 0 6px var(--why-bg), 0 8px 24px rgba(255,90,31,.2); } }
  </style>
  <div class="why-grid">
    <article class="why-item" aria-labelledby="why-card-1">
      <span class="why-icon" aria-hidden="true"><svg viewBox="0 0 96 96" aria-hidden="true"><path class="soft" d="M14 48h68"/><path d="M14 40h68v16H14z"/><path d="M22 40v6M30 40v10M38 40v6M46 40v10M54 40v6M62 40v10M70 40v6"/><path d="M26 28c6-4 12-4 18 0s12 4 18 0" class="soft"/><circle cx="72" cy="70" r="10"/><path d="M67 70l3.5 3.5L78 66"/></svg></span>
      <span class="why-number">01</span>
      <span class="why-eyebrow">CONSISTENCY YOU CAN SEW ON</span><h3 id="why-card-1">ไลน์ผลิตของคุณไม่สะดุด</h3><span class="why-highlight">มาตรฐานความยืดสม่ำเสมอทุกล็อต</span>
    </article>
    <article class="why-item" aria-labelledby="why-card-2">
      <span class="why-icon" aria-hidden="true"><svg viewBox="0 0 96 96" aria-hidden="true"><path d="M30 22c-8 10-14 18-14 26a14 14 0 0 0 28 0c0-8-6-16-14-26z"/><path d="M62 30c-6 8-11 14-11 20a11 11 0 0 0 22 0c0-6-5-12-11-20z" class="soft"/><rect x="14" y="70" width="16" height="10" rx="2" class="fill"/><rect x="34" y="70" width="16" height="10" rx="2" class="fill" opacity=".7"/><rect x="54" y="70" width="16" height="10" rx="2" class="fill" opacity=".45"/><rect x="74" y="70" width="8" height="10" rx="2" class="fill" opacity=".25"/><path d="M14 66h68" class="soft"/></svg></span>
      <span class="why-number">02</span>
      <span class="why-eyebrow">COLOR RIGHT, EVERY LOT</span><h3 id="why-card-2">สีตรง ตรงทุกล็อต</h3><span class="why-highlight">In-house Pantone Color Lab</span>
    </article>
    <article class="why-item" aria-labelledby="why-card-3">
      <span class="why-icon" aria-hidden="true"><svg viewBox="0 0 96 96" aria-hidden="true"><path d="M12 46l36-24 36 24"/><path d="M20 42v36h56V42"/><path d="M20 78h56" class="soft"/><rect x="28" y="56" width="10" height="10" rx="1.5"/><rect x="43" y="56" width="10" height="10" rx="1.5"/><rect x="58" y="56" width="10" height="10" rx="1.5"/><path d="M33 66v12M48 66v12M63 66v12" class="soft"/><circle cx="48" cy="34" r="3" class="fill"/></svg></span>
      <span class="why-number">03</span>
      <span class="why-eyebrow">ONE SUPPLIER, ZERO COORDINATION</span><h3 id="why-card-3">จบทุกขั้นตอนในที่เดียว</h3><span class="why-highlight">ครบวงจรใต้หลังคาเดียวในกรุงเทพฯ</span>
    </article>
    <article class="why-item" aria-labelledby="why-card-4">
      <span class="why-icon" aria-hidden="true"><svg viewBox="0 0 96 96" aria-hidden="true"><path d="M18 74L22 60 56 26l10 10-34 34z"/><path d="M50 32l10 10" class="soft"/><path d="M22 60l10 10"/><path d="M62 18c6-4 12-4 16 0s4 10 0 14" class="soft"/><path d="M72 46h8M74 54h10M70 62h8" class="soft"/><path d="M80 24l6-6"/><circle cx="86" cy="18" r="2.5" class="fill"/></svg></span>
      <span class="why-number">04</span>
      <span class="why-eyebrow">FROM SKETCH TO SAMPLE, FAST</span><h3 id="why-card-4">พัฒนาของใหม่ได้เร็ว</h3><span class="why-highlight">R&amp;D ร่วมกับดีไซเนอร์แบรนด์</span>
    </article>
  </div>
</section>

<!-- ========================================================================
     SCENE 2: ONE-STOP PROCESS (THE MACRO JOURNEY & GLOWING THREAD)
     ======================================================================== -->
<section id="process" style="position:relative;background:var(--bg-primary);color:#fff">
  <div id="processContainer" style="height:330vh;position:relative">
    <div style="position:sticky;top:0;height:100vh;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;padding:110px 0 40px">
      
      <!-- Macro Background Process Images (Full Bleed, ZERO clipping) -->
      <div style="position:absolute;inset:0;z-index:1;overflow:hidden">
        <?php foreach ($steps as $i => $s): ?>
          <div data-step-bg="<?= $i ?>" style="position:absolute;inset:0;opacity:0;transition:opacity 0.6s cubic-bezier(0.16,1,0.3,1);will-change:opacity">
            <img src="<?= e(img_src($s['img'])) ?>" alt="<?= e($s['title']) ?>" style="width:100%;height:100%;object-fit:cover;object-position:center;filter:brightness(0.55) saturate(0.9)">
            <div style="position:absolute;inset:0;background:linear-gradient(90deg, rgba(8,8,10,0.88) 0%, rgba(8,8,10,0.6) 55%, rgba(8,8,10,0.35) 100%)"></div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Top Header Indicator -->
      <div style="position:relative;z-index:2;max-width:1240px;margin:0 auto;padding:0 28px;width:100%">
        <div style="display:inline-flex;align-items:center;gap:10px;font-family:var(--font-mono);font-size:clamp(18px,1.6vw,22px);letter-spacing:0.16em;color:var(--brand-orange);font-weight:700;margin-bottom:8px">
          <span style="width:6px;height:6px;border-radius:50%;background:var(--brand-orange)"></span>
          03 — PROCESS PIPELINE
        </div>
        <h2 style="font-size:clamp(24px,2.8vw,38px);font-weight:600;line-height:1.25;max-width:760px">
          จากเส้นด้ายสู่ชิ้นงานสำเร็จ — ภายใต้การควบคุมทุกขั้นตอน
        </h2>
      </div>

      <!-- Middle: Active Step Narrative Stage -->
      <div style="position:relative;z-index:2;max-width:1240px;margin:0 auto;padding:0 28px;width:100%;flex:1;display:flex;align-items:center">
        <div style="position:relative;min-height:240px;width:100%;max-width:720px">
          <?php foreach ($steps as $i => $s): ?>
            <div data-step-text="<?= $i ?>" style="position:absolute;left:0;top:0;bottom:0;display:flex;flex-direction:column;justify-content:center;gap:14px;opacity:0;transform:translateY(30px);will-change:transform,opacity;pointer-events:none">
              <span style="font-family:var(--font-mono);font-weight:700;font-size:clamp(56px,8vw,100px);line-height:1;color:rgba(255,90,31,0.22);letter-spacing:-0.04em"><?= e($s['n']) ?></span>
              <h3 style="font-size:clamp(28px,3.6vw,48px);font-weight:700;line-height:1.2;color:#fff;margin-top:-10px"><?= e($s['title']) ?></h3>
              <p style="font-size:clamp(16px,1.3vw,19px);line-height:1.7;color:var(--text-secondary)"><?= e($s['desc']) ?></p>
              <div style="display:flex;gap:10px;margin-top:6px">
                <span style="font-family:var(--font-mono);font-size:11px;color:var(--brand-orange);background:rgba(255,90,31,0.12);padding:4px 10px;border-radius:6px;border:1px solid rgba(255,90,31,0.25)"><?= e($s['tag']) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Bottom: Interactive Laser Timeline Tracer -->
      <div style="position:relative;z-index:2;max-width:1240px;margin:0 auto;padding:0 28px;width:100%;display:flex;flex-direction:column;gap:14px">
        <!-- Glowing Line Bar -->
        <div style="position:relative;height:3px;background:rgba(255,255,255,0.12);border-radius:2px;overflow:hidden">
          <div id="stepLineProgress" style="position:absolute;left:0;top:0;bottom:0;width:100%;background:linear-gradient(90deg,var(--brand-orange),var(--brand-amber));transform-origin:left;transform:scaleX(0);will-change:transform"></div>
        </div>
        <!-- 6 Step Nodes -->
        <div style="display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:8px">
          <?php foreach ($steps as $i => $s): ?>
            <div data-step-node="<?= $i ?>" style="display:flex;flex-direction:column;gap:3px;opacity:0.35;transition:opacity 0.3s">
              <span style="font-family:var(--font-mono);font-size:11px;font-weight:600;color:var(--brand-orange)"><?= e($s['n']) ?></span>
              <span style="font-size:12.5px;color:#fff;font-weight:500;line-height:1.2"><?= e($s['short']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ========================================================================
     SCENE 4: INTERACTIVE PRODUCT SPECIMEN EXPLORER
     ======================================================================== -->
<section id="specimens" data-theme="light" style="--bg-primary:#f5f5f7;--bg-secondary:#ffffff;--bg-card:rgba(255,255,255,0.8);--bg-glass:rgba(255,255,255,0.72);--text-primary:#1d1d1f;--text-secondary:#515154;--text-tertiary:#86868b;--border-light:rgba(0,0,0,0.08);--border-glass:rgba(0,0,0,0.1);--brand-orange:#ee5417;color:var(--text-primary);background:var(--bg-primary);padding:120px 0">
  <div style="max-width:1240px;margin:0 auto;padding:0 24px;display:flex;flex-direction:column;gap:48px">
    
    <!-- Section Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:24px;flex-wrap:wrap">
      <div style="display:flex;flex-direction:column;gap:12px;max-width:920px">
        <span style="font-family:var(--font-mono);font-size:clamp(18px,1.6vw,22px);letter-spacing:0.16em;color:var(--brand-orange);font-weight:700">04 — PRODUCT SPECIMENS</span>
        <h2 style="font-size:clamp(30px,3.6vw,50px);font-weight:700;line-height:1.2">
          Narrow Fabric &amp; Trims ครบทุกประเภท
        </h2>
        <p style="font-size:16.5px;line-height:1.7;color:var(--text-secondary)">
          ออกแบบและพัฒนาเฉพาะสำหรับ activewear, sportswear, compression wear และ high-fashion
        </p>
      </div>
      <a class="hv-9" href="#contact" style="font-family:var(--font-heading);font-weight:600;font-size:14px;color:var(--text-primary);border:1px solid var(--border-glass);padding:12px 24px;border-radius:100px;backdrop-filter:blur(12px);transition:all 0.3s">
        ขอรับแคตตาล็อก &amp; ตัวอย่างสินค้า →
      </a>
    </div>

    <!-- Product Specimen Grid (6 Comprehensive Categories) -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:24px">
      <?php foreach ($productCards as $p): ?>
        <div class="apple-card" style="overflow:hidden;display:flex;flex-direction:column;height:100%">
          <div style="position:relative;aspect-ratio:16/10;overflow:hidden;background:#e8e8ed">
            <?php if ($p['img'] !== ''): ?><img class="hv-10" src="<?= e($p['img']) ?>" alt="<?= e($p['title']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.6s cubic-bezier(0.16,1,0.3,1)"><?php endif; ?>
            <div style="position:absolute;top:14px;left:14px">
              <span style="font-family:var(--font-mono);font-size:10px;font-weight:600;letter-spacing:0.1em;background:rgba(8,8,10,0.8);color:var(--brand-orange);border:1px solid rgba(255,90,31,0.3);padding:4px 10px;border-radius:100px;backdrop-filter:blur(8px)"><?= e($p['code']) ?></span>
            </div>
          </div>
          <div style="padding:28px 24px;display:flex;flex-direction:column;gap:12px;flex:1">
            <div style="display:flex;flex-direction:column;gap:4px">
              <span style="font-family:var(--font-mono);font-size:11px;color:var(--brand-orange);letter-spacing:0.12em"><?= e($p['label']) ?></span>
              <h3 style="font-size:20px;font-weight:600;line-height:1.3;color:var(--text-primary)"><?= e($p['title']) ?></h3>
            </div>
            <p style="font-size:14.5px;line-height:1.65;color:var(--text-secondary);flex:1"><?= e($p['desc']) ?></p>
            <div style="padding-top:14px;border-top:1px solid var(--border-light);display:flex;justify-content:flex-end;align-items:center;font-size:13.5px">
              <a class="hv-11" href="#contact" style="color:var(--brand-orange);font-weight:600">สั่งผลิต →</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- ========================================================================
     SCENE 5: R&D SERVICE — FROM SKETCH TO REALITY
     ======================================================================== -->
<section id="rnd" style="background:var(--bg-primary);color:#fff;padding:120px 0 100px;position:relative;overflow:hidden">
  <div style="position:absolute;width:44vw;height:44vw;right:-12vw;top:-8vw;border-radius:50%;background:radial-gradient(circle,rgba(255,90,31,0.10),transparent 65%);filter:blur(80px);pointer-events:none"></div>

  <div style="position:relative;max-width:1240px;margin:0 auto;padding:0 24px;display:flex;flex-direction:column;gap:56px">

    <!-- Header + Hero photo -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:40px;align-items:center">
      <div style="display:flex;flex-direction:column;gap:22px">
        <span style="font-family:var(--font-mono);font-size:clamp(18px,1.6vw,22px);letter-spacing:0.16em;color:var(--brand-orange);font-weight:700">05 — R&amp;D SERVICE · FROM SKETCH TO REALITY</span>
        <h2 style="font-size:clamp(30px,3.6vw,50px);font-weight:700;line-height:1.2;letter-spacing:-0.015em">
          เราพัฒนา Trims ให้เข้ากับการใช้งาน
        </h2>
        <p style="font-size:16.5px;line-height:1.75;color:var(--text-secondary);max-width:560px;text-wrap:pretty">
          ทีม R&amp;D ของเราทำงานร่วมกับดีไซเนอร์และ Product Developer ตั้งแต่สเก็ตช์แรก จนได้ตัวอย่างที่ใส่จริง ทดสอบจริง และพร้อมเข้าสู่การผลิตจริง
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          <span style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.08em;padding:8px 14px;border-radius:100px;border:1px solid var(--border-light);color:#fff;background:rgba(255,255,255,0.03)">STRETCH %</span>
          <span style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.08em;padding:8px 14px;border-radius:100px;border:1px solid var(--border-light);color:#fff;background:rgba(255,255,255,0.03)">RECOVERY</span>
          <span style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.08em;padding:8px 14px;border-radius:100px;border:1px solid var(--border-light);color:#fff;background:rgba(255,255,255,0.03)">COMPRESSION</span>
          <span style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.08em;padding:8px 14px;border-radius:100px;border:1px solid var(--border-light);color:#fff;background:rgba(255,255,255,0.03)">WIDTH &amp; HAND-FEEL</span>
          <span style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.08em;padding:8px 14px;border-radius:100px;border:1px solid var(--border-light);color:#fff;background:rgba(255,255,255,0.03)">GRIP</span>
          <span style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.08em;padding:8px 14px;border-radius:100px;border:1px solid var(--border-light);color:#fff;background:rgba(255,255,255,0.03)">COLOR &amp; BRANDING</span>
        </div>
      </div>
      <div style="position:relative;aspect-ratio:4/3;border-radius:22px;overflow:hidden;border:1px solid var(--border-glass);background:#14141a">
        <img src="assets/img/rnd-team.png" alt="LY R&amp;D team developing trims with a designer" style="width:100%;height:100%;object-fit:cover;display:block">
        <div style="position:absolute;inset:0;background:linear-gradient(180deg,transparent 55%,rgba(8,8,10,0.85) 100%)"></div>
        <div style="position:absolute;left:20px;right:20px;bottom:18px;display:flex;flex-direction:column;gap:3px">
          <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;color:var(--brand-orange)">IN-HOUSE R&amp;D TEAM</span>
          <span style="font-family:var(--font-heading);font-weight:600;font-size:17px;color:#fff">ทีมพัฒนาคุยกับดีไซเนอร์ของคุณโดยตรง</span>
        </div>
      </div>
    </div>

    <!-- 4-Stage Development Path -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px">
      <div class="apple-card" style="padding:26px 24px;display:flex;flex-direction:column;gap:14px;position:relative">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-family:var(--font-mono);font-size:12px;font-weight:700;color:var(--brand-orange)">01</span>
          <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;color:var(--text-tertiary)">SKETCH</span>
        </div>
        <span style="font-family:var(--font-heading);font-weight:600;font-size:19px;color:#fff;line-height:1.3">รับโจทย์จากการใช้งาน</span>
        <span style="font-size:14px;line-height:1.65;color:var(--text-secondary);text-wrap:pretty">ส่งมาได้ทั้งตัวอย่างจริง ไฟล์ภาพ สเก็ตช์ tech pack หรือแค่โจทย์การใช้งาน — เราแปลงเป็นสเปกทางเทคนิคให้</span>
      </div>
      <div class="apple-card" style="padding:26px 24px;display:flex;flex-direction:column;gap:14px;position:relative">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-family:var(--font-mono);font-size:12px;font-weight:700;color:var(--brand-orange)">02</span>
          <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;color:var(--text-tertiary)">ENGINEER</span>
        </div>
        <span style="font-family:var(--font-heading);font-weight:600;font-size:19px;color:#fff;line-height:1.3">ออกแบบโครงสร้างและวัสดุ</span>
        <span style="font-size:14px;line-height:1.65;color:var(--text-secondary);text-wrap:pretty">ออกแบบโครงสร้างและเลือกวัสดุให้ตรงกับ requirements ของการใช้งาน อาทิ แรงดึง การคืนตัว และสัมผัสที่การใช้งานนั้นต้องการ</span>
      </div>
      <div class="apple-card" style="padding:26px 24px;display:flex;flex-direction:column;gap:14px;position:relative">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-family:var(--font-mono);font-size:12px;font-weight:700;color:var(--brand-orange)">03</span>
          <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;color:var(--text-tertiary)">SAMPLE &amp; TEST</span>
        </div>
        <span style="font-family:var(--font-heading);font-weight:600;font-size:19px;color:#fff;line-height:1.3">ตัวอย่างจริง ทดสอบจริง</span>
        <span style="font-size:14px;line-height:1.65;color:var(--text-secondary);text-wrap:pretty">ขึ้นตัวอย่างพร้อม Lab-dip สี ทดสอบแรงดึง การคืนตัว และความคงทน ปรับจนดีไซเนอร์ลองใส่แล้วพอใจ</span>
      </div>
      <div class="apple-card" style="padding:26px 24px;display:flex;flex-direction:column;gap:14px;position:relative">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-family:var(--font-mono);font-size:12px;font-weight:700;color:var(--brand-orange)">04</span>
          <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;color:var(--text-tertiary)">PRODUCTION</span>
        </div>
        <span style="font-family:var(--font-heading);font-weight:600;font-size:19px;color:#fff;line-height:1.3">ขึ้นผลิตจริง พร้อมส่งมอบ</span>
        <span style="font-size:14px;line-height:1.65;color:var(--text-secondary);text-wrap:pretty">ล็อกสเปกที่ผ่านการอนุมัติเป็นรหัสสินค้าของคุณ เข้าสู่การผลิตจริงตามกำหนด และส่งมอบในคุณภาพเดียวกันทุกล็อต</span>
      </div>
    </div>

    <a class="hv-12" href="#contact" style="align-self:flex-start;background:linear-gradient(135deg,var(--brand-orange),var(--brand-orange-light));color:#fff;padding:15px 30px;border-radius:100px;font-weight:600;font-size:15px;box-shadow:0 6px 20px var(--brand-orange-glow)">
      เริ่มต้นพัฒนาชิ้นงานกับเรา →
    </a>
  </div>
</section>

<!-- ========================================================================
     SCENE 6: INTERACTIVE PANTONE COLOR LAB
     ======================================================================== -->
<section id="colorlab" style="background:var(--bg-secondary);padding:120px 0;position:relative;overflow:hidden">
  
  <div style="position:absolute;width:50vw;height:50vw;left:-10vw;top:10vw;border-radius:50%;background:radial-gradient(circle,rgba(255,90,31,0.12),transparent 65%);filter:blur(80px);pointer-events:none"></div>

  <div style="position:relative;max-width:1240px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:64px;align-items:center">
    
    <!-- Left Column: Story & Lab Precision -->
    <div style="display:flex;flex-direction:column;gap:24px">
      <span style="font-family:var(--font-mono);font-size:clamp(18px,1.6vw,22px);letter-spacing:0.16em;color:var(--brand-orange);font-weight:700">06 — IN-HOUSE DYEING &amp; PANTONE LAB</span>
      <h2 style="font-size:clamp(30px,3.6vw,50px);font-weight:700;line-height:1.2">
        โรงย้อมมาตรฐาน<br>สีตรงแม่นยำทุกล็อต
      </h2>
      <p style="font-size:16.5px;line-height:1.75;color:var(--text-secondary)">
        ระบบจ่ายสีย้อมอัตโนมัติและห้องแล็บเทียบสีมาตรฐานสากล <strong style="color:#fff">เทียบสีได้จาก Pantone TCX, Lab-dip หรือชิ้นงานตัวอย่างจริง</strong> ควบคุมเฉดให้ตรงกันตั้งแต่ตัวอย่างแรกจนถึงการผลิตซ้ำ พร้อมรับประกันความคงทนของสีต่อการซัก เหงื่อ และแสง UV
      </p>

      <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:8px">
        <div style="background:rgba(255,255,255,0.03);padding:16px 18px;border-radius:12px;border:1px solid var(--border-light);display:flex;flex-direction:column;gap:4px">
          <span style="font-family:var(--font-mono);font-size:22px;font-weight:700;color:#fff;letter-spacing:-0.02em">ΔE&lt;0.5</span>
          <span style="font-size:12.5px;color:var(--text-secondary)">ค่าความต่างสีที่ยอมรับ</span>
        </div>
        <div style="background:rgba(255,255,255,0.03);padding:16px 18px;border-radius:12px;border:1px solid var(--border-light);display:flex;flex-direction:column;gap:4px">
          <span style="font-family:var(--font-mono);font-size:22px;font-weight:700;color:#fff;letter-spacing:-0.02em">TCX</span>
          <span style="font-size:12.5px;color:var(--text-secondary)">รองรับรหัส Pantone ทั้งระบบ</span>
        </div>
        <div style="background:rgba(255,255,255,0.03);padding:16px 18px;border-radius:12px;border:1px solid var(--border-light);display:flex;flex-direction:column;gap:4px">
          <span style="font-family:var(--font-mono);font-size:22px;font-weight:700;color:#fff;letter-spacing:-0.02em">4–5</span>
          <span style="font-size:12.5px;color:var(--text-secondary)">เกรดความคงทนสี ซัก/เหงื่อ/แสง</span>
        </div>
      </div>

      <a class="hv-12" href="#contact" style="align-self:flex-start;background:linear-gradient(135deg,var(--brand-orange),var(--brand-orange-light));color:#fff;padding:15px 30px;border-radius:100px;font-weight:600;font-size:15px;box-shadow:0 6px 20px var(--brand-orange-glow);margin-top:6px">
        ส่งรหัสสีให้เราเทียบ →
      </a>
    </div>

    <!-- Right Column: Interactive Pantone Swatch & Trim Preview Simulator -->
    <div class="apple-card" style="padding:32px;display:flex;flex-direction:column;gap:24px">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <span style="font-family:var(--font-mono);font-size:11.5px;color:var(--brand-orange);letter-spacing:0.12em">COLORWAY SPECIMEN</span>
        <span style="font-family:var(--font-mono);font-size:11px;color:var(--text-tertiary)">ΔE &lt; 0.5 TOLERANCE</span>
      </div>

      <!-- Trim Simulation Visual -->
      <div style="position:relative;height:200px;border-radius:16px;overflow:hidden;background:#0d0d10;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-light)">
        <!-- Elastic Ribbon Vector -->
        <div id="swatchRibbon" style="width:85%;height:54px;border-radius:8px;background:<?= e($activeSwatch['hex']) ?>;box-shadow:0 12px 36px <?= e($activeSwatch['glow']) ?>;transition:background 0.4s cubic-bezier(0.16,1,0.3,1),box-shadow 0.4s;display:flex;align-items:center;justify-content:space-between;padding:0 24px;border:1px solid rgba(255,255,255,0.25)">
          <span style="font-family:var(--font-mono);font-size:11px;font-weight:600;color:<?= e($activeSwatch['text']) ?>;letter-spacing:0.1em" data-swatch-text>L.Y. PRECISION WEAVE</span>
          <span style="font-family:var(--font-mono);font-size:11px;font-weight:500;color:<?= e($activeSwatch['text']) ?>" data-swatch-text data-swatch-code><?= e($activeSwatch['code']) ?></span>
        </div>
      </div>

      <!-- Active Swatch Data -->
      <div style="display:flex;justify-content:space-between;align-items:flex-end">
        <div>
          <div style="font-family:var(--font-mono);font-size:12px;color:var(--text-tertiary)">SELECTED PANTONE®</div>
          <div style="font-family:var(--font-heading);font-weight:600;font-size:20px;color:#fff;margin-top:2px" data-swatch-name><?= e($activeSwatch['name']) ?></div>
        </div>
        <div style="font-family:var(--font-mono);font-size:13px;color:var(--brand-orange)" data-swatch-code><?= e($activeSwatch['code']) ?></div>
      </div>

      <!-- 5 Swatch Selectors -->
      <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px">
        <?php foreach ($swatches as $i => $sw): ?>
          <button type="button" class="hv-10" data-swatch="<?= e(json_encode($sw, JSON_UNESCAPED_UNICODE)) ?>" aria-label="<?= e($sw['name']) ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>" style="height:44px;border-radius:10px;background:<?= e($sw['hex']) ?>;border:2px solid <?= $i === 0 ? '#fff' : 'transparent' ?>;cursor:pointer;transition:transform 0.2s,border-color 0.2s;box-shadow:0 4px 10px rgba(0,0,0,0.3)"></button>
        <?php endforeach; ?>
      </div>

    </div>

  </div>

  <!-- Lab photo strip: real equipment behind the color guarantee -->
  <div style="position:relative;max-width:1240px;margin:64px auto 0;padding:0 24px;display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px">
    <div style="position:relative;aspect-ratio:16/10;border-radius:18px;overflow:hidden;border:1px solid var(--border-glass);background:#14141a">
      <img class="hv-13" src="assets/img/dye-dispenser.png" alt="Automated dye dispenser" style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.6s cubic-bezier(0.16,1,0.3,1)">
      <div style="position:absolute;inset:0;background:linear-gradient(180deg,transparent 55%,rgba(8,8,10,0.85) 100%)"></div>
      <div style="position:absolute;left:18px;right:18px;bottom:16px;display:flex;flex-direction:column;gap:3px">
        <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;color:var(--brand-orange)">AUTO DYE DISPENSER</span>
        <span style="font-family:var(--font-heading);font-weight:600;font-size:16px;color:#fff">ระบบจ่ายสีย้อมอัตโนมัติ</span>
      </div>
    </div>
    <div style="position:relative;aspect-ratio:16/10;border-radius:18px;overflow:hidden;border:1px solid var(--border-glass);background:#14141a">
      <img class="hv-13" src="assets/img/spectrophotometer.png" alt="Spectrophotometer color measurement" style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.6s cubic-bezier(0.16,1,0.3,1)">
      <div style="position:absolute;inset:0;background:linear-gradient(180deg,transparent 55%,rgba(8,8,10,0.85) 100%)"></div>
      <div style="position:absolute;left:18px;right:18px;bottom:16px;display:flex;flex-direction:column;gap:3px">
        <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;color:var(--brand-orange)">SPECTROPHOTOMETER</span>
        <span style="font-family:var(--font-heading);font-weight:600;font-size:16px;color:#fff">วัดค่าสีด้วยเครื่อง ไม่ใช้สายตา</span>
      </div>
    </div>
    <div style="position:relative;aspect-ratio:16/10;border-radius:18px;overflow:hidden;border:1px solid var(--border-glass);background:#14141a">
      <img class="hv-13" src="assets/img/lab-dip.png" alt="Lab-dip compared to standard" style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.6s cubic-bezier(0.16,1,0.3,1)">
      <div style="position:absolute;inset:0;background:linear-gradient(180deg,transparent 55%,rgba(8,8,10,0.85) 100%)"></div>
      <div style="position:absolute;left:18px;right:18px;bottom:16px;display:flex;flex-direction:column;gap:3px">
        <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;color:var(--brand-orange)">LAB-DIP vs STANDARD</span>
        <span style="font-family:var(--font-heading);font-weight:600;font-size:16px;color:#fff">เทียบ Lab-dip กับมาตรฐานก่อนขึ้นไลน์</span>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     SCENE 6: INSPIRATION HUB & GALLERY WALL
     ======================================================================== -->
<section id="gallery" data-theme="light" style="--bg-primary:#f5f5f7;--bg-secondary:#ffffff;--bg-card:rgba(255,255,255,0.8);--bg-glass:rgba(255,255,255,0.72);--text-primary:#1d1d1f;--text-secondary:#515154;--text-tertiary:#86868b;--border-light:rgba(0,0,0,0.08);--border-glass:rgba(0,0,0,0.1);--brand-orange:#ee5417;color:var(--text-primary);background:var(--bg-primary);padding:120px 0">
  <div style="max-width:1240px;margin:0 auto;padding:0 24px;display:flex;flex-direction:column;gap:44px">
    
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:24px;flex-wrap:wrap">
      <div style="display:flex;flex-direction:column;gap:12px;max-width:640px">
        <span style="font-family:var(--font-mono);font-size:clamp(18px,1.6vw,22px);letter-spacing:0.16em;color:var(--brand-orange);font-weight:700">07 — INSPIRATION HUB</span>
        <h2 style="font-size:clamp(30px,3.6vw,50px);font-weight:700;line-height:1.2">
          ตัวอย่างสินค้าของเรา
        </h2>
        <p style="font-size:16.5px;line-height:1.7;color:var(--text-secondary)">
          ทุกชิ้นมีรหัสอ้างอิงเฉพาะ สั่งพัฒนาต่อยอด หรือขอตัวอย่างจริงเพื่อเทียบสัมผัสได้ทันที
        </p>
      </div>
      <a class="hv-14" href="<?= e(SITE_INSPIRATION_URL) ?>" target="_blank" rel="noopener" style="font-family:var(--font-heading);font-weight:600;font-size:14px;color:var(--text-primary);border:1px solid var(--border-glass);padding:12px 24px;border-radius:100px;backdrop-filter:blur(12px)">
        ดูแคตตาล็อกทั้งหมด →
      </a>
    </div>

    <!-- Gallery Grid with Clean Image Slots & Realistic Product Codes -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px">
      <?php foreach ($galleryItems as $g): ?>
        <div class="apple-card" style="padding:14px;display:flex;flex-direction:column;gap:12px">
          <div style="aspect-ratio:1;border-radius:14px;overflow:hidden;background:#e8e8ed;position:relative">
            <img src="<?= e(img_src($g['img'])) ?>" alt="<?= e($g['code'] . ' ' . $g['type']) ?>" style="width:100%;height:100%;object-fit:cover;display:block">
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:0 4px">
            <span style="font-family:var(--font-mono);font-size:13px;font-weight:600;letter-spacing:0.06em;color:var(--text-primary)"><?= e($g['code']) ?></span>
            <span style="font-family:var(--font-mono);font-size:10.5px;color:var(--brand-orange)"><?= e($g['type']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- ========================================================================
     SCENE 7: FREQUENTLY ASKED QUESTIONS (APPLE SPRING ACCORDION)
     ======================================================================== -->
<section id="faq" data-theme="light" style="--bg-primary:#f5f5f7;--bg-secondary:#ffffff;--bg-card:rgba(255,255,255,0.8);--bg-glass:rgba(255,255,255,0.72);--text-primary:#1d1d1f;--text-secondary:#515154;--text-tertiary:#86868b;--border-light:rgba(0,0,0,0.08);--border-glass:rgba(0,0,0,0.1);--brand-orange:#ee5417;color:var(--text-primary);background:var(--bg-secondary);padding:120px 0">
  <div style="max-width:1240px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:56px;align-items:start">
    
    <!-- Left Column: FAQ Intro -->
    <div style="display:flex;flex-direction:column;gap:18px">
      <span style="font-family:var(--font-mono);font-size:clamp(18px,1.6vw,22px);letter-spacing:0.16em;color:var(--brand-orange);font-weight:700">08 — FAQ &amp; SPECIFICATION</span>
      <h2 style="font-size:clamp(30px,3.6vw,50px);font-weight:700;line-height:1.2">
        คำถามที่พบบ่อย
      </h2>
      <p style="font-size:16px;line-height:1.75;color:var(--text-secondary)">
        ข้อมูลเกี่ยวกับขั้นต่ำในการผลิต (MOQ), กระบวนการทำตัวอย่าง, มาตรฐานการเทียบสี และระยะเวลาจัดส่ง — ทีมฝ่ายขายพร้อมตอบทุกข้อสงสัยภายใน 24 ชั่วโมงทำการ
      </p>
      <a class="hv-15" href="mailto:sales@lyindustries.com" style="align-self:flex-start;background:rgba(0,0,0,0.04);border:1px solid var(--border-glass);color:var(--text-primary);padding:12px 24px;border-radius:100px;font-size:14px;font-weight:500;margin-top:6px;backdrop-filter:blur(12px)">
        สอบถามคำถามอื่นเพิ่มเติม →
      </a>
    </div>

    <!-- Right Column: Accordion List -->
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach ($faqList as $i => $f): ?>
        <div class="apple-card faq-item<?= $i === 0 ? ' is-open' : '' ?>" style="padding:0 24px;overflow:hidden">
          <button type="button" class="hv-4" data-faq-toggle aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" style="width:100%;background:none;border:none;padding:24px 0;display:flex;justify-content:space-between;align-items:center;gap:16px;text-align:left;cursor:pointer;color:var(--text-primary)">
            <span style="font-family:var(--font-heading);font-weight:500;font-size:17px;line-height:1.4"><?= e($f['q']) ?></span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(0,0,0,0.04);border:1px solid var(--border-light);display:grid;place-items:center;font-family:var(--font-mono);font-size:16px;color:var(--brand-orange);flex-shrink:0" class="faq-icon"><?= $i === 0 ? '−' : '+' ?></span>
          </button>
            <div class="faq-answer" style="padding:0 0 24px;font-size:15px;line-height:1.75;color:var(--text-secondary);border-top:1px solid var(--border-light);padding-top:16px">
              <?= e($f['a']) ?>
            </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>



<!-- ========================================================================
     SCENE 8: HIGH-IMPACT CALL TO ACTION & FOOTER
     ======================================================================== -->
<section id="contact" style="background:var(--bg-primary);position:relative;padding:120px 0 60px;overflow:hidden">
  
  <!-- Glowing Warm Ambient Backdrop -->
  <div style="position:absolute;bottom:-15vw;right:-15vw;width:60vw;height:60vw;border-radius:50%;background:radial-gradient(circle,rgba(255,90,31,0.22),transparent 70%);filter:blur(90px);pointer-events:none"></div>

  <div style="position:relative;max-width:1240px;margin:0 auto;padding:0 24px;display:flex;flex-direction:column;gap:80px">
    
    <!-- CTA Card -->
    <div class="apple-card" style="padding:60px 48px;display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:48px;align-items:center;background:linear-gradient(135deg,rgba(24,24,30,0.85) 0%,rgba(14,14,18,0.9) 100%)">
      <div style="display:flex;flex-direction:column;gap:18px">
        <span style="font-family:var(--font-mono);font-size:clamp(18px,1.6vw,22px);letter-spacing:0.16em;color:var(--brand-orange);font-weight:700">GET IN TOUCH</span>
        <h2 style="font-size:clamp(32px,4vw,54px);font-weight:700;line-height:1.18;color:#fff">
          เริ่มงาน Trims กับเรา
        </h2>
        <p style="font-size:16.5px;line-height:1.7;color:var(--text-secondary)">
          ส่งตัวอย่าง แบบร่าง หรือสเปกที่คุณต้องการมาให้เรา — ทีมงานฝ่ายเทคนิคและฝ่าย Support พร้อมประเมินและตอบกลับภายใน 24 ชั่วโมงทำการ
        </p>
      </div>

      <!-- Contact Actions -->
      <div style="display:flex;flex-direction:column;gap:12px;width:100%;max-width:440px;justify-self:end">
        <a class="hv-12" href="mailto:sales@lyindustries.com" style="background:linear-gradient(135deg,var(--brand-orange),var(--brand-orange-light));color:#fff;padding:18px 26px;border-radius:14px;font-weight:600;font-size:16px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 8px 24px var(--brand-orange-glow);transition:transform 0.2s">
          <span>ขอใบเสนอราคา / Request a Quote</span>
          <span style="font-size:18px">→</span>
        </a>
        <a class="hv-16" href="https://line.me/R/ti/p/@lyindustries" target="_blank" rel="noopener" style="background:rgba(255,255,255,0.04);border:1px solid var(--border-glass);color:#fff;padding:18px 26px;border-radius:14px;font-weight:600;font-size:16px;display:flex;justify-content:space-between;align-items:center;backdrop-filter:blur(12px);transition:background 0.2s">
          <span>LINE Official: @lyindustries</span>
          <span style="font-size:18px">→</span>
        </a>
        <a class="hv-16" href="tel:025170768" style="background:rgba(255,255,255,0.04);border:1px solid var(--border-glass);color:#fff;padding:18px 26px;border-radius:14px;font-weight:600;font-size:16px;display:flex;justify-content:space-between;align-items:center;backdrop-filter:blur(12px);transition:background 0.2s">
          <span>โทร: 02-517-0768 ต่อ 120, 121</span>
          <span style="font-size:18px">→</span>
        </a>
      </div>
    </div>

    <!-- Editorial Footer -->
    <footer style="border-top:1px solid var(--border-light);padding-top:48px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:36px;font-size:14px;line-height:1.75;color:var(--text-secondary)">
      
      <div style="display:flex;flex-direction:column;gap:10px">
        <span style="font-family:var(--font-heading);font-weight:700;font-size:16px;letter-spacing:0.06em;color:#fff">L.Y. INDUSTRIES CO., LTD.</span>
        <span>บริษัท แอล วาย อินดัสตรีย์ จำกัด<br>124 ซอยรามอินทรา 109 ถนนพระยาสุเรนทร์ แขวงบางชัน<br>เขตคลองสามวา กรุงเทพฯ 10510</span>
      </div>

      <div style="display:flex;flex-direction:column;gap:10px">
        <span style="font-family:var(--font-mono);font-size:11.5px;letter-spacing:0.16em;color:var(--brand-orange)">OPERATING HOURS</span>
        <span>จันทร์ – ศุกร์: 08:30 – 17:30 น.<br>เสาร์: 08:30 – 12:00 น.<br>sales@lyindustries.com</span>
      </div>

      <div style="display:flex;flex-direction:column;gap:10px">
        <span style="font-family:var(--font-mono);font-size:11.5px;letter-spacing:0.16em;color:var(--brand-orange)">SITEMAP</span>
        <div style="display:flex;flex-direction:column;gap:4px">
          <a class="hv-1" href="#hero" style="color:var(--text-secondary)">หน้าแรก</a>
          <a class="hv-1" href="catalog.php" style="color:var(--text-secondary)">แคตาล็อกสินค้า</a>
          <a class="hv-1" href="https://www.trimrite.com/" target="_blank" rel="noopener" style="color:var(--text-secondary)">TRIMRITE®</a>
          <a class="hv-1" href="about.php" style="color:var(--text-secondary)">เกี่ยวกับเรา</a>
          <a class="hv-1" href="contact.php" style="color:var(--text-secondary)">ติดต่อเรา</a>
          <a class="hv-1" href="#faq" style="color:var(--text-secondary)">คำถามพบบ่อย</a>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:10px">
        <span style="font-family:var(--font-mono);font-size:11.5px;letter-spacing:0.16em;color:var(--brand-orange)">QUALITY STANDARDS</span>
        <span>OEKO-TEX® Standard 100 Certified<br>สอดคล้องข้อกำหนด RSL ของแบรนด์กีฬาระดับโลก</span>
      </div>

    </footer>

    <!-- Copyright & Disclaimer -->
    <div style="border-top:1px solid rgba(255,255,255,0.05);padding-top:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;font-family:var(--font-mono);font-size:11.5px;color:var(--text-tertiary)">
      <span>© 2026 L.Y. INDUSTRIES CO., LTD. ALL RIGHTS RESERVED.</span>
      <span>ALSO SERVING: UNDERWEAR · FOOTWEAR · BAGS</span>
      <span>BANGKOK, THAILAND · EST. 1978</span>
    </div>

  </div>
</section>

</div>

<script src="assets/js/home.js" defer></script>
</body>
</html>
