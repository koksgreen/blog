<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Authentication.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';
    
    if (!Authentication::verifyCSRFToken($token)) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } elseif (empty($username) || empty($password)) {
        $message = 'Please fill in all fields';
        $messageType = 'error';
    } else {
        $db = new Database();
        $user = new User($db);
        $result = $user->login($username, $password);
        
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
        }
    }
}

$pageTitle = "Login";
include 'views/header.php';
?>

<div class="auth-container">
    <div class="auth-form">
        <link rel="stylesheet" href="assets/styles.css">
        <h2>Login</h2>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= Authentication::generateCSRFToken() ?>">
            <div class="form-group">
                <label for="username">Username or Email:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        
        <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>
<script src="assets/script.js"></script>
<?php include 'views/footer.php'; ?>