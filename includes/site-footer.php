<?php
/** Four-column footer used by catalog and contact. */
?>
<footer class="foot4">
  <div class="wrap">
    <div class="grid">
      <div><b><?= b('footer.main.01') ?></b><?= e(site('company_th')) ?><br><?= address_th_html() ?></div>
      <div><span class="t"><?= b('footer.main.02') ?></span><?= hours_th_html() ?><br><?= e(site('email')) ?></div>
      <div><span class="t"><?= b('footer.main.03') ?></span><nav>
        <a href="index.php"><?= b('footer.main.04') ?></a><a href="catalog.php"><?= b('footer.main.05') ?></a><a href="<?= e(site('trimrite_url')) ?>" target="_blank" rel="noopener"><?= b('footer.main.06') ?></a><a href="about.php"><?= b('footer.main.07') ?></a><a href="contact.php"><?= b('footer.main.08') ?></a><a href="index.php#faq"><?= b('footer.main.09') ?></a></nav></div>
      <div><span class="t"><?= b('footer.main.10') ?></span><?= b('footer.main.11') ?><br><?= b('footer.main.12') ?></div>
    </div>
    <div class="copy"><span><?= b('footer.main.13') ?></span><span><?= b('footer.main.14') ?></span></div>
  </div>
</footer>
