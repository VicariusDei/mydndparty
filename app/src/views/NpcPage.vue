<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>{{ terminal ? (room?.name || 'Terminale PNG') : 'Regia PNG' }}</ion-title></ion-toolbar></ion-header>
    <ion-content class="app-page">
      <section class="section-block npc-workspace">
        <p class="auth-error" v-if="error" role="alert">{{ error }}</p>
        <template v-if="!terminal">
          <article class="fantasy-card form-card">
            <h2>Personaggi della campagna</h2>
            <label>Campagna <select v-model="campaignId" @change="loadProfiles"><option :value="0">Seleziona</option><option v-for="c in campaigns" :value="c.id" :key="c.id">{{ c.name }}</option></select></label>
            <p v-if="configured === false">Chiave IA da configurare sul server. Puoi già preparare i PNG e usare battute manuali.</p>
            <label>Riprendi una sessione <select v-model="resumeId" @change="resumeRoom"><option :value="0">Seleziona sessione</option><option v-for="r in rooms" :key="r.id" :value="r.id">#{{ r.id }} · {{ r.name }} · {{ r.expires_at }}</option></select></label>
            <label>PNG <select v-model="selected" @change="selectProfile"><option :value="0">Nuovo PNG</option><option v-for="p in profiles" :value="p.id" :key="p.id">{{ p.name }}</option></select></label>
            <label>Collegamento a un PNG già in archivio <select v-model="profile.world_entity_id"><option :value="0">Nessun collegamento</option><option v-for="e in entities" :key="e.id" :value="e.id">{{ e.name }}</option></select></label>
            <div class="form-grid">
              <label>Nome <input v-model="profile.name" maxlength="120" /></label>
              <label>Voce <select v-model="profile.voice"><option v-for="v in voices" :key="v">{{ v }}</option></select></label>
              <label class="is-full">Personalità e modo di parlare <textarea v-model="profile.personality" rows="3" /></label>
              <label class="is-full">Conoscenze autorizzate <textarea v-model="profile.knowledge" rows="4" /></label>
              <label class="is-full">Memoria confermata dal master <textarea v-model="profile.memory" rows="4" /></label>
            </div>
            <ion-button :disabled="busy || !campaignId || !profile.name.trim()" @click="saveProfile">Salva PNG</ion-button>
            <ion-button :disabled="busy || !selected" @click="openRoom">Apri sessione con questo PNG</ion-button>
          </article>
          <article v-if="roomId" class="fantasy-card form-card">
            <h2>Scena e regia</h2>
            <p v-if="terminalLink"><a :href="terminalLink" target="_blank" rel="noopener">Apri terminale giocatori</a></p>
            <label v-if="token">Codice temporaneo (12 ore)<input :value="token" readonly @click="($event.target as HTMLInputElement).select()" /></label>
            <p>Su un altro tablet apri {{ terminalBase }} e inserisci il codice. Condividi solo il terminale con i giocatori.</p>
            <label>Situazione percepibile dal PNG <textarea v-model="scene" rows="3" /></label>
            <label><input type="checkbox" v-model="approval" /> Approva ogni risposta prima di mostrarla</label>
            <ion-button :disabled="busy" @click="control(false)">Aggiorna scena / Riprendi</ion-button>
            <ion-button color="danger" @click="control(true)">Pausa e interrompi</ion-button><ion-button fill="outline" @click="closeRoom">Chiudi e revoca terminale</ion-button>
            <label>Evento per un intervento <textarea v-model="eventText" rows="2" /></label>
            <ion-button :disabled="busy || room?.paused" @click="generate('suggest', eventText)">Suggerisci battuta</ion-button>
            <label>Battuta manuale <textarea v-model="manual" rows="2" /></label>
            <ion-button :disabled="busy || !manual.trim() || room?.paused" @click="sendManual">Pronuncia battuta</ion-button>
            <ion-button :disabled="busy || room?.paused" @click="summarize">Proponi memoria</ion-button>
            <div v-if="proposal"><label>Proposta da verificare <textarea v-model="proposal" rows="5" /></label><ion-button @click="profile.memory = proposal; proposal = ''">Copia nella memoria da salvare</ion-button><p>Verifica il testo, poi premi Salva PNG.</p></div>
          </article>
        </template>
        <article v-if="terminal && !connected" class="fantasy-card form-card">
          <h1>Collega il terminale</h1><label>Codice del master<input v-model="token" autocomplete="off" /></label><ion-button @click="connect">Collega</ion-button>
        </article>
        <article v-if="roomId || (terminal && connected)" class="fantasy-card form-card">
          <h2>{{ room?.name || 'Connessione…' }}</h2>
          <p role="status">{{ room?.paused ? 'In pausa' : recording ? 'In ascolto' : busy || room?.busy ? 'Elaborazione…' : playing ? 'Sta parlando' : 'Pronto' }}</p>
          <template v-if="terminal">
            <p>Voce generata con intelligenza artificiale.</p>
            <ion-button @click="toggleAudio">{{ audioEnabled ? 'Voce attiva' : 'Attiva voce' }}</ion-button>
            <ion-button color="medium" @click="stopAudio">Interrompi voce</ion-button>
            <ion-button v-if="lastSpeech" @click="speak(lastSpeech)">Riascolta ultima battuta</ion-button>
            <div class="npc-log" aria-live="polite"><p v-for="m in messages" :key="m.id"><strong>{{ m.role === 'assistant' ? room?.name : 'Giocatori' }}</strong><br />{{ m.content }}</p></div>
            <label>Domanda <textarea v-model="question" rows="3" /></label>
            <ion-button :disabled="busy || room?.paused || recording" @click="generate('ask',question)">Invia</ion-button>
            <ion-button :disabled="busy || room?.paused || playing" @click="recording ? endRecording() : startRecording()">{{ recording ? 'Termina registrazione' : 'Parla' }}</ion-button>
            <p>La registrazione viene trascritta nella domanda: verifica il testo prima di inviarlo.</p>
          </template>
          <template v-else>
            <div class="npc-log"><div v-for="m in messages" :key="m.id"><p><strong>{{ m.role === 'assistant' ? room?.name : 'Giocatori' }} · {{ m.status }}</strong></p><textarea v-if="m.status === 'pending'" v-model="m.content" rows="3" /><p v-else>{{ m.content }}</p><ion-button v-if="m.status === 'pending'" :disabled="room?.paused" @click="approveMessage(m)">Approva e pronuncia</ion-button></div></div>
          </template>
        </article>
      </section>
    </ion-content>
  </ion-page>
