const { Client } = require('ssh2');
const conn = new Client();

// Fix the Nginx conflict: remove the old 'jdonloder' config that's hijacking the domain
// Then force-pull the latest code (stash local changes first)
const COMMANDS = `
echo "=== [1] Show conflicting Nginx configs ==="
echo "--- sites-enabled ---"
ls -la /etc/nginx/sites-enabled/
echo "--- conf.d ---"
ls -la /etc/nginx/conf.d/ 2>/dev/null || echo "(no conf.d)"

echo ""
echo "=== [2] Show content of OLD 'jdonloder' config ==="
cat /etc/nginx/sites-enabled/jdonloder 2>/dev/null || echo "Not found"

echo ""
echo "=== [3] Remove the conflicting old 'jdonloder' Nginx config ==="
rm -f /etc/nginx/sites-enabled/jdonloder
echo "Removed."

echo ""
echo "=== [4] Show our mini_simple_login Nginx config ==="
cat /etc/nginx/sites-enabled/mini_simple_login 2>/dev/null || cat /etc/nginx/sites-available/mini_simple_login 2>/dev/null || echo "Not found"

echo ""
echo "=== [5] Force-pull latest code (stash local changes first) ==="
cd /var/www/mini_simple_login
git stash
git pull origin master --ff-only

echo ""
echo "=== [6] Reload Nginx ==="
nginx -t && systemctl reload nginx && echo "Nginx reloaded OK" || echo "Nginx reload FAILED"

echo ""
echo "=== [7] PM2 restart with new code ==="
pm2 restart mini_simple_login
pm2 list

echo ""
echo "=== [8] Test app responds on port 3000 ==="
sleep 2
curl -s -o /dev/null -w "HTTP %{http_code}\\n" http://127.0.0.1:3000/

echo ""
echo "=== [9] Test via domain (HTTP) ==="
curl -s -o /dev/null -w "HTTP %{http_code}\\n" -H "Host: deexinventorymanagement.site" http://127.0.0.1/ 2>/dev/null || echo "Could not test"

echo ""
echo "=== DONE - site should now be live ==="
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
