-- ═══════════════════════════════════════════════════════
-- init.sql  –  JDonloder Auth Database Schema
-- Run once to create DB objects.
-- All columns use TEXT datatype as per requirement.
-- No password encryption per requirement.
-- ═══════════════════════════════════════════════════════

-- Create database (run separately as superuser if needed):
 CREATE DATABASE jdonloder_auth;

-- ─── Users table ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id          SERIAL        PRIMARY KEY,
  username    TEXT          NOT NULL UNIQUE,
  password    TEXT          NOT NULL,
  created_at  TEXT          NOT NULL DEFAULT to_char(NOW() AT TIME ZONE 'Asia/Jakarta', 'YYYY-MM-DD HH24:MI:SS')
);

-- ─── Login history table ────────────────────────────────
-- Records every login attempt (success or failure)
CREATE TABLE IF NOT EXISTS login_history (
  id           SERIAL   PRIMARY KEY,
  username     TEXT     NOT NULL,
  success      TEXT     NOT NULL,   -- 'true' | 'false'
  ip_address   TEXT,
  user_agent   TEXT,
  login_at     TEXT     NOT NULL DEFAULT to_char(NOW() AT TIME ZONE 'Asia/Jakarta', 'YYYY-MM-DD HH24:MI:SS')
);

-- ─── Seed: default test users ───────────────────────────
-- username: admin    password: admin123
-- username: udomain  password: password
INSERT INTO users (username, password)
VALUES
  ('admin',   'admin123'),
  ('udomain', 'password')
ON CONFLICT (username) DO NOTHING;
