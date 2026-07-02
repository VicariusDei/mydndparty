<?php

final class PartyRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listByCampaign(int $campaignId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, campaign_id, user_id, player_name, character_name, class_name, ancestry_name, motto, initiative_bonus, created_at, updated_at
             FROM mdp_party_members
             WHERE campaign_id = :campaign_id
             ORDER BY character_name ASC'
        );
        $stmt->execute(['campaign_id' => $campaignId]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_party_members
                (campaign_id, user_id, player_name, character_name, class_name, ancestry_name, motto, initiative_bonus)
             VALUES
                (:campaign_id, :user_id, :player_name, :character_name, :class_name, :ancestry_name, :motto, :initiative_bonus)'
        );

        $stmt->execute([
            'campaign_id' => $data['campaign_id'],
            'user_id' => $data['user_id'],
            'player_name' => $data['player_name'],
            'character_name' => $data['character_name'],
            'class_name' => $data['class_name'],
            'ancestry_name' => $data['ancestry_name'],
            'motto' => $data['motto'],
            'initiative_bonus' => $data['initiative_bonus'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
