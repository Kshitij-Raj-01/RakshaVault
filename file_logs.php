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

// Join file_logs with files to get original_name
$stmt = $pdo->prepare("
    SELECT fl.*, f.original_name 
    FROM file_logs fl
    JOIN files f ON fl.file_id = f.id
    WHERE fl.user_id = ?
    ORDER BY fl.accessed_at DESC
");
$stmt->execute([$userId]);
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>File Access History – RakshaVault</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9fafe;
            padding: 30px;
        }
        h2 {
            color: #2c3e50;
            text-align: center;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        th {
            background-color: #2980b9;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .back {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: #2980b9;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <h2>📜 My File Access Logs</h2>

    <table>
        <tr>
            <th>📄 File Name</th>
            <th>📍 IP Address</th>
            <th>🕒 Accessed At</th>
        </tr>
        <?php if ($logs): ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['original_name']) ?></td>
                    <td><?= htmlspecialchars($log['ip_address']) ?></td>
                    <td><?= htmlspecialchars($log['accessed_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3" style="text-align:center;">No access logs found yet.</td></tr>
        <?php endif; ?>
    </table>

    <a class="back" href="dashboard.php">← Back to Dashboard</a>

</body>
</html>
