# 🚀 Deploy Tutorial: cPanel → public_html (PHP + MySQL)
## DEX Management Inventory

---

> [!IMPORTANT]
> This version uses **PHP + MySQL** — both are built into every Rumahweb cPanel.
> No Node.js, no external database, no port restrictions. Just upload and go.

---

## 📋 What You Need

- [ ] Rumahweb cPanel access
- [ ] Domain `deexinventorymanagement.site` pointed to hosting

That's it. PHP and MySQL are already installed on cPanel.

---

## Step 1 – Login to cPanel

Open: `https://deexinventorymanagement.site:2083`
*(or the link from your Rumahweb welcome email)*

Enter your cPanel **username** and **password**.

---

## Step 2 – Create MySQL Database

1. In cPanel, scroll to **"Databases"** section
2. Click **"MySQL® Databases"**

### 2a. Create the database:
1. Under "Create New Database", type: `dex_auth`
2. Click **"Create Database"**
3. The full name will be like: `cpaneluser_dex_auth` — **copy this name**

### 2b. Create a database user:
1. Under "MySQL Users → Add New User":
   - Username: `dexuser`
   - Password: Use the **Password Generator** or type a strong password
   - **Copy this password** somewhere safe
2. Click **"Create User"**
3. The full username will be like: `cpaneluser_dexuser` — **copy this name**

### 2c. Add user to database:
1. Under "Add User To Database":
   - Select user: `cpaneluser_dexuser`
   - Select database: `cpaneluser_dex_auth`
2. Click **"Add"**
3. On the next screen, check **"ALL PRIVILEGES"**
4. Click **"Make Changes"**

> [!TIP]
> Write down these 3 values — you'll need them for `config.php`:
> ```
> DB_NAME:     cpaneluser_dex_auth
> DB_USER:     cpaneluser_dexuser
> DB_PASSWORD: (the password you set)
> ```

---

## Step 3 – Import SQL Schema via phpMyAdmin

1. In cPanel, scroll to **"Databases"** section
2. Click **"phpMyAdmin"**
3. In the left sidebar, click your database name (`cpaneluser_dex_auth`)
4. Click the **"SQL"** tab at the top
5. Copy-paste the entire content below into the SQL box:

```sql
CREATE TABLE IF NOT EXISTS users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  username    VARCHAR(255) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  created_at  VARCHAR(50)  NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_history (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  username     VARCHAR(255) NOT NULL,
  success      VARCHAR(10)  NOT NULL,
  ip_address   VARCHAR(100) DEFAULT NULL,
  user_agent   TEXT         DEFAULT NULL,
  login_at     VARCHAR(50)  NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO users (username, password, created_at)
VALUES ('admin', 'admin123', NOW());
```

6. Click **"Go"** to execute
7. You should see: ✅ "3 queries executed successfully"

> **Note:** The triggers in `init_mysql.sql` are optional. The simplified SQL above works fine — timestamps will use MySQL's server time. If you need WIB timestamps, run the full `init_mysql.sql` file instead.

---

## Step 4 – Upload Files via File Manager

1. Go to cPanel home → click **"File Manager"**
2. Navigate to **`public_html`**
3. **Delete any old files** inside `public_html` (backup if needed)

### 4a. Create the `api/` subfolder:
1. Click **"+ Folder"** in top toolbar
2. Name: `api`
3. Click **"Create New Folder"**

### 4b. Download project files:
1. Go to **https://github.com/xpinz/mini_simple_login**
2. Click green **"Code"** button → **"Download ZIP"**
3. Extract the ZIP on your computer

### 4c. Upload to `public_html/` (root level):

Click **"Upload"** in File Manager and upload these files:

| From your computer | Upload to |
|---|---|
| `.htaccess` | `public_html/.htaccess` |
| `config.php` | `public_html/config.php` |
| `check.php` | `public_html/check.php` |
| `query_users.php` | `public_html/query_users.php` |
| `public/index.html` | `public_html/index.html` |
| `public/style.css` | `public_html/style.css` |
| `public/script.js` | `public_html/script.js` |
| `public/welcome.html` | `public_html/welcome.html` |

### 4d. Upload to `public_html/api/`:

1. Navigate into the `api/` folder
2. Upload: `api/login.php`

---

## Step 5 – Edit config.php

> [!CAUTION]
> This is the most important step. If config.php has wrong values, nothing works.

1. In File Manager, navigate to `public_html`
2. Right-click **`config.php`** → click **"Edit"**
3. Replace the placeholder values with your actual MySQL credentials:

```php
define('DB_HOST',     'localhost');              // keep as localhost
define('DB_NAME',     'cpaneluser_dex_auth');    // your actual DB name from Step 2
define('DB_USER',     'cpaneluser_dexuser');     // your actual DB user from Step 2
define('DB_PASSWORD', 'your_actual_password');   // the password from Step 2
```

4. Click **"Save Changes"**

---

## Step 6 – Run the Server Check

1. Open your browser → go to:
   **`https://deexinventorymanagement.site/check.php`**

2. You should see all green checkmarks:

| Check | Expected |
|---|---|
| PHP Version | ✅ 7.4+ or 8.x |
| PDO Extension | ✅ Loaded |
| PDO MySQL Driver | ✅ Loaded |
| MySQL Connection | ✅ Connected – 1 users in table |

3. If MySQL Connection shows ❌ error → double-check your `config.php` values

---

## Step 7 – Test the Login Page

1. Open: **`https://deexinventorymanagement.site/`**
2. You should see the DEX Management Inventory login page

### Test cases:

| Input | Expected Result |
|---|---|
| `admin` / `admin123` | ✅ Redirect to Welcome page |
| `anything` / `anything` | ❌ "username or password incorrect" |
| Same wrong input 3 times | ❌ Same message every time |

### Verify data was saved:
- Open: **`https://deexinventorymanagement.site/query_users.php`**
- You should see every username you typed in the Users Table

---

## Step 8 – Clean Up (Important!)

> [!CAUTION]
> Delete these files after confirming everything works.

1. File Manager → `public_html`
2. **Delete** `check.php`
3. **Delete** `query_users.php` *(or password-protect it)*

---

## ♻️ Updating Files in the Future

When you push new code to GitHub:

1. Download the updated files from GitHub
2. cPanel → File Manager → `public_html`
3. Delete the old file → Upload the new version
4. Refresh browser with `Ctrl + Shift + R`

No restart needed — PHP runs fresh on every request.

---

## 📁 Final File Structure

```
public_html/
├── .htaccess           ← Security rules
├── config.php          ← MySQL credentials (edit this!)
├── index.html          ← Login page
├── style.css           ← Styles
├── script.js           ← Client-side logic
├── welcome.html        ← Dashboard page
└── api/
    └── login.php       ← Login API endpoint
```

---

## 🔧 Troubleshooting

| Problem | Cause | Solution |
|---|---|---|
| 500 Internal Server Error | PHP error or bad `.htaccess` | Check cPanel → Error Log |
| "Server error" on login | Wrong MySQL credentials in `config.php` | Edit `config.php` with correct values |
| "SQLSTATE... Access denied" | Wrong DB user/password | Verify in cPanel → MySQL Databases |
| "Table doesn't exist" | Schema not imported | Run SQL in phpMyAdmin (Step 3) |
| Login page loads but form does nothing | `script.js` not uploaded | Re-upload from `public/` folder |
| Page shows old content | Browser cache | Press `Ctrl + Shift + R` |
| 403 Forbidden | File permissions | Set files to `644`, folders to `755` |
