<?php

final class GroupCampaignsController
{
    public function __construct(
        private GroupCampaignsRepository $groupCampaigns,
        private GroupsRepository $groups,
        private array $config
    ) {
    }

    public function list(): void
    {
        $userId = Auth::userId($this->config);
        $groupId = (int)($_GET['group_id'] ?? 0);
        if ($groupId <= 0) {
            Response::error('Gruppo obbligatorio', 422);
        }

        Response::ok([
            'campaigns' => $this->groupCampaigns->listByGroup($groupId, $userId),
        ]);
    }

    public function create(): void
    {
        $userId = Auth::userId($this->config);
        $body = Request::jsonBody();
        $groupId = (int)($body['group_id'] ?? 0);
        $name = trim((string)($body['name'] ?? ''));

        if ($groupId <= 0 || $name === '') {
            Response::error('Gruppo e nome campagna sono obbligatori', 422);
        }

        $this->groupCampaigns->createInGroup($groupId, $userId, $name, (string)($body['notes'] ?? ''));
        $this->respondGroupState($groupId, $userId);
    }

    public function participants(): void
    {
        $userId = Auth::userId($this->config);
        $campaignId = (int)($_GET['campaign_id'] ?? 0);
        if ($campaignId <= 0) {
            Response::error('Campagna obbligatoria', 422);
        }

        Response::ok([
            'participants' => $this->groupCampaigns->participants($campaignId, $userId),
        ]);
    }

    public function addParticipant(): void
    {
        $userId = Auth::userId($this->config);
        $body = Request::jsonBody();
        $campaignId = (int)($body['campaign_id'] ?? 0);
        $targetUserId = (int)($body['user_id'] ?? 0);
        $role = trim((string)($body['role'] ?? 'player'));

        if ($campaignId <= 0 || $targetUserId <= 0) {
            Response::error('Campagna e utente sono obbligatori', 422);
        }

        $this->groupCampaigns->addParticipant($campaignId, $userId, $targetUserId, $role);
        $groupId = $this->groupCampaigns->groupIdForCampaign($campaignId) ?: 0;

        Response::ok([
            'campaigns' => $groupId > 0 ? $this->groupCampaigns->listByGroup($groupId, $userId) : [],
            'participants' => $this->groupCampaigns->participants($campaignId, $userId),
        ]);
    }

    private function respondGroupState(int $groupId, int $userId): void
    {
        Response::ok([
            'campaigns' => $this->groupCampaigns->listByGroup($groupId, $userId),
            'members' => $this->groups->members($groupId),
        ]);
    }
}
