<?php
class Database {
    private $connection;
    
    public function __construct() {
        $this->connect();
        $this->createTables();
    }
    
    private function connect() {
        try {
            $dbUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
            if (!$dbUrl) {
                die('DATABASE_URL environment variable not set');
            }
            
            // Parse the DATABASE_URL for MySQL
            $parsed = parse_url($dbUrl);
            if (!$parsed) {
                die('Invalid DATABASE_URL format');
            }
            
            $host = $parsed['host'] ?? 'localhost';
            $port = $parsed['port'] ?? 3306; // MySQL default port
            $database = ltrim($parsed['path'] ?? '', '/');
            $username = $parsed['user'] ?? '';
            $password = $parsed['pass'] ?? '';
            
            // Build MySQL DSN
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            
            $this->connection = new PDO($dsn, $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function createTables() {
        try {
            // Create users table
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->connection->exec($sql);
            
            // Create blog_posts table
            $sql = "CREATE TABLE IF NOT EXISTS blog_posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                author_id INT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->connection->exec($sql);
            
        } catch (PDOException $e) {
            die("Error creating tables: " . $e->getMessage());
        }
    }
}
?>
