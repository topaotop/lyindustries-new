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
  document.querySelectorAll('form.form').forEach(f => f.addEventListener('submit', () => { dirty = false; }));
  window.addEventListener('beforeunload', e => {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });
})();
