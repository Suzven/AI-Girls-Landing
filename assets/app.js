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
