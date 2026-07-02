<?php

final class DemoController
{
    public function dashboard(): void
    {
        Response::ok([
            'campaign' => [
                'name' => 'Le Ombre di Vhalor',
                'summary' => 'Sessione pronta. Il party e\' al limite del Bosco Cavo.',
            ],
            'stats' => [
                'party_members' => 5,
                'round' => 3,
                'gold_total' => 248,
                'active_effects' => 3,
            ],
            'current_turn' => [
                'name' => 'Mirael',
                'initiative' => 21,
                'effects' => ['Benedetto', 'Velocizzato'],
            ],
        ]);
    }
}
