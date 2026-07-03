<?php

final class PartyController
{
    public function __construct(
        private PartyRepository $party,
        private CampaignRepository $campaigns,
        private array $config
    ) {
    }

    public function list(): void
    {
        $campaignId = $this->activeCampaignId();
        if ($campaignId <= 0) {
            $this->emptyPayload();
        }

        $this->respondWithState($campaignId);
    }

    public function create(): void
    {
        $userId = Auth::userId($this->config);
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $data = $this->payload($body, $campaignId, $userId);

        $this->party->create($data);
        $this->respondWithState($campaignId);
    }

    public function update(): void
    {
        $userId = Auth::userId($this->config);
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $memberId = (int)($body['id'] ?? 0);
        if ($memberId <= 0) {
            Response::error('Personaggio obbligatorio', 422);
        }

        $data = $this->payload($body, $campaignId, $userId);
        $this->party->update($campaignId, $memberId, $data);
        $this->respondWithState($campaignId);
    }

    public function delete(): void
    {
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $memberId = (int)($body['id'] ?? 0);
        if ($memberId <= 0) {
            Response::error('Personaggio obbligatorio', 422);
        }

        $this->party->deleteIfUnused($campaignId, $memberId);
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

    private function requireCampaign(): int
    {
        $campaignId = $this->activeCampaignId();
        if ($campaignId <= 0) {
            Response::error('Nessuna campagna attiva disponibile', 422);
        }

        return $campaignId;
    }

    private function payload(array $body, int $campaignId, int $userId): array
    {
        $characterName = trim((string)($body['character_name'] ?? ''));
        $playerName = trim((string)($body['player_name'] ?? ''));

        if ($characterName === '' || $playerName === '') {
            Response::error('Nome giocatore e nome personaggio sono obbligatori', 422);
        }

        return [
            'campaign_id' => $campaignId,
            'user_id' => $userId,
            'player_name' => $playerName,
            'character_name' => $characterName,
            'class_name' => trim((string)($body['class_name'] ?? '')) ?: null,
            'ancestry_name' => trim((string)($body['ancestry_name'] ?? '')) ?: null,
            'motto' => trim((string)($body['motto'] ?? '')) ?: null,
            'initiative_bonus' => (int)($body['initiative_bonus'] ?? 0),
        ];
    }

    private function respondWithState(int $campaignId): void
    {
        Response::ok([
            'party_members' => $this->party->listByCampaign($campaignId),
        ]);
    }

    private function emptyPayload(): void
    {
        Response::ok([
            'party_members' => [],
        ]);
    }
}
