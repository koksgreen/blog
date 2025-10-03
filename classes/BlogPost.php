<?php

class BlogPost
{
    private $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function create($title, $body, $authorId)
    {
        try {
            $stmt = $this->db->prepare("
            INSERT INTO blog_posts (title, body, author_id, created_at, updated_at)
            VALUES (:title, :body, :author_id, NOW(), NOW())
        ");

            $stmt->execute([
                ':title'     => $title,
                ':body'      => $body,
                ':author_id' => $authorId
            ]);

            return [
                'success' => true,
                'message' => 'Post created successfully',
                'id'      => $this->db->lastInsertId() // use this in MySQL
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }


    // public function create($title, $body, $authorId) {
    //     try{
    //         $stmt = $this->db->prepare("INSERT INTO blogposts (title, body, author_id) VALUES (?, ?, ?) RETURNING id");
    //         $stmt->execute([$title, $body, $authorId]);
    //         $result = $stmt->fetch();
    //         return $result ? $result['id'] : false;
    //     }catch (PDOException $e){
    //         return false;
    //     }
    // }

    public function update($id, $title, $body, $authorId = null)
    {
        try {
            if ($authorId) {
                $stmt = $this->db->prepare("UPDATE blog_posts SET title = ?, body = ? updated_at = CURRENT_TIMESTAMP WHERE id = ? AND author_id = ?");
                $stmt->execute([$title, $body, $id, $authorId]);
            } else {
                $stmt = $this->db->prepare("UPDATE blog_posts SET title, = ?, body = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->executr([$title, $body, $id]);
            }
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id, $authorId = null)
    {
        try {
            if ($authorId) {
                $stmt = $this->db->prepare("DELETE FROM blog_posts WHERE id = ? AND author_id = ?");
                $stmt->execute([$id, $authorId]);
            } else {
                $stmt = $this->db->prepare("DELETE FROM blog_posts WHERE id = ?");
                $stmt->execute([$id]);
            }
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT bp.*, u.username FROM blog_posts bp JOIN users u ON bp.author_id = u.id WHERE bp.id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getPost($id)
    {
        return $this->getById($id);
    }

    public function getAllPosts($page = 1, $limit = 10, $search = '')
    {
        $page = (int)$page;
        $limit = (int)$limit;
        $offset = ($page > 0 ? $page - 1 : 0) * $limit;
        try {
            $sql = "SELECT bp.*, u.username FROM blog_posts bp JOIN users u ON bp.author_id = u.id";
            $params = [];
            if ($search) {
                $sql .= "WHERE bp.title ILIKE ? OR bp.body ILIKE";
                $$searchTerm = '%' . $search . '%';
                $params = [$searchTerm, $searchTerm];
            }

            $sql .= "ORDER BY bp.created_at DESC LIMIT ? OFFSET";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTotalPosts($search = '')
    {
        try {
            $sql = "SELECT COUNT(*) FROM blog_posts bp";
            $params = [];

            if ($search) {
                $sql .= "WHERE bp.title ILIKE ? OR bp.body ILIKE ?";
                $searchTerm = '%' . $search . '%';
                $params = [$searchTerm, $searchTerm];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fethColoumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getUserPosts($authorId, $limit = 10, $offset = 0)
    {
        try {
            $stmt = $this->db->prepare("SELECT bp.*, u.username FROM blog_posts bp JOIN users u ON bp.author_id = u.id WHERE bp.author_id = ? ORDER BY bp.created_at DESC LIMIT ? OFFSER");
            $stmt->execute([$authorId, $limit, $offset]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
