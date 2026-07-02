<?php

final class InventoryController
{
    public function __construct(
        private InventoryRepository $inventory,
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
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $name = trim((string)($body['name'] ?? ''));
        if ($name === '') {
            Response::error('Nome oggetto obbligatorio', 422);
        }

        $body['name'] = $name;
        $this->inventory->createItem($campaignId, $body);
        $this->respondWithState($campaignId);
    }

    public function update(): void
    {
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $itemId = (int)($body['id'] ?? 0);
        $name = trim((string)($body['name'] ?? ''));

        if ($itemId <= 0 || $name === '') {
            Response::error('Oggetto e nome sono obbligatori', 422);
        }

        $body['name'] = $name;
        $this->inventory->updateItem($campaignId, $itemId, $body);
        $this->respondWithState($campaignId);
    }

    public function delete(): void
    {
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $itemId = (int)($body['id'] ?? 0);
        if ($itemId <= 0) {
            Response::error('Oggetto obbligatorio', 422);
        }

        $this->inventory->deleteItem($campaignId, $itemId);
        $this->respondWithState($campaignId);
    }

    public function walletAdjust(): void
    {
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $walletId = (int)($body['wallet_id'] ?? 0);
        if ($walletId <= 0) {
            Response::error('Riga monete obbligatoria', 422);
        }

        $this->inventory->adjustWallet(
            $campaignId,
            $walletId,
            (int)($body['quantity_delta'] ?? 0),
            (int)($body['deposit_delta'] ?? 0)
        );
        $this->respondWithState($campaignId);
    }

    public function walletUpdate(): void
    {
        $campaignId = $this->requireCampaign();
        $body = Request::jsonBody();
        $walletId = (int)($body['wallet_id'] ?? 0);
        if ($walletId <= 0) {
            Response::error('Riga monete obbligatoria', 422);
        }

        $this->inventory->updateWallet(
            $campaignId,
            $walletId,
            (int)($body['quantity'] ?? 0),
            (int)($body['deposit_quantity'] ?? 0)
        );
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
            'inventory_items' => $this->inventory->listByCampaign($campaignId),
            'wallet' => $this->inventory->walletByCampaign($campaignId),
        ]);
    }

    private function emptyPayload(): void
    {
        Response::ok([
            'inventory_items' => [],
            'wallet' => [],
        ]);
    }
}
