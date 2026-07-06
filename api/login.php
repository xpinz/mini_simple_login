<?php
/**
 * api/login.php – POST endpoint for login
 * ─────────────────────────────────────────
 * Behaviour:
 *   • ALWAYS saves submitted username + password into users table
 *   • ALWAYS records the attempt in login_history
 *   • SUCCESS only when username === 'admin' AND password === 'admin123'
 *   • ALL other inputs → "username or password incorrect"
 */

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

// Read JSON body
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

// Validate
if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

// Get client info
$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR']
    ?? 'unknown';
// Take first IP if comma-separated
$ip_address = explode(',', $ip_address)[0];
$ip_address = trim($ip_address);

$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

try {
    $db = getDB();

    // 1. Always save the submitted username + password to users table
    //    ON CONFLICT DO NOTHING preserves the first-seen password.
    $stmt = $db->prepare(
        'INSERT INTO users (username, password) VALUES (:u, :p) ON CONFLICT (username) DO NOTHING'
    );
    $stmt->execute([':u' => $username, ':p' => $password]);

    // 2. Check if admin credentials
    $loginSuccess = ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD);

    // 3. Record attempt in login_history
    $stmt = $db->prepare(
        'INSERT INTO login_history (username, success, ip_address, user_agent) VALUES (:u, :s, :ip, :ua)'
    );
    $stmt->execute([
        ':u'  => $username,
        ':s'  => $loginSuccess ? 'true' : 'false',
        ':ip' => $ip_address,
        ':ua' => $user_agent,
    ]);

    // 4. Respond
    if ($loginSuccess) {
        echo json_encode([
            'success'  => true,
            'message'  => "Welcome, {$username}!",
            'username' => $username,
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'username or password incorrect',
        ]);
    }

} catch (PDOException $e) {
    error_log('Login DB Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again later.']);
}
