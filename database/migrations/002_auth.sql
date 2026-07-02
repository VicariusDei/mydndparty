-- Migration 002: autenticazione completa
-- Da applicare se `database/schema.sql` era gia' stato importato prima di questa modifica.

ALTER TABLE mdp_users
  MODIFY password_hash VARCHAR(255) NULL,
  ADD COLUMN display_name VARCHAR(160) NULL AFTER password_hash,
  ADD COLUMN avatar_url VARCHAR(500) NULL AFTER display_name,
  ADD COLUMN google_id VARCHAR(80) NULL UNIQUE AFTER avatar_url,
  ADD COLUMN email_verified_at DATETIME NULL AFTER is_admin,
  ADD COLUMN last_login_at DATETIME NULL AFTER email_verified_at,
  ADD INDEX idx_mdp_users_email (email),
  ADD INDEX idx_mdp_users_google_id (google_id);

CREATE TABLE IF NOT EXISTS mdp_remember_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  selector VARCHAR(64) NOT NULL UNIQUE,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME NULL,
  user_agent VARCHAR(255) NULL,
  ip_address VARCHAR(45) NULL,
  CONSTRAINT fk_mdp_remember_user FOREIGN KEY (user_id) REFERENCES mdp_users(id) ON DELETE CASCADE,
  INDEX idx_mdp_remember_selector (selector),
  INDEX idx_mdp_remember_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_password_reset_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mdp_reset_user FOREIGN KEY (user_id) REFERENCES mdp_users(id) ON DELETE CASCADE,
  INDEX idx_mdp_reset_token_hash (token_hash),
  INDEX idx_mdp_reset_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
