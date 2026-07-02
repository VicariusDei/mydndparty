<?php 
session_start();

// Verifica se l'utente è autenticato, ad esempio controllando la presenza della variabile di sessione 'username'
if(isset($_SESSION['loginuser'])) {
    // L'utente è autenticato con successo
    $loginuser = $_SESSION['loginuser']; 
	
?>
    <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Dnd Party</title>
	
    <link rel="stylesheet" href="css\styles.css"    type="text/css" >
	<link rel="stylesheet" href="css\navbar.css"    type="text/css" >
	<link rel="stylesheet" href="css\modale.css"    type="text/css" >
	<link rel="stylesheet" href="DataTables/datatables.min.css" type="text/css">
	
    <script src="script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!--<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>-->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
	<script src="DataTables/datatables.min.js"></script>
    

</head>

	<?php
// Leggi tutti i file nella cartella "VIDEO"
$videoDir = 'video';
$videoFiles = array();

// Verifica se la directory esiste
if (is_dir($videoDir)) {
    // Leggi i file video con estensione mp4 dalla directory
    $files = scandir($videoDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
            $videoFiles[] = $videoDir . '/' . $file;
        }
    }
}

// Converte l'array PHP in un array JavaScript
echo '<script>';
echo 'const videoFiles = ' . json_encode($videoFiles) . ';';
echo '</script>';
?>
	
<body>
	
    <div id="splash-screen" style="vertical-align: middle">
        <img src="img/logo.png" alt="Loading..." />
    </div>

    <div class="mianavbar">
		
		<div class="nav-links">
			<img src="./img/menu.png" class="tab-button-menu" alt="Menu" data-action="menuIconLateral" id="menuIconLateral">
			<a href="#" id="gruppi-tab" title="Gruppi">                      <img src="img/gruppi.png" 			alt="Gruppi"></a>
			<a href="#" id="compagnia-tab" title="Personaggi" class="active"><img src="img/compagnia.png" 		alt="Compagnia"></a>
			<a href="#" id="inventario-tab" title="Inventario">              <img src="img/inventario.png" 		alt="Inventario"></a>
			<a href="#" id="monete-tab" title="Monete">                    	 <img src="img/monete.png" 			alt="Inventario"></a>
			<a href="#" id="iniziativa-tab" title="Combattimento">           <img src="img/combattimento.png" 	alt="Combattimento"></a>
			<img src="img/line.png" class="iconaMedia">
		<!--	<a href="#" id="manuali-tab" title="Manuali">                    </a>
		</div>	
	
		<div class="user-buttons">-->
			<a href="#" id="utente-tab" title="<?php echo $loginuser; ?>">		<img src="img/profilo.png" 			alt="Utente"></a>	 
			<a href="index.php?logout=S" title="Esci">							<img src="img/logout.png" 			alt="Esci"></a>
			<img src="img/line.png" class="iconaMedia">

			<button id="openModalBtn"> <img src="img/tette.png" 		alt="Inventario" style="width: 25px; height: 25px"> </button>
	    </div>				
    </div>
	
<div id="videoModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <video id="randomVideo" controls></video>
    </div>
