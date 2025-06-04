<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's tax records
$tax_query = "SELECT * FROM tax_records WHERE user_id = ? ORDER BY tax_year DESC";
$stmt = $conn->prepare($tax_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$tax_records = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Solutions - Adviso</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <div class="tax-solutions">
            <h1>Tax Solutions</h1>
            
            <!-- Tax Calculator Section -->
            <section class="tax-calculator">
                <h2>Tax Calculator</h2>
                <div class="calculator-container">
                    <form id="taxCalculatorForm" class="calculator-form">
                        <div class="form-group">
                            <label for="income">Annual Income (₹)</label>
                            <input type="number" id="income" name="income" required min="0" step="1000">
                        </div>
                        
                        <div class="form-group">
                            <label for="deductions">Total Deductions (₹)</label>
                            <input type="number" id="deductions" name="deductions" min="0" step="1000">
                        </div>
                        
                        <div class="form-group">
                            <label for="taxYear">Tax Year</label>
                            <select id="taxYear" name="taxYear" required>
                                <option value="2024">2024-25</option>
                                <option value="2023">2023-24</option>
                                <option value="2022">2022-23</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Calculate Tax</button>
                    </form>
                    
                    <div class="calculator-results" style="display: none;">
                        <h3>Tax Calculation Results</h3>
                        <div class="results-grid">
                            <div class="result-item">
                                <span class="result-label">Taxable Income</span>
                                <span class="result-value" id="taxableIncome">₹0</span>
                            </div>
                            <div class="result-item">
                                <span class="result-label">Total Tax</span>
                                <span class="result-value" id="totalTax">₹0</span>
                            </div>
                            <div class="result-item">
                                <span class="result-label">Effective Tax Rate</span>
                                <span class="result-value" id="effectiveRate">0%</span>
                            </div>
                        </div>
                        <div class="tax-breakdown">
                            <h4>Tax Breakdown</h4>
                            <canvas id="taxBreakdownChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Tax Planning Tools -->
            <section class="tax-planning">
                <h2>Tax Planning Tools</h2>
                <div class="tools-grid">
                    <div class="tool-card">
                        <h3>Deduction Optimizer</h3>
                        <p>Maximize your tax savings by optimizing your deductions.</p>
                        <a href="#" class="btn btn-secondary">Get Started</a>
                    </div>
                    
                    <div class="tool-card">
                        <h3>Investment Tax Calculator</h3>
                        <p>Calculate tax implications of different investment options.</p>
                        <a href="#" class="btn btn-secondary">Calculate</a>
                    </div>
                    
                    <div class="tool-card">
                        <h3>Retirement Tax Planning</h3>
                        <p>Plan your retirement with tax-efficient strategies.</p>
                        <a href="#" class="btn btn-secondary">Learn More</a>
                    </div>
                </div>
            </section>
            
            <!-- Tax Records -->
            <section class="tax-records">
                <h2>Your Tax Records</h2>
                <?php if ($tax_records->num_rows > 0): ?>
                    <div class="records-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tax Year</th>
                                    <th>Total Income</th>
                                    <th>Total Tax</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($record = $tax_records->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['tax_year']); ?></td>
                                        <td>₹<?php echo number_format($record['total_income'], 2); ?></td>
                                        <td>₹<?php echo number_format($record['total_tax'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower($record['status']); ?>">
                                                <?php echo htmlspecialchars($record['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="view-tax-record.php?id=<?php echo $record['id']; ?>" class="btn btn-small">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-records">
                        <p>No tax records found. Start by calculating your tax using the calculator above.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <script>
        // Tax Calculator JavaScript
        document.getElementById('taxCalculatorForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const income = parseFloat(document.getElementById('income').value);
            const deductions = parseFloat(document.getElementById('deductions').value) || 0;
            const taxableIncome = income - deductions;
            
            // Tax slabs for 2024-25 (example)
            const taxSlabs = [
                { limit: 300000, rate: 0 },
                { limit: 600000, rate: 0.05 },
                { limit: 900000, rate: 0.10 },
                { limit: 1200000, rate: 0.15 },
                { limit: 1500000, rate: 0.20 },
                { limit: Infinity, rate: 0.30 }
            ];
            
            let totalTax = 0;
            let remainingIncome = taxableIncome;
            let previousLimit = 0;
            
            // Calculate tax for each slab
            taxSlabs.forEach(slab => {
                const taxableInSlab = Math.min(remainingIncome, slab.limit - previousLimit);
                if (taxableInSlab > 0) {
                    totalTax += taxableInSlab * slab.rate;
                    remainingIncome -= taxableInSlab;
                }
                previousLimit = slab.limit;
            });
            
            // Update results
            document.getElementById('taxableIncome').textContent = '₹' + taxableIncome.toLocaleString();
            document.getElementById('totalTax').textContent = '₹' + totalTax.toLocaleString();
            document.getElementById('effectiveRate').textContent = 
                ((totalTax / income) * 100).toFixed(2) + '%';
            
            // Show results
            document.querySelector('.calculator-results').style.display = 'block';
            
            // Create tax breakdown chart
            const ctx = document.getElementById('taxBreakdownChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Tax Amount', 'Net Income'],
                    datasets: [{
                        data: [totalTax, income - totalTax],
                        backgroundColor: ['#dc3545', '#28a745']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html> 