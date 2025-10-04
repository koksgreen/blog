<?php
    header("Content-Type: application/json");

    require_once __DIR__ . '/classes/Database.php';
    require_once __DIR__ . '/classes/User.php';
    require_once __DIR__ . '/classes/BlogPost.php';
    require_once __DIR__ . '/classes/Authentication.php';

    

class API {
    private $db;
    private $user;
    private $blogPost;
    private $security;
    
    public function __construct() {
        // Set JSON content type
        header('Content-Type: application/json; charset=utf-8');
        
        // Enable CORS for API access
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        $this->db = new Database();
        $this->user = new User($this->db);
        $this->blogPost = new BlogPost($this->db);
        $this->security = new Authentication();
        
        // Start session for authentication
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Handle both URL path and query parameter routing
        if (isset($_GET['resource'])) {
            // Query parameter approach: /api.php?resource=posts&id=1
            $resource = $_GET['resource'];
            $id = $_GET['id'] ?? null;
            $segments = [$resource];
            if ($id) $segments[] = $id;
        } else {
            // Path-based approach: /api.php/posts/1
            // Remove /api prefix if present  
            $path = preg_replace('/^\/api/', '', $path);
            // Remove api.php from path
            $path = preg_replace('/^\/api\.php/', '', $path);
            
            // Parse path segments
            $segments = array_filter(explode('/', $path));
            $segments = array_values($segments); // Re-index array
        }
        
        try {
            // Route to appropriate handler
            if (empty($segments) || (count($segments) == 1 && empty($segments[0]))) {
                $this->sendResponse(['message' => 'Blog API v1.0', 'endpoints' => $this->getEndpoints()]);
                return;
            }
            
            $resource = $segments[0];
            $id = isset($segments[1]) ? $segments[1] : null;
            
            switch ($resource) {
                case 'posts':
                    $this->handlePosts($method, $id);
                    break;
                case 'auth':
                    $this->handleAuth($method, $id);
                    break;
                case 'users':
                    $this->handleUsers($method, $id);
                    break;
                default:
                    $this->sendError('Resource not found', 404);
            }
        } catch (Exception $e) {
            // Log the actual error for debugging but don't expose details to client
            error_log('API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $this->sendError('Internal server error', 500);
        }
    }
    
    private function handlePosts($method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getPost($id);
                } else {
                    $this->getPosts();
                }
                break;
            case 'POST':
                $this->createPost();
                break;
            case 'PUT':
            case 'PATCH':
                $this->updatePost($id);
                break;
            case 'DELETE':
                $this->deletePost($id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    private function handleAuth($method, $action) {
        switch ($method) {
            case 'POST':
                switch ($action) {
                    case 'login':
                        $this->login();
                        break;
                    case 'register':
                        $this->register();
                        break;
                    case 'logout':
                        $this->logout();
                        break;
                    case 'check':
                        $this->checkAuth();
                        break;
                    default:
                        $this->sendError('Invalid auth action', 400);
                }
                break;
            case 'GET':
                if ($action === 'status') {
                    $this->getAuthStatus();
                } else {
                    $this->sendError('Method not allowed', 405);
                }
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    private function handleUsers($method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getUser($id);
                } else {
                    $this->getUsers();
                }
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    // Post endpoints
    private function getPosts() {
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 10);
        $search = $_GET['search'] ?? '';
        
        // Validate pagination
        $page = max(1, $page);
        $limit = max(1, min(50, $limit)); // Limit between 1-50
        
        $posts = $this->blogPost->getAllPosts($page, $limit, $search);
        $totalPosts = $this->blogPost->getTotalPosts($search);
        $totalPages = ceil($totalPosts / $limit);
        
        $this->sendResponse([
            'posts' => $posts,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_posts' => $totalPosts,
                'per_page' => $limit,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ]);
    }
    
    private function getPost($id) {
        if (!is_numeric($id)) {
            $this->sendError('Invalid post ID', 400);
            return;
        }
        
        $post = $this->blogPost->getPost($id);
        if (!$post) {
            $this->sendError('Post not found', 404);
            return;
        }
        
        $this->sendResponse(['post' => $post]);
    }
    
    private function createPost() {
        if (!$this->isAuthenticated()) {
            $this->sendError('Authentication required', 401);
            return;
        }
        
        $data = $this->getRequestData();
        
        if (!isset($data['title']) || !isset($data['body'])) {
            $this->sendError('Title and body are required', 400);
            return;
        }
        
        $title = trim($data['title']);
        $body = trim($data['body']);
        
        if (empty($title) || empty($body)) {
            $this->sendError('Title and body cannot be empty', 400);
            return;
        }
        
        if (strlen($title) < 5) {
            $this->sendError('Title must be at least 5 characters long', 400);
            return;
        }
        
        if (strlen($body) < 10) {
            $this->sendError('Body must be at least 10 characters long', 400);
            return;
        }
        
        $postId = $this->blogPost->create($title, $body, $_SESSION['user_id']);
        
        if ($postId) {
            $post = $this->blogPost->getPost($postId);
            $this->sendResponse(['message' => 'Post created successfully', 'post' => $post], 201);
        } else {
            $this->sendError('Failed to create post', 500);
        }
    }
    
    private function updatePost($id) {
        if (!$this->isAuthenticated()) {
            $this->sendError('Authentication required', 401);
            return;
        }
        
        if (!is_numeric($id)) {
            $this->sendError('Invalid post ID', 400);
            return;
        }
        
        $post = $this->blogPost->getPost($id);
        if (!$post) {
            $this->sendError('Post not found', 404);
            return;
        }
        
        if ($post['author_id'] != $_SESSION['user_id']) {
            $this->sendError('You can only update your own posts', 403);
            return;
        }
        
        $data = $this->getRequestData();
        
        $title = isset($data['title']) ? trim($data['title']) : $post['title'];
        $body = isset($data['body']) ? trim($data['body']) : $post['body'];
        
        if (empty($title) || empty($body)) {
            $this->sendError('Title and body cannot be empty', 400);
            return;
        }
        
        if (strlen($title) < 5) {
            $this->sendError('Title must be at least 5 characters long', 400);
            return;
        }
        
        if (strlen($body) < 10) {
            $this->sendError('Body must be at least 10 characters long', 400);
            return;
        }
        
        $success = $this->blogPost->update($id, $title, $body);
        
        if ($success) {
            $updatedPost = $this->blogPost->getPost($id);
            $this->sendResponse(['message' => 'Post updated successfully', 'post' => $updatedPost]);
        } else {
            $this->sendError('Failed to update post', 500);
        }
    }
    
    private function deletePost($id) {
        if (!$this->isAuthenticated()) {
            $this->sendError('Authentication required', 401);
            return;
        }
        
        if (!is_numeric($id)) {
            $this->sendError('Invalid post ID', 400);
            return;
        }
        
        $post = $this->blogPost->getPost($id);
        if (!$post) {
            $this->sendError('Post not found', 404);
            return;
        }
        
        if ($post['author_id'] != $_SESSION['user_id']) {
            $this->sendError('You can only delete your own posts', 403);
            return;
        }
        
        $success = $this->blogPost->delete($id);
        
        if ($success) {
            $this->sendResponse(['message' => 'Post deleted successfully']);
        } else {
            $this->sendError('Failed to delete post', 500);
        }
    }
    
    // Auth endpoints
    private function login() {
        $data = $this->getRequestData();
        
        if (!isset($data['username']) || !isset($data['password'])) {
            $this->sendError('Username and password are required', 400);
            return;
        }
        
        $user = $this->user->login($data['username'], $data['password']);
        
        if ($user) {
            $this->sendResponse([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ]);
        } else {
            $this->sendError('Invalid username or password', 401);
        }
    }
    
    private function register() {
        $data = $this->getRequestData();
        
        if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
            $this->sendError('Username, email, and password are required', 400);
            return;
        }
        
        $username = trim($data['username']);
        $email = trim($data['email']);
        $password = $data['password'];
        
        // Validation
        if (strlen($username) < 3) {
            $this->sendError('Username must be at least 3 characters long', 400);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendError('Invalid email format', 400);
            return;
        }
        
        if (strlen($password) < 6) {
            $this->sendError('Password must be at least 6 characters long', 400);
            return;
        }
        
        $success = $this->user->register($username, $email, $password);
        
        if ($success === true) {
            $this->sendResponse(['message' => 'Registration successful'], 201);
        } else {
            $this->sendError($success, 400); // $success contains error message
        }
    }
    
    private function logout() {
        $this->user->logout();
        $this->sendResponse(['message' => 'Logout successful']);
    }
    
    private function checkAuth() {
        if ($this->isAuthenticated()) {
            $this->sendResponse([
                'authenticated' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username']
                ]
            ]);
        } else {
            $this->sendResponse(['authenticated' => false]);
        }
    }
    
    private function getAuthStatus() {
        $this->checkAuth();
    }
    
    // User endpoints
    private function getUsers() {
        // Only return public user info
        $users = $this->user->getAllUsers();
        $publicUsers = array_map(function($user) {
            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'created_at' => $user['created_at']
            ];
        }, $users);
        
        $this->sendResponse(['users' => $publicUsers]);
    }
    
