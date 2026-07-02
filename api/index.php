<?php

declare(strict_types=1);

$configPath = __DIR__ . '/config/config.php';
$exampleConfigPath = __DIR__ . '/config/config.example.php';
$config = file_exists($configPath) ? require $configPath : require $exampleConfigPath;

require_once __DIR__ . '/core/Request.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Mailer.php';
require_once __DIR__ . '/core/HttpClient.php';

require_once __DIR__ . '/modules/Demo/DemoController.php';
require_once __DIR__ . '/modules/Auth/AuthRepository.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/modules/Auth/AuthController.php';
require_once __DIR__ . '/modules/Auth/GoogleAuthController.php';
require_once __DIR__ . '/modules/Campaigns/CampaignRepository.php';
require_once __DIR__ . '/modules/Campaigns/CampaignController.php';
require_once __DIR__ . '/modules/Party/PartyRepository.php';
require_once __DIR__ . '/modules/Party/PartyController.php';
require_once __DIR__ . '/modules/Inventory/InventoryRepository.php';
require_once __DIR__ . '/modules/Inventory/InventoryController.php';
require_once __DIR__ . '/modules/Combat/CombatRepository.php';
require_once __DIR__ . '/modules/Combat/CombatController.php';
require_once __DIR__ . '/modules/Dashboard/DashboardController.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => Security::cookiePath($config),
    'secure' => Security::isSecureCookie($config),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

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

    $authRepository = new AuthRepository($pdo);
    Auth::bootstrapRemember($authRepository);
    $authController = new AuthController($authRepository, $config);
    $googleAuthController = new GoogleAuthController($authRepository, $authController, $config);

    $campaignRepository = new CampaignRepository($pdo);
    $campaignController = new CampaignController($campaignRepository, $config);
    $partyRepository = new PartyRepository($pdo);
    $partyController = new PartyController($partyRepository, $campaignRepository, $config);
    $inventoryRepository = new InventoryRepository($pdo);
    $inventoryController = new InventoryController($inventoryRepository, $campaignRepository, $config);
    $combatRepository = new CombatRepository($pdo);
    $combatController = new CombatController($combatRepository, $campaignRepository, $config);
    $dashboardController = new DashboardController($campaignRepository, $partyRepository, $inventoryRepository, $combatRepository, $config);

    switch ($route) {
        case 'auth/me':
            $authController->me();
            break;

        case 'auth/register':
            $authController->register();
            break;

        case 'auth/login':
            $authController->login();
            break;

        case 'auth/logout':
            $authController->logout();
            break;

        case 'auth/password/forgot':
            $authController->forgotPassword();
            break;

        case 'auth/password/reset':
            $authController->resetPassword();
            break;

        case 'auth/google/start':
            $googleAuthController->start();
            break;

        case 'auth/google/callback':
            $googleAuthController->callback();
            break;

        case 'dashboard/summary':
            $dashboardController->summary();
            break;

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

        case 'inventory/list':
            $inventoryController->list();
            break;

        case 'combat/active':
            $combatController->active();
            break;

        case 'combat/create':
            $combatController->create();
            break;

        case 'combat/activate':
            $combatController->activate();
            break;

        case 'combat/add-party-member':
            $combatController->addPartyMember();
            break;

        case 'combat/add-combatant':
            $combatController->addCombatant();
            break;

        case 'combat/next-turn':
            $combatController->nextTurn();
            break;

        case 'combat/new-round':
            $combatController->newRound();
            break;

        case 'combat/effect/add':
            $combatController->addEffect();
            break;

        case 'combat/effect/remove':
            $combatController->removeEffect();
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
