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
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      }[c];
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
        '<span>' +
        '<span class="sr-name">' + escHtml(t.name) + '</span>' +
        '<span class="sr-tag">' + escHtml(t.tagline || '') + '</span>' +
        '</span>' +
        '</a>';

    }).join('');

    box.classList.add('open');

  }

  input.addEventListener('input', function () {

    clearTimeout(timer);

    var q = input.value.trim();

    if (q.length < 2) {
      box.classList.remove('open');
      return;
    }

    timer = setTimeout(function () {

      fetch(base + 'search.php?q=' + encodeURIComponent(q))
        .then(function (r) { return r.json(); })
        .then(render)
        .catch(function () {
          box.classList.remove('open');
        });

    }, 220);

  });

  document.addEventListener('click', function (e) {

    if (!box.contains(e.target) && e.target !== input)
      box.classList.remove('open');

  });

  input.addEventListener('keydown', function (e) {

    if (e.key === 'Escape')
      box.classList.remove('open');

  });

})();





/* ===========================================================
   Premium drifting background hearts
   =========================================================== */

(function () {

    var layer = document.querySelector('.hero-bg-effects');

    if (!layer) return;

    if (
        window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) return;

    var HEART_PATH =
        'M12 21s-6.7-4.35-9.33-8.06C.9 10.44 1.7 6.9 4.6 5.5c2-1 4.4-.35 5.9 1.3l1.5 1.6 1.5-1.6c1.5-1.65 3.9-2.3 5.9-1.3 2.9 1.4 3.7 4.94 1.93 7.44C18.7 16.65 12 21 12 21Z';

    function createHeart(size, left, top, opacity, duration, rotation) {

        var el = document.createElement("div");

        el.className = "big-heart";

        el.style.width = size + "px";
        el.style.height = size + "px";

        el.style.left = left + "%";
        el.style.top = top + "%";

        el.style.opacity = opacity;

        el.style.setProperty("--dur", duration + "s");
        el.style.setProperty("--rot", rotation + "s");

        el.innerHTML =
            '<svg viewBox="0 0 24 24" fill="none">' +
            '<path d="' +
            HEART_PATH +
            '" stroke="#b894ff" stroke-width="1.15" stroke-linecap="round" stroke-linejoin="round"/>' +
            '</svg>';

        layer.appendChild(el);

    }

    if (layer.getAttribute('data-hearts') === 'tool') {

        createHeart(270, 6, -8, .035, 31, 170);
        createHeart(230, 70, 8, .045, 28, 190);
        createHeart(190, 12, 58, .04, 25, 155);
        createHeart(250, 68, 54, .035, 34, 210);
        createHeart(135, 42, 16, .055, 22, 145);
        createHeart(115, 82, 42, .05, 24, 165);
        createHeart(105, 38, 72, .045, 21, 150);

        for (var j = 0; j < 6; j++) {

            createHeart(
                42 + Math.random() * 48,
                Math.random() * 92,
                Math.random() * 86,
                .035 + Math.random() * .025,
                18 + Math.random() * 10,
                120 + Math.random() * 100
            );

        }

        return;

    }

    // ------------------------------
    // Huge hearts
    // ------------------------------

    createHeart(320, 4, 5, .035, 33, 140);

    createHeart(260, 82, 12, .04, 28, 180);

    createHeart(220, 15, 58, .03, 31, 160);

    createHeart(300, 70, 64, .04, 35, 220);

    // ------------------------------
    // Medium
    // ------------------------------

    createHeart(170, 35, 18, .055, 22, 150);

    createHeart(150, 56, 38, .05, 24, 160);

    createHeart(130, 18, 36, .045, 26, 170);

    createHeart(160, 82, 46, .055, 20, 150);

    createHeart(145, 44, 70, .05, 24, 180);

    createHeart(120, 63, 12, .045, 25, 170);

    // ------------------------------
    // Small
    // ------------------------------

    for (var i = 0; i < 12; i++) {

        createHeart(

            50 + Math.random() * 55,

            Math.random() * 100,

            Math.random() * 90,

            .04 + Math.random() * .03,

            18 + Math.random() * 12,

            120 + Math.random() * 120

        );

    }

})();
