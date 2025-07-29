<?php
require_once '../config/database.php';

class Admin {
    private $conn;
    private $table_name = "admins";
    
    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $role;
    public $avatar;
    public $last_login;
    public $login_attempts;
    public $locked_until;
    public $two_factor_enabled;
    public $two_factor_secret;
    public $is_active;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Login admin
    public function login($username, $password) {
        try {
            // Check if account is locked
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE (username = :username OR email = :username) 
                     AND is_active = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if account is locked
            if ($admin['locked_until'] && strtotime($admin['locked_until']) > time()) {
                $lockTime = date('H:i:s', strtotime($admin['locked_until']));
                return ['success' => false, 'message' => "Account locked until $lockTime"];
            }
            
            // Verify password
            if (!password_verify($password, $admin['password'])) {
                $this->incrementLoginAttempts($admin['id']);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Reset login attempts on successful login
            $this->resetLoginAttempts($admin['id']);
            
            // Update last login
            $this->updateLastLogin($admin['id']);
            
            // Set session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_full_name'] = $admin['full_name'];
            $_SESSION['admin_login_time'] = time();
            
            // Log successful login
            $this->logActivity($admin['id'], 'login', 'admin', $admin['id']);
            
            return [
                'success' => true, 
                'message' => 'Login successful',
                'data' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'full_name' => $admin['full_name'],
                    'role' => $admin['role'],
                    'avatar' => $admin['avatar']
                ]
            ];
            
        } catch(Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed'];
        }
    }
    
    // Increment login attempts
    private function incrementLoginAttempts($admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET login_attempts = login_attempts + 1,
                     locked_until = CASE 
                         WHEN login_attempts >= :max_attempts - 1 
                         THEN DATE_ADD(NOW(), INTERVAL :lockout_time SECOND)
                         ELSE locked_until 
                     END
                 WHERE id = :admin_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->bindParam(':max_attempts', $max_attempts = MAX_LOGIN_ATTEMPTS);
        $stmt->bindParam(':lockout_time', $lockout_time = LOCKOUT_TIME);
        $stmt->execute();
    }
    
    // Reset login attempts
    private function resetLoginAttempts($admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET login_attempts = 0, locked_until = NULL 
                 WHERE id = :admin_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->execute();
    }
    
    // Update last login
    private function updateLastLogin($admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET last_login = NOW() 
                 WHERE id = :admin_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->execute();
    }
    
    // Get admin by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get all admins
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT id, username, email, full_name, role, avatar, last_login, is_active, created_at 
                 FROM " . $this->table_name . " 
                 ORDER BY created_at DESC 
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create new admin
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                     (username, email, password, full_name, role) 
                     VALUES (:username, :email, :password, :full_name, :role)";
            
            $stmt = $this->conn->prepare($query);
            
            // Hash password
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':role', $data['role']);
            
            if ($stmt->execute()) {
                $admin_id = $this->conn->lastInsertId();
                $this->logActivity($_SESSION['admin_id'], 'create_admin', 'admin', $admin_id);
                return ['success' => true, 'id' => $admin_id];
            }
            
            return ['success' => false, 'message' => 'Failed to create admin'];
            
        } catch(Exception $e) {
            error_log("Create admin error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create admin'];
        }
    }
    
    // Update admin
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];
            
            foreach ($data as $key => $value) {
                if ($key == 'password' && !empty($value)) {
                    $fields[] = "password = :password";
                    $params[':password'] = password_hash($value, PASSWORD_DEFAULT);
                } elseif (in_array($key, ['username', 'email', 'full_name', 'role', 'is_active'])) {
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }
            
            if (empty($fields)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }
            
            $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt->execute($params)) {
                $this->logActivity($_SESSION['admin_id'], 'update_admin', 'admin', $id);
                return ['success' => true];
            }
            
            return ['success' => false, 'message' => 'Failed to update admin'];
            
        } catch(Exception $e) {
            error_log("Update admin error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update admin'];
        }
    }
    
    // Delete admin (soft delete)
    public function delete($id) {
        try {
            $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $this->logActivity($_SESSION['admin_id'], 'delete_admin', 'admin', $id);
                return ['success' => true];
            }
            
            return ['success' => false, 'message' => 'Failed to delete admin'];
            
        } catch(Exception $e) {
            error_log("Delete admin error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete admin'];
        }
    }
    
    // Log admin activity
    public function logActivity($admin_id, $action, $target_type = null, $target_id = null, $old_values = null, $new_values = null) {
        try {
            $query = "INSERT INTO admin_logs 
                     (admin_id, action, target_type, target_id, old_values, new_values, ip_address, user_agent) 
                     VALUES (:admin_id, :action, :target_type, :target_id, :old_values, :new_values, :ip_address, :user_agent)";
            
            $stmt = $this->conn->prepare($query);
            
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $old_values_json = $old_values ? json_encode($old_values) : null;
            $new_values_json = $new_values ? json_encode($new_values) : null;
            
            $stmt->bindParam(':admin_id', $admin_id);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':target_type', $target_type);
            $stmt->bindParam(':target_id', $target_id);
            $stmt->bindParam(':old_values', $old_values_json);
            $stmt->bindParam(':new_values', $new_values_json);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':user_agent', $user_agent);
            
            $stmt->execute();
            
        } catch(Exception $e) {
            error_log("Log activity error: " . $e->getMessage());
        }
    }
    
    // Check if user is logged in
    public static function isLoggedIn() {
        return isset($_SESSION['admin_id']) && 
               isset($_SESSION['admin_login_time']) && 
               (time() - $_SESSION['admin_login_time']) < SESSION_LIFETIME;
    }
    
    // Logout
    public static function logout() {
        if (isset($_SESSION['admin_id'])) {
            // Log logout activity
            $admin = new self((new Database())->getConnection());
            $admin->logActivity($_SESSION['admin_id'], 'logout', 'admin', $_SESSION['admin_id']);
        }
        
        // Destroy session
        session_unset();
        session_destroy();
        
        // Start new session for flash messages
        session_start();
    }
    
    // Require login
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ' . ADMIN_URL . '/login');
            exit();
        }
    }
    
    // Check permission
    public static function hasPermission($required_role = 'admin') {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $role_hierarchy = ['moderator' => 1, 'admin' => 2, 'super_admin' => 3];
        $user_role_level = $role_hierarchy[$_SESSION['admin_role']] ?? 0;
        $required_role_level = $role_hierarchy[$required_role] ?? 0;
        
        return $user_role_level >= $required_role_level;
    }
}
?>