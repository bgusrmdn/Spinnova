<?php
require_once '../../config.php';
require_once '../config/database.php';
require_once '../models/Admin.php';

// Handle different authentication actions
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'logout':
        handleLogout();
        break;
    
    case 'verify':
        handleVerifySession();
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleLogout() {
    Admin::logout();
    
    // Set success message
    $_SESSION['success_message'] = 'You have been successfully logged out.';
    
    // Redirect to login page
    header('Location: ' . ADMIN_URL . '/login');
    exit();
}

function handleVerifySession() {
    header('Content-Type: application/json');
    
    if (Admin::isLoggedIn()) {
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'role' => $_SESSION['admin_role'],
                'full_name' => $_SESSION['admin_full_name']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'message' => 'Session expired'
        ]);
    }
    exit();
}
?>