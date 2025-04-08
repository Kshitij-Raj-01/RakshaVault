<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'functions.php';

$userId = $_SESSION['user_id'];
$currentIp = $_SERVER['REMOTE_ADDR'];

$isSuspicious = isAccessSuspicious($pdo, $userId, $currentIp);


$name = $_SESSION['user_name'];
$role = $_SESSION['role'];

// If admin, redirect straight to admin dashboard
if ($role === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>RakshaVault - User Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f3f3f9;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background: #343a40;
            color: white;
            padding: 15px 25px;
            font-size: 20px;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin-top: 0;
            color: #444;
        }
        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            background: #4e73df;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #2e59d9;
        }
    </style>
</head>
<body>

<div class="navbar">
    🛡️ RakshaVault | Welcome, <?= $name ?>
</div>

<div class="container">
    <div class="card">
        <h3>📤 Upload a New File</h3>
        <a href="upload.php" class="btn">Upload File</a>
    </div>

    <div class="card">
        <h3>📂 My Files</h3>
        <a href="myfiles.php" class="btn">View Files</a>
    </div>

    <div class="card">
        <h3>🔐 Access Logs</h3>
        <a href="file_logs.php" class="btn">View Logs</a>
    </div>

    <div class="card">
        <h3>⚙️ Account Settings</h3>
        <a href="account_settings.php" class="btn">Manage Account</a>
    </div>

    <div class="card">
        <h3>🚪 Logout</h3>
        <a href="logout.php" class="btn" style="background: #dc3545;">Logout</a>
    </div>
</div>

</body>
</html>
