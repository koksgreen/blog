<?php
    session_start();

    require_once 'classes/Database.php';
    require_once 'classes/User.php';
    require_once 'classes/BlogPost.php';
    require_once 'classes/Authentication.php';

    $db = new Database();
    $blogPost = new BlogPost($db);
    $user = new User($db);

    $psotId = (int)($_GET['id'] ?? 0);
    $post = $blogPost->getById($psotId);

    if (!$post){
        header('Location: index.php?error=Post not found');
        exit;
    }

    $pageTitle = htmlspecialchars($post['title']);
    include 'views/header.php';
?>

    <div class="post-container">
        <link rel="stylesheet" href="assets/styles.css">
    <article class="post-detail">
        <header class="post-header">
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta">
                <span class="author">By <?= htmlspecialchars($post['username']) ?></span>
                <span class="date">Published on <?= date('F j, Y', strtotime($post['created_at'])) ?></span>
                <?php if ($post['updated_at'] !== $post['created_at']): ?>
                    <span class="updated">Updated on <?= date('F j, Y', strtotime($post['updated_at'])) ?></span>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="post-content">
            <?= nl2br(htmlspecialchars($post['body'])) ?>
        </div>
        
        <?php if ($user->isLoggedIn() && $user->getCurrentUser()['id'] == $post['author_id']): ?>
            <div class="post-actions">
                <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-secondary">Edit Post</a>
                <form method="POST" action="delete_post.php" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Authentication::generateCSRFToken() ?>">
                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</button>
                </form>
            </div>
        <?php endif; ?>
    </article>
    
    <div class="post-navigation">
        <a href="index.php" class="btn btn-primary">← Back to All Posts</a>
    </div>
</div>
<script src="assets/script.js"></script>
<?php include 'views/footer.php'; ?>
