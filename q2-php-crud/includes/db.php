<?php
require_once __DIR__ . "/config.php";

/**
 * Returns one shared PDO connection.
 * PDO + prepared statements protect us from SQL injection, because user
 * input is sent separately from the SQL text.
 */
function db(): PDO
{
    static $pdo = null;               // created once, reused afterwards
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw on SQL errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // never print the raw error to visitors - it can leak credentials
            error_log($e->getMessage());
            http_response_code(500);
            exit("Database connection failed. Check includes/config.php and that MySQL is running.");
        }
    }
    return $pdo;
}
