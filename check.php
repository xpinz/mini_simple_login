<?php
/**
 * check.php – Server diagnostics page (MySQL version)
 * ────────────────────────────────────────────────────
 * Upload to public_html to verify PHP + MySQL works.
 * DELETE THIS FILE after confirming everything works.
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Server Check</title>
  <style>
    body { font-family: 'Inter', system-ui, sans-serif; padding: 40px; background: #B4E1EB; }
    .card { background: #fff; border-radius: 12px; padding: 32px; max-width: 520px; margin: 0 auto; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    h1 { color: #1A2B3C; font-size: 1.3rem; margin-bottom: 20px; }
    .row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
    .label { color: #3D5A73; font-weight: 600; font-size: 0.85rem; }
    .ok { color: #1A7A45; font-weight: 700; }
    .fail { color: #CC1111; font-weight: 700; }
    .warn { color: #B8860B; font-weight: 700; }
    pre { background: #F4F9FC; padding: 12px; border-radius: 6px; font-size: 0.8rem; overflow-x: auto; margin-top: 16px; }
    .note { background: #FFF3CD; border: 1px solid #F0D060; border-radius: 8px; padding: 14px; margin-top: 16px; font-size: 0.82rem; color: #664d03; }
  </style>
</head>
<body>
<div class="card">
  <h1>🔍 DEX Login – Server Check</h1>

  <div class="row">
    <span class="label">PHP Version</span>
    <span class="ok"><?= phpversion() ?></span>
  </div>

  <div class="row">
    <span class="label">PDO Extension</span>
    <span class="<?= extension_loaded('pdo') ? 'ok' : 'fail' ?>">
      <?= extension_loaded('pdo') ? '✅ Loaded' : '❌ Missing' ?>
    </span>
  </div>

  <div class="row">
    <span class="label">PDO MySQL Driver</span>
    <span class="<?= extension_loaded('pdo_mysql') ? 'ok' : 'fail' ?>">
      <?= extension_loaded('pdo_mysql') ? '✅ Loaded' : '❌ Missing' ?>
    </span>
  </div>

  <div class="row">
    <span class="label">cURL Extension</span>
    <span class="<?= extension_loaded('curl') ? 'ok' : 'warn' ?>">
      <?= extension_loaded('curl') ? '✅ Loaded' : '⚠️ Not loaded' ?>
    </span>
  </div>

  <?php if (extension_loaded('pdo_mysql')): ?>
  <div class="row">
    <span class="label">MySQL Connection</span>
    <span>
      <?php
      require_once __DIR__ . '/config.php';
      if (DB_NAME === 'YOUR_CPANEL_DB_NAME') {
          echo '<span class="warn">⚠️ config.php not configured yet – edit DB credentials</span>';
      } else {
          try {
              $db = getDB();
              // Check if users table exists
              $tables = $db->query("SHOW TABLES LIKE 'users'")->fetchAll();
              if (count($tables) > 0) {
                  $result = $db->query("SELECT COUNT(*) as cnt FROM users")->fetch();
                  echo '<span class="ok">✅ Connected – ' . $result['cnt'] . ' users in table</span>';
              } else {
                  echo '<span class="warn">⚠️ Connected to DB but "users" table not found. Run init_mysql.sql in phpMyAdmin.</span>';
              }
          } catch (Exception $e) {
              echo '<span class="fail">❌ ' . htmlspecialchars($e->getMessage()) . '</span>';
          }
      }
      ?>
    </span>
  </div>
  <?php endif; ?>

  <div class="row">
    <span class="label">JSON Support</span>
    <span class="ok"><?= function_exists('json_encode') ? '✅ Available' : '❌ Missing' ?></span>
  </div>

  <pre>Document Root: <?= $_SERVER['DOCUMENT_ROOT'] ?>
Script Path:   <?= __FILE__ ?>
Server:        <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></pre>

  <?php if (DB_NAME === 'YOUR_CPANEL_DB_NAME'): ?>
  <div class="note">
    <strong>⚠️ Next step:</strong> Edit <code>config.php</code> and fill in your MySQL database credentials from cPanel → MySQL Databases.
  </div>
  <?php endif; ?>
</div>
</body>
</html>
