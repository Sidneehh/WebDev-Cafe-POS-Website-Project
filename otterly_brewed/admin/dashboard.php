<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin'){
    header('Location: ../otter_homepage.html');
    exit();
}

$fullname = $_SESSION['fullname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Otterly Brewed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assests/css/main_styles.css" rel="stylesheet">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <div class="welcome-section">
            <img src="../assests/images/otter-logo.png" alt="Otter Logo" class="logo-image">
            <h1 class="dashboard-title">🦦 Otterly Brewed</h1>
            <p class="welcome-text">Welcome, <?php echo $fullname; ?>!</p>
            <span class="role-badge">👤 ADMINISTRATOR</span>
        </div>

        <div class="menu-options">
            <a href="order.php" class="option-card">
                <div class="option-icon">🛒</div>
                <div class="option-content">
                    <div class="option-title">Order Items</div>
                    <div class="option-description">Create new orders and process customer purchases</div>
                </div>
            </a>

            <a href="manage_menu.php" class="option-card">
                <div class="option-icon">📋</div>
                <div class="option-content">
                    <div class="option-title">Manage Menu</div>
                    <div class="option-description">Add, edit, or remove menu items and categories</div>
                </div>
            </a>

            <a href="view_reports.php" class="option-card">
                <div class="option-icon">📊</div>
                <div class="option-content">
                    <div class="option-title">View Reports</div>
                    <div class="option-description">View sales reports and order history</div>
                </div>
            </a>
        </div>

        <div class="text-center">
            <button type="button" class="btn btn-logout" onclick="window.location.href='../logout.php'">
                Logout
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>