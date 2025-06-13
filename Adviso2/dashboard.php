<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$page_title = "Dashboard";
include 'includes/header.php';

// Get user's financial goals
$goals_stmt = $conn->prepare("
    SELECT * FROM financial_goals 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$goals_stmt->bind_param("i", $user_id);
$goals_stmt->execute();
$goals_result = $goals_stmt->get_result();

// Get user's financial goals summary
$goals_summary = $conn->query("
    SELECT 
        COUNT(*) as total_goals,
        SUM(CASE WHEN status = 'completed' OR saved_amount >= target_amount THEN 1 ELSE 0 END) as completed_goals,
        COALESCE(SUM(saved_amount), 0) as total_saved,
        COALESCE(SUM(target_amount), 0) as total_target
    FROM financial_goals 
    WHERE user_id = " . $_SESSION['user_id']
)->fetch_assoc();

// Get recent transactions
$transactions_stmt = $conn->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? 
    ORDER BY transaction_date DESC 
    LIMIT 5
");
$transactions_stmt->bind_param("i", $user_id);
$transactions_stmt->execute();
$transactions_result = $transactions_stmt->get_result();

// Get monthly spending summary
$monthly_stmt = $conn->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expenses
    FROM transactions 
    WHERE user_id = ? 
    AND MONTH(transaction_date) = MONTH(CURRENT_DATE())
    AND YEAR(transaction_date) = YEAR(CURRENT_DATE())
");
$monthly_stmt->bind_param("i", $user_id);
$monthly_stmt->execute();
$monthly_summary = $monthly_stmt->get_result()->fetch_assoc();

// Get recent achievements
$achievements_stmt = $conn->prepare("
    SELECT a.*, ua.earned_at 
    FROM achievements a
    JOIN user_achievements ua ON a.id = ua.achievement_id
    WHERE ua.user_id = ?
    ORDER BY ua.earned_at DESC
    LIMIT 3
");
$achievements_stmt->bind_param("i", $user_id);
$achievements_stmt->execute();
$achievements_result = $achievements_stmt->get_result();
?>

<div class="container py-5">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-3">Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>!</h1>
            <p class="text-muted">Track your financial progress and manage your goals</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="create-goal.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Goal
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Financial Goals</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <h3 class="mb-0"><?php echo number_format((float)$goals_summary['total_goals'], 0); ?></h3>
                            <small class="text-muted">Total Goals</small>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo number_format((float)$goals_summary['completed_goals'], 0); ?></h3>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <?php 
                        $progress = $goals_summary['total_goals'] > 0 
                            ? ($goals_summary['completed_goals'] / $goals_summary['total_goals']) * 100 
                            : 0;
                        ?>
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">₹<?php echo number_format((float)$goals_summary['total_saved'], 2); ?></h4>
                            <small class="text-muted">Total Saved</small>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">₹<?php echo number_format((float)$goals_summary['total_target'], 2); ?></h4>
                            <small class="text-muted">Target</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Monthly Income</h6>
                    <h2 class="card-title mb-0">₹<?php echo number_format((float)$monthly_summary['total_income'], 2); ?></h2>
                    <p class="text-success mb-0">
                        <small>This month</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Monthly Expenses</h6>
                    <h2 class="card-title mb-0">₹<?php echo number_format((float)$monthly_summary['total_expenses'], 2); ?></h2>
                    <p class="text-danger mb-0">
                        <small>This month</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Goals -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Recent Goals</h5>
                </div>
                <div class="card-body">
                    <?php if ($goals_result->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($goal = $goals_result->fetch_assoc()): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($goal['title']); ?></h6>
                                        <span class="badge bg-<?php echo $goal['status'] === 'completed' ? 'success' : 'primary'; ?>">
                                            <?php echo ucfirst($goal['status']); ?>
                                        </span>
                                    </div>
                                    <div class="progress mb-2" style="height: 8px;">
                                        <?php 
                                        $progress = ($goal['saved_amount'] / $goal['target_amount']) * 100;
                                        $progress = min($progress, 100);
                                        ?>
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small">
                                        <span>₹<?php echo number_format($goal['saved_amount'], 2); ?> saved</span>
                                        <span>₹<?php echo number_format($goal['target_amount'], 2); ?> target</span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center my-4">No goals created yet</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="financial-goals.php" class="btn btn-link p-0">View All Goals</a>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <?php if ($transactions_result->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($transaction = $transactions_result->fetch_assoc()): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($transaction['description']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?>
                                            </small>
                                        </div>
                                        <span class="text-<?php echo $transaction['type'] === 'income' ? 'success' : 'danger'; ?>">
                                            <?php echo $transaction['type'] === 'income' ? '+' : '-'; ?>₹<?php echo number_format($transaction['amount'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center my-4">No recent transactions</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="add-transaction.php" class="btn btn-link p-0">Add Transaction</a>
                </div>
            </div>
        </div>

        <!-- Recent Achievements -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Recent Achievements</h5>
                </div>
                <div class="card-body">
                    <?php if ($achievements_result->num_rows > 0): ?>
                        <div class="row">
                            <?php while ($achievement = $achievements_result->fetch_assoc()): ?>
                                <div class="col-md-4">
                                    <div class="achievement-card text-center p-4">
                                        <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                                        <h5><?php echo htmlspecialchars($achievement['title']); ?></h5>
                                        <p class="text-muted mb-2"><?php echo htmlspecialchars($achievement['description']); ?></p>
                                        <small class="text-muted">
                                            Earned on <?php echo date('M d, Y', strtotime($achievement['earned_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center my-4">No achievements earned yet</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="achievements.php" class="btn btn-link p-0">View All Achievements</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.achievement-card {
    background: #f8f9fa;
    border-radius: 8px;
    transition: transform 0.2s;
}

.achievement-card:hover {
    transform: translateY(-5px);
}

.progress {
    background-color: #e9ecef;
    border-radius: 4px;
}

.progress-bar {
    background-color: #0d6efd;
    border-radius: 4px;
}

.card {
    border: none;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.btn-link {
    color: #0d6efd;
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}
</style>

<?php include 'includes/footer.php'; ?> 