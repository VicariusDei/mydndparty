-- Seed demo per sviluppo locale
-- Importare dopo `database/schema.sql`
-- Credenziali demo locali: demo_master / demo12345
-- Non usare questi dati in produzione.

INSERT INTO mdp_users (id, username, email, password_hash, display_name, is_active, is_admin, email_verified_at)
VALUES
(1, 'demo_master', 'demo@mydndparty.local', '$2y$12$2jkB6WW942BvoWeJPgYpou9UeE78Nu5QjogQoDp8W2JOVyjzaknc6', 'Demo Master', 1, 1, NOW());

INSERT INTO mdp_campaigns (id, owner_user_id, name, notes, is_active)
VALUES
(1, 1, 'Le Ombre di Vhalor', 'Il party e'' al limite del Bosco Cavo. Tre effetti sono ancora attivi.', 1);

INSERT INTO mdp_party_members
(id, campaign_id, user_id, player_name, character_name, class_name, ancestry_name, motto, initiative_bonus)
VALUES
(1, 1, 1, 'Laura', 'Mirael', 'Ranger', 'Mezzelfa', 'Ogni traccia racconta una bugia.', 4),
(2, 1, 1, 'Marco', 'Thoran', 'Paladino', 'Nano', 'La fede pesa meno dello scudo.', 1),
(3, 1, 1, 'Sara', 'Nym', 'Ladra', 'Halfling', 'Se brilla, probabilmente serve.', 5);
