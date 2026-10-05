<?php
/**
 * Top bar for the sub-pages (about, catalog, contact).
 *
 * @var string $activeNav  key from site_nav() to highlight
 * @var string $quoteHref  target of the orange "ขอใบเสนอราคา" button
 */
$quoteHref ??= 'contact.php#form';
?>
<header class="nav">
  <div class="wrap">
    <a class="brand" href="index.php"><img class="mark" src="assets/img/logo-lyi.svg" alt="L.Y. Industries" width="40" height="40"><span><b>L.Y. INDUSTRIES</b><small>BANGKOK · EST. 1978</small></span></a>
    <nav>
<?php foreach (site_nav() as $link): ?>
      <a<?= $link['key'] === ($activeNav ?? '') ? ' class="on"' : '' ?> href="<?= e($link['href']) ?>"<?= external_attrs($link) ?>><?= e($link['label']) ?></a>
<?php endforeach; ?>
    </nav>
    <a class="btn btn-o" href="<?= e($quoteHref) ?>">ขอใบเสนอราคา →</a>
  </div>
</header>
