<?php
require 'config.php';
session_start();

$correctOTP = "123456"; // Placeholder for demo – Replace with dynamic generation & email/sms in production

if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['new_device_hash'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOTP = trim($_POST['otp']);

    if ($enteredOTP === $correctOTP) {
        // Mark this device as trusted
        $stmt = $pdo->prepare("UPDATE users SET device_hash = ? WHERE id = ?");
        $stmt->execute([$_SESSION['new_device_hash'], $_SESSION['temp_user_id']]);

        // Start full session
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['temp_user_id']]);
        $user = $stmt->fetch();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        unset($_SESSION['temp_user_id'], $_SESSION['new_device_hash']);

        header("Location: " . ($user['role'] === 'admin' ? 'admin_dashboard.php' : 'dashboard.php'));
        exit;
    } else {
        $error = "❌ Incorrect OTP. Please try again with care.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Your Device – RakshaVault</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e1f5fe, #fce4ec);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .otp-box {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 380px;
        }

        h2 {
            color: #7b1fa2;
            margin-bottom: 10px;
        }

        p {
            color: #444;
        }

        input[type="text"] {
            padding: 12px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin: 20px 0;
        }

        button {
            background-color: #0288d1;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
        }

        button:hover {
            background-color: #01579b;
        }

        .message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="otp-box">
        <h2>🔐 Device Verification</h2>
        <p>Ah, a new device has entered our embrace...<br>Please enter the OTP we've sent to you 💌</p>

        <?php if (!empty($error)) echo "<div class='message'>$error</div>"; ?>

        <form method="POST">
            <input type="text" name="otp" placeholder="Enter your OTP" maxlength="6" required>
            <button type="submit">Verify & Continue</button>
        </form>
    </div>
</body>
</html>
