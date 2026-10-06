// Admin helpers: text filter, "untranslated only", character counters, unsaved-changes guard.
(() => {
  'use strict';

  const rows = [...document.querySelectorAll('[data-row]')];
  const filter = document.querySelector('[data-filter]');
  const onlyMissing = document.querySelector('[data-only-missing]');

  const applyFilter = () => {
    const q = (filter?.value || '').trim().toLowerCase();
    const missing = !!onlyMissing?.checked;
    rows.forEach(row => {
      const text = [...row.querySelectorAll('textarea')].map(t => t.value).join(' ').toLowerCase() + ' ' + row.textContent.toLowerCase();
      row.hidden = (q !== '' && !text.includes(q)) || (missing && row.dataset.missing !== '1');
    });
    document.querySelectorAll('[data-section]').forEach(sec => {
      sec.hidden = sec.querySelectorAll('[data-row]:not([hidden])').length === 0;
    });
  };
  filter?.addEventListener('input', applyFilter);
  onlyMissing?.addEventListener('change', applyFilter);

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
