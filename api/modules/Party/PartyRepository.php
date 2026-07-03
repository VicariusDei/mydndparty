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

    public function findInCampaign(int $campaignId, int $memberId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, campaign_id, user_id, player_name, character_name, class_name, ancestry_name, motto, initiative_bonus, created_at, updated_at
             FROM mdp_party_members
             WHERE id = :id AND campaign_id = :campaign_id
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $memberId,
            'campaign_id' => $campaignId,
        ]);
        $member = $stmt->fetch();

        return $member ?: null;
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
            'player_name' => $this->clip((string)$data['player_name'], 120),
            'character_name' => $this->clip((string)$data['character_name'], 120),
            'class_name' => $this->nullableClip((string)($data['class_name'] ?? ''), 80),
            'ancestry_name' => $this->nullableClip((string)($data['ancestry_name'] ?? ''), 80),
            'motto' => $this->nullableClip((string)($data['motto'] ?? ''), 255),
            'initiative_bonus' => (int)$data['initiative_bonus'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $campaignId, int $memberId, array $data): void
    {
        if (!$this->findInCampaign($campaignId, $memberId)) {
            throw new RuntimeException('Personaggio non trovato nella campagna attiva.');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE mdp_party_members
             SET player_name = :player_name,
                 character_name = :character_name,
                 class_name = :class_name,
                 ancestry_name = :ancestry_name,
                 motto = :motto,
                 initiative_bonus = :initiative_bonus,
                 updated_at = NOW()
             WHERE id = :id AND campaign_id = :campaign_id'
        );
        $stmt->execute([
            'player_name' => $this->clip((string)$data['player_name'], 120),
            'character_name' => $this->clip((string)$data['character_name'], 120),
            'class_name' => $this->nullableClip((string)($data['class_name'] ?? ''), 80),
            'ancestry_name' => $this->nullableClip((string)($data['ancestry_name'] ?? ''), 80),
            'motto' => $this->nullableClip((string)($data['motto'] ?? ''), 255),
            'initiative_bonus' => (int)$data['initiative_bonus'],
            'id' => $memberId,
            'campaign_id' => $campaignId,
        ]);
    }

    public function deleteIfUnused(int $campaignId, int $memberId): void
    {
        if (!$this->findInCampaign($campaignId, $memberId)) {
            throw new RuntimeException('Personaggio non trovato nella campagna attiva.');
        }

        $dependencies = $this->dependencyCounts($memberId);
        foreach ($dependencies as $count) {
            if ($count > 0) {
                throw new RuntimeException('Personaggio non eliminabile: risulta collegato a inventario o combattimenti.');
            }
        }

        $stmt = $this->pdo->prepare('DELETE FROM mdp_party_members WHERE id = :id AND campaign_id = :campaign_id');
        $stmt->execute([
            'id' => $memberId,
            'campaign_id' => $campaignId,
        ]);
    }

    public function dependencyCounts(int $memberId): array
    {
        return [
            'inventory_items' => $this->countByColumn('mdp_inventory_items', 'owner_party_member_id', $memberId),
            'combatants' => $this->countByColumn('mdp_combatants', 'party_member_id', $memberId),
        ];
    }

    private function countByColumn(string $table, string $column, int $value): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $column . ' = :value');
        $stmt->execute(['value' => $value]);

        return (int)$stmt->fetchColumn();
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
