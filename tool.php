<?php
require_once __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
$tool = $slug ? getToolBySlug($slug) : null;
if (!$tool) {
    http_response_code(404);
    $pageTitle = 'Tool not found — ' . SITE_NAME;
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><h1 class="section-title">Tool not found</h1><p class="empty-note">This tool does not exist or was removed. <a href="' . SITE_URL . '/index.php">Back to the directory</a>.</p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$rel = getToolRelations((int)$tool['id']);
$visit = $tool['affiliate_link'] ?: $tool['website_url'];
$pageTitle = $tool['name'] . ' — Review, Features, Pricing | ' . SITE_NAME;
$pageDesc  = $tool['tagline'] ?: mb_substr(strip_tags($tool['description'] ?? ''), 0, 160);
require __DIR__ . '/includes/header.php';
?>

<section class="tool-hero">
  <div class="hero-bg-effects tool-hero-bg-effects" data-hearts="tool" aria-hidden="true"></div>
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <nav class="crumbs"><a href="<?= SITE_URL ?>/index.php">Home</a><span>/</span><?php if ($rel['categories']): ?><a href="<?= SITE_URL ?>/category.php?slug=<?= esc($rel['categories'][0]['slug']) ?>"><?= esc($rel['categories'][0]['name']) ?></a><span>/</span><?php endif; ?><b><?= esc($tool['name']) ?></b></nav>

    <div class="tool-hero-grid">
      <div class="tool-hero-info">
        <div class="tool-hero-head">
          <?php if ($tool['logo']): ?>
            <img class="tool-logo tool-logo-lg" src="<?= esc(imgUrl($tool['logo'])) ?>" alt="<?= esc($tool['name']) ?> logo">
          <?php else: ?>
            <span class="tool-logo tool-logo-lg tool-logo-fallback"><?= esc(mb_strtoupper(mb_substr($tool['name'], 0, 1))) ?></span>
          <?php endif; ?>
          <div>
            <h1><?= esc($tool['name']) ?></h1>
            <div class="tool-flags">
              <?php if ($tool['editor_score'] !== null && $tool['editor_score'] !== ''): ?><span class="flag flag-score"><svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17l-6.1 3.6 1.4-6.8L2.2 9.1l6.9-.8L12 2Z"/></svg>Editor Score <?= esc($tool['editor_score']) ?></span><?php endif; ?>
              <?php if ($tool['verified']): ?><span class="flag flag-verified"><svg viewBox="0 0 24 24" fill="none"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>Verified</span><?php endif; ?>
              <?php if ($tool['is_popular']): ?><span class="flag flag-hot">Popular<svg class="popular-fire" viewBox="0 0 24 24" aria-hidden="true"><path class="popular-fire-outer" d="M13.6 2.2c.4 2.7-.7 4.3-2 5.7-1.1-1.4-1.3-3.2-1.3-4.1C7.1 6.1 4 9.6 4 14a8 8 0 0 0 16 0c0-4.7-2.7-9-6.4-11.8Z"/><path class="popular-fire-inner" d="M12.2 20a3.3 3.3 0 0 1-3.3-3.3c0-2 1.3-3.5 3.4-5.4-.1 1.8.7 2.8 1.6 3.6.8.7 1.3 1.5 1.3 2.5a3 3 0 0 1-3 2.6Z"/></svg></span><?php endif; ?>
              <?php if ($tool['is_new']): ?><span class="flag flag-new">New</span><?php endif; ?>
            </div>
          </div>
        </div>
        <p class="tool-hero-tagline"><?= esc($tool['tagline']) ?></p>
        <div class="tool-badges">
          <?php foreach ($rel['badges'] as $b): ?><span class="chip"><?= esc($b) ?></span><?php endforeach; ?>
        </div>
        <?php if ($visit): ?>
          <a class="btn btn-primary btn-lg" href="<?= esc($visit) ?>" target="_blank" rel="nofollow sponsored noopener" onclick="gtag_report_conversion()">Visit Website
            <svg viewBox="0 0 24 24" fill="none"><path d="M7 17 17 7m0 0H8m9 0v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        <?php endif; ?>
      </div>
      <?php if ($tool['hero_image']): ?>
        <div class="tool-hero-shot">
          <img src="<?= esc(imgUrl($tool['hero_image'])) ?>" alt="<?= esc($tool['name']) ?> screenshot" loading="lazy">
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section section-tight">
  <div class="container container-narrow">

    <?php if ($tool['description']): ?>
      <div class="block">
        <h2 class="block-title">Overview</h2>
        <p class="overview-text"><?= nl2br(esc($tool['description'])) ?></p>
      </div>
    <?php endif; ?>

    <?php if ($rel['features']): ?>
      <div class="block">
        <h2 class="block-title">Key features</h2>
        <div class="feature-grid">
          <?php foreach ($rel['features'] as $f): ?>
            <div class="feature-card"><svg viewBox="0 0 24 24" fill="none"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg><?= esc($f) ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($rel['pros'] || $rel['cons']): ?>
      <div class="block">
        <h2 class="block-title">Pros &amp; cons</h2>
        <div class="proscons">
          <div class="pros-col">
            <h3>Pros</h3>
            <ul>
              <?php foreach ($rel['pros'] as $p): ?><li><svg viewBox="0 0 24 24" fill="none"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg><?= esc($p) ?></li><?php endforeach; ?>
            </ul>
          </div>
          <div class="cons-col">
            <h3>Cons</h3>
            <ul>
              <?php foreach ($rel['cons'] as $c): ?><li><svg viewBox="0 0 24 24" fill="none"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg><?= esc($c) ?></li><?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($rel['pricing'] || $tool['free_trial']): ?>
      <div class="block">
        <h2 class="block-title">Pricing</h2>
        <div class="pricing-grid">
          <?php if ($tool['free_trial'] && !array_filter($rel['pricing'], fn($p) => stripos($p['plan_name'], 'free') !== false)): ?>
            <div class="pricing-card"><span class="plan-name">Free Trial</span><span class="plan-price">$0</span></div>
          <?php endif; ?>
          <?php foreach ($rel['pricing'] as $p): ?>
            <div class="pricing-card"><span class="plan-name"><?= esc($p['plan_name']) ?></span><span class="plan-price"><?= esc($p['price']) ?></span></div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($rel['best_for']): ?>
      <div class="block">
        <h2 class="block-title">Best for</h2>
        <div class="tool-badges bestfor">
          <?php foreach ($rel['best_for'] as $b): ?><span class="chip chip-accent"><?= esc($b) ?></span><?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($rel['faqs']): ?>
      <div class="block">
        <h2 class="block-title">FAQ</h2>
        <div class="faq-list">
          <?php foreach ($rel['faqs'] as $f): ?>
            <details class="faq-item"><summary><?= esc($f['question']) ?></summary><p><?= nl2br(esc($f['answer'])) ?></p></details>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php if ($rel['alternatives']): ?>
<section class="section">
  <div class="container">
    <h2 class="section-title">Alternatives to <?= esc($tool['name']) ?></h2>
    <div class="tool-grid">
      <?php foreach ($rel['alternatives'] as $t) include __DIR__ . '/includes/tool-card.php'; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($visit): ?>
<section class="section section-tight">
  <div class="container container-narrow">
    <div class="final-cta">
      <h2>Ready to try <?= esc($tool['name']) ?>?</h2>
      <a class="btn btn-primary btn-lg" href="<?= esc($visit) ?>" target="_blank" rel="nofollow sponsored noopener">Try <?= esc($tool['name']) ?></a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
