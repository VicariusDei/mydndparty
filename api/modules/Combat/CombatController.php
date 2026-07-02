<?php

final class CombatController
{
    public function __construct(
        private CombatRepository $combat,
        private CampaignRepository $campaigns,
        private array $config
    ) {
    }

    public function active(): void
    {
        $campaignId = $this->activeCampaignId();
        if ($campaignId <= 0) {
            $this->emptyPayload();
        }

        $this->respondWithState($campaignId);
    }

    public function create(): void
    {
        $campaignId = $this->activeCampaignId();
        if ($campaignId <= 0) {
            Response::error('Nessuna campagna attiva disponibile', 422);
        }

        $body = Request::jsonBody();
        $name = trim((string)($body['name'] ?? ''));
        if ($name === '') {
            $name = 'Nuovo combattimento';
        }

        $this->combat->createEncounter($campaignId, mb_substr($name, 0, 160));
        $this->respondWithState($campaignId);
    }

    public function activate(): void
    {
        $campaignId = $this->activeCampaignId();
        $body = Request::jsonBody();
        $encounterId = (int)($body['encounter_id'] ?? 0);
        if ($campaignId <= 0 || !$this->combat->findEncounterInCampaign($encounterId, $campaignId)) {
            Response::error('Encounter non trovato', 404);
        }

        $this->combat->activateEncounter($encounterId, $campaignId);
        $this->respondWithState($campaignId);
    }

    public function addPartyMember(): void
    {
        [$campaignId, $encounter] = $this->activeEncounter();
        $body = Request::jsonBody();
        $partyMemberId = (int)($body['party_member_id'] ?? 0);
        $initiative = array_key_exists('initiative', $body) && $body['initiative'] !== '' ? (int)$body['initiative'] : null;

        if ($partyMemberId <= 0) {
            Response::error('Personaggio obbligatorio', 422);
        }

        $this->combat->addPartyMember((int)$encounter['id'], $campaignId, $partyMemberId, $initiative);
        $this->respondWithState($campaignId);
    }

    public function addCombatant(): void
    {
        [$campaignId, $encounter] = $this->activeEncounter();
        $body = Request::jsonBody();
        $name = trim((string)($body['name'] ?? ''));
        $type = trim((string)($body['type'] ?? 'enemy'));

        if ($name === '') {
            Response::error('Nome combattente obbligatorio', 422);
        }

        $this->combat->addCombatant(
            (int)$encounter['id'],
            mb_substr($name, 0, 160),
            $type,
            (int)($body['initiative'] ?? 0),
            (int)($body['initiative_bonus'] ?? 0),
            !empty($body['is_slow'])
        );
        $this->respondWithState($campaignId);
    }

    public function nextTurn(): void
    {
        [$campaignId, $encounter] = $this->activeEncounter();
        $next = $this->combat->nextTurn((int)$encounter['id']);
        $state = $this->state($campaignId);
        $state['turn_complete'] = $next === null;
        Response::ok($state);
    }

    public function newRound(): void
    {
        [$campaignId, $encounter] = $this->activeEncounter();
        $this->combat->newRound((int)$encounter['id']);
        $this->respondWithState($campaignId);
    }

    public function addEffect(): void
    {
        [$campaignId, $encounter] = $this->activeEncounter();
        $body = Request::jsonBody();
        $combatantId = (int)($body['combatant_id'] ?? 0);
        $name = trim((string)($body['name'] ?? ''));

        if ($combatantId <= 0 || $name === '') {
            Response::error('Combattente ed effetto sono obbligatori', 422);
        }

        $this->combat->addEffect(
            $combatantId,
            (int)$encounter['id'],
            mb_substr($name, 0, 120),
            (int)($body['remaining_rounds'] ?? 0),
            !empty($body['is_permanent'])
        );
        $this->respondWithState($campaignId);
    }

    public function removeEffect(): void
    {
        [$campaignId] = $this->activeEncounter();
        $body = Request::jsonBody();
        $effectId = (int)($body['effect_id'] ?? 0);
        if ($effectId <= 0) {
            Response::error('Effetto obbligatorio', 422);
        }

        $encounter = $this->combat->activeEncounterByCampaign($campaignId);
        $this->combat->removeEffect($effectId, (int)$encounter['id']);
        $this->respondWithState($campaignId);
    }

    private function activeCampaignId(): int
    {
        $userId = Auth::userId($this->config);
        $campaignId = (int)($_GET['campaign_id'] ?? 0);
        if ($campaignId <= 0) {
            $active = $this->campaigns->activeByUser($userId);
            $campaignId = $active ? (int)$active['id'] : 0;
        }

        return $campaignId;
    }

    private function activeEncounter(): array
    {
        $campaignId = $this->activeCampaignId();
        if ($campaignId <= 0) {
            Response::error('Nessuna campagna attiva disponibile', 422);
        }

        $encounter = $this->combat->activeEncounterByCampaign($campaignId);
        if (!$encounter) {
            Response::error('Nessun encounter attivo', 422);
        }

        return [$campaignId, $encounter];
    }

    private function state(int $campaignId): array
    {
        $encounter = $this->combat->activeEncounterByCampaign($campaignId);
        return [
            'encounter' => $encounter,
            'combatants' => $encounter ? $this->combat->listCombatantsByEncounter((int)$encounter['id']) : [],
            'encounters' => $this->combat->listEncountersByCampaign($campaignId),
        ];
    }

    private function respondWithState(int $campaignId): void
    {
        Response::ok($this->state($campaignId));
    }

    private function emptyPayload(): void
    {
        Response::ok([
            'encounter' => null,
            'combatants' => [],
            'encounters' => [],
        ]);
    }
}
