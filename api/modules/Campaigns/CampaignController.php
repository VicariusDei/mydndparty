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
            'active_campaign' => $this->campaigns->activeByUser($userId),
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
        $name = $this->clip(trim((string)($body['name'] ?? '')), 120);
        $notes = $this->notes($body['notes'] ?? null);

        if ($name === '') {
            Response::error('Il nome campagna e obbligatorio', 422);
        }

        $id = $this->campaigns->create($userId, $name, $notes);

        Response::ok([
            'id' => $id,
            'campaigns' => $this->campaigns->listByUser($userId),
            'active_campaign' => $this->campaigns->activeByUser($userId),
        ]);
    }

    public function update(): void
    {
        $userId = Auth::userId($this->config);
        $body = Request::jsonBody();
        $campaignId = (int)($body['id'] ?? 0);
        $name = $this->clip(trim((string)($body['name'] ?? '')), 120);
        $notes = $this->notes($body['notes'] ?? null);

        if ($campaignId <= 0 || $name === '') {
            Response::error('Campagna e nome sono obbligatori', 422);
        }

        $this->campaigns->update($userId, $campaignId, $name, $notes);
        Response::ok([
            'campaigns' => $this->campaigns->listByUser($userId),
            'active_campaign' => $this->campaigns->activeByUser($userId),
        ]);
    }

    public function activate(): void
    {
        $userId = Auth::userId($this->config);
        $body = Request::jsonBody();
        $campaignId = (int)($body['id'] ?? 0);
        if ($campaignId <= 0) {
            Response::error('Campagna obbligatoria', 422);
        }

        $this->campaigns->activate($userId, $campaignId);
        Response::ok([
            'campaigns' => $this->campaigns->listByUser($userId),
            'active_campaign' => $this->campaigns->activeByUser($userId),
        ]);
    }

    public function delete(): void
    {
        $userId = Auth::userId($this->config);
        $body = Request::jsonBody();
        $campaignId = (int)($body['id'] ?? 0);
        if ($campaignId <= 0) {
            Response::error('Campagna obbligatoria', 422);
        }

        $this->campaigns->deleteIfEmpty($userId, $campaignId);
        Response::ok([
            'campaigns' => $this->campaigns->listByUser($userId),
            'active_campaign' => $this->campaigns->activeByUser($userId),
        ]);
    }

    private function notes($value): ?string
    {
        $notes = trim((string)($value ?? ''));
        return $notes === '' ? null : $notes;
    }

    private function clip(string $value, int $length): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $length, 'UTF-8');
        }

        return substr($value, 0, $length);
    }
}
