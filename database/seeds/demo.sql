-- Seed demo per sviluppo locale
-- Importare dopo `database/schema.sql`

INSERT INTO mdp_users (id, username, email, password_hash, is_active, is_admin)
VALUES
(1, 'demo_master', 'demo@mydndparty.local', '$2y$10$demo.hash.non.usare.in.produzione', 1, 1);

INSERT INTO mdp_campaigns (id, owner_user_id, name, notes, is_active)
VALUES
(1, 1, 'Le Ombre di Vhalor', 'Il party e'' al limite del Bosco Cavo. Tre effetti sono ancora attivi.', 1);

INSERT INTO mdp_party_members
(id, campaign_id, user_id, player_name, character_name, class_name, ancestry_name, motto, initiative_bonus)
VALUES
(1, 1, 1, 'Laura', 'Mirael', 'Ranger', 'Mezzelfa', 'Ogni traccia racconta una bugia.', 4),
(2, 1, 1, 'Marco', 'Thoran', 'Paladino', 'Nano', 'La fede pesa meno dello scudo.', 1),
(3, 1, 1, 'Sara', 'Nym', 'Ladra', 'Halfling', 'Se brilla, probabilmente serve.', 5);
