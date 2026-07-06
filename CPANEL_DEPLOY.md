# 🚀 Deploy Tutorial: cPanel File Manager → public_html
## DEX Management Inventory (PHP version)

---

## Overview

This project is now **pure PHP** — no Node.js required. Upload files to `public_html` on Rumahweb cPanel and it works immediately.

---

## 📋 What You Need

- [ ] Rumahweb cPanel access
- [ ] Domain `deexinventorymanagement.site` pointed to hosting
- [ ] PHP ≥ 7.4 (usually pre-installed)
- [ ] **`pdo_pgsql` PHP extension** enabled (see Step 2)

---

## Files to Upload

These are the **only files** you need to upload into `public_html/`:

```
public_html/
├── .htaccess            ← Security rules (protect config.php)
├── config.php           ← Database credentials
├── index.html           ← Login page
├── style.css            ← Styles
├── script.js            ← Client-side login logic
├── welcome.html         ← Welcome page after login
├── check.php            ← Server diagnostic (DELETE after testing)
├── query_users.php      ← View users table (DELETE or protect in production)
└── api/
    └── login.php        ← Login API endpoint
```

---

## Step-by-Step Deployment

### Step 1 – Login to cPanel

1. Open: `https://deexinventorymanagement.site:2083`
   *(or the cPanel link from your Rumahweb welcome email)*
2. Enter your cPanel **username** and **password**

---

### Step 2 – Check if PostgreSQL Extension is Enabled

> [!IMPORTANT]
> PHP needs the `pdo_pgsql` extension to connect to Neon PostgreSQL.
> Most Rumahweb plans have it available but it might need to be enabled.

1. In cPanel, go to **"Software"** → **"Select PHP Version"**
2. Find `pdo_pgsql` and `pgsql` in the extensions list
3. **Check the checkbox** next to both to enable them
4. Click **"Save"** (or **"Apply"**)

If you don't see "Select PHP Version":
- Contact Rumahweb support: *"Please enable the pdo_pgsql extension for my PHP."*

---

### Step 3 – Open File Manager

1. In cPanel dashboard, click **"File Manager"**
2. Navigate to **`public_html`** folder
3. **Delete or backup** any old files in `public_html` (if it shows an old website)

---

### Step 4 – Create the `api/` Subfolder

1. Inside `public_html`, click **"+ Folder"** in the top toolbar
2. Name it: `api`
3. Click **"Create New Folder"**

---

### Step 5 – Upload Files

> [!TIP]
> You can download all files from GitHub:
> **https://github.com/xpinz/mini_simple_login**
> Click the green **"Code"** button → **"Download ZIP"** → extract on your computer.

#### Upload to `public_html/` (root level):

1. Click **"Upload"** in the top toolbar
2. Drag & drop or select these files:
   - `.htaccess`
   - `config.php`
   - `check.php`
   - `query_users.php`

3. Now upload the frontend files from the `public/` folder of the downloaded repo:
   - `public/index.html` → upload as `index.html`
   - `public/style.css` → upload as `style.css`
   - `public/script.js` → upload as `script.js`
   - `public/welcome.html` → upload as `welcome.html`

#### Upload to `public_html/api/`:

4. Navigate into the `api/` subfolder you created
5. Upload:
   - `api/login.php`

---

### Step 6 – Verify File Structure

After uploading, your `public_html` should look like this in File Manager:

```
public_html/
├── .htaccess           ✅
├── config.php          ✅
├── index.html          ✅
├── style.css           ✅
├── script.js           ✅
├── welcome.html        ✅
├── check.php           ✅ (delete later)
├── query_users.php     ✅ (delete later)
└── api/
    └── login.php       ✅
```

---

### Step 7 – Run the Server Check

1. Open your browser → go to:
   **`https://deexinventorymanagement.site/check.php`**

2. You should see a diagnostic page showing:

   | Check | Expected |
   |---|---|
   | PHP Version | ✅ 7.4+ or 8.x |
   | PDO Extension | ✅ Loaded |
   | PDO PostgreSQL Driver | ✅ Loaded |
   | Neon DB Connection | ✅ Connected – X users in table |

3. **If PDO PostgreSQL says ❌ Missing:**
   → Go back to Step 2 and enable `pdo_pgsql`
   → If it's not available, contact Rumahweb support

---

### Step 8 – Test the Login Page

1. Open: **`https://deexinventorymanagement.site/`**
2. You should see the DEX Management Inventory login page

3. **Test with correct credentials:**
   - Username: `admin`
   - Password: `admin123`
   - Expected: Redirects to Welcome page ✅

4. **Test with wrong credentials:**
   - Username: `anything`
   - Password: `anything`
   - Expected: "username or password incorrect" ✅

5. **Verify data was saved:**
   - Open: **`https://deexinventorymanagement.site/query_users.php`**
   - You should see the username you just typed in the Users Table

---

### Step 9 – Clean Up (Important!)

> [!CAUTION]
> Delete these files after confirming everything works.
> They expose your database to anyone who visits the URL.

1. Go to File Manager → `public_html`
2. **Delete** `check.php` (server diagnostics — no longer needed)
3. **Optionally delete** `query_users.php` (or password-protect it)

---

## ♻️ Updating Files in the Future

When you push new code to GitHub:

1. Download the updated files from GitHub
2. cPanel → File Manager → `public_html`
3. Delete the old file → Upload the new version
4. Refresh the browser (`Ctrl + Shift + R`)

That's it — no restart needed. PHP runs fresh on every request.

---

## 🔐 Optional: Password-Protect `query_users.php`

Instead of deleting `query_users.php`, you can password-protect it:

1. In cPanel, go to **"Security"** → **"Directory Privacy"**
2. Navigate to `public_html` → click the folder name
3. Check **"Password protect this directory"**
4. Set a username and password
5. Click Save

Or add this to `.htaccess` to protect just `query_users.php`:

```apache
<Files "query_users.php">
    AuthType Basic
    AuthName "Admin Only"
    AuthUserFile /home/yourusername/.htpasswd
    Require valid-user
</Files>
```

---

## 🔧 Troubleshooting

| Problem | Cause | Solution |
|---|---|---|
| 500 Internal Server Error | `.htaccess` syntax error or missing extension | Check cPanel Error Log; enable `pdo_pgsql` |
| "Server error" on login | Cannot connect to Neon DB | Check `config.php` credentials; verify `pdo_pgsql` is enabled |
| Login page loads but form does nothing | `script.js` not uploaded or wrong version | Re-upload `script.js` from `public/` folder |
| "username or password incorrect" always | Working as designed — only `admin/admin123` succeeds | This is the intended behaviour |
| Page shows old content | Browser cache | Press `Ctrl + Shift + R` (hard refresh) |
| 403 Forbidden | File permissions wrong | Set files to `644`, folders to `755` in File Manager |
| `config.php` accessible in browser | `.htaccess` not uploaded or not working | Re-upload `.htaccess`; check `mod_rewrite` is enabled |

---

## 📞 Rumahweb Support

If you need help enabling `pdo_pgsql`:
- **Live Chat:** https://www.rumahweb.com
- **Ticket:** Login to Rumahweb client area → Support → New Ticket
- **Ask:** *"Please enable the pdo_pgsql and pgsql PHP extensions on my account."*
