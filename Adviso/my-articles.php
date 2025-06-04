<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = "My Articles";
include 'includes/header.php';

// Get user's articles
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT a.*, c.name as category_name, 
           COALESCE((SELECT COUNT(*) FROM article_views WHERE article_id = a.id), 0) as views,
           COALESCE((SELECT COUNT(*) FROM article_likes WHERE article_id = a.id), 0) as likes
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.author_id = ?
    ORDER BY a.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-3">My Articles</h1>
            <p class="text-muted">Manage your published articles and track their performance</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="write-article.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Write New Article
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Likes</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($article = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <a href="article.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($article['title']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($article['category_name']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($article['status'] === 'published'): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php elseif ($article['status'] === 'draft'): ?>
                                                    <span class="badge bg-warning">Draft</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Under Review</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-eye text-muted"></i>
                                                <?php echo number_format($article['views']); ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-heart text-danger"></i>
                                                <?php echo number_format($article['likes']); ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="edit-article.php?id=<?php echo $article['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal<?php echo $article['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>

                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $article['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete "<?php echo htmlspecialchars($article['title']); ?>"?
                                                                This action cannot be undone.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <a href="delete-article.php?id=<?php echo $article['id']; ?>" 
                                                                   class="btn btn-danger">Delete</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h4>No Articles Yet</h4>
                            <p class="text-muted">Start writing your first article to share your knowledge!</p>
                            <a href="write-article.php" class="btn btn-primary mt-3">
                                <i class="fas fa-plus"></i> Write Your First Article
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 