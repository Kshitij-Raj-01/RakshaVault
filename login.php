<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function getDeviceHash() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $ip        = $_SERVER['REMOTE_ADDR'];
    return hash('sha256', $userAgent . $ip);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $currentDevice = getDeviceHash();

            if ($user['device_hash'] && $user['device_hash'] !== $currentDevice) {
                $_SESSION['temp_user_id'] = $user['id'];
                $_SESSION['new_device_hash'] = $currentDevice;
                header("Location: otp.php");
                exit;
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                header("Location: " . ($user['role'] === 'admin' ? 'admin_dashboard.php' : 'dashboard.php'));
                exit;
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login – RakshaVault</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #fce4ec, #e3f2fd);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-box {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }

        h2 {
            color: #6a1b9a;
            margin-bottom: 25px;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1em;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            color: red;
            margin-bottom: 15px;
        }

        .link {
            margin-top: 15px;
            display: block;
            color: #007bff;
            text-decoration: none;
        }

        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Welcome Back to RakshaVault 🛡️</h2>

    <?php if (!empty($error)) echo "<div class='message'>$error</div>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Your Email 💌" required>
        <input type="password" name="password" placeholder="Your Password 🔒" required>
        <button type="submit">Log In</button>
    </form>

    <a class="link" href="register.php">New here? Create an account 🌸</a>
</div>

</body>
</html>
