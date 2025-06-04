<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$page_title = "Manage Users";
include 'includes/header.php';

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    if ($action === 'make_admin') {
        $update_stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $update_stmt->bind_param("i", $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "User role updated to admin successfully.";
        } else {
            $error_message = "Error updating user role: " . $conn->error;
        }
    } elseif ($action === 'remove_admin') {
        // Prevent removing the last admin
        $check_stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
        $check_stmt->execute();
        $admin_count = $check_stmt->get_result()->fetch_assoc()['admin_count'];
        
        if ($admin_count > 1) {
            $update_stmt = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
            $update_stmt->bind_param("i", $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "User role updated to regular user successfully.";
            } else {
                $error_message = "Error updating user role: " . $conn->error;
            }
        } else {
            $error_message = "Cannot remove the last admin user.";
        }
    }
}

// Get all users
$users_stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(DISTINCT a.id) as article_count,
           COUNT(DISTINCT g.id) as goal_count
    FROM users u
    LEFT JOIN articles a ON u.id = a.author_id
    LEFT JOIN financial_goals g ON u.id = g.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users_stmt->execute();
$users_result = $users_stmt->get_result();
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h1>Manage Users</h1>
            <p class="text-muted">Manage user roles and permissions</p>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Articles</th>
                                    <th>Goals</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['article_count']; ?></td>
                                        <td><?php echo $user['goal_count']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <?php if ($user['role'] === 'admin'): ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove admin privileges from this user?');">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="action" value="remove_admin">
                                                        <button type="submit" class="btn btn-warning btn-sm">
                                                            Remove Admin
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to make this user an admin?');">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="action" value="make_admin">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            Make Admin
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">Current User</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    background-color: #f8f9fa;
}
.badge {
    font-size: 0.85em;
    padding: 0.5em 0.75em;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<?php include 'includes/footer.php'; ?> 