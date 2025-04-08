<?php
require 'config.php';

function detectSuspiciousAccess($pdo) {
    $alerts = [];

    // Rapid download detection
    $stmt = $pdo->query("
        SELECT user_id, COUNT(*) as total
        FROM file_logs
        WHERE accessed_at >= NOW() - INTERVAL 5 MINUTE
        GROUP BY user_id
        HAVING total > 3
    ");
    foreach ($stmt->fetchAll() as $row) {
        $alerts[] = "User ID {$row['user_id']} downloaded files rapidly (more than 3 in 5 mins).";
    }

    // Odd hour access
    $stmt = $pdo->query("
        SELECT user_id, HOUR(accessed_at) as hr
        FROM file_logs
        WHERE HOUR(accessed_at) BETWEEN 0 AND 4
    ");
    foreach ($stmt->fetchAll() as $row) {
        $alerts[] = "User ID {$row['user_id']} accessed files during odd hours (".$row['hr']." AM).";
    }

    // Multiple IPs in a day
    $stmt = $pdo->query("
        SELECT user_id, COUNT(DISTINCT ip_address) as ip_count
        FROM file_logs
        WHERE accessed_at >= CURDATE()
        GROUP BY user_id
        HAVING ip_count > 3
    ");
    foreach ($stmt->fetchAll() as $row) {
        $alerts[] = "User ID {$row['user_id']} used more than 3 IPs today.";
    }

    return $alerts;
}
?>
