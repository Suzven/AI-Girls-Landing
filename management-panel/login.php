<?php
require_once __DIR__ . '/auth.php';
if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (attemptLogin(trim($_POST['username'] ?? ''), (string)($_POST['password'] ?? ''))) {
        header('Location: index.php');
        exit;
    }
    $error = 'Неверный логин или пароль';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Вход — панель управления</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">
<div class="login-wrap">
  <div class="hero-glow" aria-hidden="true"></div>
  <form class="login-card" method="post">
    <div class="logo" style="margin-bottom:22px">Companion<span>Verse</span></div>
    <h1>Панель управления</h1>
    <?php if ($error): ?><div class="admin-alert admin-alert-error"><?= esc($error) ?></div><?php endif; ?>
    <label class="field">
      <span>Логин</span>
      <input type="text" name="username" required autofocus>
    </label>
    <label class="field">
      <span>Пароль</span>
      <input type="password" name="password" required>
    </label>
    <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Войти</button>
  </form>
</div>
</body>
</html>
