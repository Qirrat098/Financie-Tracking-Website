<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adviso - Financial Literacy & Planning</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php
    session_start();
    include 'includes/config.php';
    include 'includes/header.php';
    ?>

    <main class="container">
        <section class="hero">
            <h1>Welcome to Adviso</h1>
            <p>Your comprehensive financial literacy and planning platform</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="cta-buttons">
                    <a href="login.php" class="btn btn-primary">Login</a>
                    <a href="register.php" class="btn btn-secondary">Register</a>
                </div>
            <?php endif; ?>
        </section>

        <section class="features">
            <h2>Key Features</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>Financial Literacy Hub</h3>
                    <p>Access articles, tutorials, and guides on finance, taxes, and budgeting.</p>
                </div>
                <div class="feature-card">
                    <h3>Tax Solutions</h3>
                    <p>Interactive tax-saving strategies and planning tools.</p>
                </div>
                <div class="feature-card">
                    <h3>Financial Planning</h3>
                    <p>Personalized financial planning and goal tracking.</p>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html> 