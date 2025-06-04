<?php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's budget categories
$stmt = $conn->prepare("
    SELECT DISTINCT category 
    FROM transactions 
    WHERE user_id = ? 
    ORDER BY category
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$categories = $result->fetch_all(MYSQLI_ASSOC);

// Get current month's transactions
$current_month = date('Y-m');
$stmt = $conn->prepare("
    SELECT 
        category,
        type,
        SUM(amount) as total_amount
    FROM transactions 
    WHERE user_id = ? 
    AND DATE_FORMAT(date, '%Y-%m') = ?
    GROUP BY category, type
");
$stmt->bind_param("is", $_SESSION['user_id'], $current_month);
$stmt->execute();
$result = $stmt->get_result();
$monthly_totals = $result->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$total_income = 0;
$total_expenses = 0;
$category_totals = [];

foreach ($monthly_totals as $total) {
    if ($total['type'] === 'income') {
        $total_income += $total['total_amount'];
    } else {
        $total_expenses += $total['total_amount'];
        $category_totals[$total['category']] = $total['total_amount'];
    }
}

$page_title = "Budget Planning";
include 'includes/header.php';
?>

<div class="container">
    <div class="budget-header">
        <h1>Budget Planning</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
            <i class="fas fa-plus"></i> Add Transaction
        </button>
    </div>

    <div class="budget-overview">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Income</h5>
                        <p class="card-text amount income">$<?php echo number_format($total_income, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Expenses</h5>
                        <p class="card-text amount expense">$<?php echo number_format($total_expenses, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Net Balance</h5>
                        <p class="card-text amount <?php echo ($total_income - $total_expenses) >= 0 ? 'income' : 'expense'; ?>">
                            $<?php echo number_format($total_income - $total_expenses, 2); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="budget-categories">
        <h2>Category Breakdown</h2>
        <div class="row">
            <?php foreach ($category_totals as $category => $amount): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo ucfirst($category); ?></h5>
                            <p class="card-text amount expense">$<?php echo number_format($amount, 2); ?></p>
                            <div class="progress">
                                <div class="progress-bar bg-danger" 
                                     role="progressbar" 
                                     style="width: <?php echo ($amount / $total_expenses) * 100; ?>%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="add-transaction.php" method="POST" id="addTransactionForm">
                    <div class="form-group mb-3">
                        <label for="type">Transaction Type</label>
                        <select id="type" name="type" class="form-control" required>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="amount">Amount ($)</label>
                        <input type="number" id="amount" name="amount" class="form-control" min="0" step="0.01" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['category']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($category['category'])); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description" class="form-control">
                    </div>

                    <div class="form-group mb-3">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addTransactionForm" class="btn btn-primary">Add Transaction</button>
            </div>
        </div>
    </div>
</div>

<style>
.budget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.budget-overview {
    margin-bottom: 40px;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.amount {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 10px 0;
}

.amount.income {
    color: #28a745;
}

.amount.expense {
    color: #dc3545;
}

.budget-categories {
    margin-top: 40px;
}

.budget-categories h2 {
    margin-bottom: 20px;
}

.progress {
    height: 8px;
    margin-top: 10px;
}

.progress-bar {
    transition: width 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default date to today
    document.getElementById('date').valueAsDate = new Date();
    
    // Handle category selection
    const categorySelect = document.getElementById('category');
    categorySelect.addEventListener('change', function() {
        if (this.value === 'other') {
            const newCategory = prompt('Enter new category name:');
            if (newCategory) {
                const option = new Option(newCategory, newCategory);
                categorySelect.add(option);
                categorySelect.value = newCategory;
            } else {
                this.value = '';
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 