<?php
/** ожидает переменную $t (строка из tools) */
$cardBadges = getToolBadges((int)$t['id'], 4);
?>
<a class="tool-card" href="<?= SITE_URL ?>/tool.php?slug=<?= esc($t['slug']) ?>">
  <?php if (!empty($t['hero_image'])): ?>
    <div class="tool-card-shot">
      <img src="<?= esc(imgUrl($t['hero_image'])) ?>" alt="<?= esc($t['name']) ?> preview" loading="lazy">
    </div>
  <?php endif; ?>
  <div class="tool-card-top">
    <?php if ($t['logo']): ?>
      <img class="tool-logo" src="<?= esc(imgUrl($t['logo'])) ?>" alt="<?= esc($t['name']) ?> logo" loading="lazy">
    <?php else: ?>
      <span class="tool-logo tool-logo-fallback"><?= esc(mb_strtoupper(mb_substr($t['name'], 0, 1))) ?></span>
    <?php endif; ?>
    <div class="tool-card-title">
      <h3><?= esc($t['name']) ?></h3>
      <div class="tool-flags">
        <?php if ($t['verified']): ?><span class="flag flag-verified"><svg viewBox="0 0 24 24" fill="none"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>Verified</span><?php endif; ?>
        <?php if ($t['is_popular']): ?><span class="flag flag-hot">Popular</span><?php endif; ?>
        <?php if ($t['is_new']): ?><span class="flag flag-new">New</span><?php endif; ?>
      </div>
    </div>
    <?php if ($t['editor_score'] !== null && $t['editor_score'] !== ''): ?>
      <span class="score"><svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17l-6.1 3.6 1.4-6.8L2.2 9.1l6.9-.8L12 2Z"/></svg><?= esc($t['editor_score']) ?></span>
    <?php endif; ?>
  </div>
  <p class="tool-tagline"><?= esc($t['tagline']) ?></p>
  <div class="tool-badges">
    <?php foreach ($cardBadges as $b): ?><span class="chip"><?= esc($b) ?></span><?php endforeach; ?>
  </div>
  <div class="tool-card-bottom">
    <span class="price-hint"><?= $t['free_trial'] ? 'Free Trial' : 'Paid' ?></span>
    <span class="view-link">View <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
  </div>
</a>
