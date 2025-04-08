<?php
require 'config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🔒 Access denied.");
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get IDs of expired files
    $stmt = $pdo->prepare("SELECT id FROM files WHERE expires_at IS NOT NULL AND expires_at < NOW()");
    $stmt->execute();
    $expiredFiles = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($expiredFiles) {
        // Delete from file_logs
        $in = str_repeat('?,', count($expiredFiles) - 1) . '?';
        $pdo->prepare("DELETE FROM file_logs WHERE file_id IN ($in)")->execute($expiredFiles);

        // Now delete from files
        $pdo->prepare("DELETE FROM files WHERE id IN ($in)")->execute($expiredFiles);
    }

    $pdo->commit();
    echo "🧹 Expired files and related logs deleted successfully.";
    header("Location: login.php");
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Cleanup failed: " . $e->getMessage();
}
?>
