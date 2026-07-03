-- MyDndParty groups, contextual roles, assets and calendar migration
-- Scopo: account neutri, gruppi di gioco, campagne dentro gruppi, ruoli contestuali e materiale campagna.
-- Non modifica le tabelle esistenti: usa tabelle di relazione additive.

CREATE TABLE IF NOT EXISTS mdp_game_groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(160) NULL UNIQUE,
  description TEXT NULL,
  created_by_user_id INT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_game_groups_creator FOREIGN KEY (created_by_user_id) REFERENCES mdp_users(id) ON DELETE CASCADE,
  INDEX idx_mdp_game_groups_creator (created_by_user_id),
  INDEX idx_mdp_game_groups_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_game_group_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  game_group_id INT NOT NULL,
  user_id INT NOT NULL,
  username_snapshot VARCHAR(80) NULL,
  role ENUM('owner','admin','member') NOT NULL DEFAULT 'member',
  status ENUM('invited','active','suspended','left') NOT NULL DEFAULT 'active',
  invited_by_user_id INT NULL,
  invited_at DATETIME NULL,
  joined_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_group_members_group FOREIGN KEY (game_group_id) REFERENCES mdp_game_groups(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_group_members_user FOREIGN KEY (user_id) REFERENCES mdp_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_group_members_inviter FOREIGN KEY (invited_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  UNIQUE KEY uq_mdp_group_member (game_group_id, user_id),
  INDEX idx_mdp_group_members_user (user_id),
  INDEX idx_mdp_group_members_status (game_group_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_game_group_campaigns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  game_group_id INT NOT NULL,
  campaign_id INT NOT NULL,
  created_by_user_id INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mdp_group_campaigns_group FOREIGN KEY (game_group_id) REFERENCES mdp_game_groups(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_group_campaigns_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_group_campaigns_creator FOREIGN KEY (created_by_user_id) REFERENCES mdp_users(id) ON DELETE CASCADE,
  UNIQUE KEY uq_mdp_group_campaign_campaign (campaign_id),
  INDEX idx_mdp_group_campaigns_group (game_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_campaign_participants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  user_id INT NOT NULL,
  party_member_id INT NULL,
  role ENUM('master','co_master','player','viewer') NOT NULL DEFAULT 'player',
  status ENUM('invited','active','suspended','left') NOT NULL DEFAULT 'active',
  added_by_user_id INT NULL,
  invited_at DATETIME NULL,
  joined_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_campaign_participants_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_campaign_participants_user FOREIGN KEY (user_id) REFERENCES mdp_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_campaign_participants_member FOREIGN KEY (party_member_id) REFERENCES mdp_party_members(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_campaign_participants_adder FOREIGN KEY (added_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  UNIQUE KEY uq_mdp_campaign_participant (campaign_id, user_id),
  INDEX idx_mdp_campaign_participants_user (user_id),
  INDEX idx_mdp_campaign_participants_role (campaign_id, role),
  INDEX idx_mdp_campaign_participants_status (campaign_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_campaign_assets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  session_id INT NULL,
  uploaded_by_user_id INT NULL,
  asset_type ENUM('audio','video','image','pdf','document','link','other') NOT NULL DEFAULT 'other',
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  original_filename VARCHAR(255) NULL,
  stored_path VARCHAR(500) NULL,
  external_url VARCHAR(1000) NULL,
  mime_type VARCHAR(120) NULL,
  size_bytes INT NULL,
  visibility ENUM('party','master','private','restricted','public_readonly') NOT NULL DEFAULT 'party',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_campaign_assets_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_campaign_assets_session FOREIGN KEY (session_id) REFERENCES mdp_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_campaign_assets_user FOREIGN KEY (uploaded_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_campaign_assets_campaign_type (campaign_id, asset_type),
  INDEX idx_mdp_campaign_assets_session (session_id),
  INDEX idx_mdp_campaign_assets_visibility (campaign_id, visibility)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_session_calendar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL UNIQUE,
  campaign_id INT NOT NULL,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  timezone_name VARCHAR(80) NOT NULL DEFAULT 'Europe/Rome',
  location_label VARCHAR(180) NULL,
  location_url VARCHAR(500) NULL,
  calendar_status ENUM('planned','confirmed','cancelled','played') NOT NULL DEFAULT 'planned',
  created_by_user_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_session_calendar_session FOREIGN KEY (session_id) REFERENCES mdp_sessions(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_session_calendar_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_session_calendar_user FOREIGN KEY (created_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_session_calendar_campaign_start (campaign_id, starts_at),
  INDEX idx_mdp_session_calendar_status (campaign_id, calendar_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
