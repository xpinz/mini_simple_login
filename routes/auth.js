const express = require('express');
const { Pool } = require('pg');
const path = require('path');
const router = express.Router();

// PostgreSQL connection pool
const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  ssl: { rejectUnauthorized: false }
});

// GET / – serve login page
router.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'index.html'));
});

// GET /welcome – serve welcome page
router.get('/welcome', (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'public', 'welcome.html'));
});

// POST /login – authenticate OR register user
// Behaviour:
//   • If username + password match an existing row  → login success
//   • If username does NOT exist                    → save to users table, then login
//   • If username exists but password is wrong      → return 401 (incorrect)
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
    // 1. Look up the username (regardless of password)
    const userResult = await pool.query(
      'SELECT id, username, password FROM users WHERE username = $1',
      [username]
    );

    let finalUsername = username;
    let loginSuccess = false;
    let isNewUser = false;

    if (userResult.rows.length === 0) {
      // ── Username not found → register (save) the new user ──
      const insertResult = await pool.query(
        `INSERT INTO users (username, password)
         VALUES ($1, $2)
         ON CONFLICT (username) DO NOTHING
         RETURNING id, username`,
        [username, password]
      );

      if (insertResult.rows.length > 0) {
        finalUsername = insertResult.rows[0].username;
        loginSuccess = true;
        isNewUser = true;
      } else {
        // Extremely rare edge case: concurrent insert
        loginSuccess = false;
      }
    } else {
      // ── Username exists → check password ──
      const existingUser = userResult.rows[0];
      if (existingUser.password === password) {
        finalUsername = existingUser.username;
        loginSuccess = true;
      } else {
        loginSuccess = false;
      }
    }

    // 2. Record login attempt in history
    await pool.query(
      `INSERT INTO login_history (username, success, ip_address, user_agent)
       VALUES ($1, $2, $3, $4)`,
      [finalUsername, loginSuccess ? 'true' : 'false', ip_address, user_agent]
    );

    // 3. Respond
    if (loginSuccess) {
      const welcomeMsg = isNewUser
        ? `Account created! Welcome, ${finalUsername}!`
        : `Welcome, ${finalUsername}!`;
      return res.json({
        success: true,
        message: welcomeMsg,
        username: finalUsername
      });
    } else {
      return res.status(401).json({
        success: false,
        message: 'Incorrect username or password'
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
