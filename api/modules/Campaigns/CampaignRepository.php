<?php

final class CampaignRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, notes, is_active, created_at, updated_at
             FROM mdp_campaigns
             WHERE owner_user_id = :user_id
             ORDER BY is_active DESC, name ASC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function activeByUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, notes, is_active, created_at, updated_at
             FROM mdp_campaigns
             WHERE owner_user_id = :user_id AND is_active = 1
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $campaign = $stmt->fetch();

        return $campaign ?: null;
    }

    public function findByUser(int $userId, int $campaignId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, notes, is_active, created_at, updated_at
             FROM mdp_campaigns
             WHERE id = :id AND owner_user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $campaignId,
            'user_id' => $userId,
        ]);
        $campaign = $stmt->fetch();

        return $campaign ?: null;
    }

    public function create(int $userId, string $name, ?string $notes = null): int
    {
        $hasCampaigns = count($this->listByUser($userId)) > 0;
        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_campaigns (owner_user_id, name, notes, is_active)
             VALUES (:user_id, :name, :notes, :is_active)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'name' => $name,
            'notes' => $notes,
            'is_active' => $hasCampaigns ? 0 : 1,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $userId, int $campaignId, string $name, ?string $notes): void
    {
        if (!$this->findByUser($userId, $campaignId)) {
            throw new RuntimeException('Campagna non trovata.');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE mdp_campaigns
             SET name = :name,
                 notes = :notes,
                 updated_at = NOW()
             WHERE id = :id AND owner_user_id = :user_id'
        );
        $stmt->execute([
            'name' => $name,
            'notes' => $notes,
            'id' => $campaignId,
            'user_id' => $userId,
        ]);
    }

    public function activate(int $userId, int $campaignId): void
    {
        if (!$this->findByUser($userId, $campaignId)) {
            throw new RuntimeException('Campagna non trovata.');
        }

        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare('UPDATE mdp_campaigns SET is_active = 0, updated_at = NOW() WHERE owner_user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        $stmt = $this->pdo->prepare('UPDATE mdp_campaigns SET is_active = 1, updated_at = NOW() WHERE id = :id AND owner_user_id = :user_id');
        $stmt->execute([
            'id' => $campaignId,
            'user_id' => $userId,
        ]);

        $this->pdo->commit();
    }

    public function deleteIfEmpty(int $userId, int $campaignId): void
    {
        if (!$this->findByUser($userId, $campaignId)) {
            throw new RuntimeException('Campagna non trovata.');
        }

        $dependencies = $this->dependencyCounts($campaignId);
        foreach ($dependencies as $count) {
            if ($count > 0) {
                throw new RuntimeException('Campagna non eliminabile: contiene party, inventario, monete o combattimenti.');
            }
        }

        $stmt = $this->pdo->prepare('DELETE FROM mdp_campaigns WHERE id = :id AND owner_user_id = :user_id');
        $stmt->execute([
            'id' => $campaignId,
            'user_id' => $userId,
        ]);
    }

    public function dependencyCounts(int $campaignId): array
    {
        return [
            'party_members' => $this->countTable('mdp_party_members', $campaignId),
            'inventory_items' => $this->countTable('mdp_inventory_items', $campaignId),
            'wallet_rows' => $this->countTable('mdp_wallets', $campaignId),
            'encounters' => $this->countTable('mdp_encounters', $campaignId),
        ];
    }

    private function countTable(string $table, int $campaignId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE campaign_id = :campaign_id');
        $stmt->execute(['campaign_id' => $campaignId]);

        return (int)$stmt->fetchColumn();
    }
}
