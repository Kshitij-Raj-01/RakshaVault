<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete user's files and account
    $pdo->prepare("DELETE FROM files WHERE user_id = ?")->execute([$userId]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

    session_destroy();
    header("Location: goodbye.html"); // You can create a poetic farewell page here 💌
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Account – RakshaVault</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fef2f2;
            color: #2c3e50;
            padding: 50px;
        }
        .container {
            width: 500px;
            margin: auto;
            background: #fff0f0;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(255, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            color: #b91c1c;
            font-size: 24px;
        }
        p {
            margin-top: 10px;
            color: #4b5563;
        }
        button {
            background-color: #dc2626;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background-color: #b91c1c;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #2563eb;
        }
        a:hover {
            text-decoration: underline;
        }
        .emoji {
            font-size: 40px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="emoji">⚠️</div>
    <h2>Are you absolutely sure?</h2>
    <p>This will <strong>permanently erase</strong> your account and all the precious memories and files stored in RakshaVault.</p>
    <p>There's no going back from here</p>

    <form method="POST">
        <button type="submit">Yes, Delete My Account</button>
    </form>

    <a href="account_settings.php">← No, take me back to safety</a>
</div>

</body>
</html>
