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
    $new_amount = floatval($_POST['new_amount'] ?? 0);
    
    // Validate input
    $errors = [];
    if ($goal_id <= 0) {
        $errors[] = "Invalid goal ID";
    }
    if ($new_amount < 0) {
        $errors[] = "Amount cannot be negative";
    }
    
    // If no errors, proceed with update
    if (empty($errors)) {
        try {
            // First verify that the goal belongs to the user
            $stmt = $conn->prepare("SELECT id FROM financial_goals WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $goal_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Goal not found or you don't have permission to update it");
            }
            
            // Update the goal's current amount
            $stmt = $conn->prepare("UPDATE financial_goals SET current_amount = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("dii", $new_amount, $goal_id, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Goal progress updated successfully!";
            } else {
                throw new Exception("Failed to update goal progress");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating goal progress: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
} else {
    $_SESSION['error'] = "Invalid request method";
}

// Redirect back to goals page
header('Location: financial-goals.php');
exit(); 