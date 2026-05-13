<?php
require_once 'db.php';
$accent_color = '#ffffff';
$effects_enabled = true;

if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT theme_color, effects_enabled FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_settings = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user_settings) {
        $accent_color = htmlspecialchars($user_settings['theme_color']);
        $effects_enabled = (bool)$user_settings['effects_enabled'];
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Photo-Ocena</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --accent-color: <?= $accent_color ?>;
        }
        <?= !$effects_enabled ? '*, *::before, *::after { animation: none !important; transition: none !important; box-shadow: none !important; }' : '' ?>
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php">
                <svg width="180" height="40" xmlns="http://www.w3.org/2000/svg">
                    <text x="5" y="28" fill="var(--accent-color)" font-family="Arial, sans-serif" font-size="22" font-weight="bold" class="logo-text">Photo-Ocena</text>
                    <circle cx="155" cy="20" r="8" fill="none" stroke="var(--accent-color)" stroke-width="2" class="logo-circle"/>
                </svg>
            </a>
        </div>
        <nav>
            <a href="index.php">Strona główna</a>
            <a href="leaderboard.php">Ranking</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="upload.php">Dodaj zdjęcie</a>
                <a href="profile.php">Profil</a>
                <a href="logout.php">Wyloguj</a>
            <?php else: ?>
                <a href="login.php">Logowanie</a>
                <a href="register.php">Rejestracja</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
