<?php

// Connessione al database (esempio)
include('dbConfig.php');
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica della connessione al database
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Verifica se il modulo di login è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ottieni i valori inseriti dall'utente
    $loginuser = $_POST['username'];
    $password = $_POST['password'];

    // Esegui la query per cercare l'utente nel database
    $query = "SELECT * FROM utenti WHERE username = '$loginuser' OR email = '$loginuser' and validata = 'S'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // Utente trovato, verifica la password
        $row = $result->fetch_assoc();
        $stored_password = $row['password']; // Supponendo che la password sia memorizzata nel campo 'password'
        
        // Verifica la password inserita con quella memorizzata nel database
        if (password_verify($password, $stored_password)) {
            // Password corretta, autenticazione riuscita
            session_start();
            $_SESSION['loginuser'] = $loginuser; // Salva l'username in sessione
            header("Location: auth.php"); // Reindirizza alla pagina di autenticazione
            exit();

        } else {
            // Password errata
            //echo "Credenziali errate. Riprova.";
			header("Location: index.php?badLogin=S");
        }
    } else {
        // Utente non trovato
        //echo "Utente non trovato. Riprova.";
		header("Location: index.php?badLogin=S");
    }
}

$conn->close();
?>
