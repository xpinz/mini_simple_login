# 📋 Tutorial: View the `users` Table in Neon PostgreSQL

There are **3 ways** to view your users table. Choose whichever suits you best.

---

## Method 1 – Neon Web Console (Easiest, No Setup)

1. Open your browser and go to: **https://console.neon.tech**
2. Sign in with your Neon account
3. Select your project: **`jdonloder_auth`**
4. Click **"SQL Editor"** in the left sidebar
5. Paste and run this query:

```sql
SELECT id, username, password, created_at
FROM users
ORDER BY id DESC;
```

You'll see all registered users in a table format directly in the browser.

---

## Method 2 – Neon Web Console: Table Explorer (Even Easier)

1. Go to **https://console.neon.tech**
2. Left sidebar → **"Tables"**
3. Select database: `jdonloder_auth`
4. Select schema: `public`
5. Click on the **`users`** table

You'll see rows with pagination — no SQL required.

---

## Method 3 – Via SSH on Your Server (psql terminal)

SSH into your server first:

```bash
ssh root@202.10.40.9
# Password: T$PZE!zF8eO3GT
```

Then connect to the Neon DB using `psql`:

```bash
psql "postgresql://neondb_owner:npg_PtZKR8Uk1Tbr@ep-billowing-cell-aojlsjmr-pooler.c-2.ap-southeast-1.aws.neon.tech/jdonloder_auth?sslmode=require"
```

Once inside the `psql` prompt, run:

```sql
-- View all users
SELECT id, username, password, created_at FROM users ORDER BY id DESC;

-- Count how many users
SELECT COUNT(*) FROM users;

-- View login history
SELECT * FROM login_history ORDER BY id DESC LIMIT 20;

-- Delete a specific user
DELETE FROM users WHERE username = 'unwanted_user';

-- Exit psql
\q
```

> **Tip:** If `psql` is not installed, install it:
> ```bash
> sudo apt-get install -y postgresql-client
> ```

---

## Method 4 – From Your Local Machine (Windows PowerShell)

Install `psql` locally or use this PowerShell snippet to query via Node.js:

Create a quick file `query_users.js` in your project:

```js
require('dotenv').config();
const { Pool } = require('pg');
const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  ssl: { rejectUnauthorized: false }
});
(async () => {
  const { rows } = await pool.query(
    'SELECT id, username, password, created_at FROM users ORDER BY id DESC'
  );
  console.table(rows);
  await pool.end();
})();
```

Then run it from your project folder:

```powershell
node query_users.js
```

You'll see a nicely formatted table in your terminal.

---

## Quick Reference Queries

| Purpose | SQL |
|---|---|
| View all users | `SELECT * FROM users ORDER BY id DESC;` |
| View latest 10 | `SELECT * FROM users ORDER BY id DESC LIMIT 10;` |
| Find a user | `SELECT * FROM users WHERE username = 'admin';` |
| Count users | `SELECT COUNT(*) FROM users;` |
| View login history | `SELECT * FROM login_history ORDER BY id DESC LIMIT 20;` |
| Delete a user | `DELETE FROM users WHERE username = 'testuser';` |
| Delete all test users | `DELETE FROM users WHERE username LIKE 'newuser_%';` |
