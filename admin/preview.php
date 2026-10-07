<?php
declare(strict_types=1);

/**
 * The real public page rendered for the "edit on the page" views (blocks.php, lists.php — shown
 * in an iframe). Page texts from b() come wrapped in <span data-lyiweb-b="page.section.nn">; list
 * texts carry an invisible zero-width tag (content_preview_mark) that the script below turns into
 * data-lyiweb-item="list#id" on their element. Clicks are reported to the parent window, which
 * sends back "highlight" and "live text" messages.
 */

require __DIR__ . '/../includes/admin/init.php';

admin_require('content.translate');
header('X-Frame-Options: SAMEORIGIN');   // init.php sends DENY; this page is meant to be framed by the admin

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
  [data-lyiweb-b], [data-lyiweb-item] { cursor: pointer; border-radius: 3px; outline: 2px dashed transparent; outline-offset: 2px; transition: outline-color .12s, background-color .12s; }
  [data-lyiweb-b]:hover, [data-lyiweb-item]:hover { outline-color: rgba(255, 90, 31, .85); }
  .lyiweb-on { outline: 3px solid #ff5a1f !important; background-color: rgba(255, 90, 31, .18); }
  html { scroll-behavior: auto !important; }
</style>
<script>
(() => {
  'use strict';
  const origin = location.origin;
  const send = msg => parent.postMessage(msg, origin);
  const toggles = '[data-menu-toggle],[data-faq-toggle],[data-swatch]';
  const TAG = /⁣([​‌‍⁠]+)⁤/g;
  const TAG_ESC = /\\u(2063|2064|200[bcdBCD]|2060)/g;
  const DIGIT = { '​': 0, '‌': 1, '‍': 2, '⁠': 3 };
  const texts = {};   // "list#id.field" → text nodes showing that field

  // turn the invisible list tags into data-lyiweb-item on the element holding the text
  const decode = () => {
    const marks = window.LYIWEB_MARKS || [];
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    const found = [];
    for (let n = walker.nextNode(); n; n = walker.nextNode()) if (n.nodeValue.includes('⁣')) found.push(n);
    found.forEach(node => {
      const tags = [...node.nodeValue.matchAll(TAG)];
      node.nodeValue = node.nodeValue.replace(TAG, '');
      const first = marks[[...tags[0][1]].reduce((v, c) => v * 4 + DIGIT[c], 0)];
      if (!first) return;
      const [list, id, field] = first;
      const el = node.parentElement;
      if (el && !el.dataset.lyiwebItem) { el.dataset.lyiwebItem = list + '#' + id; el.dataset.lyiwebField = field; }
      if (tags.length === 1) (texts[`${list}#${id}.${field}`] ||= []).push(node);
    });
    // tags also landed in attributes (alt, aria-label, the colour buttons' JSON) — clean them
    document.querySelectorAll('*').forEach(el => {
      for (const a of el.attributes) {
        if (a.value.includes('⁣') || /\\u2063/i.test(a.value)) el.setAttribute(a.name, a.value.replace(TAG, '').replace(TAG_ESC, ''));
      }
    });
  };

  // where to scroll so the element is really on screen: scroll-driven scenes of the homepage
  // (process steps, flying tiles) only show an item at one scroll position
  const targetY = el => {
    const stepEl = el.closest('[data-step-text],[data-step-node],[data-step-bg],[data-step-media]');
    const proc = document.getElementById('processContainer');
    if (stepEl && proc) {
      const i = parseInt(stepEl.dataset.stepText ?? stepEl.dataset.stepNode ?? stepEl.dataset.stepBg ?? stepEl.dataset.stepMedia, 10) || 0;
      const count = document.querySelectorAll('[data-step-text]').length || 1;
      const top = proc.getBoundingClientRect().top + scrollY;
      return top + ((i + 0.5) / count) * (proc.offsetHeight - innerHeight);
    }
    const asm = el.closest('#assembleContainer');
    if (asm) return asm.getBoundingClientRect().top + scrollY + 0.75 * (asm.offsetHeight - innerHeight);
    const r = el.getBoundingClientRect();
    if (r.top >= 40 && r.bottom <= innerHeight - 40) return null;   // already on screen
    return r.top + scrollY - (innerHeight - r.height) / 2;
  };
  const highlight = (els, scroll) => {
    document.querySelectorAll('.lyiweb-on').forEach(el => el.classList.remove('lyiweb-on'));
    els.forEach(el => el.classList.add('lyiweb-on'));
    const el = els.find(x => x.getClientRects().length && !x.closest('[data-step-node]')) || els[0];
    // scroll this page only — scrollIntoView would also scroll the admin page around the frame
    if (el && scroll !== false) {
      const y = targetY(el);
      if (y !== null) window.scrollTo(0, Math.max(0, y));
    }
  };

  // clicks pick a text to edit; links and forms never navigate away from the preview
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-lyiweb-b]');
    const it = b ? null : e.target.closest('[data-lyiweb-item]');
    if (b || it) {
      e.preventDefault();
      e.stopPropagation();
      if (b) send({ type: 'pick', key: b.dataset.lyiwebB });
      else send({ type: 'pickItem', ref: it.dataset.lyiwebItem, field: it.dataset.lyiwebField });
      return;
    }
    if (e.target.closest('a, button[type=submit]')) e.preventDefault();
    if (!e.target.closest(toggles)) send({ type: 'miss' });
  }, true);
  document.addEventListener('submit', e => e.preventDefault(), true);

  window.addEventListener('message', e => {
    if (e.origin !== origin || !e.data) return;
    const d = e.data;
    if (d.type === 'focus') {
      highlight([...document.querySelectorAll(`[data-lyiweb-b="${CSS.escape(d.key || '')}"]`)], d.scroll);
    } else if (d.type === 'text') {
      document.querySelectorAll(`[data-lyiweb-b="${CSS.escape(d.key || '')}"]`).forEach(el => { el.textContent = d.value; });
    } else if (d.type === 'focusItem') {
      const all = [...document.querySelectorAll(`[data-lyiweb-item="${CSS.escape(d.ref || '')}"]`)];
      const exact = d.field ? all.filter(el => el.dataset.lyiwebField === d.field) : [];
      highlight(exact.length ? exact : all, d.scroll);
    } else if (d.type === 'itemText') {
      (texts[`${d.ref}.${d.field}`] || []).forEach(n => { n.nodeValue = d.value; });
    }
  });
  addEventListener('DOMContentLoaded', decode);
  addEventListener('load', () => send({ type: 'ready' }));
})();
</script>
HTML;
$marks = '<script>window.LYIWEB_MARKS = ' . json_encode($GLOBALS['lyiweb_preview_marks'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ';</script>';
$html = preg_replace('/<head>/i', '<head>' . $inject, $html, 1) ?? $html;
$html = preg_replace('#</body>#i', $marks . '</body>', $html, 1) ?? $html;

echo $html;
