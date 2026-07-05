# 🚀 Deployment Guide
## Host `mini_simple_login` on `deexinventorymanagement.site`
### Server: Rumahweb VPS via SSH

---

## Prerequisites Checklist

Before you start, confirm these on your server:
- [ ] Node.js ≥ 18 installed
- [ ] npm installed
- [ ] PM2 installed (process manager)
- [ ] Nginx installed
- [ ] Git installed
- [ ] Domain `deexinventorymanagement.site` pointing to `202.10.40.9`

---

## Step 1 – Connect to Your Server via SSH

Open **PowerShell** or **Terminal** on your local machine:

```bash
ssh root@202.10.40.9
# Enter password when prompted: T$PZE!zF8eO3GT
```

> **Tip:** You can also use **PuTTY** on Windows if you prefer a GUI SSH client.

---

## Step 2 – Install Node.js (if not already installed)

```bash
# Check if Node is installed
node -v

# If not installed, use NodeSource for Ubuntu/Debian:
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify
node -v    # should show v20.x.x
npm -v     # should show 10.x.x
```

---

## Step 3 – Install PM2 (Process Manager)

```bash
npm install -g pm2
pm2 -v   # verify installation
```

---

## Step 4 – Clone the Repository

```bash
# Navigate to your web directory
cd /var/www

# Clone your GitHub repo
git clone https://github.com/xpinz/mini_simple_login.git

# Enter the project folder
cd mini_simple_login
```

---

## Step 5 – Create the `.env` File on the Server

> ⚠️ The `.env` file is NOT pushed to GitHub (it's gitignored). You must create it manually on the server.

```bash
nano .env
```

Paste the following content (use your Neon credentials):

```
DATABASE_URL=postgresql://neondb_owner:npg_PtZKR8Uk1Tbr@ep-billowing-cell-aojlsjmr-pooler.c-2.ap-southeast-1.aws.neon.tech/jdonloder_auth?sslmode=require&channel_binding=require
PORT=3000
```

Save and exit: `Ctrl + X` → `Y` → `Enter`

---

## Step 6 – Install Dependencies

```bash
npm install --omit=dev
```

---

## Step 7 – Start the App with PM2

```bash
# Start the app
pm2 start server.js --name "mini_simple_login"

# Save PM2 process list (survives reboots)
pm2 save

# Enable PM2 to start on system boot
pm2 startup
# ↑ Copy and run the command it prints!

# Check app is running
pm2 status
pm2 logs mini_simple_login
```

You should see:
```
✅ Server running at http://localhost:3000
```

---

## Step 8 – Configure Nginx as Reverse Proxy

This maps your domain to the Node.js app.

```bash
# Create a new Nginx config file
sudo nano /etc/nginx/sites-available/mini_simple_login
```

Paste the following:

```nginx
server {
    listen 80;
    server_name deexinventorymanagement.site www.deexinventorymanagement.site;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        proxy_pass         http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header   Upgrade $http_upgrade;
        proxy_set_header   Connection 'upgrade';
        proxy_set_header   Host $host;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}
```

Save and exit: `Ctrl + X` → `Y` → `Enter`

```bash
# Enable the config
sudo ln -s /etc/nginx/sites-available/mini_simple_login /etc/nginx/sites-enabled/

# Test Nginx config for syntax errors
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## Step 9 – Enable HTTPS with Let's Encrypt (SSL)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate for your domain
sudo certbot --nginx -d deexinventorymanagement.site -d www.deexinventorymanagement.site

# Follow the prompts:
#  - Enter your email address
#  - Agree to terms of service (A)
#  - Choose redirect HTTP to HTTPS (2) ← recommended

# Verify auto-renewal works
sudo certbot renew --dry-run
```

After this, your app will be accessible at:
**https://deexinventorymanagement.site/**

---

## Step 10 – Verify Everything Works

```bash
# Check PM2 status
pm2 status

# Check Nginx status
sudo systemctl status nginx

# Check logs
pm2 logs mini_simple_login --lines 20
```

Open your browser and visit:
- **https://deexinventorymanagement.site/** → Login page appears
- Login with `admin` / `admin123` → Welcome page appears
- Login with wrong credentials → Error message alternates black/red

---

## Updating the App in the Future

When you push new code to GitHub, update your server like this:

```bash
cd /var/www/mini_simple_login
git pull origin master
npm install --omit=dev
pm2 restart mini_simple_login
```

---

## Troubleshooting

| Problem | Solution |
|---|---|
| Port 3000 already in use | `pm2 delete mini_simple_login` then restart |
| Nginx 502 Bad Gateway | App not running — check `pm2 status` |
| SSL certificate error | Run `sudo certbot renew` |
| DB connection error | Check `.env` has correct `DATABASE_URL` |
| Can't SSH | Verify IP `202.10.40.9` and password |

---

## Quick Reference

| Item | Value |
|---|---|
| Server IP | `202.10.40.9` |
| App Directory | `/var/www/mini_simple_login` |
| App Port | `3000` |
| GitHub Repo | https://github.com/xpinz/mini_simple_login |
| Live URL | https://deexinventorymanagement.site/ |
| PM2 App Name | `mini_simple_login` |
| Test credentials | `admin` / `admin123` |
