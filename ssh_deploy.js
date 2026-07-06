const { Client } = require('ssh2');
const conn = new Client();

const COMMANDS = `
set -e
echo "[1/6] Current PM2 list..."
pm2 list || true

echo "[2/6] Deleting PM2 id=0 and named process..."
pm2 delete 0 2>/dev/null && echo "Deleted id=0" || echo "id=0 not found, skipping"
pm2 delete mini_simple_login 2>/dev/null && echo "Deleted mini_simple_login" || echo "Not found, skipping"

echo "[3/6] Checking project directory..."
if [ -d /var/www/mini_simple_login ]; then
  echo "Directory exists, pulling latest..."
  cd /var/www/mini_simple_login && git pull origin master
else
  echo "Cloning fresh..."
  mkdir -p /var/www && cd /var/www && git clone https://github.com/xpinz/mini_simple_login.git && cd mini_simple_login
fi

echo "[4/6] Installing dependencies..."
cd /var/www/mini_simple_login && npm install --omit=dev

echo "[5/6] Creating .env if missing..."
if [ ! -f /var/www/mini_simple_login/.env ]; then
  echo 'DATABASE_URL=postgresql://neondb_owner:npg_PtZKR8Uk1Tbr@ep-billowing-cell-aojlsjmr-pooler.c-2.ap-southeast-1.aws.neon.tech/jdonloder_auth?sslmode=require&channel_binding=require' > /var/www/mini_simple_login/.env
  echo 'PORT=3000' >> /var/www/mini_simple_login/.env
  echo ".env created"
else
  echo ".env already exists"
fi

echo "[6/6] Starting PM2 and saving..."
cd /var/www/mini_simple_login && pm2 start server.js --name mini_simple_login
pm2 save --force
pm2 list
echo "=== DEPLOY COMPLETE ==="
`.trim();

conn.on('ready', () => {
  console.log('✅ SSH Connected');
  conn.exec(COMMANDS, { pty: false }, (err, stream) => {
    if (err) { console.error('Exec error:', err); conn.end(); return; }
    stream
      .on('close', (code) => {
        console.log(`\n✅ Finished with exit code: ${code}`);
        conn.end();
      })
      .on('data', (data) => process.stdout.write(data.toString()))
      .stderr.on('data', (data) => process.stderr.write(data.toString()));
  });
}).on('error', (err) => {
  console.error('Connection error:', err.message);
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
