<?php
declare(strict_types=1);

/**
 * The real public page rendered for the "edit on the page" view of blocks.php (shown in an iframe).
 * Every b() text is wrapped in <span data-lyiweb-b="page.section.nn">; a small script reports
 * clicks to the parent window and takes "highlight" / "live text" messages back.
 */

require __DIR__ . '/../includes/admin/init.php';

admin_require('content.translate');
header('X-Frame-Options: SAMEORIGIN');   // init.php sends DENY; this page is meant to be framed by blocks.php

$files = ['home' => 'index.php', 'about' => 'about.php', 'catalog' => 'catalog.php', 'contact' => 'contact.php', 'footer' => 'catalog.php'];
$page = (string) ($_GET['page'] ?? 'home');
if (!isset($files[$page])) {
    $page = 'home';
}

define('LYIWEB_PREVIEW', true);
ob_start();
require APP_ROOT . '/' . $files[$page];
$html = (string) ob_get_clean();

$inject = <<<'HTML'
<base href="../">
<style>
  [data-lyiweb-b] { cursor: pointer; border-radius: 3px; outline: 2px dashed transparent; outline-offset: 2px; transition: outline-color .12s, background-color .12s; }
  [data-lyiweb-b]:hover { outline-color: rgba(255, 90, 31, .85); }
  [data-lyiweb-b].lyiweb-on { outline: 3px solid #ff5a1f; background-color: rgba(255, 90, 31, .18); }
  html { scroll-behavior: auto !important; }
</style>
<script>
(() => {
  'use strict';
  const origin = location.origin;
  const send = msg => parent.postMessage(msg, origin);
  const toggles = '[data-menu-toggle],[data-faq-toggle],[data-swatch]';
  // clicks pick a text to edit; links and forms never navigate away from the preview
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-lyiweb-b]');
    if (b) {
      e.preventDefault();
      e.stopPropagation();
      send({ type: 'pick', key: b.dataset.lyiwebB });
      return;
    }
    if (e.target.closest('a, button[type=submit]')) e.preventDefault();
    if (!e.target.closest(toggles)) send({ type: 'miss' });
  }, true);
  document.addEventListener('submit', e => e.preventDefault(), true);
  window.addEventListener('message', e => {
    if (e.origin !== origin || !e.data) return;
    const els = [...document.querySelectorAll(`[data-lyiweb-b="${CSS.escape(e.data.key || '')}"]`)];
    if (e.data.type === 'focus') {
      document.querySelectorAll('.lyiweb-on').forEach(el => el.classList.remove('lyiweb-on'));
      els.forEach(el => el.classList.add('lyiweb-on'));
      const el = els.find(x => x.getClientRects().length) || els[0];
      // scroll this page only — scrollIntoView would also scroll the admin page around the frame
      if (el && e.data.scroll !== false) {
        const r = el.getBoundingClientRect();
        window.scrollTo(0, Math.max(0, r.top + window.scrollY - (innerHeight - r.height) / 2));
      }
    } else if (e.data.type === 'text') {
      els.forEach(el => { el.textContent = e.data.value; });
    }
  });
  addEventListener('load', () => send({ type: 'ready' }));
})();
</script>
HTML;
$html = preg_replace('/<head>/i', '<head>' . $inject, $html, 1) ?? $html;

echo $html;
