// Homepage interactions: side menu, FAQ accordion, color-lab swatches and the scroll-driven scenes.
(() => {
  'use strict';

  const q = (s) => document.querySelector(s);
  const qa = (s) => [...document.querySelectorAll(s)];
  const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
  const easeOutCubic = (t) => 1 - Math.pow(1 - clamp(t, 0, 1), 3);

  // ---- Side menu (☰)
  const burger = q('.mobile-burger');
  qa('[data-menu-toggle]').forEach(el => el.addEventListener('click', () => {
    const open = document.documentElement.classList.toggle('menu-open');
    if (burger) burger.setAttribute('aria-expanded', String(open));
  }));

  // ---- FAQ accordion: one item open at a time; clicking the open item closes it
  qa('[data-faq-toggle]').forEach(btn => btn.addEventListener('click', () => {
    const item = btn.closest('.faq-item');
    const willOpen = !item.classList.contains('is-open');
    qa('.faq-item').forEach(other => {
      const open = other === item && willOpen;
      other.classList.toggle('is-open', open);
      other.querySelector('[data-faq-toggle]').setAttribute('aria-expanded', String(open));
      other.querySelector('.faq-icon').textContent = open ? '−' : '+';
    });
  }));

  // ---- Color lab swatches
  const ribbon = q('#swatchRibbon');
  qa('[data-swatch]').forEach(btn => btn.addEventListener('click', () => {
    const sw = JSON.parse(btn.dataset.swatch);
    qa('[data-swatch]').forEach(b => {
      b.style.borderColor = b === btn ? '#fff' : 'transparent';
      b.setAttribute('aria-pressed', String(b === btn));
    });
    if (ribbon) {
      ribbon.style.background = sw.hex;
      ribbon.style.boxShadow = `0 12px 36px ${sw.glow}`;
    }
    qa('[data-swatch-text]').forEach(el => { el.style.color = sw.text; });
    qa('[data-swatch-code]').forEach(el => { el.textContent = sw.code; });
    qa('[data-swatch-name]').forEach(el => { el.textContent = sw.name; });
  }));

  // ---- Hero background video
  const video = q('#heroVideo');
  if (video) {
    video.muted = true;
    video.defaultMuted = true;
    video.play().catch(() => {});
  }

  // ---- Scroll-driven scenes (smoothed requestAnimationFrame loop)
  const progressBar = q('#lyProgress');
  const heroContent = q('#heroContent');
  const assembleEl = q('#assembleContainer');
  const assembleTitle = q('#assembleTitle');
  const assembleCaption = q('#assembleCaption');
  const tiles = qa('[data-tile]');
  const processEl = q('#processContainer');
  const stepBgs = qa('[data-step-bg]');
  const stepTexts = qa('[data-step-text]');
  const stepNodes = qa('[data-step-node]');
  const stepLine = q('#stepLineProgress');
  const stepCount = stepTexts.length;

  // Wide banner-style photos (wider than 2:1, often with black areas baked in) are shown whole and
  // centred instead of being zoomed to cover the screen — zooming crops them and makes them blurry.
  stepBgs.forEach(bg => {
    const img = bg.querySelector('img');
    if (!img) return;
    const fit = () => { if (img.naturalHeight && img.naturalWidth / img.naturalHeight > 2) img.classList.add('step-bg-contain'); };
    if (img.complete) fit(); else img.addEventListener('load', fit, { once: true });
  });

  const readScroll = () => window.scrollY || document.scrollingElement?.scrollTop || document.body.scrollTop || 0;
  let smoothScrollY = readScroll();

  // Normalized progress (0–1) of scrolling through a tall pinned container; -1 when absent.
  const progressOf = (el, winHeight) => {
    if (!el) return -1;
    const rect = el.getBoundingClientRect();
    const total = rect.height - winHeight;
    if (total <= 0) return 0;
    return clamp(-rect.top / total, 0, 1);
  };

  const frame = () => {
    smoothScrollY += (readScroll() - smoothScrollY) * 0.12;
    const y = smoothScrollY;
    const winHeight = window.innerHeight;
    const docHeight = document.documentElement.scrollHeight - winHeight;

    // 1. Top progress bar
    if (progressBar && docHeight > 0) {
      progressBar.style.width = ((y / docHeight) * 100).toFixed(2) + '%';
    }

    // 2. Hero recede parallax
    if (heroContent) {
      const p = clamp(y / (winHeight * 0.85), 0, 1);
      heroContent.style.transform = `translate3d(0, ${(p * 80).toFixed(1)}px, 0) scale(${(1 - p * 0.08).toFixed(3)})`;
      heroContent.style.opacity = (1 - p * 1.1).toFixed(3);
    }
    if (video) {
      video.style.transform = `scale(${(1.02 + clamp(y / winHeight, 0, 1) * 0.08).toFixed(3)})`;
    }

    // 3. Scene 1: application tiles fly into place
    const pAssemble = progressOf(assembleEl, winHeight);
    if (pAssemble >= 0) {
      const top = assembleEl.getBoundingClientRect().top;
      const enter = easeOutCubic(clamp((winHeight * 0.9 - top) / (winHeight * 0.7), 0, 1));
      const tIn = Math.max(easeOutCubic(pAssemble / 0.08), enter);
      if (assembleTitle) {
        assembleTitle.style.opacity = tIn.toFixed(3);
        assembleTitle.style.transform = `translate3d(0, ${((1 - tIn) * 30).toFixed(1)}px, 0)`;
      }
      tiles.forEach(tile => {
        const idx = parseInt(tile.dataset.tile, 10);
        const k = easeOutCubic((pAssemble - (0.02 + idx * 0.045)) / 0.22);
        const dx = parseFloat(tile.dataset.dx) || 0;
        const dy = parseFloat(tile.dataset.dy) || 0;
        const rot = parseFloat(tile.dataset.rot) || 0;
        tile.style.opacity = k.toFixed(3);
        tile.style.transform = `translate3d(${((1 - k) * dx).toFixed(1)}px, ${((1 - k) * dy).toFixed(1)}px, 0) rotate(${((1 - k) * rot).toFixed(1)}deg) scale(${(0.88 + k * 0.12).toFixed(3)})`;
      });
      if (assembleCaption) {
        const c = easeOutCubic((pAssemble - 0.5) / 0.12);
        assembleCaption.style.opacity = c.toFixed(3);
        assembleCaption.style.transform = `translate3d(0, ${((1 - c) * 20).toFixed(1)}px, 0)`;
      }
    }

    // 4. Scene 2: process pipeline step swap
    const pProcess = progressOf(processEl, winHeight);
    if (pProcess >= 0 && stepCount > 0) {
      const currentFloat = pProcess * stepCount;
      const currentIdx = Math.min(stepCount - 1, Math.floor(currentFloat));
      const localT = currentFloat - currentIdx;

      stepBgs.forEach(bg => {
        bg.style.opacity = parseInt(bg.dataset.stepBg, 10) === currentIdx ? (1 - localT * 0.3).toFixed(3) : '0';
      });

      stepTexts.forEach(text => {
        const idx = parseInt(text.dataset.stepText, 10);
        const media = q(`[data-step-media="${idx}"]`);
        if (idx === currentIdx) {
          const inT = easeOutCubic(localT / 0.22);
          const outT = easeOutCubic((localT - 0.78) / 0.22);
          const op = inT * (1 - outT);
          text.style.opacity = op.toFixed(3);
          text.style.transform = `translate3d(0, ${((1 - inT) * 24 - outT * 24).toFixed(1)}px, 0)`;
          text.style.pointerEvents = op > 0.6 ? 'auto' : 'none';
          if (media) {
            media.style.opacity = op.toFixed(3);
            media.style.transform = `translate3d(0, ${((1 - inT) * 30 - outT * 30).toFixed(1)}px, 0)`;
          }
        } else {
          text.style.opacity = '0';
          text.style.pointerEvents = 'none';
          if (media) media.style.opacity = '0';
        }
      });

      if (stepLine) stepLine.style.transform = `scaleX(${pProcess.toFixed(4)})`;
      stepNodes.forEach(node => {
        node.style.opacity = parseInt(node.dataset.stepNode, 10) <= currentIdx ? '1' : '0.35';
      });
    }

    requestAnimationFrame(frame);
  };

  requestAnimationFrame(frame);
})();
