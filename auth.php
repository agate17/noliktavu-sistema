<?php
/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has any of the specified roles
 * @param array $roles Array of role names to check
 * @return bool
 */
function hasRole($roles) {
    if (!isLoggedIn() || !isset($_SESSION['role'])) {
        return false;
    }
    return in_array($_SESSION['role'], $roles);
}

/**
 * Get current user's ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user's role
 * @return string|null
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get current user's username
 * @return string|null
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Require user to be logged in
 * Redirects to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Require user to have specific role
 * Redirects to dashboard if role requirement not met
 * @param array $roles Array of allowed role names
 */
function requireRole($roles) {
    requireLogin();
    if (!hasRole($roles)) {
        header('Location: dashboard.php');
        exit();
    }
} 