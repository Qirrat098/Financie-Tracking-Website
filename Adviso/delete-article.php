<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Check if article_id is provided
if (!isset($_POST['article_id'])) {
    $_SESSION['error'] = "Invalid request";
    header('Location: articles.php');
    exit();
}

$article_id = (int)$_POST['article_id'];

// Verify that the article belongs to the current user
$check_sql = "SELECT id FROM articles WHERE id = ? AND author_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param('ii', $article_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Article not found or you don't have permission to delete it";
    header('Location: articles.php');
    exit();
}

// Delete the article
$delete_sql = "DELETE FROM articles WHERE id = ?";
$stmt = $conn->prepare($delete_sql);
$stmt->bind_param('i', $article_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Article deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting article: " . $conn->error;
}

header('Location: articles.php');
exit(); 