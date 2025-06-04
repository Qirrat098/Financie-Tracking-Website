<?php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goal_id = intval($_POST['goal_id'] ?? 0);
    
    // Validate input
    if ($goal_id <= 0) {
        $_SESSION['error'] = "Invalid goal ID";
        header('Location: financial-goals.php');
        exit();
    }
    
    try {
        // First verify that the goal belongs to the user
        $stmt = $conn->prepare("SELECT id FROM financial_goals WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $goal_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Goal not found or you don't have permission to delete it");
        }
        
        // Delete the goal
        $stmt = $conn->prepare("DELETE FROM financial_goals WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $goal_id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Goal deleted successfully!";
        } else {
            throw new Exception("Failed to delete goal");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting goal: " . $e->getMessage();
    }
}

// Redirect back to goals page
header('Location: financial-goals.php');
exit(); 