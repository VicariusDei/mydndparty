<?php

final class CombatRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function activeEncounterByCampaign(int $campaignId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, campaign_id, name, current_round, is_active, created_at, updated_at
             FROM mdp_encounters
             WHERE campaign_id = :campaign_id
             ORDER BY is_active DESC, id DESC
             LIMIT 1'
        );
        $stmt->execute(['campaign_id' => $campaignId]);
        $encounter = $stmt->fetch();

        return $encounter ?: null;
    }

    public function findEncounterInCampaign(int $encounterId, int $campaignId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, campaign_id, name, current_round, is_active, created_at, updated_at
             FROM mdp_encounters
             WHERE id = :id AND campaign_id = :campaign_id
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $encounterId,
            'campaign_id' => $campaignId,
        ]);
        $encounter = $stmt->fetch();

        return $encounter ?: null;
    }

    public function listEncountersByCampaign(int $campaignId, int $limit = 20): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->pdo->prepare(
            'SELECT e.id, e.campaign_id, e.name, e.current_round, e.is_active, e.created_at, e.updated_at,
                    COUNT(c.id) AS combatants_count
             FROM mdp_encounters e
             LEFT JOIN mdp_combatants c ON c.encounter_id = e.id
             WHERE e.campaign_id = :campaign_id
             GROUP BY e.id, e.campaign_id, e.name, e.current_round, e.is_active, e.created_at, e.updated_at
             ORDER BY e.is_active DESC, e.id DESC
             LIMIT ' . $limit
        );
        $stmt->execute(['campaign_id' => $campaignId]);

        return $stmt->fetchAll();
    }

    public function createEncounter(int $campaignId, string $name): int
    {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare('UPDATE mdp_encounters SET is_active = 0, updated_at = NOW() WHERE campaign_id = :campaign_id');
        $stmt->execute(['campaign_id' => $campaignId]);

        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_encounters (campaign_id, name, current_round, is_active)
             VALUES (:campaign_id, :name, 1, 1)'
        );
        $stmt->execute([
            'campaign_id' => $campaignId,
            'name' => $name,
        ]);

        $id = (int)$this->pdo->lastInsertId();
        $this->pdo->commit();

        return $id;
    }

    public function activateEncounter(int $encounterId, int $campaignId): void
    {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare('UPDATE mdp_encounters SET is_active = 0, updated_at = NOW() WHERE campaign_id = :campaign_id');
        $stmt->execute(['campaign_id' => $campaignId]);

        $stmt = $this->pdo->prepare('UPDATE mdp_encounters SET is_active = 1, updated_at = NOW() WHERE id = :id AND campaign_id = :campaign_id');
        $stmt->execute([
            'id' => $encounterId,
            'campaign_id' => $campaignId,
        ]);

        $this->pdo->commit();
    }

    public function listCombatantsByEncounter(int $encounterId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.encounter_id, c.party_member_id, c.name, c.type, c.initiative, c.initiative_bonus, c.is_slow, c.has_acted, c.sort_order,
                    pm.character_name, pm.player_name, pm.class_name, pm.ancestry_name
             FROM mdp_combatants c
             LEFT JOIN mdp_party_members pm ON pm.id = c.party_member_id
             WHERE c.encounter_id = :encounter_id
             ORDER BY c.has_acted ASC, c.is_slow ASC, c.initiative DESC, c.initiative_bonus DESC, c.sort_order ASC, c.name ASC'
        );
        $stmt->execute(['encounter_id' => $encounterId]);
        $combatants = $stmt->fetchAll();

        if (!$combatants) {
            return [];
        }

        $effectsByCombatant = $this->effectsByCombatants(array_column($combatants, 'id'));
        foreach ($combatants as &$combatant) {
            $combatant['effects'] = $effectsByCombatant[(int)$combatant['id']] ?? [];
        }

        return $combatants;
    }

    public function addPartyMember(int $encounterId, int $campaignId, int $partyMemberId, ?int $initiative = null): int
    {
        $member = $this->partyMemberInCampaign($partyMemberId, $campaignId);
        if (!$member) {
            throw new RuntimeException('Personaggio non trovato nella campagna attiva.');
        }

        $existing = $this->combatantByPartyMember($encounterId, $partyMemberId);
        if ($existing) {
            return (int)$existing['id'];
        }

        return $this->addCombatant(
            $encounterId,
            (string)$member['character_name'],
            'player',
            $initiative ?? 0,
            (int)$member['initiative_bonus'],
            false,
            $partyMemberId
        );
    }

    public function addCombatant(int $encounterId, string $name, string $type, int $initiative, int $initiativeBonus, bool $isSlow, ?int $partyMemberId = null): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_combatants (encounter_id, party_member_id, name, type, initiative, initiative_bonus, is_slow, has_acted, sort_order)
             VALUES (:encounter_id, :party_member_id, :name, :type, :initiative, :initiative_bonus, :is_slow, 0, :sort_order)'
        );
        $stmt->execute([
            'encounter_id' => $encounterId,
            'party_member_id' => $partyMemberId,
            'name' => $name,
            'type' => in_array($type, ['player', 'enemy', 'npc'], true) ? $type : 'enemy',
            'initiative' => $initiative,
            'initiative_bonus' => $initiativeBonus,
            'is_slow' => $isSlow ? 1 : 0,
            'sort_order' => $this->nextSortOrder($encounterId),
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function nextTurn(int $encounterId): ?array
    {
        $current = $this->currentTurn($encounterId);
        if (!$current) {
            return null;
        }

        $stmt = $this->pdo->prepare('UPDATE mdp_combatants SET has_acted = 1 WHERE id = :id');
        $stmt->execute(['id' => $current['id']]);

        return $this->currentTurn($encounterId);
    }

    public function newRound(int $encounterId): void
    {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare('UPDATE mdp_encounters SET current_round = current_round + 1, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $encounterId]);

        $stmt = $this->pdo->prepare('UPDATE mdp_combatants SET has_acted = 0 WHERE encounter_id = :encounter_id');
        $stmt->execute(['encounter_id' => $encounterId]);

        $stmt = $this->pdo->prepare(
            'UPDATE mdp_effects e
             JOIN mdp_combatants c ON c.id = e.combatant_id
             SET e.remaining_rounds = GREATEST(e.remaining_rounds - 1, 0)
             WHERE c.encounter_id = :encounter_id AND e.is_permanent = 0'
        );
        $stmt->execute(['encounter_id' => $encounterId]);

        $stmt = $this->pdo->prepare(
            'DELETE e FROM mdp_effects e
             JOIN mdp_combatants c ON c.id = e.combatant_id
             WHERE c.encounter_id = :encounter_id AND e.is_permanent = 0 AND e.remaining_rounds <= 0'
        );
        $stmt->execute(['encounter_id' => $encounterId]);

        $this->pdo->commit();
    }

    public function addEffect(int $combatantId, int $encounterId, string $name, int $remainingRounds, bool $isPermanent): int
    {
        if (!$this->combatantInEncounter($combatantId, $encounterId)) {
            throw new RuntimeException('Combattente non trovato nell encounter attivo.');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO mdp_effects (combatant_id, name, remaining_rounds, is_permanent)
             VALUES (:combatant_id, :name, :remaining_rounds, :is_permanent)'
        );
        $stmt->execute([
            'combatant_id' => $combatantId,
            'name' => $name,
            'remaining_rounds' => max(0, $remainingRounds),
            'is_permanent' => $isPermanent ? 1 : 0,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function removeEffect(int $effectId, int $encounterId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE e FROM mdp_effects e
             JOIN mdp_combatants c ON c.id = e.combatant_id
             WHERE e.id = :effect_id AND c.encounter_id = :encounter_id'
        );
        $stmt->execute([
            'effect_id' => $effectId,
            'encounter_id' => $encounterId,
        ]);
    }

    public function countCombatantsByCampaign(int $campaignId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM mdp_combatants c
             JOIN mdp_encounters e ON e.id = c.encounter_id
             WHERE e.campaign_id = :campaign_id'
        );
        $stmt->execute(['campaign_id' => $campaignId]);

        return (int)$stmt->fetchColumn();
    }

    private function currentTurn(int $encounterId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, encounter_id, party_member_id, name, type, initiative, initiative_bonus, is_slow, has_acted, sort_order
             FROM mdp_combatants
             WHERE encounter_id = :encounter_id AND has_acted = 0
             ORDER BY is_slow ASC, initiative DESC, initiative_bonus DESC, sort_order ASC, name ASC
             LIMIT 1'
        );
        $stmt->execute(['encounter_id' => $encounterId]);
        $current = $stmt->fetch();

        return $current ?: null;
    }

    private function partyMemberInCampaign(int $partyMemberId, int $campaignId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, campaign_id, user_id, player_name, character_name, class_name, ancestry_name, motto, initiative_bonus
             FROM mdp_party_members
             WHERE id = :id AND campaign_id = :campaign_id
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $partyMemberId,
            'campaign_id' => $campaignId,
        ]);
        $member = $stmt->fetch();

        return $member ?: null;
    }

    private function combatantByPartyMember(int $encounterId, int $partyMemberId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id FROM mdp_combatants WHERE encounter_id = :encounter_id AND party_member_id = :party_member_id LIMIT 1');
        $stmt->execute([
            'encounter_id' => $encounterId,
            'party_member_id' => $partyMemberId,
        ]);
        $combatant = $stmt->fetch();

        return $combatant ?: null;
    }

    private function combatantInEncounter(int $combatantId, int $encounterId): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM mdp_combatants WHERE id = :id AND encounter_id = :encounter_id LIMIT 1');
        $stmt->execute([
            'id' => $combatantId,
            'encounter_id' => $encounterId,
        ]);

        return (bool)$stmt->fetch();
    }

    private function nextSortOrder(int $encounterId): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM mdp_combatants WHERE encounter_id = :encounter_id');
        $stmt->execute(['encounter_id' => $encounterId]);

        return (int)$stmt->fetchColumn();
    }

    private function effectsByCombatants(array $combatantIds): array
    {
        $combatantIds = array_values(array_filter(array_map('intval', $combatantIds)));
        if (!$combatantIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($combatantIds), '?'));
        $stmt = $this->pdo->prepare(
            'SELECT id, combatant_id, name, remaining_rounds, is_permanent, created_at
             FROM mdp_effects
             WHERE combatant_id IN (' . $placeholders . ')
             ORDER BY is_permanent DESC, remaining_rounds DESC, name ASC'
        );
        $stmt->execute($combatantIds);

        $byCombatant = [];
        foreach ($stmt->fetchAll() as $effect) {
            $id = (int)$effect['combatant_id'];
            if (!isset($byCombatant[$id])) {
                $byCombatant[$id] = [];
            }
            $byCombatant[$id][] = $effect;
        }

        return $byCombatant;
    }
}
