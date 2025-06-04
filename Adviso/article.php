<?php
session_start();
require_once 'includes/config.php';

// Get article slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: financial-literacy.php');
    exit();
}

// Get article details
$sql = "SELECT a.*, c.name as category_name, u.name as author_name 
        FROM articles a 
        JOIN categories c ON a.category_id = c.id 
        JOIN users u ON a.author_id = u.id 
        WHERE a.slug = ? AND a.status = 'published'";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: financial-literacy.php');
    exit();
}

$article = $result->fetch_assoc();

// Get related articles
$related_sql = "SELECT a.*, c.name as category_name, u.name as author_name 
                FROM articles a 
                JOIN categories c ON a.category_id = c.id 
                JOIN users u ON a.author_id = u.id 
                WHERE a.category_id = ? 
                AND a.id != ? 
                AND a.status = 'published' 
                ORDER BY a.created_at DESC 
                LIMIT 3";

$stmt = $conn->prepare($related_sql);
$stmt->bind_param('ii', $article['category_id'], $article['id']);
$stmt->execute();
$related_articles = $stmt->get_result();

$page_title = $article['title'];
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Article Content -->
            <article class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($article['category_name']); ?></span>
                    </div>
                    
                    <h1 class="card-title h2 mb-3"><?php echo htmlspecialchars($article['title']); ?></h1>
                    
                    <div class="text-muted mb-4">
                        By <?php echo htmlspecialchars($article['author_name']); ?> • 
                        <?php echo date('F j, Y', strtotime($article['created_at'])); ?>
                    </div>
                    
                    <div class="article-content">
                        <?php echo $article['content']; ?>
                    </div>
                </div>
            </article>
        </div>
        
        <div class="col-lg-4">
            <!-- Related Articles -->
            <?php if ($related_articles->num_rows > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Related Articles</h2>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php while ($related = $related_articles->fetch_assoc()): ?>
                                <a href="article.php?slug=<?php echo htmlspecialchars($related['slug']); ?>" 
                                   class="list-group-item list-group-item-action">
                                    <h3 class="h6 mb-1"><?php echo htmlspecialchars($related['title']); ?></h3>
                                    <small class="text-muted">
                                        By <?php echo htmlspecialchars($related['author_name']); ?> • 
                                        <?php echo date('M j, Y', strtotime($related['created_at'])); ?>
                                    </small>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Category Navigation -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">Categories</h2>
                </div>
                <div class="card-body">
                    <?php
                    $categories_sql = "SELECT c.*, COUNT(a.id) as article_count 
                                     FROM categories c 
                                     LEFT JOIN articles a ON c.id = a.category_id 
                                     WHERE a.status = 'published' 
                                     GROUP BY c.id 
                                     ORDER BY article_count DESC";
                    $categories = $conn->query($categories_sql);
                    ?>
                    <div class="list-group list-group-flush">
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <a href="financial-literacy.php?category=<?php echo $category['id']; ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($category['name']); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $category['article_count']; ?></span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.article-content {
    font-size: 1.1rem;
    line-height: 1.8;
}

.article-content img {
    max-width: 100%;
    height: auto;
    margin: 1rem 0;
}

.article-content h2, 
.article-content h3, 
.article-content h4 {
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.article-content p {
    margin-bottom: 1.5rem;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.article-content blockquote {
    border-left: 4px solid #dee2e6;
    padding-left: 1rem;
    margin-left: 0;
    margin-bottom: 1.5rem;
    color: #6c757d;
}
</style>

<?php include 'includes/footer.php'; ?> 