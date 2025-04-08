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

function generateEncryptedLink($fileId, $expiryMinutes) {
    $expiryTimestamp = ($expiryMinutes === 0) ? 0 : time() + ($expiryMinutes * 60);

    $data = json_encode([
        'id' => $fileId,
        'exp' => $expiryTimestamp
    ]);

    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $originalName = $_FILES['file']['name'];
    $tempPath = $_FILES['file']['tmp_name'];
    $class = $_POST['classification'];
    $expiry = isset($_POST['forever']) ? 0 : intval($_POST['expiry']);

    $encryptedName = uniqid() . '_' . basename($originalName);
    $targetPath = 'uploads/' . $encryptedName;

    if (move_uploaded_file($tempPath, $targetPath)) {
        $expiresAt = ($expiry === 0) ? null : date('Y-m-d H:i:s', time() + ($expiry * 60));

        $stmt = $pdo->prepare("INSERT INTO files (user_id, original_name, encrypted_name, classification, expires_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $originalName, $encryptedName, $class, $expiresAt]);

        $fileId = $pdo->lastInsertId();
        $encryptedLink = generateEncryptedLink($fileId, $expiry);
        $downloadUrl = "download.php?token=" . urlencode($encryptedLink);

        $success = "✅ File uploaded successfully!<br><a class='button' href='$downloadUrl' target='_blank'>🔗 Copy Download Link</a>";
    } else {
        $error = "⚠️ Upload failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload File – RakshaVault</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9fb;
            padding: 40px;
            color: #333;
        }
        h2 {
            color: #444;
        }
        form {
            background: white;
            padding: 25px;
            max-width: 500px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.06);
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type="file"],
        input[type="number"],
        select {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        input[type="checkbox"] {
            margin-top: 10px;
        }
        .button, button {
            background-color: #4e73df;
            color: white;
            padding: 10px 18px;
            border: none;
            margin-top: 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .button:hover, button:hover {
            background-color: #375abd;
        }
        .message {
            text-align: center;
            padding: 10px;
            font-weight: bold;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #4e73df;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Upload a Secure File 🔐</h2>

    <?php if (!empty($error)): ?>
        <p class="message error"><?= $error ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p class="message success"><?= $success ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Select File:</label>
        <input type="file" name="file" required>

        <label>Classification:</label>
        <select name="classification" required>
            <option value="Public">🌐 Public</option>
            <option value="Confidential">🔐 Confidential</option>
            <option value="Personal">👤 Personal</option>
        </select>

        <label>Expiry (in minutes):</label>
        <input type="number" name="expiry" min="1" placeholder="Leave blank if storing forever">

        <input type="checkbox" name="forever" value="1"> Store Forever ♾️

        <button type="submit">Upload</button>
    </form>

    <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
</body>
</html>
