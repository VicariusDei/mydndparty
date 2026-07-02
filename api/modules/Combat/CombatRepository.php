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

    public function listCombatantsByEncounter(int $encounterId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.encounter_id, c.party_member_id, c.name, c.type, c.initiative, c.initiative_bonus, c.is_slow, c.has_acted, c.sort_order,
                    pm.character_name, pm.player_name, pm.class_name, pm.ancestry_name
             FROM mdp_combatants c
             LEFT JOIN mdp_party_members pm ON pm.id = c.party_member_id
             WHERE c.encounter_id = :encounter_id
             ORDER BY c.is_slow ASC, c.initiative DESC, c.initiative_bonus DESC, c.sort_order ASC, c.name ASC'
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
