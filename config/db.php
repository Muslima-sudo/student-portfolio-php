<?php
// Ma'lumotlar bazasi sozlamalari
$host = 'localhost';
$db   = 'portfolio_db';
$user = 'portfolio_user'; // Ubuntu'dagi mysql foydalanuvchi nomingiz
$pass = '12345';     // Ubuntu'dagi mysql parolingiz
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Xatolarni ko'rsatish
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Ma'lumotlarni massiv ko'rinishida olish
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Haqiqiy prepared statementlardan foydalanish
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Xatolik yuz bersa, xabarni ko'rsatamiz (ishlab chiqarishda buni yashirish kerak)
     die("Ma'lumotlar bazasiga ulanib bo'lmadi: " . $e->getMessage());
}
?>