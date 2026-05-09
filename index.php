<?php
require_once 'config/db.php';

// A9 Talabi: COUNT(*) bilan jami loyihalar sonini hisoblash[cite: 1]
$count_sql = "SELECT COUNT(*) as total FROM projects";
$total_projects = $pdo->query($count_sql)->fetchColumn();

// A9 Talabi: LIKE orqali qidiruv tizimi[cite: 1]
$search_query = '';
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    // Tozalash
    $search_query = htmlspecialchars(trim($_GET['q']));
    
    // LIKE bilan qidirish so'rovi (JOIN yordamida muallif va kategoriya ham olinadi)[cite: 1]
    $sql = "SELECT p.*, u.full_name as author, c.name as category 
            FROM projects p 
            JOIN users u ON p.user_id = u.id 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.title LIKE :search OR p.description LIKE :search 
            ORDER BY p.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    // LIKE uchun % belgilarini qo'shamiz
    $stmt->execute([':search' => "%$search_query%"]);
    $projects = $stmt->fetchAll();
} else {
    // Oddiy barcha loyihalarni chiqarish (A9 talabi - LIMIT 10 qo'shildi)[cite: 1]
    $sql = "SELECT p.*, u.full_name as author, c.name as category 
            FROM projects p 
            JOIN users u ON p.user_id = u.id 
            JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC 
            LIMIT 10"; 
    $stmt = $pdo->query($sql);
    $projects = $stmt->fetchAll();
}

// Yuqori qismni (Header) chaqiramiz[cite: 1]
include 'includes/header.php';
?>

<div class="container">
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: #343a40;">Talabalar Portfolio Dasturiga Xush Kelibsiz!</h1>
        <p style="font-size: 18px; color: #6c757d;">Bu yerda talabalarimiz tomonidan yaratilgan ajoyib loyihalar bilan tanishishingiz mumkin.</p>
        
        <!-- Statistikani chiqarish -->
        <div class="badge" style="font-size: 16px; padding: 10px 20px; background: #28a745; color: white;">
            Tizimdagi jami loyihalar soni: <?php echo $total_projects; ?> ta
        </div>
    </div>

    <!-- LIKE qidiruv formasi -->
    <div style="max-width: 650px; margin: 0 auto 30px auto; background: #fff; padding: 10px; border-radius: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
        <form action="index.php" method="GET" style="display: flex; gap: 10px; align-items: center;">
            <input type="text" name="q" placeholder="Loyihalarni nomi yoki tavsifi bo'yicha qidiring..." value="<?php echo $search_query; ?>" style="margin: 0; border: none; outline: none; padding: 12px 20px; flex: 1; font-size: 15px; border-radius: 40px; background: transparent;">
            <button type="submit" class="btn" style="border-radius: 30px; padding: 12px 25px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,123,255,0.3);">Qidirish</button>
        </form>
    </div>

    <?php if ($search_query): ?>
        <h3>"<?php echo $search_query; ?>" bo'yicha qidiruv natijalari:</h3>
    <?php endif; ?>

    <!-- Loyihalar tarmog'i (Grid) -->
    <div class="portfolio-grid">
        <?php if (count($projects) > 0): ?>
            <?php foreach ($projects as $project): ?>
                <div class="portfolio-item">
                    <span class="badge"><?php echo htmlspecialchars($project['category']); ?></span>
                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                    <p style="font-size: 14px; color: #555;">
                        <?php 
                        $desc = htmlspecialchars($project['description']);
                        // A4 Talabi: Satrlarni qisqartirish[cite: 1]
                        echo (strlen($desc) > 80) ? substr($desc, 0, 80) . '...' : $desc; 
                        ?>
                    </p>
                    <hr style="border: 0; border-top: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                        <strong>Muallif: <?php echo htmlspecialchars($project['author']); ?></strong>
                        <?php if(!empty($project['project_url'])): ?>
                            <a href="<?php echo htmlspecialchars($project['project_url']); ?>" target="_blank" class="btn" style="padding: 5px 10px; font-size: 12px;">Ko'rish</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: white; border-radius: 8px;">
                <h3 style="color: #dc3545;">Hech qanday loyiha topilmadi!</h3>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
// Pastki qismni (Footer) chaqiramiz[cite: 1]
include 'includes/footer.php'; 
?>