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

    public function create(int $userId, string $name, ?string $notes = null): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_campaigns (owner_user_id, name, notes, is_active)
             VALUES (:user_id, :name, :notes, 0)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'name' => $name,
            'notes' => $notes,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
