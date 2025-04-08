<?php
require 'config.php';
require 'functions.php';
require 'ai_alerts.php';
$aiAlerts = detectSuspiciousAccess($pdo);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
}

$totalFiles = $pdo->query("SELECT COUNT(*) FROM files")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$expired = $pdo->query("SELECT COUNT(*) FROM files WHERE expires_at IS NOT NULL AND expires_at < NOW()")->fetchColumn();
$active = $pdo->query("SELECT COUNT(*) FROM files WHERE expires_at IS NULL OR expires_at > NOW()")->fetchColumn();

$stmt = $pdo->query("SELECT classification, COUNT(*) AS total FROM files GROUP BY classification");
$byClass = $stmt->fetchAll();

$filter = $_GET['filter'] ?? '';
if ($filter) {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE classification = ?");
    $stmt->execute([$filter]);
    $filteredFiles = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM files");
    $filteredFiles = $stmt->fetchAll();
}

$stmt = $pdo->query("SELECT fl.*, u.email, f.original_name FROM file_logs fl LEFT JOIN users u ON fl.user_id = u.id LEFT JOIN files f ON fl.file_id = f.id ORDER BY fl.accessed_at DESC LIMIT 10");
$logs = $stmt->fetchAll();

$stmt = $pdo->query("SELECT DATE(uploaded_at) as upload_date, COUNT(*) as total FROM files GROUP BY DATE(uploaded_at) ORDER BY upload_date DESC LIMIT 7");
$trendData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$chartLabels = [];
$chartCounts = [];
foreach (array_reverse($trendData) as $row) {
    $chartLabels[] = $row['upload_date'];
    $chartCounts[] = $row['total'];
}

function getSuspiciousUsers($pdo) {
    $stmt = $pdo->query("SELECT user_id, COUNT(DISTINCT ip_address) AS ip_count FROM file_logs GROUP BY user_id HAVING ip_count >= 3");
    return $stmt->fetchAll();
}
$suspiciousUsers = getSuspiciousUsers($pdo);

