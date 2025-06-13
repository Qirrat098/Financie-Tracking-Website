<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

// Handle moderation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = $_POST['article_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $feedback = $_POST['feedback'] ?? '';

    if ($article_id && $action) {
        switch ($action) {
            case 'approve':
                $status = 'published';
                break;
            case 'reject':
                $status = 'rejected';
                break;
            case 'request_revision':
                $status = 'revision_needed';
                break;
            default:
                $status = 'pending';
        }

        $update_sql = "UPDATE articles SET 
            status = ?, 
            moderator_feedback = ?,
            moderated_by = ?,
            moderated_at = CURRENT_TIMESTAMP
            WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssii', $status, $feedback, $_SESSION['user_id'], $article_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Article has been " . str_replace('_', ' ', $status);
        } else {
            $_SESSION['error'] = "Error updating article status";
        }
        
        $stmt->close();
    }
}

// Get pending articles
$pending_sql = "SELECT a.*, u.username as author_name, c.name as category_name 
                FROM articles a 
                JOIN users u ON a.author_id = u.id 
                JOIN categories c ON a.category_id = c.id 
                WHERE a.status = 'pending' 
                ORDER BY a.created_at DESC";
$pending_result = $conn->query($pending_sql);

// Get articles needing revision
$revision_sql = "SELECT a.*, u.username as author_name, c.name as category_name 
                FROM articles a 
                JOIN users u ON a.author_id = u.id 
                JOIN categories c ON a.category_id = c.id 
                WHERE a.status = 'revision_needed' 
                ORDER BY a.moderated_at DESC";
$revision_result = $conn->query($revision_sql);

$page_title = "Moderate Articles";
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Moderate Articles</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Pending Articles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">Pending Articles</h2>
                </div>
                <div class="card-body">
                    <?php if ($pending_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($article = $pending_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <a href="article.php?id=<?php echo $article['id']; ?>" target="_blank">
                                                    <?php echo htmlspecialchars($article['title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                                            <td><?php echo htmlspecialchars($article['category_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($article['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" 
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
                                                                <label class="form-label">Feedback</label>
                                                                <textarea name="feedback" class="form-control" rows="3" 
                                                                          placeholder="Provide feedback for the author..."></textarea>
                                                            </div>

                                                            <div class="d-flex gap-2">
                                                                <button type="submit" name="action" value="approve" 
                                                                        class="btn btn-success flex-grow-1">
                                                                    Approve
                                                                </button>
                                                                <button type="submit" name="action" value="request_revision" 
                                                                        class="btn btn-warning flex-grow-1">
                                                                    Request Revision
                                                                </button>
                                                                <button type="submit" name="action" value="reject" 
                                                                        class="btn btn-danger flex-grow-1">
                                                                    Reject
                                                                </button>
                                                            </div>
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
                        <p class="text-muted">No pending articles to moderate.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Articles Needing Revision -->
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Articles Needing Revision</h2>
                </div>
                <div class="card-body">
                    <?php if ($revision_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th>Feedback</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($article = $revision_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <a href="article.php?id=<?php echo $article['id']; ?>" target="_blank">
                                                    <?php echo htmlspecialchars($article['title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                                            <td><?php echo htmlspecialchars($article['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($article['moderator_feedback']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($article['updated_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#revisionModal<?php echo $article['id']; ?>">
                                                    Review Revision
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Revision Review Modal -->
                                        <div class="modal fade" id="revisionModal<?php echo $article['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Review Revision</h5>
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
                                                                <label class="form-label">Previous Feedback</label>
                                                                <p class="form-control-static"><?php echo htmlspecialchars($article['moderator_feedback']); ?></p>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">New Feedback</label>
                                                                <textarea name="feedback" class="form-control" rows="3" 
                                                                          placeholder="Provide new feedback if needed..."></textarea>
                                                            </div>

                                                            <div class="d-flex gap-2">
                                                                <button type="submit" name="action" value="approve" 
                                                                        class="btn btn-success flex-grow-1">
                                                                    Approve
                                                                </button>
                                                                <button type="submit" name="action" value="request_revision" 
                                                                        class="btn btn-warning flex-grow-1">
                                                                    Request Another Revision
                                                                </button>
                                                                <button type="submit" name="action" value="reject" 
                                                                        class="btn btn-danger flex-grow-1">
                                                                    Reject
                                                                </button>
                                                            </div>
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
                        <p class="text-muted">No articles currently need revision.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 