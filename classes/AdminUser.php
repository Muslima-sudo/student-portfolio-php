<?php
require_once 'User.php';

// Meros (inheritance)[cite: 1]
class AdminUser extends User {
    
    private $db_admin;

    public function __construct($db_conn) {
        parent::__construct($db_conn);
        $this->db_admin = $db_conn;
    }

    // Admin uchun maxsus metod[cite: 1]
    public function deleteAnyProject($project_id) {
        $sql = "DELETE FROM projects WHERE id = :id";
        $stmt = $this->db_admin->prepare($sql);
        return $stmt->execute([':id' => $project_id]);
    }
}
?>