<?php
session_start();

require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/BlogPost.php';
require_once 'classes/Authentication.php';

//verify if user is loggeed in
$db = new Database();
$user = new User($db);
if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $token = $_POST['csrf_token'] ?? '';

    if (!Authentication::verifyCSRFToken($token)) {
        $message = 'invalid security token. Plesae try again.';
        $messageType = 'error';
    } elseif (empty($title) || empty($body)) {
        $message = 'Please fill in the fields';
        $messageType = 'error';
    } else {
        $blogPost = new BlogPost($db);
        $currentUser = $user->getCurrentUser();
        $result = $blogPost->create($title, $body, $currentUser['id']);

        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';

        if ($result['success']) {
            header('Location: dashboard.php?message=Post created successfully');
            exit;
        }
    }
}

$pageTitle = "Create New Post";
include 'views/header.php';
?>

<div class="form-container">
    <link rel="stylesheet" href="assets/styles.css">
    <div class="post-form">
        <h2>Create New Post</h2>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= Authentication::generateCSRFToken() ?>">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="body">Content:</label>
                <textarea id="body" name="body" rows="10" required><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Post</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script src="/assets/script.js"></script>
<?php include 'views/footer.php'; ?>
