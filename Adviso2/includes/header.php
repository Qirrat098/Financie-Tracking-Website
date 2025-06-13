<?php
// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Adviso</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<header class="main-header">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chart-line"></i>
                <span>Adviso</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'financial-literacy.php' ? 'active' : ''; ?>" href="financial-literacy.php">
                            <i class="fas fa-book"></i> Financial Literacy
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'tax-solutions.php' ? 'active' : ''; ?>" href="tax-solutions.php">
                            <i class="fas fa-calculator"></i> Tax Solutions
                        </a>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-tools"></i> Financial Tools
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item <?php echo $current_page === 'financial-goals.php' ? 'active' : ''; ?>" href="financial-goals.php">
                                    <i class="fas fa-bullseye"></i> Financial Goals
                                </a></li>
                                <li><a class="dropdown-item <?php echo $current_page === 'budget.php' ? 'active' : ''; ?>" href="budget.php">
                                    <i class="fas fa-wallet"></i> Budget
                                </a></li>
                                <li><a class="dropdown-item <?php echo $current_page === 'financial-recommendations.php' ? 'active' : ''; ?>" href="financial-recommendations.php">
                                    <i class="fas fa-lightbulb"></i> Recommendations
                                </a></li>
                                <li><a class="dropdown-item <?php echo $current_page === 'calculators.php' ? 'active' : ''; ?>" href="calculators.php">
                                    <i class="fas fa-calculator"></i> Calculators
                                </a></li>
                                <li><a class="dropdown-item <?php echo $current_page === 'achievements.php' ? 'active' : ''; ?>" href="achievements.php">
                                    <i class="fas fa-trophy"></i> Achievements
                                </a></li>
                                <li><a class="dropdown-item <?php echo $current_page === 'progress-tracking.php' ? 'active' : ''; ?>" href="progress-tracking.php">
                                    <i class="fas fa-chart-line"></i> Progress Tracking
                                </a></li>
                                <li><a class="dropdown-item <?php echo $current_page === 'progress-history.php' ? 'active' : ''; ?>" href="progress-history.php">
                                    <i class="fas fa-history"></i> Progress History
                                </a></li>
                            </ul>
                        </li>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-shield"></i> Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item <?php echo $current_page === 'manage-users.php' ? 'active' : ''; ?>" href="manage-users.php">
                                            <i class="fas fa-users"></i> Manage Users
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?php echo $current_page === 'moderate-article.php' ? 'active' : ''; ?>" href="moderate-article.php">
                                            <i class="fas fa-newspaper"></i> Moderate Articles
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item <?php echo $current_page === 'admin-dashboard.php' ? 'active' : ''; ?>" href="admin-dashboard.php">
                                            <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'my-articles.php' ? 'active' : ''; ?>" href="my-articles.php">
                                <i class="fas fa-newspaper"></i> My Articles
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-link nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i>
                                <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary me-2">Login</a>
                        <a href="register.php" class="btn btn-outline-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<style>
.main-header {
    background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
}

.navbar {
    padding: 0.5rem 1rem;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #fff;
    font-size: 1.4rem;
    font-weight: 600;
    text-decoration: none;
}

.navbar-brand i {
    font-size: 1.6rem;
    color: #64b5f6;
}

.nav-link {
    color: #fff !important;
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
}

.nav-link.active {
    background: rgba(255, 255, 255, 0.2);
}

.dropdown-menu {
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    background: #fff;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #333;
}

.dropdown-item:hover {
    background: #f8f9fa;
}

.dropdown-item.active {
    background: #e9ecef;
    color: #0d6efd;
}

.btn-link {
    color: #fff !important;
    text-decoration: none;
}

.btn-link:hover {
    color: #fff !important;
    opacity: 0.9;
}

/* Add margin to body to prevent content from hiding under fixed header */
body {
    margin-top: 70px;
}

@media (max-width: 991px) {
    .navbar-collapse {
        background: #1a237e;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 0.5rem;
    }
    
    .nav-link {
        padding: 0.75rem 1rem;
    }
    
    .dropdown-menu {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        margin-top: 0;
    }
    
    .dropdown-item {
        color: #fff;
    }
    
    .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.1);
    }
}
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable all dropdowns
    var dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            var dropdownMenu = this.nextElementSibling;
            dropdownMenu.classList.toggle('show');
            this.setAttribute('aria-expanded', dropdownMenu.classList.contains('show'));
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.dropdown-toggle')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(dropdown) {
                dropdown.classList.remove('show');
            });
            document.querySelectorAll('.dropdown-toggle[aria-expanded="true"]').forEach(function(toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            });
        }
    });
});
</script>
</body>
</html> 