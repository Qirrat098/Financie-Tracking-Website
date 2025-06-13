<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get tax record ID from URL
$record_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($record_id <= 0) {
    header('Location: tax-solutions.php');
    exit();
}

// Get tax record details
$record_query = "SELECT tr.*, u.name as user_name 
                FROM tax_records tr 
                JOIN users u ON tr.user_id = u.id 
                WHERE tr.id = ? AND tr.user_id = ?";
$stmt = $conn->prepare($record_query);
$stmt->bind_param("ii", $record_id, $_SESSION['user_id']);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();

if (!$record) {
    header('Location: tax-solutions.php');
    exit();
}

// Get income sources for this tax year
$income_query = "SELECT * FROM transactions 
                WHERE user_id = ? 
                AND type = 'income' 
                AND YEAR(transaction_date) = ?";
$stmt = $conn->prepare($income_query);
$year = substr($record['tax_year'], 0, 4);
$stmt->bind_param("is", $_SESSION['user_id'], $year);
$stmt->execute();
$income_sources = $stmt->get_result();

// Get deductions for this tax year
$deductions_query = "SELECT * FROM transactions 
                    WHERE user_id = ? 
                    AND type = 'expense' 
                    AND category IN ('tax_deduction', 'investment', 'insurance')
                    AND YEAR(transaction_date) = ?";
$stmt = $conn->prepare($deductions_query);
$stmt->bind_param("is", $_SESSION['user_id'], $year);
$stmt->execute();
$deductions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Record Details - Adviso</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <div class="tax-record-detail">
            <div class="page-header">
                <h1>Tax Record Details</h1>
                <a href="tax-solutions.php" class="btn btn-secondary">Back to Tax Solutions</a>
            </div>

            <!-- Tax Year Summary -->
            <section class="tax-summary">
                <div class="summary-header">
                    <h2>Tax Year <?php echo htmlspecialchars($record['tax_year']); ?></h2>
                    <span class="status-badge <?php echo strtolower($record['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($record['status'])); ?>
                    </span>
                </div>

                <div class="summary-grid">
                    <div class="summary-card">
                        <h3>Total Income</h3>
                        <p class="amount">₹<?php echo number_format($record['total_income'], 2); ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Total Deductions</h3>
                        <p class="amount">₹<?php echo number_format($record['total_deductions'], 2); ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Taxable Income</h3>
                        <p class="amount">₹<?php echo number_format($record['total_income'] - $record['total_deductions'], 2); ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Total Tax</h3>
                        <p class="amount">₹<?php echo number_format($record['total_tax'], 2); ?></p>
                    </div>
                </div>

                <?php if ($record['filing_date']): ?>
                    <div class="filing-info">
                        <p>Filed on: <?php echo date('F d, Y', strtotime($record['filing_date'])); ?></p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Income Sources -->
            <section class="income-sources">
                <h2>Income Sources</h2>
                <?php if ($income_sources->num_rows > 0): ?>
                    <div class="sources-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($income = $income_sources->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($income['transaction_date'])); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($income['category'])); ?></td>
                                        <td><?php echo htmlspecialchars($income['description']); ?></td>
                                        <td>₹<?php echo number_format($income['amount'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-data">No income sources recorded for this tax year.</p>
                <?php endif; ?>
            </section>

            <!-- Deductions -->
            <section class="deductions">
                <h2>Tax Deductions</h2>
                <?php if ($deductions->num_rows > 0): ?>
                    <div class="deductions-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($deduction = $deductions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($deduction['transaction_date'])); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($deduction['category'])); ?></td>
                                        <td><?php echo htmlspecialchars($deduction['description']); ?></td>
                                        <td>₹<?php echo number_format($deduction['amount'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-data">No deductions recorded for this tax year.</p>
                <?php endif; ?>
            </section>

            <!-- Tax Breakdown Chart -->
            <section class="tax-breakdown">
                <h2>Tax Breakdown</h2>
                <div class="chart-container">
                    <canvas id="taxBreakdownChart"></canvas>
                </div>
            </section>

            <!-- Tax Saving Recommendations -->
            <section class="tax-recommendations">
                <h2>Tax Saving Recommendations</h2>
                <div class="recommendations-grid">
                    <div class="recommendation-card">
                        <h3>Investment Opportunities</h3>
                        <p>Consider investing in tax-saving instruments like ELSS, PPF, or NPS to reduce your taxable income.</p>
                    </div>
                    <div class="recommendation-card">
                        <h3>Insurance Premiums</h3>
                        <p>Review your insurance coverage and consider increasing your health insurance premium for additional tax benefits.</p>
                    </div>
                    <div class="recommendation-card">
                        <h3>Home Loan Benefits</h3>
                        <p>If you have a home loan, ensure you're claiming all eligible deductions for principal and interest payments.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Tax Breakdown Chart
        const ctx = document.getElementById('taxBreakdownChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Income Tax', 'Net Income'],
                datasets: [{
                    data: [
                        <?php echo $record['total_tax']; ?>,
                        <?php echo $record['total_income'] - $record['total_tax']; ?>
                    ],
                    backgroundColor: ['#dc3545', '#28a745']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Tax vs Net Income Distribution'
                    }
                }
            }
        });
    </script>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html> 