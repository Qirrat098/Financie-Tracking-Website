<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$page_title = "Moderate Articles";
include 'includes/header.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id'], $_POST['status'])) {
    $article_id = (int)$_POST['article_id'];
    $status = $_POST['status'];
    $feedback = $_POST['feedback'] ?? '';
    
    $update_stmt = $conn->prepare("
        UPDATE articles 
        SET status = ?, 
            moderator_feedback = ?,
            moderated_by = ?,
            moderated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $update_stmt->bind_param("ssii", $status, $feedback, $_SESSION['user_id'], $article_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Article status updated successfully.";
    } else {
        $error_message = "Error updating article status: " . $conn->error;
    }
}

// Get articles pending moderation
$articles_stmt = $conn->prepare("
    SELECT a.*, u.name as author_name, c.name as category_name,
           COALESCE(m.name, 'Not moderated') as moderator_name
    FROM articles a
    JOIN users u ON a.author_id = u.id
    JOIN categories c ON a.category_id = c.id
    LEFT JOIN users m ON a.moderated_by = m.id
    WHERE a.status IN ('pending', 'revision_needed')
    ORDER BY a.created_at DESC
");
$articles_stmt->execute();
$articles_result = $articles_stmt->get_result();
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h1>Moderate Articles</h1>
            <p class="text-muted">Review and moderate pending articles</p>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col">
            <?php if ($articles_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Last Moderated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($article = $articles_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" target="_blank">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                                    <td><?php echo htmlspecialchars($article['category_name']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $article['status'] === 'pending' ? 'warning' : 
                                                ($article['status'] === 'revision_needed' ? 'info' : 'secondary'); 
                                        ?>">
                                            <?php echo ucfirst($article['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($article['created_at'])); ?></td>
                                    <td>
                                        <?php 
                                        if ($article['moderated_at']) {
                                            echo date('M d, Y', strtotime($article['moderated_at']));
                                            echo " by " . htmlspecialchars($article['moderator_name']);
                                        } else {
                                            echo "Not moderated yet";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#moderateModal<?php echo $article['id']; ?>">
                                            Moderate
                                        </button>
                                    </td>
                                </tr>

                                <!-- Moderation Modal -->
                                <div class="modal fade" id="moderateModal<?php echo $article['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Moderate Article</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Article Title</label>
                                                        <p class="form-control-static"><?php echo htmlspecialchars($article['title']); ?></p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Current Status</label>
                                                        <p class="form-control-static">
                                                            <span class="badge bg-<?php 
                                                                echo $article['status'] === 'pending' ? 'warning' : 
                                                                    ($article['status'] === 'revision_needed' ? 'info' : 'secondary'); 
                                                            ?>">
                                                                <?php echo ucfirst($article['status']); ?>
                                                            </span>
                                                        </p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="status" class="form-label">New Status</label>
                                                        <select class="form-select" name="status" id="status" required>
                                                            <option value="published">Publish</option>
                                                            <option value="revision_needed">Request Revision</option>
                                                            <option value="rejected">Reject</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="feedback" class="form-label">Feedback to Author</label>
                                                        <textarea class="form-control" name="feedback" id="feedback" rows="3" 
                                                                placeholder="Provide feedback to the author..."><?php echo htmlspecialchars($article['moderator_feedback']); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    No articles pending moderation.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 