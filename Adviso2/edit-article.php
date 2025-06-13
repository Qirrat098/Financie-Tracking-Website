<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Function to log messages
function logArticleActivity($message, $article_id, $user_id) {
    $log_file = 'logs/article_activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] User ID: $user_id, Article ID: $article_id - $message\n";
    
    // Create logs directory if it doesn't exist
    if (!file_exists('logs')) {
        mkdir('logs', 0777, true);
    }
    
    // Append to log file
    file_put_contents($log_file, $log_message, FILE_APPEND);
    
    // Also log to error_log for console visibility
    error_log($log_message);
}

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get article ID from URL
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    $_SESSION['error'] = "Invalid article ID";
    header('Location: articles.php');
    exit();
}

// Get categories for the dropdown
$categories_sql = "SELECT id, name FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);

// Get article details
$article_sql = "SELECT * FROM articles WHERE id = ? AND author_id = ?";
$stmt = $conn->prepare($article_sql);
$stmt->bind_param('ii', $article_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Article not found or you don't have permission to edit it";
    header('Location: articles.php');
    exit();
}

$article = $result->fetch_assoc();

// Log initial article load
logArticleActivity("Article loaded for editing. Current status: " . $article['status'], $article_id, $_SESSION['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug POST data
    error_log("POST data received: " . print_r($_POST, true));
    
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    
    // Debug initial status from POST
    error_log("Raw status from POST: " . (isset($_POST['status']) ? $_POST['status'] : 'not set'));
    
    // Handle status with validation
    $valid_statuses = ['draft', 'pending', 'published', 'rejected', 'revision_needed'];
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'draft';
    
    // Debug status after initial assignment
    error_log("Status after initial assignment: " . $status);
    
    // Ensure status is valid
    if (!in_array($status, $valid_statuses)) {
        error_log("Invalid status detected: " . $status . ", defaulting to draft");
        $status = 'draft';
    }
    
    error_log("Final status before update: " . $status);
    
    // Log form submission with all POST data
    logArticleActivity("Form submitted with POST data: " . print_r($_POST, true), $article_id, $_SESSION['user_id']);
    
    // Validate input
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($content)) {
        $errors[] = "Content is required";
    }
    if ($category_id <= 0) {
        $errors[] = "Please select a category";
    }
    
    if (empty($errors)) {
        // Update article
        $update_sql = "UPDATE articles 
                      SET title = ?, 
                          content = ?, 
                          category_id = ?, 
                          status = ?, 
                          updated_at = CURRENT_TIMESTAMP 
                      WHERE id = ? AND author_id = ?";
        
        $stmt = $conn->prepare($update_sql);
        if (!$stmt) {
            logArticleActivity("Error preparing statement: " . $conn->error, $article_id, $_SESSION['user_id']);
            $errors[] = "Error preparing update statement";
        } else {
            error_log("Executing update with status: " . $status);
            $stmt->bind_param('ssiisi', $title, $content, $category_id, $status, $article_id, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                // Verify the update immediately
                $verify_sql = "SELECT status FROM articles WHERE id = ? AND author_id = ?";
                $verify_stmt = $conn->prepare($verify_sql);
                $verify_stmt->bind_param('ii', $article_id, $_SESSION['user_id']);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                $updated_article = $verify_result->fetch_assoc();
                
                error_log("Status after update: " . print_r($updated_article, true));
                
                if ($updated_article['status'] !== $status) {
                    error_log("WARNING: Status mismatch! Expected: " . $status . ", Got: " . $updated_article['status']);
                    
                    // Try to fix the status
                    $fix_sql = "UPDATE articles SET status = ? WHERE id = ? AND author_id = ?";
                    $fix_stmt = $conn->prepare($fix_sql);
                    $fix_stmt->bind_param('sii', $status, $article_id, $_SESSION['user_id']);
                    if ($fix_stmt->execute()) {
                        error_log("Fixed status to: " . $status);
                    } else {
                        error_log("Failed to fix status: " . $conn->error);
                    }
                    $fix_stmt->close();
                }
                
                $_SESSION['success'] = "Article updated successfully!";
                header('Location: articles.php');
                exit();
            } else {
                $error_message = "Error updating article: " . $conn->error;
                logArticleActivity($error_message, $article_id, $_SESSION['user_id']);
                $errors[] = $error_message;
            }
            
            $stmt->close();
        }
    } else {
        logArticleActivity("Validation errors: " . implode(", ", $errors), $article_id, $_SESSION['user_id']);
    }
}

$page_title = "Edit Article";
include 'includes/header.php';
?>

<!-- Add TinyMCE -->
<script src="https://cdn.tiny.cloud/1/e3y7gyakbkdex4cqsb5f36x89sbpj627rlz1f08ciwwshslg/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<!-- Add console logging script -->
<script>
function logToConsole(message) {
    console.log(message);
}

// Log when the page loads
console.log('Article edit page loaded');
console.log('Current article status:', '<?php echo $article['status']; ?>');

// Log form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const status = document.getElementById('status').value;
            console.log('Form submitted with status:', status);
            
            // Log the entire form data
            const formData = new FormData(form);
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // Ensure status is set
            if (!status) {
                e.preventDefault();
                alert('Please select a status');
                return false;
            }
        });
    }
});
</script>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h1 class="h3 mb-0">Edit Article</h1>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($article['title']); ?>" required>
                            <div class="invalid-feedback">Please provide a title.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php while ($category = $categories_result->fetch_assoc()): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $article['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                            <div class="invalid-feedback">Please provide content.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="draft" <?php echo ($article['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="pending" <?php echo ($article['status'] === 'pending') ? 'selected' : ''; ?>>Submit for Review</option>
                            </select>
                            <div class="form-text">Current status: <?php echo htmlspecialchars($article['status'] ?? 'draft'); ?></div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Article</button>
                            <a href="articles.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize TinyMCE
tinymce.init({
    selector: '#content',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    height: 500,
    menubar: true,
    branding: false,
    promotion: false,
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 16px; }',
    setup: function(editor) {
        editor.on('change', function() {
            editor.save(); // Save content to textarea
        });
    }
});

// Form validation
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php include 'includes/footer.php'; ?> 