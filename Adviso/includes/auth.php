<?php
/**
 * Authentication helper functions
 */

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is an admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is an author
 * @return bool
 */
function isAuthor() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'author' || $_SESSION['role'] === 'admin');
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * @return string|null
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
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
 * Require user to be admin
 * Redirects to home page if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Require user to be author
 * Redirects to home page if not author
 */
function requireAuthor() {
    if (!isAuthor()) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Get current user data
 * @param mysqli $conn Database connection
 * @return array|null
 */
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Set user session data
 * @param array $user User data
 */
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
}

/**
 * Clear user session data
 */
function clearUserSession() {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    unset($_SESSION['role']);
    session_destroy();
}

/**
 * Check if user has specific role
 * @param mysqli $conn Database connection
 * @param string $role Role to check
 * @return bool
 */
function hasRole($conn, $role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    return $user && $user['role'] === $role;
}

/**
 * Require user to have specific role
 * Redirects to home page if role check fails
 * @param mysqli $conn Database connection
 * @param string $role Role to check
 */
function requireRole($conn, $role) {
    if (!hasRole($conn, $role)) {
        header('Location: index.php');
        exit();
    }
} 