 <?php
	session_start();
	
	include('dbConfig.php');
	include('funzioni.php');
	
	$conn = new mysqli($servername, $username, $password, $dbname);
	
	// Verifica se l'utente è autenticato, ad esempio controllando la presenza della variabile di sessione 'username'
	if(isset($_SESSION['loginuser'])) {
		// L'utente è autenticato con successo
		$loginuser = $_SESSION['loginuser']; 
		$idUtente = getUserId($conn, $loginuser);

		
		if ($conn->connect_error) {
			die("Connessione al database fallita: " . $conn->connect_error);
		}
		
		
		
		
		
		
		
	} else {
		echo 'errore di autenticazione'; 
	}
	// Chiusura della connessione al database
	$conn->close();
?>
