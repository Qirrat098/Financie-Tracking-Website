<?php
session_start();
require_once 'includes/config.php';

// Get current page for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

// Get category filter
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the query
$where_conditions = ["status = 'published'"];
$params = [];
$types = '';

if ($category_id > 0) {
    $where_conditions[] = "category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR content LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total articles count
$count_sql = "SELECT COUNT(*) as total FROM articles $where_clause";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_articles = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_articles / $per_page);

// Get articles
$sql = "SELECT a.*, c.name as category_name, u.name as author_name 
        FROM articles a 
        JOIN categories c ON a.category_id = c.id 
        JOIN users u ON a.author_id = u.id 
        $where_clause 
        ORDER BY a.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$types .= 'ii';
$params[] = $per_page;
$params[] = $offset;
$stmt->bind_param($types, ...$params);
$stmt->execute();
$articles = $stmt->get_result();

// Get categories for filter
$categories_sql = "SELECT id, name FROM categories ORDER BY name";
$categories = $conn->query($categories_sql);

$page_title = "Financial Literacy Hub";
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Financial Literacy Hub</h1>
            
            <!-- Search and Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search articles..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="category" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <?php if (!empty($search) || $category_id > 0): ?>
                            <div class="col-md-2">
                                <a href="financial-literacy.php" class="btn btn-secondary w-100">
                                    Clear Filters
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Articles Grid -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php if ($articles->num_rows > 0): ?>
                    <?php while ($article = $articles->fetch_assoc()): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="mb-2">
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($article['category_name']); ?>
                                        </span>
                                    </div>
                                    <h5 class="card-title">
                                        <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted">
                                        <?php 
                                        $excerpt = strip_tags($article['content']);
                                        echo strlen($excerpt) > 150 ? substr($excerpt, 0, 150) . '...' : $excerpt;
                                        ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <small class="text-muted">
                                        By <?php echo htmlspecialchars($article['author_name']); ?> • 
                                        <?php echo date('M j, Y', strtotime($article['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            No articles found. <?php echo !empty($search) ? 'Try a different search term.' : ''; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 