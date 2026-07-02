<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

echo json_encode(array(
    'ok' => true,
    'php_version' => PHP_VERSION,
    'sapi' => PHP_SAPI,
    'config_exists' => file_exists(dirname(__DIR__) . '/config/config.php'),
    'migration_script_exists' => file_exists(__DIR__ . '/migrate-legacy.php'),
    'display_errors' => ini_get('display_errors'),
    'display_startup_errors' => ini_get('display_startup_errors'),
    'error_reporting' => error_reporting()
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
