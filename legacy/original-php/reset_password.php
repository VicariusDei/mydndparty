<html>
<head>
    <title>mydndparty - Registrazione</title>
    <!-- Stili CSS per la formattazione -->
    <link rel="stylesheet" href="css/login.css" type="text/css">
	
</head>
	
<body>

	<?php 
		include('dbConfig.php');
		$conn = new mysqli($servername, $username, $password, $dbname);
	
		if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['token'])) {
			$token = $_GET['token'];

			// Verifica se il token è valido e non scaduto
			$query = "SELECT idUtente FROM resetPassword WHERE token = '$token' AND timestamp >= NOW() - INTERVAL 1 HOUR";
			$result = $conn->query($query);

			if ($result->num_rows == 1) {
				$row = $result->fetch_assoc();
				?>
					<div class="container">
					   <h1 id="login-title">⚔️ MYDNDPARTY ⚔️</h2>
						<form action="reset_password.php" method="post" class="login-form">
							<h2 id="login-title">Inserisci la  nuova password</h2>
							<input type="hidden" name ="idUtente" id="idUtente" value="<?php echo $row['idUtente'];  ?>">
							<input type="password" id="password" name ="password" placeholder="Password" required class="input-field"><br><br>
							<input type="password" id="confirm_password" name="confirm_password" placeholder="Conferma Password" required class="input-field"><br>
							<div id="password_error" style="color: red;"></div> <!-- Messaggio di errore -->
							
							<input type="submit" value="Recupera" class="login-button">
							</br>
	
						</form>
						<img src="./img/logo.png" alt="Logo" class="logo">
					</div>

	<?php
			} else {
				// Token non valido o scaduto
				// Mostra un messaggio di errore all'utente
			}
		} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
			// Ricevi i dati dal modulo
			$idUtente = $_POST['idUtente'];
			$password = $_POST['password'];
			$confirm_password = $_POST['confirm_password'];

			// Verifica se le password coincidono
			if ($password === $confirm_password) {
				// Le password coincidono, puoi procedere con il reset della password
				// Esegui il codice per aggiornare la password nel database
				$hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash della nuova password

				// Esegui l'aggiornamento nel database utilizzando l'idUtente
				$update_query = "UPDATE utenti SET password = '$hashed_password' WHERE id = $idUtente";
				if ($conn->query($update_query) === TRUE) {
			
					$delete_query = "delete from resetPassword WHERE idUtente = $idUtente";
					$conn->query($update_query);
					$conn->close();
					header("Location: index.php?resetPassword=OK"); // Reindirizza alla pagina di autenticazione
					exit();
				} else {
					//echo $update_query . ' error';;
					$conn->close();
					header("Location: index.php?resetPassword=ERROR"); // Reindirizza alla pagina di autenticazione
					exit();
				}
			}
		}

		$conn->close();
?>


    
</body>
</html>