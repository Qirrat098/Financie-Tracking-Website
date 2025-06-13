<?php
require_once 'includes/config.php';

// Check if admin user exists
$check_admin = $conn->query("SELECT id FROM users WHERE email = 'admin@adviso.com'");

if ($check_admin->num_rows === 0) {
    // Create admin user
    $name = 'Admin User';
    $email = 'admin@adviso.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $role = 'admin';
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully!<br>";
        echo "Email: admin@adviso.com<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating admin user: " . $conn->error;
    }
} else {
    echo "Admin user already exists!<br>";
    echo "Email: admin@adviso.com<br>";
    echo "Password: admin123<br>";
}

// Verify admin user
$admin = $conn->query("SELECT id, name, email, role FROM users WHERE email = 'admin@adviso.com'")->fetch_assoc();
echo "<br>Admin user details:<br>";
echo "ID: " . $admin['id'] . "<br>";
echo "Name: " . $admin['name'] . "<br>";
echo "Email: " . $admin['email'] . "<br>";
echo "Role: " . $admin['role'] . "<br>";

echo "<br><a href='login.php'>Go to Login</a>";
?> 