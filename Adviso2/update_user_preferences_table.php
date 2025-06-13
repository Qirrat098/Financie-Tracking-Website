<?php
require_once 'includes/config.php';

// Create user_preferences table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS user_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    theme VARCHAR(10) DEFAULT 'light',
    email_notifications BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT TRUE,
    share_progress BOOLEAN DEFAULT FALSE,
    share_achievements BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql)) {
    echo "User preferences table created successfully!<br>";
} else {
    echo "Error creating user preferences table: " . $conn->error . "<br>";
}

// Add default preferences for existing users
$sql = "INSERT INTO user_preferences (user_id, theme, email_notifications, push_notifications)
        SELECT id, 'light', TRUE, TRUE
        FROM users
        WHERE id NOT IN (SELECT user_id FROM user_preferences)";

if ($conn->query($sql)) {
    echo "Default preferences added for existing users!<br>";
} else {
    echo "Error adding default preferences: " . $conn->error . "<br>";
}

echo "<br><a href='settings.php'>Go to Settings</a>";
?> 