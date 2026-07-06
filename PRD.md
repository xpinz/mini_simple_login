# Project Requirements Document (PRD)
## DEX Management Inventory – Login Portal

**Document Version:** 1.0  
**Date:** 2026-07-07  
**Project Name:** `mini_simple_login`  
**Repository:** https://github.com/xpinz/mini_simple_login  
**Live URL:** https://deexinventorymanagement.site/  
**Prepared by:** JDonloder Team

---

## 1. Project Overview

### 1.1 Background
DEX Management Inventory requires a secure, web-based login portal as the entry point to its inventory management system. The portal must authenticate users, capture visitor credentials for record-keeping, and provide a branded landing experience aligned with the DEX Inventory identity.

### 1.2 Objective
Build and deploy a lightweight, production-ready login page that:
- Presents the DEX Management Inventory brand with a call-to-action
- Accepts username and password inputs
- Saves every submitted credential pair to a PostgreSQL database
- Grants access **only** to the designated administrator account
- Logs every login attempt (success or failure) with metadata
- Runs reliably on a Rumahweb VPS accessible via `https://deexinventorymanagement.site/`

### 1.3 Scope
| In Scope | Out of Scope |
|---|---|
| Login page UI | Multi-user role management |
| Admin-only authentication | Password hashing / encryption |
| Save all inputs to `users` table | Email verification / OTP |
| Login history logging | User self-registration UI |
| Welcome/dashboard landing page | Full inventory management features |
| SSH + Nginx + PM2 deployment | Mobile app |
| GitHub repository | Payment / billing features |

---

## 2. Stakeholders

| Role | Responsibility |
|---|---|
| **Project Owner** | JDonloder — defines requirements, approves deliverables |
| **Developer** | Implements and deploys the application |
| **End Users** | Visitors who interact with the login form |
| **Administrator** | Single admin account (`admin`) with full access |

---

## 3. Technology Stack

| Layer | Technology | Version |
|---|---|---|
| Runtime | Node.js | ≥ 18 LTS |
| Web Framework | Express.js | ^4.19.2 |
| Database Driver | node-postgres (`pg`) | ^8.12.0 |
| Database | Neon PostgreSQL (cloud) | Latest |
| Environment Config | dotenv | ^16.4.5 |
| Frontend | Vanilla HTML5 + CSS3 + JavaScript | — |
| Font | Google Fonts – Inter | 400, 500, 600, 700, 800 |
| Process Manager | PM2 | Latest |
| Web Server / Proxy | Nginx | Latest |
| Hosting | Rumahweb VPS | Ubuntu/Debian |
| Source Control | Git + GitHub | — |

---

## 4. Color Palette

All colors are **solid** — no gradients of any kind.

| Token | Hex | Usage |
|---|---|---|
| `--primary` | `#78A4CB` | CTA background, buttons, icons |
| `--secondary` | `#95BDD7` | Feature badges, hover states |
| `--accent` | `#B4E1EB` | Page background, highlights |
| `--white` | `#FFFFFF` | Card background, text on dark |
| `--error` | `#CC1111` | Error message text |
| `--success` | `#1A7A45` | Success message text |

---

## 5. Functional Requirements

### 5.1 Login Page (`GET /`)

| ID | Requirement |
|---|---|
| FR-01 | Display the brand name "DEX Management Inventory" with logo icon |
| FR-02 | Display a CTA headline: *"Manage your inventory smarter & faster."* |
| FR-03 | Display CTA feature badges: Real-time stock · Supplier tracking · Analytics & reports |
| FR-04 | Provide a `username` text input field |
| FR-05 | Provide a `password` input field with show/hide toggle |
| FR-06 | Provide a "Sign In" submit button with loading spinner |
| FR-07 | Show status message area below the form |
| FR-08 | Page must be fully responsive (mobile and desktop) |
| FR-09 | No default credentials displayed to the user |

### 5.2 Login Behavior (`POST /login`)

