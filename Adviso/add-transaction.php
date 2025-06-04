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
    $type = $_POST['type'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    
    // Validate input
    $errors = [];
    if (!in_array($type, ['income', 'expense'])) {
        $errors[] = "Invalid transaction type";
    }
    if ($amount <= 0) {
        $errors[] = "Amount must be greater than 0";
    }
    if (empty($category)) {
        $errors[] = "Category is required";
    }
    if (empty($date)) {
        $errors[] = "Date is required";
    }
    
    // If no errors, proceed with insertion
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO transactions (user_id, type, amount, category, description, date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param("isdsss", 
                $_SESSION['user_id'],
                $type,
                $amount,
                $category,
                $description,
                $date
            );
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Transaction added successfully!";
            } else {
                throw new Exception("Failed to add transaction");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error adding transaction: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
} else {
    $_SESSION['error'] = "Invalid request method";
}

// Redirect back to budget page
header('Location: budget.php');
exit(); 