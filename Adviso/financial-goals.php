<?php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's financial goals
$stmt = $conn->prepare("
    SELECT * FROM financial_goals 
    WHERE user_id = ? 
    ORDER BY target_date ASC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$goals = $result->fetch_all(MYSQLI_ASSOC);

$page_title = "Financial Goals";
include 'includes/header.php';
?>

<div class="container">
    <div class="goals-header">
        <h1>Financial Goals</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGoalModal">
            <i class="fas fa-plus"></i> Create New Goal
        </button>
    </div>

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

    <?php if (empty($goals)): ?>
        <div class="no-goals">
            <p>You haven't set any financial goals yet. Start by creating your first goal!</p>
        </div>
    <?php else: ?>
        <div class="goals-grid">
            <?php foreach ($goals as $goal): ?>
                <div class="goal-card">
                    <div class="goal-header">
                        <h3><?php echo htmlspecialchars($goal['title']); ?></h3>
                        <span class="category-badge"><?php echo htmlspecialchars($goal['category']); ?></span>
                    </div>
                    
                    <div class="goal-progress">
                        <?php 
                        $progress = ($goal['current_amount'] / $goal['target_amount']) * 100;
                        $progress = min($progress, 100); // Cap at 100%
                        ?>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <div class="progress-text">
                            $<?php echo number_format($goal['current_amount'], 2); ?> of $<?php echo number_format($goal['target_amount'], 2); ?>
                        </div>
                    </div>

                    <div class="goal-details">
                        <div class="goal-info">
                            <p><strong>Target Date:</strong> <?php echo date('F j, Y', strtotime($goal['target_date'])); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($goal['description']); ?></p>
                        </div>
                        <div class="goal-actions">
                            <button class="btn btn-sm btn-primary update-progress" 
                                    data-goal-id="<?php echo $goal['id']; ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#updateProgressModal">
                                Update Progress
                            </button>
                            <button class="btn btn-sm btn-danger delete-goal"
                                    data-goal-id="<?php echo $goal['id']; ?>"
                                    data-goal-title="<?php echo htmlspecialchars($goal['title']); ?>">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create Goal Modal -->
<div class="modal fade" id="createGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Financial Goal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="create-goal.php" method="POST" id="createGoalForm">
                    <div class="form-group">
                        <label for="title">Goal Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select a category</option>
                            <option value="savings">Savings</option>
                            <option value="investment">Investment</option>
                            <option value="purchase">Major Purchase</option>
                            <option value="debt">Debt Repayment</option>
                            <option value="emergency">Emergency Fund</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="target_amount">Target Amount ($)</label>
                        <input type="number" id="target_amount" name="target_amount" class="form-control" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="current_amount">Current Amount ($)</label>
                        <input type="number" id="current_amount" name="current_amount" class="form-control" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="target_date">Target Date</label>
                        <input type="date" id="target_date" name="target_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="createGoalForm" class="btn btn-primary">Create Goal</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Progress Modal -->
<div class="modal fade" id="updateProgressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Goal Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="update-goal-progress.php" method="POST" id="updateProgressForm">
                    <input type="hidden" id="goal_id" name="goal_id">
                    <div class="form-group">
                        <label for="new_amount">Current Amount ($)</label>
                        <input type="number" id="new_amount" name="new_amount" class="form-control" min="0" step="0.01" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="updateProgressForm" class="btn btn-primary">Update Progress</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Goal Confirmation Modal -->
<div class="modal fade" id="deleteGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Goal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this goal? This action cannot be undone.</p>
                <form action="delete-goal.php" method="POST" id="deleteGoalForm">
                    <input type="hidden" id="delete_goal_id" name="goal_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deleteGoalForm" class="btn btn-danger">Delete Goal</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for target date input to today
    const targetDateInput = document.getElementById('target_date');
    const today = new Date().toISOString().split('T')[0];
    targetDateInput.min = today;

    // Handle update progress button clicks
    document.querySelectorAll('.update-progress').forEach(button => {
        button.addEventListener('click', function() {
            const goalId = this.dataset.goalId;
            document.getElementById('goal_id').value = goalId;
        });
    });

    // Handle delete goal button clicks
    document.querySelectorAll('.delete-goal').forEach(button => {
        button.addEventListener('click', function() {
            const goalId = this.dataset.goalId;
            const goalTitle = this.dataset.goalTitle;
            document.getElementById('delete_goal_id').value = goalId;
            document.querySelector('#deleteGoalModal .modal-body p').textContent = 
                `Are you sure you want to delete the goal "${goalTitle}"? This action cannot be undone.`;
            new bootstrap.Modal(document.getElementById('deleteGoalModal')).show();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 