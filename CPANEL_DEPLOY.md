# 🚀 Deploy Tutorial: DEX Management Inventory
## Rumahweb cPanel → File Manager / Node.js App

---

> [!IMPORTANT]
> **Node.js apps CANNOT run directly from `public_html` like PHP or static HTML.**
> Node.js requires a process manager. In cPanel, this is handled by **"Setup Node.js App"** (Phusion Passenger).
> This tutorial covers the correct method to deploy via cPanel on Rumahweb.

---

## 📋 Prerequisites

Before you start, make sure you have:
- [ ] Active Rumahweb hosting account with **cPanel access**
- [ ] Domain pointed to your hosting (e.g. `deexinventorymanagement.site`)
- [ ] Files ready: download/clone `https://github.com/xpinz/mini_simple_login`
- [ ] Your Neon `DATABASE_URL` connection string

---

## Method A – cPanel Node.js App (Recommended)

This is the proper way to run a Node.js Express app on cPanel hosting.

---

### Step 1 – Log in to cPanel

1. Open your browser → go to: `https://deexinventorymanagement.site:2083`
   *(or use the link Rumahweb sent in your welcome email)*
2. Enter your **cPanel username** and **password**
3. You are now inside the cPanel dashboard

---

### Step 2 – Open "Setup Node.js App"

1. In the cPanel dashboard, scroll to the **"Software"** section
2. Click **"Setup Node.js App"**

   > If you don't see "Setup Node.js App", your hosting plan may not support Node.js.
   > Contact Rumahweb support and ask: *"Does my plan support Node.js via Phusion Passenger?"*

---

### Step 3 – Create a New Node.js Application

Click the **"Create Application"** button (top right). Fill in the form:

| Field | Value |
|---|---|
| **Node.js version** | `18` (or highest available) |
| **Application mode** | `Production` |
| **Application root** | `mini_simple_login` *(folder name in your home directory)* |
| **Application URL** | Select your domain: `deexinventorymanagement.site` |
| **Application startup file** | `server.js` |

Click **"Create"** to save.

---

### Step 4 – Upload Project Files via File Manager

1. Go back to cPanel home → click **"File Manager"**
2. Navigate to your **home directory** (e.g. `/home/yourusername/`)
3. You should see a folder called `mini_simple_login` that was created in Step 3

   > ⚠️ Do NOT use `public_html` — put the app files in the `mini_simple_login` folder created by Node.js App setup.

4. **Upload these files** into `mini_simple_login/`:

   Go to **File Manager → Upload** button, then upload the following files:
   
   | File | Upload? |
   |---|---|
   | `server.js` | ✅ Yes |
   | `package.json` | ✅ Yes |
   | `routes/auth.js` | ✅ Yes (create `routes/` subfolder first) |
   | `public/index.html` | ✅ Yes (create `public/` subfolder first) |
   | `public/style.css` | ✅ Yes |
   | `public/script.js` | ✅ Yes |
   | `public/welcome.html` | ✅ Yes |
   | `.env` | ✅ Yes (see Step 5) |
   | `node_modules/` | ❌ No — install via terminal (Step 6) |
   | `.git/` | ❌ No |
   | `ssh_deploy.js` | ❌ No |

#### How to create subfolders in File Manager:
1. Click **"+ Folder"** (top left toolbar)
2. Type `routes` → Create
3. Enter the `routes` folder → Upload → select `auth.js`
4. Go back → create `public` folder → Upload `index.html`, `style.css`, `script.js`, `welcome.html`

---

### Step 5 – Create the `.env` File

1. In File Manager, navigate into the `mini_simple_login` folder
2. Click **"+ File"** → name it `.env` → Create
3. Right-click the `.env` file → **Edit**
4. Paste the following content:

```
DATABASE_URL=postgresql://neondb_owner:npg_PtZKR8Uk1Tbr@ep-billowing-cell-aojlsjmr-pooler.c-2.ap-southeast-1.aws.neon.tech/jdonloder_auth?sslmode=require&channel_binding=require
PORT=3000
```

5. Click **Save Changes**

> [!CAUTION]
> Never share your `.env` file or commit it to GitHub. It contains your database credentials.

---

### Step 6 – Install Dependencies via cPanel Terminal

> [!NOTE]
> You need to run `npm install` to install Node.js packages. Use the cPanel Terminal for this.

