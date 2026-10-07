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
    if (f.hasAttribute('data-leave')) return;   // this form goes to another page after saving
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
  // role scopes: "all" covers every page, "whole page" covers its sections → grey out what is already included
  const tree = document.querySelector('[data-scope-tree]');
  if (tree) {
    const allBox = tree.querySelector('[data-scope-all]');
    const contentPerms = [...document.querySelectorAll('[data-content-perm]')];
    const syncTree = () => {
      const all = !!allBox?.checked;
      tree.querySelectorAll('[data-scope-page]').forEach(page => {
        const whole = page.querySelector('[data-scope-whole]');
        whole.disabled = all;
        page.querySelectorAll('.scope-secs input').forEach(i => { i.disabled = all || whole.checked; });
      });
      tree.classList.toggle('is-off', contentPerms.length > 0 && !contentPerms.some(c => c.checked));
    };
    tree.addEventListener('change', syncTree);
    contentPerms.forEach(c => c.addEventListener('change', syncTree));
    if (!tree.disabled) syncTree();
  }

  // user picker: searchable dropdown grouped by department (A→Z), pick → that user's permissions page
  const picker = document.querySelector('[data-user-picker]');
  if (picker) {
    const input = picker.querySelector('[role=combobox]');
    const list = picker.querySelector('[role=listbox]');
    let users = [];
    try { users = JSON.parse(picker.querySelector('[data-user-list]').textContent); } catch (err) { /* keep the plain search form */ }
    let options = [];
    let active = -1;
    const norm = v => v.toLocaleLowerCase('th');
    const esc = v => v.replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const setActive = i => {
      options.forEach(o => o.classList.remove('on'));
      active = i;
      if (options[i]) {
        options[i].classList.add('on');
        options[i].scrollIntoView({ block: 'nearest' });
        input.setAttribute('aria-activedescendant', options[i].id);
      } else {
        input.removeAttribute('aria-activedescendant');
      }
    };
    const render = () => {
      const q = norm(input.value.trim());
      const hits = users.filter(u => q === '' || norm(`${u.name} ${u.user} ${u.dept}`).includes(q));
      let html = '';
      let dept = null;
      hits.forEach(u => {
        if (u.dept !== dept) {
          dept = u.dept;
          html += `<div class="picker-group" role="presentation">${esc(dept)}<small>${hits.filter(h => h.dept === dept).length}</small></div>`;
        }
        html += `<a class="picker-opt" id="pick-${u.id}" role="option" href="?id=${u.id}"><span>${esc(u.name)}</span>`
              + `${u.ok ? '<em>login ได้</em>' : ''}</a>`;
      });
      list.innerHTML = html || '<div class="picker-empty">ไม่พบผู้ใช้</div>';
      options = [...list.querySelectorAll('.picker-opt')];
      setActive(q === '' ? -1 : 0);
    };
    const open = () => { render(); list.hidden = false; input.setAttribute('aria-expanded', 'true'); };
    const close = () => { list.hidden = true; input.setAttribute('aria-expanded', 'false'); setActive(-1); };
    if (users.length) {
      input.addEventListener('focus', open);
      input.addEventListener('click', open);
      input.addEventListener('input', open);
      input.addEventListener('keydown', e => {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
          e.preventDefault();
          if (list.hidden) open();
          const n = options.length;
          if (n) setActive(e.key === 'ArrowDown' ? (active + 1) % n : (active - 1 + n) % n);
        } else if (e.key === 'Enter' && !list.hidden && options[active]) {
          e.preventDefault();
          location.href = options[active].href;
        } else if (e.key === 'Escape') {
          close();
        }
      });
      document.addEventListener('click', e => { if (!picker.contains(e.target)) close(); });
    }
  }

  // phone/tablet: ☰ opens the sidebar as a drawer; backdrop, Esc or picking a page closes it
  const navBtn = document.querySelector('[data-nav-toggle]');
  if (navBtn) {
    const root = document.documentElement;
    const setNav = open => {
      root.classList.toggle('nav-open', open);
      navBtn.setAttribute('aria-expanded', String(open));
      if (open) document.getElementById('admin-nav')?.focus({ preventScroll: true });   // keyboard starts inside the drawer
    };
    navBtn.addEventListener('click', () => setNav(!root.classList.contains('nav-open')));
    document.querySelector('[data-nav-close]')?.addEventListener('click', () => setNav(false));
    document.querySelectorAll('#admin-nav a').forEach(a => a.addEventListener('click', () => setNav(false)));
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && root.classList.contains('nav-open')) { setNav(false); navBtn.focus(); }
    });
  }

  // list editor (lists.php): reorder, show/hide, add, delete, image pick — all saved with one button
  const listForm = document.querySelector('[data-list-form]');
  if (listForm) {
    const box = listForm.querySelector('[data-items]');
    const tpl = listForm.querySelector('template[data-item-template]');
    let seq = 0;
    const renumber = () => {
      box.querySelectorAll('[data-item]').forEach((item, i) => {
        item.querySelector('[data-item-n]').textContent = String(i + 1);
        item.querySelector('[data-order]').value = String((i + 1) * 10);
      });
    };
    const titleOf = item => {
      const src = [...item.querySelectorAll('[data-title-src]')].find(el => el.value.trim() !== '');
      item.querySelector('[data-item-title]').textContent = src ? src.value.trim().slice(0, 70) : '(รายการใหม่)';
    };
    box.addEventListener('click', e => {
      const item = e.target.closest('[data-item]');
      if (!item) return;
      const move = e.target.closest('[data-move]');
      if (move) {
        e.preventDefault();   // buttons sit in <summary>: do not open/close the card
        const sib = move.dataset.move === '-1' ? item.previousElementSibling : item.nextElementSibling;
        if (sib) {
          move.dataset.move === '-1' ? box.insertBefore(item, sib) : box.insertBefore(sib, item);
          renumber();
          dirty = true;
          move.focus();
        }
      } else if (e.target.closest('[data-remove]')) {
        if (!/^\d+$/.test(item.dataset.key)) { item.remove(); renumber(); return; }   // never saved: just drop it
        item.classList.add('is-deleted');
        item.querySelector('[data-delete]').value = '1';
        item.open = false;
        dirty = true;
      } else if (e.target.closest('[data-undo]')) {
        item.classList.remove('is-deleted');
        item.querySelector('[data-delete]').value = '0';
      }
    });
    box.addEventListener('change', e => {
      const item = e.target.closest('[data-item]');
      if (e.target.matches('[data-active]')) item.classList.toggle('is-hidden', !e.target.checked);
      if (e.target.matches('[data-color-pick]')) {
        const t = e.target.parentElement.querySelector('[data-color-text]');
        t.value = e.target.value;
        dirty = true;
      }
      if (e.target.matches('[data-img-input]')) shrinkImage(e.target);
    });
    box.addEventListener('input', e => {
      const item = e.target.closest('[data-item]');
      if (e.target.matches('[data-title-src]')) titleOf(item);
      if (e.target.matches('[data-color-text]') && /^#([0-9a-f]{3}){1,2}$/i.test(e.target.value)) {
        const v = e.target.value.toLowerCase();
        e.target.parentElement.querySelector('[data-color-pick]').value = v.length === 4 ? '#' + [...v.slice(1)].map(c => c + c).join('') : v;
      }
      dirty = true;
    });
    listForm.querySelector('[data-add-item]')?.addEventListener('click', () => {
      seq += 1;
      const html = tpl.innerHTML.replaceAll('__KEY__', 'n' + seq);
      box.insertAdjacentHTML('beforeend', html);
      const item = box.lastElementChild;
      item.open = true;
      renumber();
      dirty = true;
      item.scrollIntoView({ behavior: 'smooth', block: 'center' });
      item.querySelector('input:not([type=hidden]):not([type=checkbox]), textarea')?.focus({ preventScroll: true });
    });

    // filter by text / untranslated
    const lf = listForm.querySelector('[data-filter]');
    const lm = listForm.querySelector('[data-only-missing]');
    const applyListFilter = () => {
      const q = (lf?.value || '').trim().toLowerCase();
      box.querySelectorAll('[data-item]').forEach(item => {
        const text = [...item.querySelectorAll('input, textarea, select')].map(el => el.value).join(' ').toLowerCase();
        item.hidden = (q !== '' && !text.includes(q)) || (!!lm?.checked && !item.querySelector('[data-missing]'));
        if (q !== '' && !item.hidden) item.open = true;
      });
    };
    lf?.addEventListener('input', applyListFilter);
    lm?.addEventListener('change', applyListFilter);

    // shrink photos in the browser before upload (servers may accept only ~2 MB), WebP when supported
    const MAX_SIDE = 2400;
    const shrinkImage = async input => {
      const file = input.files && input.files[0];
      const field = input.closest('[data-img-field]');
      const note = field.querySelector('[data-img-note]');
      if (!file) return;
      const showPreview = blob => {
        const url = URL.createObjectURL(blob);
        let img = field.querySelector('img[data-img-preview]');
        if (!img) {
          img = document.createElement('img');
          img.dataset.imgPreview = '';
          field.querySelector('[data-img-preview]')?.replaceWith(img);
        }
        img.src = url;
      };
      const kb = n => (n / 1024 / 1024 >= 1 ? (n / 1024 / 1024).toFixed(1) + ' MB' : Math.round(n / 1024) + ' KB');
      try {
        const bmp = await createImageBitmap(file);
        const scale = Math.min(1, MAX_SIDE / Math.max(bmp.width, bmp.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bmp.width * scale);
        canvas.height = Math.round(bmp.height * scale);
        canvas.getContext('2d').drawImage(bmp, 0, 0, canvas.width, canvas.height);
        let blob = await new Promise(r => canvas.toBlob(r, 'image/webp', 0.86));
        if (!blob || blob.type !== 'image/webp') blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', 0.86));
        if (blob && blob.size < file.size) {
          const ext = blob.type === 'image/webp' ? 'webp' : 'jpg';
          const dt = new DataTransfer();
          dt.items.add(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.' + ext, { type: blob.type }));
          input.files = dt.files;
          note.textContent = `ย่อแล้ว ${kb(file.size)} → ${kb(blob.size)} (${canvas.width}×${canvas.height}) — จะอัปโหลดเมื่อกดบันทึก`;
        } else {
          note.textContent = `${kb(file.size)} — จะอัปโหลดเมื่อกดบันทึก`;
        }
        showPreview(input.files[0]);
      } catch (err) {
        note.textContent = 'ย่อรูปในเบราว์เซอร์ไม่ได้ — จะส่งไฟล์เดิม (' + kb(file.size) + ')';
        showPreview(file);
      }
      dirty = true;
    };
  }

  // destructive buttons ask first
  document.querySelectorAll('[data-confirm]').forEach(btn => btn.addEventListener('click', e => {
    if (!window.confirm(btn.dataset.confirm)) e.preventDefault();
  }));

  window.addEventListener('beforeunload', e => {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });
})();
