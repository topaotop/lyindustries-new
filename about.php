<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$activeNav = 'about';
$meta = page_meta('about', lang());
?>
<!DOCTYPE html>
<html lang="<?= e(lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['title']) ?></title>
<?= seo_head_tags('about', $meta) ?>
<link rel="icon" type="image/svg+xml" href="assets/img/brand/logo-lyi.svg">
<link rel="apple-touch-icon" href="assets/img/brand/apple-touch-icon.png">
<meta name="description" content="<?= e($meta['meta_desc']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;600;700&family=Anuphan:wght@300;400;500;600&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"AboutPage","name":"<?= json_inner(block_text('about.schema.01')) ?>","mainEntity":{"@type":"Organization","name":"L.Y. Industries Co., Ltd.",<?= json_member('alternateName', org_alternate_names()) ?>,<?= json_member('foundingDate', site('org_founding')) ?>,"url":"<?= json_inner(schema_site_url()) ?>","email":"<?= json_inner(site('email')) ?>","telephone":"<?= json_inner(phone_schema(site('phone'))) ?>",<?= json_member('address', org_postal_address()) ?>,<?= json_member('sameAs', org_same_as()) ?>,"description":"<?= json_inner(block_text('about.schema.02')) ?>"}}
</script>
<style>
:root{--bg-primary:#08080a;--bg-secondary:#101014;--bg-card:rgba(22,22,28,.7);--brand-orange:#ff5a1f;--brand-orange-light:#ff7e47;--text-primary:#f5f5f7;--text-secondary:#a1a1a6;--text-tertiary:#6e6e73;--border-light:rgba(255,255,255,.08);--border-glass:rgba(255,255,255,.12);--font-heading:'Kanit',-apple-system,sans-serif;--font-body:'Anuphan',-apple-system,sans-serif;--font-mono:'JetBrains Mono',Menlo,monospace}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg-primary);color:var(--text-primary);font-family:var(--font-body);line-height:1.6;-webkit-font-smoothing:antialiased}
a{color:inherit;text-decoration:none}
.wrap{max-width:1140px;margin:0 auto;padding:0 24px}
.eyebrow{font-family:var(--font-mono);font-size:clamp(15px,1.4vw,18px);letter-spacing:.16em;color:var(--brand-orange);font-weight:700}
h1,h2,h3{font-family:var(--font-heading);line-height:1.2}
/* nav */
.nav{position:sticky;top:0;z-index:50;background:rgba(8,8,10,.8);backdrop-filter:blur(18px);border-bottom:1px solid var(--border-light)}
.nav .wrap{display:flex;align-items:center;justify-content:space-between;height:72px;gap:16px}
.brand{display:flex;align-items:center;gap:12px}
.brand .mark{width:40px;height:40px;display:block;flex-shrink:0}
.brand b{font:700 15px var(--font-heading);letter-spacing:.02em;display:block}
.brand small{font:500 9px var(--font-mono);letter-spacing:.12em;color:var(--text-secondary)}
.nav nav{display:flex;gap:26px;font-size:15px;color:var(--text-secondary)}
.nav nav a:hover,.nav nav a.on{color:#fff}
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:100px;font-weight:600;font-size:14.5px;transition:transform .2s,box-shadow .2s}
.btn-o{background:linear-gradient(135deg,var(--brand-orange),var(--brand-orange-light));color:#fff;box-shadow:0 8px 24px rgba(255,90,31,.25)}
.lang-switch:hover{border-color:var(--brand-orange)!important}
.btn-o:hover{transform:translateY(-1px);box-shadow:0 12px 30px rgba(255,90,31,.35)}
.btn-g{border:1px solid var(--border-glass);color:var(--text-primary);background:rgba(255,255,255,.03)}
/* hero */
.hero{padding:96px 0 56px;position:relative;overflow:hidden}
.hero::before{content:"";position:absolute;inset:auto -10% -40% -10%;height:70%;background:radial-gradient(ellipse at 30% 50%,rgba(255,90,31,.18),transparent 60%);pointer-events:none}
.hero .wrap{position:relative;display:flex;flex-direction:column;gap:22px;max-width:900px}
.hero h1{font-size:clamp(36px,5vw,64px);font-weight:700;letter-spacing:-.01em}
.hero h1 span{color:var(--brand-orange)}
.hero p.lead{font-size:clamp(17px,1.6vw,20px);color:var(--text-secondary);max-width:760px;text-wrap:pretty}
/* video */
.video{padding:16px 0 72px}
.video-frame{position:relative;border-radius:24px;overflow:hidden;border:1px solid var(--border-light);background:#000;box-shadow:0 30px 80px rgba(0,0,0,.6),0 0 0 1px rgba(255,90,31,.15)}
.video-frame::before{content:"";display:block;padding-top:56.25%}
.video-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.video-cap{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-top:14px;font:500 11px var(--font-mono);letter-spacing:.14em;color:var(--text-tertiary)}
/* stats */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--border-light);border:1px solid var(--border-light);border-radius:20px;overflow:hidden;margin-bottom:96px}
.stat{background:var(--bg-secondary);padding:26px 24px;display:flex;flex-direction:column;gap:6px}
.stat b{font:700 clamp(26px,3vw,38px)/1 var(--font-heading);color:#fff}
.stat span{font-size:13.5px;color:var(--text-secondary)}
/* sections */
section.block{padding:0 0 96px}
.head{display:flex;flex-direction:column;gap:14px;margin-bottom:40px;max-width:820px}
.head h2{font-size:clamp(28px,3.4vw,46px);font-weight:700}
.head p{font-size:16.5px;color:var(--text-secondary);text-wrap:pretty}
/* story */
.story{display:grid;grid-template-columns:1.1fr .9fr;gap:48px;align-items:start}
.story p{font-size:16px;color:var(--text-secondary);margin-bottom:16px;text-wrap:pretty}
.story p strong{color:#fff;font-weight:500}
.facts{background:var(--bg-secondary);border:1px solid var(--border-light);border-radius:20px;padding:26px 28px;display:flex;flex-direction:column;gap:0}
.facts .row{display:grid;grid-template-columns:120px 1fr;gap:14px;padding:12px 0;border-bottom:1px solid var(--border-light);font-size:14.5px}
.facts .row:last-child{border-bottom:0}
.facts .k{font:600 10.5px var(--font-mono);letter-spacing:.12em;color:var(--brand-orange);padding-top:4px}
.facts .v{color:var(--text-primary)}
/* trust grid */
.grid4{display:grid;grid-template-columns:repeat(4,1fr);gap:0;position:relative}
.grid4::before{content:"";position:absolute;left:0;right:0;top:42px;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.18) 10%,rgba(255,255,255,.18) 90%,transparent)}
.item{padding:0 24px 0 0;display:flex;flex-direction:column;gap:12px;align-items:flex-start;position:relative}
.item+.item{padding-left:24px;border-left:1px solid var(--border-light)}
.icon{width:84px;height:84px;border-radius:50%;background:var(--bg-primary);border:1px solid var(--border-glass);box-shadow:0 0 0 8px var(--bg-primary),0 10px 30px rgba(255,90,31,.2);display:grid;place-items:center;color:var(--brand-orange);margin-bottom:8px;position:relative;z-index:1}
.icon svg{width:50px;height:50px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
.icon .soft{stroke:rgba(255,255,255,.35)}.icon .fill{fill:var(--brand-orange);stroke:none}
.num{font:600 12px var(--font-mono);letter-spacing:.14em;color:var(--brand-orange)}
.num::after{content:"";display:inline-block;width:22px;height:1px;background:var(--brand-orange);margin-left:10px;vertical-align:middle}
.item h3{font-size:clamp(19px,1.6vw,22px);font-weight:600}
.item p{font-size:14.5px;color:var(--text-secondary);text-wrap:pretty}
/* facilities */
.fac{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.fac article{display:flex;align-items:center;gap:18px;background:var(--bg-secondary);border:1px solid var(--border-light);border-radius:20px;padding:24px 26px;transition:transform .3s ease,border-color .3s ease,box-shadow .3s ease}
.fac article:hover{transform:translateY(-3px);border-color:var(--brand-orange)}
.fac .ic{flex:none;width:60px;height:60px;border-radius:16px;display:grid;place-items:center;background:rgba(255,90,31,.10);color:var(--brand-orange)}
.fac .ic svg{width:30px;height:30px;fill:none;stroke:currentColor;stroke-width:1.6;stroke-linecap:round;stroke-linejoin:round}
.fac .t{display:flex;flex-direction:column;gap:4px;min-width:0}
.fac .t span{font:600 10.5px var(--font-mono);letter-spacing:.12em;color:var(--brand-orange)}
.fac h3{font-size:19px;font-weight:600;line-height:1.3}
/* cta */
.cta{background:var(--bg-secondary);border-top:1px solid var(--border-light);padding:80px 0}
.cta .wrap{display:flex;flex-direction:column;gap:20px;align-items:flex-start}
.cta h2{font-size:clamp(28px,3.4vw,44px);font-weight:700;max-width:760px}
.cta p{font-size:16px;color:var(--text-secondary);max-width:640px}
.cta .row{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px}
footer{padding:36px 0;border-top:1px solid var(--border-light);font:500 11px var(--font-mono);letter-spacing:.12em;color:var(--text-tertiary)}
footer .wrap{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
@media(max-width:960px){.stats{grid-template-columns:repeat(2,1fr)}.story{grid-template-columns:1fr}.grid4{grid-template-columns:repeat(2,1fr);row-gap:44px}.grid4::before{display:none}.item:nth-child(3){padding-left:0;border-left:0}.fac{grid-template-columns:1fr}.nav nav{display:none}}
@media(max-width:600px){.hero{padding:64px 0 40px}.stats{grid-template-columns:1fr 1fr}.grid4{grid-template-columns:1fr}.item{padding:0!important;border-left:0!important}}

/* ===== alternating light sections ===== */
.light{--bg-primary:#f5f5f7;--bg-secondary:#ffffff;--bg-card:#ffffff;--brand-orange:#e8531a;--text-primary:#1d1d1f;--text-secondary:#515154;--text-tertiary:#86868b;--border-light:rgba(0,0,0,.08);--border-glass:rgba(0,0,0,.12);background:var(--bg-primary);color:var(--text-primary)}
.band{padding:96px 0 0}
.light .stat b,.light .head h2,.light .story p strong,.light .cta h2{color:var(--text-primary)}
.light .stats{box-shadow:0 10px 30px rgba(0,0,0,.05)}
.light .facts{box-shadow:0 10px 30px rgba(0,0,0,.05)}
.light .btn-g{background:#fff}
section.video{padding-bottom:96px}
#facilities{padding-top:96px}
.cta{border-top:0}
.video.light{padding-top:72px}
.light .fac article{box-shadow:0 10px 30px rgba(0,0,0,.05)}
.light .fac h3{color:var(--text-primary)}
.light .video-frame{box-shadow:0 30px 70px rgba(0,0,0,.18)}

/* stat hover glow */
.stats{overflow:visible;isolation:isolate}
.stat{position:relative;transition:background .35s ease,box-shadow .35s ease,transform .35s ease;z-index:0}
.stat:first-child{border-radius:20px 0 0 20px}.stat:last-child{border-radius:0 20px 20px 0}
.stat::before{content:"";position:absolute;inset:0;border-radius:inherit;background:radial-gradient(120% 90% at 30% 0%,rgba(255,90,31,.28),transparent 65%);opacity:0;transition:opacity .35s ease;pointer-events:none}
.stat b,.stat span{position:relative;transition:color .35s ease,text-shadow .35s ease}
.stat:hover{background:#17171c;box-shadow:0 0 0 1px rgba(255,90,31,.55),0 0 36px rgba(255,90,31,.35),0 18px 40px rgba(0,0,0,.45);transform:translateY(-3px);z-index:2}
.stat:hover::before{opacity:1}
.stat:hover b{color:var(--brand-orange-light);text-shadow:0 0 18px rgba(255,110,50,.55)}
.stat:hover span{color:#d6d6da}
@media(max-width:960px){.stat:first-child,.stat:last-child{border-radius:0}}
</style>
</head>
<body>

<?php require __DIR__ . '/includes/site-header.php'; ?>

<section class="hero">
  <div class="wrap">
    <span class="eyebrow"><?= b('about.hero.01') ?></span>
    <h1><?= b('about.hero.02') ?><br><?= b('about.hero.03') ?><?= word_gap() ?><span><?= b('about.hero.04') ?></span></h1>
    <p class="lead"><?= b('about.hero.05') ?></p>
  </div>
</section>

<section class="video light">
  <div class="wrap">
    <div class="video-frame">
      <iframe src="https://www.youtube-nocookie.com/embed/Lr59gy7RcWo?start=6&rel=0&modestbranding=1" title="L.Y. Industries — Inside the factory" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>
    </div>
    <div class="video-cap"><span><?= b('about.video.01') ?></span><span><?= b('about.video.02') ?></span></div>
  </div>
</section>

<div class="band">
<div class="wrap">
  <div class="stats">
    <div class="stat"><b><?= b('about.video.03') ?></b><span><?= b('about.video.04') ?></span></div>
    <div class="stat"><b><?= b('about.video.05') ?></b><span><?= b('about.video.06') ?></span></div>
    <div class="stat"><b style="white-space:nowrap;font-size:clamp(22px,2.5vw,34px)"><?= b('about.video.07') ?></b><span><?= b('about.video.08') ?></span></div>
    <div class="stat"><b><?= b('about.video.09') ?></b><span><?= b('about.video.10') ?></span></div>
  </div>
</div>

<section class="block" id="story">
  <div class="wrap">
    <div class="head">
      <span class="eyebrow"><?= b('about.story.01') ?></span>
      <h2><?= b('about.story.02') ?></h2>
    </div>
    <div class="story">
      <div>
        <p><strong><?= b('about.story.03') ?></strong> <?= b('about.story.04') ?> <em><?= b('about.story.05') ?></em></p>
        <p><?= b('about.story.06') ?></p>
      </div>
      <div class="facts">
        <div class="row"><span class="k"><?= b('about.story.07') ?></span><span class="v"><?= b('about.story.08') ?></span></div>
        <div class="row"><span class="k"><?= b('about.story.09') ?></span><span class="v"><?= b('about.story.10') ?></span></div>
        <div class="row"><span class="k"><?= b('about.story.11') ?></span><span class="v"><?= address_html() ?></span></div>
        <div class="row"><span class="k"><?= b('about.story.12') ?></span><span class="v"><?= b('about.story.13') ?></span></div>
        <div class="row"><span class="k"><?= b('about.story.14') ?></span><span class="v"><?= b('about.story.15') ?></span></div>
        <div class="row"><span class="k"><?= b('about.story.16') ?></span><span class="v"><?= e(phone_display()) ?> · <?= e(site('email')) ?></span></div>
      </div>
    </div>
  </div>
</section>
</div>

<section class="block light" id="facilities">
  <div class="wrap">
    <div class="head">
      <span class="eyebrow"><?= b('about.facilities.01') ?></span>
      <h2><?= b('about.facilities.02') ?></h2>
      <p><?= b('about.facilities.03') ?></p>
    </div>
    <div class="fac">
      <article><div class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></div><div class="t"><span><?= b('about.facilities.04') ?></span><h3><?= b('about.facilities.05') ?></h3></div></article>
      <article><div class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.7s6 6.3 6 11.1a6 6 0 0 1-12 0c0-4.8 6-11.1 6-11.1Z"/><path d="M9.5 14.5a2.5 2.5 0 0 0 2.5 2.5"/></svg></div><div class="t"><span><?= b('about.facilities.06') ?></span><h3><?= b('about.facilities.07') ?></h3></div></article>
      <article><div class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 3h6"/><path d="M10 3v6.5L4.6 18.2A1.9 1.9 0 0 0 6.2 21h11.6a1.9 1.9 0 0 0 1.6-2.8L14 9.5V3"/><path d="M7.5 15h9"/></svg></div><div class="t"><span><?= b('about.facilities.08') ?></span><h3><?= b('about.facilities.09') ?></h3></div></article>
      <article><div class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3v5M8 11v5M8 19v2M16 3v2M16 8v5M16 16v5"/><path d="M3 8h2M8 8h8M19 8h2M3 16h5M11 16h5M19 16h2" /><path d="M3 12h18" stroke-dasharray="3 3"/></svg></div><div class="t"><span><?= b('about.facilities.10') ?></span><h3><?= b('about.facilities.11') ?></h3></div></article>
      <article><div class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4 8.1 15.9M14.5 14.5 20 20M8.1 8.1 12 12"/></svg></div><div class="t"><span><?= b('about.facilities.12') ?></span><h3><?= b('about.facilities.13') ?></h3></div></article>
      <article><div class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4.5 6v5.5c0 4.6 3.1 8.2 7.5 9.5 4.4-1.3 7.5-4.9 7.5-9.5V6Z"/><path d="m8.8 12 2.3 2.3 4.2-4.4"/></svg></div><div class="t"><span><?= b('about.facilities.14') ?></span><h3><?= b('about.facilities.15') ?></h3></div></article>
    </div>
  </div>
</section>

<section class="cta">
  <div class="wrap">
    <span class="eyebrow"><?= b('about.cta.01') ?></span>
    <h2><?= b('about.cta.02') ?></h2>
    <p><?= b('about.cta.03') ?></p>
    <div class="row">
      <a class="btn btn-o" href="contact.php#form"><?= b('about.cta.04') ?></a>
      <a class="btn btn-g" href="mailto:<?= e(site('email')) ?>"><?= e(site('email')) ?></a>
      <a class="btn btn-g" href="<?= e(tel_href_intl()) ?>"><?= e(phone_display()) ?></a>
    </div>
  </div>
</section>

<footer><div class="wrap"><span><?= b('about.cta.05') ?></span><span><?= b('about.cta.06') ?></span></div></footer>
<script src="<?= e(asset('assets/js/track.js')) ?>" defer></script>
</body>
</html>
