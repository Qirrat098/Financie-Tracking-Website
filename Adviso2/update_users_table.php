<?php
require_once 'includes/config.php';

// Temporarily disable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Drop tables in correct order (child tables first)
$tables = [
    'user_achievements',
    'achievements',
    'transactions',
    'financial_goals',
    'users'
];

foreach ($tables as $table) {
    $sql = "DROP TABLE IF EXISTS $table";
    if ($conn->query($sql)) {
        echo "Table $table dropped successfully<br>";
    } else {
        echo "Error dropping table $table: " . $conn->error . "<br>";
    }
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Create users table with correct structure
$sql = "CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Insert a default admin user
$default_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$name = 'Admin User';
$email = 'admin@adviso.com';
$role = 'admin';
$stmt->bind_param("ssss", $name, $email, $default_password, $role);

if ($stmt->execute()) {
    echo "Default admin user created successfully<br>";
} else {
    echo "Error creating default admin user: " . $stmt->error . "<br>";
}

// Insert a test user
$test_password = password_hash('test123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$name = 'Test User';
$email = 'test@adviso.com';
$stmt->bind_param("sss", $name, $email, $test_password);

if ($stmt->execute()) {
    echo "Test user created successfully<br>";
} else {
    echo "Error creating test user: " . $stmt->error . "<br>";
}

// Add indexes for better performance
$indexes = [
    "CREATE INDEX idx_users_email ON users(email)",
    "CREATE INDEX idx_users_role ON users(role)"
];

foreach ($indexes as $index) {
    if ($conn->query($index)) {
        echo "Index created successfully<br>";
    } else {
        echo "Error creating index: " . $conn->error . "<br>";
    }
}

echo "<br>Users table setup completed! <a href='update_financial_goals_table.php'>Continue with Financial Tables Setup</a>";
?> 