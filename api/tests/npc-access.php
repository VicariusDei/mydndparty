<?php
// Test isolato: PDO SQLite con traduzione delle sole funzioni temporali MySQL usate.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start();
class TestResponse extends RuntimeException { public function __construct(public array $payload, public int $status=200) {parent::__construct();} }
class Response {
    public static function ok(array $data=[]): void { throw new TestResponse($data); }
    public static function error(string $error,int $status=400): void {throw new TestResponse(['error'=>$error],$status);}
}
class Auth {public static function userId(array $config): int {return $GLOBALS['user'];}}
class Request {public static function jsonBody(): array {return $GLOBALS['body'];}}
class TestPDO extends PDO {
    public function prepare(string $query,array $options=[]): PDOStatement|false {
        $query=str_replace(['DATE_ADD(NOW(),INTERVAL 12 HOUR)','DATE_ADD(NOW(),INTERVAL 90 SECOND)','NOW()',' FOR UPDATE'],["datetime('now','+12 hours')","datetime('now','+90 seconds')","datetime('now')",''],$query);
        return parent::prepare($query,$options);
    }
}
require __DIR__.'/../modules/Npc/NpcController.php';
$pdo=new TestPDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE mdp_campaigns(id INTEGER PRIMARY KEY,owner_user_id INTEGER,name TEXT);
CREATE TABLE mdp_campaign_participants(id INTEGER PRIMARY KEY,campaign_id INTEGER,user_id INTEGER,role TEXT,status TEXT);
CREATE TABLE mdp_world_entities(id INTEGER PRIMARY KEY,campaign_id INTEGER,type TEXT,name TEXT);
CREATE TABLE mdp_npc_profiles(id INTEGER PRIMARY KEY AUTOINCREMENT,campaign_id INTEGER,world_entity_id INTEGER,name TEXT,personality TEXT,knowledge TEXT,memory TEXT,voice TEXT);
CREATE TABLE mdp_npc_rooms(id INTEGER PRIMARY KEY AUTOINCREMENT,npc_id INTEGER,token_hash TEXT,expires_at TEXT,scene TEXT,approval INTEGER DEFAULT 1,paused INTEGER DEFAULT 0,epoch INTEGER DEFAULT 0,busy_until TEXT);
CREATE TABLE mdp_npc_messages(id INTEGER PRIMARY KEY AUTOINCREMENT,room_id INTEGER,role TEXT,content TEXT,status TEXT,epoch INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
INSERT INTO mdp_campaigns VALUES(1,10,'Cyberpunk'),(2,20,'Fantasy');
INSERT INTO mdp_campaign_participants VALUES(1,1,11,'co_master','active'),(2,1,12,'player','active');
INSERT INTO mdp_world_entities VALUES(1,1,'npc','ECHO'),(2,2,'npc','Mago');");
$controller=new NpcController($pdo,[]);
function callNpc(string $action,array $body=[],int $user=10,string $token=''): TestResponse {
    $GLOBALS['user']=$user;$GLOBALS['body']=$body;$_GET=$body;
    $_SERVER['REQUEST_METHOD']=in_array($action,['campaigns','list','state'])?'GET':'POST';
    $_SERVER['HTTP_X_NPC_TOKEN']=$token;$_SERVER['HTTP_HOST']='localhost';unset($_SERVER['HTTP_ORIGIN']);
    try {$GLOBALS['controller']->handle($action);}catch(TestResponse $r){return $r;}
    throw new RuntimeException('Risposta mancante');
}
function check(bool $condition,string $message):void {if(!$condition)throw new RuntimeException('FAIL: '.$message);echo 'PASS: '.$message."\n";}
check(callNpc('list',['campaign_id'=>1],12)->status===403,'Giocatore escluso dalla regia');
check(callNpc('list',['campaign_id'=>1],20)->status===403,'Campagne isolate');
check(callNpc('list',['campaign_id'=>1],11)->status===200,'Co-master autorizzato');
check(count(callNpc('campaigns',[],11)->payload['campaigns'])===1,'Campagne del co-master selezionabili');
$profile=['campaign_id'=>1,'name'=>'ECHO','personality'=>'segreta','knowledge'=>'solo PNG','memory'=>'confermata','voice'=>'alloy','world_entity_id'=>2];
check(callNpc('save',$profile)->status===422,'Entità narrativa di altra campagna rifiutata');
$profile['world_entity_id']=1;
$npc=callNpc('save',$profile)->payload['id'];
$opened=callNpc('open',['npc_id'=>$npc])->payload;
$room=['room_id'=>$opened['room_id']];$token=$opened['token'];
$stored=$pdo->query('SELECT token_hash FROM mdp_npc_rooms')->fetchColumn();
check($stored!==$token && $stored===hash('sha256',$token),'Codice terminale conservato solo come hash');
check(callNpc('state',[],10,'errato')->status===403,'Codice non valido rifiutato');
check(callNpc('manual',$room+['text'=>'Sono qui.'])->status===200,'Battuta manuale disponibile senza chiave IA');
$pdo->exec("INSERT INTO mdp_npc_messages(room_id,role,content,status,epoch) VALUES(1,'assistant','SEGRETO','pending',0)");
$state=callNpc('state',[],0,$token)->payload;
check(count($state['messages'])===1 && $state['scene']===null && !isset($state['room']['knowledge']) && !isset($state['room']['personality']),'Terminale riceve solo battute pubblicate, nessuna configurazione');
check(callNpc('approve',$room+['message_id'=>2,'content'=>'Approvato'])->status===200,'Approvazione master');
check(count(callNpc('state',[],0,$token)->payload['messages'])===2,'Battuta pubblicata dopo approvazione');
check(callNpc('control',$room+['paused'=>true,'scene'=>'Scena','approval'=>true])->status===200,'Pausa master');
check(callNpc('ask',['text'=>'Ciao'],0,$token)->status===409,'Domande bloccate in pausa');
check(callNpc('state',[],0,$token)->payload['room']['epoch']===1,'Interruzione invalida le operazioni precedenti');
callNpc('control',$room+['paused'=>false,'scene'=>'Scena','approval'=>true]);
check(callNpc('ask',['text'=>'Ciao'],0,$token)->status===503,'Chiave mancante segnalata senza bloccare sessione');
$pdo->exec("INSERT INTO mdp_npc_messages(room_id,role,content,status,epoch) VALUES(1,'assistant','Vecchia bozza','pending',2)");
callNpc('save',$profile+['id'=>$npc]);
check($pdo->query("SELECT status FROM mdp_npc_messages WHERE content='Vecchia bozza'")->fetchColumn()==='cancelled','Modifica PNG annulla le bozze precedenti');
check(callNpc('close',$room)->status===200,'Chiusura sessione');
check(callNpc('state',[],0,$token)->status===403,'Codice revocato inutilizzabile');
$_SERVER['REQUEST_METHOD']='GET';
try{$controller->handle('save');}catch(TestResponse $r){check($r->status===405,'Mutazioni via GET rifiutate');}
echo "Verifiche completate.\n";
