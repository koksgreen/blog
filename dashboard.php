<?php
session_start();

require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/BlogPost.php';
require_once 'classes/Authentication.php';

// Check if user is logged in
$db = new Database();
$user = new User($db);
if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$blogPost = new BlogPost($db);
$currentUser = $user->getCurrentUser();

// Handle pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

// Get user's posts
$userPosts = $blogPost->getUserPosts($currentUser['id'], $limit, $offset);

$pageTitle = "My Dashboard";
include 'views/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <link rel="stylesheet" href="assets/styles.css">
        <h1>My Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($currentUser['username']) ?>!</p>
        <a href="create_post.php" class="btn btn-primary">Create New Post</a>
    </div>
    
    <div class="dashboard-content">
        <h2>My Posts</h2>
        
        <?php if (empty($userPosts)): ?>
            <div class="no-posts">
                <p>You haven't created any posts yet.</p>
                <a href="create_Post.php" class="btn btn-primary">Create Your First Post</a>
            </div>
        <?php else: ?>
            <div class="posts-list">
                <?php foreach ($userPosts as $post): ?>
                    <div class="post-item">
                        <div class="post-info">
                            <h3><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                            <div class="post-meta">
                                <span>Created: <?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                                <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                    <span>Updated: <?= date('M j, Y', strtotime($post['updated_at'])) ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="post-excerpt"><?= htmlspecialchars(substr($post['body'], 0, 100)) ?>...</p>
                        </div>
                        <div class="post-actions">
                            <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-secondary">Edit</a>
                            <form method="POST" action="delete_post.php" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= Authentication::generateCSRFToken() ?>">
                                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="assets/script.js"></script>
<?php include 'views/footer.php'; ?>