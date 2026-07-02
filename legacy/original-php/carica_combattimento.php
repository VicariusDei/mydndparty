
 <?php
	session_start();
	
	header("Content-Type: text/html; charset=UTF-8");
    
// Connessione al database
    include('dbConfig.php');
	include('funzioni.php');

    $conn = new mysqli($servername, $username, $password, $dbname);

	if ($conn->connect_error) {
        die("Connessione al database fallita: " . $conn->connect_error);
    }

	if(isset($_SESSION['loginuser'])) {
		// L'utente � autenticato con successo
		$loginuser 			= $_SESSION['loginuser']; 
		$idUtente 			= getUserId($conn, $loginuser);
		$gruppoAttivo 		= getGruppoAttivo($conn, $loginuser);
		$dadoPredefinito	= getDadoPredefinito($conn,$loginuser);
		
		if ($gruppoAttivo === 0) {
			exit();
		}
		// ELIMINA COMBATTENTE
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["eliminacombattente"]) && $_POST["eliminacombattente"] = "S") {
			$id = $_POST["id"];
			$query = "DELETE FROM combattimento WHERE idCombattimento = $id";
			$result = $conn->query($query);
		}

		// MODIFICA DEL FLAG ATTIVA AUDIO
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["fightAttivo"])) {
			$query = "update round set attivo = " .  $_POST['fightAttivo'];
			$result = $conn->query($query);
		}

		// AGGIUNGI AVVERSARIO
		if (isset($_GET['nuovoavversario']) && $_GET['nuovoavversario']==="S") {
			$query = "INSERT INTO combattimento (idGruppo, idPersonaggio, personaggio, iniziativa, bonusIniziativa) 
						VALUES ($gruppoAttivo, 0, 'Nuovo Avversario', 0, 0);";
			$result = $conn->query($query);
		}
		
		
		// APPLICA EFFETTO
		if (isset($_GET['applicaeffetto']) && $_GET['applicaeffetto']==="S") {
			$idCombattente = $_GET['idcombattenteeffetto'];
			$effetto = $_GET['tipoeffetto'];
			$durata = $_GET['durataeffetto'];
			$permanente = $_GET['permanente'];
			$query = "INSERT INTO effetti (idCombattimento, effetto, round, permanente) 
						VALUES ($idCombattente, '$effetto', $durata, '$permanente');";
			$conn->query($query);
			echo $query;
		}
		
		// ELIMINA EFFETTO
		if (isset($_GET['eliminaeffetto']) && $_GET['eliminaeffetto']==="S") {
			$id = $_GET['id'];
			$query = "DELETE FROM effetti 
						WHERE id=$id";
			$conn->query($query);
			echo $query;
		}


		// SALVATAGGIO DATI FORM COMBATTIMENTO
		if (isset($_GET['aggiornainiziativa']) && $_GET['aggiornainiziativa']==="S") {

			$rawData = file_get_contents("php://input");
			$dati = json_decode($rawData, true);

			foreach ($dati as $dato) {
				$id = mysqli_real_escape_string($conn, $dato['id']);
				$personaggio = mysqli_real_escape_string($conn, $dato['personaggio']);
				$iniziativa = mysqli_real_escape_string($conn, $dato['iniziativa']);
				$bonusiniziativa = mysqli_real_escape_string($conn, $dato['bonusiniziativa']);        
				$lentoPost = mysqli_real_escape_string($conn, $dato['lento']);

				if ($lentoPost === 'true' || $lentoPost === true) {
					$lento = 'S';
				} else {
					$lento = $lentoPost;//'N';
				}       

				$query = "UPDATE combattimento 
							SET personaggio = '$personaggio', lento = '$lento', iniziativa = '$iniziativa', bonusIniziativa = '$bonusiniziativa' 
							WHERE idCombattimento = $id and idGruppo = $gruppoAttivo;";
				$result = $conn->query($query);
			} 
		}


		// AVANZAMENTO DI INIZIATIVA
		if (isset($_GET["avanzainiziativa"]) && $_GET["avanzainiziativa"] === "S") {
			$query = "SELECT MAX(iniziativa) AS max_iniziativa 
						FROM combattimento 
						WHERE fight = 0 and idGruppo = $gruppoAttivo"; 
			$result = $conn->query($query);

			if ($result && $row = $result->fetch_assoc()) {
				$max_iniziativa = $row['max_iniziativa'];

				//$query = "UPDATE Sql1758266_1.combattimento SET fight = 1 WHERE fight = 0 AND iniziativa = $max_iniziativa";
				$query = "UPDATE combattimento 
							SET fight = 1 
							WHERE fight = 0 AND iniziativa = $max_iniziativa and idGruppo = $gruppoAttivo";
				$result = $conn->query($query);

			}
		}

		
		
		// SALVATAGGIO DATI SINGOLO COMBATTENTE (pulsante salva su riga)
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["salvainiziativacombattente"]) && $_POST["salvainiziativacombattente"] = "S") {
						//$personaggio = $_POST["personaggio"];
			//$iniziativa = $_POST['iniziativa'];
			$id = $_POST["id"];
			$valore = $_POST['valore'];
			$tipo = $_POST['tipo'];
			//$lentoPost = $_POST['lento'];
			//$lento = ($lentoPost === 'true' || $lentoPost === true) ? 'S' : 'N';     

			//$stmt = $conn->prepare("UPDATE combattimento SET personaggio = ?, iniziativa = ?, bonusIniziativa = ?, lento = ? 
			//							WHERE idCombattimento = ? and idGruppo = ?");
			//$stmt->bind_param("ssisii", $personaggio, $iniziativa, $bonusiniziativa, $lento, $id, $gruppoAttivo);
			if ($tipo === 'bonusIniziativa') {
				$stmt = $conn->prepare("UPDATE combattimento SET bonusIniziativa = ?
											WHERE idCombattimento = ? and idGruppo = ?");
				$stmt->bind_param("iii", $valore, $id, $gruppoAttivo);
				
			} else if ($tipo === 'lento') {
				$lento = ($valore === 'true' || $valore === true) ? 'S' : 'N';

				$stmt = $conn->prepare("UPDATE combattimento SET lento = ?
											WHERE idCombattimento = ? and idGruppo = ?");
				$stmt->bind_param("sii", $lento, $id, $gruppoAttivo);

			}
			$stmt->execute();
			$stmt->close();
		}




		// NUOVO COMBATTIMENTO
		if (isset($_GET["avversari"]) && $_GET["nuovocombattimento"] === "S") {
			$avversari = $_GET["avversari"];

			// cancello tutte le instanze del vecchio combattimento
			$query = "DELETE FROM effetti
						WHERE exists (SELECT * FROM combattimento WHERE idGruppo = $gruppoAttivo AND combattimento.idCombattimento = effetti.idCombattimento)";
			$result = $conn->query($query);
			
			$query = "DELETE FROM combattimento
						WHERE idGruppo = $gruppoAttivo";
			$result = $conn->query($query);
			
			$query = "DELETE FROM round
						WHERE idGruppo = $gruppoAttivo";
			$result = $conn->query($query);
			
			$query = "INSERT INTO round (idGruppo, round, id, attivo)
						VALUES ($gruppoAttivo, 0, 0, 0)";
			$result = $conn->query($query);
			
			$query = "INSERT INTO combattimento (idGruppo, idPersonaggio, personaggio, iniziativa, bonusIniziativa, lento, fight) 
						SELECT $gruppoAttivo, compagnia.id, nomePG, 0, bonusIniziativa, 'N', 0 
						FROM compagnia, gruppi
						WHERE gruppi.id = $gruppoAttivo and compagnia.idGruppo = gruppi.id ;";
			$result = $conn->query($query);

			
			// Ciclo per inserire gli avversari nella tabella combattimento
			for ($i = 0; $i < $avversari; $i++) {
				$nomeAvversario =  "Gruppo " . $i+1;//mysqli_real_escape_string($conn, prompt("Inserisci il nome dell'avversario:"));

				// Esempio di query per inserire gli avversari con iniziativa e bonusIniziativa a 0
				$queryAvversario = "INSERT INTO combattimento (idGruppo, idPersonaggio, personaggio, iniziativa, bonusIniziativa, lento, fight) 
										VALUES ($gruppoAttivo, 0, '$nomeAvversario', 0, 0,'N',0);";
				$resultAvversario = $conn->query($queryAvversario);
			}
		}


		// NUOVO ROUND
		if (isset($_GET["nuovoround"]) && $_GET["nuovoround"] === "S") {
			$dadoIniziativa = $_GET["dadoIniziativa"];
			
			$righeEvidenziate = array();
			
			$query = "UPDATE combattimento 
						SET iniziativa = bonusIniziativa+ CEILING(RAND() * $dadoIniziativa)
						WHERE idGruppo = $gruppoAttivo;";
			$result = $conn->query($query);

			$query = "UPDATE combattimento 
						SET iniziativa = 0 
						WHERE lento ='S' AND idGruppo = $gruppoAttivo;";
			$result = $conn->query($query);

			$query = "UPDATE combattimento 
						SET fight = 0
						WHERE idGruppo = $gruppoAttivo;";
			$result = $conn->query($query);

			$query = "UPDATE round 
						SET round = round + 1, id = (SELECT idCombattimento FROM combattimento WHERE idGruppo = $gruppoAttivo order by iniziativa desc limit 1)
						WHERE idGruppo = $gruppoAttivo;";
			$result = $conn->query($query);
			
			$query = "UPDATE effetti 
						SET round = round - 1 
						WHERE exists (SELECT * FROM combattimento WHERE idGruppo = $gruppoAttivo AND combattimento.idCombattimento = effetti.idCombattimento) and permanente = 'N';";
			$result = $conn->query($query);
			
			// cancello tutte le instanze del vecchio combattimento
			$query = "DELETE FROM effetti
						WHERE exists (SELECT * FROM combattimento WHERE idGruppo = $gruppoAttivo AND combattimento.idCombattimento = effetti.idCombattimento) and round=0 and permanente = 'N'";
			$result = $conn->query($query);

		}
		
		$personaggiAttiviArray = [];
		$query = "SELECT idCombattimento id  
					FROM combattimento, round 
					WHERE fight = 0 AND iniziativa = (SELECT MAX(iniziativa) FROM combattimento WHERE fight = 0 and idGruppo = $gruppoAttivo) and combattimento.idGruppo = $gruppoAttivo and round.idGruppo =combattimento.idGruppo and round>0";
		$personaggiAttivi = $conn->query($query);
		
		while ($row = $personaggiAttivi->fetch_assoc()) {
    		$personaggiAttiviArray[] = $row['id'];
			//echo $row['IdPersonaggio'];
		}
		
		
		// Query per selezionare tutti i dati dalla tabella
		$query = "SELECT combattimento.idCombattimento id, combattimento.personaggio personaggio, combattimento.iniziativa iniziativa, combattimento.bonusIniziativa bonusIniziativa, combattimento.lento lento
					FROM combattimento 
							left outer join gruppi on combattimento.idGruppo = gruppi.id 
							left outer join compagnia on compagnia.idGruppo = gruppi.id and combattimento.idPersonaggio = compagnia.id
					WHERE gruppi.id = $gruppoAttivo
					ORDER BY iniziativa desc"; 
		$result = $conn->query($query);

		echo "<thead>
				<tr>
					<th>Iniziativa</th>
					<th></th>
					<th>Personaggio</th>
					<th></th>
					<th>Lento</th>
					<th>Rapido</th>
					<th>Bonus</th>
					<th></th>
				</tr>
			   </thead><tbody>";

		if ($result->num_rows > 0) {

			while ($row = $result->fetch_assoc()) {
				
				$classeAttivo = (in_array($row['id'], $personaggiAttiviArray)) ? '<img src="img/swords.png" class="icon">' : '';

				echo '<tr>';
				
				echo '<td><input class="iniziativa max-width-2" type="text" id="iniziativa_' . $row["id"] . '" value="' . $row["iniziativa"] . '"></td>';
				echo '<td>';
				echo $classeAttivo;
				echo '</td>';
				echo '<td>';
				echo ' 	<input class="personaggio max-width-30" type="text" id="personaggio_' . $row["id"] . '" value="' . $row["personaggio"] . '"></td>';
				echo '	<td> <button class="button-with-image-effetto" data-action="applica-effetto" data-support="' . $row["personaggio"] . '" data-id="' . $row["id"] . '"></button>';
				echo '</td>';
				
				$value = $row["lento"];
				$checkedAttribute = ($value === 'S') ? 'checked' : '';

				echo '<td>';
				echo '<input data-action = "impostaLento" data-support="' . $row["personaggio"] . '" data-id="' . $row["id"] . '" class="lento" type="checkbox" id="lento_' . $row["id"] . '" ' . $checkedAttribute . '/>';
				echo '</td>';
				
				echo '<td>';
				echo '<input data-action = "impostaRapido" data-support="' . $row["personaggio"] . '" data-id="' . $row["id"] . '" class="rapido" type="checkbox" id="rapido_' . $row["id"] . '" ' . $checkedAttribute . '/>';
				echo '</td>';

				echo '<td><input data-action = "apriTastieraNumerica" class="bonusiniziativa2 max-width-2" type="text" id="bonusiniziativa_' . $row["id"] . '" value="' . $row["bonusIniziativa"] . '" data-support="' . $row["personaggio"] . '"></td>';

				echo '<td>';
				//echo '<button data-action="salva-iniziativa-combattente" data-id="' . $row["id"] . '"  data-iniziativa="' . $row["iniziativa"] . '" data-bonus-iniziativa="'.$row["bonusIniziativa"].'">💾</button>&nbsp;';
				echo '<button data-action="elimina-combattente" data-id="' . $row["id"] . '">🗑️</button></td>';
				echo "</tr>";
			}
		} else { 
			echo "<tr><td colspan='7'>Attenzione, ricorda di creare gruppi e personaggi prima di avviare un combattimento</td></tr>";
		}

		
		$query = "SELECT round
					FROM round 
					WHERE idGruppo = $gruppoAttivo"; 
		$result = $conn->query($query);
		$row= $result->fetch_assoc();
		$round = $row["round"];
		//$checkedAttivo = ($row["attivo"] === "1") ? 'checked' : '';

		echo "<tr>";
		echo '<td colspan="7"><span style="font-size: 20px;"  class="rounded-background"><strong>Round Attuale: ' . $round . '</strong></span></td>';

		echo '<td colspan="1"><Button id="refresh"/>🔄</BUTTON></td>';
		echo "</tr>";

		$query = "SELECT GROUP_CONCAT(personaggio SEPARATOR ', ') AS nomi_personaggi, GROUP_CONCAT(idCombattimento SEPARATOR ', ') as id  
						FROM combattimento 
						WHERE fight = 0 AND iniziativa = (SELECT MAX(iniziativa) FROM combattimento WHERE fight = 0 and idGruppo = $gruppoAttivo) and idGruppo = $gruppoAttivo";


		$result = $conn->query($query);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			echo "<tr><td colspan='8'><table style='width:100%'><tr>";
			//echo '<td style="width:20%; min-width:200px;"><label class="switch switch--elastic jsSwitcher" role="switch" aria-label="elastic switch" aria-checked="false">🔈 <input type="checkbox" data-action="attivaFight" data-id="' . $attivo . '" id="attivo" ' . $checkedAttivo .' class="off-screen" name="switcher" aria-hidden="true"/><span class="switch__lever"></Button></label></td>';       
			if ($row["nomi_personaggi"] === null) {
				echo '<td style="width:40%; min-width:200px;"><span style="font-size: 20px;"><strong>ROUND TERMINATO</strong></span></td>';
				echo '<td style="width:40%; min-width:200px;"><button id="nuovo-round" data-action="nuovo-round">Nuovo Round</button></td>';
				//$audio = "Round terminato, stronzi";           
			} else {
				if ($round === "0") {
					echo '<td style="width:40%; min-width:200px;"><span style="font-size: 20px;">Imposta i bonus, salvali e inizia!</strong></span></br></td>';
					echo '<td style="width:40%; min-width:200px;"><button id="nuovo-round" data-action="nuovo-round">Inizio Combattimento</button></td>';
				}  else {
					echo '<td style="width:40%; min-width:200px;"><span style="font-size: 20px;">Tocca a: <strong>' . $row["nomi_personaggi"] . '</strong></span></br></td>';
					echo '<td style="width:40%; min-width:200px;"><button id="avanza-iniziativa" data-action="avanza-iniziativa">Avanza Iniziativa</button></td>';
					//$audio = $frasi[array_rand($frasi)] . " " . $row["nomi_personaggi"];
				}
			}
			echo '<td style="width: auto;min-width:100px;">
			<label for="dadoIniziativa">Dado Iniziativa</label>
			<select name="dadoIniziativa"  id="dadoIniziativa"  style="width: auto;" required>';
				$query = "select id, dado, descrizione, predefinita FROM dadoIniziativa";  
				$result = $conn->query($query);
				if ($result->num_rows > 0) {
					while ($row = $result->fetch_assoc()) {
						$selected = $row['id'] == $dadoPredefinito ? 'selected' : '';
						echo "<option value='".$row['dado']."' ".$selected.">".$row['descrizione']."</option>";
					}
				}
			echo '
			</select> 
			</td>';

			echo "</tr></table></td></tr>";
		}
		
		$query = "SELECT effetti.id id, combattimento.idCombattimento idCombattimento, personaggio, effetto, round, permanente
						FROM combattimento, effetti
						WHERE idGruppo = $gruppoAttivo and effetti.idCombattimento  = combattimento.idCombattimento ";
		$result = $conn->query($query);
		// Inizia la tabella HTML

		// Verifica se ci sono righe nel risultato
		if ($result->num_rows > 0) {
			// Inizia la tabella HTML
			echo '<table>';
			echo '<tr><th>Personaggio</th><th>Effetto</th><th>Round rimasti</th><th>Permanente</th><th></th></tr>';

			// Itera su ogni riga del risultato
			while ($row = $result->fetch_assoc()) {
				echo "<tr>";
				echo "<td>" . htmlspecialchars($row['personaggio']) . "</td>";
				echo "<td>" . htmlspecialchars($row['effetto']) . "</td>";
				echo "<td>" . htmlspecialchars($row['round']) . "</td>";
				echo "<td>" . htmlspecialchars($row['permanente']) . "</td>"; // Modifica come necessario per la rappresentazione di 'permanente'
				echo '<td><button data-action="elimina-effetto" data-id="' . $row["id"] . '">🗑️</button></td>';
				echo "</tr>";
			}

			// Chiudi la tabella HTML
			echo '</table>';
		 
		}

		echo '</tbody>
				<tfoot>
					<tr>
						<td colspan="5">
							<!--<button id="nuovo-round" data-action="nuovo-round">Nuovo Round</button>
							<button id="salva-combattimento" data-action="salva-combattimento">Salva Combattimento</button>-->
						</td>
					</tr>
					<tr>
						<td colspan="5">
							<button id="nuovo-avversario">Nuovo Combattente</button>

						</td>
					</tr>
					<tr>
						<td colspan="5">
							<button id="nuovo-combattimento">Nuovo Combattimento</button>
						</td>
					</tr>
					
				</tfoot>
				

		'; 
	
	} else { 
		echo 'problemi di connessione';
	}
    $conn->close();

        
?>


