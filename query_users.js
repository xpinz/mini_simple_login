/**
 * query_users.js
 * Run: node query_users.js
 * Prints all rows from the users table to the console.
 */
require('dotenv').config();
const { Pool } = require('pg');

const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  ssl: { rejectUnauthorized: false }
});

(async () => {
  try {
    console.log('\n📋 Users Table\n' + '─'.repeat(60));
    const { rows: users } = await pool.query(
      'SELECT id, username, password, created_at FROM users ORDER BY id DESC'
    );
    console.table(users);

    console.log('\n📜 Login History (last 20)\n' + '─'.repeat(60));
    const { rows: history } = await pool.query(
      'SELECT id, username, success, ip_address, login_at FROM login_history ORDER BY id DESC LIMIT 20'
    );
    console.table(history);
  } catch (err) {
    console.error('❌ DB Error:', err.message);
  } finally {
    await pool.end();
  }
})();
