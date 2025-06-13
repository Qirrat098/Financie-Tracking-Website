<?php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's goals with progress
$stmt = $conn->prepare("
    SELECT 
        g.*,
        (g.saved_amount / g.target_amount * 100) as progress_percentage,
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

$page_title = "Progress Tracking";
include 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Progress Tracking</h1>

    <div class="row">
        <?php foreach ($goals as $goal): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($goal['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($goal['description']); ?></p>
                        
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?php echo min($goal['progress_percentage'], 100); ?>%"
                                 aria-valuenow="<?php echo $goal['progress_percentage']; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo number_format($goal['progress_percentage'], 1); ?>%
                            </div>
                        </div>

                        <div class="goal-details">
                            <p><strong>Target Amount:</strong> $<?php echo number_format($goal['target_amount'], 2); ?></p>
                            <p><strong>Current Amount:</strong> $<?php echo number_format($goal['saved_amount'], 2); ?></p>
                            <p><strong>Target Date:</strong> <?php echo date('F j, Y', strtotime($goal['target_date'])); ?></p>
                            <p><strong>Days Remaining:</strong> <?php echo $goal['days_remaining']; ?></p>
                        </div>

                        <div class="goal-actions mt-3">
                            <button class="btn btn-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#updateProgressModal"
                                    data-goal-id="<?php echo $goal['id']; ?>"
                                    data-goal-title="<?php echo htmlspecialchars($goal['title']); ?>"
                                    data-current-amount="<?php echo $goal['saved_amount']; ?>">
                                Update Progress
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Update Progress Modal -->
<div class="modal fade" id="updateProgressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="update-goal-progress.php" method="POST">
                    <input type="hidden" name="goal_id" id="goal_id">
                    <div class="mb-3">
                        <label for="saved_amount" class="form-label">Current Amount</label>
                        <input type="number" class="form-control" id="saved_amount" name="saved_amount" step="0.01" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateProgressModal = document.getElementById('updateProgressModal');
    if (updateProgressModal) {
        updateProgressModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const goalId = button.getAttribute('data-goal-id');
            const goalTitle = button.getAttribute('data-goal-title');
            const currentAmount = button.getAttribute('data-current-amount');
            
            const modalTitle = updateProgressModal.querySelector('.modal-title');
            const goalIdInput = updateProgressModal.querySelector('#goal_id');
            const savedAmountInput = updateProgressModal.querySelector('#saved_amount');
            
            modalTitle.textContent = `Update Progress - ${goalTitle}`;
            goalIdInput.value = goalId;
            savedAmountInput.value = currentAmount;
        });
    }
});
</script>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.progress {
    height: 1.5rem;
}

.progress-bar {
    background-color: #2196f3;
    color: white;
    font-weight: bold;
    text-align: center;
    line-height: 1.5rem;
}

.goal-details {
    margin-top: 1rem;
}

.goal-details p {
    margin-bottom: 0.5rem;
}

.goal-actions {
    display: flex;
    justify-content: flex-end;
}
</style>

<?php include 'includes/footer.php'; ?> 