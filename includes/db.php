<?php
// ============================================================
// Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change to your DB user
define('DB_PASS', '');            // Change to your DB password
define('DB_NAME', 'voting_system');
define('APP_NAME', 'VoteSecure');
define('APP_URL', 'http://localhost/voting-system');

// Create connection
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
