<?php
require_once 'includes/config.php';

// Drop existing financial_goals table if it exists
$sql = "DROP TABLE IF EXISTS financial_goals";
if ($conn->query($sql)) {
    echo "Existing financial_goals table dropped successfully<br>";
} else {
    echo "Error dropping financial_goals table: " . $conn->error . "<br>";
}

// Create financial_goals table with correct structure
$sql = "CREATE TABLE financial_goals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    target_amount DECIMAL(15,2) NOT NULL,
    saved_amount DECIMAL(15,2) DEFAULT 0.00,
    target_date DATE,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    category VARCHAR(50),
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql)) {
    echo "Financial goals table created successfully<br>";
} else {
    echo "Error creating financial goals table: " . $conn->error . "<br>";
}

// Drop existing transactions table if it exists
$sql = "DROP TABLE IF EXISTS transactions";
if ($conn->query($sql)) {
    echo "Existing transactions table dropped successfully<br>";
} else {
    echo "Error dropping transactions table: " . $conn->error . "<br>";
}

// Create transactions table
$sql = "CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql)) {
    echo "Transactions table created successfully<br>";
} else {
    echo "Error creating transactions table: " . $conn->error . "<br>";
}

// Drop existing achievements tables if they exist
$sql = "DROP TABLE IF EXISTS user_achievements";
if ($conn->query($sql)) {
    echo "Existing user_achievements table dropped successfully<br>";
} else {
    echo "Error dropping user_achievements table: " . $conn->error . "<br>";
}

$sql = "DROP TABLE IF EXISTS achievements";
if ($conn->query($sql)) {
    echo "Existing achievements table dropped successfully<br>";
} else {
    echo "Error dropping achievements table: " . $conn->error . "<br>";
}

// Create achievements table
$sql = "CREATE TABLE achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    criteria TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Achievements table created successfully<br>";
} else {
    echo "Error creating achievements table: " . $conn->error . "<br>";
}

// Create user_achievements table
$sql = "CREATE TABLE user_achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_achievement (user_id, achievement_id)
)";

if ($conn->query($sql)) {
    echo "User achievements table created successfully<br>";
} else {
    echo "Error creating user achievements table: " . $conn->error . "<br>";
}

// Insert default achievements
$achievements = [
    [
        'title' => 'First Goal',
        'description' => 'Created your first financial goal',
        'icon' => 'fa-bullseye',
        'criteria' => 'Create first financial goal'
    ],
    [
        'title' => 'Goal Master',
        'description' => 'Completed 5 financial goals',
        'icon' => 'fa-trophy',
        'criteria' => 'Complete 5 goals'
    ],
    [
        'title' => 'Savings Champion',
        'description' => 'Saved over ₹50,000',
        'icon' => 'fa-medal',
        'criteria' => 'Total savings exceed ₹50,000'
    ]
];

$stmt = $conn->prepare("INSERT INTO achievements (title, description, icon, criteria) VALUES (?, ?, ?, ?)");

foreach ($achievements as $achievement) {
    $stmt->bind_param("ssss", 
        $achievement['title'],
        $achievement['description'],
        $achievement['icon'],
        $achievement['criteria']
    );
    $stmt->execute();
}

echo "Default achievements added successfully<br>";

// Add indexes for better performance
$indexes = [
    "CREATE INDEX idx_goals_user ON financial_goals(user_id)",
    "CREATE INDEX idx_goals_status ON financial_goals(status)",
    "CREATE INDEX idx_transactions_user ON transactions(user_id)",
    "CREATE INDEX idx_transactions_date ON transactions(transaction_date)",
    "CREATE INDEX idx_user_achievements_user ON user_achievements(user_id)"
];

foreach ($indexes as $index) {
    if ($conn->query($index)) {
        echo "Index created successfully<br>";
    } else {
        echo "Error creating index: " . $conn->error . "<br>";
    }
}

// Insert sample data for testing
$sample_goals = [
    [
        'user_id' => 1,
        'title' => 'Emergency Fund',
        'description' => 'Building emergency fund for unexpected expenses',
        'target_amount' => 100000.00,
        'saved_amount' => 50000.00,
        'target_date' => '2024-12-31',
        'category' => 'Savings',
        'priority' => 'high'
    ],
    [
        'user_id' => 1,
        'title' => 'New Car',
        'description' => 'Saving for a new car purchase',
        'target_amount' => 250000.00,
        'saved_amount' => 150000.00,
        'target_date' => '2024-06-30',
        'category' => 'Vehicle',
        'priority' => 'medium'
    ]
];

$stmt = $conn->prepare("INSERT INTO financial_goals (user_id, title, description, target_amount, saved_amount, target_date, category, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($sample_goals as $goal) {
    $stmt->bind_param("issddsss", 
        $goal['user_id'],
        $goal['title'],
        $goal['description'],
        $goal['target_amount'],
        $goal['saved_amount'],
        $goal['target_date'],
        $goal['category'],
        $goal['priority']
    );
    $stmt->execute();
}

echo "Sample goals added successfully<br>";

// Insert sample transactions
$sample_transactions = [
    [
        'user_id' => 1,
        'type' => 'income',
        'amount' => 50000.00,
        'description' => 'Monthly Salary',
        'category' => 'Salary',
        'transaction_date' => date('Y-m-d')
    ],
    [
        'user_id' => 1,
        'type' => 'expense',
        'amount' => 15000.00,
        'description' => 'Monthly Rent',
        'category' => 'Housing',
        'transaction_date' => date('Y-m-d')
    ]
];

$stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description, category, transaction_date) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($sample_transactions as $transaction) {
    $stmt->bind_param("isdsss", 
        $transaction['user_id'],
        $transaction['type'],
        $transaction['amount'],
        $transaction['description'],
        $transaction['category'],
        $transaction['transaction_date']
    );
    $stmt->execute();
}

echo "Sample transactions added successfully<br>";

echo "<br>Setup completed! <a href='dashboard.php'>Return to Dashboard</a>";
?> 