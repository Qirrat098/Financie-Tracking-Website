<?php
/**
 * Utility functions for the Adviso application
 */

/**
 * Sanitize user input
 * @param string $input The input to sanitize
 * @return string Sanitized input
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency values
 * @param float $amount The amount to format
 * @return string Formatted currency string
 */
function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 * @param string $date The date to format
 * @param string $format The desired format (default: 'F j, Y')
 * @return string Formatted date
 */
function format_date($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Calculate reading time for an article
 * @param string $content The article content
 * @return int Estimated reading time in minutes
 */
function calculate_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Assuming 200 words per minute
    return max(1, $reading_time); // Minimum 1 minute
}

/**
 * Generate excerpt from content
 * @param string $content The content to excerpt
 * @param int $length Maximum length of excerpt
 * @return string Excerpt
 */
function generate_excerpt($content, $length = 150) {
    $excerpt = strip_tags($content);
    if (strlen($excerpt) > $length) {
        $excerpt = substr($excerpt, 0, $length) . '...';
    }
    return $excerpt;
}

/**
 * Check if user has required role
 * @param string $required_role The role to check for
 * @return bool True if user has required role
 */
function has_role($required_role) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    return $_SESSION['user_role'] === $required_role;
}

/**
 * Get user's full name
 * @param int $user_id The user ID
 * @param mysqli $conn Database connection
 * @return string User's full name
 */
function get_user_name($user_id, $conn) {
    $query = "SELECT first_name, last_name FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        return $user['first_name'] . ' ' . $user['last_name'];
    }
    return 'Unknown User';
}

/**
 * Get category name by ID
 * @param int $category_id The category ID
 * @param mysqli $conn Database connection
 * @return string Category name
 */
function get_category_name($category_id, $conn) {
    $query = "SELECT name FROM categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($category = mysqli_fetch_assoc($result)) {
        return $category['name'];
    }
    return 'Uncategorized';
}

/**
 * Check if article exists
 * @param int $article_id The article ID
 * @param mysqli $conn Database connection
 * @return bool True if article exists
 */
function article_exists($article_id, $conn) {
    $query = "SELECT id FROM articles WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $article_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

/**
 * Get article status badge HTML
 * @param string $status The article status
 * @return string HTML for status badge
 */
function get_status_badge($status) {
    $badges = [
        'draft' => '<span class="status-badge draft">Draft</span>',
        'published' => '<span class="status-badge published">Published</span>',
        'pending' => '<span class="status-badge pending">Pending Review</span>'
    ];
    return $badges[$status] ?? $badges['draft'];
}

/**
 * Log user activity
 * @param int $user_id The user ID
 * @param string $action The action performed
 * @param mysqli $conn Database connection
 * @return bool True if logged successfully
 */
function log_activity($user_id, $action, $conn) {
    $query = "INSERT INTO activity_logs (user_id, action, created_at) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $action);
    return mysqli_stmt_execute($stmt);
}

/**
 * Check if user is article author
 * @param int $article_id The article ID
 * @param int $user_id The user ID
 * @param mysqli $conn Database connection
 * @return bool True if user is author
 */
function is_article_author($article_id, $user_id, $conn) {
    $query = "SELECT user_id FROM articles WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $article_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

/**
 * Get pagination links
 * @param int $current_page Current page number
 * @param int $total_pages Total number of pages
 * @param string $base_url Base URL for pagination links
 * @return string HTML for pagination links
 */
function get_pagination_links($current_page, $total_pages, $base_url) {
    $html = '<div class="pagination">';
    
    // Previous page link
    if ($current_page > 1) {
        $html .= '<a href="' . $base_url . '?page=' . ($current_page - 1) . '" class="btn btn-secondary">&laquo; Previous</a>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="current-page">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $base_url . '?page=' . $i . '" class="page-link">' . $i . '</a>';
        }
    }
    
    // Next page link
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $base_url . '?page=' . ($current_page + 1) . '" class="btn btn-secondary">Next &raquo;</a>';
    }
    
    $html .= '</div>';
    return $html;
} 