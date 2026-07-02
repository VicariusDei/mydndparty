<?php
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
ini_set('html_errors','0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

$configFile = dirname(__DIR__) . '/config/config.php';
$config = file_exists($configFile) ? require $configFile : array();
$token = isset($config['migration_token']) ? (string)$config['migration_token'] : '';
$given = isset($_GET['token']) ? (string)$_GET['token'] : '';
if ($token === '' || !hash_equals($token, $given)) {
    http_response_code(403);
    echo json_encode(array('ok'=>false,'error'=>'token non valido'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$db = isset($config['db']) ? $config['db'] : array();
$pdo = new PDO(
    'mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=' . (isset($db['charset']) ? $db['charset'] : 'utf8mb4'),
    $db['user'],
    $db['pass'],
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC)
);

$tables = array('inventario','monete','combattimento','dadoIniziativa','effetti','mdp_legacy_migration_map');
$out = array('ok'=>true,'tables'=>array());
foreach ($tables as $table) {
    $cols = array();
    $stmt = $pdo->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION');
    $stmt->execute(array($table));
    foreach ($stmt as $row) {
        $cols[] = $row['COLUMN_NAME'];
    }
    $out['tables'][$table] = array('columns'=>$cols);
    if ($cols) {
        $sample = $pdo->query('SELECT * FROM `' . str_replace('`','``',$table) . '` LIMIT 3')->fetchAll();
        $out['tables'][$table]['sample'] = $sample;
    }
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