    private function getUser($id) {
        if (!is_numeric($id)) {
            $this->sendError('Invalid user ID', 400);
            return;
        }
        
        $user = $this->user->getUserById($id);
        if (!$user) {
            $this->sendError('User not found', 404);
            return;
        }
        
        // Return only public info
        $publicUser = [
            'id' => $user['id'],
            'username' => $user['username'],
            'created_at' => $user['created_at']
        ];
        
        $this->sendResponse(['user' => $publicUser]);
    }
    
    // Helper methods
    private function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    private function getRequestData() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback to form data
            return $_POST;
        }
        
        return $data ?? [];
    }
    
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    private function sendError($message, $statusCode = 400) {
        http_response_code($statusCode);
        echo json_encode([
            'error' => true,
            'message' => $message
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    private function getEndpoints() {
        return [
            'posts' => [
                'GET /api.php?resource=posts' => 'Get all posts (supports &page, &limit, &search)',
                'GET /api.php?resource=posts&id={id}' => 'Get single post',
                'POST /api.php?resource=posts' => 'Create new post (requires auth)',
                'PUT /api.php?resource=posts&id={id}' => 'Update post (requires auth, owner only)',
                'DELETE /api.php?resource=posts&id={id}' => 'Delete post (requires auth, owner only)'
            ],
            'auth' => [
                'POST /api.php?resource=auth&id=login' => 'Login user',
                'POST /api.php?resource=auth&id=register' => 'Register new user',
                'POST /api.php?resource=auth&id=logout' => 'Logout user',
                'GET /api.php?resource=auth&id=status' => 'Check authentication status'
            ],
            'users' => [
                'GET /api.php?resource=users' => 'Get all users (public info only)',
                'GET /api.php?resource=users&id={id}' => 'Get single user (public info only)'
            ],
            'info' => [
                'Note' => 'Session-based authentication. Use cookies for persistent login across requests.',
                'CSRF' => 'CSRF protection not enforced for API endpoints - use for same-origin requests only or implement token-based auth.'
            ]
        ];
    }
    
}

$api = new API();
$api->handleRequest();
