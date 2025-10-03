<?php
session_start();

require_once 'classes/Database.php';
require_once 'classes/User.php';

$db = new Database();
$user = new User($db);
$user->logout();

header('Location: index.php?message=Logged out successfully');
exit;
?>