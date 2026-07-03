<?php

final class PlayerNotesController
{
    public function __construct(
        private PlayerNotesRepository $notes,
        private CampaignRepository $campaigns,
        private array $config
    ) {
    }

    public function list(): void
    {
        $campaignId = $this->activeCampaignId();
        if ($campaignId <= 0) {
            Response::ok([
                'player_notes' => [],
            ]);
        }

        Response::ok([
            'player_notes' => $this->notes->listByCampaign($campaignId),
        ]);
    }

    public function create(): void
    {
        $userId = Auth::userId($this->config);
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $content = trim((string)($body['content'] ?? ''));

        if ($content === '') {
            Response::error('Il contenuto della nota e obbligatorio', 422);
        }

        $body['content'] = $content;
        $this->notes->create($campaignId, $userId, $body);
        $this->respondWithState($campaignId);
    }

    public function update(): void
    {
        $userId = Auth::userId($this->config);
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $noteId = (int)($body['id'] ?? 0);
        $content = trim((string)($body['content'] ?? ''));

        if ($noteId <= 0 || $content === '') {
            Response::error('Nota e contenuto sono obbligatori', 422);
        }

        $body['content'] = $content;
        $this->notes->update($campaignId, $noteId, $userId, $body);
        $this->respondWithState($campaignId);
    }

    public function delete(): void
    {
        $userId = Auth::userId($this->config);
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $noteId = (int)($body['id'] ?? 0);

        if ($noteId <= 0) {
            Response::error('Nota obbligatoria', 422);
        }

        $this->notes->softDelete($campaignId, $noteId, $userId);
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

    private function respondWithState(int $campaignId): void
    {
        Response::ok([
            'player_notes' => $this->notes->listByCampaign($campaignId),
        ]);
    }
}
