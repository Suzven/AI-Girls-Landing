<?php
require_once __DIR__ . '/auth.php';
requireLogin();

/* удаление */
if (isset($_GET['delete'])) {
    $st = db()->prepare('DELETE FROM tools WHERE id = ?');
    $st->execute([(int)$_GET['delete']]);
    header('Location: index.php?msg=deleted');
    exit;
}

$tools = db()->query('SELECT t.*, (SELECT COUNT(*) FROM tool_categories tc WHERE tc.tool_id = t.id) AS cat_cnt FROM tools t ORDER BY t.created_at DESC')->fetchAll();
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Офферы — панель управления</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

<header class="admin-header">
  <div class="admin-container admin-header-inner">
    <a class="logo" href="index.php">Companion<span>Verse</span> <em>/ панель</em></a>
    <nav class="admin-nav">
      <a href="index.php" class="active">Офферы</a>
      <a href="categories.php">Категории</a>
      <a href="../index.php" target="_blank">Открыть сайт</a>
      <a href="logout.php">Выйти (<?= esc($_SESSION['cv_username']) ?>)</a>
    </nav>
  </div>
</header>

<main class="admin-container">
  <div class="admin-toolbar">
    <h1>Офферы <span class="count-pill"><?= count($tools) ?></span></h1>
    <a class="btn btn-primary" href="tool-form.php">+ Добавить оффер</a>
  </div>

  <?php if ($msg === 'deleted'): ?><div class="admin-alert admin-alert-ok">Оффер удалён</div><?php endif; ?>
  <?php if ($msg === 'saved'): ?><div class="admin-alert admin-alert-ok">Оффер сохранён</div><?php endif; ?>

  <?php if (!$tools): ?>
    <div class="admin-empty">Пока нет ни одного оффера. Нажми «Добавить оффер», чтобы создать первый.</div>
  <?php else: ?>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Оффер</th>
          <th>Slug</th>
          <th>Категорий</th>
          <th>Score</th>
          <th>Метки</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tools as $t): ?>
        <tr>
          <td>
            <div class="admin-tool-cell">
              <?php if ($t['logo']): ?><img src="<?= esc($t['logo']) ?>" alt=""><?php else: ?><span class="admin-logo-fallback"><?= esc(mb_strtoupper(mb_substr($t['name'], 0, 1))) ?></span><?php endif; ?>
              <div>
                <b><?= esc($t['name']) ?></b>
                <span><?= esc(mb_substr($t['tagline'], 0, 60)) ?></span>
              </div>
            </div>
          </td>
          <td><code><?= esc($t['slug']) ?></code></td>
          <td><?= (int)$t['cat_cnt'] ?></td>
          <td><?= $t['editor_score'] !== null ? esc($t['editor_score']) : '—' ?></td>
          <td class="admin-flags">
            <?php if ($t['featured']): ?><span class="mini-flag">Featured</span><?php endif; ?>
            <?php if ($t['verified']): ?><span class="mini-flag">Verified</span><?php endif; ?>
            <?php if ($t['is_new']): ?><span class="mini-flag">New</span><?php endif; ?>
            <?php if ($t['is_popular']): ?><span class="mini-flag">Popular</span><?php endif; ?>
            <?php if ($t['free_trial']): ?><span class="mini-flag mini-flag-green">Free Trial</span><?php endif; ?>
          </td>
          <td class="admin-actions">
            <a class="btn btn-ghost btn-sm" href="../tool.php?slug=<?= esc($t['slug']) ?>" target="_blank">Смотреть</a>
            <a class="btn btn-ghost btn-sm" href="tool-form.php?id=<?= (int)$t['id'] ?>">Изменить</a>
            <a class="btn btn-ghost btn-sm btn-danger" href="index.php?delete=<?= (int)$t['id'] ?>" onclick="return confirm('Удалить оффер «<?= esc($t['name']) ?>»? Это действие необратимо.')">Удалить</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</main>
</body>
</html>
