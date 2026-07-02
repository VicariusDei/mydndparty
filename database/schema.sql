-- MyDndParty nuovo schema iniziale
-- Target: MySQL / MariaDB compatibile Aruba

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE campaigns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  notes MEDIUMTEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_campaigns_owner FOREIGN KEY (owner_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE party_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  user_id INT NOT NULL,
  player_name VARCHAR(120) NOT NULL,
  character_name VARCHAR(120) NOT NULL,
  class_name VARCHAR(80) NULL,
  ancestry_name VARCHAR(80) NULL,
  motto VARCHAR(255) NULL,
  initiative_bonus INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_party_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id),
  CONSTRAINT fk_party_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inventory_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  owner_party_member_id INT NULL,
  name VARCHAR(160) NOT NULL,
  category VARCHAR(80) NULL,
  quantity INT NOT NULL DEFAULT 1,
  value_gold DECIMAL(10,2) NOT NULL DEFAULT 0,
  is_identified TINYINT(1) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_inventory_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id),
  CONSTRAINT fk_inventory_owner FOREIGN KEY (owner_party_member_id) REFERENCES party_members(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE coin_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL UNIQUE,
  name VARCHAR(80) NOT NULL,
  gold_value DECIMAL(10,4) NOT NULL,
  weight_value DECIMAL(10,4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wallets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  party_member_id INT NULL,
  coin_type_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  deposit_quantity INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_wallet_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id),
  CONSTRAINT fk_wallet_member FOREIGN KEY (party_member_id) REFERENCES party_members(id),
  CONSTRAINT fk_wallet_coin FOREIGN KEY (coin_type_id) REFERENCES coin_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE encounters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  name VARCHAR(160) NOT NULL,
  current_round INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_encounter_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE combatants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  encounter_id INT NOT NULL,
  party_member_id INT NULL,
  name VARCHAR(160) NOT NULL,
  type ENUM('player','enemy','npc') NOT NULL DEFAULT 'enemy',
  initiative INT NOT NULL DEFAULT 0,
  initiative_bonus INT NOT NULL DEFAULT 0,
  is_slow TINYINT(1) NOT NULL DEFAULT 0,
  has_acted TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_combatant_encounter FOREIGN KEY (encounter_id) REFERENCES encounters(id),
  CONSTRAINT fk_combatant_party FOREIGN KEY (party_member_id) REFERENCES party_members(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE effects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  combatant_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  remaining_rounds INT NOT NULL DEFAULT 0,
  is_permanent TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_effect_combatant FOREIGN KEY (combatant_id) REFERENCES combatants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO coin_types (code, name, gold_value, weight_value) VALUES
('MR', 'Rame', 0.01, 0.02),
('MA', 'Argento', 0.10, 0.02),
('MO', 'Oro', 1.00, 0.02),
('MP', 'Platino', 10.00, 0.02);
