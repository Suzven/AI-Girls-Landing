<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

function isLoggedIn(): bool {
    return !empty($_SESSION['cv_user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Проверка логина по таблице users.
 * Пароль в БД может быть либо простым текстом, либо password_hash().
 */
function attemptLogin(string $username, string $password): bool {
    $st = db()->prepare('SELECT * FROM users WHERE username = ?');
    $st->execute([$username]);
    $user = $st->fetch();
    if (!$user) return false;

    $stored = $user['password'];
    $ok = hash_equals($stored, $password) || password_verify($password, $stored);
    if ($ok) {
        $_SESSION['cv_user_id'] = (int)$user['id'];
        $_SESSION['cv_username'] = $user['username'];
    }
    return $ok;
}
