<?php
class User {
    // Encapsulation: private xususiyatlar
    private $db;
    protected $id;
    protected $full_name;
    protected $email;

    // Konstruktor majburiy
    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    // Ro'yxatdan o'tish[cite: 1]
    public function register($name, $email, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Xavfsiz hash[cite: 1]
        
        $sql = "INSERT INTO users (full_name, email, password) VALUES (:name, :email, :pass)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':pass' => $hashed_password
        ]);
    }

    // Tizimga kirish[cite: 1]
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Getter metodlar[cite: 1]
    public function getInfo($user_id) {
        $sql = "SELECT id, full_name, email, role FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $user_id]);
        return $stmt->fetch();
    }
}
?>