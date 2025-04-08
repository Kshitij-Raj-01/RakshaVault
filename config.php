<?php
// config.php – using .env for sensitive info

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);

    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $host = $_ENV['DB_HOST'];
    $db   = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        die("💔 Database connection failed: " . $e->getMessage());
    }

    if (!function_exists('isRakshaModeOn')) {
        function isRakshaModeOn($pdo) {
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'raksha_mode'");
            $stmt->execute();
            return $stmt->fetchColumn() === 'on';
        }
    }

    if (!defined('ENCRYPTION_KEY')) {
        define('ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY']);
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
?>
