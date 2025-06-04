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

// Get user's recent transactions
$stmt = $conn->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? 
    ORDER BY date DESC 
    LIMIT 10
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);

// Generate recommendations based on goals and transactions
$recommendations = [];

// Analyze goals progress
foreach ($goals as $goal) {
    $progress = ($goal['current_amount'] / $goal['target_amount']) * 100;
    $days_remaining = (strtotime($goal['target_date']) - time()) / (60 * 60 * 24);
    
    if ($progress < 50 && $days_remaining < 30) {
        $recommendations[] = [
            'type' => 'warning',
            'title' => 'Goal Progress Alert',
            'message' => "Your goal '{$goal['title']}' is less than 50% complete with less than 30 days remaining. Consider adjusting your target date or increasing your savings rate."
        ];
    }
    
    if ($progress >= 100) {
        $recommendations[] = [
            'type' => 'success',
            'title' => 'Goal Achieved!',
            'message' => "Congratulations! You've achieved your goal '{$goal['title']}'. Consider setting a new goal to continue your financial journey."
        ];
    }
}

// Analyze spending patterns
$total_spending = 0;
$category_spending = [];
foreach ($transactions as $transaction) {
    if ($transaction['type'] === 'expense') {
        $total_spending += $transaction['amount'];
        $category = $transaction['category'];
        if (!isset($category_spending[$category])) {
            $category_spending[$category] = 0;
        }
        $category_spending[$category] += $transaction['amount'];
    }
}

// Identify high spending categories
foreach ($category_spending as $category => $amount) {
    if ($amount > ($total_spending * 0.3)) { // If category is more than 30% of total spending
        $recommendations[] = [
            'type' => 'info',
            'title' => 'Spending Pattern',
            'message' => "Your spending in the {$category} category is significantly high. Consider reviewing your expenses in this area."
        ];
    }
}

// Add general recommendations
$recommendations[] = [
    'type' => 'info',
    'title' => 'Emergency Fund',
    'message' => "Make sure you have an emergency fund that covers 3-6 months of expenses."
];

$recommendations[] = [
    'type' => 'info',
    'title' => 'Investment Diversification',
    'message' => "Consider diversifying your investments across different asset classes to manage risk."
];

$page_title = "Financial Recommendations";
include 'includes/header.php';
?>

<div class="container">
    <div class="recommendations-header">
        <h1>Financial Recommendations</h1>
        <p class="lead">Personalized suggestions to help you achieve your financial goals</p>
    </div>

    <div class="recommendations-grid">
        <?php foreach ($recommendations as $recommendation): ?>
            <div class="recommendation-card <?php echo $recommendation['type']; ?>">
                <div class="recommendation-header">
                    <h3><?php echo htmlspecialchars($recommendation['title']); ?></h3>
                </div>
                <div class="recommendation-body">
                    <p><?php echo htmlspecialchars($recommendation['message']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="market-insights">
        <h2>Market Insights</h2>
        <div class="insights-grid">
            <div class="insight-card">
                <h3>Economic Outlook</h3>
                <p>Stay informed about current market trends and economic indicators that may affect your financial decisions.</p>
                <a href="#" class="btn btn-outline-primary">Learn More</a>
            </div>
            <div class="insight-card">
                <h3>Investment Opportunities</h3>
                <p>Discover potential investment opportunities based on your risk profile and financial goals.</p>
                <a href="#" class="btn btn-outline-primary">Explore</a>
            </div>
            <div class="insight-card">
                <h3>Tax Planning</h3>
                <p>Get insights on tax-saving strategies and investment options that can help reduce your tax burden.</p>
                <a href="#" class="btn btn-outline-primary">View Strategies</a>
            </div>
        </div>
    </div>
</div>

<style>
.recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.recommendation-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    transition: transform 0.2s;
}

.recommendation-card:hover {
    transform: translateY(-5px);
}

.recommendation-card.warning {
    border-left: 4px solid #ffc107;
}

.recommendation-card.success {
    border-left: 4px solid #28a745;
}

.recommendation-card.info {
    border-left: 4px solid #17a2b8;
}

.recommendation-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.2rem;
}

.recommendation-body p {
    margin: 10px 0 0;
    color: #666;
}

.market-insights {
    margin-top: 40px;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.insight-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.insight-card h3 {
    color: #333;
    margin-bottom: 15px;
}

.insight-card p {
    color: #666;
    margin-bottom: 20px;
}

.recommendations-header {
    text-align: center;
    margin-bottom: 30px;
}

.recommendations-header h1 {
    color: #333;
    margin-bottom: 10px;
}

.recommendations-header .lead {
    color: #666;
    font-size: 1.1rem;
}
</style>

<?php include 'includes/footer.php'; ?> 