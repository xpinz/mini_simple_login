-- ═══════════════════════════════════════════════════════
-- init_mysql.sql  –  DEX Management Inventory Auth Schema
-- For MySQL (Rumahweb cPanel)
-- Run this via phpMyAdmin after creating the database.
-- ═══════════════════════════════════════════════════════

-- ─── Users table ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  username    VARCHAR(255) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  created_at  VARCHAR(50)  NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trigger to auto-set created_at in WIB (UTC+7)
DELIMITER //
CREATE TRIGGER users_set_created_at
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
  IF NEW.created_at = '0000-00-00 00:00:00' THEN
    SET NEW.created_at = DATE_FORMAT(CONVERT_TZ(NOW(), 'SYSTEM', '+07:00'), '%Y-%m-%d %H:%i:%s');
  END IF;
END //
DELIMITER ;

-- ─── Login history table ────────────────────────────────
CREATE TABLE IF NOT EXISTS login_history (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  username     VARCHAR(255) NOT NULL,
  success      VARCHAR(10)  NOT NULL,
  ip_address   VARCHAR(100) DEFAULT NULL,
  user_agent   TEXT         DEFAULT NULL,
  login_at     VARCHAR(50)  NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trigger to auto-set login_at in WIB (UTC+7)
DELIMITER //
CREATE TRIGGER login_history_set_login_at
BEFORE INSERT ON login_history
FOR EACH ROW
BEGIN
  IF NEW.login_at = '0000-00-00 00:00:00' THEN
    SET NEW.login_at = DATE_FORMAT(CONVERT_TZ(NOW(), 'SYSTEM', '+07:00'), '%Y-%m-%d %H:%i:%s');
  END IF;
END //
DELIMITER ;

-- ─── Seed: default admin user ───────────────────────────
INSERT IGNORE INTO users (username, password)
VALUES ('admin', 'admin123');
