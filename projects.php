<?php
session_start();

// Sessionsiz kishilarni qaytarib yuboramiz
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/db.php';
require_once 'classes/Project.php';

$projectObj = new Project($pdo);
$user_id = $_SESSION['user_id'];
$message = '';

// --- 1. KATEGORIYALARNI OLISH (Forma uchun dropdown) ---
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

// --- 2. O'CHIRISH (DELETE) AMALI - $_GET orqali ishlaydi (A7 talabi) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $project_id = intval($_GET['id']);
    if ($projectObj->delete($project_id, $user_id)) {
        $message = "<div class='success'>Loyiha muvaffaqiyatli o'chirildi!</div>";
    }
}

// --- 3. QO'SHISH VA TAHRIRLASH (CREATE & UPDATE) - $_POST orqali ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_project'])) {
    $title = trim($_POST['title']);
    $category_id = intval($_POST['category_id']);
    $description = trim($_POST['description']);
    $project_url = trim($_POST['project_url']);
    $edit_id = isset($_POST['project_id']) && !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;

    // Validatsiya (A10 talabi)[cite: 1]
    if (empty($title) || empty($category_id)) {
        $message = "<div class='error'>Sarlavha va Kategoriya tanlanishi shart!</div>";
    } else {
        if ($projectObj->save($user_id, $category_id, $title, $description, $project_url, $edit_id)) {
            $message = "<div class='success'>Loyiha saqlandi!</div>";
        } else {
            $message = "<div class='error'>Xatolik yuz berdi.</div>";
        }
    }
}

// --- 4. BARCHA LOYIHALARNI OLISH VA QAYTA ISHLASH ---
// Ma'lumotlar bazasidan userning barcha loyihalarini olamiz (Project.php da JOIN qilingan)[cite: 1]
$projects = $projectObj->getAll($user_id);

// A3, A4 Talablari: PHP da qidiruv va massivlar bilan ishlash[cite: 1]
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = strtolower(trim($_GET['search'])); // strtolower() bilan kichik harfga o'tkazamiz
    
    // array_filter va strpos orqali massivdan qidirish (A3 va A4)[cite: 1]
    $projects = array_filter($projects, function($project) use ($search_query) {
        $title_lower = strtolower($project['title']);
        $desc_lower = strtolower($project['description']);
        // Agar qidirilgan so'z sarlavha yoki tavsif ichida topilsa (strpos ishlatamiz)
        return (strpos($title_lower, $search_query) !== false || strpos($desc_lower, $search_query) !== false);
    });
}

// Tahrirlash tugmasi bosilganda formaga ma'lumotlarni yuklash uchun
$edit_project = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    // array_filter yordamida kerakli loyihani topib olamiz
    $found = array_filter($projects, function($p) use ($edit_id) {
        return $p['id'] == $edit_id;
    });
    if (!empty($found)) {
        $edit_project = array_values($found)[0];
    }
}
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Loyihalar - Portfolio</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .header a { background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
        .form-box { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        input[type="text"], input[type="url"], select, textarea { width: 100%; padding: 8px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .search-box { margin-bottom: 20px; display: flex; gap: 10px; }
        .search-box input { margin: 0; width: 70%; }
        .search-box button { width: 30%; background: #007bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #333; color: white; }
        .success { color: green; margin-bottom: 15px; font-weight: bold; }
        .error { color: red; margin-bottom: 15px; font-weight: bold; }
        .action-btn { padding: 5px 10px; text-decoration: none; color: white; border-radius: 3px; font-size: 12px; }
        .btn-edit { background: #ffc107; color: black; }
        .btn-delete { background: #dc3545; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Mening Loyihalarim</h2>
        <a href="dashboard.php">← Kabinetga qaytish</a>
    </div>

    <?php echo $message; ?>

    <!-- Qo'shish / Tahrirlash Formasi -->
    <div class="form-box">
        <h3><?php echo $edit_project ? "Loyihani tahrirlash" : "Yangi loyiha qo'shish"; ?></h3>
        <form action="projects.php" method="POST">
            <!-- Tahrirlanayotgan bo'lsa ID yashirin tarzda jo'natiladi -->
            <input type="hidden" name="project_id" value="<?php echo $edit_project ? $edit_project['id'] : ''; ?>">
            
            <label>Loyiha sarlavhasi:</label>
            <input type="text" name="title" required value="<?php echo $edit_project ? htmlspecialchars($edit_project['title']) : ''; ?>">
            
            <label>Kategoriya:</label>
            <select name="category_id" required>
                <option value="">Tanlang...</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_project && $edit_project['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label>Loyiha URL manzili (ixtiyoriy):</label>
            <input type="url" name="project_url" value="<?php echo $edit_project ? htmlspecialchars($edit_project['project_url']) : ''; ?>">
            
            <label>Qisqacha tavsif:</label>
            <textarea name="description" rows="3"><?php echo $edit_project ? htmlspecialchars($edit_project['description']) : ''; ?></textarea>
            
            <button type="submit" name="save_project"><?php echo $edit_project ? "Yangilash" : "Saqlash"; ?></button>
            <?php if($edit_project): ?>
                <a href="projects.php" style="margin-left:10px; color:blue;">Bekor qilish</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Qidiruv formasi -->
    <form class="search-box" action="projects.php" method="GET">
        <input type="text" name="search" placeholder="Loyihalar orasidan qidirish..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">Qidirish</button>
    </form>

    <!-- Loyihalar jadvali -->
    <table>
        <thead>
            <tr>
                <th>TR</th>
                <th>Sarlavha</th>
                <th>Kategoriya</th>
                <th>Tavsif</th>
                <th>Havola</th>
                <th>Amallar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($projects) > 0): ?>
                <?php $tr = 1; foreach ($projects as $row): ?>
                <tr>
                    <td><?php echo $tr++; ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td>
                        <?php 
                        // A4 Talabi: Satrlarni kesish[cite: 1]
                        $desc = htmlspecialchars($row['description']);
                        if (strlen($desc) > 50) {
                            echo substr($desc, 0, 50) . '...';
                        } else {
                            echo $desc;
                        }
                        ?>
                    </td>
                    <td>
                        <?php if(!empty($row['project_url'])): ?>
                            <a href="<?php echo htmlspecialchars($row['project_url']); ?>" target="_blank">Ko'rish</a>
                        <?php else: ?>
                            Yo'q
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- A7 Talabi: $_GET orqali parametrlar uzatish[cite: 1] -->
                        <a href="projects.php?action=edit&id=<?php echo $row['id']; ?>" class="action-btn btn-edit">Tahrirlash</a>
                        <a href="projects.php?action=delete&id=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Rostdan ham o\'chirmoqchimisiz?');">O'chirish</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Hozircha loyihalar yo'q.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>