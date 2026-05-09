<?php
session_start();
require_once 'config/db.php';
require_once 'classes/User.php';

// Cookie orqali avtomatik kirishni tekshiramiz (A7 talabi)
// Agar cookie mavjud bo'lsa, to'g'ridan-to'g'ri dashboard.php ga yo'naltiramiz
if (isset($_COOKIE['remember_user'])) {
    $_SESSION['user_id'] = $_COOKIE['remember_user'];
    header("Location: dashboard.php");
    exit;
}

// Agar foydalanuvchi allaqachon login qilgan bo'lsa, uni ham o'tkazib yuboramiz
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$userObj = new User($pdo);
$error = '';

// CSRF Token va Captcha urinishlari hisoblagichi (A10, A11)[cite: 1]
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!isset($_SESSION['login_captcha_attempts'])) {
    $_SESSION['login_captcha_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Blokirovkani tekshirish[cite: 1]
    if (isset($_SESSION['login_blocked_until']) && $_SESSION['login_blocked_until'] > time()) {
        $remaining = $_SESSION['login_blocked_until'] - time();
        $error = "Siz 3 marta xato qildingiz. Iltimos, $remaining soniya kuting.";
    } else {
        // CSRF ni tekshirish[cite: 1]
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = "Xavfsizlik xatosi (CSRF). Iltimos formani yangilang.";
        } else {
            // Captchani tekshirish[cite: 1]
            $user_captcha = trim($_POST['captcha']);
            
            if ($user_captcha != $_SESSION['captcha_answer']) {
                $_SESSION['login_captcha_attempts']++;
                $error = "Captcha noto'g'ri. Urinishlar: " . $_SESSION['login_captcha_attempts'];
                
                if ($_SESSION['login_captcha_attempts'] >= 3) {
                    $_SESSION['login_blocked_until'] = time() + 60;
                    $_SESSION['login_captcha_attempts'] = 0; 
                    $error = "3 marta xato qildingiz. Forma 1 daqiqaga bloklandi!";
                }
            } else {
                // Ma'lumotlarni tozalab olish (A10)[cite: 1]
                $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'];

                // Bazadan foydalanuvchini tekshirish
                $loggedInUser = $userObj->login($email, $password);

                if ($loggedInUser) {
                    // Muvaffaqiyatli kirish: Sessiyaga yozish[cite: 1]
                    $_SESSION['user_id'] = $loggedInUser['id'];
                    $_SESSION['role'] = $loggedInUser['role'];
                    $_SESSION['login_captcha_attempts'] = 0; // Xatolarni tozalash
                    
                    // "Meni eslab qol" belgilangan bo'lsa, Cookie o'rnatish (7 kun)[cite: 1]
                    if (isset($_POST['remember'])) {
                        setcookie('remember_user', $loggedInUser['id'], time() + (7 * 24 * 60 * 60), "/");
                    }
                    
                    // Kabinetga yo'naltirish
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Email yoki parol noto'g'ri!";
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
    <title>Tizimga kirish - Portfolio</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 350px;}
        input[type="email"], input[type="password"], input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; margin-bottom: 10px; font-size: 14px; }
        .captcha-box { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
        .remember-box { display: flex; align-items: center; gap: 5px; margin-bottom: 15px; font-size: 14px;}
        .remember-box input { width: auto; margin: 0; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Tizimga kirish</h2>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Parol:</label>
        <input type="password" name="password" required>

        <div class="remember-box">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Meni eslab qol</label>
        </div>

        <label>Matematik misolni yeching:</label>
        <div class="captcha-box">
            <img src="includes/captcha.php" alt="Captcha" id="captcha-img">
            <button type="button" onclick="document.getElementById('captcha-img').src='includes/captcha.php?'+Math.random()">🔄 Yangilash</button>
        </div>
        <input type="text" name="captcha" placeholder="Javobni kiriting" required autocomplete="off">

        <?php if (isset($_SESSION['login_blocked_until']) && $_SESSION['login_blocked_until'] > time()): ?>
            <button type="button" class="btn" style="background:#ccc; cursor:not-allowed;" disabled>Forma bloklangan</button>
        <?php else: ?>
            <button type="submit" class="btn">Kirish</button>
        <?php endif; ?>
    </form>
    
    <p style="text-align: center; font-size: 14px; margin-top: 15px;">
        Akkauntingiz yo'qmi? <a href="register.php">Ro'yxatdan o'tish</a>
    </p>
</div>

</body>
</html>