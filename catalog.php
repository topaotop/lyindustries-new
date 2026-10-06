<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$activeNav = 'catalog';
$meta = page_meta('catalog');
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['title']) ?></title>
<link rel="icon" type="image/svg+xml" href="assets/img/brand/logo-lyi.svg">
<link rel="apple-touch-icon" href="assets/img/brand/apple-touch-icon.png">
<meta name="description" content="<?= e($meta['meta_desc']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;600;700&family=Anuphan:wght@300;400;500;600&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<script type="application/ld+json">{"@context": "https://schema.org", "@type": "CollectionPage", "name": "แคตตาล็อกสินค้า Narrow Fabric & Trims — L.Y. Industries", "about": {"@type": "Organization", "name": "L.Y. Industries Co., Ltd.", "url": "https://lyindustries.com"}, "mainEntity": {"@type": "ItemList", "itemListElement": [{"@type": "ListItem", "position": 1, "item": {"@type": "Product", "name": "ยางยืด / สายยืด (Elastic Webbing)", "category": "Narrow Fabric & Trims", "description": "ยืดหยุ่นสม่ำเสมอ คืนรูปยอดเยี่ยม ไม่ย้วยหลังผ่านการซักนับร้อยครั้ง สำหรับขอบเอว สายบ่า และงาน activewear", "brand": {"@type": "Brand", "name": "L.Y. Industries"}}}, {"@type": "ListItem", "position": 2, "item": {"@type": "Product", "name": "เทปทอ (Woven Tape)", "category": "Narrow Fabric & Trims", "description": "โครงสร้างแน่น ทนทานต่อแรงดึงสูง คงรูปได้ดีเยี่ยม สำหรับสายรัดกระเป๋า แถบตกแต่ง และชิ้นส่วนโครงสร้าง", "brand": {"@type": "Brand", "name": "L.Y. Industries"}}}, {"@type": "ListItem", "position": 3, "item": {"@type": "Product", "name": "เทปถัก Raschel / Crochet", "category": "Narrow Fabric & Trims", "description": "น้ำหนักเบา ผิวสัมผัสนุ่มเป็นพิเศษ ระบายอากาศได้ดี เหมาะสำหรับ overlay และชิ้นงานสัมผัสผิวหนังโดยตรง", "brand": {"@type": "Brand", "name": "L.Y. Industries"}}}, {"@type": "ListItem", "position": 4, "item": {"@type": "Product", "name": "เชือก เชือกยางยืด (Cords & Elastic Cords)", "category": "Narrow Fabric & Trims", "description": "เชือกกลม เชือกแบน เชือกยางยืด ถักเปีย พร้อมงาน tipping หัวเชือกครบทุกเทคนิค (ซิลิโคนจุ่ม, โลหะสลักโลโก้, ฟิล์มหด)", "brand": {"@type": "Brand", "name": "L.Y. Industries"}}}, {"@type": "ListItem", "position": 5, "item": {"@type": "Product", "name": "ขอบเอว (Engineered Waistbands)", "category": "Narrow Fabric & Trims", "description": "จุดที่ผู้สวมใส่รู้สึกในทุกวินาที ควบคุมทั้งความนุ่มนวลต่อผิวและแรงกระชับที่พอดีตัวสำหรับกางเกงกีฬา", "brand": {"@type": "Brand", "name": "L.Y. Industries"}}}, {"@type": "ListItem", "position": 6, "item": {"@type": "Product", "name": "งาน Finish ต่างๆ และพิมพ์โลโก้ (Finishing & Branding)", "category": "Narrow Fabric & Trims", "description": "ต่อยอดเทปให้ครบทั้งฟังก์ชันและแบรนด์ — ซิลิโคนกันลื่น พิมพ์ลาย heat transfer ปั๊มนูน เลเซอร์ ตัดร้อน/ตัดเย็น ไปจนถึงงานป้ายเลเบล เลือกผสมได้ตามการใช้งาน", "brand": {"@type": "Brand", "name": "L.Y. Industries"}}}]}}</script>
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
.fac{display:grid;grid-template-columns:repeat(3,1fr);gap:28px 24px}
.fac article{position:relative;padding-bottom:56px;isolation:isolate}
.fac figure{position:relative;aspect-ratio:4/3.3;border-radius:24px;overflow:hidden;background:#000}
.fac figure::after{content:"";position:absolute;top:0;right:0;width:32%;height:34px;background:var(--bg-primary);border-bottom-left-radius:18px}
.fac img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s ease}
.fac article:hover img{transform:scale(1.04)}
.fac .t{position:absolute;right:0;bottom:0;left:18%;background:var(--bg-secondary);border:1px solid var(--border-light);border-radius:22px;padding:20px 22px 22px;display:flex;flex-direction:column;gap:6px;box-shadow:0 18px 40px rgba(0,0,0,.45)}
.fac .t span{font:600 10.5px var(--font-mono);letter-spacing:.12em;color:var(--brand-orange)}
.fac h3{font-size:19px;font-weight:600;line-height:1.3}
.fac p{font-size:13.5px;line-height:1.6;color:var(--text-secondary)}
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

