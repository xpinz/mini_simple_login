<?php
/**
 * check.php – Server diagnostics page
 * ─────────────────────────────────────
 * Upload this to public_html to verify PHP + PostgreSQL works.
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
    .card { background: #fff; border-radius: 12px; padding: 32px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    h1 { color: #1A2B3C; font-size: 1.3rem; margin-bottom: 20px; }
    .row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
    .label { color: #3D5A73; font-weight: 600; font-size: 0.85rem; }
    .ok { color: #1A7A45; font-weight: 700; }
    .fail { color: #CC1111; font-weight: 700; }
    .warn { color: #B8860B; font-weight: 700; }
    pre { background: #F4F9FC; padding: 12px; border-radius: 6px; font-size: 0.8rem; overflow-x: auto; margin-top: 16px; }
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
    <span class="label">PDO PostgreSQL Driver</span>
    <span class="<?= extension_loaded('pdo_pgsql') ? 'ok' : 'fail' ?>">
      <?= extension_loaded('pdo_pgsql') ? '✅ Loaded' : '❌ Missing – contact Rumahweb support' ?>
    </span>
  </div>

  <div class="row">
    <span class="label">pg_connect() available</span>
    <span class="<?= function_exists('pg_connect') ? 'ok' : 'warn' ?>">
      <?= function_exists('pg_connect') ? '✅ Yes' : '⚠️ No (not required if PDO pgsql works)' ?>
    </span>
  </div>

  <?php if (extension_loaded('pdo_pgsql')): ?>
  <div class="row">
    <span class="label">Neon DB Connection</span>
    <span>
      <?php
      try {
          require_once __DIR__ . '/config.php';
          $db = getDB();
          $result = $db->query("SELECT COUNT(*) as cnt FROM users")->fetch();
          echo '<span class="ok">✅ Connected – ' . $result['cnt'] . ' users in table</span>';
      } catch (Exception $e) {
          echo '<span class="fail">❌ ' . htmlspecialchars($e->getMessage()) . '</span>';
      }
      ?>
    </span>
  </div>
  <?php endif; ?>

  <div class="row">
    <span class="label">JSON Support</span>
    <span class="ok">✅ <?= function_exists('json_encode') ? 'Available' : 'Missing' ?></span>
  </div>

  <div class="row">
    <span class="label">.htaccess / mod_rewrite</span>
    <span class="<?= in_array('mod_rewrite', apache_get_modules() ?? []) ? 'ok' : 'warn' ?>">
      <?php
      if (function_exists('apache_get_modules')) {
          echo in_array('mod_rewrite', apache_get_modules()) ? '✅ Enabled' : '⚠️ Check with host';
      } else {
          echo '⚠️ Cannot detect (non-Apache or CGI mode)';
      }
      ?>
    </span>
  </div>

  <pre>Document Root: <?= $_SERVER['DOCUMENT_ROOT'] ?>
Script Path:   <?= __FILE__ ?>
Server:        <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></pre>
</div>
</body>
</html>
