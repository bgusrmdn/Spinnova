<?php
// Main Configuration File
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'slotmania_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'SlotMania');
define('SITE_VERSION', '1.0.0');
define('ADMIN_EMAIL', 'admin@slotmania.com');

// URL Configuration - Dynamic Base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptPath = rtrim($scriptPath, '/');

define('BASE_URL', $protocol . $domain . $scriptPath);
define('ADMIN_URL', BASE_URL . '/admin');
define('API_URL', BASE_URL . '/api');
define('ASSETS_URL', BASE_URL . '/public/assets');

// Path Configuration
define('ROOT_PATH', __DIR__);
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('API_PATH', ROOT_PATH . '/api');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Security Configuration
define('JWT_SECRET', 'your-super-secret-jwt-key-change-this-in-production');
define('HASH_ALGO', 'sha256');
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// Game Configuration
define('DEFAULT_CREDITS', 1000);
define('MIN_BET', 1);
define('MAX_BET', 1000);
define('HOUSE_EDGE', 0.05); // 5%

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session Configuration
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS Headers for API
if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Error Handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error = [
        'error' => true,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'time' => date('Y-m-d H:i:s')
    ];
    
    error_log(json_encode($error));
    
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
        exit();
    }
}

set_error_handler('customErrorHandler');
?>