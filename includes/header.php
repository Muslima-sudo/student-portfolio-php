<?php
// Agar sessiya boshlanmagan bo'lsa, boshlaymiz
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portfolio Tizimi</title>
    <!-- css ulash -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="header-nav">
    <div class="brand"><a href="index.php" style="color: inherit; margin:0;">Student Portfolio</a></div>
    <div>
        <a href="index.php">Bosh sahifa</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Kabinet</a>
            <a href="projects.php">Loyihalarim</a>
            <a href="logout.php" class="btn btn-danger" style="padding: 5px 10px; margin-left: 15px;">Chiqish</a>
        <?php else: ?>
            <a href="login.php">Kirish</a>
            <a href="register.php" class="btn" style="padding: 5px 10px; margin-left: 15px;">Ro'yxatdan o'tish</a>
        <?php endif; ?>
    </div>
</div>