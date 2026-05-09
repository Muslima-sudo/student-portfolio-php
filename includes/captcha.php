<?php
session_start();

// Tasodifiy ikkita son yaratamiz[cite: 1]
$num1 = rand(1, 9);
$num2 = rand(1, 9);

// Captcha javobini $_SESSION da saqlaymiz[cite: 1]
$_SESSION['captcha_answer'] = $num1 + $num2;

// Rasm o'lchamlari
$width = 120;
$height = 40;

header('Content-Type: image/png');
$image = imagecreatetruecolor($width, $height);

// Ranglar
$bg_color = imagecolorallocate($image, 240, 240, 240); // Ochiq kulrang fon
$text_color = imagecolorallocate($image, 0, 0, 0);     // Qora matn
$line_color = imagecolorallocate($image, 150, 150, 150); // Chiziqlar rangi

// Fonni bo'yash
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Shovqin (chiziqlar) qo'shish (AI emas, qo'lda yozilgan vizual xavfsizlik effekti)
for($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// Matematik ifodani rasmga yozish
$math_string = "$num1 + $num2 = ?";
imagestring($image, 5, 25, 12, $math_string, $text_color);

// Rasmni chiqarish va xotirani tozalash
imagepng($image);
imagedestroy($image);
?>