<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/core/Request.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/modules/Demo/DemoController.php';

$configPath = __DIR__ . '/config/config.php';
$exampleConfigPath = __DIR__ . '/config/config.example.php';
$config = file_exists($configPath) ? require $configPath : require $exampleConfigPath;

$route = Request::route();

try {
    switch ($route) {
        case 'health':
            Response::ok([
                'app' => 'MyDndParty API',
                'status' => 'ready',
            ]);
            break;

        case 'demo/dashboard':
            (new DemoController())->dashboard();
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
