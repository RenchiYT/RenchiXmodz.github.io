<?php
require_once 'config.php';

if (isLoggedIn()) {
    logActivity($pdo, $_SESSION['user_id'], 'logout', 'User logged out');
}

session_destroy();
redirect('index.php');
?>
