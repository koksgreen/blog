<?php
session_start();

require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/BlogPost.php';
require_once 'classes/Authentication.php';

$db = new Database();
$user = new User($db);
if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$blogPost = new BlogPost($db);
$currentUser = $user->getCurrentUser();
$postId = (int)($_GET['id'] ?? 0);
$post = $blogPost->getById($postId);

// Check if post exists and user owns it
if (!$post || $post['author_id'] != $currentUser['id']) {
    header('Location: dashboard.php?error=Post not found or access denied');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $token = $_POST['csrf_token'] ?? '';
    
    if (!Authentication::verifyCSRFToken($token)) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } elseif (empty($title) || empty($body)) {
        $message = 'Please fill in all fields';
        $messageType = 'error';
    } else {
        $result = $blogPost->update($postId, $title, $body, $currentUser['id']);
        
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            header('Location: dashboard.php?message=Post updated successfully');
            exit;
        }
    }
}

$pageTitle = "Edit Post";
include 'views/header.php';
?>

<div class="form-container">
     <link rel="stylesheet" href="assets/styles.css">
    <div class="post-form">
        <h2>Edit Post</h2>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= Authentication::generateCSRFToken() ?>">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" 
                       value="<?= htmlspecialchars($_POST['title'] ?? $post['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="body">Content:</label>
                <textarea id="body" name="body" rows="10" required><?= htmlspecialchars($_POST['body'] ?? $post['body']) ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Post</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                <a href="post.php?id=<?= $post['id'] ?>" class="btn btn-outline">View Post</a>
            </div>
        </form>
    </div>
</div>

<?php include 'views/footer.php'; ?>