<?php

final class GroupsController
{
    public function __construct(
        private GroupsRepository $groups,
        private array $config
    ) {
    }

    public function list(): void
    {
        $userId = Auth::userId($this->config);
        Response::ok([
            'groups' => $this->groups->listByUser($userId),
        ]);
    }

    public function create(): void
    {
        $userId = Auth::userId($this->config);
        $body = Request::jsonBody();
        $name = trim((string)($body['name'] ?? ''));
        if ($name === '') {
            Response::error('Il nome gruppo e obbligatorio', 422);
        }

        $this->groups->create($userId, $name, (string)($body['description'] ?? ''));
        $this->respondWithGroups($userId);
    }

    public function members(): void
    {
        $userId = Auth::userId($this->config);
        $groupId = (int)($_GET['group_id'] ?? 0);
        if ($groupId <= 0) {
            Response::error('Gruppo obbligatorio', 422);
        }
        if (!$this->groups->isMember($groupId, $userId)) {
            Response::error('Gruppo non disponibile', 403);
        }

        Response::ok([
            'members' => $this->groups->members($groupId),
        ]);
    }

    public function addMember(): void
    {
        $userId = Auth::userId($this->config);
        $body = Request::jsonBody();
        $groupId = (int)($body['group_id'] ?? 0);
        $username = trim((string)($body['username'] ?? ''));
        $role = trim((string)($body['role'] ?? 'member'));

        if ($groupId <= 0 || $username === '') {
            Response::error('Gruppo e username sono obbligatori', 422);
        }

        $this->groups->addMemberByUsername($groupId, $userId, $username, $role);
        Response::ok([
            'groups' => $this->groups->listByUser($userId),
            'members' => $this->groups->members($groupId),
        ]);
    }

    public function findUser(): void
    {
        Auth::userId($this->config);
        $username = trim((string)($_GET['username'] ?? ''));
        if ($username === '') {
            Response::error('Username obbligatorio', 422);
        }

        $user = $this->groups->findUserByUsername($username);
        Response::ok([
            'user' => $user,
        ]);
    }

    private function respondWithGroups(int $userId): void
    {
        Response::ok([
            'groups' => $this->groups->listByUser($userId),
        ]);
    }
}
