<?php
/**
 * config.php – MySQL connection for Rumahweb cPanel
 * ──────────────────────────────────────────────────
 * HOW TO FILL IN:
 *   1. Go to cPanel → "MySQL Databases"
 *   2. Create a database → copy the full name (e.g., cpanel_user_dbname)
 *   3. Create a database user → copy the full username and password
 *   4. Add user to database with ALL PRIVILEGES
 *   5. Fill in below
 *
 * This file is protected from direct browser access by .htaccess
 */

// ── MySQL Database Credentials ──
// Replace these with your actual cPanel MySQL values
define('DB_HOST',     'localhost');              // always 'localhost' on cPanel
define('DB_NAME',     'deey2313_dex_auth');      // created in cPanel → MySQL Databases
define('DB_USER',     'deey2313_dexuser');       // created in cPanel → MySQL Databases
define('DB_PASSWORD', 'YOUR_DB_PASSWORD');       // the password you set in cPanel

// ── Admin credentials (only account that can log in) ──
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

/**
 * Get a PDO connection to MySQL
 * @return PDO
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('DB Connection Error: ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Server error. Please try again later.']);
        exit;
    }
}
