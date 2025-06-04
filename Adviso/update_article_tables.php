<?php
require_once 'includes/config.php';

// Create article_views table
$sql = "CREATE TABLE IF NOT EXISTS article_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT NOT NULL,
    user_id INT,
    ip_address VARCHAR(45),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql)) {
    echo "Article views table created successfully!<br>";
} else {
    echo "Error creating article views table: " . $conn->error . "<br>";
}

// Create article_likes table
$sql = "CREATE TABLE IF NOT EXISTS article_likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT NOT NULL,
    user_id INT NOT NULL,
    liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_article_like (article_id, user_id)
)";

if ($conn->query($sql)) {
    echo "Article likes table created successfully!<br>";
} else {
    echo "Error creating article likes table: " . $conn->error . "<br>";
}

// Add indexes for better performance
$indexes = [
    "CREATE INDEX idx_article_views_article ON article_views(article_id)",
    "CREATE INDEX idx_article_views_user ON article_views(user_id)",
    "CREATE INDEX idx_article_views_ip ON article_views(ip_address)",
    "CREATE INDEX idx_article_likes_article ON article_likes(article_id)",
    "CREATE INDEX idx_article_likes_user ON article_likes(user_id)"
];

foreach ($indexes as $index) {
    if ($conn->query($index)) {
        echo "Index created successfully!<br>";
    } else {
        echo "Error creating index: " . $conn->error . "<br>";
    }
}

echo "<br><a href='my-articles.php'>Go to My Articles</a>";
?> 