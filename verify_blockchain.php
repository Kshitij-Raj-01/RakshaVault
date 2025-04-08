<?php
require 'config.php';

$stmt = $pdo->query("SELECT * FROM blockchain ORDER BY id ASC");
$chain = $stmt->fetchAll();

$prevHash = null;
$valid = true;

foreach ($chain as $block) {
    $expectedHash = hash('sha256', 
        $block['user_id'] . 
        $block['file_id'] . 
        $block['ip_address'] . 
        $block['accessed_at'] . 
        $prevHash
    );

    if ($expectedHash !== $block['hash']) {
        echo "❌ Tampering detected at Block ID: {$block['id']}<br>";
        $valid = false;
        break;
    }

    $prevHash = $block['hash'];
}

if ($valid) {
    echo "✅ All blockchain entries are valid and intact.";
}
?>
