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

// Check current table structure
$sql = "SHOW CREATE TABLE articles";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    echo "Current table structure:<br>";
    echo "<pre>" . $row['Create Table'] . "</pre><br>";
}

// Fix the articles table structure
$sql = "ALTER TABLE articles MODIFY COLUMN status ENUM('draft', 'pending', 'published', 'rejected', 'revision_needed') NOT NULL DEFAULT 'draft'";
execute_query($conn, $sql);

// Update any NULL status values to 'draft'
$sql = "UPDATE articles SET status = 'draft' WHERE status IS NULL";
execute_query($conn, $sql);

// Check for any articles with NULL status
$sql = "SELECT id, title, status FROM articles WHERE status IS NULL";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "Warning: Found articles with NULL status:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "Article ID: " . $row['id'] . ", Title: " . $row['title'] . "<br>";
    }
} else {
    echo "No articles found with NULL status.<br>";
}

// Check all articles and their statuses
$sql = "SELECT id, title, status FROM articles ORDER BY id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "<br>All articles and their statuses:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "Article ID: " . $row['id'] . ", Title: " . $row['title'] . ", Status: " . ($row['status'] ?? 'NULL') . "<br>";
    }
}

echo "<br>Articles table has been checked and fixed. <a href='articles.php'>Return to Articles</a>";

$conn->close();
?> 