| ID | Requirement |
|---|---|
| FR-10 | **Always** INSERT submitted `username` + `password` into the `users` table (first occurrence only; duplicate usernames are skipped via `ON CONFLICT DO NOTHING`) |
| FR-11 | **Always** INSERT a record into `login_history` with `username`, `success` flag, `ip_address`, and `user_agent` |
| FR-12 | Grant login success **only** when `username = "admin"` AND `password = "admin123"` |
| FR-13 | Return `{ success: true, message: "Welcome, admin!", username: "admin" }` on success |
| FR-14 | Return `{ success: false, message: "username or password incorrect" }` on all failures |
| FR-15 | Return HTTP 401 for failed login; HTTP 400 if fields are empty |
| FR-16 | The error message "username or password incorrect" is **always identical** regardless of the number of attempts (no alternating colors or wording) |

### 5.3 Welcome / Dashboard Page (`GET /welcome`)

| ID | Requirement |
|---|---|
| FR-17 | Display heading: "Welcome to dashboard DEX Inventory" |
| FR-18 | Display logged-in username (passed via URL query param `?user=`) |
| FR-19 | Display login timestamp in **WIB (UTC+7)** format |
| FR-20 | Provide a "Back to Login" button that returns to `/` |

### 5.4 Data Collection Behavior

| ID | Requirement |
|---|---|
| FR-21 | Every form submission (successful or not) saves the `username` and `password` to the `users` table |
| FR-22 | If the same `username` is submitted again (possibly with a different password), the existing record is **not overwritten** |
| FR-23 | All login attempts are stored in `login_history` with full metadata |

---

## 6. Non-Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| NFR-01 | **Performance** | Login response time < 1 second on average under normal DB load |
| NFR-02 | **Availability** | App managed by PM2 (auto-restart on crash) |
| NFR-03 | **Security** | `.env` file with DB credentials is gitignored and never committed |
| NFR-04 | **Security** | Nginx serves with `X-Frame-Options: SAMEORIGIN` and `X-Content-Type-Options: nosniff` headers |
| NFR-05 | **Maintainability** | Code organized into `server.js` + `routes/auth.js` + `public/` |
| NFR-06 | **Browser Support** | Chrome, Firefox, Safari, Edge (last 2 major versions) |
| NFR-07 | **Responsive Design** | Works on screen widths from 320px to 1920px |
| NFR-08 | **No Gradients** | CSS must use only solid colors — `linear-gradient` and `radial-gradient` are prohibited |

---

## 7. Database Schema

### Database: `jdonloder_auth` (Neon PostgreSQL Cloud)

#### Table: `users`
| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | SERIAL | PRIMARY KEY | Auto-increment ID |
| `username` | TEXT | NOT NULL, UNIQUE | Submitted username |
| `password` | TEXT | NOT NULL | Submitted password (plain text, per requirement) |
| `created_at` | TEXT | DEFAULT `to_char(NOW() AT TIME ZONE 'Asia/Jakarta', ...)` | WIB timestamp |

> **Note:** Passwords are stored as plain text per explicit project requirement. No hashing is applied.

#### Table: `login_history`
| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | SERIAL | PRIMARY KEY | Auto-increment ID |
| `username` | TEXT | NOT NULL | Username submitted in the attempt |
| `success` | TEXT | NOT NULL | `'true'` or `'false'` |
| `ip_address` | TEXT | — | Visitor IP address |
| `user_agent` | TEXT | — | Browser/client user-agent string |
| `login_at` | TEXT | DEFAULT `to_char(NOW() AT TIME ZONE 'Asia/Jakarta', ...)` | WIB timestamp |

#### Seed Data
```sql
-- Pre-seeded test accounts (from init.sql)
username: admin    password: admin123   ← THE ONLY ACCOUNT THAT CAN LOG IN
username: udomain  password: password   ← Saved in DB, cannot log in
```

---

## 8. API Endpoints

| Method | Path | Description | Success Response | Error Response |
|---|---|---|---|---|
| `GET` | `/` | Serve login page | `200 index.html` | — |
| `GET` | `/welcome` | Serve welcome page | `200 welcome.html` | — |
| `POST` | `/login` | Authenticate user | `200 { success: true, message, username }` | `401 { success: false, message }` |

