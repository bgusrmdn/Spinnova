<?php
require_once '../../config.php';
require_once '../config/database.php';
require_once '../models/Admin.php';

// Check if already logged in
if (Admin::isLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/dashboard');
    exit();
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Username and password are required';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $admin = new Admin($db);
        
        $result = $admin->login($username, $password);
        
        if ($result['success']) {
            $success_message = $result['message'];
            header('Location: ' . ADMIN_URL . '/dashboard');
            exit();
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Admin Login</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        .login-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            transform: translateY(-1.5rem) scale(0.8);
            color: #667eea;
        }
        
        .floating-label {
            position: absolute;
            left: 1rem;
            top: 1rem;
            transition: all 0.3s ease;
            pointer-events: none;
            color: #6b7280;
        }
        
        .btn-gradient {
            background: linear-gradient(45deg, #667eea, #764ba2);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(45deg, #5a67d8, #6b46c1);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .slide-in {
            animation: slideIn 0.8s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="login-bg flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-8 slide-in">
            <div class="inline-block">
                <i class="fas fa-dice text-6xl text-white mb-4 pulse-animation"></i>
                <h1 class="text-4xl font-bold text-white mb-2"><?= SITE_NAME ?></h1>
                <p class="text-white opacity-80">Admin Control Panel</p>
            </div>
        </div>
        
        <!-- Login Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl slide-in">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-white mb-2">Welcome Back</h2>
                <p class="text-white opacity-70">Sign in to your admin account</p>
            </div>
            
            <!-- Error/Success Messages -->
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-500 bg-opacity-20 border border-red-500 text-red-100 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="bg-green-500 bg-opacity-20 border border-green-500 text-green-100 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <!-- Username Field -->
                <div class="input-group">
                    <input 
                        type="text" 
                        name="username" 
                        id="username"
                        class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all"
                        placeholder="Username or Email"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                        autocomplete="username"
                    >
                    <label for="username" class="floating-label">
                        <i class="fas fa-user mr-2"></i>Username or Email
                    </label>
                </div>
                
                <!-- Password Field -->
                <div class="input-group">
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all"
                        placeholder="Password"
                        required
                        autocomplete="current-password"
                    >
                    <label for="password" class="floating-label">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <button 
                        type="button" 
                        class="absolute right-3 top-3 text-white opacity-70 hover:opacity-100 transition-opacity"
                        onclick="togglePassword()"
                    >
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                
                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center text-white opacity-80">
                        <input type="checkbox" name="remember" class="mr-2 rounded">
                        <span class="text-sm">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-white opacity-80 hover:opacity-100 transition-opacity">
                        Forgot password?
                    </a>
                </div>
                
                <!-- Login Button -->
                <button 
                    type="submit" 
                    class="w-full btn-gradient text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>
            
            <!-- Additional Info -->
            <div class="mt-8 text-center">
                <p class="text-white opacity-60 text-sm">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Secure admin access
                </p>
                <div class="mt-4 flex justify-center space-x-4 text-white opacity-40">
                    <span class="text-xs">v<?= SITE_VERSION ?></span>
                    <span class="text-xs">|</span>
                    <span class="text-xs"><?= date('Y') ?> <?= SITE_NAME ?></span>
                </div>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="mt-6 text-center">
            <div class="inline-flex items-center px-4 py-2 bg-black bg-opacity-30 rounded-full text-white text-sm">
                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                All systems operational
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.bg-red-500, .bg-green-500');
            messages.forEach(message => {
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';
                setTimeout(() => message.remove(), 300);
            });
        }, 5000);
        
        // Add loading state to form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...';
            
            // Re-enable button after 5 seconds (in case of errors)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 5000);
        });
        
        // Focus first input on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + L to focus username
            if (e.altKey && e.key === 'l') {
                e.preventDefault();
                document.getElementById('username').focus();
            }
        });
    </script>
</body>
</html>