<?php

final class InventoryRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listByCampaign(int $campaignId, int $limit = 200): array
    {
        $limit = max(1, min($limit, 500));
        $stmt = $this->pdo->prepare(
            'SELECT i.id, i.campaign_id, i.owner_party_member_id, i.name, i.category, i.quantity, i.value_gold, i.is_identified, i.notes, i.created_at, i.updated_at,
                    pm.character_name AS owner_character_name,
                    pm.player_name AS owner_player_name
             FROM mdp_inventory_items i
             LEFT JOIN mdp_party_members pm ON pm.id = i.owner_party_member_id
             WHERE i.campaign_id = :campaign_id
             ORDER BY i.category ASC, i.name ASC
             LIMIT ' . $limit
        );
        $stmt->execute(['campaign_id' => $campaignId]);

        return $stmt->fetchAll();
    }

    public function recentByCampaign(int $campaignId, int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        $stmt = $this->pdo->prepare(
            'SELECT i.id, i.campaign_id, i.owner_party_member_id, i.name, i.category, i.quantity, i.value_gold, i.is_identified, i.notes, i.created_at, i.updated_at,
                    pm.character_name AS owner_character_name,
                    pm.player_name AS owner_player_name
             FROM mdp_inventory_items i
             LEFT JOIN mdp_party_members pm ON pm.id = i.owner_party_member_id
             WHERE i.campaign_id = :campaign_id
             ORDER BY i.id DESC
             LIMIT ' . $limit
        );
        $stmt->execute(['campaign_id' => $campaignId]);

        return $stmt->fetchAll();
    }

    public function countByCampaign(int $campaignId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM mdp_inventory_items WHERE campaign_id = :campaign_id');
        $stmt->execute(['campaign_id' => $campaignId]);

        return (int)$stmt->fetchColumn();
    }

    public function walletByCampaign(int $campaignId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT w.id, w.campaign_id, w.party_member_id, w.coin_type_id, w.quantity, w.deposit_quantity,
                    ct.code, ct.name, ct.gold_value, ct.weight_value,
                    pm.character_name AS owner_character_name
             FROM mdp_wallets w
             JOIN mdp_coin_types ct ON ct.id = w.coin_type_id
             LEFT JOIN mdp_party_members pm ON pm.id = w.party_member_id
             WHERE w.campaign_id = :campaign_id
             ORDER BY ct.gold_value ASC, ct.code ASC'
        );
        $stmt->execute(['campaign_id' => $campaignId]);

        return $stmt->fetchAll();
    }
}
