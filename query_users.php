<?php
/**
 * query_users.php – View users table in browser
 * ──────────────────────────────────────────────
 * Access: https://deexinventorymanagement.site/query_users.php
 * DELETE THIS FILE in production for security.
 */

require_once __DIR__ . '/config.php';
$db = getDB();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $db->prepare('DELETE FROM users WHERE id = :id AND username != :admin');
    $stmt->execute([':id' => (int)$_POST['delete_id'], ':admin' => 'admin']);
    header('Location: query_users.php');
    exit;
}

// Fetch data
$users   = $db->query('SELECT id, username, password, created_at FROM users ORDER BY id DESC')->fetchAll();
$history = $db->query('SELECT id, username, success, ip_address, user_agent, login_at FROM login_history ORDER BY id DESC LIMIT 50')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users Table – DEX Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', system-ui, sans-serif; background: #B4E1EB; padding: 32px 16px; }
    .container { max-width: 900px; margin: 0 auto; }
    h1 { color: #1A2B3C; font-size: 1.4rem; margin-bottom: 8px; }
    h2 { color: #3D5A73; font-size: 1.1rem; margin: 32px 0 12px; }
    .subtitle { color: #6B8FA8; font-size: 0.85rem; margin-bottom: 20px; }
    .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(120,164,203,0.15); margin-bottom: 24px; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    th { background: #78A4CB; color: #fff; padding: 10px 12px; text-align: left; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; font-size: 0.72rem; }
    td { padding: 9px 12px; border-bottom: 1px solid #E8F0F5; color: #1A2B3C; }
    tr:hover td { background: #F4F9FC; }
    .success-true { color: #1A7A45; font-weight: 700; }
    .success-false { color: #CC1111; font-weight: 700; }
    .btn-del { background: #CC1111; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; font-size: 0.72rem; cursor: pointer; font-weight: 600; }
    .btn-del:hover { background: #a00; }
    .count { display: inline-block; background: #78A4CB; color: #fff; padding: 2px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; margin-left: 8px; }
    .back { display: inline-block; margin-top: 16px; color: #78A4CB; text-decoration: none; font-weight: 600; font-size: 0.85rem; }
    .back:hover { text-decoration: underline; }
  </style>
</head>
<body>
<div class="container">
  <h1>📋 DEX Database Viewer</h1>
  <p class="subtitle">Live data from Neon PostgreSQL · <code>jdonloder_auth</code></p>

  <h2>Users Table <span class="count"><?= count($users) ?> rows</span></h2>
  <div class="card">
    <table>
      <tr><th>ID</th><th>Username</th><th>Password</th><th>Created At</th><th>Action</th></tr>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
        <td><?= htmlspecialchars($u['password']) ?></td>
        <td><?= htmlspecialchars($u['created_at']) ?></td>
        <td>
          <?php if ($u['username'] !== 'admin'): ?>
          <form method="POST" style="display:inline" onsubmit="return confirm('Delete user <?= htmlspecialchars($u['username']) ?>?')">
            <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
            <button type="submit" class="btn-del">Delete</button>
          </form>
          <?php else: ?>
          <span style="color:#6B8FA8; font-size:0.72rem">Protected</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <h2>Login History <span class="count">Last 50</span></h2>
  <div class="card">
    <table>
      <tr><th>ID</th><th>Username</th><th>Success</th><th>IP Address</th><th>User Agent</th><th>Login At</th></tr>
      <?php foreach ($history as $h): ?>
      <tr>
        <td><?= $h['id'] ?></td>
        <td><?= htmlspecialchars($h['username']) ?></td>
        <td class="success-<?= $h['success'] ?>"><?= $h['success'] === 'true' ? '✅ Yes' : '❌ No' ?></td>
        <td><?= htmlspecialchars($h['ip_address']) ?></td>
        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($h['user_agent']) ?>"><?= htmlspecialchars($h['user_agent']) ?></td>
        <td><?= htmlspecialchars($h['login_at']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <a href="/" class="back">← Back to Login</a>
</div>
</body>
</html>
