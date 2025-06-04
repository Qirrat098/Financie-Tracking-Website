<?php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's achievements and milestones
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
$goals = $result->fetch_all(MYSQLI_ASSOC);

// Calculate achievements
$achievements = [
    'first_goal' => false,
    'goal_master' => false,
    'savings_champion' => false,
    'early_bird' => false,
    'consistency_king' => false
];

$total_saved = 0;
$completed_goals = 0;
$early_completions = 0;

foreach ($goals as $goal) {
    $total_saved += $goal['current_amount'];
    if ($goal['progress_percentage'] >= 100) {
        $completed_goals++;
        if ($goal['days_remaining'] > 0) {
            $early_completions++;
        }
    }
}

// Check achievement conditions
if (count($goals) > 0) {
    $achievements['first_goal'] = true;
}
if (count($goals) >= 5) {
    $achievements['goal_master'] = true;
}
if ($total_saved >= 10000) {
    $achievements['savings_champion'] = true;
}
if ($early_completions > 0) {
    $achievements['early_bird'] = true;
}
if ($completed_goals >= 3) {
    $achievements['consistency_king'] = true;
}

// Get milestones
$milestones = [];
foreach ($goals as $goal) {
    if ($goal['progress_percentage'] >= 25 && $goal['progress_percentage'] < 100) {
        $milestones[] = [
            'title' => $goal['title'],
            'progress' => $goal['progress_percentage'],
            'days_remaining' => $goal['days_remaining'],
            'category' => $goal['category']
        ];
    }
}

$page_title = "Achievements & Milestones";
include 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Achievements & Milestones</h1>

    <!-- Achievements Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-trophy"></i> Your Achievements
                    </h5>
                    <div class="achievements-grid">
                        <div class="achievement-item <?php echo $achievements['first_goal'] ? 'unlocked' : ''; ?>">
                            <div class="achievement-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <h6>First Goal</h6>
                            <p>Created your first financial goal</p>
                        </div>
                        <div class="achievement-item <?php echo $achievements['goal_master'] ? 'unlocked' : ''; ?>">
                            <div class="achievement-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h6>Goal Master</h6>
                            <p>Created 5 or more financial goals</p>
                        </div>
                        <div class="achievement-item <?php echo $achievements['savings_champion'] ? 'unlocked' : ''; ?>">
                            <div class="achievement-icon">
                                <i class="fas fa-piggy-bank"></i>
                            </div>
                            <h6>Savings Champion</h6>
                            <p>Saved over $10,000 across all goals</p>
                        </div>
                        <div class="achievement-item <?php echo $achievements['early_bird'] ? 'unlocked' : ''; ?>">
                            <div class="achievement-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h6>Early Bird</h6>
                            <p>Completed a goal before its target date</p>
                        </div>
                        <div class="achievement-item <?php echo $achievements['consistency_king'] ? 'unlocked' : ''; ?>">
                            <div class="achievement-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h6>Consistency King</h6>
                            <p>Completed 3 or more goals</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Milestones Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-flag"></i> Current Milestones
                    </h5>
                    <?php if (empty($milestones)): ?>
                        <p class="text-center">No active milestones</p>
                    <?php else: ?>
                        <div class="milestones-list">
                            <?php foreach ($milestones as $milestone): ?>
                                <div class="milestone-item">
                                    <div class="milestone-header">
                                        <h6><?php echo htmlspecialchars($milestone['title']); ?></h6>
                                        <span class="badge bg-primary"><?php echo $milestone['category']; ?></span>
                                    </div>
                                    <div class="milestone-progress">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo $milestone['progress']; ?>%">
                                                <?php echo number_format($milestone['progress'], 1); ?>%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="milestone-footer">
                                        <small><?php echo $milestone['days_remaining']; ?> days remaining</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.achievements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.achievement-item {
    text-align: center;
    padding: 1rem;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.achievement-item.unlocked {
    background: #e3f2fd;
    border: 2px solid #2196f3;
}

.achievement-item:not(.unlocked) {
    opacity: 0.6;
}

.achievement-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #2196f3;
}

.achievement-item h6 {
    margin-bottom: 0.5rem;
    color: #333;
}

.achievement-item p {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0;
}

.milestones-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.milestone-item {
    padding: 1rem;
    border-radius: 8px;
    background: #f8f9fa;
    transition: transform 0.2s;
}

.milestone-item:hover {
    transform: translateY(-2px);
}

.milestone-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.milestone-progress {
    margin: 0.5rem 0;
}

.milestone-footer {
    display: flex;
    justify-content: flex-end;
}

.progress {
    height: 1rem;
    border-radius: 0.5rem;
}

.progress-bar {
    background-color: #2196f3;
    border-radius: 0.5rem;
    color: white;
    font-size: 0.8rem;
    line-height: 1rem;
    text-align: center;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}
</style>

<?php include 'includes/footer.php'; ?> 