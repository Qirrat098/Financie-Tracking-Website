<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    header('Location: login.php');
    exit();
}

// Check if user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You do not have permission to access this page.";
    header('Location: dashboard.php');
    exit();
}

require_once 'includes/config.php';
require_once 'includes/auth.php';

// Additional security check - verify admin status in database
$user_id = $_SESSION['user_id'];
$check_admin = $conn->prepare("SELECT role FROM users WHERE id = ? AND role = 'admin'");
$check_admin->bind_param("i", $user_id);
$check_admin->execute();
$result = $check_admin->get_result();

if ($result->num_rows === 0) {
    // If user is not an admin in database, clear session and redirect
    session_destroy();
    $_SESSION['error'] = "Invalid access attempt detected.";
    header('Location: login.php');
    exit();
}

$page_title = "Admin Dashboard";
include 'includes/header.php';

// Get statistics
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_articles' => $conn->query("SELECT COUNT(*) as count FROM articles")->fetch_assoc()['count'],
    'pending_articles' => $conn->query("SELECT COUNT(*) as count FROM articles WHERE status = 'pending'")->fetch_assoc()['count'],
    'total_goals' => $conn->query("SELECT COUNT(*) as count FROM financial_goals")->fetch_assoc()['count'],
    'total_transactions' => $conn->query("SELECT COUNT(*) as count FROM transactions")->fetch_assoc()['count']
];

// Get recent activities
$recent_articles = $conn->query("
    SELECT a.*, u.name as author_name 
    FROM articles a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");

$recent_users = $conn->query("
    SELECT * FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h1>Admin Dashboard</h1>
            <p class="text-muted">Overview of system statistics and recent activities</p>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Admin Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="manage-users.php" class="btn btn-primary w-100 py-3">
                                <i class="fas fa-users me-2"></i>
                                Manage Users
                                <small class="d-block mt-1">Add, edit, or remove user accounts</small>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="moderate-article.php" class="btn btn-success w-100 py-3">
                                <i class="fas fa-newspaper me-2"></i>
                                Moderate Articles
                                <small class="d-block mt-1">Review and manage article submissions</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Total Users</h6>
                    <h2 class="card-title mb-0"><?php echo number_format($stats['total_users']); ?></h2>
                    <p class="text-muted mb-0">
                        <small>Registered users</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Articles</h6>
                    <h2 class="card-title mb-0"><?php echo number_format($stats['total_articles']); ?></h2>
                    <p class="text-muted mb-0">
                        <small><?php echo number_format($stats['pending_articles']); ?> pending review</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Financial Data</h6>
                    <h2 class="card-title mb-0"><?php echo number_format($stats['total_goals']); ?></h2>
                    <p class="text-muted mb-0">
                        <small><?php echo number_format($stats['total_transactions']); ?> transactions</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Articles -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Articles</h5>
                    <a href="moderate-article.php" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-newspaper me-1"></i> Manage
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($recent_articles->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($article = $recent_articles->fetch_assoc()): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($article['title']); ?></h6>
                                            <small class="text-muted">
                                                By <?php echo htmlspecialchars($article['author_name']); ?> • 
                                                <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?php 
                                            echo $article['status'] === 'published' ? 'success' : 
                                                ($article['status'] === 'pending' ? 'warning' : 'secondary'); 
                                        ?>">
                                            <?php echo ucfirst($article['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center my-4">No articles found</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Users</h5>
                    <a href="manage-users.php" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-users me-1"></i> Manage
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($recent_users->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($user['email']); ?> • 
                                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center my-4">No users found</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
}
.badge {
    font-size: 0.85em;
    padding: 0.5em 0.75em;
}
.btn-link {
    color: #0d6efd;
    text-decoration: none;
}
.btn-link:hover {
    text-decoration: underline;
}
.btn {
    position: relative;
    overflow: hidden;
}
.btn::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.3s, height 0.3s;
}
.btn:hover::after {
    width: 300px;
    height: 300px;
}
.btn small {
    opacity: 0.8;
    font-size: 0.85em;
}
</style>

<?php include 'includes/footer.php'; ?> 