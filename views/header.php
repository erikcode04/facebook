<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook-projekt</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/posts.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/profile.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/search.css">
</head>

<body>
    <header>
        <nav>
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">Facebook-projekt</a>
            </div>
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>">Hem</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="<?php echo BASE_URL; ?>posts.php">Inlägg</a></li>
                    <li><a href="<?php echo BASE_URL; ?>search.php">Sök</a></li>
                    <li><a href="<?php echo BASE_URL; ?>profile.php">Profil</a></li>
                    <li><a href="<?php echo BASE_URL; ?>logout.php">Logga ut</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>login.php">Logga in</a></li>
                    <li><a href="<?php echo BASE_URL; ?>register.php">Registrera</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container">