</template>
<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import { onIonViewWillLeave, onIonViewDidEnter, IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButton } from '@ionic/vue';
import { apiUrl, apiGet, type ApiResponse } from '../services/api';
interface Profile { world_entity_id:number|null; id:number; name:string; personality:string; knowledge:string; memory:string; voice:string }
interface Message { id:number; role:string; content:string; status:string }
interface Room { id:number; name:string; paused:boolean; epoch:number; busy:boolean; approval:boolean }
const terminal = useRoute().path === '/npc-terminal';
const campaigns=ref<{id:number;name:string}[]>([]), campaignId=ref(0), profiles=ref<Profile[]>([]), selected=ref(0);
const empty=():Profile=>({world_entity_id:0,id:0,name:'',personality:'',knowledge:'',memory:'',voice:'alloy'});
const rooms=ref<{id:number;npc_id:number;name:string;expires_at:string}[]>([]), resumeId=ref(0);
const entities=ref<{id:number;name:string}[]>([]);
const profile=ref<Profile>(empty()), configured=ref<boolean>(), roomId=ref(0), room=ref<Room>(), messages=ref<Message[]>([]);
const token=ref(''), connected=ref(false), terminalLink=ref(''), scene=ref(''), approval=ref(true), error=ref(''), busy=ref(false);
const question=ref(''), eventText=ref(''), manual=ref(''), proposal=ref(''), recording=ref(false), playing=ref(false), audioEnabled=ref(false), lastSpeech=ref(0);
const voices=['alloy','ash','coral','echo','fable','nova','onyx','sage','shimmer'];
const terminalBase=location.origin+import.meta.env.BASE_URL+'npc-terminal';
let timer:ReturnType<typeof setInterval>|undefined, recorder:MediaRecorder|undefined, stream:MediaStream|undefined, audio:HTMLAudioElement|undefined, objectUrl='', captureTimer:ReturnType<typeof setTimeout>|undefined;
let lastScene='';
let seen=new Set<number>(), polling=false, generation=0, alive=true;
async function request<T>(action:string, body:Record<string,unknown>={}, get=false):Promise<T> {
  const headers:Record<string,string>={};
  if(terminal) headers['X-NPC-Token']=token.value;
  if(!get) headers['Content-Type']='application/json';
  const response=await fetch(apiUrl('npc/'+action,get?{room_id:roomId.value}:undefined),{method:get?'GET':'POST',credentials:'include',headers,...(!get?{body:JSON.stringify({room_id:roomId.value,...body})}:{})});
  const data=await response.json() as ApiResponse<T>;
  if(!response.ok || !data.ok) throw new Error(data.error || 'Operazione non riuscita');
  return data.data as T;
}
async function safely(fn:()=>Promise<void>) { error.value=''; try{await fn();}catch(e){error.value=e instanceof Error?e.message:'Errore di connessione';} }
async function loadProfiles(){ await safely(async()=>{roomId.value=0; room.value=undefined; messages.value=[]; selected.value=0; profile.value=empty(); const r=await apiGet<{rooms:{id:number;npc_id:number;name:string;expires_at:string}[];profiles:Profile[];entities:{id:number;name:string}[];configured:boolean}>('npc/list',{campaign_id:campaignId.value});if(!r.ok)throw new Error(r.error);rooms.value=r.data!.rooms;profiles.value=r.data!.profiles;entities.value=r.data!.entities;configured.value=r.data!.configured;}); }
function selectProfile(){profile.value={...(profiles.value.find(p=>Number(p.id)===Number(selected.value))||empty())};roomId.value=0;room.value=undefined;messages.value=[];}
async function saveProfile(){await safely(async()=>{const r=await request<{id:number}>('save',{...profile.value,campaign_id:campaignId.value});selected.value=r.id;profile.value.id=r.id;const list=await apiGet<{profiles:Profile[]}>('npc/list',{campaign_id:campaignId.value});if(list.ok)profiles.value=list.data!.profiles;});}
async function openRoom(){await safely(async()=>{const r=await request<{room_id:number;token:string}>('open',{npc_id:selected.value});roomId.value=r.room_id;token.value=r.token;terminalLink.value=terminalBase+'#'+r.token;scene.value='';seen=new Set();await poll();});}
async function resumeRoom(){const r=rooms.value.find(x=>Number(x.id)===Number(resumeId.value));if(!r)return;selected.value=Number(r.npc_id);selectProfile();roomId.value=Number(r.id);terminalLink.value='';token.value='';await poll();scene.value=lastScene;approval.value=room.value?.approval??true;}
async function closeRoom(){await safely(async()=>{await request('close');await poll();});}
async function control(paused:boolean){await safely(async()=>{await request('control',{scene:scene.value,approval:approval.value,paused});await poll();});}
async function connect(){await safely(async()=>{token.value=token.value.trim();seen=new Set();await poll();connected.value=true;sessionStorage.setItem('npc-terminal-token',token.value);});}
async function poll(){
 if(polling || (!terminal&&!roomId.value) || (terminal&&!token.value))return;
 polling=true;
 try{
  const r=await request<{room:Room;messages:Message[];scene:string|null}>('state',{},true);
  if(!alive)return;
  if(room.value && (room.value.epoch!==r.room.epoch || r.room.paused)){generation++;stopAudio();if(recording.value)cancelRecording();}
  if(!terminal)lastScene=r.scene||'';
  room.value=r.room;messages.value=r.messages;
  const fresh=r.messages.filter(m=>m.role==='assistant'&&m.status==='published'&&!seen.has(Number(m.id)));
  r.messages.forEach(m=>seen.add(Number(m.id)));
  if(terminal && fresh.length){lastSpeech.value=Number(fresh[fresh.length-1].id);if(audioEnabled.value&&!r.room.paused)void speak(lastSpeech.value);}
 }catch(e){error.value=e instanceof Error?e.message:'Disconnesso';stopAudio();}finally{polling=false;}
}
async function generate(action:string,text:string){if(busy.value)return;busy.value=true;await safely(async()=>{await request(action,{text});if(action==='ask')question.value='';await poll();});busy.value=false;}
async function sendManual(){await safely(async()=>{await request('manual',{text:manual.value});manual.value='';await poll();});}
async function approveMessage(m:Message){await safely(async()=>{await request('approve',{message_id:m.id,content:m.content});await poll();});}
async function summarize(){busy.value=true;await safely(async()=>{proposal.value=(await request<{proposal:string}>('summary')).proposal;});busy.value=false;}
function toggleAudio(){audioEnabled.value=!audioEnabled.value;if(!audioEnabled.value)stopAudio();}
function stopAudio(){generation++;audio?.pause();audio=undefined;playing.value=false;if(objectUrl){URL.revokeObjectURL(objectUrl);objectUrl='';}}
async function speak(id:number){stopAudio();const version=generation;await safely(async()=>{
 const r=await fetch(apiUrl('npc/speech'),{method:'POST',headers:{'Content-Type':'application/json','X-NPC-Token':token.value},body:JSON.stringify({message_id:id})});
 if(!r.ok){const p=await r.json();throw new Error(p.error||'Sintesi vocale non disponibile');}
 const blob=await r.blob();if(!alive||version!==generation||room.value?.paused)return;
 objectUrl=URL.createObjectURL(blob);audio=new Audio(objectUrl);audio.onended=()=>{playing.value=false;};
 try{await audio.play();playing.value=true;}catch{throw new Error('Premi Riascolta per consentire la riproduzione audio sul tablet.');}
 });}
