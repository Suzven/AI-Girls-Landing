<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$d = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        try {
            $d->prepare('INSERT INTO categories (name, slug) VALUES (?,?)')->execute([$name, slugify($name)]);
        } catch (PDOException $e) {
            $error = 'Такая категория уже существует';
        }
    }
}

if (isset($_GET['delete'])) {
    $d->prepare('DELETE FROM categories WHERE id=?')->execute([(int)$_GET['delete']]);
    header('Location: categories.php');
    exit;
}

$cats = getCategories();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Категории — панель управления</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

<header class="admin-header">
  <div class="admin-container admin-header-inner">
    <a class="logo" href="index.php">Companion<span>Verse</span> <em>/ панель</em></a>
    <nav class="admin-nav">
      <a href="index.php">Офферы</a>
      <a href="categories.php" class="active">Категории</a>
      <a href="logout.php">Выйти</a>
    </nav>
  </div>
</header>

<main class="admin-container">
  <div class="admin-toolbar"><h1>Категории</h1></div>

  <?php if ($error): ?><div class="admin-alert admin-alert-error"><?= esc($error) ?></div><?php endif; ?>

  <form method="post" class="cat-add-form">
    <input type="text" name="name" placeholder="Название категории, напр. Voice Chat" required>
    <button class="btn btn-primary" type="submit">Добавить</button>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Категория</th><th>Slug (URL)</th><th>Офферов</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($cats as $c): ?>
        <tr>
          <td><b><?= esc($c['name']) ?></b></td>
          <td><code>category.php?slug=<?= esc($c['slug']) ?></code></td>
          <td><?= (int)$c['cnt'] ?></td>
          <td class="admin-actions">
            <a class="btn btn-ghost btn-sm btn-danger" href="categories.php?delete=<?= (int)$c['id'] ?>" onclick="return confirm('Удалить категорию «<?= esc($c['name']) ?>»?')">Удалить</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
