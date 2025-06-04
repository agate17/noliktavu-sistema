<?php 
<?php
// Database configuration

$host = 'localhost';
$dbname = 'warehouse_system';
$username = 'root';  // Change if needed
$password = '';      // Change if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session for user management
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get current user role
function getUserRole() {
    return $_SESSION['role_name'] ?? null;
}

// Function to check user permissions
function hasPermission($required_permission) {
    if (!isLoggedIn()) return false;
    
    $role = getUserRole();
    
    // Admin has all permissions
    if ($role === 'Administrator') return true;
    
    // Check specific permissions
    $permissions = $_SESSION['permissions'] ?? '';
    return strpos($permissions, $required_permission) !== false;
}
?>
?>