$stmt = $pdo->query("SELECT b.*, u.email, f.original_name FROM blockchain b LEFT JOIN users u ON b.user_id = u.id LEFT JOIN files f ON b.file_id = f.id ORDER BY b.id DESC LIMIT 10");
$chain = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>RakshaVault - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #f3f4f6, #e5e7eb);
            padding: 30px;
            color: #1f2937;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1, h3 {
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #d1d5db;
        }
        table th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        button {
            padding: 10px 20px;
            border: none;
            background: #2563eb;
            color: white;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #1e40af;
        }
        select {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #9ca3af;
        }
        ul { padding-left: 20px; }
        ul li { margin-bottom: 5px; }
        a {
            color: #2563eb;
            font-weight: 500;
            text-decoration: none;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div style="display: flex; justify-content: space-between; align-items: center;">
    <h1>🛡️ RakshaVault Admin Dashboard</h1>
    <form action="logout.php" method="post">
        <button style="background-color: #dc2626;">🚪 Logout</button>
    </form>
</div>

<div class="card">
    <h3>📈 File Uploads Over Last 7 Days</h3>
    <canvas id="uploadChart" height="100"></canvas>
</div>

<div class="card">📂 Total Files: <strong><?= $totalFiles ?></strong></div>
<div class="card">👤 Total Users: <strong><?= $totalUsers ?></strong></div>
<div class="card">🢨 Expired Files: <strong><?= $expired ?></strong></div>
<div class="card">✅ Active Files: <strong><?= $active ?></strong></div>

<div class="card">
  🏷️ Files by Classification:
  <ul>
    <?php foreach ($byClass as $row): ?>
      <li><?= $row['classification'] ?>: <strong><?= $row['total'] ?></strong></li>
    <?php endforeach; ?>
  </ul>
</div>

<div class="card">
  <h3>🔍 Filter Files by Classification</h3>
  <form method="get">
    <label for="filter">Select:</label>
    <select name="filter" id="filter" onchange="this.form.submit()">
      <option value="">-- All --</option>
      <option value="Public" <?= $filter === 'Public' ? 'selected' : '' ?>>Public</option>
      <option value="Confidential" <?= $filter === 'Confidential' ? 'selected' : '' ?>>Confidential</option>
      <option value="Top Secret" <?= $filter === 'Top Secret' ? 'selected' : '' ?>>Top Secret</option>
      <option value="Internal" <?= $filter === 'Internal' ? 'selected' : '' ?>>Internal</option>
    </select>
  </form>
  <table>
    <tr>
      <th>Original Name</th><th>Uploaded By</th><th>Classification</th><th>Uploaded At</th>
    </tr>
    <?php foreach ($filteredFiles as $file): ?>
      <tr>
        <td><?= htmlspecialchars($file['original_name']) ?></td>
        <td>
          <?php
          $u = $pdo->prepare("SELECT email FROM users WHERE id = ?");
          $u->execute([$file['user_id']]);
          echo htmlspecialchars($u->fetchColumn());
          ?>
        </td>
        <td><?= htmlspecialchars($file['classification']) ?></td>
        <td><?= $file['uploaded_at'] ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="card">
  <h3>📜 Recent File Access Logs</h3>
  <table>
    <tr>
      <th>User Email</th><th>File Name</th><th>IP Address</th><th>Accessed At</th>
    </tr>
    <?php foreach ($logs as $log): ?>
      <tr>
        <td><?= htmlspecialchars($log['email']) ?></td>
        <td><?= htmlspecialchars($log['original_name']) ?></td>
        <td><?= $log['ip_address'] ?></td>
        <td><?= $log['accessed_at'] ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="card">
  <h3>🔗 Blockchain Access Log</h3>
  <table>
    <tr>
      <th>User</th><th>File</th><th>IP</th><th>Time</th><th>Hash</th>
    </tr>
    <?php foreach ($chain as $b): ?>
      <tr>
        <td><?= htmlspecialchars($b['email']) ?></td>
        <td><?= htmlspecialchars($b['original_name']) ?></td>
        <td><?= $b['ip_address'] ?></td>
        <td><?= $b['accessed_at'] ?></td>
        <td style="font-size:12px;"><?= substr($b['hash'], 0, 20) ?>...</td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="card">
  <h3>🚨 Suspicious User Access Patterns</h3>
  <?php if (count($suspiciousUsers) > 0): ?>
    <ul>
      <?php foreach ($suspiciousUsers as $u): ?>
        <li>User ID: <?= $u['user_id'] ?> — IPs used: <strong><?= $u['ip_count'] ?></strong></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>All clear. 🌈 No suspicious patterns found.</p>
  <?php endif; ?>
  <div style="text-align: center; margin-top: 20px;">
    <form method="get" action="suspicious_ips.php">
      <button type="submit">🕵️ View Suspicious IPs</button>
    </form>
  </div>
</div>

<div class="card">
  <h3>🤖 AI-Generated Access Alerts</h3>
  <?php if (!empty($aiAlerts)): ?>
    <ul>
      <?php foreach ($aiAlerts as $alert): ?>
        <li>⚠️ <?= htmlspecialchars($alert) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>🧘 All activity appears normal.</p>
  <?php endif; ?>
</div>

<div class="card">
  <h3>⚠️ Emergency Raksha Mode</h3>
  <form method="post" action="toggle_raksha.php">
      <button type="submit"><?= isRakshaModeOn($pdo) ? '🔚 Disable' : '🚨 Enable' ?> Raksha Mode</button>
  </form>
  <p style="color:red;"><strong>Status: <?= isRakshaModeOn($pdo) ? 'ACTIVE 🚨' : 'OFF ✅' ?></strong></p>
</div>

<p><a href="cleanup_expired.php">🧹 Run Cleanup Now</a></p>

<script>
const ctx = document.getElementById('uploadChart').getContext('2d');
const uploadChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Files Uploaded',
            data: <?= json_encode($chartCounts) ?>,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>