</div>
	
	
    <div id="gruppi-page" style="display: none;">
        <div id="gruppi-intestazione">			GRUPPI</div>
        <table id="table-gruppi">  </table>
    </div>

    <div id="inventario-page" style="display: none;">
        <div id="inventario-intestazione">		INVENTARIO</div>
        <table id="table-inventario">   </table>
    </div>

    <div id="monete-page" style="display: none;">
        <div id="monete-intestazione">			MONETE</div>
        <table id="table-monete">   </table>
    </div>

    <div id="compagnia-page">
        <div id="compagnia-intestazione">		PERSONAGGI</div>
        <table id="table-compagnia"  class="display"></table>
    </div>

    <div id="iniziativa-page" style="display: none;">
        <div id="combattimento-intestazione">	COMBATTIMENTO</div>
        <table id="table-combattimento"></table>
		<br>
		<table style="table-layout: fixed; width: 90%;">			
			<tr>
						<td >
							ISTRUZIONI
						</td>
					</tr>
					
					<tr>
						<td style="overflow-wrap: break-word;">
						<div style="text-align: left">
							1 - Per preparare un nuovo combattimento premere NUOVO COMBATTIMENTO<br>
							2 - Si indica il numero degli avversari che il master inizialmente comunica<br>
							3 - Si impostano i bonus di iniziativa per gli avversari salvando ogni riga<br>
							4 - Premendo INIZIO COMBATTIMENTO il sistema tira random un <strong>D6</strong> applica i bonus e ordina per iniziativa<br>
							5 - Premendo AVANZA INIZIATIVA il sistema chiama il turno dei personaggi<br>
							6 - al termine del round con NUOVO ROUND si riparte.<br>
							<br>
							Di fianco ad ogni personaggio è presente un pulsante che permette di applicare una condizione ad un combattente.<br> In questo modo è possibile tenete sotto controllo la durata degli incantesimi o condizioni particolari 
							<br>
							Nel caso in cui si dovessero aggiungere altri personaggio allo scontro potete usare il pulsante NUOVO COMBATTENTE
					 </div>
						</td>
					</tr>
					</table>
    </div> 

	<div id="utente-page" style="display: none;">
        <div id="utente-intestazione">			UTENTE</div>
        <table id="table-utente"></table>
    </div> 

	<div id="istruzioni-page" style="display: none;">
        <div id="istruzioni-intestazione">			MYDNDPARTY</div>
        <table id="table-istruzioni"></table>
    </div> 

	
    <div id="manuali-page" style="display: none;">
    <div id="manuali-intestazione">				MANUALI</div>
       <table>
            <thead>
				<tr>
					<th>Manuale</th>
					<th>Visualizza</th>
				</tr>
            </thead>
            <tr>
                <td>Manuale base</td>
                <td>
                    <button type="button" class="btn btn-primary" onclick="openPdfPage('1eGQlURiXVVA6CeuRIHNktLAy4E8hWGni')"> 🔎</button>              
                </td>
            </tr>
        <tr>
            <td>Ambientazione</td>
            <td>
                <button type="button" class="btn btn-primary" onclick="openPdfPage('1-dE2qpiuCa3utM4EsJBJ_rwdwIRp0tlN')">🔎</button>
            </td>
        </tr>
        <tr>
            <td>Druido e Illusionista</td>
            <td>
                <button type="button" class="btn btn-primary" onclick="openPdfPage('1-bTctTc6tSeqH5KQW4rd-6EDWzD4Kbgt')">🔎</button>
            </td>
        </tr>
        <tr>
            <td>Lista degli Incantesimi</td>
            <td>
                <button type="button" class="btn btn-primary" onclick="openPdfPage('1-5bpmCsQxxqFvxzNln7EvoNHASRlbdYx')">🔎</button>
            </td>
        </tr>
        <tr>
            <td>Manuale Expert</td>
            <td>
                <button type="button" class="btn btn-primary" onclick="openPdfPage('1SwqUFIAxP_UUokHLDYElF2nkwQPVwtzm')">🔎</button>
            </td>
        </tr>
        <tr>
            <td>Manuale del Master</td>
            <td>
                <button type="button" class="btn btn-primary" onclick="openPdfPage('1-VDcs14p1OWWMClswumoWTpDSGCwMXRl')">🔎</button>
            </td>
        </tr>
        <tr>
            <td>Tomo delle Regole</td>
            <td>
                <button type="button" class="btn btn-primary" onclick="openPdfPage('1-Z8_EkaHAB_Xo38OuIQsPOuhQ6czmWA_')">🔎</button>
            </td>
        </tr>  

        </table> 
    </div>

    <div style="text-align: center; font-size: 12px;">
        <br>
        <br>
        Una produzione MYDNDPARTY.
    </div>

	<div class="lateral-menu-tab" id="menuTab">
		<div class="tab-content-menu">
			<div>
				<img src="./img/menu.png" alt="Menu" class="tab-button-menu" data-action="menuIconLateral" id="menuIconLateral">
				<img src="./img/logo_orizzontale.png" alt="Menu" id="menuLogoLateral" >
			</div>
			<ul>
				<li><a href="#" id="link-impostazioni">	Impostazioni</a></li>
				<li><a href="#" id="link-istruzioni">	Istruzioni</a></li>
				<li><a href="#" id="link-formContatti">	Contatti</a></li>
				<!-- Ulteriori voci di menu -->
			</ul>
		</div>
		<!--<img src="./img/menu.png" alt="Menu" id="menuIconLateral" class="tab-button-menu">-->
    </div>
   			
	
</body>
	<script>
    // Seleziona elementi HTML
    const openModalBtn = document.getElementById('openModalBtn');
    const videoModal = document.getElementById('videoModal');
    const closeModal = document.querySelector('.close');
    const randomVideo = document.getElementById('randomVideo');

    // Funzione per aprire la modale e riprodurre un video random
    openModalBtn.onclick = function () {
        if (videoFiles.length > 0) {
            // Seleziona un video casuale dalla lista
            const randomIndex = Math.floor(Math.random() * videoFiles.length);
            randomVideo.src = videoFiles[randomIndex];
            randomVideo.play();
            videoModal.style.display = 'flex';
        } else {
            alert("Nessun video trovato nella cartella.");
        }
    }

    // Funzione per chiudere la modale
    closeModal.onclick = function () {
        videoModal.style.display = 'none';
        randomVideo.pause();
        randomVideo.currentTime = 0; // Riavvia il video quando la modale si chiude
    }

    // Chiudi la modale se si clicca fuori dalla finestra
    window.onclick = function (event) {
        if (event.target == videoModal) {
            videoModal.style.display = 'none';
            randomVideo.pause();
            randomVideo.currentTime = 0;
        }
    }
</script>
	
</html>

<?php 
} else {
    header("Location: index.php?furbetto=S");
    exit();
}
?>

