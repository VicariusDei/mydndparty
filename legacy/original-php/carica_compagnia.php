	
<?php
	session_start();
    header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
    header("Pragma: no-cache"); // HTTP 1.0
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in passato 

    // Connessione al database
    include('dbConfig.php');
	include('funzioni.php');
	
    $conn = new mysqli($servername, $username, $password, $dbname);


	if(isset($_SESSION['loginuser'])) {
		// L'utente � autenticato con successo
		$loginuser = $_SESSION['loginuser']; 
		$idUtente = getUserId($conn, $loginuser);
	

		
		if ($conn->connect_error) {
			die("Connessione al database fallita: " . $conn->connect_error);
		}
    
    	// ---------------------------- ELIMINA PERSONAGGIO
	   	if (isset($_GET['eliminaPersonaggio']) && $_GET['eliminaPersonaggio']==="S") {
			$idPersonaggio = $_GET["id"];
			$query = "DELETE FROM compagnia WHERE id =$idPersonaggio and idUtente = $idUtente"; 
			//echo $query;
			$conn->query($query);
		}

		// ---------------------------- SALVA AGGIUNTA PERSONAGGIO
		if (isset($_GET['salvaNuovoPersonaggio']) && $_GET['salvaNuovoPersonaggio']==="S") 
		{  
			$salvaNuovoPersonaggio = $_GET['salvaNuovoPersonaggio'];
			$nomeGiocatorenew = $_GET['nomeGiocatorenew'];
			$nomePersonaggionew = $_GET['nomePersonaggionew'];
			$bonusiniziativanew = $_GET['bonusiniziativanew'];
			$mottonew = $_GET['mottonew'];
			$classenew = $_GET['classenew'];
			$razzanew = $_GET['razzanew'];
			$grupponew = $_GET['grupponew'];

			$query = "INSERT INTO compagnia (nomeGiocatore, nomePG, bonusIniziativa, Motto, Classe, Razza, idUtente, idGruppo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt = $conn->prepare($query);
			
			if ($stmt) {
				// Collegare i parametri e impostarli
				$stmt->bind_param("ssisssis", $nomeGiocatorenew, $nomePersonaggionew, $bonusiniziativanew,  $mottonew, $classenew, $razzanew, $idUtente, $grupponew);
				
				// Eseguire la query
				if ($stmt->execute()) {
					// Successo
					logStatement($conn, "carica_compagnia.php", "Personaggio ". $nomePersonaggionew ." aggiunto con successo.", 4, $idUtente);
				} else {
					// Errore nell'esecuzione della query
					logStatement($conn, "carica_compagnia.php", "Errore durante l'aggiunta del personaggio: " . $stmt->error, 3, $idUtente);
				}

				// Chiudere il prepared statement
				$stmt->close();
			} 
			
		}


	
	// ---------------------------- 					SALVA MODIFICA PERSONAGGIO
		if (isset($_GET['salvaModificaPersonaggio']) && $_GET['salvaModificaPersonaggio']==="S") 
		{  
			$personaggioId      = $_GET['id'];
			$nomeGiocatorenew   = $_GET['nomeGiocatorenew'];
			$nomePersonaggionew = $_GET['nomePersonaggionew'];
			$bonusiniziativanew = $_GET['bonusiniziativanew'];
			$mottonew           = $_GET['mottonew'];
			$classenew          = $_GET['classenew'];
			$razzanew           = $_GET['razzanew'];
			$grupponew 			= $_GET['idGrupponew'];
			

			
			$query = "UPDATE compagnia 
              			SET NomePG=?, nomeGiocatore=?, Classe=?, Razza=?, bonusIniziativa=?, Motto=?, idGruppo=?
              			WHERE id=? AND idUtente=?";
    
			// Utilizzare un prepared statement
			$stmt = $conn->prepare($query);

			if ($stmt) {
				// Collegare i parametri e impostarli
				$stmt->bind_param("ssssisiii", $nomeGiocatorenew, $nomePersonaggionew, $classenew, $razzanew, $bonusiniziativanew, $mottonew, $grupponew, $personaggioId, $idUtente);

				// Eseguire la query
				if ($stmt->execute()) {
					// Successo
					logStatement($conn, "carica_compagnia.php", "Modifiche al personaggio $personaggioId eseguite con successo.", 4, $userId);
				} else {
					// Errore nell'esecuzione della query
					logStatement($conn, "carica_compagnia.php", "Errore durante la modifica del personaggio $personaggioId: " . $stmt->error, 3, $userId);
				}

				// Chiudere il prepared statement
				$stmt->close();
			} 
		}


	//  -----------------------------                  	AGGIUNGI PERSONAGGIO
    if (isset($_GET['aggiungiPersonaggio']) && $_GET['aggiungiPersonaggio']==="S") 
    {
        echo '
    
                <thead>
                    <tr>
                        <th>Inserisci il nuovo personaggio</th>
                    </tr>
                </thead>
                <tbody><tr>
                <td>
                    <label>Nome Giocatore:</label>
                    <input type="text" name="nomeGiocatore" id = "newNomeGiocatore" required><br><br>

                    <label>Nome Personaggio:</label>
                    <input type="text" name="nomePersonaggio" id = "newNomePersonaggio" required><br><br>

                    <label>Bonus Iniziativa:</label>
                    <input type="number" name="bonusiniziativa" id = "newBonusIniziativa" required><br><br>

                    <label>Motto:</label>
                    <input type="text" name="Motto" id = "newMotto"><br><br>

                    <label>Classe:</label>
                    <select name="Classe"  id = "newClasse" required>
                    ';                      
                            
					$query = "select classe FROM classi order by classe"; 
					$result = $conn->query($query);

					if ($result->num_rows > 0) {
							while ($row = $result->fetch_assoc()) {
								echo "<option value='".$row['classe']."'>".$row['classe']."</option>";
							}
					}

					echo '
					</select><br><br>

					<label for="Razza">Razza:</label>
					<select name="Razza"  id = "newRazza" required>';

					$query = "select razza FROM razze order by razza"; 
					$result = $conn->query($query);

					if ($result->num_rows > 0) {
							while ($row = $result->fetch_assoc()) {
								echo "<option value='".$row['razza']."'>".$row['razza']."</option>";
							}
					}
					echo '
                    </select> <br><br>
					
					<label for="Gruppo">Gruppo:</label>
					<select name="gruppo" id="newGruppo" required>';

					$query = "select id, Gruppo FROM gruppi WHERE idUser =$idUtente order by Gruppo"; 
					$result = $conn->query($query);

					if ($result->num_rows > 0) {
							while ($row = $result->fetch_assoc()) {
								echo "<option value='".$row['id']."'>".$row['Gruppo']."</option>";
							}
					}
					echo '
					</select>
					
					</td></tr></tbody>
							<tfoot>
							<tr><td style="text-align:center;">
								<button data-action="annulla-aggiungi-personaggio">🔙</button>&nbsp;
								<Button data-action="salva-aggiungi-personaggio" >💾</button>
							</td></tr>
							</tfoot>            

        ';
    } 
	//  -----------------------------                  MODIFICA PERSONAGGIO
		elseif (isset($_GET['modificaPersonaggio']) && $_GET['modificaPersonaggio']==="S") 
    
   	{
		$personaggioId = $_GET['id'];

		$query = "SELECT 	compagnia.id, nomePG, nomeGiocatore, bonusIniziativa, Classe, Razza, Motto, idGruppo, Gruppo
					FROM 	compagnia, gruppi 
					WHERE 	compagnia.idUtente=$idUtente and compagnia.idGruppo = gruppi.id and 
							compagnia.idUtente = gruppi.idUser and compagnia.id = $personaggioId"; // 
			
		//$query = "select * FROM compagnia WHERE id = $personaggioId and idUtente = $idUtente"; 
		$result = $conn->query($query);

		if ($result->num_rows > 0){
			// Genera HTML tabellare con i dati
			$row = $result->fetch_assoc();
			$nomePG = $row['nomePG'];
			$nomeGiocatore = $row['nomeGiocatore'];
			$Classe =  $row['Classe'];
			$Razza = $row['Razza'];
			$Motto = $row['Motto'];
			$bonusIniziativa =  $row['bonusIniziativa'];
			$idGruppo =  $row['idGruppo'];
			$Gruppo =  $row['Gruppo'];
		}

		echo '
				<thead>
					<tr>
					<th>Modifica Personaggio</th>
					</tr>
				</thead>

				<tr>
				<td> ';
		echo '        
		<tbody>
			<input type="hidden" id="nuovo_personaggioid" value="'.$personaggioId.'">

			<label>Nome Personaggio:</label>
			<input type="text" id="nuovo_nomePG" value="'.$nomePG.'" placeholder="Nome del personaggio" required><br><br>

			<label>Nome Giocatore:</label>
			<input type="text" id="nuovo_nomeGiocatore" value="'.$nomeGiocatore.'" placeholder="Nome del giocatore" required><br><br>

			<label>Bonus Iniziativa:</label>
			<input type="text" id="nuovo_bonusIniziativa" value="'.$bonusIniziativa.'" placeholder="Bonus Iniziativa" required><br><br>

			<label>Motto:</label>
			<input type="text" id="nuovo_Motto" value="'.$Motto.'" placeholder="Motto"><br><br>

			<label>Classe:</label>
			<select id="nuovo_classe">';
				$query = "select classe FROM classi order by classe"; 
				$result = $conn->query($query);

				if ($result->num_rows > 0) {
						while ($row = $result->fetch_assoc()) {
							$selezionato = ($Classe === $row['classe']) ? 'selected' : '';
							echo "<option value='".$row['classe']."' ".$selezionato.">".$row['classe']."</option>";
						}
				}

			echo'
			</select><br><br>

			<label>Razza:</label>
			<select id="nuovo_razza">';

				$query = "select razza FROM razze order by razza"; 
				$result = $conn->query($query);

				if ($result->num_rows > 0) {

						while ($row = $result->fetch_assoc()) {
							$selezionato = ($Razza === $row['razza']) ? 'selected' : '';
							echo "<option value='".$row['razza']."' ".$selezionato.">".$row['razza']."</option>";
						}
				}
			echo '
			</select><br><br>
			
			<label>Gruppo:</label>
			<select id="nuovo_gruppo">';

				$query = "select id, Gruppo FROM gruppi where idUser = $idUtente order by Gruppo"; 
				$result = $conn->query($query);

				if ($result->num_rows > 0) {

						while ($row = $result->fetch_assoc()) {
							$selezionato = ($idGruppo === $row['id']) ? 'selected' : '';
							echo "<option value='".$row['id']."' ".$selezionato.">".$row['Gruppo']."</option>";
						}
				}
			echo '
			</select><br><br>



			</td>
			</tr></tbody>
			<tfoot>
			<tr><td style="text-align:center;">
				<button data-action="annulla-modifica-personaggio">🔙</button>&nbsp;
				<button data-action="salva-modifica-personaggio">💾</button>
			</td></tr>
			</tfoot>';

    }  
		else 
    
    {  // Query per selezionare tutti i dati dalla tabella
		$query = "SELECT 	compagnia.id, nomePG, nomeGiocatore, bonusIniziativa, Classe, Razza, Motto, idGruppo, Gruppo
					FROM 	compagnia, gruppi 
					WHERE 	compagnia.idUtente=$idUtente and compagnia.idGruppo = gruppi.id and compagnia.idUtente = gruppi.idUser 
					ORDER By Gruppo"; // Sostituisci "nome_tabella" con il nome effettivo della tabella
		$result = $conn->query($query);

		echo '<table id="tabella-compagnia-ordinata"  class="display"> 
				<thead>
					<tr>
						<th data-orderable="true">Personaggio</th>
						<th data-orderable="true">Gruppo</th>
						<th></th>
					</tr>
				</thead>
				<tbody>';

		if ($result->num_rows > 0) {
			// Genera HTML tabellare con i dati
			while ($row = $result->fetch_assoc()) {
				echo "<tr>";
				echo "<td style='text-align: left;'><strong>" . $row["nomePG"] . "</strong> - Bonus Ini: <strong>".$row["bonusIniziativa"]."</strong></br>";
				echo "Classe:<strong>" . $row["Classe"]. " </strong> Razza: <strong>" . $row["Razza"] . "</strong></br>";
				echo "Giocatore: <strong>" . $row["nomeGiocatore"] . "</strong></td>";
				echo "<td>" . $row["Gruppo"] . "</td>";
				echo '<td><button data-action="modifica-personaggio" data-id="' . $row["id"] . '">✏️</button>&nbsp;<button data-action="elimina-personaggio" data-id="' . $row["id"] . '">🗑️</button></td>';
				echo "</tr>";
			}
		} else {
			echo "<tr><td colspan='5'>Nessun dato trovato.</td></tr>";
		}
		echo "	</tbody>
				<tfoot>
					<tr>
						<td colspan='3'>
							<div id='fixed-button'>
								<button id='aggiungi-personaggio' data-action='aggiungi-personaggio' class='button-with-image-nuovo-personaggio'> </button>
							</div>
						</td>
					</tr>
				</tfoot>
			</table>";
			
		echo'

		<!--	<table id="tabler-compagnia" class="display"><thead><tr><th>test</th></tr></thead></table>-->

		';
	}

} else { 
	echo 'problemi di connessione';
}

    $conn->close();

?>


