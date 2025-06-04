<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = "Settings";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="nav flex-column nav-pills" role="tablist">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#account" type="button">
                            <i class="fas fa-user me-2"></i> Account Settings
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#notifications" type="button">
                            <i class="fas fa-bell me-2"></i> Notifications
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#privacy" type="button">
                            <i class="fas fa-shield-alt me-2"></i> Privacy
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#preferences" type="button">
                            <i class="fas fa-cog me-2"></i> Preferences
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Account Settings -->
                        <div class="tab-pane fade show active" id="account">
                            <h5 class="card-title mb-4">Account Settings</h5>
                            <form action="update-settings.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Email Notifications</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="emailUpdates" checked>
                                        <label class="form-check-label" for="emailUpdates">
                                            Receive email updates about your account
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="emailNewsletter" checked>
                                        <label class="form-check-label" for="emailNewsletter">
                                            Receive newsletter and updates
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>

                        <!-- Notifications -->
                        <div class="tab-pane fade" id="notifications">
                            <h5 class="card-title mb-4">Notification Settings</h5>
                            <form action="update-notifications.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Push Notifications</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="pushGoals" checked>
                                        <label class="form-check-label" for="pushGoals">
                                            Goal progress updates
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="pushAchievements" checked>
                                        <label class="form-check-label" for="pushAchievements">
                                            Achievement notifications
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="pushReminders" checked>
                                        <label class="form-check-label" for="pushReminders">
                                            Reminders and alerts
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>

                        <!-- Privacy -->
                        <div class="tab-pane fade" id="privacy">
                            <h5 class="card-title mb-4">Privacy Settings</h5>
                            <form action="update-privacy.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Data Sharing</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="shareProgress">
                                        <label class="form-check-label" for="shareProgress">
                                            Share progress with friends
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="shareAchievements">
                                        <label class="form-check-label" for="shareAchievements">
                                            Share achievements
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>

                        <!-- Preferences -->
                        <div class="tab-pane fade" id="preferences">
                            <h5 class="card-title mb-4">Preferences</h5>
                            <form action="update-preferences.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Display Settings</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="theme" id="themeLight" checked>
                                        <label class="form-check-label" for="themeLight">
                                            Light Theme
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="theme" id="themeDark">
                                        <label class="form-check-label" for="themeDark">
                                            Dark Theme
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 