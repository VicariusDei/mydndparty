<?php

final class GroupCampaignsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listByGroup(int $groupId, int $userId): array
    {
        $this->assertGroupMember($groupId, $userId);

        $stmt = $this->pdo->prepare(
            "SELECT c.id, c.owner_user_id, c.name, c.notes, c.is_active, c.created_at, c.updated_at,
                    gc.game_group_id,
                    u.username AS owner_username,
                    u.display_name AS owner_display_name,
                    COUNT(DISTINCT cp.id) AS participants_count
             FROM mdp_game_group_campaigns gc
             INNER JOIN mdp_campaigns c ON c.id = gc.campaign_id
             INNER JOIN mdp_users u ON u.id = c.owner_user_id
             LEFT JOIN mdp_campaign_participants cp ON cp.campaign_id = c.id AND cp.status = 'active'
             WHERE gc.game_group_id = :group_id
             GROUP BY c.id, c.owner_user_id, c.name, c.notes, c.is_active, c.created_at, c.updated_at,
                      gc.game_group_id, u.username, u.display_name
             ORDER BY c.created_at DESC, c.name ASC"
        );
        $stmt->execute(['group_id' => $groupId]);

        return $stmt->fetchAll();
    }

    public function createInGroup(int $groupId, int $userId, string $name, ?string $notes = null): int
    {
        $this->assertGroupMember($groupId, $userId);

        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_campaigns (owner_user_id, name, notes, is_active)
             VALUES (:owner_user_id, :name, :notes, 0)'
        );
        $stmt->execute([
            'owner_user_id' => $userId,
            'name' => $this->clip($name, 120),
            'notes' => $this->nullableClip((string)($notes ?? ''), 60000),
        ]);
        $campaignId = (int)$this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_game_group_campaigns (game_group_id, campaign_id, created_by_user_id)
             VALUES (:group_id, :campaign_id, :created_by_user_id)'
        );
        $stmt->execute([
            'group_id' => $groupId,
            'campaign_id' => $campaignId,
            'created_by_user_id' => $userId,
        ]);

        $stmt = $this->pdo->prepare(
            "INSERT INTO mdp_campaign_participants
                (campaign_id, user_id, party_member_id, role, status, added_by_user_id, invited_at, joined_at)
             VALUES
                (:campaign_id, :user_id, NULL, 'master', 'active', :added_by_user_id, NOW(), NOW())"
        );
        $stmt->execute([
            'campaign_id' => $campaignId,
            'user_id' => $userId,
            'added_by_user_id' => $userId,
        ]);

        $this->pdo->commit();

        return $campaignId;
    }

    public function participants(int $campaignId, int $userId): array
    {
        $this->assertCampaignVisible($campaignId, $userId);

        $stmt = $this->pdo->prepare(
            'SELECT cp.id, cp.campaign_id, cp.user_id, cp.party_member_id, cp.role, cp.status,
                    cp.added_by_user_id, cp.invited_at, cp.joined_at, cp.created_at, cp.updated_at,
                    u.username, u.display_name, u.email, u.avatar_url,
                    pm.character_name, pm.player_name
             FROM mdp_campaign_participants cp
             INNER JOIN mdp_users u ON u.id = cp.user_id
             LEFT JOIN mdp_party_members pm ON pm.id = cp.party_member_id
             WHERE cp.campaign_id = :campaign_id
             ORDER BY cp.role ASC, u.display_name ASC, u.username ASC'
        );
        $stmt->execute(['campaign_id' => $campaignId]);

        return $stmt->fetchAll();
    }

    public function addParticipant(int $campaignId, int $actorUserId, int $targetUserId, string $role): void
    {
        $this->assertCanManageCampaign($campaignId, $actorUserId);
        $this->assertUserInCampaignGroup($campaignId, $targetUserId);
        $role = in_array($role, ['master', 'co_master', 'player', 'viewer'], true) ? $role : 'player';

        $stmt = $this->pdo->prepare(
            "INSERT INTO mdp_campaign_participants
                (campaign_id, user_id, party_member_id, role, status, added_by_user_id, invited_at, joined_at)
             VALUES
                (:campaign_id, :user_id, NULL, :role, 'active', :added_by_user_id, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                role = VALUES(role),
                status = 'active',
                updated_at = NOW()"
        );
        $stmt->execute([
            'campaign_id' => $campaignId,
            'user_id' => $targetUserId,
            'role' => $role,
            'added_by_user_id' => $actorUserId,
        ]);
    }

    public function groupIdForCampaign(int $campaignId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT game_group_id FROM mdp_game_group_campaigns WHERE campaign_id = :campaign_id LIMIT 1');
        $stmt->execute(['campaign_id' => $campaignId]);
        $id = $stmt->fetchColumn();

        return $id ? (int)$id : null;
    }

    private function assertGroupMember(int $groupId, int $userId): void
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM mdp_game_group_members
             WHERE game_group_id = :group_id AND user_id = :user_id AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);

        if (!$stmt->fetch()) {
            throw new RuntimeException('Gruppo non disponibile.');
        }
    }

    private function assertCampaignVisible(int $campaignId, int $userId): void
    {
        $groupId = $this->groupIdForCampaign($campaignId);
        if (!$groupId) {
            throw new RuntimeException('Campagna non collegata a un gruppo.');
        }

        $this->assertGroupMember($groupId, $userId);
    }

    private function assertCanManageCampaign(int $campaignId, int $userId): void
    {
        $stmt = $this->pdo->prepare(
            "SELECT role FROM mdp_campaign_participants
             WHERE campaign_id = :campaign_id AND user_id = :user_id AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute([
            'campaign_id' => $campaignId,
            'user_id' => $userId,
        ]);
        $role = $stmt->fetchColumn();

        if (!in_array($role, ['master', 'co_master'], true)) {
            throw new RuntimeException('Non hai i permessi per gestire questa campagna.');
        }
    }

    private function assertUserInCampaignGroup(int $campaignId, int $targetUserId): void
    {
        $groupId = $this->groupIdForCampaign($campaignId);
        if (!$groupId) {
            throw new RuntimeException('Campagna non collegata a un gruppo.');
        }

        $this->assertGroupMember($groupId, $targetUserId);
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
