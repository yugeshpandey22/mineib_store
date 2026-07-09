<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $base = defined('BASE_URL') ? BASE_URL : '/storemanagement';
        header("Location: " . $base . "/login.php");
        exit; 
    }
}

function loginUser($pdo, $username, $password) {
    $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logoutUser() {
    session_unset();
    session_destroy();
}
?>
