 <?php
	session_start();
	
	include('dbConfig.php');
	include('funzioni.php');
	
	$conn = new mysqli($servername, $username, $password, $dbname);
	
	// Verifica se l'utente � autenticato, ad esempio controllando la presenza della variabile di sessione 'username'
	if(isset($_SESSION['loginuser'])) {
		// L'utente � autenticato con successo
		$loginuser = $_SESSION['loginuser']; 
		$idUtente = getUserId($conn, $loginuser);

		
		if ($conn->connect_error) {
			die("Connessione al database fallita: " . $conn->connect_error);
		}

		if (isset($_POST['id']) && $_POST['modificaDiario']==="S") {
			$Idgruppo = $_POST['id'];
			$sql = "SELECT * FROM gruppi where id = " .  $Idgruppo;
			$result = $conn->query($sql);

			//echo "<table><tr><th>ID</th><th>Gruppo</th><th>Appunti</th><th>Azioni</th></tr>";
			$row = $result->fetch_assoc();
			echo '<thead><tr>';
			echo '		<th style="width: 100%;">Diario e scoperte del gruppo </br>';
			echo '				<input type="text" name="nuovo_nomeGruppo" value="'.$row["Gruppo"].'"></th>';
			echo '</tr></thead>';
			echo '<tbody>';
			echo '	<tr><td><textarea name="nuovo_appunti" >' . $row["Appunti"] . '</textarea></td>';
			echo '</tbody>';
			echo ' <tfoot><tr><td>	<button title="Torna Indietro" data-action="torna-gruppi">🔙</button>&nbsp;';
			echo '				  	<button title="Salva" data-action="salva-diario" data-id="' . $row["id"] . '">💾</button>&nbsp;';
			echo '</td></tr> </tfoot>';

		} elseif (isset($_POST['id']) && $_POST['salvaDiario']==="S") {
			$idGruppo = $_POST['id'];
			$nuovo_appunti = $_POST['nuovo_appunti'];
			$nuovo_gruppo = $_POST['nuovo_gruppo'];

			$query = "update gruppi set Appunti='$nuovo_appunti', Gruppo = '$nuovo_gruppo'  WHERE id = $idGruppo and idUtente = $idUtente"; 
			$conn->query($query);

		} elseif (isset($_POST['id']) && $_POST['attivaGruppo']==="S") {
			$idGruppo = $_POST['id'];
			
			$query = "update gruppi set Attivo = 'N' where idUser = $idUtente";
			$conn->query($query);
			
			$query = "update gruppi set Attivo = 'S' where idUser = $idUtente and id = $idGruppo";
			$conn->query($query);
			
		} elseif (isset($_POST['aggiungiGruppo']) && $_POST['aggiungiGruppo']==="S") {
			echo '<thead><tr>';
			echo '<th>Nuovo Gruppo</th>';
			echo '</tr></thead>';
			echo '<tbody>';
			echo '<tr><td><label>Nome</label></td></tr>';
			echo '<tr><td><input type="text" name="nome_nuovo_gruppo" id="nome_nuovo_gruppo"></td></tr>';
			echo '<tr><td><label>Diario</label></td></tr>';
			echo '<tr><td><textarea name="diario_nuovo_gruppo" id="diario_nuovo_gruppo"></textarea></td></tr>';
			echo '</tbody>';
			echo '  <tfoot><tr>';
			echo '   <td colspan="3">';
			echo '		<BUTTON title="Torna Indietro" data-action="torna-gruppi">🔙</BUTTON>&nbsp;';
			echo ' 		<BUTTON title="Salva" data-action="salva-nuovo-gruppo">💾</button></td>';
			echo '  </tr></tfoot>';

		} elseif (isset($_POST['salvaNuovoGruppo']) && $_POST['salvaNuovoGruppo']==="S")  {
			$nomeGruppo = mysqli_real_escape_string($conn, $_POST['nomeGruppo']);
			$diarioGruppo = mysqli_real_escape_string($conn, $_POST['diarioGruppo']);
			
			$sql = "INSERT INTO gruppi(idUser, Gruppo, Attivo, Appunti) 
						VALUES ('$idUtente','$nomeGruppo','N','$diarioGruppo' )";
			$conn->query($sql);
			

		} elseif (isset($_POST['eliminaGruppo']) && $_POST['eliminaGruppo']==="S")  {
			$idGruppo = mysqli_real_escape_string($conn, $_POST['id']);
			$sql = "delete from gruppi where id = $idGruppo";
			$conn->query($sql);
			
			$sql = "delete from monete where idGruppo = $idGruppo";
			$conn->query($sql);
			
			
		} else {

			  // Funzione per mostrare i dati della tabella
			$sql = "SELECT *, gruppi.id AS gruppi_id FROM gruppi, utenti where (username = '" .  $loginuser . "' or email = '" . $loginuser . "') and utenti.id = gruppi.idUser ";
			$result = $conn->query($sql);

			echo '<thead><tr>';
			echo '<th></th>';
			echo '<th>Nome</th>';
			echo '<th>Diario</th>';
			echo '<th></th>';
			echo '</tr></thead> <tbody>';

			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					echo '<tr>';
					echo '<td><button title="Attiva Gruppo" data-action="attiva-gruppo" data-id="' . $row["gruppi_id"] . '">ATTIVA</button></td>';
					echo '<td>' . ($row["Attivo"] == "S" ? "✅" : "") . " " . $row["Gruppo"] . '</td>';
					echo '<td><button title="Modifica Diario" data-action="modifica-diario" data-id="' . $row["gruppi_id"] . '">📖</button></td>';
					echo '<td><button title="Elimina Gruppo" data-action="elimina-gruppo" data-id="' . $row["gruppi_id"] . '">🗑️</button></td>';
					echo '</tr>';
				}
			} else {
				//echo $sql;
				echo "Nessun gruppo trovato.";
			}
			echo '</tbody>';
			echo "<tfoot>
                <tr>
                    <td colspan='3'>
                        <div id='fixed-button'>
                            <button title='Aggiungi Gruppo' id='aggiungi-gruppo' data-action='aggiungi-gruppo' class='button-with-image-nuovo-gruppo'> </button>
                        </div>
                    </td>
                </tr>
            </tfoot>";
			//echo '  <tfoot><tr>';
			//echo '   <td colspan="3"><button data-action="aggiungi-gruppo" id="aggiungi-gruppo">Aggiungi Gruppo</button></td>';
			//echo '  </tr></tfoot>';

		}
	} else {
		echo 'errore di autenticazione'; 
	}
	// Chiusura della connessione al database
	$conn->close();
?>
