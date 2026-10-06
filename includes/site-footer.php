<?php
/** Four-column footer used by catalog and contact. */
?>
<footer class="foot4">
  <div class="wrap">
    <div class="grid">
      <div><b>L.Y. INDUSTRIES CO., LTD.</b><?= e(site('company_th')) ?><br><?= address_th_html() ?></div>
      <div><span class="t">OPERATING HOURS</span><?= hours_th_html() ?><br><?= e(site('email')) ?></div>
      <div><span class="t">SITEMAP</span><nav>
        <a href="index.php">หน้าแรก</a><a href="catalog.php">แคตาล็อกสินค้า</a><a href="<?= e(site('trimrite_url')) ?>" target="_blank" rel="noopener">TRIMRITE®</a><a href="about.php">เกี่ยวกับเรา</a><a href="contact.php">ติดต่อเรา</a><a href="index.php#faq">คำถามพบบ่อย</a></nav></div>
      <div><span class="t">QUALITY STANDARDS</span>OEKO-TEX® Standard 100 Certified<br>สอดคล้องข้อกำหนด RSL ของแบรนด์กีฬาระดับโลก</div>
    </div>
    <div class="copy"><span>© 2026 L.Y. INDUSTRIES CO., LTD. ALL RIGHTS RESERVED.</span><span>BANGKOK, THAILAND · EST. 1978</span></div>
  </div>
</footer>
