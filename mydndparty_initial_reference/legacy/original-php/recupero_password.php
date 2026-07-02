<?php 
	session_start();
    include('dbConfig.php');

    // Connessione al database
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connessione al database fallita: " . $conn->connect_error);
    }

	// verifico se l'utente esiste. aggiorno il token temporaneo e mando la mail di recupero
	if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Recupero dei dati dal modulo di registrazione
        $nome = $_POST['nome'];
		
        $query_check = "SELECT id, username, email, token FROM utenti WHERE username = ? OR email = ?";
        $stmt_check = $conn->prepare($query_check);
		
		if ($stmt_check) {
            $stmt_check->bind_param("ss", $nome, $nome);
            $stmt_check->execute();
            $stmt_check->store_result();
 			
			if ($stmt_check->num_rows == 1) {
				$stmt_check->bind_result($id, $username, $email, $token);
				$stmt_check->fetch();
				
				$uniqueToken  = uniqid(); // Genera un token univoco
				$activationLink = "https://www.friabili.it/reset_password.php?recupero=S&token=" . $uniqueToken; // Costruisci il link di attivazione	
					
				$query_insert = "INSERT INTO resetPassword (idUtente, timestamp, token) VALUES (?, NOW(), ?)";
				$stmt_insert = $conn->prepare($query_insert);

				if ($stmt_insert) {
					//echo $id;
					//echo "</br>";
					//echo $uniqueToken;
					// Binding dei parametri e esecuzione del prepared statement
					$stmt_insert->bind_param("is", $id, $uniqueToken);
					$stmt_insert->execute();

					$destinatario = $email;
					$oggetto = "Recupero password mydndparty";
					$corpo_email = "Ciao " . $nome . ", hai fallito il tiro salvezza su memoria eh...! Ecco il link per il recupero della password " . $activationLink . " per la prossima ora sarà funzionante, dopodichè dovrai effettuare un nuovo recupero.";

					// Imposta intestazioni aggiuntive
					$headers = "From: mydndparty@friabili.it\r\n";
					$headers .= "Reply-To: mydndparty@friabili.it\r\n";
					$headers .= "X-Mailer: PHP/" . phpversion();

					$stmt_check->close();
					$conn->close();
					// Invia l'email
					if (mail($destinatario, $oggetto, $corpo_email, $headers)) {
						echo "Email inviata con successo!";
						header("Location: index.php?mailRecuperoPassword=OK"); // Reindirizza alla pagina di autenticazione
						exit();
					} else {
						echo "Errore nell'invio dell'email.";
						header("Location: index.php?mailRecuperoPassword=ERROR"); // Reindirizza alla pagina di autenticazione
						exit();
					}
					
					
				}
		
			}
			$stmt_check->close();
		}
	}
	

    $conn->close();
?>

<html>
<head>
    <title>mydndparty - Registrazione</title>
    <!-- Stili CSS per la formattazione -->
    <link rel="stylesheet" href="css\login.css" type="text/css">
</head>
<body>
    <div class="container">
       <h1 id="login-title">⚔️ MYDNDPARTY ⚔️</h1>
        <form action="recupero_password.php" method="post" class="login-form">
            <h2 id="login-title">Recupero passoword</h2>
			<h3 id="login-title">Inserisci nome utente o mail</h3>
            <input type="text" name="nome" placeholder="Nome o email" required class="input-field"><br><br>
            <input type="submit" value="Recupera" class="login-button">
            </br>
        </form>
        <img src="./img/logo.png" alt="Logo" class="logo">
    </div>

</body>
</html>