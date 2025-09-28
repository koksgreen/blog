<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Blog' ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/script.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">Blog</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">My Posts</a>
                    <a href="create_post.php">Create Post</a>
                    <span class="user-info">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="main-content">