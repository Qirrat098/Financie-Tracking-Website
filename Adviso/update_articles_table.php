<?php
require_once 'includes/config.php';

// Function to execute SQL queries
function execute_query($conn, $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Query executed successfully: " . $sql . "<br>";
    } else {
        echo "Error executing query: " . $conn->error . "<br>";
    }
}

// Drop existing articles table
$sql = "DROP TABLE IF EXISTS articles";
execute_query($conn, $sql);

// Create articles table with all required columns
$sql = "CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    author_id INT NOT NULL,
    category_id INT NOT NULL,
    status ENUM('draft', 'pending', 'published', 'rejected', 'revision_needed') NOT NULL DEFAULT 'draft',
    moderator_feedback TEXT,
    moderated_by INT,
    moderated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (moderated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

execute_query($conn, $sql);

// Insert sample articles
$sample_articles = [
    [
        'title' => 'Understanding Personal Finance Basics',
        'content' => '<p>Personal finance is the process of planning and managing personal financial activities such as income generation, spending, saving, investing, and protection.</p><p>Key areas of personal finance include:</p><ul><li>Budgeting</li><li>Saving</li><li>Investing</li><li>Insurance</li><li>Retirement planning</li></ul>',
        'category_id' => 1, // Personal Finance
        'status' => 'published'
    ],
    [
        'title' => 'Investment Strategies for Beginners',
        'content' => '<p>Investing can seem daunting for beginners, but with the right approach, anyone can start building their investment portfolio.</p><p>Here are some basic investment strategies:</p><ul><li>Start with index funds</li><li>Diversify your portfolio</li><li>Invest regularly</li><li>Think long-term</li></ul>',
        'category_id' => 2, // Investment
        'status' => 'published'
    ],
    [
        'title' => 'Tax Planning Tips for 2024',
        'content' => '<p>Effective tax planning can help you minimize your tax liability and maximize your savings.</p><p>Important tax planning considerations:</p><ul><li>Understand tax brackets</li><li>Maximize deductions</li><li>Utilize tax-advantaged accounts</li><li>Plan for retirement</li></ul>',
        'category_id' => 3, // Tax Planning
        'status' => 'pending'
    ]
];

// Insert sample articles
foreach ($sample_articles as $article) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $article['title'])));
    $sql = "INSERT INTO articles (title, slug, content, author_id, category_id, status) 
            VALUES (?, ?, ?, 1, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssis', 
        $article['title'],
        $slug,
        $article['content'],
        $article['category_id'],
        $article['status']
    );
    
    if ($stmt->execute()) {
        echo "Sample article added: " . $article['title'] . "<br>";
    } else {
        echo "Error adding sample article: " . $stmt->error . "<br>";
    }
    
    $stmt->close();
}

echo "Articles table has been recreated and populated with sample data successfully!";

$conn->close();
?> 