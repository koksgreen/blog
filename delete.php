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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?error=Invalid request method');
    exit;
}

$blogPost = new BlogPost($db);
$currentUser = $user->getCurrentUser();
$postId = (int)($_POST['id'] ?? 0);
$token = $_POST['csrf_token'] ?? '';

if (!Authentication::verifyCSRFToken($token)) {
    header('Location: dashboard.php?error=Invalid security token');
    exit;
}

if ($postId > 0) {
    $result = $blogPost->delete($postId, $currentUser['id']);

    if ($result['success']) {
        header('Location: dashboard.php?message=Post deleted successfully');
    } else {
        header('Location: dashboard.php?error=' . urlencode($result['message']));
    }
} else {
    header('Location: dashboard.php?error=Invalid post ID');
}

exit;
