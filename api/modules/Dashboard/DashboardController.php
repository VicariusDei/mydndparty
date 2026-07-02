<?php

final class DashboardController
{
    public function __construct(
        private CampaignRepository $campaigns,
        private PartyRepository $party,
        private InventoryRepository $inventory,
        private CombatRepository $combat,
        private array $config
    ) {
    }

    public function summary(): void
    {
        $userId = Auth::userId($this->config);
        $campaign = $this->campaigns->activeByUser($userId);

        if (!$campaign) {
            Response::ok([
                'campaign' => null,
                'stats' => [
                    'campaigns' => count($this->campaigns->listByUser($userId)),
                    'party_members' => 0,
                    'inventory_items' => 0,
                    'wallet_rows' => 0,
                    'encounters' => 0,
                    'combatants' => 0,
                    'messages' => 0,
                    'friend_requests' => 0,
                ],
                'party_members' => [],
                'recent_inventory' => [],
                'wallet' => [],
                'active_encounter' => null,
                'combatants' => [],
                'messages' => [],
                'friend_requests' => [],
            ]);
        }

        $campaignId = (int)$campaign['id'];
        $partyMembers = $this->party->listByCampaign($campaignId);
        $wallet = $this->inventory->walletByCampaign($campaignId);
        $activeEncounter = $this->combat->activeEncounterByCampaign($campaignId);
        $combatants = $activeEncounter ? $this->combat->listCombatantsByEncounter((int)$activeEncounter['id']) : [];
        $encounters = $this->combat->listEncountersByCampaign($campaignId);

        Response::ok([
            'campaign' => $campaign,
            'stats' => [
                'campaigns' => count($this->campaigns->listByUser($userId)),
                'party_members' => count($partyMembers),
                'inventory_items' => $this->inventory->countByCampaign($campaignId),
                'wallet_rows' => count($wallet),
                'encounters' => count($encounters),
                'combatants' => $this->combat->countCombatantsByCampaign($campaignId),
                'messages' => 0,
                'friend_requests' => 0,
            ],
            'party_members' => array_slice($partyMembers, 0, 6),
            'recent_inventory' => $this->inventory->recentByCampaign($campaignId, 5),
            'wallet' => $wallet,
            'active_encounter' => $activeEncounter,
            'combatants' => array_slice($combatants, 0, 8),
            'messages' => [],
            'friend_requests' => [],
        ]);
    }
}
