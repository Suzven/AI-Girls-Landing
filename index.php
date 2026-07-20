<?php
require_once __DIR__ . '/includes/db.php';
$featured = getTools(['featured' => 1, 'limit' => 8]);
$latest   = getTools(['limit' => 20]);
$cats     = getCategories();
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero-bg-effects"></div>
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container hero-inner">
    <h1>Discover the Best<br>AI Companion Apps</h1>
    <p class="hero-sub">Compare AI companions, virtual girlfriends, roleplay chatbots and character generators — all in one place.</p>
    <div class="hero-search">
      <svg class="search-icon" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      <input type="text" id="site-search" placeholder="Search: Candy AI, DreamGF, anime girlfriend, voice companion..." autocomplete="off">
      <div id="search-results" class="search-results"></div>
    </div>
    <a class="btn btn-primary" href="#latest">Browse AI Companions</a>
  </div>
</section>

<section class="section" id="categories">
  <div class="container">
    <h2 class="section-title">Popular categories</h2>
    <div class="cat-grid">
      <?php foreach ($cats as $c): ?>
        <a class="cat-pill" href="<?= SITE_URL ?>/category.php?slug=<?= esc($c['slug']) ?>">
          <?= esc($c['name']) ?><span class="cat-count"><?= (int)$c['cnt'] ?></span>
        </a>
      <?php endforeach; ?>
      <a class="cat-pill cat-pill-accent" href="<?= SITE_URL ?>/category.php?free=1">Free Trial</a>
    </div>
  </div>
</section>

<?php if ($featured): ?>
<section class="section" id="featured">
  <div class="container">
    <h2 class="section-title">Featured tools</h2>
    <div class="tool-grid">
      <?php foreach ($featured as $t) include __DIR__ . '/includes/tool-card.php'; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" id="latest">
  <div class="container">
    <h2 class="section-title">Latest tools</h2>
    <?php if ($latest): ?>
      <div class="tool-grid">
        <?php foreach ($latest as $t) include __DIR__ . '/includes/tool-card.php'; ?>
      </div>
    <?php else: ?>
      <p class="empty-note">No tools added yet. Add your first tool in the management panel.</p>
    <?php endif; ?>
  </div>
</section>

<section class="section" id="faq">
  <div class="container container-narrow">
    <h2 class="section-title">Frequently asked questions</h2>
    <div class="faq-list">
      <details class="faq-item">
        <summary>What is an AI Companion?</summary>
        <p>An AI companion is a chatbot designed for personal, ongoing conversations — from friendly chat and roleplay to romantic interactions. Many services also offer voice messages and image generation of your custom character.</p>
      </details>
      <details class="faq-item">
        <summary>Are AI companions free?</summary>
        <p>Most services offer a free trial or a limited free tier. Full features like unlimited messages, voice and image generation usually require a subscription. Use our Free Trial filter to find tools you can test first.</p>
      </details>
      <details class="faq-item">
        <summary>Are AI girlfriend apps safe?</summary>
        <p>Reputable services use encrypted connections and discreet billing. We list each tool's official website so you always sign up at the source. Never share personal financial details inside a chat.</p>
      </details>
      <details class="faq-item">
        <summary>Which AI companion is the most realistic?</summary>
        <p>It depends on what matters to you: conversation quality, memory, voice or images. Check the Editor Score, key features and pros &amp; cons on each card to compare.</p>
      </details>
      <details class="faq-item">
        <summary>How do we rank tools?</summary>
        <p>Our editors test each service and score it on conversation quality, features, pricing and overall experience. Featured placement may include partner tools, which is why every card carries a disclosure.</p>
      </details>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
