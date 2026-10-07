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
    const pane = document.querySelector('[data-edit-pane]');
    if (document.documentElement.classList.contains('blocks-visual') && pane) pane.scrollTo({ top: 0 });
    else if (form) window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 70, behavior: 'smooth' });
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

  // blocks.php "เห็นหน้าเว็บ" view: the real page on the left, click a text → its fields on the right
  const bform = document.querySelector('[data-blocks-form]');
  if (bform) {
    const root = document.documentElement;
    const page = bform.dataset.page;
    const frame = bform.querySelector('[data-preview]');
    const box = bform.querySelector('[data-preview-box]');
    const pane = bform.querySelector('[data-edit-pane]');
    const note = bform.querySelector('[data-preview-note]');
    const wide = window.matchMedia('(min-width: 1100px)');
    const DESIGN_W = 1280;   // the page is laid out at desktop width, then scaled to fit
    const store = (k, v) => { try { v === undefined ? sessionStorage.removeItem(k) : sessionStorage.setItem(k, v); } catch (err) { /* blocked */ } };
    const read = k => { try { return sessionStorage.getItem(k); } catch (err) { return null; } };
    let mode = 'visual';
    try { mode = localStorage.getItem('lyiweb-blocks-view') || 'visual'; } catch (err) { /* blocked */ }
    let ready = false;
    const post = msg => { if (ready) frame.contentWindow.postMessage(msg, location.origin); };
    const fit = () => {
      if (!root.classList.contains('blocks-visual')) return;
      const scale = box.clientWidth / DESIGN_W;
      frame.style.width = DESIGN_W + 'px';
      frame.style.height = Math.ceil(box.clientHeight / scale) + 'px';
      frame.style.transform = `scale(${scale})`;
    };
    const applyView = () => {
      const visual = wide.matches && mode === 'visual';
      root.classList.toggle('blocks-visual', visual);
      bform.querySelectorAll('[data-view]').forEach(b => b.classList.toggle('on', b.dataset.view === mode));
      if (visual && !frame.getAttribute('src')) frame.src = frame.dataset.src;
      fit();
    };
    bform.querySelector('[data-view-switch]').addEventListener('click', e => {
      const b = e.target.closest('[data-view]');
      if (!b) return;
      mode = b.dataset.view;
      try { localStorage.setItem('lyiweb-blocks-view', mode); } catch (err) { /* blocked */ }
      applyView();
    });
    wide.addEventListener('change', applyView);
    window.addEventListener('resize', fit);

    let noteTimer = 0;
    const say = html => {
      note.innerHTML = html;
      note.hidden = false;
      clearTimeout(noteTimer);
      noteTimer = setTimeout(() => { note.hidden = true; }, 7000);
    };
    const rowOf = key => bform.querySelector(`[data-row][data-key="${CSS.escape(key)}"]`);
    // open the fields of one text: its section, scrolled into view, highlighted, cursor in the box
    const pick = (key, fromPreview) => {
      const row = rowOf(key);
      if (!row) {
        const other = key.split('.')[0];
        say(other !== page
          ? `ข้อความนี้อยู่ในแท็บอื่น — <a href="?page=${encodeURIComponent(other)}&focus=${encodeURIComponent(key)}">ไปแก้ที่แท็บนั้น →</a>`
          : 'คุณไม่มีสิทธิ์แก้ข้อความนี้');
        return;
      }
      note.hidden = true;
      const sec = row.closest('[data-section-key]').dataset.sectionKey;
      const btn = bform.querySelector(`[data-subnav] [data-sec="${CSS.escape(sec)}"]`);
      if (btn && !btn.classList.contains('on')) btn.click();
      bform.querySelectorAll('.row.is-picked').forEach(r => r.classList.remove('is-picked'));
      row.classList.add('is-picked');
      // scroll only the edit pane (scrollIntoView would also move the whole admin page)
      if (root.classList.contains('blocks-visual')) {
        pane.scrollTop += row.getBoundingClientRect().top - pane.getBoundingClientRect().top - (pane.clientHeight - row.offsetHeight) / 2;
      } else {
        row.scrollIntoView({ block: 'center' });
      }
      const ta = row.querySelector('[data-th]:not([readonly])') || row.querySelector('textarea:not([readonly])');
      if (ta) { ta.focus({ preventScroll: true }); ta.setSelectionRange(ta.value.length, ta.value.length); }
      store('lyiweb-blocks-last:' + page, key);
      post({ type: 'focus', key, scroll: !fromPreview });
    };
    window.addEventListener('message', e => {
      if (e.origin !== location.origin || e.source !== frame.contentWindow || !e.data) return;
      if (e.data.type === 'ready') {
        ready = true;
        fit();
        const want = new URLSearchParams(location.search).get('focus') || read('lyiweb-blocks-last:' + page);
        if (want && rowOf(want)) pick(want, false);
        else if (page === 'footer') post({ type: 'focus', key: bform.querySelector('[data-row]')?.dataset.key || '' });
      } else if (e.data.type === 'pick') {
        pick(e.data.key, true);
      } else if (e.data.type === 'pickItem') {
        const [list, id] = String(e.data.ref).split('#');
        say(`นี่คือรายการ (การ์ด/FAQ/ขั้นตอน …) — <a href="lists.php?list=${encodeURIComponent(list)}&focus=${encodeURIComponent(id)}">แก้ที่ รายการ & รูปภาพ →</a>`);
      } else if (e.data.type === 'miss') {
        say('ตรงนี้ไม่ได้แก้ในหน้านี้ — เบอร์โทร อีเมล ที่อยู่ แก้ที่ <a href="settings.php">ข้อมูลติดต่อ & ลิงก์</a> · รูปภาพและเมนูด้านบนยังแก้ไม่ได้');
      }
    });
    // typing → preview updates live; moving into a field → its text is highlighted on the page
    bform.addEventListener('input', e => {
      const row = e.target.closest('[data-row]');
      if (row && e.target.matches('[data-th]')) post({ type: 'text', key: row.dataset.key, value: e.target.value });
    });
    bform.addEventListener('focusin', e => {
      const row = e.target.closest('[data-row]');
      if (!row || row.classList.contains('is-picked')) return;
      bform.querySelectorAll('.row.is-picked').forEach(r => r.classList.remove('is-picked'));
      row.classList.add('is-picked');
      store('lyiweb-blocks-last:' + page, row.dataset.key);
      post({ type: 'focus', key: row.dataset.key, scroll: true });
    });
    // keep the edit pane's scroll position across save → reload
    const paneKey = 'lyiweb-blocks-pane:' + page;
    bform.addEventListener('submit', () => store(paneKey, String(pane.scrollTop)));
    applyView();
    const paneTop = read(paneKey);
    if (paneTop !== null && root.classList.contains('blocks-visual')) pane.scrollTop = parseInt(paneTop, 10) || 0;
    store(paneKey);

  }

  // lists.php "เห็นหน้าเว็บ" view: the real page on the left, click an item → its card on the right
  const lform = document.querySelector('[data-list-form]');
  if (lform) {
    const root = document.documentElement;
    const list = lform.dataset.list;
    const names = JSON.parse(lform.dataset.listNames || '{}');
    const frame = lform.querySelector('[data-preview]');
    const box = lform.querySelector('[data-preview-box]');
    const pane = lform.querySelector('[data-edit-pane]');
    const note = lform.querySelector('[data-preview-note]');
    const wide = window.matchMedia('(min-width: 1100px)');
    const DESIGN_W = 1280;
    const store = (k, v) => { try { v === undefined ? sessionStorage.removeItem(k) : sessionStorage.setItem(k, v); } catch (err) { /* blocked */ } };
    const read = k => { try { return sessionStorage.getItem(k); } catch (err) { return null; } };
    let mode = 'visual';
    try { mode = localStorage.getItem('lyiweb-blocks-view') || 'visual'; } catch (err) { /* blocked */ }
    let ready = false;
    const post = msg => { if (ready) frame.contentWindow.postMessage(msg, location.origin); };
    const visual = () => root.classList.contains('blocks-visual');
    const fit = () => {
      if (!visual()) return;
      const scale = box.clientWidth / DESIGN_W;
      frame.style.width = DESIGN_W + 'px';
      frame.style.height = Math.ceil(box.clientHeight / scale) + 'px';
      frame.style.transform = `scale(${scale})`;
    };
    const applyView = () => {
      root.classList.toggle('blocks-visual', wide.matches && mode === 'visual');
      lform.querySelectorAll('[data-view]').forEach(b => b.classList.toggle('on', b.dataset.view === mode));
      if (visual() && !frame.getAttribute('src')) frame.src = frame.dataset.src;
      fit();
    };
    lform.querySelector('[data-view-switch]').addEventListener('click', e => {
      const b = e.target.closest('[data-view]');
      if (!b) return;
      mode = b.dataset.view;
      try { localStorage.setItem('lyiweb-blocks-view', mode); } catch (err) { /* blocked */ }
      applyView();
    });
    wide.addEventListener('change', applyView);
    window.addEventListener('resize', fit);

    let noteTimer = 0;
    const say = html => {
      note.innerHTML = html;
      note.hidden = false;
      clearTimeout(noteTimer);
      noteTimer = setTimeout(() => { note.hidden = true; }, 7000);
    };
    const cardOf = ref => lform.querySelector(`[data-items] [data-ref="${CSS.escape(ref)}"]`);
    const mark = card => {
      lform.querySelectorAll('.item.is-picked').forEach(c => c.classList.remove('is-picked'));
      card.classList.add('is-picked');
      store('lyiweb-lists-last:' + list, card.dataset.ref);
    };
    // open one item: card opened and scrolled into view, cursor in the field that was clicked
    const pickItem = (ref, field, fromPreview) => {
      const [itemList, id] = String(ref).split('#');
      if (itemList !== list) {
        say(`รายการนี้อยู่ในแท็บ "${names[itemList] || itemList}" — <a href="?list=${encodeURIComponent(itemList)}&focus=${encodeURIComponent(id)}">ไปแก้ที่แท็บนั้น →</a>`);
        return;
      }
      const card = cardOf(ref);
      if (!card) { say('รายการนี้ไม่อยู่ในฟอร์ม (อาจเพิ่งถูกลบหรือคุณไม่มีสิทธิ์)'); return; }
      note.hidden = true;
      card.hidden = false;
      card.open = true;
      mark(card);
      if (visual()) pane.scrollTop += card.getBoundingClientRect().top - pane.getBoundingClientRect().top - 60;
      else card.scrollIntoView({ block: 'start' });
      const input = (field && card.querySelector(`[data-live="${CSS.escape(field)}"]:not([readonly])`))
        || card.querySelector('input:not([type=hidden]):not([type=checkbox]):not([readonly]), textarea:not([readonly])');
      input?.focus({ preventScroll: true });
      post({ type: 'focusItem', ref, scroll: !fromPreview });
    };
    window.addEventListener('message', e => {
      if (e.origin !== location.origin || e.source !== frame.contentWindow || !e.data) return;
      const d = e.data;
      if (d.type === 'ready') {
        ready = true;
        fit();
        const last = read('lyiweb-lists-last:' + list);
        const ref = focusId ? `${list}#${focusId}` : (last && cardOf(last) ? last : lform.querySelector('[data-items] [data-ref]')?.dataset.ref);
        if (ref) post({ type: 'focusItem', ref, scroll: true });
      } else if (d.type === 'pickItem') {
        pickItem(d.ref, d.field, true);
      } else if (d.type === 'pick') {
        const page = String(d.key).split('.')[0];
        say(`นี่คือข้อความหน้าเว็บ — <a href="blocks.php?page=${encodeURIComponent(page)}&focus=${encodeURIComponent(d.key)}">แก้ที่ ข้อความหน้าเว็บ →</a>`);
      } else if (d.type === 'miss') {
        say('ตรงนี้ไม่ใช่รายการ — เบอร์โทร อีเมล ที่อยู่ แก้ที่ <a href="settings.php">ข้อมูลติดต่อ & ลิงก์</a> · รูปภาพประกอบส่วนต่างๆ และเมนูด้านบนยังแก้ไม่ได้');
      }
    });
    // typing → the page updates live; opening/entering a card → the item is outlined on the page
    lform.addEventListener('input', e => {
      const card = e.target.closest('[data-ref]');
      if (card && e.target.matches('[data-live]')) post({ type: 'itemText', ref: card.dataset.ref, field: e.target.dataset.live, value: e.target.value });
    });
    // any field clicked on the right → that item (and that field, when shown) is selected on the page
    lform.addEventListener('focusin', e => {
      const card = e.target.closest('[data-items] [data-ref]');
      if (!card) return;
      mark(card);
      post({ type: 'focusItem', ref: card.dataset.ref, field: e.target.dataset.live || null, scroll: true });
    });
    lform.querySelector('[data-items]').addEventListener('toggle', e => {
      const card = e.target;
      // skip when a field inside already took the cursor (its own focusin selected the exact field)
      if (card.matches && card.matches('[data-ref]') && card.open && !card.contains(document.activeElement)) { mark(card); post({ type: 'focusItem', ref: card.dataset.ref, scroll: true }); }
    }, true);
    const paneKey = 'lyiweb-lists-pane:' + list;
    lform.addEventListener('submit', () => store(paneKey, String(pane.scrollTop)));
    const focusId = new URLSearchParams(location.search).get('focus');
    applyView();
    const paneTop = read(paneKey);
    if (paneTop !== null && visual()) pane.scrollTop = parseInt(paneTop, 10) || 0;
    store(paneKey);
    if (focusId) pickItem(`${list}#${focusId}`, null, false);   // link from the text editor (works in both views)
  }

  // SEO & AEO page: live Google result preview per language box, length hint on the counters
  document.querySelectorAll('[data-serp-group]').forEach(group => {
    const t = group.querySelector('[data-serp-title-src]');
    const d = group.querySelector('[data-serp-desc-src]');
    const fallback = sel => {   // an empty English field shows the Thai text on the site
      if (!group.closest('.seo-langs')) return '';
      const th = group.closest('.seo-langs').querySelector('[data-serp-group]');
      return th && th !== group ? th.querySelector(sel).value.trim() : '';
    };
    const draw = () => {
      group.querySelector('[data-serp-title]').textContent = t.value.trim() || fallback('[data-serp-title-src]') || '(ยังไม่มีชื่อหน้า)';
      group.querySelector('[data-serp-desc]').textContent = d.value.trim() || fallback('[data-serp-desc-src]') || '(ยังไม่มีคำอธิบาย — Google จะเลือกข้อความจากหน้าเว็บมาแสดงเอง)';
    };
    group.closest('form')?.addEventListener('input', draw);
    draw();
  });
  document.querySelectorAll('textarea[data-range]').forEach(t => {
    const [lo, hi] = t.dataset.range.split('-').map(Number);
    const out = t.parentElement.querySelector('.count');
    const tint = () => {
      const n = t.value.trim().length;
      if (out) out.dataset.state = n === 0 ? 'empty' : n < lo || n > hi ? 'warn' : 'ok';
    };
    t.addEventListener('input', tint);
    tint();
  });
  // share picture: preview the chosen file before saving
  document.querySelectorAll('[data-og-input]').forEach(input => input.addEventListener('change', () => {
    const f = input.files && input.files[0];
    const img = input.closest('.og-row')?.querySelector('[data-og-preview]');
    if (f && img) img.src = URL.createObjectURL(f);
  }));

  // destructive buttons ask first
  document.querySelectorAll('[data-confirm]').forEach(btn => btn.addEventListener('click', e => {
    if (!window.confirm(btn.dataset.confirm)) e.preventDefault();
  }));

  window.addEventListener('beforeunload', e => {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });
})();
