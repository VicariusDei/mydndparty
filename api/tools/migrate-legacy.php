<?php

header('Content-Type: application/json; charset=utf-8');

function mdp_json($payload, $statusCode) {
    if (!headers_sent()) {
        http_response_code($statusCode);
    }
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function mdp_starts_with($value, $prefix) {
    return substr((string)$value, 0, strlen($prefix)) === $prefix;
}

$configFile = dirname(__DIR__) . '/config/config.php';
$config = array();
if (file_exists($configFile)) {
    $loadedConfig = require $configFile;
    if (is_array($loadedConfig)) {
        $config = $loadedConfig;
    }
}

$token = '';
if (isset($config['migration_token'])) {
    $token = (string)$config['migration_token'];
} elseif (getenv('MDP_MIGRATION_TOKEN')) {
    $token = (string)getenv('MDP_MIGRATION_TOKEN');
}

$given = '';
if (isset($_GET['token'])) {
    $given = (string)$_GET['token'];
} elseif (isset($_SERVER['HTTP_X_MDP_MIGRATION_TOKEN'])) {
    $given = (string)$_SERVER['HTTP_X_MDP_MIGRATION_TOKEN'];
}

if ($token === '' || !hash_equals($token, $given)) {
    mdp_json(array(
        'ok' => false,
        'error' => 'Token migrazione mancante/non valido. Aggiungere migration_token in api/config/config.php.',
    ), 403);
}

$execute = isset($_GET['execute']) && (string)$_GET['execute'] === '1';
$dry = !$execute;
$db = isset($config['db']) && is_array($config['db']) ? $config['db'] : array();

try {
    $host = isset($db['host']) ? (string)$db['host'] : 'localhost';
    $name = isset($db['name']) ? (string)$db['name'] : '';
    $user = isset($db['user']) ? (string)$db['user'] : '';
    $pass = isset($db['pass']) ? (string)$db['pass'] : '';
    $charset = isset($db['charset']) ? (string)$db['charset'] : 'utf8mb4';

    if ($name === '' || $user === '') {
        throw new RuntimeException('Configurazione database incompleta in api/config/config.php.');
    }

    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $name, $charset),
        $user,
        $pass,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC)
    );

    $schema = mdp_schema($pdo);
    $out = array('ok' => true, 'mode' => $dry ? 'dry-run' : 'execute', 'tables' => array(), 'inserted' => array(), 'mapped' => array(), 'warnings' => array());

    foreach (array('mdp_users','mdp_campaigns','mdp_party_members','mdp_inventory_items','mdp_coin_types','mdp_wallets','mdp_encounters','mdp_combatants','mdp_effects') as $t) {
        if (!isset($schema[$t])) {
            throw new RuntimeException('Manca ' . $t . ': importare prima database/schema.sql');
        }
    }

    if ($execute) {
        $pdo->beginTransaction();
        $pdo->exec("CREATE TABLE IF NOT EXISTS mdp_legacy_migration_map (
            id INT AUTO_INCREMENT PRIMARY KEY,
            legacy_table VARCHAR(80) NOT NULL,
            legacy_id VARCHAR(120) NOT NULL,
            target_table VARCHAR(80) NOT NULL,
            target_id INT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_mdp_legacy_map (legacy_table, legacy_id, target_table)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    mdp_users($pdo, $schema, $dry, $out);
    mdp_campaigns($pdo, $schema, $dry, $out);
    mdp_members($pdo, $schema, $dry, $out);
    mdp_inventory($pdo, $schema, $dry, $out);
    mdp_coins($pdo, $schema, $dry, $out);
    mdp_wallets($pdo, $schema, $dry, $out);
    mdp_encounters($pdo, $schema, $dry, $out);
    mdp_combatants($pdo, $schema, $dry, $out);
    mdp_effects($pdo, $schema, $dry, $out);

    if ($execute) {
        $pdo->commit();
    }

    mdp_json($out, 200);
} catch (Exception $e) {
    if (isset($pdo) && $execute && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    mdp_json(array('ok' => false, 'mode' => isset($dry) && $dry ? 'dry-run' : 'execute', 'error' => $e->getMessage()), 500);
}

function mdp_schema($pdo) {
    $s = array();
    $stmt = $pdo->query('SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE()');
    foreach ($stmt as $r) {
        $table = (string)$r['TABLE_NAME'];
        $column = (string)$r['COLUMN_NAME'];
        if (!isset($s[$table])) {
            $s[$table] = array();
        }
        $s[$table][strtolower($column)] = $column;
    }
    return $s;
}

function mdp_has($s, $t) { return isset($s[$t]); }
function mdp_q($x) { return '`' . str_replace('`', '``', $x) . '`'; }
function mdp_col($s, $t, $candidates) {
    foreach ($candidates as $c) {
        $k = strtolower($c);
        if (isset($s[$t][$k])) {
            return $s[$t][$k];
        }
    }
    return null;
}
function mdp_rows($pdo, $t) { return $pdo->query('SELECT * FROM ' . mdp_q($t))->fetchAll(); }
function mdp_val($r, $cols, $def) {
    foreach ($cols as $c) {
        if ($c !== null && array_key_exists($c, $r) && $r[$c] !== null && $r[$c] !== '') {
            return $r[$c];
        }
    }
    return $def;
}
function mdp_str($v, $def, $len) {
    $x = trim((string)($v === null ? '' : $v));
    if ($x === '') {
        $x = (string)($def === null ? '' : $def);
    }
    if ($x === '') {
        return null;
    }
    return function_exists('mb_substr') ? mb_substr($x, 0, $len, 'UTF-8') : substr($x, 0, $len);
}
function mdp_bool($v, $def) {
    if ($v === null || $v === '') {
        return $def ? 1 : 0;
    }
    if (is_numeric($v)) {
        return (int)$v > 0 ? 1 : 0;
    }
    return in_array(strtolower(trim((string)$v)), array('1','true','yes','si','sì','admin','active','attivo','validato'), true) ? 1 : 0;
}
function mdp_id($r, $col, $i) {
    $v = $col !== null && isset($r[$col]) ? trim((string)$r[$col]) : '';
    return $v !== '' ? $v : 'row_' . $i;
}
function mdp_hash($v) {
    $h = trim((string)($v === null ? '' : $v));
    if (mdp_starts_with($h, '$2y$') || mdp_starts_with($h, '$2a$') || mdp_starts_with($h, '$argon2')) {
        return $h;
    }
    return null;
}
function mdp_add(&$out, $k, $n) {
    if (!isset($out['inserted'][$k])) {
        $out['inserted'][$k] = 0;
    }
    $out['inserted'][$k] += $n;
}
function mdp_warn(&$out, $m) { $out['warnings'][] = $m; }
function mdp_ins($pdo, $sql, $p) { $st = $pdo->prepare($sql); $st->execute($p); return (int)$pdo->lastInsertId(); }
function mdp_map_put($pdo, $src, $sid, $dst, $tid) {
    $st = $pdo->prepare('INSERT INTO mdp_legacy_migration_map (legacy_table,legacy_id,target_table,target_id) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE target_id=VALUES(target_id)');
    $st->execute(array($src, $sid, $dst, $tid));
}
function mdp_map_get($pdo, $src, $sid, $dst) {
    if ((string)$sid === '') {
        return null;
    }
    $st = $pdo->prepare('SELECT target_id FROM mdp_legacy_migration_map WHERE legacy_table=? AND legacy_id=? AND target_table=? LIMIT 1');
    $st->execute(array($src, $sid, $dst));
    $v = $st->fetchColumn();
    return $v === false ? null : (int)$v;
}
function mdp_first_map($pdo, $dst) {
    $st = $pdo->prepare('SELECT target_id FROM mdp_legacy_migration_map WHERE target_table=? ORDER BY id LIMIT 1');
    $st->execute(array($dst));
    $v = $st->fetchColumn();
    return $v === false ? null : (int)$v;
}
function mdp_member_campaign($pdo, $mid) {
    $st = $pdo->prepare('SELECT campaign_id FROM mdp_party_members WHERE id=?');
    $st->execute(array($mid));
    $v = $st->fetchColumn();
    return $v === false ? null : (int)$v;
}
function mdp_legacy_name($pdo, $s, $t, $id) {
    if (!mdp_has($s, $t) || $id === null || $id === '') {
        return null;
    }
    $idc = mdp_col($s, $t, array('id','idClasse','idRazza'));
    $nc = mdp_col($s, $t, array('nome','name','descrizione','classe','razza'));
    if (!$idc || !$nc) {
        return null;
    }
    $st = $pdo->prepare('SELECT ' . mdp_q($nc) . ' FROM ' . mdp_q($t) . ' WHERE ' . mdp_q($idc) . '=? LIMIT 1');
    $st->execute(array($id));
    $v = $st->fetchColumn();
    return $v === false ? null : mdp_str($v, null, 80);
}

function mdp_users($pdo, $s, $dry, &$out) {
    $t = 'utenti';
    if (!mdp_has($s, $t)) { mdp_warn($out, 'Tabella utenti non trovata.'); return; }
    $id = mdp_col($s, $t, array('id','idUtente','id_utente','utente_id'));
    $u = mdp_col($s, $t, array('username','user','login','loginuser','nomeUtente'));
    $e = mdp_col($s, $t, array('email','mail'));
    $p = mdp_col($s, $t, array('password_hash','password','passwd','pwd'));
    $n = mdp_col($s, $t, array('display_name','nomeCompleto','nominativo','nome','username'));
    $a = mdp_col($s, $t, array('is_admin','admin','amministratore','ruolo'));
    $act = mdp_col($s, $t, array('is_active','attivo','active','validato','stato'));
    $rs = mdp_rows($pdo, $t); $out['tables'][$t] = count($rs); $i = 0;
    foreach ($rs as $r) {
        $i++; $lid = mdp_id($r, $id, $i);
        if ($dry) { mdp_add($out, 'users_to_import', 1); continue; }
        $username = strtolower(preg_replace('/[^a-z0-9_.-]+/i', '_', mdp_str(mdp_val($r, array($u), null), 'legacy_user_' . $lid, 80)));
        if ($username === '') { $username = 'legacy_user_' . $lid; }
        $email = strtolower(trim((string)mdp_val($r, array($e), '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $email = 'legacy_' . $lid . '@mydndparty.invalid'; }
        $st = $pdo->prepare('SELECT id FROM mdp_users WHERE username=? OR email=? LIMIT 1');
        $st->execute(array($username, $email));
        $ex = $st->fetchColumn();
        if ($ex !== false) { mdp_map_put($pdo, $t, $lid, 'mdp_users', (int)$ex); $out['mapped']['users_existing'] = isset($out['mapped']['users_existing']) ? $out['mapped']['users_existing'] + 1 : 1; continue; }
        $tid = mdp_ins($pdo, 'INSERT INTO mdp_users (username,email,password_hash,display_name,is_active,is_admin,email_verified_at) VALUES (?,?,?,?,?,?,NOW())', array($username, $email, mdp_hash(mdp_val($r, array($p), null)), mdp_str(mdp_val($r, array($n,$u), null), $username, 160), mdp_bool(mdp_val($r, array($act), 1), true), mdp_bool(mdp_val($r, array($a), 0), false)));
        mdp_map_put($pdo, $t, $lid, 'mdp_users', $tid); mdp_add($out, 'users', 1);
    }
}

function mdp_campaigns($pdo, $s, $dry, &$out) {
    $t='gruppi'; if (!mdp_has($s,$t)) { mdp_warn($out,'Tabella gruppi non trovata.'); return; }
    $id=mdp_col($s,$t,array('id','idGruppo','id_gruppo')); $own=mdp_col($s,$t,array('idUtente','id_utente','utente_id','idUser','owner_user_id')); $n=mdp_col($s,$t,array('name','nome','nomeGruppo','gruppo','titolo')); $note=mdp_col($s,$t,array('notes','note','diario','appunti','descrizione')); $act=mdp_col($s,$t,array('is_active','attivo','active','stato'));
    $rs=mdp_rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0;
    foreach($rs as $r){ $i++; $lid=mdp_id($r,$id,$i); if($dry){mdp_add($out,'campaigns_to_import',1); continue;} $uid=mdp_map_get($pdo,'utenti',(string)mdp_val($r,array($own),''),'mdp_users'); if(!$uid){$uid=mdp_first_map($pdo,'mdp_users');} if(!$uid){mdp_warn($out,'Gruppo '.$lid.' saltato: utente proprietario assente.'); continue;} $tid=mdp_ins($pdo,'INSERT INTO mdp_campaigns (owner_user_id,name,notes,is_active) VALUES (?,?,?,?)',array($uid,mdp_str(mdp_val($r,array($n),null),'Campagna legacy '.$lid,120),mdp_str(mdp_val($r,array($note),null),null,60000),mdp_bool(mdp_val($r,array($act),1),true))); mdp_map_put($pdo,$t,$lid,'mdp_campaigns',$tid); mdp_add($out,'campaigns',1); }
}

function mdp_members($pdo, $s, $dry, &$out) {
    $t='compagnia'; if(!mdp_has($s,$t)){mdp_warn($out,'Tabella compagnia non trovata.'); return;}
    $id=mdp_col($s,$t,array('id','idCompagnia','idPersonaggio')); $g=mdp_col($s,$t,array('idGruppo','id_gruppo','gruppo_id')); $u=mdp_col($s,$t,array('idUtente','id_utente','utente_id')); $pl=mdp_col($s,$t,array('player_name','giocatore','nomeGiocatore','player')); $ch=mdp_col($s,$t,array('character_name','personaggio','nomePersonaggio','nome')); $cl=mdp_col($s,$t,array('class_name','classe','nomeClasse')); $clid=mdp_col($s,$t,array('idClasse','id_classe')); $ra=mdp_col($s,$t,array('ancestry_name','razza','nomeRazza')); $raid=mdp_col($s,$t,array('idRazza','id_razza')); $mo=mdp_col($s,$t,array('motto','citazione','frase')); $ini=mdp_col($s,$t,array('initiative_bonus','bonusIniziativa','bonus_iniziativa','iniziativa'));
    $rs=mdp_rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0;
    foreach($rs as $r){$i++; $lid=mdp_id($r,$id,$i); if($dry){mdp_add($out,'party_members_to_import',1); continue;} $cid=mdp_map_get($pdo,'gruppi',(string)mdp_val($r,array($g),''),'mdp_campaigns'); if(!$cid){mdp_warn($out,'Personaggio '.$lid.' saltato: gruppo assente.'); continue;} $uid=mdp_map_get($pdo,'utenti',(string)mdp_val($r,array($u),''),'mdp_users'); if(!$uid){$st=$pdo->prepare('SELECT owner_user_id FROM mdp_campaigns WHERE id=?'); $st->execute(array($cid)); $uid=(int)$st->fetchColumn();} $char=mdp_str(mdp_val($r,array($ch),null),'Personaggio legacy '.$lid,120); $class=mdp_str(mdp_val($r,array($cl),null),null,80); if(!$class){$class=mdp_legacy_name($pdo,$s,'classi',mdp_val($r,array($clid),null));} $race=mdp_str(mdp_val($r,array($ra),null),null,80); if(!$race){$race=mdp_legacy_name($pdo,$s,'razze',mdp_val($r,array($raid),null));} $tid=mdp_ins($pdo,'INSERT INTO mdp_party_members (campaign_id,user_id,player_name,character_name,class_name,ancestry_name,motto,initiative_bonus) VALUES (?,?,?,?,?,?,?,?)',array($cid,$uid,mdp_str(mdp_val($r,array($pl),null),$char,120),$char,$class,$race,mdp_str(mdp_val($r,array($mo),null),null,255),(int)mdp_val($r,array($ini),0))); mdp_map_put($pdo,$t,$lid,'mdp_party_members',$tid); mdp_add($out,'party_members',1);}
}

function mdp_inventory($pdo, $s, $dry, &$out) {
    $t='inventario'; if(!mdp_has($s,$t)){mdp_warn($out,'Tabella inventario non trovata.'); return;}
    $id=mdp_col($s,$t,array('id','idInventario')); $g=mdp_col($s,$t,array('idGruppo','id_gruppo','gruppo_id')); $m=mdp_col($s,$t,array('idCompagnia','id_compagnia','idPersonaggio')); $n=mdp_col($s,$t,array('name','nome','nomeOggetto','oggetto')); $cat=mdp_col($s,$t,array('category','categoria','tipo')); $qta=mdp_col($s,$t,array('quantity','quantita','qta','qty')); $val=mdp_col($s,$t,array('value_gold','valoreOro','valore')); $ide=mdp_col($s,$t,array('is_identified','identificato')); $note=mdp_col($s,$t,array('notes','note','descrizione'));
    $rs=mdp_rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0;
    foreach($rs as $r){$i++; $lid=mdp_id($r,$id,$i); if($dry){mdp_add($out,'inventory_to_import',1); continue;} $mid=mdp_map_get($pdo,'compagnia',(string)mdp_val($r,array($m),''),'mdp_party_members'); $cid=mdp_map_get($pdo,'gruppi',(string)mdp_val($r,array($g),''),'mdp_campaigns'); if(!$cid && $mid){$cid=mdp_member_campaign($pdo,$mid);} if(!$cid){mdp_warn($out,'Oggetto '.$lid.' saltato: campagna assente.'); continue;} $tid=mdp_ins($pdo,'INSERT INTO mdp_inventory_items (campaign_id,owner_party_member_id,name,category,quantity,value_gold,is_identified,notes) VALUES (?,?,?,?,?,?,?,?)',array($cid,$mid,mdp_str(mdp_val($r,array($n),null),'Oggetto legacy '.$lid,160),mdp_str(mdp_val($r,array($cat),null),null,80),max(1,(int)mdp_val($r,array($qta),1)),(float)str_replace(',','.',(string)mdp_val($r,array($val),0)),mdp_bool(mdp_val($r,array($ide),0),false),mdp_str(mdp_val($r,array($note),null),null,60000))); mdp_map_put($pdo,$t,$lid,'mdp_inventory_items',$tid); mdp_add($out,'inventory_items',1);}
}

function mdp_coin_code($name, $fallback) { $n=strtolower((string)$name); if(strpos($n,'rame')!==false)return'MR'; if(strpos($n,'argento')!==false)return'MA'; if(strpos($n,'platino')!==false)return'MP'; if(strpos($n,'oro')!==false)return'MO'; $x=preg_replace('/[^a-z0-9]+/i','',$fallback); return substr(strtoupper($x ? $x : 'LEG'),0,10); }
function mdp_coins($pdo, $s, $dry, &$out) {
    $t='tipoMonete'; if(!mdp_has($s,$t)){mdp_warn($out,'Tabella tipoMonete non trovata: uso monete standard.'); return;}
    $id=mdp_col($s,$t,array('id','idTipoMonete','idTipoMoneta')); $code=mdp_col($s,$t,array('code','codice','sigla')); $name=mdp_col($s,$t,array('name','nome','tipo','descrizione')); $gold=mdp_col($s,$t,array('gold_value','valoreOro','valore')); $weight=mdp_col($s,$t,array('weight_value','peso','weight'));
    $rs=mdp_rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0;
    foreach($rs as $r){$i++; $lid=mdp_id($r,$id,$i); if($dry){mdp_add($out,'coin_types_to_map',1); continue;} $nm=mdp_str(mdp_val($r,array($name),null),'Moneta legacy '.$lid,80); $cd=mdp_str(mdp_val($r,array($code),null),mdp_coin_code($nm,'LEG'.$lid),10); $st=$pdo->prepare('SELECT id FROM mdp_coin_types WHERE code=?'); $st->execute(array($cd)); $ex=$st->fetchColumn(); if($ex===false){$ex=mdp_ins($pdo,'INSERT INTO mdp_coin_types (code,name,gold_value,weight_value) VALUES (?,?,?,?)',array($cd,$nm,(float)str_replace(',','.',(string)mdp_val($r,array($gold),1)),(float)str_replace(',','.',(string)mdp_val($r,array($weight),0)))); mdp_add($out,'coin_types',1);} mdp_map_put($pdo,$t,$lid,'mdp_coin_types',(int)$ex);}
}
function mdp_default_coin($pdo) { $v=$pdo->query("SELECT id FROM mdp_coin_types WHERE code='MO' LIMIT 1")->fetchColumn(); return $v===false?null:(int)$v; }
function mdp_wallets($pdo, $s, $dry, &$out) {
    $t='monete'; if(!mdp_has($s,$t)){mdp_warn($out,'Tabella monete non trovata.'); return;}
    $id=mdp_col($s,$t,array('id','idMonete')); $g=mdp_col($s,$t,array('idGruppo','id_gruppo','gruppo_id')); $m=mdp_col($s,$t,array('idCompagnia','id_compagnia','idPersonaggio')); $typ=mdp_col($s,$t,array('idTipoMonete','idTipoMoneta','coin_type_id')); $qta=mdp_col($s,$t,array('quantity','quantita','qta','qty')); $dep=mdp_col($s,$t,array('deposit_quantity','deposito','quantitaDeposito'));
    $rs=mdp_rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0;
    foreach($rs as $r){$i++; $lid=mdp_id($r,$id,$i); if($dry){mdp_add($out,'wallet_rows_to_import',1); continue;} $mid=mdp_map_get($pdo,'compagnia',(string)mdp_val($r,array($m),''),'mdp_party_members'); $cid=mdp_map_get($pdo,'gruppi',(string)mdp_val($r,array($g),''),'mdp_campaigns'); if(!$cid && $mid){$cid=mdp_member_campaign($pdo,$mid);} $ct=mdp_map_get($pdo,'tipoMonete',(string)mdp_val($r,array($typ),''),'mdp_coin_types'); if(!$ct){$ct=mdp_default_coin($pdo);} if(!$cid || !$ct){mdp_warn($out,'Monete '.$lid.' saltate: campagna/tipo assente.'); continue;} $tid=mdp_ins($pdo,'INSERT INTO mdp_wallets (campaign_id,party_member_id,coin_type_id,quantity,deposit_quantity) VALUES (?,?,?,?,?)',array($cid,$mid,$ct,(int)mdp_val($r,array($qta),0),(int)mdp_val($r,array($dep),0))); mdp_map_put($pdo,$t,$lid,'mdp_wallets',$tid); mdp_add($out,'wallet_rows',1);}
}

function mdp_encounters($pdo, $s, $dry, &$out) {
    $t='combattimento'; if(!mdp_has($s,$t)){mdp_warn($out,'Tabella combattimento non trovata.'); return;}
    $id=mdp_col($s,$t,array('id','idCombattimento')); $g=mdp_col($s,$t,array('idGruppo','id_gruppo','gruppo_id')); $n=mdp_col($s,$t,array('name','nome','titolo','descrizione')); $round=mdp_col($s,$t,array('current_round','round','roundCorrente')); $act=mdp_col($s,$t,array('is_active','attivo','active','stato'));
    $rs=mdp_rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0;
    foreach($rs as $r){$i++; $lid=mdp_id($r,$id,$i); if($dry){mdp_add($out,'encounters_to_import',1); continue;} $cid=mdp_map_get($pdo,'gruppi',(string)mdp_val($r,array($g),''),'mdp_campaigns'); if(!$cid){mdp_warn($out,'Combattimento '.$lid.' saltato: gruppo assente.'); continue;} $tid=mdp_ins($pdo,'INSERT INTO mdp_encounters (campaign_id,name,current_round,is_active) VALUES (?,?,?,?)',array($cid,mdp_str(mdp_val($r,array($n),null),'Combattimento legacy '.$lid,160),max(0,(int)mdp_val($r,array($round),0)),mdp_bool(mdp_val($r,array($act),1),true))); mdp_map_put($pdo,$t,$lid,'mdp_encounters',$tid); mdp_add($out,'encounters',1);}
}

function mdp_combatants($pdo, $s, $dry, &$out) {
    $t = mdp_has($s,'dadoIniziativa') ? 'dadoIniziativa' : (mdp_has($s,'round') ? 'round' : null); if(!$t){mdp_warn($out,'Tabelle dadoIniziativa/round non trovate.'); return;}
    $id=mdp_col($s,$t,array('id','idDadoIniziativa','idRound')); $enc=mdp_col($s,$t,array('idCombattimento','id_combattimento','combattimento_id')); $m=mdp_col($s,$t,array('idCompagnia','id_compagnia','idPersonaggio')); $n=mdp_col($s,$t,array('name','nome','nomeCombattente','combattente','personaggio')); $ini=mdp_col($s,$t,array('initiative','iniziativa','dado','valore')); $bonus=mdp_col($s,$t,array('initiative_bonus','bonusIniziativa','bonus')); $slow=mdp_col($s,$t,array('is_slow','lento')); $act=mdp_col($s,$t,array('has_acted','agito')); $ord=mdp_col($s,$t,array('sort_order','ordine','posizione'));
    $rs=mdp_rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0;
    foreach($rs as $r){$i++; $lid=mdp_id($r,$id,$i); if($dry){mdp_add($out,'combatants_to_import',1); continue;} $eid=mdp_map_get($pdo,'combattimento',(string)mdp_val($r,array($enc),''),'mdp_encounters'); if(!$eid){mdp_warn($out,'Combattente '.$lid.' saltato: combattimento assente.'); continue;} $mid=mdp_map_get($pdo,'compagnia',(string)mdp_val($r,array($m),''),'mdp_party_members'); $name=mdp_str(mdp_val($r,array($n),null),null,160); if(!$name && $mid){$st=$pdo->prepare('SELECT character_name FROM mdp_party_members WHERE id=?'); $st->execute(array($mid)); $name=mdp_str($st->fetchColumn(),null,160);} $tid=mdp_ins($pdo,'INSERT INTO mdp_combatants (encounter_id,party_member_id,name,type,initiative,initiative_bonus,is_slow,has_acted,sort_order) VALUES (?,?,?,?,?,?,?,?,?)',array($eid,$mid,$name ? $name : 'Combattente legacy '.$lid,$mid ? 'player' : 'enemy',(int)mdp_val($r,array($ini),0),(int)mdp_val($r,array($bonus),0),mdp_bool(mdp_val($r,array($slow),0),false),mdp_bool(mdp_val($r,array($act),0),false),(int)mdp_val($r,array($ord),$i))); mdp_map_put($pdo,$t,$lid,'mdp_combatants',$tid); mdp_add($out,'combatants',1);}
}

function mdp_effects($pdo, $s, $dry, &$out) {
    $t='effetti'; if(!mdp_has($s,$t)){mdp_warn($out,'Tabella effetti non trovata.'); return;}
    $id=mdp_col($s,$t,array('id','idEffetto')); $c=mdp_col($s,$t,array('idDadoIniziativa','idCombattente','combatant_id')); $m=mdp_col($s,$t,array('idCompagnia','idPersonaggio')); $n=mdp_col($s,$t,array('name','nome','effetto','descrizione')); $round=mdp_col($s,$t,array('remaining_rounds','roundRimanenti','durata','round')); $perm=mdp_col($s,$t,array('is_permanent','permanente'));
    $rs=mdp_rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0;
    foreach($rs as $r){$i++; $lid=mdp_id($r,$id,$i); if($dry){mdp_add($out,'effects_to_import',1); continue;} $cid=mdp_map_get($pdo,'dadoIniziativa',(string)mdp_val($r,array($c),''),'mdp_combatants'); if(!$cid){$cid=mdp_map_get($pdo,'round',(string)mdp_val($r,array($c),''),'mdp_combatants');} if(!$cid){$mid=mdp_map_get($pdo,'compagnia',(string)mdp_val($r,array($m),''),'mdp_party_members'); if($mid){$st=$pdo->prepare('SELECT id FROM mdp_combatants WHERE party_member_id=? ORDER BY id DESC LIMIT 1'); $st->execute(array($mid)); $v=$st->fetchColumn(); $cid=$v===false?null:(int)$v;}} if(!$cid){mdp_warn($out,'Effetto '.$lid.' saltato: combattente assente.'); continue;} $tid=mdp_ins($pdo,'INSERT INTO mdp_effects (combatant_id,name,remaining_rounds,is_permanent) VALUES (?,?,?,?)',array($cid,mdp_str(mdp_val($r,array($n),null),'Effetto legacy '.$lid,120),max(0,(int)mdp_val($r,array($round),0)),mdp_bool(mdp_val($r,array($perm),0),false))); mdp_map_put($pdo,$t,$lid,'mdp_effects',$tid); mdp_add($out,'effects',1);}
}
