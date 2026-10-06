// Admin helpers: text filter, "untranslated only", character counters, unsaved-changes guard.
(() => {
  'use strict';

  const rows = [...document.querySelectorAll('[data-row]')];
  const filter = document.querySelector('[data-filter]');
  const onlyMissing = document.querySelector('[data-only-missing]');

  // sub-menu: show one section of the page at a time ("all" = everything); remembered per page
  const subnav = document.querySelector('[data-subnav]');
  const secKey = subnav ? 'lyiweb-admin-section:' + subnav.dataset.page : null;
  let currentSec = 'all';
  try { currentSec = (secKey && sessionStorage.getItem(secKey)) || 'all'; } catch (err) { /* storage blocked */ }
  if (subnav && !subnav.querySelector(`[data-sec="${CSS.escape(currentSec)}"]`)) currentSec = 'all';

  const applyFilter = () => {
    const q = (filter?.value || '').trim().toLowerCase();
    const missing = !!onlyMissing?.checked;
    const bySection = q === '' && currentSec !== 'all';   // a search always looks through every section
    rows.forEach(row => {
      const text = [...row.querySelectorAll('textarea')].map(t => t.value).join(' ').toLowerCase() + ' ' + row.textContent.toLowerCase();
      row.hidden = (q !== '' && !text.includes(q)) || (missing && row.dataset.missing !== '1');
    });
    document.querySelectorAll('[data-section-key]').forEach(sec => {
      const key = sec.dataset.sectionKey;
      const outOfSection = bySection && key !== currentSec;
      sec.hidden = key === 'seo'
        ? outOfSection || q !== '' || missing
        : outOfSection || sec.querySelectorAll('[data-row]:not([hidden])').length === 0;
    });
    subnav?.querySelectorAll('[data-sec]').forEach(b => {
      const on = b.dataset.sec === (q === '' ? currentSec : 'all');
      b.classList.toggle('on', on);
      b.setAttribute('aria-pressed', String(on));
    });
  };
  filter?.addEventListener('input', applyFilter);
  onlyMissing?.addEventListener('change', applyFilter);
  subnav?.addEventListener('click', e => {
    const btn = e.target.closest('[data-sec]');
    if (!btn) return;
    currentSec = btn.dataset.sec;
    try { sessionStorage.setItem(secKey, currentSec); } catch (err) { /* storage blocked */ }
    if (filter) filter.value = '';
    applyFilter();
    // jump to the start of the content, just under the sticky toolbar
    const form = document.getElementById('blocks-form');
    if (form) window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 70, behavior: 'smooth' });
  });
  if (subnav) applyFilter();

  // character counters on SEO fields
  document.querySelectorAll('[data-count]').forEach(t => {
    // wrap textarea + counter so they occupy one grid cell together
    const wrap = document.createElement('div');
    const out = document.createElement('small');
    out.className = 'count';
    t.parentNode.insertBefore(wrap, t);
    wrap.append(t, out);
    const update = () => { out.textContent = `${t.value.length} / ${t.maxLength} ตัวอักษร`; };
    t.addEventListener('input', update);
    update();
  });

  // highlight changed fields and warn before leaving with unsaved changes
  let dirty = false;
  document.querySelectorAll('form.form textarea, form.form input:not([type=hidden])').forEach(el => {
    const initial = el.value;
    el.addEventListener('input', () => {
      el.classList.toggle('changed', el.value !== initial);
      dirty = true;
    });
  });
  // keep the scroll position across "save → reload" instead of jumping to the top
  const scrollKey = 'lyiweb-admin-scroll:' + location.pathname + location.search.replace(/[?&]_=\d+/, '');
  document.querySelectorAll('form.form').forEach(f => f.addEventListener('submit', () => {
    dirty = false;
    try { sessionStorage.setItem(scrollKey, String(window.scrollY)); } catch (err) { /* storage blocked */ }
  }));
  let saved = null;
  try { saved = sessionStorage.getItem(scrollKey); sessionStorage.removeItem(scrollKey); } catch (err) { /* storage blocked */ }
  const firstError = document.querySelector('.has-error');
  if (firstError) {
    // validation failed: go straight to the first field that needs fixing
    firstError.scrollIntoView({ block: 'center' });
    firstError.querySelector('input, textarea')?.focus({ preventScroll: true });
  } else if (saved !== null) {
    window.scrollTo(0, parseInt(saved, 10) || 0);
  }
  // after a save the confirmation floats in the corner so it is visible wherever we are
  if (saved !== null || firstError) {
    document.querySelectorAll('[data-flash], .main > .flash-error').forEach(el => {
      el.classList.add('toast');
      setTimeout(() => el.classList.add('toast-hide'), 4000);
    });
  }
  window.addEventListener('beforeunload', e => {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });
})();
