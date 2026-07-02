<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

$__sent = false;
register_shutdown_function(function () use (&$__sent) {
    if ($__sent) return;
    $e = error_get_last();
    if ($e) {
        http_response_code(500);
        echo json_encode(array('ok'=>false,'stage'=>'shutdown','fatal'=>$e), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
});

function done($p, $s = 200) {
    global $__sent;
    $__sent = true;
    http_response_code($s);
    echo json_encode($p, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
function q($x){return '`'.str_replace('`','``',$x).'`';}
function one($pdo,$sql,$p=array()){$st=$pdo->prepare($sql);$st->execute($p);$v=$st->fetchColumn();return $v===false?null:$v;}
function rows($pdo,$sql,$p=array()){$st=$pdo->prepare($sql);$st->execute($p);return $st->fetchAll(PDO::FETCH_ASSOC);}
function ins($pdo,$sql,$p){$st=$pdo->prepare($sql);$st->execute($p);return (int)$pdo->lastInsertId();}
function inc(&$a,$k){if(!isset($a[$k]))$a[$k]=0;$a[$k]++;}
function b($v){$v=strtolower(trim((string)$v));return in_array($v,array('1','s','si','y','yes','true','t'),true)?1:0;}
function cut($v,$n,$d=null){$v=trim((string)($v===null?'':$v));if($v==='')$v=(string)($d===null?'':$d);if($v==='')return null;return function_exists('mb_substr')?mb_substr($v,0,$n,'UTF-8'):substr($v,0,$n);} 
function mapget($pdo,$lt,$lid,$tt){return one($pdo,'SELECT target_id FROM mdp_legacy_migration_map WHERE legacy_table=? AND legacy_id=? AND target_table=? LIMIT 1',array($lt,(string)$lid,$tt));}
function mapput($pdo,$lt,$lid,$tt,$tid){$st=$pdo->prepare('INSERT INTO mdp_legacy_migration_map (legacy_table,legacy_id,target_table,target_id) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE target_id=VALUES(target_id)');$st->execute(array($lt,(string)$lid,$tt,(int)$tid));}
function hasTable($pdo,$t){return (int)one($pdo,'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',array($t))>0;}
function countTable($pdo,$t){return hasTable($pdo,$t)?(int)one($pdo,'SELECT COUNT(*) FROM '.q($t)):null;}

try {
    $cfile = dirname(__DIR__) . '/config/config.php';
    if (!file_exists($cfile)) done(array('ok'=>false,'stage'=>'config','error'=>'config.php assente'),500);
    $c = require $cfile;
    if (!is_array($c)) done(array('ok'=>false,'stage'=>'config','error'=>'config.php non restituisce array'),500);
    $tok = isset($c['migration_token']) ? (string)$c['migration_token'] : '';
    $given = isset($_GET['token']) ? (string)$_GET['token'] : '';
    if ($tok === '' || !hash_equals($tok, $given)) done(array('ok'=>false,'stage'=>'token','error'=>'token non valido'),403);
    if (!isset($c['db']) || !is_array($c['db'])) done(array('ok'=>false,'stage'=>'db-config','error'=>'config db assente'),500);

    $execute = isset($_GET['execute']) && (string)$_GET['execute'] === '1';
    $db = $c['db'];
    $charset = isset($db['charset']) ? $db['charset'] : 'utf8mb4';
    $pdo = new PDO('mysql:host='.$db['host'].';dbname='.$db['name'].';charset='.$charset,$db['user'],$db['pass'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC));
    if(!hasTable($pdo,'mdp_legacy_migration_map')) done(array('ok'=>false,'stage'=>'map','error'=>'mdp_legacy_migration_map assente'),500);

    $out=array('ok'=>true,'mode'=>$execute?'execute':'dry-run','before'=>array(),'inserted'=>array(),'mapped'=>array(),'warnings'=>array());
    foreach(array('mdp_inventory_items','mdp_wallets','mdp_encounters','mdp_combatants','mdp_effects','mdp_legacy_migration_map') as $t)$out['before'][$t]=countTable($pdo,$t);
    if($execute)$pdo->beginTransaction();

    foreach(rows($pdo,'SELECT * FROM inventario ORDER BY id') as $r){
      if(mapget($pdo,'inventario',$r['id'],'mdp_inventory_items')){inc($out['mapped'],'inventory_existing');continue;}
      $uid=mapget($pdo,'utenti',$r['idUtente'],'mdp_users');
      $cid=$uid?one($pdo,'SELECT id FROM mdp_campaigns WHERE owner_user_id=? ORDER BY id LIMIT 1',array($uid)):null;
      if(!$cid){$out['warnings'][]='Inventario '.$r['id'].' saltato: nessuna campagna per utente '.$r['idUtente'];continue;}
      if(!$execute){inc($out['inserted'],'inventory_items_to_insert');continue;}
      $tid=ins($pdo,'INSERT INTO mdp_inventory_items (campaign_id,owner_party_member_id,name,category,quantity,value_gold,is_identified,notes) VALUES (?,?,?,?,?,?,?,?)',array($cid,null,cut($r['des'],160,'Oggetto legacy '.$r['id']),cut($r['categoria'],80,null),max(1,(int)$r['qta']),(float)str_replace(',','.',(string)$r['val']),b($r['ide']),cut($r['note'],65535,null)));
      mapput($pdo,'inventario',$r['id'],'mdp_inventory_items',$tid);inc($out['inserted'],'inventory_items');
    }

    foreach(rows($pdo,'SELECT * FROM monete ORDER BY id') as $r){
      if(mapget($pdo,'monete',$r['id'],'mdp_wallets')){inc($out['mapped'],'wallets_existing');continue;}
      $cid=mapget($pdo,'gruppi',$r['idGruppo'],'mdp_campaigns');$coin=mapget($pdo,'tipoMonete',$r['idMoneta'],'mdp_coin_types');
      if(!$cid||!$coin){$out['warnings'][]='Monete '.$r['id'].' saltate: gruppo/moneta non mappati';continue;}
      if(!$execute){inc($out['inserted'],'wallet_rows_to_insert');continue;}
      $tid=ins($pdo,'INSERT INTO mdp_wallets (campaign_id,party_member_id,coin_type_id,quantity,deposit_quantity) VALUES (?,?,?,?,?)',array($cid,null,$coin,(int)$r['quantita'],(int)$r['quantitaDeposito']));
      mapput($pdo,'monete',$r['id'],'mdp_wallets',$tid);inc($out['inserted'],'wallet_rows');
    }

    foreach(rows($pdo,'SELECT * FROM combattimento ORDER BY idCombattimento') as $r){
      if(mapget($pdo,'combattimento',$r['idCombattimento'],'mdp_combatants')){inc($out['mapped'],'combatants_existing');continue;}
      $cid=mapget($pdo,'gruppi',$r['idGruppo'],'mdp_campaigns');
      if(!$cid){$out['warnings'][]='Combattimento '.$r['idCombattimento'].' saltato: gruppo '.$r['idGruppo'].' non mappato';continue;}
      $enc=mapget($pdo,'legacy_fight_group',$r['idGruppo'],'mdp_encounters');
      if(!$enc&&$execute){$enc=ins($pdo,'INSERT INTO mdp_encounters (campaign_id,name,current_round,is_active) VALUES (?,?,?,?)',array($cid,'Combattimento legacy gruppo '.$r['idGruppo'],0,1));mapput($pdo,'legacy_fight_group',$r['idGruppo'],'mdp_encounters',$enc);inc($out['inserted'],'encounters_repair');}
      if(!$enc&&!$execute)inc($out['inserted'],'encounters_repair_to_insert');
      $pm=mapget($pdo,'compagnia',$r['idPersonaggio'],'mdp_party_members');
      if(!$execute){inc($out['inserted'],'combatants_to_insert');continue;}
      $tid=ins($pdo,'INSERT INTO mdp_combatants (encounter_id,party_member_id,name,type,initiative,initiative_bonus,is_slow,has_acted,sort_order) VALUES (?,?,?,?,?,?,?,?,?)',array($enc,$pm,cut($r['personaggio'],160,'Combattente legacy '.$r['idCombattimento']),$pm?'player':'enemy',(int)$r['iniziativa'],(int)$r['bonusIniziativa'],b($r['lento']),0,(int)$r['idCombattimento']));
      mapput($pdo,'combattimento',$r['idCombattimento'],'mdp_combatants',$tid);inc($out['inserted'],'combatants');
    }

    foreach(rows($pdo,'SELECT * FROM effetti ORDER BY id') as $r){
      if(mapget($pdo,'effetti',$r['id'],'mdp_effects')){inc($out['mapped'],'effects_existing');continue;}
      $combatant=mapget($pdo,'combattimento',$r['idCombattimento'],'mdp_combatants');
      if(!$combatant){$out['warnings'][]='Effetto '.$r['id'].' saltato: combattente '.$r['idCombattimento'].' non mappato';continue;}
      if(!$execute){inc($out['inserted'],'effects_to_insert');continue;}
      $tid=ins($pdo,'INSERT INTO mdp_effects (combatant_id,name,remaining_rounds,is_permanent) VALUES (?,?,?,?)',array($combatant,cut($r['effetto'],120,'Effetto legacy '.$r['id']),(int)$r['round'],b($r['permanente'])));
      mapput($pdo,'effetti',$r['id'],'mdp_effects',$tid);inc($out['inserted'],'effects');
    }

    if($execute)$pdo->commit();
    foreach(array('mdp_inventory_items','mdp_wallets','mdp_encounters','mdp_combatants','mdp_effects','mdp_legacy_migration_map') as $t)$out['after'][$t]=countTable($pdo,$t);
    done($out,200);
} catch (Exception $e) {
    if (isset($pdo) && isset($execute) && $execute && $pdo->inTransaction()) $pdo->rollBack();
    done(array('ok'=>false,'stage'=>'catch','error'=>$e->getMessage()),500);
}
