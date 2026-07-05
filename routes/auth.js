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

// POST /login – authenticate user
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
    // Check credentials (plain text as per schema)
    const result = await pool.query(
      'SELECT id, username FROM users WHERE username = $1 AND password = $2',
      [username, password]
    );

    const success = result.rows.length > 0;
    const loggedUsername = success ? result.rows[0].username : username;

    // Record login attempt in history
    await pool.query(
      `INSERT INTO login_history (username, success, ip_address, user_agent)
       VALUES ($1, $2, $3, $4)`,
      [loggedUsername, success ? 'true' : 'false', ip_address, user_agent]
    );

    if (success) {
      return res.json({
        success: true,
        message: `Welcome, ${result.rows[0].username}!`,
        username: result.rows[0].username
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
