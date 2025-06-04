<?php
require_once 'includes/config.php';

// Drop existing tax_records table if it exists
$drop_table_sql = "DROP TABLE IF EXISTS tax_records";
if ($conn->query($drop_table_sql)) {
    echo "Existing tax records table dropped successfully<br>";
} else {
    echo "Error dropping table: " . $conn->error . "<br>";
}

// Create tax_records table
$create_table_sql = "CREATE TABLE IF NOT EXISTS tax_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tax_year VARCHAR(9) NOT NULL,
    total_income DECIMAL(15,2) NOT NULL,
    total_deductions DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_tax DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed') NOT NULL DEFAULT 'pending',
    filing_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($create_table_sql)) {
    echo "Tax records table created successfully<br>";
    
    // Add sample data
    $sample_data_sql = "INSERT INTO tax_records (user_id, tax_year, total_income, total_deductions, total_tax, status, filing_date) VALUES
        (1, '2023-24', 1200000.00, 150000.00, 150000.00, 'completed', '2024-03-15'),
        (1, '2022-23', 1000000.00, 120000.00, 120000.00, 'completed', '2023-03-15'),
        (1, '2024-25', 1500000.00, 200000.00, 200000.00, 'pending', NULL)";
    
    if ($conn->query($sample_data_sql)) {
        echo "Sample tax records added successfully<br>";
    } else {
        echo "Error adding sample data: " . $conn->error . "<br>";
    }
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Function to execute SQL queries
function execute_query($conn, $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Query executed successfully<br>";
    } else {
        echo "Error executing query: " . mysqli_error($conn) . "<br>";
    }
}

// Create categories table
$create_categories_table = "CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

execute_query($conn, $create_categories_table);

// Insert sample categories
$categories = [
    ['Personal Finance', 'personal-finance', 'Articles about managing personal finances, budgeting, and saving money'],
    ['Investment', 'investment', 'Articles about investment strategies, stocks, bonds, and other investment vehicles'],
    ['Tax Planning', 'tax-planning', 'Articles about tax strategies, deductions, and tax-efficient investing'],
    ['Retirement', 'retirement', 'Articles about retirement planning, pensions, and retirement accounts'],
    ['Insurance', 'insurance', 'Articles about different types of insurance and risk management'],
    ['Estate Planning', 'estate-planning', 'Articles about wills, trusts, and estate management'],
    ['Financial Education', 'financial-education', 'Articles about financial literacy and education'],
    ['Market Analysis', 'market-analysis', 'Articles about market trends and economic analysis']
];

foreach ($categories as $category) {
    $name = mysqli_real_escape_string($conn, $category[0]);
    $slug = mysqli_real_escape_string($conn, $category[1]);
    $description = mysqli_real_escape_string($conn, $category[2]);
    
    $insert_category = "INSERT IGNORE INTO categories (name, slug, description) 
                       VALUES ('$name', '$slug', '$description')";
    execute_query($conn, $insert_category);
}

echo "Database update completed!";

$conn->close();
?> 