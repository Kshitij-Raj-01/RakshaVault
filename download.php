<?php
require 'config.php';
require 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['token'])) {
    die("🚫 Invalid access.");
}

$token = $_GET['token'];

if (isRakshaModeOn($pdo)) {
    die("🚨 Raksha Mode is active. Downloads are temporarily disabled.");
}

if (isAccessSuspicious($pdo, $userId, $ip)) {
    // For now, we'll just store an alert in a table called "alerts"
    $stmt = $pdo->prepare("INSERT INTO alerts (user_id, file_id, ip_address, message) VALUES (?, ?, ?, ?)");
    $msg = "Suspicious download detected from new IP $ip";
    $stmt->execute([$userId, $fileId, $ip, $msg]);
}


try {
    // 🔐 Decode and decrypt the token
    $raw = base64_decode($token);
    $iv = substr($raw, 0, 16);
    $encrypted = substr($raw, 16);

    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
    $data = json_decode($decrypted, true);

    // ⛔ Invalid or malformed link
    if (!$data || !isset($data['id'], $data['exp'])) {
        die("❌ Invalid or corrupted link.");
    }

    $fileId = $data['id'];
    $expiry = $data['exp'];

    // ⏳ Check for expired links
    if ($expiry !== 0 && time() > $expiry) {
        die("⏳ This secure link has expired.");
    }

    // 📁 Fetch file info
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch();

    if (!$file) {
        die("📁 File not found.");
    }

    $filePath = 'uploads/' . $file['encrypted_name'];
    $downloadName = $file['original_name'];

    if (!file_exists($filePath)) {
        die("🧨 File missing on server.");
    }

    function getRealUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    // 🧾 Log download
    $userId = $_SESSION['user_id'] ?? null;
    $ip = getRealUserIP();

    $logStmt = $pdo->prepare("INSERT INTO file_logs (user_id, file_id, ip_address) VALUES (?, ?, ?)");
    $logStmt->execute([$userId, $file['id'], $ip]);

    require_once 'blockchain.php';
addToBlockchain($pdo, $userId, $fileId, $ip);


    // 📤 Send the file to the user
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=\"" . basename($downloadName) . "\"");
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;

} catch (Exception $e) {
    die("💔 An error occurred: " . $e->getMessage());
}
