<?php
require_once 'config/config.php';

// Default categories for transactions
$default_categories = [
    'Salary',
    'Freelance',
    'Investments',
    'Housing',
    'Utilities',
    'Transportation',
    'Food',
    'Entertainment',
    'Shopping',
    'Healthcare',
    'Education',
    'Travel',
    'Gifts',
    'Savings',
    'Other'
];

// Insert default categories into transactions table
$stmt = $conn->prepare("
    INSERT IGNORE INTO transactions (user_id, type, amount, category, description, transaction_date)
    VALUES (?, 'income', 0, ?, 'Default Category', CURRENT_DATE())
");

foreach ($default_categories as $category) {
    $stmt->bind_param("is", $_SESSION['user_id'], $category);
    $stmt->execute();
}

echo "Default categories added successfully!";
?> 