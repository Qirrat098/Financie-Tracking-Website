<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    try {
        // Validate input
        if (empty($name) || empty($email)) {
            throw new Exception("Name and email are required");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Check if email is already taken by another user
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $user_id);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            throw new Exception("Email is already taken");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Update basic info
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating profile information");
        }
        
        // Handle password change if requested
        if (!empty($current_password)) {
            // Verify current password
            $check_password = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $check_password->bind_param("i", $user_id);
            $check_password->execute();
            $result = $check_password->get_result();
            $user = $result->fetch_assoc();
            
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
            
            // Validate new password
            if (empty($new_password)) {
                throw new Exception("New password is required");
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match");
            }
            
            if (strlen($new_password) < 8) {
                throw new Exception("Password must be at least 8 characters long");
            }
            
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_password->bind_param("si", $hashed_password, $user_id);
            
            if (!$update_password->execute()) {
                throw new Exception("Error updating password");
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Update session
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        
        // Set success message
        $_SESSION['success_message'] = "Profile updated successfully!";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    // Redirect back to profile page
    header('Location: profile.php');
    exit();
} else {
    // If not POST request, redirect to profile page
    header('Location: profile.php');
    exit();
}
?> 