1. In cPanel, go to **"Advanced"** section → click **"Terminal"**
   *(If Terminal is not available, use SSH: `ssh yourusername@deexinventorymanagement.site`)*

2. In the terminal, run:

```bash
# Go to your app folder
cd ~/mini_simple_login

# Install packages listed in package.json
npm install --omit=dev

# Verify node_modules was created
ls node_modules | head -5
```

You should see folders like `express`, `pg`, `dotenv` in `node_modules/`.

---

### Step 7 – Start the Application

1. Go back to cPanel → **"Setup Node.js App"**
2. Find your application `mini_simple_login` in the list
3. Click the **▶ Start** button (or **Restart** if already started)
4. Status should show: **"Running"**

---

### Step 8 – Verify It's Working

1. Open your browser → visit: **`https://deexinventorymanagement.site/`**
2. You should see the DEX Management Inventory login page
3. Test login with `admin` / `admin123` → should reach Welcome page
4. Test with wrong credentials → should show "username or password incorrect"

---

### Step 9 – Set Up HTTPS (if not already active)

1. In cPanel, scroll to **"Security"** section → click **"SSL/TLS Status"**
2. Find your domain → click **"Run AutoSSL"**
3. Wait a few minutes → refresh the page
4. Your site should now have a green padlock at `https://deexinventorymanagement.site/`

---

## Method B – File Manager `public_html` (Static Fallback Only)

> [!WARNING]
> This method does **NOT** support the Node.js backend. The login form will not connect to PostgreSQL.
> Use this ONLY to test the visual appearance of the frontend.

If you only want to preview the UI design as a static page:

1. cPanel → **File Manager** → open `public_html/`
2. Upload only:
   - `public/index.html` → rename to `index.html` in `public_html/`
   - `public/style.css` → upload as `style.css`
   - `public/script.js` → upload as `script.js`
   - `public/welcome.html` → upload as `welcome.html`
3. Visit `https://deexinventorymanagement.site/`

⚠️ Clicking "Sign In" will return a network error since there is no backend.

---

## ♻️ Updating the App (After Code Changes)

When you push new code to GitHub and want to update cPanel:

### Option A – Re-upload via File Manager
1. Download the changed files from GitHub
2. File Manager → navigate to `~/mini_simple_login/`
3. Delete the old file → Upload the new version
4. cPanel → Setup Node.js App → **Restart** the app

### Option B – Use cPanel Terminal (Faster)
```bash
cd ~/mini_simple_login
git pull origin master       # pull latest from GitHub
npm install --omit=dev       # update packages if needed
```
Then go to **Setup Node.js App → Restart**.

---

## 🔧 Troubleshooting

| Problem | Cause | Solution |
|---|---|---|
| "Setup Node.js App" not visible | Plan doesn't support Node.js | Contact Rumahweb support |
| App shows "503 Service Unavailable" | App not started or crashed | Setup Node.js App → Start/Restart |
| "Cannot find module 'express'" | npm install not run | Run `npm install` in Terminal |
| DB connection error | Wrong `.env` or file missing | Check `.env` content, restart app |
| Login form submits but nothing happens | `server.js` or `routes/auth.js` missing | Re-upload missing files |
| Site shows old page | Browser cache | Press `Ctrl + Shift + R` (hard refresh) |
| HTTPS not working | SSL not configured | cPanel → SSL/TLS → Run AutoSSL |

---

## 📁 Final Folder Structure on cPanel Server

```
/home/yourusername/
├── public_html/          ← Default website (other sites, NOT our app)
│   └── (other files)
└── mini_simple_login/    ← Our Node.js app lives HERE
    ├── .env              ← DB credentials
    ├── server.js         ← App entry point
    ├── package.json
    ├── node_modules/     ← Installed by npm install
    ├── routes/
    │   └── auth.js
    └── public/
        ├── index.html
        ├── style.css
        ├── script.js
        └── welcome.html
```

---

## 📞 Rumahweb Support

If you encounter issues specific to Rumahweb's cPanel configuration:
- **Live Chat:** https://www.rumahweb.com
- **Ticket:** Login to Rumahweb client area → Support → New Ticket
- **Ask:** *"I need to run a Node.js Express app on my hosting. Is Phusion Passenger / Node.js App Setup enabled on my account?"*
