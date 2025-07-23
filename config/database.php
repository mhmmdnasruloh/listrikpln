<?php
// config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "pembayaran";
    private $username = "root";
    private $password = ""zxc;
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                 $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}

// Fungsi helper untuk query
function executeQuery($query, $params = []) {
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

// Start session
session_start();

// Fungsi untuk check login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk check role
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}
?>