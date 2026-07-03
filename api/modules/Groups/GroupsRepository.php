<?php

final class GroupsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT g.id, g.name, g.slug, g.description, g.created_by_user_id, g.is_active, g.created_at, g.updated_at,
                    gm.role AS my_role,
                    gm.status AS my_status,
                    COUNT(DISTINCT active_members.id) AS members_count,
                    COUNT(DISTINCT gc.id) AS campaigns_count
             FROM mdp_game_groups g
             INNER JOIN mdp_game_group_members gm ON gm.game_group_id = g.id AND gm.user_id = :user_id
             LEFT JOIN mdp_game_group_members active_members ON active_members.game_group_id = g.id AND active_members.status = 'active'
             LEFT JOIN mdp_game_group_campaigns gc ON gc.game_group_id = g.id
             WHERE gm.status = 'active' AND g.is_active = 1
             GROUP BY g.id, g.name, g.slug, g.description, g.created_by_user_id, g.is_active, g.created_at, g.updated_at, gm.role, gm.status
             ORDER BY g.name ASC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function create(int $userId, string $name, ?string $description = null): int
    {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_game_groups (name, slug, description, created_by_user_id)
             VALUES (:name, :slug, :description, :created_by_user_id)'
        );
        $stmt->execute([
            'name' => $this->clip($name, 160),
            'slug' => $this->uniqueSlug($name),
            'description' => $this->nullableClip((string)($description ?? ''), 10000),
            'created_by_user_id' => $userId,
        ]);

        $groupId = (int)$this->pdo->lastInsertId();
        $stmt = $this->pdo->prepare(
            "INSERT INTO mdp_game_group_members
                (game_group_id, user_id, username_snapshot, role, status, joined_at)
             SELECT :group_id, id, username, 'owner', 'active', NOW()
             FROM mdp_users
             WHERE id = :user_id"
        );
        $stmt->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);

        $this->pdo->commit();

        return $groupId;
    }

    public function members(int $groupId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT gm.id, gm.game_group_id, gm.user_id, gm.username_snapshot, gm.role, gm.status,
                    gm.invited_by_user_id, gm.invited_at, gm.joined_at, gm.created_at, gm.updated_at,
                    u.username, u.display_name, u.email, u.avatar_url
             FROM mdp_game_group_members gm
             INNER JOIN mdp_users u ON u.id = gm.user_id
             WHERE gm.game_group_id = :group_id
             ORDER BY gm.role ASC, u.display_name ASC, u.username ASC'
        );
        $stmt->execute(['group_id' => $groupId]);

        return $stmt->fetchAll();
    }

    public function addMemberByUsername(int $groupId, int $actorUserId, string $username, string $role = 'member'): void
    {
        $this->assertCanManageGroup($groupId, $actorUserId);
        $user = $this->findUserByUsername($username);
        if (!$user) {
            throw new RuntimeException('Utente non trovato.');
        }

        $role = in_array($role, ['admin', 'member'], true) ? $role : 'member';
        $stmt = $this->pdo->prepare(
            "INSERT INTO mdp_game_group_members
                (game_group_id, user_id, username_snapshot, role, status, invited_by_user_id, invited_at, joined_at)
             VALUES
                (:group_id, :user_id, :username_snapshot, :role, 'active', :actor_user_id, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                role = VALUES(role),
                status = 'active',
                updated_at = NOW()"
        );
        $stmt->execute([
            'group_id' => $groupId,
            'user_id' => (int)$user['id'],
            'username_snapshot' => $user['username'],
            'role' => $role,
            'actor_user_id' => $actorUserId,
        ]);
    }

    public function findUserByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, display_name, email, avatar_url
             FROM mdp_users
             WHERE username = :username
             LIMIT 1'
        );
        $stmt->execute(['username' => trim($username)]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function isMember(int $groupId, int $userId): bool
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

        return (bool)$stmt->fetch();
    }

    public function assertCanManageGroup(int $groupId, int $userId): void
    {
        $stmt = $this->pdo->prepare(
            "SELECT role FROM mdp_game_group_members
             WHERE game_group_id = :group_id AND user_id = :user_id AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);
        $role = $stmt->fetchColumn();

        if (!in_array($role, ['owner', 'admin'], true)) {
            throw new RuntimeException('Non hai i permessi per gestire questo gruppo.');
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        if ($base === '') {
            $base = 'gruppo';
        }

        $slug = $base;
        $i = 2;
        while ($this->slugExists($slug)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM mdp_game_groups WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);

        return (bool)$stmt->fetch();
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
