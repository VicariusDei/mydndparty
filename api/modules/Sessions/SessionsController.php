<?php

final class SessionsController
{
    public function __construct(
        private SessionsRepository $sessions,
        private CampaignRepository $campaigns,
        private array $config
    ) {
    }

    public function list(): void
    {
        $campaignId = $this->activeCampaignId();
        if ($campaignId <= 0) {
            Response::ok([
                'sessions' => [],
                'latest_session' => null,
            ]);
        }

        $this->respondWithState($campaignId);
    }

    public function create(): void
    {
        $userId = Auth::userId($this->config);
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $title = trim((string)($body['title'] ?? ''));

        if ($title === '') {
            Response::error('Il titolo sessione e obbligatorio', 422);
        }

        $body['title'] = $title;
        $this->sessions->create($campaignId, $userId, $body);
        $this->respondWithState($campaignId);
    }

    public function update(): void
    {
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $sessionId = (int)($body['id'] ?? 0);
        $title = trim((string)($body['title'] ?? ''));

        if ($sessionId <= 0 || $title === '') {
            Response::error('Sessione e titolo sono obbligatori', 422);
        }

        $body['title'] = $title;
        $this->sessions->update($campaignId, $sessionId, $body);
        $this->respondWithState($campaignId);
    }

    public function delete(): void
    {
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $sessionId = (int)($body['id'] ?? 0);
        if ($sessionId <= 0) {
            Response::error('Sessione obbligatoria', 422);
        }

        $this->sessions->deleteIfEmpty($campaignId, $sessionId);
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
            'sessions' => $this->sessions->listByCampaign($campaignId),
            'latest_session' => $this->sessions->latestByCampaign($campaignId),
        ]);
    }
}
