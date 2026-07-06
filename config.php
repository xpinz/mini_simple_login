<?php
/**
 * config.php – Neon PostgreSQL connection via PDO
 * ─────────────────────────────────────────────────
 * This file is protected from direct browser access by .htaccess
 */

// Neon PostgreSQL credentials
define('DB_HOST',     'ep-billowing-cell-aojlsjmr-pooler.c-2.ap-southeast-1.aws.neon.tech');
define('DB_PORT',     '5432');
define('DB_NAME',     'jdonloder_auth');
define('DB_USER',     'neondb_owner');
define('DB_PASSWORD', 'npg_PtZKR8Uk1Tbr');

// Admin credentials (only account that can log in)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

/**
 * Get a PDO connection to Neon PostgreSQL
 * @return PDO
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
        DB_HOST, DB_PORT, DB_NAME
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Log error but don't expose details to client
        error_log('DB Connection Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error. Please try again later.']);
        exit;
    }
}
