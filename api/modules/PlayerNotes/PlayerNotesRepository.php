<?php

final class PlayerNotesRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listByCampaign(int $campaignId, int $limit = 100): array
    {
        $limit = max(1, min($limit, 300));
        $stmt = $this->pdo->prepare(
            "SELECT n.id, n.campaign_id, n.session_id, n.author_user_id, n.author_party_member_id, n.author_label,
                    n.origin_channel, n.note_type, n.title, n.content, n.share_scope, n.status, n.master_flag,
                    n.corrected_by_user_id, n.corrected_at, n.converted_target_type, n.converted_target_id,
                    n.created_at, n.updated_at,
                    s.session_number, s.title AS session_title,
                    u.display_name AS author_display_name,
                    u.username AS author_username,
                    pm.character_name AS author_character_name,
                    pm.player_name AS author_player_name,
                    cu.display_name AS corrected_by_display_name,
                    cu.username AS corrected_by_username
             FROM mdp_player_notes n
             LEFT JOIN mdp_sessions s ON s.id = n.session_id
             LEFT JOIN mdp_users u ON u.id = n.author_user_id
             LEFT JOIN mdp_party_members pm ON pm.id = n.author_party_member_id
             LEFT JOIN mdp_users cu ON cu.id = n.corrected_by_user_id
             WHERE n.campaign_id = :campaign_id
               AND n.status <> 'deleted'
             ORDER BY n.created_at DESC, n.id DESC
             LIMIT " . $limit
        );
        $stmt->execute(['campaign_id' => $campaignId]);
        $notes = $stmt->fetchAll();

        if (!$notes) {
            return [];
        }

        $recipients = $this->recipientsByNotes(array_column($notes, 'id'));
        foreach ($notes as &$note) {
            $note['recipients'] = $recipients[(int)$note['id']] ?? [];
        }

        return $notes;
    }

    public function create(int $campaignId, int $userId, array $data): int
    {
        $shareScope = $this->shareScope((string)($data['share_scope'] ?? 'party'));
        $noteType = $this->noteType((string)($data['note_type'] ?? 'note'));
        $authorPartyMemberId = $this->partyMemberId($campaignId, (int)($data['author_party_member_id'] ?? 0));
        $sessionId = $this->sessionId($campaignId, (int)($data['session_id'] ?? 0));

        $stmt = $this->pdo->prepare(
            "INSERT INTO mdp_player_notes
                (campaign_id, session_id, author_user_id, author_party_member_id, author_label, origin_channel,
                 note_type, title, content, share_scope, status, master_flag)
             VALUES
                (:campaign_id, :session_id, :author_user_id, :author_party_member_id, :author_label, 'web',
                 :note_type, :title, :content, :share_scope, 'visible', :master_flag)"
        );
        $stmt->execute([
            'campaign_id' => $campaignId,
            'session_id' => $sessionId,
            'author_user_id' => $userId,
            'author_party_member_id' => $authorPartyMemberId,
            'author_label' => $this->nullableClip((string)($data['author_label'] ?? ''), 120),
            'note_type' => $noteType,
            'title' => $this->nullableClip((string)($data['title'] ?? ''), 180),
            'content' => $this->clip((string)$data['content'], 60000),
            'share_scope' => $shareScope,
            'master_flag' => $this->masterFlag((string)($data['master_flag'] ?? 'none')),
        ]);

        $noteId = (int)$this->pdo->lastInsertId();
        $this->replaceRecipients($campaignId, $noteId, $data);

        return $noteId;
    }

    public function update(int $campaignId, int $noteId, int $userId, array $data): void
    {
        $note = $this->findInCampaign($campaignId, $noteId);
        if (!$note) {
            throw new RuntimeException('Nota non trovata nella campagna attiva.');
        }

        $this->pdo->beginTransaction();
        $this->insertRevision($note, $userId, (string)($data['revision_reason'] ?? 'Aggiornamento nota'));

        $stmt = $this->pdo->prepare(
            'UPDATE mdp_player_notes
             SET session_id = :session_id,
                 note_type = :note_type,
                 title = :title,
                 content = :content,
                 share_scope = :share_scope,
                 status = :status,
                 master_flag = :master_flag,
                 corrected_by_user_id = :corrected_by_user_id,
                 corrected_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id AND campaign_id = :campaign_id'
        );
        $stmt->execute([
            'session_id' => $this->sessionId($campaignId, (int)($data['session_id'] ?? 0)),
            'note_type' => $this->noteType((string)($data['note_type'] ?? $note['note_type'])),
            'title' => $this->nullableClip((string)($data['title'] ?? ''), 180),
            'content' => $this->clip((string)($data['content'] ?? $note['content']), 60000),
            'share_scope' => $this->shareScope((string)($data['share_scope'] ?? $note['share_scope'])),
            'status' => $this->status((string)($data['status'] ?? 'corrected')),
            'master_flag' => $this->masterFlag((string)($data['master_flag'] ?? $note['master_flag'])),
            'corrected_by_user_id' => $userId,
            'id' => $noteId,
            'campaign_id' => $campaignId,
        ]);

        $this->replaceRecipients($campaignId, $noteId, $data);
        $this->pdo->commit();
    }

    public function softDelete(int $campaignId, int $noteId, int $userId): void
    {
        $note = $this->findInCampaign($campaignId, $noteId);
        if (!$note) {
            throw new RuntimeException('Nota non trovata nella campagna attiva.');
        }

        $this->pdo->beginTransaction();
        $this->insertRevision($note, $userId, 'Eliminazione logica');

        $stmt = $this->pdo->prepare(
            "UPDATE mdp_player_notes
             SET status = 'deleted', corrected_by_user_id = :user_id, corrected_at = NOW(), updated_at = NOW()
             WHERE id = :id AND campaign_id = :campaign_id"
        );
        $stmt->execute([
            'user_id' => $userId,
            'id' => $noteId,
            'campaign_id' => $campaignId,
        ]);

        $this->pdo->commit();
    }

    private function findInCampaign(int $campaignId, int $noteId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM mdp_player_notes WHERE id = :id AND campaign_id = :campaign_id LIMIT 1');
        $stmt->execute([
            'id' => $noteId,
            'campaign_id' => $campaignId,
        ]);
        $note = $stmt->fetch();

        return $note ?: null;
    }

    private function insertRevision(array $note, int $userId, string $reason): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_player_note_revisions
                (note_id, edited_by_user_id, previous_title, previous_content, previous_share_scope, previous_status, revision_reason)
             VALUES
                (:note_id, :edited_by_user_id, :previous_title, :previous_content, :previous_share_scope, :previous_status, :revision_reason)'
        );
        $stmt->execute([
            'note_id' => $note['id'],
            'edited_by_user_id' => $userId,
            'previous_title' => $note['title'],
            'previous_content' => $note['content'],
            'previous_share_scope' => $note['share_scope'],
            'previous_status' => $note['status'],
            'revision_reason' => $this->nullableClip($reason, 220),
        ]);
    }

    private function replaceRecipients(int $campaignId, int $noteId, array $data): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM mdp_player_note_recipients WHERE note_id = :note_id');
        $stmt->execute(['note_id' => $noteId]);

        $partyMemberIds = $this->intList($data['recipient_party_member_ids'] ?? []);
        if (!$partyMemberIds) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_player_note_recipients (note_id, recipient_party_member_id)
             VALUES (:note_id, :recipient_party_member_id)'
        );

        foreach ($partyMemberIds as $partyMemberId) {
            $validId = $this->partyMemberId($campaignId, $partyMemberId);
            if ($validId) {
                $stmt->execute([
                    'note_id' => $noteId,
                    'recipient_party_member_id' => $validId,
                ]);
            }
        }
    }

    private function recipientsByNotes(array $noteIds): array
    {
        $noteIds = array_values(array_filter(array_map('intval', $noteIds)));
        if (!$noteIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($noteIds), '?'));
        $stmt = $this->pdo->prepare(
            'SELECT r.note_id, r.recipient_user_id, r.recipient_party_member_id,
                    u.display_name, u.username,
                    pm.character_name, pm.player_name
             FROM mdp_player_note_recipients r
             LEFT JOIN mdp_users u ON u.id = r.recipient_user_id
             LEFT JOIN mdp_party_members pm ON pm.id = r.recipient_party_member_id
             WHERE r.note_id IN (' . $placeholders . ')
             ORDER BY pm.character_name ASC, u.display_name ASC, u.username ASC'
        );
        $stmt->execute($noteIds);

        $byNote = [];
        foreach ($stmt->fetchAll() as $recipient) {
            $id = (int)$recipient['note_id'];
            if (!isset($byNote[$id])) {
                $byNote[$id] = [];
            }
            $byNote[$id][] = $recipient;
        }

        return $byNote;
    }

    private function partyMemberId(int $campaignId, int $partyMemberId): ?int
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

    private function sessionId(int $campaignId, int $sessionId): ?int
    {
        if ($sessionId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT id FROM mdp_sessions WHERE id = :id AND campaign_id = :campaign_id LIMIT 1');
        $stmt->execute([
            'id' => $sessionId,
            'campaign_id' => $campaignId,
        ]);

        return $stmt->fetch() ? $sessionId : null;
    }

    private function intList($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            $number = (int)$item;
            if ($number > 0) {
                $result[] = $number;
            }
        }

        return array_values(array_unique($result));
    }

    private function shareScope(string $value): string
    {
        return in_array($value, ['party', 'private', 'restricted', 'master', 'public_readonly'], true) ? $value : 'party';
    }

    private function noteType(string $value): string
    {
        return in_array($value, ['note', 'npc', 'place', 'quest', 'loot', 'question', 'rules', 'idea', 'scene', 'decision'], true) ? $value : 'note';
    }

    private function status(string $value): string
    {
        return in_array($value, ['visible', 'hidden', 'corrected', 'converted', 'deleted'], true) ? $value : 'corrected';
    }

    private function masterFlag(string $value): string
    {
        return in_array($value, ['none', 'needs_review', 'verified', 'spoiler', 'incorrect'], true) ? $value : 'none';
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
