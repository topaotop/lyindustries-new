<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/lib/visitor.php';

$activeNav = 'contact';
$meta = page_meta('contact', lang());
$quoteHref = '#form';
?>
<!DOCTYPE html>
<html lang="<?= e(lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['title']) ?></title>
<?= seo_head_tags('contact', $meta) ?>
<link rel="icon" type="image/svg+xml" href="assets/img/brand/logo-lyi.svg">
<link rel="apple-touch-icon" href="assets/img/brand/apple-touch-icon.png">
<meta name="description" content="<?= e($meta['meta_desc']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;600;700&family=Anuphan:wght@300;400;500;600&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"ContactPage","name":"<?= json_inner(block_text('contact.schema.01')) ?>","mainEntity":{"@type":["Organization","LocalBusiness"],"name":"L.Y. Industries Co., Ltd.",<?= json_member('alternateName', array_values(array_unique([...org_alternate_names(), company_name()]))) ?>,<?= json_member('foundingDate', site('org_founding')) ?>,"url":"<?= json_inner(schema_site_url()) ?>","email":"<?= json_inner(site('email')) ?>","telephone":"<?= json_inner(phone_schema(site('phone'))) ?>","faxNumber":"<?= json_inner(phone_schema(site('fax'))) ?>",<?= json_member('address', org_postal_address()) ?>,"hasMap":"https://www.google.com/maps/search/?api=1&query=L.Y.+Industries+124+Soi+Ram+Inthra+109+Phraya+Suren+Rd+Bang+Chan+Khlong+Sam+Wa+Bangkok+10510","openingHoursSpecification":[{"@type":"OpeningHoursSpecification","dayOfWeek":["Monday","Tuesday","Wednesday","Thursday","Friday"],"opens":"<?= json_inner(site('hours_weekday_open')) ?>","closes":"<?= json_inner(site('hours_weekday_close')) ?>"},{"@type":"OpeningHoursSpecification","dayOfWeek":"Saturday","opens":"<?= json_inner(site('hours_sat_open')) ?>","closes":"<?= json_inner(site('hours_sat_close')) ?>"}],"contactPoint":{"@type":"ContactPoint","contactType":"sales","telephone":"<?= json_inner(phone_schema(site('phone'))) ?>","email":"<?= json_inner(site('email')) ?>","availableLanguage":["th","en"]},<?= json_member('sameAs', org_same_as()) ?>}}
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
form.card button:disabled{opacity:.6;cursor:wait}
form.card .hp{position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden}
form.card .sent{display:flex;flex-direction:column;gap:6px;padding:28px 4px;text-align:center}
form.card .sent[hidden]{display:none}
form.card .sent strong{font-size:20px;color:var(--text-primary)}
form.card .sent span{color:var(--text-secondary)}
form.card.is-sent>:not(.sent){display:none}
form.card .ferr{font-size:13.5px;color:#ff8a65}
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
/* light section */
.light{--bg-primary:#f5f5f7;--bg-secondary:#ffffff;--bg-card:#ffffff;--brand-orange:#e8531a;--text-primary:#1d1d1f;--text-secondary:#515154;--text-tertiary:#86868b;--border-light:rgba(0,0,0,.08);--border-glass:rgba(0,0,0,.14);background:var(--bg-primary);color:var(--text-primary)}
section.block.light{padding:96px 0}
section.block.light+section.block{padding-top:96px}
.light form.card,.light .info{box-shadow:0 10px 30px rgba(0,0,0,.05)}
.light input,.light select,.light textarea{background:#fff}
</style>
</head>
<body>

<?php require __DIR__ . '/includes/site-header.php'; ?>

<section class="hero">
  <div class="wrap">
    <span class="eyebrow"><?= b('contact.hero.01') ?></span>
    <h1><?= b('contact.hero.02') ?><span>.</span></h1>
  </div>
</section>


<section class="block light" id="form">
  <div class="wrap">
    <div class="head">
      <span class="eyebrow"><?= b('contact.form.01') ?></span>
      <h2><?= b('contact.form.02') ?></h2>
      <p><?= b('contact.form.03') ?></p>
    </div>
    <div class="cgrid">
      <form class="card" id="qform" method="post" action="api/v1/contact.php"<?= isset($_GET['sent']) ? ' data-sent' : '' ?>>
        <div class="sent full" role="status"<?= isset($_GET['sent']) ? '' : ' hidden' ?>><strong><?= b('contact.form.24') ?></strong><span><?= b('contact.form.25') ?></span></div>
        <input type="hidden" name="t" value="<?= e(form_stamp()) ?>">
        <input type="hidden" name="lang" value="<?= e(lang()) ?>">
        <label class="hp" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
        <label><?= b('contact.form.04') ?><input name="name" required></label>
        <label><?= b('contact.form.05') ?><input name="company"></label>
        <label><?= b('contact.form.06') ?><input name="email" type="email" required></label>
        <label><?= b('contact.form.07') ?><input name="phone" type="tel"></label>
        <label class="full"><?= b('contact.form.08') ?>

          <select name="product">
            <option><?= b('contact.form.09') ?></option><option><?= b('contact.form.10') ?></option><option><?= b('contact.form.11') ?></option>
            <option><?= b('contact.form.12') ?></option><option><?= b('contact.form.13') ?></option><option><?= b('contact.form.14') ?></option><option><?= b('contact.form.15') ?></option>
          </select></label>
        <label class="full"><?= b('contact.form.16') ?><textarea name="msg" required></textarea></label>
        <button class="btn btn-o" type="submit" data-label="<?= ba('contact.form.17') ?>" data-busy="<?= ba('contact.form.27') ?>"><?= b('contact.form.17') ?></button>
        <span class="ferr full" role="alert" data-err-invalid="<?= ba('contact.form.28') ?>" data-err-rate="<?= ba('contact.form.29') ?>" data-err-server="<?= ba('contact.form.26') ?>"<?= isset($_GET['err']) ? '' : ' hidden' ?>><?= b('contact.form.28') ?></span>
        <span class="note full"><?= b('contact.form.30') ?> <?= e(site('line_id')) ?> <?= b('contact.form.31') ?> <a href="mailto:<?= e(site('email')) ?>"><?= e(site('email')) ?></a></span>
      </form>
      <div class="info">
        <div class="row"><span class="k"><?= b('contact.form.18') ?></span><span><?= e(company_name()) ?><br><?= address_html() ?></span></div>
        <div class="row"><span class="k"><?= b('contact.form.19') ?></span><a href="<?= e(tel_href_intl()) ?>"><?= e(phone_display()) ?></a></div>
        <div class="row"><span class="k"><?= b('contact.form.20') ?></span><span><?= e(site('fax')) ?></span></div>
        <div class="row"><span class="k"><?= b('contact.form.21') ?></span><a href="mailto:<?= e(site('email')) ?>"><?= e(site('email')) ?></a></div>
        <div class="row"><span class="k"><?= b('contact.form.22') ?></span><a href="<?= e(site('line_url')) ?>" target="_blank" rel="noopener"><?= e(site('line_id')) ?></a></div>
        <div class="row"><span class="k"><?= b('contact.form.23') ?></span><span><?= hours_html() ?></span></div>
      </div>
    </div>
  </div>
</section>

<section class="block" id="map">
  <div class="wrap">
    <div class="head">
      <span class="eyebrow"><?= b('contact.map.01') ?></span>
      <h2><?= b('contact.map.02') ?></h2>
    </div>
    <div class="map"><iframe title="<?= ba('contact.map.04') ?>" src="https://www.google.com/maps?q=L.Y.+Industries+124+Soi+Ram+Inthra+109+Phraya+Suren+Rd+Bang+Chan+Khlong+Sam+Wa+Bangkok+10510&hl=th&z=16&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
    <div class="maprow"><span><?= address_html() ?></span><a class="btn btn-g" href="https://www.google.com/maps/search/?api=1&query=L.Y.+Industries+124+Soi+Ram+Inthra+109+Phraya+Suren+Rd+Bang+Chan+Khlong+Sam+Wa+Bangkok+10510" target="_blank" rel="noopener"><?= b('contact.map.03') ?></a></div>
  </div>
</section>

<?php require __DIR__ . '/includes/site-footer.php'; ?>

<script>
// Quote form → saved in the admin (api/v1/contact.php). If the server cannot take it, open the
// visitor's email app with everything filled in instead, so no request is lost.
(function(){
  var form=document.getElementById('qform'),btn=form.querySelector('button[type=submit]'),err=form.querySelector('.ferr'),sent=form.querySelector('.sent');
  if(form.hasAttribute('data-sent')){form.classList.add('is-sent');}
  function mailto(f){
    var body='ชื่อ: '+f.get('name')+'\nบริษัท: '+f.get('company')+'\nอีเมล: '+f.get('email')+'\nโทร: '+f.get('phone')+'\nสินค้า: '+f.get('product')+'\n\nรายละเอียด:\n'+f.get('msg');
    location.href='mailto:<?= e(site('email')) ?>?subject='+encodeURIComponent('ขอใบเสนอราคา — '+f.get('product'))+'&body='+encodeURIComponent(body);
  }
  function show(kind){err.textContent=err.getAttribute('data-err-'+kind);err.hidden=false;}
  form.addEventListener('submit',function(e){
    e.preventDefault();
    var f=new FormData(form);
    err.hidden=true;btn.disabled=true;btn.textContent=btn.getAttribute('data-busy');
    fetch(form.action,{method:'POST',body:f,headers:{'Accept':'application/json'}})
      .then(function(r){return r.json().catch(function(){return {ok:false,error:'server'};});})
      .then(function(res){
        if(res.ok){form.classList.add('is-sent');sent.hidden=false;sent.scrollIntoView({block:'center',behavior:'smooth'});return;}
        if(res.error==='invalid'||res.error==='rate'){show(res.error);}else{show('server');mailto(f);}
      })
      .catch(function(){show('server');mailto(f);})
      .then(function(){btn.disabled=false;btn.textContent=btn.getAttribute('data-label');});
  });
})();
</script>
<script src="<?= e(asset('assets/js/track.js')) ?>" defer></script>
</body>
</html>
