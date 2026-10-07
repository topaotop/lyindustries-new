// Counts clicks on email / LINE / phone links (lyiweb_channel_clicks) without slowing the click down.
// Links are found by their address, so page templates need no extra attributes.
(() => {
  'use strict';
  if (location.pathname.includes('/admin/')) return;   // admin preview of the page: never counted
  const file = location.pathname.split('/').pop().replace(/\.php$/, '');
  const page = { '': 'home', index: 'home', about: 'about', catalog: 'catalog', contact: 'contact' }[file];
  if (!page) return;
  const endpoint = new URL('api/v1/track.php', location.href.replace(/[^/]*$/, '')).href;
  const channelOf = href => (/^mailto:/i.test(href) ? 'email' : /^tel:/i.test(href) ? 'tel' : /(^|\/\/)(line\.me|lin\.ee)\//i.test(href) ? 'line' : null);
  // where on the page: menu / footer / the section's id
  const positionOf = a => {
    if (a.closest('#sideMenu, aside')) return 'side-menu';
    if (a.closest('header')) return 'header';
    if (a.closest('footer')) return 'footer';
    const sec = a.closest('section[id]');
    return sec ? sec.id : 'page';
  };
  document.addEventListener('click', e => {
    const a = e.target.closest && e.target.closest('a[href]');
    const channel = a && channelOf(a.getAttribute('href'));
    if (!channel) return;
    const data = new URLSearchParams({ channel, page, position: positionOf(a), lang: document.documentElement.lang || 'th' });
    try {
      if (!(navigator.sendBeacon && navigator.sendBeacon(endpoint, data))) fetch(endpoint, { method: 'POST', body: data, keepalive: true });
    } catch (err) { /* never block the click */ }
  }, true);
})();