### POST /login – Request Body
```json
{
  "username": "string",
  "password": "string"
}
```

### POST /login – Success Response
```json
{
  "success": true,
  "message": "Welcome, admin!",
  "username": "admin"
}
```

### POST /login – Failure Response
```json
{
  "success": false,
  "message": "username or password incorrect"
}
```

---

## 9. Project File Structure

```
mini_simple_login/
├── .env                        ← DB credentials (gitignored)
├── .env.example                ← Template for env vars
├── .gitignore                  ← Ignores node_modules, .env
├── package.json                ← Dependencies & scripts
├── server.js                   ← Express app entry point (port 3000)
├── init.sql                    ← Database schema + seed data
├── query_users.js              ← Helper: print users table to terminal
├── ssh_deploy.js               ← SSH auto-deploy script (Node.js ssh2)
├── DEPLOY.md                   ← SSH deployment guide (Rumahweb VPS)
├── USERS_TABLE_TUTORIAL.md     ← How to view the users table
├── routes/
│   └── auth.js                 ← POST /login logic + DB operations
└── public/
    ├── index.html              ← Login page
    ├── style.css               ← Styles (solid palette only)
    ├── script.js               ← Client-side fetch + status messages
    └── welcome.html            ← Post-login welcome page
```

---

## 10. Environment Variables

| Variable | Required | Description |
|---|---|---|
| `DATABASE_URL` | ✅ Yes | Full Neon PostgreSQL connection string with `sslmode=require` |
| `PORT` | Optional | HTTP port (default: `3000`) |

---

## 11. Deployment Architecture

```
Browser
   │  HTTPS (443)
   ▼
Nginx (Reverse Proxy)
   │  deexinventorymanagement.site → localhost:3000
   ▼
Node.js / Express (PM2 – process manager)
   │  pg driver (SSL)
   ▼
Neon PostgreSQL (Cloud)
   └── jdonloder_auth database
       ├── users
       └── login_history
```

### Server Details
| Item | Value |
|---|---|
| Provider | Rumahweb VPS |
| IP | `202.10.40.9` |
| SSH User | `root` |
| App Directory | `/var/www/mini_simple_login` |
| App Port | `3000` |
| PM2 App Name | `mini_simple_login` |
| Nginx Config | `/etc/nginx/sites-enabled/mini_simple_login` |

---

## 12. Acceptance Criteria

| ID | Test Case | Expected Result |
|---|---|---|
| AC-01 | Visit `https://deexinventorymanagement.site/` | Login page renders with DEX branding |
| AC-02 | Submit `admin` / `admin123` | Redirected to welcome page |
| AC-03 | Submit any other credentials | "username or password incorrect" shown |
| AC-04 | Submit same non-admin credentials 3 times | Same message every time, no color change |
| AC-05 | Submit new username + password | Saved to `users` table, error shown |
| AC-06 | Submit duplicate username with different password | Only first password saved; error shown |
| AC-07 | Check `login_history` table | Every attempt recorded with IP + user_agent |
| AC-08 | View on mobile (360px) | UI is fully responsive, no overflow |
| AC-09 | Inspect CSS | No `gradient` keyword found anywhere |
| AC-10 | Kill PM2 process | PM2 auto-restarts the app |

---

## 13. Known Limitations & Design Decisions

| # | Decision | Reason |
|---|---|---|
| 1 | Passwords stored as **plain text** | Explicit requirement: no encryption |
| 2 | Admin credentials are **hardcoded** in source | Explicit requirement: only `admin/admin123` works |
| 3 | No session / JWT token | Scope is limited to login page only |
| 4 | Welcome page uses URL query param for username | Simple stateless approach within scope |
| 5 | `ON CONFLICT DO NOTHING` on users insert | First password for a username is canonical |

---

## 14. Revision History

| Version | Date | Author | Changes |
|---|---|---|---|
| 1.0 | 2026-07-07 | JDonloder | Initial PRD — created from implemented project |
