<?php
require_once '../config.php';
require_once 'config/database.php';
require_once 'models/Admin.php';

// Check if admin is logged in
if (Admin::isLoggedIn()) {
    // Redirect to dashboard
    header('Location: ' . ADMIN_URL . '/dashboard');
    exit();
} else {
    // Redirect to login
    header('Location: ' . ADMIN_URL . '/login');
    exit();
}
?>