<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/core/Request.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';

require_once __DIR__ . '/modules/Demo/DemoController.php';
require_once __DIR__ . '/modules/Campaigns/CampaignRepository.php';
require_once __DIR__ . '/modules/Campaigns/CampaignController.php';
require_once __DIR__ . '/modules/Party/PartyRepository.php';
require_once __DIR__ . '/modules/Party/PartyController.php';

$configPath = __DIR__ . '/config/config.php';
$exampleConfigPath = __DIR__ . '/config/config.example.php';
$config = file_exists($configPath) ? require $configPath : require $exampleConfigPath;

$route = Request::route();

try {
    if ($route === 'health') {
        Response::ok([
            'app' => 'MyDndParty API',
            'status' => 'ready',
        ]);
    }

    if ($route === 'demo/dashboard') {
        (new DemoController())->dashboard();
    }

    $db = new Database($config);
    $pdo = $db->pdo();

    $campaignRepository = new CampaignRepository($pdo);
    $campaignController = new CampaignController($campaignRepository, $config);
    $partyRepository = new PartyRepository($pdo);
    $partyController = new PartyController($partyRepository, $campaignRepository, $config);

    switch ($route) {
        case 'campaigns/list':
            $campaignController->list();
            break;

        case 'campaigns/active':
            $campaignController->active();
            break;

        case 'campaigns/create':
            $campaignController->create();
            break;

        case 'party/list':
            $partyController->list();
            break;

        case 'party/create':
            $partyController->create();
            break;

        default:
            Response::error('Route non trovata: ' . $route, 404);
    }
} catch (Throwable $exception) {
    if (!empty($config['app_debug'])) {
        Response::error($exception->getMessage(), 500);
    }

    Response::error('Errore interno', 500);
}
