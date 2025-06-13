<?php
require_once 'config/config.php';

// Drop the table if it exists
$conn->query("DROP TABLE IF EXISTS transactions");

// Create the transactions table
$sql = "CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql)) {
    echo "Transactions table created successfully<br>";
    
    // Insert sample transactions for user_id 1
    $sample_transactions = [
        [
            'user_id' => 1,
            'type' => 'expense',
            'amount' => 50.00,
            'category' => 'groceries',
            'description' => 'Weekly groceries',
            'date' => date('Y-m-d')
        ],
        [
            'user_id' => 1,
            'type' => 'expense',
            'amount' => 100.00,
            'category' => 'utilities',
            'description' => 'Electricity bill',
            'date' => date('Y-m-d', strtotime('-1 day'))
        ],
        [
            'user_id' => 1,
            'type' => 'income',
            'amount' => 2000.00,
            'category' => 'salary',
            'description' => 'Monthly salary',
            'date' => date('Y-m-d', strtotime('-5 days'))
        ],
        [
            'user_id' => 1,
            'type' => 'expense',
            'amount' => 75.00,
            'category' => 'entertainment',
            'description' => 'Movie night',
            'date' => date('Y-m-d', strtotime('-2 days'))
        ],
        [
            'user_id' => 1,
            'type' => 'expense',
            'amount' => 30.00,
            'category' => 'transportation',
            'description' => 'Gas for car',
            'date' => date('Y-m-d', strtotime('-3 days'))
        ]
    ];

    $stmt = $conn->prepare("
        INSERT INTO transactions (user_id, type, amount, category, description, date)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($sample_transactions as $transaction) {
        $stmt->bind_param(
            "isdsss",
            $transaction['user_id'],
            $transaction['type'],
            $transaction['amount'],
            $transaction['category'],
            $transaction['description'],
            $transaction['date']
        );
        $stmt->execute();
    }

    echo "Sample transactions inserted successfully<br>";
} else {
    echo "Error creating transactions table: " . $conn->error . "<br>";
}

$conn->close();
?> 