async function startRecording(){await safely(async()=>{
 if(!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder)throw new Error('Microfono non disponibile: usa HTTPS o la domanda scritta.');
 stopAudio();stream=await navigator.mediaDevices.getUserMedia({audio:true});
 const mime=['audio/webm;codecs=opus','audio/mp4','audio/webm'].find(t=>MediaRecorder.isTypeSupported(t));
 recorder=new MediaRecorder(stream,mime?{mimeType:mime}:undefined);const chunks:BlobPart[]=[];const version=generation;
 recorder.ondataavailable=e=>{if(e.data.size)chunks.push(e.data);};
 recorder.onstop=()=>{stream?.getTracks().forEach(t=>t.stop());recording.value=false;clearTimeout(captureTimer);if(alive&&version===generation)void transcribe(new Blob(chunks,{type:recorder?.mimeType||'audio/mp4'}));};
 recorder.start();recording.value=true;captureTimer=setTimeout(endRecording,60000);
 });}
function endRecording(){if(recorder?.state==='recording')recorder.stop();}
function cancelRecording(){generation++;endRecording();stream?.getTracks().forEach(t=>t.stop());}
async function transcribe(blob:Blob){busy.value=true;await safely(async()=>{const data=new FormData();data.append('audio',blob,blob.type.includes('mp4')?'recording.mp4':'recording.webm');const r=await fetch(apiUrl('npc/transcribe'),{method:'POST',headers:{'X-NPC-Token':token.value},body:data});const p=await r.json();if(!r.ok||!p.ok)throw new Error(p.error||'Trascrizione non disponibile');question.value=p.data.text;});busy.value=false;}
onMounted(async()=>{
 if(terminal){token.value=location.hash.slice(1)||sessionStorage.getItem('npc-terminal-token')||'';if(token.value){history.replaceState(null,'',location.pathname);await connect();}}
 else{const r=await apiGet<{campaigns:{id:number;name:string}[]}>('npc/campaigns');if(r.ok)campaigns.value=r.data?.campaigns||[];else error.value=r.error||'Accedi per continuare';}
 startPolling();
});
function startPolling(){clearInterval(timer);timer=setInterval(()=>void poll(),1500);}
onIonViewWillLeave(()=>{clearInterval(timer);clearTimeout(captureTimer);cancelRecording();stopAudio();});
onIonViewDidEnter(()=>{if(alive)startPolling();});
onBeforeUnmount(()=>{alive=false;clearInterval(timer);clearTimeout(captureTimer);cancelRecording();stopAudio();});
</script>
<style scoped>
.npc-workspace{max-width:1000px;margin:auto;display:grid;gap:20px;padding-bottom:40px}.form-card{padding:20px}label{display:block;margin:12px 0;font-size:14px}input:not([type=checkbox]),textarea,select{display:block;width:100%;box-sizing:border-box;margin-top:6px;padding:12px;color:var(--ion-text-color);background:var(--ion-card-background,var(--ion-background-color));border:1px solid #7776;border-radius:10px;font:inherit}textarea{resize:vertical}.npc-log{max-height:50vh;overflow:auto;white-space:pre-wrap}.npc-log>div,.npc-log>p{border-bottom:1px solid #8884;padding:12px 0}h2{font-size:21px}a{overflow-wrap:anywhere}
</style>
