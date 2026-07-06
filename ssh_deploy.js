const { Client } = require('ssh2');
const conn = new Client();

const COMMANDS = `
echo "=== [1] Pull latest code ==="
cd /var/www/mini_simple_login
git stash 2>/dev/null || true
git pull origin master --ff-only

echo ""
echo "=== [2] Check if PHP is installed ==="
php -v | head -1

echo ""
echo "=== [3] Check pdo_pgsql extension ==="
php -m | grep -i pgsql || echo "pdo_pgsql NOT found"

echo ""
echo "=== [4] Test PHP login API directly ==="
cd /var/www/mini_simple_login
php -r "
  require 'config.php';
  try {
    \\$db = getDB();
    \\$r = \\$db->query('SELECT COUNT(*) as c FROM users')->fetch();
    echo 'DB OK: ' . \\$r['c'] . ' users' . PHP_EOL;
  } catch (Exception \\$e) {
    echo 'DB ERROR: ' . \\$e->getMessage() . PHP_EOL;
  }
"

echo ""
echo "=== [5] Restart PM2 (Node.js still handles HTTP on this VPS) ==="
pm2 restart mini_simple_login 2>/dev/null || pm2 start server.js --name mini_simple_login
pm2 list

echo ""
echo "=== [6] Configure Nginx to also serve PHP files ==="
# Check if nginx already has PHP support for this site
grep -c "php" /etc/nginx/sites-available/mini_simple_login 2>/dev/null || echo "No PHP config in nginx yet"

echo ""
echo "=== DONE ==="
`.trim();

conn.on('ready', () => {
  console.log('✅ SSH Connected');
  conn.exec(COMMANDS, { pty: false }, (err, stream) => {
    if (err) { console.error('Exec error:', err); conn.end(); return; }
    stream
      .on('close', (code) => {
        console.log(`\n✅ Finished (exit code: ${code})`);
        conn.end();
      })
      .on('data', (data) => process.stdout.write(data.toString()))
      .stderr.on('data', (data) => process.stderr.write(data.toString()));
  });
}).on('error', (err) => {
  console.error('❌ Connection error:', err.message);
}).connect({
  host: '202.10.40.9',
  port: 22,
  username: 'root',
  password: 'T$PZE!zF8eO3GT',
  readyTimeout: 20000,
  algorithms: {
    serverHostKey: ['ssh-rsa', 'ecdsa-sha2-nistp256', 'ssh-ed25519']
  }
});
