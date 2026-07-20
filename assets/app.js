/* CompanionVerse — live search */
(function () {
  var input = document.getElementById('site-search');
  var box = document.getElementById('search-results');
  if (!input || !box) return;

  var timer = null;
  var base = (document.querySelector('link[rel="stylesheet"][href*="assets/style.css"]') || {}).href || '';
  base = base.replace(/assets\/style\.css.*$/, '');

  function escHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function render(items) {
    if (!items.length) {
      box.innerHTML = '<div class="sr-empty">Nothing found. Try another name or category.</div>';
      box.classList.add('open');
      return;
    }
    box.innerHTML = items.map(function (t) {
      var logo = t.logo
        ? '<img src="' + escHtml(t.logo) + '" alt="">'
        : '<span class="sr-fallback">' + escHtml(t.name.charAt(0).toUpperCase()) + '</span>';
      return '<a class="sr-item" href="' + base + 'tool.php?slug=' + encodeURIComponent(t.slug) + '">' +
        logo +
        '<span><span class="sr-name">' + escHtml(t.name) + '</span>' +
        '<span class="sr-tag">' + escHtml(t.tagline || '') + '</span></span></a>';
    }).join('');
    box.classList.add('open');
  }

  input.addEventListener('input', function () {
    clearTimeout(timer);
    var q = input.value.trim();
    if (q.length < 2) { box.classList.remove('open'); return; }
    timer = setTimeout(function () {
      fetch(base + 'search.php?q=' + encodeURIComponent(q))
        .then(function (r) { return r.json(); })
        .then(render)
        .catch(function () { box.classList.remove('open'); });
    }, 220);
  });

  document.addEventListener('click', function (e) {
    if (!box.contains(e.target) && e.target !== input) box.classList.remove('open');
  });

  input.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') box.classList.remove('open');
  });
})();

/* CompanionVerse — floating heart outlines in the hero */
(function () {
  console.log("HEART SCRIPT START");
  var hero = document.querySelector('.hero');
  if (!hero) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var HEART_PATH = 'M12 21s-6.7-4.35-9.33-8.06C.9 10.44 1.7 6.9 4.6 5.5c2-1 4.4-.35 5.9 1.3l1.5 1.6 1.5-1.6c1.5-1.65 3.9-2.3 5.9-1.3 2.9 1.4 3.7 4.94 1.93 7.44C18.7 16.65 12 21 12 21Z';
  var COLORS = ['#a78bfa', '#c4b5fd', '#7c5cf5'];
  var MAX_HEARTS = 9;

  function rand(min, max) { return min + Math.random() * (max - min); }

  function spawnHeart() {
    if (document.hidden) return;
    if (hero.querySelectorAll('.heart-float').length >= MAX_HEARTS) return;

    var el = document.createElement('span');
    el.className = 'heart-float';

    var size = rand(13, 28);
    var dur = rand(9, 15);
    el.style.left = rand(4, 94) + '%';
    el.style.setProperty('--h-size', size.toFixed(0) + 'px');
    el.style.setProperty('--h-dur', dur.toFixed(1) + 's');
    el.style.setProperty('--h-sway', rand(3, 5.5).toFixed(1) + 's');
    el.style.setProperty('--h-amp', rand(8, 22).toFixed(0) + 'px');
    el.style.setProperty('--h-rise', '-' + rand(380, 620).toFixed(0) + 'px');
    el.style.setProperty('--h-tilt', rand(-14, 14).toFixed(0) + 'deg');
    el.style.setProperty('--h-op', rand(0.18, 0.38).toFixed(2));
    el.style.setProperty('--h-color', COLORS[Math.floor(Math.random() * COLORS.length)]);

    el.innerHTML = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
      '<path d="' + HEART_PATH + '" stroke="currentColor" stroke-width="1.6" ' +
      'stroke-linecap="round" stroke-linejoin="round"/></svg>';

    el.addEventListener('animationend', function (e) {
      if (e.animationName === 'heartRise') el.remove();
    });

    hero.appendChild(el);
  }

  // первые пару сердечек сразу, дальше — с случайным интервалом
  setTimeout(spawnHeart, 600);
  setTimeout(spawnHeart, 1800);
  (function loop() {
    setTimeout(function () { spawnHeart(); loop(); }, rand(1400, 2800));
  })();
})();
