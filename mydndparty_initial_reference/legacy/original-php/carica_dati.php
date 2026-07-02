 <?php
	session_start();
    //header("Content-Type: text/html; charset=UTF-8");
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
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

		if (isset($_GET['eliminaOggetto']) && $_GET['eliminaOggetto']==="S") {
			$idOggetto = $_GET["idOggetto"];

			$query = "DELETE FROM inventario WHERE idUtente = $idUtente and id = $idOggetto";
			$conn->query($query);

		}

		if (isset($_GET['salvaModificaOggetto']) && $_GET['salvaModificaOggetto']==="S") {
			$idOggetto = $_GET["idOggetto"];
			$nome = $_GET["nome"];
			$identificato = $_GET["identificato"];
			$quantita = $_GET["quantita"];
			$valore =  $_GET["valore"];
			$categoria = $_GET["categoria"];
			$note = $_GET["note"];

			$query = "update inventario set des='$nome', ide ='$identificato', qta=$quantita, val=$valore, note = '$note', categoria = '$categoria' WHERE id = $idOggetto and idUtente = $idUtente"; 
			$conn->query($query);
			

		}

		if (isset($_GET['salvaNuovoOggetto']) && $_GET['salvaNuovoOggetto']==="S") {
			$nome = $_GET["nome"];
			$quantita = $_GET["quantita"];
			$identificato = $_GET["identificato"];
			$valore =  $_GET["valore"];
			$categoria = $_GET["categoria"];
			$note = $_GET["note"];

			// Query per l'inserimento dell'oggetto
			//$query = "insert into inventario (des, ide, qta, val, categoria, note, idUtente) VALUES ('$nome', '$identificato', '$quantita', '$valore','$categoria', '$note', $idUtente);"; 
			$query = "INSERT INTO inventario (des, ide, qta, val, categoria, note, idUtente) VALUES (?, ?, ?, ?, ?, ?, ?)";
			//$stmt = $pdo->prepare($query);
			//$stmt->execute([$nome, $identificato, $quantita, $valore, $categoria, $note, $idUtente]);

			$stmt = $conn->prepare($query);
    		$stmt->bind_param('ssisssi', $nome, $identificato, $quantita, $valore, $categoria, $note, $idUtente);
     		$stmt->execute();
			
			//$conn->query($query);

		}

		/*---------------------NUOVO OGGETTO------------------------------------*/

		if (isset($_GET['nuovoOggetto']) && $_GET['nuovoOggetto']==="S") 
		{
			echo '
			<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    input, select, textarea {
    width: 100%;
    text-align: left; /* Allinea il testo a sinistra */
}

td {
    padding: 8px;
    vertical-align: middle;
}

label {
    display: inline-block;
    width: 150px; /* imposta una larghezza fissa per allineare verticalmente */
    text-align: right;
    padding-right: 10px;
}
</style>

			<table>
    <thead>
        <tr>
            <th colspan="2">Inserisci Oggetto</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <label for="nome">Nome:</label>
            </td>
            <td>
                <input type="text" id="nome" required>
            </td>
        </tr>
        <tr>
            <td>
                <label for="identificato">Identificato:</label>
            </td>
            <td>
                <select id="identificato" name="identificato" class="modal-combobox">
                    <option value="SI">SI</option>
                    <option value="NO">NO</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <label for="quantita">Qta:</label>
            </td>
            <td>
                <input type="number" id="quantita" required>
            </td>
        </tr>
        <tr>
            <td>
                <label for="valore">Valore:</label>
            </td>
            <td>
                <input type="number" id="valore" required>
            </td>
        </tr>
        <tr>
            <td>
                <label for="categoria">Categoria:</label>
            </td>
            <td>
                <select id="categoria" class="modal-combobox">
                    <option value="Armi">Armi</option>
                    <option value="Armature">Armature</option>
                    <option value="Pozioni">Pozioni</option>
                    <option value="Pergamene">Pergamene</option>
                    <option value="Tesori">Tesori</option>
                    <option value="Varie">Varie</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <label for="note">Note o Effetti:</label>
            </td>
            <td>
                <textarea id="note"></textarea>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="text-align:center;">
                <button data-action="annulla-aggiungi-oggetto">Annulla</button> &nbsp;
                <button data-action="salva-nuovo-oggetto">Salva</button>
            </td>
        </tr>
    </tfoot>
</table>
<thead>';



		}


		/*---------------------MODIFICA OGGETTO------------------------------------*/

			elseif (isset($_GET['modificaOggetto']) && $_GET['modificaOggetto']==="S") 
		{ 
			$idOggetto = $_GET['idOggetto'];
			// Query per eliminare l'oggetto
			$query = "select * FROM inventario WHERE idUtente = $idUtente and id = $idOggetto"; 
			$result = $conn->query($query);

			if ($result->num_rows > 0){
				// Genera HTML tabellare con i dati
				$row = $result->fetch_assoc();
				$des = $row['des'];
				$qta = $row['qta'];
				$val = $row['val'];
				$ide = $row['ide'];
				$note =  $row['note'];
				$categoria =  $row['categoria'];
			}



	echo '<thead>
				<tr>
					<th>Modifica Oggetto</th>
				</tr>
			</thead>
		 <tr>
			   <td> 

					<input type="hidden" id="modifica_oggettoId" value="'. $idOggetto . '">
					<label for="nuovo_nome">Descrizione:</label>
					<input type="text" name="nuovo_nome" id ="nuovo_nome" value="'.$des.'" placeholder="Nome" required>
				</td>
			</tr>
					<tr>
				<td>        
					<label for="nuovo_ide">Identificato:</label>
					<select name="nuovo_ide" id="nuovo_ide" class="modal-combobox">
							<option value="SI" ' . ($ide === "SI" ? "selected" : "") . '>SI</option>
							<option value="NO" ' . ($ide === "NO" ? "selected" : "") . '>NO</option>
					</select>
			   </td>
			</tr>
			<tr>
				<td>         
					<label for="nuova_qta">Quantità:</label>
					<input type="number" name="nuova_qta" id="nuova_qta" value="'.$qta.'" placeholder="Nuova Quantità" required>
				</td>
			</tr>
			 <tr>
				<td> 
					<label for="nuovo_val">Valore:</label>
					<input type="number" name="nuovo_val" id="nuovo_val" value="'.$val.'" placeholder="Nuovo Valore" required>
			   </td>
			</tr>

			<tr>
				<td> 
					<label for="nuova_categoria">Categoria:</label>
					<select name="nuova_categoria" id="nuova_categoria" class="modal-combobox">
						<option value="Armi" ' . ($categoria === "Armi" ? "selected" : "") . '>Armi</option>
						<option value="Armature" ' . ($categoria === "Armature" ? "selected" : "") . '>Armature</option>
						<option value="Pozioni" ' . ($categoria === "Pozioni" ? "selected" : "") . '>Pozioni</option>
						<option value="Pergamene" ' . ($categoria === "Pergamene" ? "selected" : "") . '>Pergamene</option>
						<option value="Tesori" ' . ($categoria === "Tesori" ? "selected" : "") . '>Tesori</option>
						<option value="Varie" ' . ($categoria === "Varie" ? "selected" : "") . '>Varie</option>
					</select>
			   </td>
			</tr>
				 <tr>
				<td> 
					<label for="nuove_note">Note o Effetti:</label>
					<textarea name="nuove_note" id="nuove_note" placeholder="Nuove Note">'.$note.'
					</textarea>
			   </td>
			</tr>
			<tr>
				<td> 

					<BUTTON data-action="annulla-modifica-oggetto">🔙</BUTTON>       
					<BUTTON data-action="salva-modifica-oggetto">💾</BUTTON>

				</td>
			</tr>';

		} else {

		$query = "SELECT * FROM inventario where idUtente = $idUtente"; 

		$result = $conn->query($query);
		echo '  <thead>

				<tr class="categorieOggetti"><td colspan="3"><div id="category-filter">
				<div class="table-container">
					<div class="table-cell">
						<label ><input type="checkbox" class="hidden-checkbox" id="categoria-Armi" onclick="filtraCategorie()">
						<img src="img/armi.png" class="iconaMedia"></label>
					</div> 
					<div class="table-cell">
						 <label><input type="checkbox" class="hidden-checkbox" id="categoria-Armature" onclick="filtraCategorie()">
						<img src="img/armature.png" class="iconaMedia"></label>
					</div>
					<div class="table-cell">
						 <label><input type="checkbox" class="hidden-checkbox" id="categoria-Pozioni" onclick="filtraCategorie()">
						<img src="img/pozioni.png" class="iconaMedia"></label>
					</div>
					<div class="table-cell">
						 <label><input type="checkbox" class="hidden-checkbox" id="categoria-Pergamene" onclick="filtraCategorie()">
						<img src="img/pergamene.png" class="iconaMedia"></label>
					</div>
					<div class="table-cell">
						 <label><input type="checkbox" class="hidden-checkbox" id="categoria-Tesori" onclick="filtraCategorie()">
						<img src="img/tesori.png" class="iconaMedia"></label>
					</div>
					<div class="table-cell">
						 <label><input type="checkbox" class="hidden-checkbox" id="categoria-Varie" onclick="filtraCategorie()">
						<img src="img/varie.png" class="iconaMedia"></label>
					</div>
				</div>
			</div></td></tr>
					<tr>
						<th>Oggetto</th>
						<th class="categoria">Categoria</th>
						<th></th>
					</tr>
				</thead> 
			<tbody>  ';

		if ($result->num_rows > 0) {
			// Genera HTML tabellare con i dati
			while ($row = $result->fetch_assoc()) {
				echo "<tr>";
				echo "	<td style='text-align:left;'>";
				echo '		<img data-des="'.$row["des"].'" data-action="mostraNote" data-id="'. $row["note"] .'" style="max-width: 100%; max-height: 100%; vertical-align: middle;" src="' . ($row["ide"] == "SI" ? './img/Identificato.png' : './img/nonIdentificato.png') . '" class="icona" alt="' . ($row["ide"] == "SI" ? 'Immagine identificato' : 'Immagine non identificato') . '">';
				echo "		<strong>".$row["des"]."</strong><br>"; 
				echo "		Quantità: <strong>" . $row["qta"] . "</strong>";
				echo "	</td>";

				echo "	<td class='categoria'><strong>" . $row["categoria"] . "</strong></td>";

				echo '	<td>';
				echo '		<button data-action="modifica-oggetto" data-id="' . $row["id"] . '">✏️</button>&nbsp;';
				echo '		<button data-action="elimina-oggetto" data-id="' . $row["id"] . '">🗑️</button>';
				echo '	</td>';
				echo '</tr>';


			}
		} else {
			echo "<tr><td colspan='3'>Nessun dato trovato.</td></tr>";
		}
		echo '</tbody>
				<div id="fixed-button">
					<button id="nuovo-oggetto" data-action="nuovo-oggetto" class="button-with-image-nuovo-oggetto"> </button>
				</div>';
		}  
	} else 
	{ echo 'problemi di connessione'; }
    
	$conn->close();
?>

