<?php 
	session_start();
    include('dbConfig.php');

    // Connessione al database
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connessione al database fallita: " . $conn->connect_error);
    }

    $modal_message = ""; // Inizializza la variabile per il messaggio della modale

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Recupero dei dati dal modulo di registrazione
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $conferma_password = $_POST['conferma_password'];
		$validata = 'N';
		
        // Verifica se le password coincidono
        if ($password !== $conferma_password) {
            $modal_message = "Le password non coincidono. Riprova.";
        } else {
            // Verifica se lo username o l'email esistono già nel database
            $query_check = "SELECT username, email FROM utenti WHERE username = ? OR email = ?";
            $stmt_check = $conn->prepare($query_check);
            
            if ($stmt_check) {
                $stmt_check->bind_param("ss", $nome, $email);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows > 0) {
                    $modal_message = "Username o email già in uso. Si prega di sceglierne altri.";
                } else {
                    // Esegui l'hash della password prima di salvarla nel database
                    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                    $currentDateTime = date('Y-m-d H:i:s');
                    
					$token = uniqid(); // Genera un token univoco
					$activationLink = "https://www.friabili.it/index.php?attivaProfilo=S&token=" . $token; // Costruisci il link di attivazione	
					
                    // Utilizzo di un prepared statement per l'inserimento sicuro dei dati
                    $query_insert = "INSERT INTO utenti (username, email, password, validata, created_at, token, admin) VALUES (?, ?, ?, ?, ?, ?, 0)";
                    $stmt_insert = $conn->prepare($query_insert);
                    
                    if ($stmt_insert) {
                        // Binding dei parametri e esecuzione del prepared statement
                        $stmt_insert->bind_param("ssssss", $nome, $email, $password_hashed, $validata, $currentDateTime, $token);
                        $stmt_insert->execute();
												
						$destinatario = $email;
						$oggetto = "Registrazione mydndparty";
						$corpo_email = "ciao " . $nome . " e benvenuto in mydndparty! Ecco il link per l'attivazione " . $activationLink;

						// Imposta intestazioni aggiuntive
						$headers = "From: mydndparty@friabili.it\r\n";
						$headers .= "Reply-To: mydndparty@friabili.it\r\n";
						$headers .= "X-Mailer: PHP/" . phpversion();
						//$headers .= "Bcc: davidecool@gmail.com"; // Aggiungi l'indirizzo email di copia nascosta
						
						// Invia l'email
						if (mail($destinatario, $oggetto, $corpo_email, $headers)) {
							echo "Email inviata con successo!";
						} else {
							echo "Errore nell'invio dell'email.";
						}
						
						$destinatario = 'davidecool@gmail.com';
						$oggetto = "Nuova registrazione mydndparty";
						$corpo_email = "Ciao, un nuovo utente ($nome) si è registrato su mydndparty. Bye!";			
						mail($destinatario, $oggetto, $corpo_email, $headers);
							
                        //$_SESSION['username'] = $nome; // Salva l'username in sessione
                        header("Location: index.php?reg=OK"); // Reindirizza alla pagina di autenticazione
                        exit();
                        
                    } else {
                        $modal_message = "Errore nella preparazione della query di inserimento.";
                    }
                    
                    // Chiudi il prepared statement di inserimento
                    $stmt_insert->close();
                }
                // Chiudi il prepared statement di controllo
                $stmt_check->close();
            } else {
                $modal_message = "Errore nella preparazione della query di controllo.";
            }
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
       <h1 id="login-title">⚔️ MYDNDPARTY ⚔️</h2>
        <form action="registrazione.php" method="post" class="login-form">
            <h2 id="login-title">Registrazione</h2>
            <input type="text" name="nome" placeholder="Nome" required class="input-field"><br><br>
            <input type="email" name="email" placeholder="Email" required class="input-field"><br><br>
            <input type="password" name="password" placeholder="Password" required class="input-field"><br><br>
            <input type="password" name="conferma_password" placeholder="Conferma Password" required class="input-field"><br><br>

            <input type="submit" value="Registrati" class="login-button">
            </br>
            <?php if ($modal_message!="" ) {echo $modal_message;} ?>
        </form>
        <img src="./img/logo.png" alt="Logo" class="logo">
    </div>

</body>
</html>



