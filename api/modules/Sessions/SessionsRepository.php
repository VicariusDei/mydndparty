<?php

final class SessionsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listByCampaign(int $campaignId, int $limit = 100): array
    {
        $limit = max(1, min($limit, 300));
        $stmt = $this->pdo->prepare(
            "SELECT s.id, s.campaign_id, s.session_number, s.title, s.real_date, s.world_date,
                    s.summary, s.master_notes, s.status, s.visibility, s.created_by_user_id,
                    s.created_at, s.updated_at,
                    u.display_name AS created_by_display_name,
                    u.username AS created_by_username,
                    COUNT(n.id) AS player_notes_count
             FROM mdp_sessions s
             LEFT JOIN mdp_users u ON u.id = s.created_by_user_id
             LEFT JOIN mdp_player_notes n ON n.session_id = s.id AND n.status <> 'deleted'
             WHERE s.campaign_id = :campaign_id
             GROUP BY s.id, s.campaign_id, s.session_number, s.title, s.real_date, s.world_date,
                      s.summary, s.master_notes, s.status, s.visibility, s.created_by_user_id,
                      s.created_at, s.updated_at, u.display_name, u.username
             ORDER BY s.session_number DESC, s.id DESC
             LIMIT " . $limit
        );
        $stmt->execute(['campaign_id' => $campaignId]);

        return $stmt->fetchAll();
    }

    public function latestByCampaign(int $campaignId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, campaign_id, session_number, title, real_date, world_date, summary, master_notes, status, visibility, created_by_user_id, created_at, updated_at
             FROM mdp_sessions
             WHERE campaign_id = :campaign_id
             ORDER BY session_number DESC, id DESC
             LIMIT 1'
        );
        $stmt->execute(['campaign_id' => $campaignId]);
        $session = $stmt->fetch();

        return $session ?: null;
    }

    public function countByCampaign(int $campaignId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM mdp_sessions WHERE campaign_id = :campaign_id');
        $stmt->execute(['campaign_id' => $campaignId]);

        return (int)$stmt->fetchColumn();
    }

    public function create(int $campaignId, int $userId, array $data): int
    {
        $number = (int)($data['session_number'] ?? 0);
        if ($number <= 0) {
            $number = $this->nextSessionNumber($campaignId);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_sessions
                (campaign_id, session_number, title, real_date, world_date, summary, master_notes, status, visibility, created_by_user_id)
             VALUES
                (:campaign_id, :session_number, :title, :real_date, :world_date, :summary, :master_notes, :status, :visibility, :created_by_user_id)'
        );
        $stmt->execute([
            'campaign_id' => $campaignId,
            'session_number' => $number,
            'title' => $this->clip((string)$data['title'], 180),
            'real_date' => $this->dateOrNull($data['real_date'] ?? null),
            'world_date' => $this->nullableClip((string)($data['world_date'] ?? ''), 120),
            'summary' => $this->nullableClip((string)($data['summary'] ?? ''), 60000),
            'master_notes' => $this->nullableClip((string)($data['master_notes'] ?? ''), 60000),
            'status' => $this->status((string)($data['status'] ?? 'draft')),
            'visibility' => $this->visibility((string)($data['visibility'] ?? 'party')),
            'created_by_user_id' => $userId,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $campaignId, int $sessionId, array $data): void
    {
        if (!$this->findInCampaign($campaignId, $sessionId)) {
            throw new RuntimeException('Sessione non trovata nella campagna attiva.');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE mdp_sessions
             SET session_number = :session_number,
                 title = :title,
                 real_date = :real_date,
                 world_date = :world_date,
                 summary = :summary,
                 master_notes = :master_notes,
                 status = :status,
                 visibility = :visibility,
                 updated_at = NOW()
             WHERE id = :id AND campaign_id = :campaign_id'
        );
        $stmt->execute([
            'session_number' => max(1, (int)($data['session_number'] ?? 1)),
            'title' => $this->clip((string)$data['title'], 180),
            'real_date' => $this->dateOrNull($data['real_date'] ?? null),
            'world_date' => $this->nullableClip((string)($data['world_date'] ?? ''), 120),
            'summary' => $this->nullableClip((string)($data['summary'] ?? ''), 60000),
            'master_notes' => $this->nullableClip((string)($data['master_notes'] ?? ''), 60000),
            'status' => $this->status((string)($data['status'] ?? 'draft')),
            'visibility' => $this->visibility((string)($data['visibility'] ?? 'party')),
            'id' => $sessionId,
            'campaign_id' => $campaignId,
        ]);
    }

    public function deleteIfEmpty(int $campaignId, int $sessionId): void
    {
        if (!$this->findInCampaign($campaignId, $sessionId)) {
            throw new RuntimeException('Sessione non trovata nella campagna attiva.');
        }

        $dependencies = $this->dependencyCounts($sessionId);
        foreach ($dependencies as $count) {
            if ($count > 0) {
                throw new RuntimeException('Sessione non eliminabile: contiene note, timeline, quest o entita collegate.');
            }
        }

        $stmt = $this->pdo->prepare('DELETE FROM mdp_sessions WHERE id = :id AND campaign_id = :campaign_id');
        $stmt->execute([
            'id' => $sessionId,
            'campaign_id' => $campaignId,
        ]);
    }

    public function sessionExistsInCampaign(int $campaignId, int $sessionId): bool
    {
        return $this->findInCampaign($campaignId, $sessionId) !== null;
    }

    private function findInCampaign(int $campaignId, int $sessionId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id FROM mdp_sessions WHERE id = :id AND campaign_id = :campaign_id LIMIT 1');
        $stmt->execute([
            'id' => $sessionId,
            'campaign_id' => $campaignId,
        ]);
        $session = $stmt->fetch();

        return $session ?: null;
    }

    private function nextSessionNumber(int $campaignId): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(MAX(session_number), 0) + 1 FROM mdp_sessions WHERE campaign_id = :campaign_id');
        $stmt->execute(['campaign_id' => $campaignId]);

        return (int)$stmt->fetchColumn();
    }

    private function dependencyCounts(int $sessionId): array
    {
        return [
            'player_notes' => $this->countBySession('mdp_player_notes', $sessionId),
            'timeline_events' => $this->countBySession('mdp_timeline_events', $sessionId),
            'opened_quests' => $this->countByColumn('mdp_quests', 'opened_session_id', $sessionId),
            'closed_quests' => $this->countByColumn('mdp_quests', 'closed_session_id', $sessionId),
            'world_entities' => $this->countByColumn('mdp_world_entities', 'first_session_id', $sessionId),
        ];
    }

    private function countBySession(string $table, int $sessionId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE session_id = :session_id');
        $stmt->execute(['session_id' => $sessionId]);

        return (int)$stmt->fetchColumn();
    }

    private function countByColumn(string $table, string $column, int $value): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $column . ' = :value');
        $stmt->execute(['value' => $value]);

        return (int)$stmt->fetchColumn();
    }

    private function status(string $value): string
    {
        return in_array($value, ['draft', 'published', 'archived'], true) ? $value : 'draft';
    }

    private function visibility(string $value): string
    {
        return in_array($value, ['party', 'master', 'private', 'custom'], true) ? $value : 'party';
    }

    private function dateOrNull($value): ?string
    {
        $value = trim((string)($value ?? ''));
        if ($value === '') {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);
        return $date ? $date->format('Y-m-d') : null;
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
