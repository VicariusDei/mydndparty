-- MyDndParty narrative core migration
-- Target: MySQL / MariaDB compatibile Aruba
-- Scopo: rendere l'app agnostica rispetto al regolamento e centrata sulla storia del party.

CREATE TABLE IF NOT EXISTS mdp_campaign_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL UNIQUE,
  ruleset_code VARCHAR(80) NULL,
  ruleset_name VARCHAR(160) NULL,
  default_visibility ENUM('party','master','private','custom') NOT NULL DEFAULT 'party',
  allow_player_quick_notes TINYINT(1) NOT NULL DEFAULT 1,
  quick_note_moderation ENUM('none','master_review') NOT NULL DEFAULT 'master_review',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_campaign_settings_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  INDEX idx_mdp_campaign_settings_ruleset (ruleset_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  session_number INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  real_date DATE NULL,
  world_date VARCHAR(120) NULL,
  summary MEDIUMTEXT NULL,
  master_notes MEDIUMTEXT NULL,
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  visibility ENUM('party','master','private','custom') NOT NULL DEFAULT 'party',
  created_by_user_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_sessions_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_sessions_user FOREIGN KEY (created_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  UNIQUE KEY uq_mdp_sessions_number (campaign_id, session_number),
  INDEX idx_mdp_sessions_campaign_date (campaign_id, real_date),
  INDEX idx_mdp_sessions_campaign_status (campaign_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_world_entities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  type ENUM('npc','place','faction','object','lore','event','creature','organization','other') NOT NULL,
  name VARCHAR(180) NOT NULL,
  subtitle VARCHAR(220) NULL,
  summary TEXT NULL,
  description MEDIUMTEXT NULL,
  secret_notes MEDIUMTEXT NULL,
  status VARCHAR(80) NULL,
  visibility ENUM('party','master','private','custom') NOT NULL DEFAULT 'party',
  first_session_id INT NULL,
  created_by_user_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_entities_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_entities_first_session FOREIGN KEY (first_session_id) REFERENCES mdp_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_entities_user FOREIGN KEY (created_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_entities_campaign_type (campaign_id, type),
  INDEX idx_mdp_entities_campaign_name (campaign_id, name),
  INDEX idx_mdp_entities_status (campaign_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_quests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  summary TEXT NULL,
  description MEDIUMTEXT NULL,
  status ENUM('open','suspended','completed','failed','abandoned') NOT NULL DEFAULT 'open',
  priority ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal',
  visibility ENUM('party','master','private','custom') NOT NULL DEFAULT 'party',
  opened_session_id INT NULL,
  closed_session_id INT NULL,
  created_by_user_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_quests_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_quests_opened_session FOREIGN KEY (opened_session_id) REFERENCES mdp_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_quests_closed_session FOREIGN KEY (closed_session_id) REFERENCES mdp_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_quests_user FOREIGN KEY (created_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_quests_campaign_status (campaign_id, status),
  INDEX idx_mdp_quests_campaign_priority (campaign_id, priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_timeline_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  session_id INT NULL,
  title VARCHAR(180) NOT NULL,
  event_type ENUM('session','discovery','battle','death','travel','quest','relationship','lore','loot','custom') NOT NULL DEFAULT 'custom',
  event_date VARCHAR(120) NULL,
  summary TEXT NULL,
  visibility ENUM('party','master','private','custom') NOT NULL DEFAULT 'party',
  sort_order INT NOT NULL DEFAULT 0,
  created_by_user_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_timeline_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_timeline_session FOREIGN KEY (session_id) REFERENCES mdp_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_timeline_user FOREIGN KEY (created_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_timeline_campaign_type (campaign_id, event_type),
  INDEX idx_mdp_timeline_session (session_id),
  INDEX idx_mdp_timeline_order (campaign_id, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_entity_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  source_type ENUM('session','entity','quest','timeline_event','party_member','inventory_item','encounter') NOT NULL,
  source_id INT NOT NULL,
  target_type ENUM('session','entity','quest','timeline_event','party_member','inventory_item','encounter') NOT NULL,
  target_id INT NOT NULL,
  relation_type VARCHAR(80) NOT NULL DEFAULT 'related',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mdp_entity_links_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  INDEX idx_mdp_entity_links_source (campaign_id, source_type, source_id),
  INDEX idx_mdp_entity_links_target (campaign_id, target_type, target_id),
  INDEX idx_mdp_entity_links_relation (campaign_id, relation_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_quick_access_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  session_id INT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  label VARCHAR(120) NULL,
  scope ENUM('quick_note','session_input','player_claim','read_only') NOT NULL DEFAULT 'quick_note',
  visibility ENUM('party','master','private','custom') NOT NULL DEFAULT 'party',
  expires_at DATETIME NOT NULL,
  max_uses INT NULL,
  used_count INT NOT NULL DEFAULT 0,
  is_revoked TINYINT(1) NOT NULL DEFAULT 0,
  created_by_user_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME NULL,
  CONSTRAINT fk_mdp_qr_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_qr_session FOREIGN KEY (session_id) REFERENCES mdp_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_qr_user FOREIGN KEY (created_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_qr_campaign_scope (campaign_id, scope),
  INDEX idx_mdp_qr_expires (expires_at),
  INDEX idx_mdp_qr_revoked (is_revoked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_quick_notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  session_id INT NULL,
  access_token_id INT NULL,
  author_user_id INT NULL,
  author_label VARCHAR(120) NULL,
  channel ENUM('web','qr','telegram','whatsapp','admin') NOT NULL DEFAULT 'web',
  note_type ENUM('note','npc','place','quest','loot','question','rules','idea') NOT NULL DEFAULT 'note',
  content TEXT NOT NULL,
  status ENUM('pending','accepted','rejected','converted') NOT NULL DEFAULT 'pending',
  converted_target_type ENUM('session','entity','quest','timeline_event','inventory_item','none') NOT NULL DEFAULT 'none',
  converted_target_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_by_user_id INT NULL,
  reviewed_at DATETIME NULL,
  CONSTRAINT fk_mdp_quick_notes_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_quick_notes_session FOREIGN KEY (session_id) REFERENCES mdp_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_quick_notes_token FOREIGN KEY (access_token_id) REFERENCES mdp_quick_access_tokens(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_quick_notes_author FOREIGN KEY (author_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_quick_notes_reviewer FOREIGN KEY (reviewed_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_quick_notes_campaign_status (campaign_id, status),
  INDEX idx_mdp_quick_notes_session (session_id),
  INDEX idx_mdp_quick_notes_channel (campaign_id, channel),
  INDEX idx_mdp_quick_notes_created (campaign_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_bot_identities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  user_id INT NULL,
  provider ENUM('telegram','whatsapp') NOT NULL,
  provider_chat_id VARCHAR(190) NOT NULL,
  display_name VARCHAR(160) NULL,
  is_enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_bot_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_bot_user FOREIGN KEY (user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  UNIQUE KEY uq_mdp_bot_provider_chat (provider, provider_chat_id),
  INDEX idx_mdp_bot_campaign_provider (campaign_id, provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_custom_fields (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  applies_to ENUM('party_member','entity','quest','session','inventory_item') NOT NULL,
  field_key VARCHAR(80) NOT NULL,
  label VARCHAR(120) NOT NULL,
  field_type ENUM('text','number','textarea','select','checkbox','date') NOT NULL DEFAULT 'text',
  options_json TEXT NULL,
  is_required TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mdp_custom_fields_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  UNIQUE KEY uq_mdp_custom_fields_key (campaign_id, applies_to, field_key),
  INDEX idx_mdp_custom_fields_applies (campaign_id, applies_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_custom_field_values (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  field_id INT NOT NULL,
  target_type ENUM('party_member','entity','quest','session','inventory_item') NOT NULL,
  target_id INT NOT NULL,
  value_text MEDIUMTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_custom_values_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_custom_values_field FOREIGN KEY (field_id) REFERENCES mdp_custom_fields(id) ON DELETE CASCADE,
  UNIQUE KEY uq_mdp_custom_value (field_id, target_type, target_id),
  INDEX idx_mdp_custom_values_target (campaign_id, target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
