<?php
function addToBlockchain($pdo, $userId, $fileId, $ip) {
    // Get last block
    $stmt = $pdo->query("SELECT hash FROM blockchain ORDER BY id DESC LIMIT 1");
    $lastHash = $stmt->fetchColumn() ?? 'GENESIS';

    $timestamp = date('Y-m-d H:i:s');
    $data = "$userId|$fileId|$ip|$timestamp|$lastHash";
    $currentHash = hash('sha256', $data);

    $insert = $pdo->prepare("
        INSERT INTO blockchain (user_id, file_id, ip_address, accessed_at, hash, previous_hash)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insert->execute([$userId, $fileId, $ip, $timestamp, $currentHash, $lastHash]);
}
?>
