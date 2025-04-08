<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("⛔ Unauthorized.");
}

$current = isRakshaModeOn($pdo) ? 'off' : 'on';

$stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'raksha_mode'");
$stmt->execute([$current]);

header("Location: admin_dashboard.php");
exit;
?>
