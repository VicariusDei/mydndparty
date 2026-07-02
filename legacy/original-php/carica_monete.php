<?php
	session_start();

    header("Content-Type: text/html; charset=UTF-8");
    
    include('dbConfig.php');
	include('funzioni.php');



    // Connessione al database
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connessione al database fallita: " . $conn->connect_error);
    }

	if(isset($_SESSION['loginuser'])) {
		// L'utente è autenticato con successo
		$loginuser = $_SESSION['loginuser']; 
		$idUtente = getUserId($conn, $loginuser);
		$gruppoAttivo = getGruppoAttivo($conn, $loginuser);
		

		
		if ($_SERVER["REQUEST_METHOD"] === "GET") {
			if (isset($_GET["aggiungiMonete"]) && $_GET["aggiungiMonete"] === "S") {
				$query = "UPDATE monete SET quantita = quantita + ? WHERE id = ?";

				$stmt = $conn->prepare($query);
				$stmt->bind_param("ii", $_GET["quantita"], $_GET["idMoneta"]);
				$stmt->execute();
				$stmt->close();
			}

			if (isset($_GET["togliMonete"]) && $_GET["togliMonete"] === "S") {
				$query = "UPDATE monete SET quantita = quantita - ? WHERE id = ?";

				$stmt = $conn->prepare($query);
				$stmt->bind_param("ii", $_GET["quantita"], $_GET["idMoneta"]);
				$stmt->execute();
				$stmt->close();
			}

			if (isset($_GET["depositaMonete"]) && $_GET["depositaMonete"] === "S") {
				$query = "UPDATE monete SET quantita = quantita - ?, quantitaDeposito = quantitaDeposito + ? WHERE id = ?";

				$stmt = $conn->prepare($query);
				$stmt->bind_param("iii", $_GET["quantita"], $_GET["quantita"], $_GET["idMoneta"]);
				$stmt->execute();
				$stmt->close();
			}

			if (isset($_GET["prelevaMonete"]) && $_GET["prelevaMonete"] === "S") {
				$query = "UPDATE monete SET quantita = quantita + ?, quantitaDeposito = quantitaDeposito - ? WHERE id = ?";

				$stmt = $conn->prepare($query);
				$stmt->bind_param("iii", $_GET["quantita"], $_GET["quantita"], $_GET["idMoneta"]);
				$stmt->execute();
				$stmt->close();
			}
		}


		$sql = "SELECT 	monete.id as id, 				monete.idMoneta as idMoneta, 					tipoMonete.moneta as moneta, 		
						monete.quantita as quantita, 	monete.quantitaDeposito as quantitaDeposito, 	tipoMonete.rapporto as rapportoOro, tipoMonete.peso as peso
					FROM monete, tipoMonete
					WHERE monete.idMoneta = tipoMonete.id and monete.idGruppo = '$gruppoAttivo'";
		$result = $conn->query($sql);

		echo '<thead>
				<tr>
					<th colspan ="5">TRASPORTATE</th>
				</tr>

				<tr>
					<th>Monete</th>
					<th>Quantità</th>
					<th>In Oro</th>
					<th>Peso</th>
					<th></th>
				</tr>
			</thead>
			<tbody>';

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$quantita = $row["quantita"];
				$color = ($quantita < 0) ? 'style="color: red;"' : ''; // Colora in rosso se la quantità è negativa

				echo '<tr>
						<td>' . $row["moneta"] . '</td>
						<td ' . $color . '>' . $quantita . '</td>
						<td ' . $color . '>' . $quantita * $row["rapportoOro"] . '</td>
						<td ' . $color . '>' . $quantita * $row["peso"] . '</td>
						<td>  
							<button data-action="aggiungiMonete" data-id="' . $row["id"] . '" title="Aggiungere">➕</button>
							<button data-action="togliMonete" data-id="' . $row["id"] . '" title="Sottrarre">➖</button>
							<button data-action="depositaMonete" data-id="' . $row["id"] . '" title="Depositare">➡️</button>
						</td>
					</tr>'; 
			}
		}
		echo '</tbody>';
		
		$sql = "SELECT round(sum(monete.quantita*tipoMonete.rapporto),2) totaleOro, round(sum(monete.quantita*tipoMonete.peso),2) totalePeso 
					FROM monete, tipoMonete
					WHERE monete.idMoneta = tipoMonete.id and monete.idGruppo = '$gruppoAttivo'";
		$result = $conn->query($sql);

		$row = $result->fetch_assoc();
		echo '<tr>'; 
		echo '<td></td>';
		echo '<td></td>';
		echo '<td>' . $row["totaleOro"] . '</td>';
		echo '<td>' . $row["totalePeso"] . 'Kg</td>';
		echo '<td></td>';
		echo '</tr>';


		$sql = "SELECT 	monete.id as id, 				monete.idMoneta as idMoneta, 					tipoMonete.moneta as moneta, 		
						monete.quantita as quantita, 	monete.quantitaDeposito as quantitaDeposito, 	tipoMonete.rapporto as rapportoOro, tipoMonete.peso as peso
					FROM monete, tipoMonete
					WHERE monete.idMoneta = tipoMonete.id and monete.idGruppo = '$gruppoAttivo'";

		$result = $conn->query($sql);

		echo '<thead>
				<tr>
					<th colspan ="5">DEPOSITATE</th>
				</tr>
				<tr>
					<th>Monete</th>
					<th>Quantità</th>
					<th>In Oro</th>
					<th>Peso</th>
					<th></th>
				</tr>
			</thead>
			<tbody>';

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				echo '<tr><td>' . $row["moneta"] . '</td>';
				echo '<td>' . $row["quantitaDeposito"] . '</td>'; 
				echo '<td>' . $row["quantitaDeposito"]*$row["rapportoOro"] . '</td>'; 
				//echo '<td>' . $row["quantita"]*$row["peso"] . '</td>';
				echo '<td></td>';
				echo '<td>  <button data-action="prelevaMonete"     data-id="' . $row["id"] . '"  title="Prelevare">⬅️</button>';
				//echo'       <button data-action="aggiungiMonete"    data-id="' . $row["id"] . '"  title="Aggiungere">➕</button>';
				//echo '      <button data-action="togliMonete"       data-id="' . $row["id"] . '"  title="Sottrarre">➖</button>';
				echo '</td></tr>'; 
			}
		}
		echo '</tbody>';
		$sql = "SELECT round(sum(quantita*rapportoOro),2) totaleOro, round(sum(quantita*peso),2) totalePeso from monete  where deposito='S'";

		$sql = "SELECT 	round(sum(monete.quantitaDeposito*tipoMonete.rapporto),2) totaleOro, 			
						round(sum(monete.quantitaDeposito*tipoMonete.peso),2) totalePeso 
					FROM monete, tipoMonete
					WHERE monete.idMoneta = tipoMonete.id and monete.idGruppo = '$gruppoAttivo'";

		$result = $conn->query($sql);

		$row = $result->fetch_assoc();
		echo '<tr>'; 
		echo '<td></td>';
		echo '<td></td>';
		echo '<td>' . $row["totaleOro"] . '</td>';
		//echo '<td>' . $row["totalePeso"] . 'Kg</td>';
		echo '<td></td>';
		echo '</tr>';  
		
	} else {echo 'problemi di connessione';}

    // Chiusura della connessione al database
    $conn->close();

?>
