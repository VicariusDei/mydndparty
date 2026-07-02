<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
$configFile = dirname(__DIR__) . '/config/config.php';
$config = file_exists($configFile) ? require $configFile : [];
$token = (string)($config['migration_token'] ?? getenv('MDP_MIGRATION_TOKEN') ?: '');
$given = (string)($_GET['token'] ?? ($_SERVER['HTTP_X_MDP_MIGRATION_TOKEN'] ?? ''));
if ($token === '' || !hash_equals($token, $given)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token migrazione mancante/non valido. Aggiungere migration_token in api/config/config.php.'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$execute = (string)($_GET['execute'] ?? '') === '1';
$dry = !$execute;
$db = $config['db'] ?? [];
$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'] ?? 'localhost', $db['name'] ?? '', $db['charset'] ?? 'utf8mb4'),
    (string)($db['user'] ?? ''),
    (string)($db['pass'] ?? ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$schema = schema($pdo);
$out = ['ok' => true, 'mode' => $dry ? 'dry-run' : 'execute', 'tables' => [], 'inserted' => [], 'mapped' => [], 'warnings' => []];

try {
    foreach (['mdp_users','mdp_campaigns','mdp_party_members','mdp_inventory_items','mdp_coin_types','mdp_wallets','mdp_encounters','mdp_combatants','mdp_effects'] as $t) {
        if (!isset($schema[$t])) throw new RuntimeException("Manca {$t}: importare prima database/schema.sql");
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

    users($pdo, $schema, $dry, $out);
    campaigns($pdo, $schema, $dry, $out);
    members($pdo, $schema, $dry, $out);
    inventory($pdo, $schema, $dry, $out);
    coins($pdo, $schema, $dry, $out);
    wallets($pdo, $schema, $dry, $out);
    encounters($pdo, $schema, $dry, $out);
    combatants($pdo, $schema, $dry, $out);
    effects($pdo, $schema, $dry, $out);

    if ($execute) $pdo->commit();
} catch (Throwable $e) {
    if ($execute && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    $out['ok'] = false;
    $out['error'] = $e->getMessage();
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

function schema(PDO $pdo): array {
    $s = [];
    foreach ($pdo->query('SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE()') as $r) {
        $s[(string)$r['TABLE_NAME']][strtolower((string)$r['COLUMN_NAME'])] = (string)$r['COLUMN_NAME'];
    }
    return $s;
}
function has(array $s, string $t): bool { return isset($s[$t]); }
function col(array $s, string $t, array $c): ?string { foreach ($c as $x) { $k = strtolower($x); if (isset($s[$t][$k])) return $s[$t][$k]; } return null; }
function q(string $x): string { return '`' . str_replace('`', '``', $x) . '`'; }
function rows(PDO $pdo, string $t): array { return $pdo->query('SELECT * FROM ' . q($t))->fetchAll(); }
function val(array $r, array $cols, mixed $def = null): mixed { foreach ($cols as $c) if ($c && isset($r[$c]) && $r[$c] !== '') return $r[$c]; return $def; }
function strv(mixed $v, ?string $def, int $len): ?string { $x = trim((string)($v ?? '')); if ($x === '') $x = (string)($def ?? ''); return $x === '' ? null : substr($x, 0, $len); }
function boolv(mixed $v, bool $def = false): int { if ($v === null || $v === '') return $def ? 1 : 0; if (is_numeric($v)) return (int)$v > 0 ? 1 : 0; return in_array(strtolower(trim((string)$v)), ['1','true','yes','si','sì','admin','active','attivo','validato'], true) ? 1 : 0; }
function idv(array $r, ?string $c, int $i): string { $v = $c ? trim((string)($r[$c] ?? '')) : ''; return $v !== '' ? $v : 'row_' . $i; }
function modernHash(mixed $v): ?string { $h = trim((string)($v ?? '')); return str_starts_with($h, '$2y$') || str_starts_with($h, '$2a$') || str_starts_with($h, '$argon2') ? $h : null; }
function add(array &$out, string $k, int $n = 1): void { $out['inserted'][$k] = ($out['inserted'][$k] ?? 0) + $n; }
function warn(array &$out, string $m): void { $out['warnings'][] = $m; }
function mapPut(PDO $pdo, string $src, string $sid, string $dst, int $tid): void { $st = $pdo->prepare('INSERT INTO mdp_legacy_migration_map (legacy_table,legacy_id,target_table,target_id) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE target_id=VALUES(target_id)'); $st->execute([$src,$sid,$dst,$tid]); }
function mapGet(PDO $pdo, string $src, string $sid, string $dst): ?int { $st = $pdo->prepare('SELECT target_id FROM mdp_legacy_migration_map WHERE legacy_table=? AND legacy_id=? AND target_table=? LIMIT 1'); $st->execute([$src,$sid,$dst]); $v = $st->fetchColumn(); return $v === false ? null : (int)$v; }
function firstMap(PDO $pdo, string $dst): ?int { $st = $pdo->prepare('SELECT target_id FROM mdp_legacy_migration_map WHERE target_table=? ORDER BY id LIMIT 1'); $st->execute([$dst]); $v = $st->fetchColumn(); return $v === false ? null : (int)$v; }
function ins(PDO $pdo, string $sql, array $p): int { $st = $pdo->prepare($sql); $st->execute($p); return (int)$pdo->lastInsertId(); }
function memberCampaign(PDO $pdo, int $mid): ?int { $st = $pdo->prepare('SELECT campaign_id FROM mdp_party_members WHERE id=?'); $st->execute([$mid]); $v = $st->fetchColumn(); return $v === false ? null : (int)$v; }
function legacyName(PDO $pdo, array $s, string $t, mixed $id): ?string { if (!has($s,$t) || $id === null || $id === '') return null; $idc = col($s,$t,['id','idClasse','idRazza']); $nc = col($s,$t,['nome','name','descrizione','classe','razza']); if (!$idc || !$nc) return null; $st = $pdo->prepare('SELECT '.q($nc).' FROM '.q($t).' WHERE '.q($idc).'=? LIMIT 1'); $st->execute([$id]); $v = $st->fetchColumn(); return $v === false ? null : strv($v,null,80); }

function users(PDO $pdo, array $s, bool $dry, array &$out): void {
    $t='utenti'; if (!has($s,$t)) { warn($out,'Tabella utenti non trovata.'); return; }
    $id=col($s,$t,['id','idUtente','id_utente','utente_id']); $u=col($s,$t,['username','user','login','loginuser','nomeUtente']); $e=col($s,$t,['email','mail']); $p=col($s,$t,['password_hash','password','passwd','pwd']); $n=col($s,$t,['display_name','nomeCompleto','nominativo','nome','username']); $a=col($s,$t,['is_admin','admin','amministratore','ruolo']); $act=col($s,$t,['is_active','attivo','active','validato','stato']);
    $rs=rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0;
    foreach($rs as $r){ $i++; $lid=idv($r,$id,$i); if($dry){add($out,'users_to_import'); continue;} $username=strtolower(preg_replace('/[^a-z0-9_.-]+/i','_',strv(val($r,[$u]),'legacy_user_'.$lid,80)) ?: 'legacy_user_'.$lid); $email=strtolower(trim((string)val($r,[$e]))); if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $email='legacy_'.$lid.'@mydndparty.invalid'; $st=$pdo->prepare('SELECT id FROM mdp_users WHERE username=? OR email=? LIMIT 1'); $st->execute([$username,$email]); $ex=$st->fetchColumn(); if($ex!==false){mapPut($pdo,$t,$lid,'mdp_users',(int)$ex); $out['mapped']['users_existing']=($out['mapped']['users_existing']??0)+1; continue;} $tid=ins($pdo,'INSERT INTO mdp_users (username,email,password_hash,display_name,is_active,is_admin,email_verified_at) VALUES (?,?,?,?,?,?,NOW())',[$username,$email,modernHash(val($r,[$p])),strv(val($r,[$n,$u]),$username,160),boolv(val($r,[$act],1),true),boolv(val($r,[$a],0))]); mapPut($pdo,$t,$lid,'mdp_users',$tid); add($out,'users'); }
}
function campaigns(PDO $pdo, array $s, bool $dry, array &$out): void {
    $t='gruppi'; if(!has($s,$t)){warn($out,'Tabella gruppi non trovata.'); return;} $id=col($s,$t,['id','idGruppo','id_gruppo']); $own=col($s,$t,['idUtente','id_utente','utente_id','idUser','owner_user_id']); $n=col($s,$t,['name','nome','nomeGruppo','gruppo','titolo']); $note=col($s,$t,['notes','note','diario','appunti','descrizione']); $act=col($s,$t,['is_active','attivo','active','stato']);
    $rs=rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0; foreach($rs as $r){$i++; $lid=idv($r,$id,$i); if($dry){add($out,'campaigns_to_import'); continue;} $uid=mapGet($pdo,'utenti',(string)val($r,[$own],''),'mdp_users') ?? firstMap($pdo,'mdp_users'); if(!$uid){warn($out,"Gruppo {$lid} saltato: utente proprietario assente."); continue;} $tid=ins($pdo,'INSERT INTO mdp_campaigns (owner_user_id,name,notes,is_active) VALUES (?,?,?,?)',[$uid,strv(val($r,[$n]),'Campagna legacy '.$lid,120),strv(val($r,[$note]),null,60000),boolv(val($r,[$act],1),true)]); mapPut($pdo,$t,$lid,'mdp_campaigns',$tid); add($out,'campaigns');}}
function members(PDO $pdo, array $s, bool $dry, array &$out): void {
    $t='compagnia'; if(!has($s,$t)){warn($out,'Tabella compagnia non trovata.'); return;} $id=col($s,$t,['id','idCompagnia','idPersonaggio']); $g=col($s,$t,['idGruppo','id_gruppo','gruppo_id']); $u=col($s,$t,['idUtente','id_utente','utente_id']); $pl=col($s,$t,['player_name','giocatore','nomeGiocatore','player']); $ch=col($s,$t,['character_name','personaggio','nomePersonaggio','nome']); $cl=col($s,$t,['class_name','classe','nomeClasse']); $clid=col($s,$t,['idClasse','id_classe']); $ra=col($s,$t,['ancestry_name','razza','nomeRazza']); $raid=col($s,$t,['idRazza','id_razza']); $mo=col($s,$t,['motto','citazione','frase']); $ini=col($s,$t,['initiative_bonus','bonusIniziativa','bonus_iniziativa','iniziativa']);
    $rs=rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0; foreach($rs as $r){$i++; $lid=idv($r,$id,$i); if($dry){add($out,'party_members_to_import'); continue;} $cid=mapGet($pdo,'gruppi',(string)val($r,[$g],''),'mdp_campaigns'); if(!$cid){warn($out,"Personaggio {$lid} saltato: gruppo assente."); continue;} $uid=mapGet($pdo,'utenti',(string)val($r,[$u],''),'mdp_users'); if(!$uid){$st=$pdo->prepare('SELECT owner_user_id FROM mdp_campaigns WHERE id=?'); $st->execute([$cid]); $uid=(int)$st->fetchColumn();} $char=strv(val($r,[$ch]),'Personaggio legacy '.$lid,120); $class=strv(val($r,[$cl]),null,80) ?? legacyName($pdo,$s,'classi',val($r,[$clid])); $race=strv(val($r,[$ra]),null,80) ?? legacyName($pdo,$s,'razze',val($r,[$raid])); $tid=ins($pdo,'INSERT INTO mdp_party_members (campaign_id,user_id,player_name,character_name,class_name,ancestry_name,motto,initiative_bonus) VALUES (?,?,?,?,?,?,?,?)',[$cid,$uid,strv(val($r,[$pl]),$char,120),$char,$class,$race,strv(val($r,[$mo]),null,255),(int)val($r,[$ini],0)]); mapPut($pdo,$t,$lid,'mdp_party_members',$tid); add($out,'party_members');}}
function inventory(PDO $pdo, array $s, bool $dry, array &$out): void {
    $t='inventario'; if(!has($s,$t)){warn($out,'Tabella inventario non trovata.'); return;} $id=col($s,$t,['id','idInventario']); $g=col($s,$t,['idGruppo','id_gruppo','gruppo_id']); $m=col($s,$t,['idCompagnia','id_compagnia','idPersonaggio']); $n=col($s,$t,['name','nome','nomeOggetto','oggetto']); $cat=col($s,$t,['category','categoria','tipo']); $qta=col($s,$t,['quantity','quantita','qta','qty']); $val=col($s,$t,['value_gold','valoreOro','valore']); $ide=col($s,$t,['is_identified','identificato']); $note=col($s,$t,['notes','note','descrizione']);
    $rs=rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0; foreach($rs as $r){$i++; $lid=idv($r,$id,$i); if($dry){add($out,'inventory_to_import'); continue;} $mid=mapGet($pdo,'compagnia',(string)val($r,[$m],''),'mdp_party_members'); $cid=mapGet($pdo,'gruppi',(string)val($r,[$g],''),'mdp_campaigns') ?? ($mid ? memberCampaign($pdo,$mid) : null); if(!$cid){warn($out,"Oggetto {$lid} saltato: campagna assente."); continue;} $tid=ins($pdo,'INSERT INTO mdp_inventory_items (campaign_id,owner_party_member_id,name,category,quantity,value_gold,is_identified,notes) VALUES (?,?,?,?,?,?,?,?)',[$cid,$mid,strv(val($r,[$n]),'Oggetto legacy '.$lid,160),strv(val($r,[$cat]),null,80),max(1,(int)val($r,[$qta],1)),(float)str_replace(',','.',(string)val($r,[$val],0)),boolv(val($r,[$ide],0)),strv(val($r,[$note]),null,60000)]); mapPut($pdo,$t,$lid,'mdp_inventory_items',$tid); add($out,'inventory_items');}}
function coinCode(?string $name, string $fallback): string { $n=strtolower((string)$name); if(str_contains($n,'rame'))return'MR'; if(str_contains($n,'argento'))return'MA'; if(str_contains($n,'platino'))return'MP'; if(str_contains($n,'oro'))return'MO'; return substr(strtoupper(preg_replace('/[^a-z0-9]+/i','',$fallback)?:'LEG'),0,10); }
function coins(PDO $pdo, array $s, bool $dry, array &$out): void {
    $t='tipoMonete'; if(!has($s,$t)){warn($out,'Tabella tipoMonete non trovata: uso monete standard.'); return;} $id=col($s,$t,['id','idTipoMonete','idTipoMoneta']); $code=col($s,$t,['code','codice','sigla']); $name=col($s,$t,['name','nome','tipo','descrizione']); $gold=col($s,$t,['gold_value','valoreOro','valore']); $weight=col($s,$t,['weight_value','peso','weight']);
    $rs=rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0; foreach($rs as $r){$i++; $lid=idv($r,$id,$i); if($dry){add($out,'coin_types_to_map'); continue;} $nm=strv(val($r,[$name]),'Moneta legacy '.$lid,80); $cd=strv(val($r,[$code]),coinCode($nm,'LEG'.$lid),10); $st=$pdo->prepare('SELECT id FROM mdp_coin_types WHERE code=?'); $st->execute([$cd]); $ex=$st->fetchColumn(); if($ex===false){$ex=ins($pdo,'INSERT INTO mdp_coin_types (code,name,gold_value,weight_value) VALUES (?,?,?,?)',[$cd,$nm,(float)str_replace(',','.',(string)val($r,[$gold],1)),(float)str_replace(',','.',(string)val($r,[$weight],0))]); add($out,'coin_types');} mapPut($pdo,$t,$lid,'mdp_coin_types',(int)$ex);}}
function defaultCoin(PDO $pdo): ?int { $v=$pdo->query("SELECT id FROM mdp_coin_types WHERE code='MO' LIMIT 1")->fetchColumn(); return $v===false?null:(int)$v; }
function wallets(PDO $pdo, array $s, bool $dry, array &$out): void {
    $t='monete'; if(!has($s,$t)){warn($out,'Tabella monete non trovata.'); return;} $id=col($s,$t,['id','idMonete']); $g=col($s,$t,['idGruppo','id_gruppo','gruppo_id']); $m=col($s,$t,['idCompagnia','id_compagnia','idPersonaggio']); $typ=col($s,$t,['idTipoMonete','idTipoMoneta','coin_type_id']); $qta=col($s,$t,['quantity','quantita','qta','qty']); $dep=col($s,$t,['deposit_quantity','deposito','quantitaDeposito']);
    $rs=rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0; foreach($rs as $r){$i++; $lid=idv($r,$id,$i); if($dry){add($out,'wallet_rows_to_import'); continue;} $mid=mapGet($pdo,'compagnia',(string)val($r,[$m],''),'mdp_party_members'); $cid=mapGet($pdo,'gruppi',(string)val($r,[$g],''),'mdp_campaigns') ?? ($mid ? memberCampaign($pdo,$mid) : null); $ct=mapGet($pdo,'tipoMonete',(string)val($r,[$typ],''),'mdp_coin_types') ?? defaultCoin($pdo); if(!$cid||!$ct){warn($out,"Monete {$lid} saltate: campagna/tipo assente."); continue;} $tid=ins($pdo,'INSERT INTO mdp_wallets (campaign_id,party_member_id,coin_type_id,quantity,deposit_quantity) VALUES (?,?,?,?,?)',[$cid,$mid,$ct,(int)val($r,[$qta],0),(int)val($r,[$dep],0)]); mapPut($pdo,$t,$lid,'mdp_wallets',$tid); add($out,'wallet_rows');}}
function encounters(PDO $pdo, array $s, bool $dry, array &$out): void {
    $t='combattimento'; if(!has($s,$t)){warn($out,'Tabella combattimento non trovata.'); return;} $id=col($s,$t,['id','idCombattimento']); $g=col($s,$t,['idGruppo','id_gruppo','gruppo_id']); $n=col($s,$t,['name','nome','titolo','descrizione']); $round=col($s,$t,['current_round','round','roundCorrente']); $act=col($s,$t,['is_active','attivo','active','stato']);
    $rs=rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0; foreach($rs as $r){$i++; $lid=idv($r,$id,$i); if($dry){add($out,'encounters_to_import'); continue;} $cid=mapGet($pdo,'gruppi',(string)val($r,[$g],''),'mdp_campaigns'); if(!$cid){warn($out,"Combattimento {$lid} saltato: gruppo assente."); continue;} $tid=ins($pdo,'INSERT INTO mdp_encounters (campaign_id,name,current_round,is_active) VALUES (?,?,?,?)',[$cid,strv(val($r,[$n]),'Combattimento legacy '.$lid,160),max(0,(int)val($r,[$round],0)),boolv(val($r,[$act],1),true)]); mapPut($pdo,$t,$lid,'mdp_encounters',$tid); add($out,'encounters');}}
function combatants(PDO $pdo, array $s, bool $dry, array &$out): void {
    $t=has($s,'dadoIniziativa')?'dadoIniziativa':(has($s,'round')?'round':null); if(!$t){warn($out,'Tabelle dadoIniziativa/round non trovate.'); return;} $id=col($s,$t,['id','idDadoIniziativa','idRound']); $enc=col($s,$t,['idCombattimento','id_combattimento','combattimento_id']); $m=col($s,$t,['idCompagnia','id_compagnia','idPersonaggio']); $n=col($s,$t,['name','nome','nomeCombattente','combattente','personaggio']); $ini=col($s,$t,['initiative','iniziativa','dado','valore']); $bonus=col($s,$t,['initiative_bonus','bonusIniziativa','bonus']); $slow=col($s,$t,['is_slow','lento']); $act=col($s,$t,['has_acted','agito']); $ord=col($s,$t,['sort_order','ordine','posizione']);
    $rs=rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0; foreach($rs as $r){$i++; $lid=idv($r,$id,$i); if($dry){add($out,'combatants_to_import'); continue;} $eid=mapGet($pdo,'combattimento',(string)val($r,[$enc],''),'mdp_encounters'); if(!$eid){warn($out,"Combattente {$lid} saltato: combattimento assente."); continue;} $mid=mapGet($pdo,'compagnia',(string)val($r,[$m],''),'mdp_party_members'); $name=strv(val($r,[$n]),null,160); if(!$name && $mid){$st=$pdo->prepare('SELECT character_name FROM mdp_party_members WHERE id=?');$st->execute([$mid]);$name=strv($st->fetchColumn(),null,160);} $tid=ins($pdo,'INSERT INTO mdp_combatants (encounter_id,party_member_id,name,type,initiative,initiative_bonus,is_slow,has_acted,sort_order) VALUES (?,?,?,?,?,?,?,?,?)',[$eid,$mid,$name?:'Combattente legacy '.$lid,$mid?'player':'enemy',(int)val($r,[$ini],0),(int)val($r,[$bonus],0),boolv(val($r,[$slow],0)),boolv(val($r,[$act],0)),(int)val($r,[$ord],$i)]); mapPut($pdo,$t,$lid,'mdp_combatants',$tid); add($out,'combatants');}}
function effects(PDO $pdo, array $s, bool $dry, array &$out): void {
    $t='effetti'; if(!has($s,$t)){warn($out,'Tabella effetti non trovata.'); return;} $id=col($s,$t,['id','idEffetto']); $c=col($s,$t,['idDadoIniziativa','idCombattente','combatant_id']); $m=col($s,$t,['idCompagnia','idPersonaggio']); $n=col($s,$t,['name','nome','effetto','descrizione']); $round=col($s,$t,['remaining_rounds','roundRimanenti','durata','round']); $perm=col($s,$t,['is_permanent','permanente']);
    $rs=rows($pdo,$t); $out['tables'][$t]=count($rs); $i=0; foreach($rs as $r){$i++; $lid=idv($r,$id,$i); if($dry){add($out,'effects_to_import'); continue;} $cid=mapGet($pdo,'dadoIniziativa',(string)val($r,[$c],''),'mdp_combatants') ?? mapGet($pdo,'round',(string)val($r,[$c],''),'mdp_combatants'); if(!$cid){$mid=mapGet($pdo,'compagnia',(string)val($r,[$m],''),'mdp_party_members'); if($mid){$st=$pdo->prepare('SELECT id FROM mdp_combatants WHERE party_member_id=? ORDER BY id DESC LIMIT 1');$st->execute([$mid]);$v=$st->fetchColumn();$cid=$v===false?null:(int)$v;}} if(!$cid){warn($out,"Effetto {$lid} saltato: combattente assente."); continue;} $tid=ins($pdo,'INSERT INTO mdp_effects (combatant_id,name,remaining_rounds,is_permanent) VALUES (?,?,?,?)',[$cid,strv(val($r,[$n]),'Effetto legacy '.$lid,120),max(0,(int)val($r,[$round],0)),boolv(val($r,[$perm],0))]); mapPut($pdo,$t,$lid,'mdp_effects',$tid); add($out,'effects');}}
