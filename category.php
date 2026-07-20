<?php
require_once __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
$free = isset($_GET['free']);

if ($free) {
    $title = 'Free Trial AI Companions';
    $tools = getTools(['free_trial' => 1]);
} else {
    $st = db()->prepare('SELECT * FROM categories WHERE slug = ?');
    $st->execute([$slug]);
    $cat = $st->fetch();
    if (!$cat) { header('Location: ' . SITE_URL . '/index.php'); exit; }
    $title = $cat['name'];
    $tools = getTools(['category_slug' => $slug]);
}

$pageTitle = $title . ' — ' . SITE_NAME;
$pageDesc  = 'Browse and compare the best ' . $title . ' tools. Features, pricing, pros and cons in one place.';
require __DIR__ . '/includes/header.php';
?>

<section class="cat-hero">
  <div class="hero-glow" aria-hidden="true"></div>
  <div class="container">
    <nav class="crumbs"><a href="<?= SITE_URL ?>/index.php">Home</a><span>/</span><b><?= esc($title) ?></b></nav>
    <h1><?= esc($title) ?></h1>
    <p class="hero-sub"><?= count($tools) ?> tool<?= count($tools) === 1 ? '' : 's' ?> in this collection</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if ($tools): ?>
      <div class="tool-grid">
        <?php foreach ($tools as $t) include __DIR__ . '/includes/tool-card.php'; ?>
      </div>
    <?php else: ?>
      <p class="empty-note">No tools in this category yet. <a href="<?= SITE_URL ?>/index.php">Browse the full directory</a>.</p>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
