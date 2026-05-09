<?php
session_start();
require_once 'config/db.php';
require_once 'classes/User.php';

$userObj = new User($pdo);
$error = '';
$success = '';

// 1. CSRF Token yaratish (agar yo'q bo'lsa)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. Captcha urinishlarini sanash uchun o'zgaruvchi
if (!isset($_SESSION['captcha_attempts'])) {
    $_SESSION['captcha_attempts'] = 0;
}

// 3. Formadan ma'lumot kelsa (POST so'rov)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Bloklangan vaqtni tekshirish
    if (isset($_SESSION['blocked_until']) && $_SESSION['blocked_until'] > time()) {
        $remaining = $_SESSION['blocked_until'] - time();
        $error = "Siz 3 marta xato qildingiz. Iltimos, $remaining soniya kuting.";
    } else {
        // CSRF ni tekshirish
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = "Xavfsizlik xatosi (CSRF). Iltimos formani yangilang.";
        } else {
            // Captcha'ni tekshirish
            $user_captcha = trim($_POST['captcha']);
            
            if ($user_captcha != $_SESSION['captcha_answer']) {
                $_SESSION['captcha_attempts']++;
                $error = "Captcha noto'g'ri kiritildi. Urinishlar soni: " . $_SESSION['captcha_attempts'];
                
                // 3 marta xato qilsa, 1 daqiqa (60 soniya) bloklash
                if ($_SESSION['captcha_attempts'] >= 3) {
                    $_SESSION['blocked_until'] = time() + 60;
                    $_SESSION['captcha_attempts'] = 0; // Urinishlarni nolga qaytaramiz
                    $error = "3 marta xato qildingiz. Forma 1 daqiqaga bloklandi!";
                }
            } else {
                // Hamma narsa to'g'ri bo'lsa, ma'lumotlarni tozalab (XSS dan himoya) o'qiymiz
                $full_name = htmlspecialchars(trim($_POST['full_name']));
                $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'];

                // A10 Talabi: Parol uzunligi va murakkabligi (Katta-kichik harf, raqam, belgi)
                $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

                // Validatsiya (Bo'sh joylarni tekshirish va hokazo)
                if (empty($full_name) || empty($email) || empty($password)) {
                    $error = "Barcha maydonlarni to'ldiring.";
                } elseif (strlen($password) < 8) {
                    $error = "Parol kamida 8 ta belgidan iborat bo'lishi kerak.";
                } elseif (!preg_match($password_pattern, $password)) {
                    $error = "Parol juda zaif! Unda kamida 1 ta katta harf, 1 ta kichik harf, 1 ta raqam va 1 ta maxsus belgi (@,#,$,%,^,&,*) bo'lishi shart.";
                } else {
                    // Bazaga yozish
                    try {
                        if ($userObj->register($full_name, $email, $password)) {
                            $success = "Muvaffaqiyatli ro'yxatdan o'tdingiz! <a href='login.php'>Tizimga kiring</a>";
                            $_SESSION['captcha_attempts'] = 0; // Muvaffaqiyatli bo'lsa urinishni tozalaymiz
                        }
                    } catch (PDOException $e) {
                        // Agar email bazada mavjud bo'lsa (UNIQUE sharti ishlaganda)
                        $error = "Bu email avval ro'yxatdan o'tgan!";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Ro'yxatdan o'tish - Portfolio</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 350px;}
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; margin-bottom: 10px; font-size: 14px; line-height: 1.4; }
        .success { color: green; margin-bottom: 10px; font-size: 14px; }
        .captcha-box { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Ro'yxatdan o'tish</h2>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php else: ?>

    <form action="register.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label>Ism-sharifingiz:</label>
        <input type="text" name="full_name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Parol (min 8 belgi, katta-kichik harf, raqam va belgi):</label>
        <input type="password" name="password" required>

        <label>Matematik misolni yeching:</label>
        <div class="captcha-box">
            <img src="includes/captcha.php" alt="Captcha" id="captcha-img">
            <button type="button" onclick="document.getElementById('captcha-img').src='includes/captcha.php?'+Math.random()">🔄 Yangilash</button>
        </div>
        <input type="text" name="captcha" placeholder="Javobni kiriting" required autocomplete="off">

        <?php if (isset($_SESSION['blocked_until']) && $_SESSION['blocked_until'] > time()): ?>
            <button type="button" class="btn" style="background:#ccc; cursor:not-allowed;" disabled>Forma bloklangan</button>
        <?php else: ?>
            <button type="submit" class="btn">Ro'yxatdan o'tish</button>
        <?php endif; ?>
    </form>
    
    <p style="text-align: center; font-size: 14px; margin-top: 15px;">
        Akkauntingiz bormi? <a href="login.php">Kirish</a>
    </p>

    <?php endif; ?>
</div>

</body>
</html>