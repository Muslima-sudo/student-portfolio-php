<?php
session_start();

// Barcha sessiya o'zgaruvchilarini tozalash
$_SESSION = array();

// Sessiyani butunlay yo'q qilish
session_destroy();

// Agar "Meni eslab qol" cookie-si mavjud bo'lsa, uning vaqtini o'tmishga o'tkazib o'chiramiz
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Login sahifasiga qaytarish
header("Location: login.php");
exit;
?>