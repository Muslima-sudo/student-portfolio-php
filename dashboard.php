<?php
session_start();

// Xavfsizlik: Agar foydalanuvchi tizimga kirmagan bo'lsa, loginga qaytaramiz
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/db.php';
require_once 'classes/User.php';

// Obyekt yaratamiz va foydalanuvchi ma'lumotlarini olamiz[cite: 1]
$userObj = new User($pdo);
$userInfo = $userObj->getInfo($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Kabinet - Portfolio</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .header { background: #333; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: #ff4d4d; text-decoration: none; font-weight: bold; }
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .info-box { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .menu-btn { display: inline-block; background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
        .menu-btn:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="header">
    <h2>Student Portfolio</h2>
    <a href="logout.php">Tizimdan chiqish (Logout)</a>
</div>

<div class="container">
    <h1>Xush kelibsiz, <?php echo htmlspecialchars($userInfo['full_name']); ?>!</h1>
    
    <div class="info-box">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($userInfo['email']); ?></p>
        <p><strong>Ruxsat darajangiz (Rol):</strong> <?php echo ucfirst(htmlspecialchars($userInfo['role'])); ?></p>
        
        <?php
        // A2 talabi: switch operatoridan foydalanish[cite: 1]
        switch ($userInfo['role']) {
            case 'admin':
                echo "<p style='color:red;'><b>Siz tizim administratorisiz! Barcha huquqlarga egasiz.</b></p>";
                break;
            case 'user':
                echo "<p style='color:green;'><b>Siz oddiy foydalanuvchisiz.</b></p>";
                break;
            default:
                echo "<p>Rol aniqlanmadi.</p>";
        }
        ?>
    </div>

    <h3>Mening menyu panelim</h3>
    <a href="projects.php" class="menu-btn">Loyihalarni boshqarish (CRUD)</a>
</div>

</body>
</html>