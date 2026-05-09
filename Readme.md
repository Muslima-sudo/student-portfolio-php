STUDENT PORTFOLIO VA BOSHQARUV TIZIMI
"Web Texnologiyalar" fani uchun yakuniy loyiha


Texnologiyalar: PHP (sof), MySQL (PDO), HTML, CSS (Frameworksiz).
Muhit: LAMP Stack (Linux, MySQL, PHP lokal serveri)

O'RNATISH VA ISHGA TUSHIRISH YO'RIQNOMASI:

1. MA'LUMOTLAR BAZASINI O'RNATISH:
   - MySQL yoki phpMyAdmin ga kiring.
   - "database" papkasidagi "portfolio.sql" faylini import qiling.
   (Ushbu fayl avtomatik ravishda "portfolio_db" bazasini va 
    kerakli 3 ta jadvalni yaratadi).

2. BAZAGA ULANISHNI SOZLACH:
   - "config/db.php" faylini oching.
   - O'zingizning MySQL root (yoki maxsus yaratilgan) foydalanuvchi nomingiz
     va parolingizni kiriting ($user va $pass o'zgaruvchilari).

3. LOYIHANI ISHGA TUSHIRISH (Terminal orqali):
   - Loyiha papkasida terminalni oching.
   - Quyidagi buyruq orqali PHP lokal serverini ko'taring:
     php -S localhost:8000
   - Brauzerda http://localhost:8000 manziliga kiring.

AMALGA OSHIRILGAN ASOSIY IMKONIYATLAR:
- To'liq OOP yondashuvi (Sinflar, Meros, Inkapsulyatsiya).
- PDO va Prepared Statements orqali SQL Injection himoyasi.
- XSS himoyasi va CSRF tokenlar.
- Parollar password_hash() orqali shifrlangan.
- Cookie orqali "Meni eslab qol" funksiyasi (7 kunlik).
- Matematik Captcha va 3 marta xatodan so'ng 1 daqiqalik blokirovka.
- Bitta fayl ichida to'liq CRUD (projects.php).

Loyihani tayyorladi: Muslima
