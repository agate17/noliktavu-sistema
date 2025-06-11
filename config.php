<?php

$host = 'localhost';
$dbname = 'stash_warehouse';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function hasPermission($required_permission) {
    if (!isLoggedIn()) return false;

    $role = getUserRole();
    if (strtolower($role) === 'administrator') return true;

    $permissions = $_SESSION['permissions'] ?? '';
    return strpos($permissions, $required_permission) !== false;
}
?>
