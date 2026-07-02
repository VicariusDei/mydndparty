<?php

final class CampaignController
{
    public function __construct(
        private CampaignRepository $campaigns,
        private array $config
    ) {
    }

    public function list(): void
    {
        $userId = Auth::userId($this->config);
        Response::ok([
            'campaigns' => $this->campaigns->listByUser($userId),
        ]);
    }

    public function active(): void
    {
        $userId = Auth::userId($this->config);
        $campaign = $this->campaigns->activeByUser($userId);

        if (!$campaign) {
            Response::ok([
                'campaign' => null,
            ]);
        }

        Response::ok([
            'campaign' => $campaign,
        ]);
    }

    public function create(): void
    {
        $userId = Auth::userId($this->config);
        $body = Request::jsonBody();
        $name = trim((string)($body['name'] ?? ''));
        $notes = isset($body['notes']) ? trim((string)$body['notes']) : null;

        if ($name === '') {
            Response::error('Il nome campagna e\' obbligatorio', 422);
        }

        $id = $this->campaigns->create($userId, $name, $notes ?: null);

        Response::ok([
            'id' => $id,
        ]);
    }
}
