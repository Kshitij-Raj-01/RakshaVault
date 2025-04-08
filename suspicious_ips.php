<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🔒 Access denied.");
}

$stmt = $pdo->query("SELECT user_id, ip_address, COUNT(*) as total_accesses 
                     FROM file_logs 
                     GROUP BY user_id, ip_address 
                     ORDER BY user_id, total_accesses DESC");

$results = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $results[$row['user_id']][] = [
        'ip' => $row['ip_address'],
        'accesses' => $row['total_accesses']
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Suspicious IP Access Patterns</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f9fafb;
            padding: 40px;
            color: #1f2937;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        h1 {
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #d1d5db;
        }
        th {
            background: #f3f4f6;
        }
        a.button {
            display: inline-block;
            margin-top: 20px;
            background: #2563eb;
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
        }
        a.button:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
<h1>🕵️ Suspicious IP Access Details</h1>
<div class="card">
    <?php foreach ($results as $userId => $ipData): ?>
        <?php if (count($ipData) >= 3): ?>
            <h3>User ID: <?= htmlspecialchars($userId) ?></h3>
            <table>
                <tr><th>IP Address</th><th>Access Count</th></tr>
                <?php foreach ($ipData as $entry): ?>
                    <tr>
                        <td><?= htmlspecialchars($entry['ip']) ?></td>
                        <td><?= $entry['accesses'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table><br>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<a href="admin_dashboard.php" class="button">← Back to Dashboard</a>
</body>
</html>