/* contact */
.touch{background:linear-gradient(160deg,#16161b,#0d0d10);border:1px solid var(--border-light);border-radius:28px;padding:56px 48px;display:grid;grid-template-columns:1.15fr .85fr;gap:48px;align-items:center;margin-bottom:96px;position:relative;overflow:hidden}
.touch::after{content:"";position:absolute;right:-20%;bottom:-60%;width:70%;height:120%;background:radial-gradient(ellipse,rgba(255,90,31,.12),transparent 60%);pointer-events:none}
.touch h2{font-size:clamp(30px,3.6vw,50px);font-weight:700;margin:14px 0 18px}
.touch p{color:var(--text-secondary);font-size:16px;max-width:520px;text-wrap:pretty}
.acts{display:flex;flex-direction:column;gap:12px;position:relative;z-index:1}
.act{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:18px 24px;border-radius:14px;border:1px solid var(--border-glass);background:rgba(255,255,255,.03);font-weight:600;font-size:16px;transition:transform .2s,border-color .2s}
.act:hover{transform:translateY(-1px);border-color:rgba(255,255,255,.3)}
.act.o{background:linear-gradient(135deg,var(--brand-orange),var(--brand-orange-light));border-color:transparent;box-shadow:0 10px 28px rgba(255,90,31,.3)}
.cgrid{display:grid;grid-template-columns:1.2fr .8fr;gap:28px;align-items:start}
form.card,.info{background:var(--bg-secondary);border:1px solid var(--border-light);border-radius:24px;padding:32px}
form.card{display:grid;grid-template-columns:1fr 1fr;gap:16px}
form.card .full{grid-column:1/-1}
label{display:flex;flex-direction:column;gap:8px;font-size:13.5px;color:var(--text-secondary)}
input,select,textarea{font:inherit;font-size:15px;color:var(--text-primary);background:var(--bg-primary);border:1px solid var(--border-glass);border-radius:12px;padding:13px 14px;outline:none;transition:border-color .2s}
input:focus,select:focus,textarea:focus{border-color:var(--brand-orange)}
textarea{min-height:130px;resize:vertical}
form.card button{justify-self:start;border:0;cursor:pointer;font:inherit}
.note{font-size:12.5px;color:var(--text-tertiary)}
.info{display:flex;flex-direction:column;gap:0}
.info .row{display:grid;grid-template-columns:110px 1fr;gap:14px;padding:16px 0;border-bottom:1px solid var(--border-light);font-size:15px}
.info .row:first-child{padding-top:0}.info .row:last-child{border-bottom:0;padding-bottom:0}
.info .k{font:600 10.5px var(--font-mono);letter-spacing:.12em;color:var(--brand-orange);padding-top:4px}
.info a:hover{color:var(--brand-orange)}
.map{border-radius:24px;overflow:hidden;border:1px solid var(--border-light);background:#111;position:relative}
.map iframe{display:block;width:100%;height:440px;border:0;filter:grayscale(.35) contrast(1.05)}
.maprow{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-top:16px;color:var(--text-secondary);font-size:14.5px}
.foot4{padding:56px 0 28px;border-top:1px solid var(--border-light);font:400 14px/1.75 var(--font-body);letter-spacing:0;color:var(--text-secondary)}
.foot4 .wrap{display:block}
.foot4 .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:36px;font-size:14px;line-height:1.75;color:var(--text-secondary)}
.foot4 .t{font:600 11px var(--font-mono);letter-spacing:.16em;color:var(--brand-orange);display:block;margin-bottom:10px}
.foot4 b{font:700 15px var(--font-heading);letter-spacing:.06em;color:#fff;display:block;margin-bottom:10px}
.foot4 nav{display:flex;flex-direction:column;gap:4px}.foot4 nav a:hover{color:#fff}
.foot4 .copy{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:40px;padding-top:22px;border-top:1px solid var(--border-light);font:500 11px var(--font-mono);letter-spacing:.12em;color:var(--text-tertiary)}
@media(max-width:960px){.touch{grid-template-columns:1fr;padding:40px 28px}.cgrid{grid-template-columns:1fr}.foot4 .grid{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.touch h2 br{display:none}form.card{grid-template-columns:1fr;padding:24px}.foot4 .grid{grid-template-columns:1fr}.map iframe{height:340px}}

.chips{display:flex;flex-wrap:wrap;gap:10px;margin:-8px 0 36px}
.chip{font:inherit;font-size:14px;color:var(--text-secondary);background:rgba(255,255,255,.03);border:1px solid var(--border-glass);border-radius:100px;padding:9px 18px;cursor:pointer;transition:all .2s}
.chip:hover{color:#fff}.chip.on{background:var(--brand-orange);border-color:var(--brand-orange);color:#fff}
.fac{display:grid;grid-template-columns:repeat(3,1fr);gap:28px 24px}
.fac article{position:relative;padding-bottom:64px;isolation:isolate}
.fac article.hide,.sm.hide{display:none}
.fac figure{position:relative;aspect-ratio:4/3.3;border-radius:24px;overflow:hidden;background:#000}
.fac figure::after{content:"";position:absolute;top:0;right:0;width:32%;height:34px;background:var(--bg-primary);border-bottom-left-radius:18px}
.fac img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s ease}
.fac article:hover img{transform:scale(1.04)}
.fac .t{position:absolute;right:0;bottom:0;left:18%;background:var(--bg-secondary);border:1px solid var(--border-light);border-radius:22px;padding:20px 22px 22px;display:flex;flex-direction:column;gap:6px;box-shadow:0 18px 40px rgba(0,0,0,.45)}
.fac .t span{font:600 10.5px var(--font-mono);letter-spacing:.12em;color:var(--brand-orange)}
.fac h3{font-size:19px;font-weight:600;line-height:1.3}
.fac p{font-size:13.5px;line-height:1.6;color:var(--text-secondary)}
.fac em{font:500 11.5px var(--font-mono);font-style:normal;color:var(--text-tertiary);letter-spacing:.04em}
.smgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.sm{background:var(--bg-secondary);border:1px solid var(--border-light);border-radius:20px;overflow:hidden;display:flex;flex-direction:column;transition:border-color .2s,transform .2s}
.sm:hover{border-color:rgba(255,90,31,.45);transform:translateY(-2px)}
.sm figure{position:relative;aspect-ratio:16/10;background:#000;overflow:hidden}
.sm img{width:100%;height:100%;object-fit:cover;display:block;opacity:.92}
.sm figure b{position:absolute;left:14px;top:14px;font:600 12px var(--font-mono);letter-spacing:.1em;color:#ffb08f;background:rgba(0,0,0,.6);border:1px solid rgba(255,90,31,.4);padding:4px 10px;border-radius:100px}
.sm div{padding:18px 20px 20px;display:flex;flex-direction:column;gap:4px}
.sm span{font:600 10.5px var(--font-mono);letter-spacing:.12em;color:var(--brand-orange);text-transform:uppercase}
.sm h4{font:600 17px var(--font-heading)}
.sm i{font-style:normal;font-size:13.5px;color:var(--brand-orange);margin-top:6px}
.hub{background:linear-gradient(160deg,#16161b,#0d0d10);border:1px solid var(--border-light);border-radius:28px;padding:48px;display:flex;justify-content:space-between;align-items:center;gap:32px;flex-wrap:wrap;margin-bottom:96px}
.hub h2{font-size:clamp(26px,3vw,40px);font-weight:700;margin:12px 0 10px}
.hub p{color:var(--text-secondary);max-width:560px}
.hub .row{display:flex;gap:12px;flex-wrap:wrap}
@media(max-width:960px){.fac,.smgrid{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.fac,.smgrid{grid-template-columns:1fr}.hub{padding:32px 24px}}

/* ===== LIGHT THEME ===== */
:root{--bg-primary:#f5f5f7;--bg-secondary:#ffffff;--bg-card:#ffffff;--brand-orange:#e8531a;--brand-orange-light:#ff7a3d;--text-primary:#1d1d1f;--text-secondary:#515154;--text-tertiary:#86868b;--border-light:rgba(0,0,0,.08);--border-glass:rgba(0,0,0,.12)}
body{background:var(--bg-primary);color:var(--text-primary)}
.nav{background:rgba(245,245,247,.85)}
.brand small{color:var(--text-tertiary)}
.nav nav a:hover,.nav nav a.on{color:var(--text-primary)}
.hero::before{background:radial-gradient(ellipse at 30% 50%,rgba(232,83,26,.10),transparent 60%)}
.hero h1,.head h2,.hub h2{color:var(--text-primary)}
.btn-g{background:#fff;color:var(--text-primary)}
.chip{background:#fff;color:var(--text-secondary)}
.chip:hover{color:var(--text-primary);border-color:rgba(0,0,0,.25)}
.chip.on{color:#fff}
.fac figure{background:#e9e9ec}
.fac .t{box-shadow:0 18px 40px rgba(0,0,0,.10)}
.fac h3,.sm h4{color:var(--text-primary)}
.sm{box-shadow:0 1px 2px rgba(0,0,0,.04)}
.sm:hover{box-shadow:0 12px 30px rgba(0,0,0,.08)}
.sm figure{background:#e9e9ec}
.hub{background:linear-gradient(160deg,#ffffff,#f0f0f3);box-shadow:0 20px 50px rgba(0,0,0,.06)}
.foot4{background:#ffffff}
.foot4 b{color:var(--text-primary)}
.foot4 nav a:hover{color:var(--text-primary)}

.sqgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px}
.sq{background:#fff;border:1px solid var(--border-light);border-radius:20px;padding:14px;display:flex;flex-direction:column;gap:12px;transition:transform .2s,box-shadow .2s,border-color .2s}
.sq:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(0,0,0,.08);border-color:rgba(232,83,26,.35)}
.sq figure{aspect-ratio:1;border-radius:14px;overflow:hidden;background:#e8e8ed}
.sq img{width:100%;height:100%;object-fit:cover;display:block}
.sq-row{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:0 4px}
.sq-row b{font:600 13px var(--font-mono);letter-spacing:.06em;color:var(--text-primary)}
.sq-row span{font:500 10.5px var(--font-mono);color:var(--brand-orange);text-align:right}

.pcgrid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px}
.pc{background:#fff;border:1px solid var(--border-light);border-radius:22px;overflow:hidden;display:flex;flex-direction:column;transition:transform .25s,box-shadow .25s}
.pc:hover{transform:translateY(-4px);box-shadow:0 20px 44px rgba(0,0,0,.10)}
.pc figure{position:relative;aspect-ratio:16/10;background:#000;overflow:hidden}
.pc img{width:100%;height:100%;object-fit:cover;display:block}
.pc figure b{position:absolute;left:14px;top:14px;font:600 11px var(--font-mono);letter-spacing:.1em;color:#ff8a5c;background:rgba(0,0,0,.6);border:1px solid rgba(255,90,31,.35);padding:4px 10px;border-radius:100px}
.pc-b{padding:26px 24px 22px;display:flex;flex-direction:column;gap:8px;flex:1}
.pc-b span{font:600 11px var(--font-mono);letter-spacing:.14em;color:var(--brand-orange)}
.pc-b h3{font:600 20px/1.3 var(--font-heading);color:var(--text-primary);flex:1;padding-bottom:6px}
.pc-b p{font-size:15px;line-height:1.7;color:var(--text-secondary);flex:1;padding-bottom:14px;border-bottom:1px solid var(--border-light)}
.pc-b a{align-self:flex-end;padding-top:14px;border-top:1px solid var(--border-light);width:100%;text-align:right;font:600 14px var(--font-heading);color:var(--brand-orange);margin-top:6px}
@media(max-width:960px){.pcgrid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.pcgrid{grid-template-columns:1fr}}
</style>
</head>
<body>

<?php require __DIR__ . '/includes/site-header.php'; ?>

<section class="hero">
  <div class="wrap">
    <span class="eyebrow"><?= b('catalog.hero.01') ?></span>
    <h1><?= b('catalog.hero.02') ?><span>.</span></h1>
    <p class="lead"><?= b('catalog.hero.03') ?></p>
  </div>
</section>

<section class="block" id="categories">
  <div class="wrap">
    <div class="head">
      <span class="eyebrow"><?= b('catalog.categories.01') ?></span>
      <h2><?= b('catalog.categories.02') ?></h2>
    </div>
    <div class="pcgrid">
      <article class="pc"><figure><img src="assets/img/products/prod-elastic.jpg?v=2" alt="ยางยืด / สายยืด (Elastic Webbing) — L.Y. Industries" loading="lazy"><b><?= b('catalog.categories.03') ?></b></figure><div class="pc-b"><span><?= b('catalog.categories.04') ?></span><h3><?= b('catalog.categories.05') ?></h3><a href="<?= e(site('inspiration_url')) ?>" target="_blank" rel="noopener"><?= b('catalog.categories.06') ?></a></div></article>
      <article class="pc"><figure><img src="assets/img/products/prod-woven.jpg" alt="เทปทอ (Woven Tape) — L.Y. Industries" loading="lazy"><b><?= b('catalog.categories.07') ?></b></figure><div class="pc-b"><span><?= b('catalog.categories.08') ?></span><h3><?= b('catalog.categories.09') ?></h3><a href="<?= e(site('inspiration_url')) ?>" target="_blank" rel="noopener"><?= b('catalog.categories.10') ?></a></div></article>
      <article class="pc"><figure><img src="assets/img/products/prod-knit.jpg" alt="เทปถัก Raschel / Crochet — L.Y. Industries" loading="lazy"><b><?= b('catalog.categories.11') ?></b></figure><div class="pc-b"><span><?= b('catalog.categories.12') ?></span><h3><?= b('catalog.categories.13') ?></h3><a href="<?= e(site('inspiration_url')) ?>" target="_blank" rel="noopener"><?= b('catalog.categories.14') ?></a></div></article>
      <article class="pc"><figure><img src="assets/img/products/prod-cord.jpg" alt="เชือก เชือกยางยืด (Cords &amp; Elastic Cords) — L.Y. Industries" loading="lazy"><b><?= b('catalog.categories.15') ?></b></figure><div class="pc-b"><span><?= b('catalog.categories.16') ?></span><h3><?= b('catalog.categories.17') ?></h3><a href="<?= e(site('inspiration_url')) ?>" target="_blank" rel="noopener"><?= b('catalog.categories.18') ?></a></div></article>
      <article class="pc"><figure><img src="assets/img/products/prod-waistband.jpg?v=2" alt="ขอบเอว (Engineered Waistbands) — L.Y. Industries" loading="lazy"><b><?= b('catalog.categories.19') ?></b></figure><div class="pc-b"><span><?= b('catalog.categories.20') ?></span><h3><?= b('catalog.categories.21') ?></h3><a href="<?= e(site('inspiration_url')) ?>" target="_blank" rel="noopener"><?= b('catalog.categories.22') ?></a></div></article>
      <article class="pc"><figure><img src="assets/img/products/prod-finishing.jpg" alt="งาน Finish ต่างๆ และพิมพ์โลโก้ (Finishing &amp; Branding) — L.Y. Industries" loading="lazy"><b><?= b('catalog.categories.23') ?></b></figure><div class="pc-b"><span><?= b('catalog.categories.24') ?></span><h3><?= b('catalog.categories.25') ?></h3><a href="<?= e(site('inspiration_url')) ?>" target="_blank" rel="noopener"><?= b('catalog.categories.26') ?></a></div></article>
    </div>
  </div>
</section>

<section class="block" id="samples">
  <div class="wrap">
    <div class="head">
      <span class="eyebrow"><?= b('catalog.samples.01') ?></span>
      <h2><?= b('catalog.samples.02') ?></h2>
      <p><?= b('catalog.samples.03') ?></p>
    </div>
    <div class="sqgrid">
      <a class="sq" href="contact.php#form"><figure><img src="<?= e(SITE_PLACEHOLDER_IMG) ?>" alt="LY2086 Elastic Jacquard — ยางยืดทอลาย Jacquard" loading="lazy"></figure><div class="sq-row"><b><?= b('catalog.samples.04') ?></b><span><?= b('catalog.samples.05') ?></span></div></a>
      <a class="sq" href="contact.php#form"><figure><img src="<?= e(SITE_PLACEHOLDER_IMG) ?>" alt="RLY1319 Raschel Knit Tape — เทปถัก Raschel" loading="lazy"></figure><div class="sq-row"><b><?= b('catalog.samples.06') ?></b><span><?= b('catalog.samples.07') ?></span></div></a>
      <a class="sq" href="contact.php#form"><figure><img src="<?= e(SITE_PLACEHOLDER_IMG) ?>" alt="RLY1452 Braided Cord Tipped — เชือกถักเปียพร้อมหัวเชือก" loading="lazy"></figure><div class="sq-row"><b><?= b('catalog.samples.08') ?></b><span><?= b('catalog.samples.09') ?></span></div></a>
      <a class="sq" href="contact.php#form"><figure><img src="<?= e(SITE_PLACEHOLDER_IMG) ?>" alt="LY2101 Silicone Grip Tape — เทปซิลิโคนกันลื่น" loading="lazy"></figure><div class="sq-row"><b><?= b('catalog.samples.10') ?></b><span><?= b('catalog.samples.11') ?></span></div></a>
      <a class="sq" href="contact.php#form"><figure><img src="<?= e(SITE_PLACEHOLDER_IMG) ?>" alt="RLY1377 Engineered Waistband — ขอบเอวกางเกงกีฬา" loading="lazy"></figure><div class="sq-row"><b><?= b('catalog.samples.12') ?></b><span><?= b('catalog.samples.13') ?></span></div></a>
      <a class="sq" href="contact.php#form"><figure><img src="<?= e(SITE_PLACEHOLDER_IMG) ?>" alt="LY2144 Woven High-Tensile — เทปทอรับแรงดึงสูง" loading="lazy"></figure><div class="sq-row"><b><?= b('catalog.samples.14') ?></b><span><?= b('catalog.samples.15') ?></span></div></a>
    </div>
  </div>
</section>

<div class="wrap">
  <div class="hub">
    <div>
      <span class="eyebrow"><?= b('catalog.samples.16') ?></span>
      <h2><?= b('catalog.samples.17') ?></h2>
      <p><?= b('catalog.samples.18') ?></p>
    </div>
    <div class="row">
      <a class="btn btn-o" href="<?= e(site('inspiration_url')) ?>" target="_blank" rel="noopener"><?= b('catalog.samples.19') ?></a>
      <a class="btn btn-g" href="contact.php#form"><?= b('catalog.samples.20') ?></a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/site-footer.php'; ?>

</body>
</html>
