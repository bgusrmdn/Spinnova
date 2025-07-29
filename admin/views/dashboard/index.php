<?php
require_once '../../config.php';
require_once '../config/database.php';
require_once '../models/Admin.php';

// Require login
Admin::requireLogin();

// Get current admin info
$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db);
$current_admin = $admin->getById($_SESSION['admin_id']);

// Get dashboard statistics
$stats = [
    'total_users' => $database->fetchOne("SELECT COUNT(*) as count FROM users WHERE is_active = 1")['count'] ?? 0,
    'total_games' => $database->fetchOne("SELECT COUNT(*) as count FROM games WHERE is_active = 1")['count'] ?? 0,
    'total_sessions' => $database->fetchOne("SELECT COUNT(*) as count FROM game_sessions WHERE status = 'active'")['count'] ?? 0,
    'total_revenue' => $database->fetchOne("SELECT COALESCE(SUM(total_bet), 0) as total FROM game_sessions WHERE DATE(started_at) = CURDATE()")['total'] ?? 0,
];

// Get recent activities
$recent_activities = $database->fetchAll("
    SELECT al.*, a.username, a.full_name 
    FROM admin_logs al 
    JOIN admins a ON al.admin_id = a.id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");

// Get active game sessions
$active_sessions = $database->fetchAll("
    SELECT gs.*, u.username, g.name as game_name 
    FROM game_sessions gs 
    JOIN users u ON gs.user_id = u.id 
    JOIN games g ON gs.game_id = g.id 
    WHERE gs.status = 'active' 
    ORDER BY gs.started_at DESC 
    LIMIT 10
");

// Get recent users
$recent_users = $database->fetchAll("
    SELECT id, username, email, full_name, balance, created_at 
    FROM users 
    WHERE is_active = 1 
    ORDER BY created_at DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Admin Dashboard</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .sidebar {
            transition: all 0.3s ease;
        }
        
        .sidebar-collapsed {
            width: 80px;
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .pulse-dot {
            animation: pulse-dot 2s infinite;
        }
        
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.1); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-64 bg-white shadow-lg z-40">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dice text-white text-lg"></i>
                </div>
                <div id="sidebar-text">
                    <h2 class="text-xl font-bold text-gray-800"><?= SITE_NAME ?></h2>
                    <p class="text-sm text-gray-500">Admin Panel</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="<?= ADMIN_URL ?>/dashboard" class="flex items-center space-x-3 p-3 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="<?= ADMIN_URL ?>/users" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-users w-5"></i>
                        <span class="sidebar-text">Users</span>
                    </a>
                </li>
                <li>
                    <a href="<?= ADMIN_URL ?>/games" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-gamepad w-5"></i>
                        <span class="sidebar-text">Games</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span class="sidebar-text">Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-credit-card w-5"></i>
                        <span class="sidebar-text">Transactions</span>
                    </a>
                </li>
                <li>
                    <a href="<?= ADMIN_URL ?>/settings" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-cog w-5"></i>
                        <span class="sidebar-text">Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- User Info -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-gray-600"></i>
                </div>
                <div class="sidebar-text">
                    <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($current_admin['full_name']) ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($current_admin['role']) ?></p>
                </div>
            </div>
            <a href="<?= ADMIN_URL ?>/logout" class="block mt-3 text-center py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors text-sm">
                <i class="fas fa-sign-out-alt mr-1"></i>
                <span class="sidebar-text">Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div id="main-content" class="ml-64 transition-all duration-300">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button id="sidebar-toggle" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button class="text-gray-500 hover:text-gray-700 relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                        </button>
                    </div>
                    
                    <!-- Search -->
                    <div class="relative">
                        <input type="search" placeholder="Search..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <!-- Real-time status -->
                    <div class="flex items-center space-x-2 px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                        <div class="w-2 h-2 bg-green-500 rounded-full pulse-dot"></div>
                        <span>Live</span>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Dashboard Content -->
        <main class="p-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Users -->
                <div class="stat-card bg-white rounded-xl p-6 card-shadow fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Users</p>
                            <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_users']) ?></p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-arrow-up"></i> +12% this month
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Active Games -->
                <div class="stat-card bg-white rounded-xl p-6 card-shadow fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Active Games</p>
                            <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_games']) ?></p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-arrow-up"></i> +3 new games
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-gamepad text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Live Sessions -->
                <div class="stat-card bg-white rounded-xl p-6 card-shadow fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Live Sessions</p>
                            <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_sessions']) ?></p>
                            <p class="text-sm text-blue-600 mt-1">
                                <i class="fas fa-eye"></i> Currently playing
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-play text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Today's Revenue -->
                <div class="stat-card bg-white rounded-xl p-6 card-shadow fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Today's Revenue</p>
                            <p class="text-3xl font-bold text-gray-900">Rp <?= number_format($stats['total_revenue']) ?></p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-arrow-up"></i> +8% vs yesterday
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts and Tables Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-xl p-6 card-shadow">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue Trend (Last 7 Days)</h3>
                    <canvas id="revenueChart" height="200"></canvas>
                </div>
                
                <!-- Popular Games -->
                <div class="bg-white rounded-xl p-6 card-shadow">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Popular Games</h3>
                    <canvas id="gamesChart" height="200"></canvas>
                </div>
            </div>
            
            <!-- Tables Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activities -->
                <div class="bg-white rounded-xl p-6 card-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Activities</h3>
                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">View all</a>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-800">
                                        <span class="font-medium"><?= htmlspecialchars($activity['full_name']) ?></span>
                                        <?= htmlspecialchars($activity['action']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500"><?= date('M j, Y H:i', strtotime($activity['created_at'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Active Sessions -->
                <div class="bg-white rounded-xl p-6 card-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Active Sessions</h3>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                            <?= count($active_sessions) ?> active
                        </span>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($active_sessions as $session): ?>
                            <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-gamepad text-green-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($session['username']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($session['game_name']) ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-800">Rp <?= number_format($session['current_balance']) ?></p>
                                    <p class="text-xs text-gray-500"><?= date('H:i', strtotime($session['started_at'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Sidebar toggle
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            
            sidebar.classList.toggle('sidebar-collapsed');
            
            if (sidebar.classList.contains('sidebar-collapsed')) {
                mainContent.style.marginLeft = '80px';
                sidebarTexts.forEach(text => text.style.display = 'none');
            } else {
                mainContent.style.marginLeft = '256px';
                setTimeout(() => {
                    sidebarTexts.forEach(text => text.style.display = 'block');
                }, 150);
            }
        });
        
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Revenue',
                    data: [1200, 1900, 3000, 5000, 2000, 3000, 4500],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Games Chart
        const gamesCtx = document.getElementById('gamesChart').getContext('2d');
        new Chart(gamesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cherry Blast', 'Diamond Strike', 'Royal Fortune', 'Lucky Clover'],
                datasets: [{
                    data: [30, 25, 25, 20],
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#f5576c'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
        
        // Real-time updates (simulated)
        setInterval(() => {
            // Simulate real-time updates
            const liveElements = document.querySelectorAll('.pulse-dot');
            liveElements.forEach(el => {
                el.style.animation = 'none';
                setTimeout(() => {
                    el.style.animation = 'pulse-dot 2s infinite';
                }, 10);
            });
        }, 5000);
        
        // Auto-refresh data every 30 seconds
        setInterval(() => {
            // In a real application, you would fetch new data here
            console.log('Refreshing dashboard data...');
        }, 30000);
    </script>
</body>
</html>