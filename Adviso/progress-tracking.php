<?php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's financial goals with progress
$stmt = $conn->prepare("
    SELECT 
        g.*,
        (g.current_amount / g.target_amount * 100) as progress_percentage,
        DATEDIFF(g.target_date, CURDATE()) as days_remaining
    FROM financial_goals g
    WHERE g.user_id = ?
    ORDER BY g.target_date ASC
");

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']);

if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die("Error getting result: " . $stmt->error);
}

$goals = $result->fetch_all(MYSQLI_ASSOC);

// Debug information
$debug_info = [
    'user_id' => $_SESSION['user_id'],
    'total_goals' => count($goals),
    'goals_data' => $goals
];

// Calculate overall progress statistics
$total_goals = count($goals);
$completed_goals = 0;
$total_progress = 0;
$upcoming_milestones = [];

foreach ($goals as $goal) {
    if ($goal['progress_percentage'] >= 100) {
        $completed_goals++;
    }
    $total_progress += $goal['progress_percentage'];
    
    // Add milestone if goal is not completed and has significant progress
    if ($goal['progress_percentage'] < 100 && $goal['progress_percentage'] >= 25) {
        $upcoming_milestones[] = [
            'title' => $goal['title'],
            'progress' => $goal['progress_percentage'],
            'days_remaining' => $goal['days_remaining']
        ];
    }
}

$average_progress = $total_goals > 0 ? $total_progress / $total_goals : 0;
$completion_rate = $total_goals > 0 ? ($completed_goals / $total_goals) * 100 : 0;

$page_title = "Progress Tracking";
include 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Progress Tracking</h1>

    <?php if (empty($goals)): ?>
        <div class="alert alert-info">
            <h4 class="alert-heading">No Goals Found</h4>
            <p>You haven't created any financial goals yet. Start by creating your first goal!</p>
            <hr>
            <p class="mb-0">
                <a href="financial-goals.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Goal
                </a>
            </p>
        </div>
    <?php else: ?>
        <!-- Progress Overview -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-chart-pie"></i> Overall Progress
                        </h5>
                        <div class="progress-circle" data-progress="<?php echo $average_progress; ?>">
                            <span class="progress-text"><?php echo number_format($average_progress, 1); ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-trophy"></i> Completion Rate
                        </h5>
                        <div class="progress-circle" data-progress="<?php echo $completion_rate; ?>">
                            <span class="progress-text"><?php echo number_format($completion_rate, 1); ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-bullseye"></i> Active Goals
                        </h5>
                        <div class="d-flex justify-content-center align-items-center" style="height: 150px;">
                            <h2 class="mb-0"><?php echo $total_goals; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Goals Progress -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-tasks"></i> Goals Progress
                        </h5>
                        <?php foreach ($goals as $goal): ?>
                            <div class="goal-progress mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($goal['title']); ?></h6>
                                    <span class="badge bg-<?php echo $goal['progress_percentage'] >= 100 ? 'success' : 'primary'; ?>">
                                        <?php echo number_format($goal['progress_percentage'], 1); ?>%
                                    </span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $goal['progress_percentage'] >= 100 ? 'bg-success' : ''; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $goal['progress_percentage']; ?>%">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small>$<?php echo number_format($goal['current_amount'], 2); ?> / $<?php echo number_format($goal['target_amount'], 2); ?></small>
                                    <small><?php echo $goal['days_remaining']; ?> days remaining</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Milestones -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-flag"></i> Upcoming Milestones
                        </h5>
                        <?php if (empty($upcoming_milestones)): ?>
                            <p class="text-center">No upcoming milestones</p>
                        <?php else: ?>
                            <div class="milestones">
                                <?php foreach ($upcoming_milestones as $milestone): ?>
                                    <div class="milestone-item mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($milestone['title']); ?></h6>
                                                <small class="text-muted"><?php echo $milestone['days_remaining']; ?> days remaining</small>
                                            </div>
                                            <div class="progress-circle small" data-progress="<?php echo $milestone['progress']; ?>">
                                                <span class="progress-text"><?php echo number_format($milestone['progress'], 1); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.progress-circle {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto;
    border-radius: 50%;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-circle::before {
    content: '';
    position: absolute;
    width: 130px;
    height: 130px;
    border-radius: 50%;
    background: white;
}

.progress-circle::after {
    content: '';
    position: absolute;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: conic-gradient(#0d6efd var(--progress), transparent var(--progress));
    --progress: calc(var(--data-progress) * 1%);
}

.progress-circle.small {
    width: 60px;
    height: 60px;
}

.progress-circle.small::before {
    width: 50px;
    height: 50px;
}

.progress-circle.small::after {
    width: 60px;
    height: 60px;
}

.progress-text {
    position: relative;
    z-index: 1;
    font-size: 1.5rem;
    font-weight: bold;
    color: #0d6efd;
}

.progress-circle.small .progress-text {
    font-size: 0.8rem;
}

.goal-progress {
    padding: 10px;
    border-radius: 4px;
    background: #f8f9fa;
}

.milestone-item {
    padding: 10px;
    border-radius: 4px;
    background: #f8f9fa;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize progress circles
    document.querySelectorAll('.progress-circle').forEach(circle => {
        const progress = circle.getAttribute('data-progress');
        circle.style.setProperty('--data-progress', progress);
    });
});
</script>

<?php include 'includes/footer.php'; ?> 