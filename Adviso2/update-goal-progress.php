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
    $goal_id = $_POST['goal_id'] ?? '';
    $saved_amount = $_POST['saved_amount'] ?? '';

    // Validate input
    if (empty($goal_id) || !is_numeric($saved_amount)) {
        $_SESSION['error'] = "Invalid input. Please try again.";
        header('Location: financial-goals.php');
        exit();
    }

    // Get the goal's target amount
    $stmt = $conn->prepare("SELECT target_amount FROM financial_goals WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $goal_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $goal = $result->fetch_assoc();

    if (!$goal) {
        $_SESSION['error'] = "Goal not found.";
        header('Location: financial-goals.php');
        exit();
    }

    // Check if saved amount exceeds target amount
    if ($saved_amount > $goal['target_amount']) {
        $_SESSION['error'] = "Saved amount cannot exceed the target amount of $" . number_format($goal['target_amount'], 2);
        header('Location: financial-goals.php');
        exit();
    }

    // Update the goal's progress
    $stmt = $conn->prepare("
        UPDATE financial_goals 
        SET saved_amount = ?,
            status = CASE WHEN ? >= target_amount THEN 'completed' ELSE status END,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ddii", $saved_amount, $saved_amount, $goal_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Goal progress updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating goal progress. Please try again.";
    }
}

header('Location: financial-goals.php');
exit(); 