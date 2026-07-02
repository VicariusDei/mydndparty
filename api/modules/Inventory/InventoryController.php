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
        $userId = Auth::userId($this->config);
        $campaignId = (int)($_GET['campaign_id'] ?? 0);
        if ($campaignId <= 0) {
            $active = $this->campaigns->activeByUser($userId);
            $campaignId = $active ? (int)$active['id'] : 0;
        }

        if ($campaignId <= 0) {
            Response::ok([
                'inventory_items' => [],
                'wallet' => [],
            ]);
        }

        Response::ok([
            'inventory_items' => $this->inventory->listByCampaign($campaignId),
            'wallet' => $this->inventory->walletByCampaign($campaignId),
        ]);
    }
}
