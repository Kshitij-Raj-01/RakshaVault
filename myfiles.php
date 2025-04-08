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
$filter = $_GET['class'] ?? null;

if ($filter) {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? AND classification = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$userId, $filter]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$userId]);
}
$files = $stmt->fetchAll();

function generateSecureToken($fileId, $expiresAt) {
    $data = [
        'id' => $fileId,
        'exp' => $expiresAt ? strtotime($expiresAt) : 0
    ];
    $raw = openssl_random_pseudo_bytes(16) . openssl_encrypt(json_encode($data), 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv = openssl_random_pseudo_bytes(16));
    return base64_encode($iv . substr($raw, 16));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>📁 My Files – RakshaVault</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f2f2f7;
            padding: 40px;
            color: #333;
        }
        h2 {
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
            color: #555;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .filter-msg {
            margin-bottom: 10px;
        }
        a.button {
            background-color: #4e73df;
            color: white;
            padding: 8px 14px;
            text-decoration: none;
            border-radius: 5px;
        }
        a.button:hover {
            background-color: #2e59d9;
        }
    </style>
</head>
<body>
    <h2>📁 My Files</h2>

    <?php if ($filter): ?>
        <p class="filter-msg">Showing files classified as: <strong><?= htmlspecialchars($filter) ?></strong></p>
        <p><a class="button" href="myfiles.php">🔙 Show All Files</a></p>
    <?php endif; ?>

    <?php if (count($files) === 0): ?>
        <p>📭 No files uploaded yet.</p>
    <?php else: ?>
    <table>
        <tr>
            <th>📄 File Name</th>
            <th>🏷️ Classification</th>
            <th>⏳ Expiry</th>
            <th>📥 Action</th>
        </tr>
        <?php foreach ($files as $file): ?>
            <tr>
                <td><?= htmlspecialchars($file['original_name']) ?></td>
                <td><?= htmlspecialchars($file['classification']) ?></td>
                <td><?= $file['expires_at'] ?? 'Never' ?></td>
                <td><a class="button" href="download.php?token=<?= urlencode(generateSecureToken($file['id'], $file['expires_at'])) ?>">Download</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</body>
</html>
