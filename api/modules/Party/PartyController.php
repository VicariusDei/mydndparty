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
        $userId = Auth::userId($this->config);
        $campaignId = (int)($_GET['campaign_id'] ?? 0);

        if ($campaignId <= 0) {
            $active = $this->campaigns->activeByUser($userId);
            $campaignId = $active ? (int)$active['id'] : 0;
        }

        if ($campaignId <= 0) {
            Response::ok([
                'party_members' => [],
            ]);
        }

        Response::ok([
            'party_members' => $this->party->listByCampaign($campaignId),
        ]);
    }

    public function create(): void
    {
        $userId = Auth::userId($this->config);
        $body = Request::jsonBody();

        $campaignId = (int)($body['campaign_id'] ?? 0);
        if ($campaignId <= 0) {
            $active = $this->campaigns->activeByUser($userId);
            $campaignId = $active ? (int)$active['id'] : 0;
        }

        if ($campaignId <= 0) {
            Response::error('Nessuna campagna attiva disponibile', 422);
        }

        $characterName = trim((string)($body['character_name'] ?? ''));
        $playerName = trim((string)($body['player_name'] ?? ''));

        if ($characterName === '' || $playerName === '') {
            Response::error('Nome giocatore e nome personaggio sono obbligatori', 422);
        }

        $id = $this->party->create([
            'campaign_id' => $campaignId,
            'user_id' => $userId,
            'player_name' => $playerName,
            'character_name' => $characterName,
            'class_name' => trim((string)($body['class_name'] ?? '')) ?: null,
            'ancestry_name' => trim((string)($body['ancestry_name'] ?? '')) ?: null,
            'motto' => trim((string)($body['motto'] ?? '')) ?: null,
            'initiative_bonus' => (int)($body['initiative_bonus'] ?? 0),
        ]);

        Response::ok([
            'id' => $id,
        ]);
    }
}
