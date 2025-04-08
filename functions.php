<?php
function isAccessSuspicious($pdo, $userId, $ipAddress) {
    // Get the last 5 IPs used by the user
    $stmt = $pdo->prepare("SELECT ip_address FROM file_logs WHERE user_id = ? ORDER BY accessed_at DESC LIMIT 5");
    $stmt->execute([$userId]);
    $recentIps = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $uniqueIps = array_unique($recentIps);

    // If this IP is new and user had 3+ different IPs recently = suspicious
    if (!in_array($ipAddress, $uniqueIps) && count($uniqueIps) >= 3) {
        return true;
    }

    return false;
}
