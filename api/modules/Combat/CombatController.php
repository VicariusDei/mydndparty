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
        $userId = Auth::userId($this->config);
        $campaignId = (int)($_GET['campaign_id'] ?? 0);
        if ($campaignId <= 0) {
            $active = $this->campaigns->activeByUser($userId);
            $campaignId = $active ? (int)$active['id'] : 0;
        }

        if ($campaignId <= 0) {
            Response::ok([
                'encounter' => null,
                'combatants' => [],
                'encounters' => [],
            ]);
        }

        $encounter = $this->combat->activeEncounterByCampaign($campaignId);
        Response::ok([
            'encounter' => $encounter,
            'combatants' => $encounter ? $this->combat->listCombatantsByEncounter((int)$encounter['id']) : [],
            'encounters' => $this->combat->listEncountersByCampaign($campaignId),
        ]);
    }
}
