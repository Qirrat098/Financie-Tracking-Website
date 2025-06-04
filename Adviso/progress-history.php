<?php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's progress history
$stmt = $conn->prepare("
    SELECT 
        g.*,
        (g.current_amount / g.target_amount * 100) as progress_percentage,
        DATEDIFF(g.target_date, CURDATE()) as days_remaining,
        DATE_FORMAT(g.created_at, '%Y-%m-%d') as start_date,
        CASE 
            WHEN g.current_amount >= g.target_amount THEN DATE_FORMAT(g.updated_at, '%Y-%m-%d')
            ELSE NULL
        END as completion_date
    FROM financial_goals g
    WHERE g.user_id = ?
    ORDER BY g.created_at DESC
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

// Calculate statistics
$total_goals = count($goals);
$completed_goals = 0;
$total_saved = 0;
$average_completion_time = 0;
$completion_times = [];

foreach ($goals as $goal) {
    $total_saved += $goal['current_amount'];
    if ($goal['progress_percentage'] >= 100) {
        $completed_goals++;
        if ($goal['completion_date']) {
            $start = new DateTime($goal['start_date']);
            $end = new DateTime($goal['completion_date']);
            $completion_times[] = $start->diff($end)->days;
        }
    }
}

if (!empty($completion_times)) {
    $average_completion_time = array_sum($completion_times) / count($completion_times);
}

// Prepare data for charts
$monthly_progress = [];
$category_distribution = [];

foreach ($goals as $goal) {
    $month = date('Y-m', strtotime($goal['created_at']));
    if (!isset($monthly_progress[$month])) {
        $monthly_progress[$month] = 0;
    }
    $monthly_progress[$month] += $goal['current_amount'];

    if (!isset($category_distribution[$goal['category']])) {
        $category_distribution[$goal['category']] = 0;
    }
    $category_distribution[$goal['category']] += $goal['current_amount'];
}

$page_title = "Progress History";
include 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Progress History</h1>

    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-bullseye"></i> Total Goals
                    </h5>
                    <h2 class="mb-0"><?php echo $total_goals; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-check-circle"></i> Completed Goals
                    </h5>
                    <h2 class="mb-0"><?php echo $completed_goals; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-dollar-sign"></i> Total Saved
                    </h5>
                    <h2 class="mb-0">$<?php echo number_format($total_saved, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-clock"></i> Avg. Completion Time
                    </h5>
                    <h2 class="mb-0"><?php echo round($average_completion_time); ?> days</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Timeline -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-history"></i> Progress Timeline
                    </h5>
                    <div class="timeline">
                        <?php foreach ($goals as $goal): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker <?php echo $goal['progress_percentage'] >= 100 ? 'completed' : ''; ?>"></div>
                                <div class="timeline-content">
                                    <h6><?php echo htmlspecialchars($goal['title']); ?></h6>
                                    <div class="timeline-details">
                                        <span class="badge bg-primary"><?php echo $goal['category']; ?></span>
                                        <span class="date">Started: <?php echo date('M d, Y', strtotime($goal['start_date'])); ?></span>
                                        <?php if ($goal['completion_date']): ?>
                                            <span class="date">Completed: <?php echo date('M d, Y', strtotime($goal['completion_date'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo $goal['progress_percentage']; ?>%">
                                            <?php echo number_format($goal['progress_percentage'], 1); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-chart-line"></i> Monthly Progress
                    </h5>
                    <canvas id="monthlyProgressChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-chart-pie"></i> Category Distribution
                    </h5>
                    <canvas id="categoryDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 15px;
    height: 100%;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: 11px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #6c757d;
    border: 2px solid #fff;
}

.timeline-marker.completed {
    background: #28a745;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.timeline-details {
    display: flex;
    gap: 10px;
    margin: 5px 0;
    flex-wrap: wrap;
}

.timeline-details .date {
    color: #6c757d;
    font-size: 0.9rem;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
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
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Progress Chart
    const monthlyCtx = document.getElementById('monthlyProgressChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($monthly_progress)); ?>,
            datasets: [{
                label: 'Monthly Savings',
                data: <?php echo json_encode(array_values($monthly_progress)); ?>,
                borderColor: '#2196f3',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Category Distribution Chart
    const categoryCtx = document.getElementById('categoryDistributionChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($category_distribution)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($category_distribution)); ?>,
                backgroundColor: [
                    '#2196f3',
                    '#4caf50',
                    '#ff9800',
                    '#f44336',
                    '#9c27b0'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 