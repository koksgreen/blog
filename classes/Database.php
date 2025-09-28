<?php
class Database
{
    private $connection;

    public function __construct()
    {
        $this->connect();         // ✅ corrected method name
        $this->createTables();    // make sure this method exists
    }

    private function connect()
    {
        try {
            $dbUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');

            if ($dbUrl) {
                $parsed = parse_url($dbUrl);
                if (!$parsed) {
                    die('Invalid DATABASE_URL format');
                }

                $host = $parsed['host'] ?? 'localhost';
                $port = $parsed['port'] ?? 3306;
                $database = ltrim($parsed['path'] ?? '', '/');
                $username = $parsed['user'] ?? 'root';
                $password = $parsed['pass'] ?? '';
            } else {
                $host = '127.0.0.1';
                $port = 3306;
                $database = 'myblog';
                $username = 'root';
                $password = '';
            }

            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

            $this->connection = new PDO($dsn, $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function createTables()
    {
        // Example: create users table
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->connection->exec($sql);

        // You can add blog_posts table creation here too
    }

    private function createBlogPostsTable()
    {
        try {
            $sql = "
            CREATE TABLE IF NOT EXISTS blog_posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                author_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";

            $this->connection->exec($sql);
            // echo "blog_posts table created successfully!";
        } catch (PDOException $e) {
            die("Failed to create blog_posts table: " . $e->getMessage());
        }
    }
}
