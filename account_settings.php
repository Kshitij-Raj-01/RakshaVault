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

// Fetch current user info
$stmt = $pdo->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name']);
    $newEmail = trim($_POST['email']);

    // Update name/email
    $updateStmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $updateStmt->execute([$newName, $newEmail, $userId]);
    $success = "✅ Account updated successfully.";
    $user['name'] = $newName;
    $user['email'] = $newEmail;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Settings – RakshaVault</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4f8;
            padding: 40px;
        }
        .container {
            width: 500px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            text-align: center;
        }
        label {
            display: block;
            margin-top: 15px;
            color: #333;
            font-weight: 500;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }
        button {
            background-color: #2980b9;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            font-size: 16px;
        }
        button:hover {
            background-color: #216a94;
        }
        .note {
            color: gray;
            font-size: 0.9em;
            margin-top: 15px;
            text-align: center;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            display: inline-block;
            margin: 6px 10px;
            color: #2980b9;
            text-decoration: none;
            font-weight: 500;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .danger {
            color: red !important;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>👤 Account Settings</h2>

    <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

    <form method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <button type="submit">Update Account</button>
    </form>

    <div class="links">
        <a href="change_password.php">🔑 Change Password</a>
        <a href="delete_account.php" class="danger">🗑️ Delete Account</a>
    </div>

    <p class="note">📅 Registered on: <?= htmlspecialchars(date('F j, Y', strtotime($user['created_at']))) ?></p>

    <div class="links">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
