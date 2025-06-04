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
    // Validate and sanitize input
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $target_amount = floatval($_POST['target_amount'] ?? 0);
    $current_amount = floatval($_POST['current_amount'] ?? 0);
    $target_date = trim($_POST['target_date'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validate required fields
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($category)) {
        $errors[] = "Category is required";
    }
    if ($target_amount <= 0) {
        $errors[] = "Target amount must be greater than 0";
    }
    if ($current_amount < 0) {
        $errors[] = "Current amount cannot be negative";
    }
    if (empty($target_date)) {
        $errors[] = "Target date is required";
    }
    
    // If no errors, proceed with goal creation
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO financial_goals (
                    user_id, title, category, target_amount, 
                    current_amount, target_date, description, 
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->bind_param(
                "issddss",
                $_SESSION['user_id'],
                $title,
                $category,
                $target_amount,
                $current_amount,
                $target_date,
                $description
            );
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Financial goal created successfully!";
                header('Location: financial-goals.php');
                exit();
            } else {
                throw new Exception("Failed to create goal");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error creating goal: " . $e->getMessage();
            header('Location: financial-goals.php');
            exit();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        header('Location: financial-goals.php');
        exit();
    }
} else {
    // If not POST request, redirect to goals page
    header('Location: financial-goals.php');
    exit();
} 