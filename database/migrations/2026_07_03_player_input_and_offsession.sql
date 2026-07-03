-- MyDndParty player input and off-session migration
-- Da eseguire dopo 2026_07_03_narrative_core.sql
-- Scopo: note giocatore subito visibili, condivisione granulare e gioco off-sessione cronologico.

CREATE TABLE IF NOT EXISTS mdp_player_notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  session_id INT NULL,
  author_user_id INT NULL,
  author_party_member_id INT NULL,
  author_label VARCHAR(120) NULL,
  origin_channel ENUM('web','qr','telegram','whatsapp','discord','email','admin','import') NOT NULL DEFAULT 'web',
  note_type ENUM('note','npc','place','quest','loot','question','rules','idea','scene','decision') NOT NULL DEFAULT 'note',
  title VARCHAR(180) NULL,
  content TEXT NOT NULL,
  share_scope ENUM('party','private','restricted','master','public_readonly') NOT NULL DEFAULT 'party',
  status ENUM('visible','hidden','corrected','converted','deleted') NOT NULL DEFAULT 'visible',
  master_flag ENUM('none','needs_review','verified','spoiler','incorrect') NOT NULL DEFAULT 'none',
  corrected_by_user_id INT NULL,
  corrected_at DATETIME NULL,
  converted_target_type ENUM('none','session','entity','quest','timeline_event','inventory_item') NOT NULL DEFAULT 'none',
  converted_target_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_player_notes_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_player_notes_session FOREIGN KEY (session_id) REFERENCES mdp_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_player_notes_author_user FOREIGN KEY (author_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_player_notes_author_member FOREIGN KEY (author_party_member_id) REFERENCES mdp_party_members(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_player_notes_corrector FOREIGN KEY (corrected_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_player_notes_campaign_created (campaign_id, created_at),
  INDEX idx_mdp_player_notes_session_created (session_id, created_at),
  INDEX idx_mdp_player_notes_scope (campaign_id, share_scope),
  INDEX idx_mdp_player_notes_status (campaign_id, status),
  INDEX idx_mdp_player_notes_channel (campaign_id, origin_channel),
  INDEX idx_mdp_player_notes_type (campaign_id, note_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_player_note_recipients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  note_id INT NOT NULL,
  recipient_user_id INT NULL,
  recipient_party_member_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mdp_note_recipients_note FOREIGN KEY (note_id) REFERENCES mdp_player_notes(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_note_recipients_user FOREIGN KEY (recipient_user_id) REFERENCES mdp_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_note_recipients_member FOREIGN KEY (recipient_party_member_id) REFERENCES mdp_party_members(id) ON DELETE CASCADE,
  INDEX idx_mdp_note_recipients_note (note_id),
  INDEX idx_mdp_note_recipients_user (recipient_user_id),
  INDEX idx_mdp_note_recipients_member (recipient_party_member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_player_note_revisions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  note_id INT NOT NULL,
  edited_by_user_id INT NULL,
  previous_title VARCHAR(180) NULL,
  previous_content TEXT NULL,
  previous_share_scope ENUM('party','private','restricted','master','public_readonly') NULL,
  previous_status ENUM('visible','hidden','corrected','converted','deleted') NULL,
  revision_reason VARCHAR(220) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mdp_note_revisions_note FOREIGN KEY (note_id) REFERENCES mdp_player_notes(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_note_revisions_user FOREIGN KEY (edited_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_note_revisions_note (note_id),
  INDEX idx_mdp_note_revisions_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_offsession_threads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  session_id INT NULL,
  title VARCHAR(180) NOT NULL,
  thread_type ENUM('roleplay','planning','recap','rules','loot','quest','logistics','private_scene','other') NOT NULL DEFAULT 'roleplay',
  share_scope ENUM('party','private','restricted','master','public_readonly') NOT NULL DEFAULT 'party',
  status ENUM('open','paused','closed','archived') NOT NULL DEFAULT 'open',
  created_by_user_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  last_message_at DATETIME NULL,
  CONSTRAINT fk_mdp_offsession_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_offsession_session FOREIGN KEY (session_id) REFERENCES mdp_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_offsession_user FOREIGN KEY (created_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_offsession_campaign_status (campaign_id, status),
  INDEX idx_mdp_offsession_campaign_type (campaign_id, thread_type),
  INDEX idx_mdp_offsession_last_message (campaign_id, last_message_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_offsession_thread_recipients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  thread_id INT NOT NULL,
  recipient_user_id INT NULL,
  recipient_party_member_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mdp_thread_recipients_thread FOREIGN KEY (thread_id) REFERENCES mdp_offsession_threads(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_thread_recipients_user FOREIGN KEY (recipient_user_id) REFERENCES mdp_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_thread_recipients_member FOREIGN KEY (recipient_party_member_id) REFERENCES mdp_party_members(id) ON DELETE CASCADE,
  INDEX idx_mdp_thread_recipients_thread (thread_id),
  INDEX idx_mdp_thread_recipients_user (recipient_user_id),
  INDEX idx_mdp_thread_recipients_member (recipient_party_member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_offsession_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  thread_id INT NOT NULL,
  campaign_id INT NOT NULL,
  author_user_id INT NULL,
  author_party_member_id INT NULL,
  author_label VARCHAR(120) NULL,
  origin_channel ENUM('web','qr','telegram','whatsapp','discord','email','admin','import') NOT NULL DEFAULT 'web',
  message_type ENUM('message','action','scene','note','decision','attachment','system') NOT NULL DEFAULT 'message',
  content TEXT NOT NULL,
  status ENUM('visible','hidden','corrected','deleted') NOT NULL DEFAULT 'visible',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_mdp_offsession_messages_thread FOREIGN KEY (thread_id) REFERENCES mdp_offsession_threads(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_offsession_messages_campaign FOREIGN KEY (campaign_id) REFERENCES mdp_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_offsession_messages_user FOREIGN KEY (author_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_mdp_offsession_messages_member FOREIGN KEY (author_party_member_id) REFERENCES mdp_party_members(id) ON DELETE SET NULL,
  INDEX idx_mdp_offsession_messages_thread_created (thread_id, created_at),
  INDEX idx_mdp_offsession_messages_campaign_created (campaign_id, created_at),
  INDEX idx_mdp_offsession_messages_channel (campaign_id, origin_channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mdp_offsession_message_revisions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  message_id INT NOT NULL,
  edited_by_user_id INT NULL,
  previous_content TEXT NULL,
  previous_status ENUM('visible','hidden','corrected','deleted') NULL,
  revision_reason VARCHAR(220) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mdp_offsession_revisions_message FOREIGN KEY (message_id) REFERENCES mdp_offsession_messages(id) ON DELETE CASCADE,
  CONSTRAINT fk_mdp_offsession_revisions_user FOREIGN KEY (edited_by_user_id) REFERENCES mdp_users(id) ON DELETE SET NULL,
  INDEX idx_mdp_offsession_revisions_message (message_id),
  INDEX idx_mdp_offsession_revisions_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
