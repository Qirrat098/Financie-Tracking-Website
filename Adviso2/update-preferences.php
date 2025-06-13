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
    $theme = isset($_POST['theme']) ? $_POST['theme'] : 'light';
    
    try {
        // First, check if user preferences exist
        $check_stmt = $conn->prepare("SELECT id FROM user_preferences WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing preferences
            $stmt = $conn->prepare("UPDATE user_preferences SET theme = ? WHERE user_id = ?");
            $stmt->bind_param("si", $theme, $user_id);
        } else {
            // Insert new preferences
            $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, theme) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $theme);
        }
        
        if ($stmt->execute()) {
            // Store theme preference in session
            $_SESSION['theme'] = $theme;
            
            // Set success message
            $_SESSION['success_message'] = "Preferences updated successfully!";
        } else {
            throw new Exception("Error updating preferences");
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error updating preferences: " . $e->getMessage();
    }
    
    // Redirect back to settings page
    header('Location: settings.php');
    exit();
} else {
    // If not POST request, redirect to settings page
    header('Location: settings.php');
    exit();
}
?> 