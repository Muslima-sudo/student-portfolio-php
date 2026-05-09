<?php
class Project {
    private $db;

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    // Loyihani saqlash: agar $id berilsa UPDATE, yo'qsa INSERT qiladi
    public function save($user_id, $category_id, $title, $description, $project_url, $id = null) {
        if ($id) {
            $sql = "UPDATE projects 
                    SET category_id = :cat_id, title = :title, description = :desc, project_url = :url 
                    WHERE id = :id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':cat_id' => $category_id,
                ':title' => $title,
                ':desc' => $description,
                ':url' => $project_url,
                ':id' => $id,
                ':user_id' => $user_id
            ]);
        } else {
            $sql = "INSERT INTO projects (user_id, category_id, title, description, project_url) 
                    VALUES (:user_id, :cat_id, :title, :desc, :url)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':user_id' => $user_id,
                ':cat_id' => $category_id,
                ':title' => $title,
                ':desc' => $description,
                ':url' => $project_url
            ]);
        }
    }

    // O'z loyihasini o'chirish
    public function delete($id, $user_id) {
        $sql = "DELETE FROM projects WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $user_id]);
    }

    // Barcha loyihalarni olish (JOIN yordamida kategoriya nomi bilan)
    public function getAll($user_id = null) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM projects p 
                JOIN categories c ON p.category_id = c.id";
        
        // Agar aniq bir foydalanuvchining loyihalari kerak bo'lsa
        if ($user_id) {
            $sql .= " WHERE p.user_id = :user_id";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($user_id) {
            $stmt->execute([':user_id' => $user_id]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
}
?>