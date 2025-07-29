<?php
require_once 'config.php';

// Check if already installed
if (file_exists('.installed')) {
    die('System already installed. Delete .installed file to reinstall.');
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Handle installation steps
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['step']) {
        case '1':
            // Database connection test
            try {
                $database = new Database();
                $connection = $database->getConnection();
                if ($connection) {
                    $success = 'Database connection successful!';
                    $step = 2;
                }
            } catch (Exception $e) {
                $error = 'Database connection failed: ' . $e->getMessage();
            }
            break;
            
        case '2':
            // Create database tables
            try {
                $database = new Database();
                $database->initializeDatabase();
                
                // Read and execute SQL file
                $sql = file_get_contents('database/migrations/init.sql');
                $queries = explode(';', $sql);
                
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $database->execute($query);
                    }
                }
                
                $success = 'Database tables created successfully!';
                $step = 3;
            } catch (Exception $e) {
                $error = 'Failed to create database tables: ' . $e->getMessage();
            }
            break;
            
        case '3':
            // Create admin user
            try {
                $username = $_POST['admin_username'];
                $email = $_POST['admin_email'];
                $password = $_POST['admin_password'];
                $full_name = $_POST['admin_fullname'];
                
                if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
                    throw new Exception('All fields are required');
                }
                
                $database = new Database();
                $db = $database->getConnection();
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert admin user
                $query = "INSERT INTO admins (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'super_admin')";
                $stmt = $db->prepare($query);
                $stmt->execute([$username, $email, $hashed_password, $full_name]);
                
                // Create installation marker
                file_put_contents('.installed', date('Y-m-d H:i:s'));
                
                $success = 'Installation completed successfully!';
                $step = 4;
            } catch (Exception $e) {
                $error = 'Failed to create admin user: ' . $e->getMessage();
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
</head>
<body class="gradient-bg flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <i class="fas fa-dice text-6xl text-white mb-4"></i>
            <h1 class="text-4xl font-bold text-white mb-2"><?= SITE_NAME ?></h1>
            <p class="text-white opacity-80">Installation Wizard</p>
        </div>
        
        <!-- Installation Steps -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between text-white text-sm mb-2">
                    <span>Step <?= $step ?> of 4</span>
                    <span><?= round(($step / 4) * 100) ?>% Complete</span>
                </div>
                <div class="w-full bg-white bg-opacity-20 rounded-full h-2">
                    <div class="bg-white h-2 rounded-full transition-all duration-500" style="width: <?= ($step / 4) * 100 ?>%"></div>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-500 bg-opacity-20 border border-red-500 text-red-100 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-500 bg-opacity-20 border border-green-500 text-green-100 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <!-- Step 1: Database Connection -->
                <div class="text-center">
                    <i class="fas fa-database text-4xl text-white mb-4"></i>
                    <h2 class="text-2xl font-bold text-white mb-4">Database Connection</h2>
                    <p class="text-white opacity-80 mb-6">Let's test your database connection settings.</p>
                    
                    <div class="bg-white bg-opacity-10 rounded-lg p-4 mb-6 text-left">
                        <h3 class="text-white font-semibold mb-2">Current Settings:</h3>
                        <ul class="text-white opacity-80 space-y-1">
                            <li><strong>Host:</strong> <?= DB_HOST ?></li>
                            <li><strong>Database:</strong> <?= DB_NAME ?></li>
                            <li><strong>Username:</strong> <?= DB_USER ?></li>
                            <li><strong>Password:</strong> <?= DB_PASS ? str_repeat('*', strlen(DB_PASS)) : 'Not set' ?></li>
                        </ul>
                        <p class="text-yellow-200 text-sm mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            You can change these settings in config.php
                        </p>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="step" value="1">
                        <button type="submit" class="w-full bg-white text-purple-600 font-bold py-3 px-6 rounded-lg hover:bg-gray-100 transition-colors">
                            <i class="fas fa-plug mr-2"></i>Test Database Connection
                        </button>
                    </form>
                </div>
                
            <?php elseif ($step == 2): ?>
                <!-- Step 2: Create Tables -->
                <div class="text-center">
                    <i class="fas fa-table text-4xl text-white mb-4"></i>
                    <h2 class="text-2xl font-bold text-white mb-4">Create Database Tables</h2>
                    <p class="text-white opacity-80 mb-6">Now let's create the necessary database tables.</p>
                    
                    <form method="POST">
                        <input type="hidden" name="step" value="2">
                        <button type="submit" class="w-full bg-white text-purple-600 font-bold py-3 px-6 rounded-lg hover:bg-gray-100 transition-colors">
                            <i class="fas fa-hammer mr-2"></i>Create Database Tables
                        </button>
                    </form>
                </div>
                
            <?php elseif ($step == 3): ?>
                <!-- Step 3: Create Admin User -->
                <div>
                    <div class="text-center mb-6">
                        <i class="fas fa-user-shield text-4xl text-white mb-4"></i>
                        <h2 class="text-2xl font-bold text-white mb-4">Create Admin Account</h2>
                        <p class="text-white opacity-80">Create your administrator account to manage the system.</p>
                    </div>
                    
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="step" value="3">
                        
                        <div>
                            <label class="block text-white text-sm font-medium mb-2">Full Name</label>
                            <input type="text" name="admin_fullname" required 
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                placeholder="Your full name">
                        </div>
                        
                        <div>
                            <label class="block text-white text-sm font-medium mb-2">Username</label>
                            <input type="text" name="admin_username" required 
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                placeholder="admin">
                        </div>
                        
                        <div>
                            <label class="block text-white text-sm font-medium mb-2">Email</label>
                            <input type="email" name="admin_email" required 
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                placeholder="admin@example.com">
                        </div>
                        
                        <div>
                            <label class="block text-white text-sm font-medium mb-2">Password</label>
                            <input type="password" name="admin_password" required 
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                placeholder="Strong password" minlength="6">
                        </div>
                        
                        <button type="submit" class="w-full bg-white text-purple-600 font-bold py-3 px-6 rounded-lg hover:bg-gray-100 transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>Create Admin Account
                        </button>
                    </form>
                </div>
                
            <?php elseif ($step == 4): ?>
                <!-- Step 4: Complete -->
                <div class="text-center">
                    <i class="fas fa-check-circle text-6xl text-green-400 mb-4"></i>
                    <h2 class="text-3xl font-bold text-white mb-4">Installation Complete!</h2>
                    <p class="text-white opacity-80 mb-8">Your <?= SITE_NAME ?> admin panel is now ready to use.</p>
                    
                    <div class="bg-white bg-opacity-10 rounded-lg p-6 mb-6 text-left">
                        <h3 class="text-white font-semibold mb-3">Next Steps:</h3>
                        <ul class="text-white opacity-80 space-y-2">
                            <li class="flex items-start">
                                <i class="fas fa-arrow-right text-green-400 mr-2 mt-1"></i>
                                <span>Access your admin panel</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-arrow-right text-green-400 mr-2 mt-1"></i>
                                <span>Configure your site settings</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-arrow-right text-green-400 mr-2 mt-1"></i>
                                <span>Add your slot games</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-arrow-right text-green-400 mr-2 mt-1"></i>
                                <span>Delete this install.php file for security</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="<?= ADMIN_URL ?>/login" class="block w-full bg-white text-purple-600 font-bold py-3 px-6 rounded-lg hover:bg-gray-100 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Go to Admin Panel
                        </a>
                        <a href="<?= BASE_URL ?>" class="block w-full bg-transparent border-2 border-white text-white font-bold py-3 px-6 rounded-lg hover:bg-white hover:text-purple-600 transition-colors">
                            <i class="fas fa-home mr-2"></i>View Website
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6 text-white opacity-60">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>