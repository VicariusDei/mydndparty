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

    public function createItem(int $campaignId, array $data): int
    {
        $ownerId = $this->ownerId($campaignId, (int)($data['owner_party_member_id'] ?? 0));
        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_inventory_items (campaign_id, owner_party_member_id, name, category, quantity, value_gold, is_identified, notes)
             VALUES (:campaign_id, :owner_party_member_id, :name, :category, :quantity, :value_gold, :is_identified, :notes)'
        );
        $stmt->execute([
            'campaign_id' => $campaignId,
            'owner_party_member_id' => $ownerId,
            'name' => $this->clip((string)$data['name'], 160),
            'category' => $this->nullableClip((string)($data['category'] ?? ''), 80),
            'quantity' => max(1, (int)($data['quantity'] ?? 1)),
            'value_gold' => max(0, (float)($data['value_gold'] ?? 0)),
            'is_identified' => !empty($data['is_identified']) ? 1 : 0,
            'notes' => $this->nullableClip((string)($data['notes'] ?? ''), 60000),
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function updateItem(int $campaignId, int $itemId, array $data): void
    {
        if (!$this->findItemInCampaign($campaignId, $itemId)) {
            throw new RuntimeException('Oggetto non trovato nella campagna attiva.');
        }

        $ownerId = $this->ownerId($campaignId, (int)($data['owner_party_member_id'] ?? 0));
        $stmt = $this->pdo->prepare(
            'UPDATE mdp_inventory_items
             SET owner_party_member_id = :owner_party_member_id,
                 name = :name,
                 category = :category,
                 quantity = :quantity,
                 value_gold = :value_gold,
                 is_identified = :is_identified,
                 notes = :notes,
                 updated_at = NOW()
             WHERE id = :id AND campaign_id = :campaign_id'
        );
        $stmt->execute([
            'owner_party_member_id' => $ownerId,
            'name' => $this->clip((string)$data['name'], 160),
            'category' => $this->nullableClip((string)($data['category'] ?? ''), 80),
            'quantity' => max(1, (int)($data['quantity'] ?? 1)),
            'value_gold' => max(0, (float)($data['value_gold'] ?? 0)),
            'is_identified' => !empty($data['is_identified']) ? 1 : 0,
            'notes' => $this->nullableClip((string)($data['notes'] ?? ''), 60000),
            'id' => $itemId,
            'campaign_id' => $campaignId,
        ]);
    }

    public function deleteItem(int $campaignId, int $itemId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM mdp_inventory_items WHERE id = :id AND campaign_id = :campaign_id');
        $stmt->execute([
            'id' => $itemId,
            'campaign_id' => $campaignId,
        ]);
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

    public function adjustWallet(int $campaignId, int $walletId, int $quantityDelta, int $depositDelta): void
    {
        $wallet = $this->findWalletInCampaign($campaignId, $walletId);
        if (!$wallet) {
            throw new RuntimeException('Riga monete non trovata nella campagna attiva.');
        }

        $quantity = max(0, (int)$wallet['quantity'] + $quantityDelta);
        $deposit = max(0, (int)$wallet['deposit_quantity'] + $depositDelta);

        $stmt = $this->pdo->prepare(
            'UPDATE mdp_wallets
             SET quantity = :quantity,
                 deposit_quantity = :deposit_quantity
             WHERE id = :id AND campaign_id = :campaign_id'
        );
        $stmt->execute([
            'quantity' => $quantity,
            'deposit_quantity' => $deposit,
            'id' => $walletId,
            'campaign_id' => $campaignId,
        ]);
    }

    public function updateWallet(int $campaignId, int $walletId, int $quantity, int $depositQuantity): void
    {
        if (!$this->findWalletInCampaign($campaignId, $walletId)) {
            throw new RuntimeException('Riga monete non trovata nella campagna attiva.');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE mdp_wallets
             SET quantity = :quantity,
                 deposit_quantity = :deposit_quantity
             WHERE id = :id AND campaign_id = :campaign_id'
        );
        $stmt->execute([
            'quantity' => max(0, $quantity),
            'deposit_quantity' => max(0, $depositQuantity),
            'id' => $walletId,
            'campaign_id' => $campaignId,
        ]);
    }

    private function findItemInCampaign(int $campaignId, int $itemId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id FROM mdp_inventory_items WHERE id = :id AND campaign_id = :campaign_id LIMIT 1');
        $stmt->execute([
            'id' => $itemId,
            'campaign_id' => $campaignId,
        ]);
        $item = $stmt->fetch();

        return $item ?: null;
    }

    private function findWalletInCampaign(int $campaignId, int $walletId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, quantity, deposit_quantity FROM mdp_wallets WHERE id = :id AND campaign_id = :campaign_id LIMIT 1');
        $stmt->execute([
            'id' => $walletId,
            'campaign_id' => $campaignId,
        ]);
        $wallet = $stmt->fetch();

        return $wallet ?: null;
    }

    private function ownerId(int $campaignId, int $partyMemberId): ?int
    {
        if ($partyMemberId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT id FROM mdp_party_members WHERE id = :id AND campaign_id = :campaign_id LIMIT 1');
        $stmt->execute([
            'id' => $partyMemberId,
            'campaign_id' => $campaignId,
        ]);

        return $stmt->fetch() ? $partyMemberId : null;
    }

    private function clip(string $value, int $length): string
    {
        $value = trim($value);
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $length, 'UTF-8');
        }

        return substr($value, 0, $length);
    }

    private function nullableClip(string $value, int $length): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return $this->clip($value, $length);
    }
}
