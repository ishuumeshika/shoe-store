<?php
// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if(!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: ../pages/auth/login.php");
        exit;
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if(!isAdmin()) {
        header("Location: ../index.php");
        exit;
    }
}


// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}
?>