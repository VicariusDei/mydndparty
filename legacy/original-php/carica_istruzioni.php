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
		// L'utente è autenticato con successo
		$loginuser = $_SESSION['loginuser']; 
		$idUtente = getUserId($conn, $loginuser);
		
		if ($conn->connect_error) {
			die("Connessione al database fallita: " . $conn->connect_error);
		}
    
	 	if ($_SERVER["REQUEST_METHOD"] == "POST") {
			// Processa i dati del modulo di contatto qui
			$name = $_POST['name'];
			$email = $_POST['email'];
			$message = $_POST['message'];

			$destinatario = 'davidecool@gmail.com';
			$oggetto = "Messaggio da mydndparty";
			$corpo_email = "Ciao! " . $name . " ti ha inviato un messaggio su mydndparty: " . $message;

			// Imposta intestazioni aggiuntive
			$headers = "From: mydndparty@friabili.it\r\n";
			$headers .= "Reply-To: davidecool@gmail.com\r\n";
			$headers .= "X-Mailer: PHP/" . phpversion();
			
			
			$headers = "From: mydndparty <mydndparty@friabili.it>\r\n";
			$headers .= "Reply-To: mydndparty@friabili.it\r\n";
			$headers .= "Return-Path: mydndparty@friabili.it\r\n";
			$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			$headers .= "X-Priority: 1\r\n"; // Imposta la priorità dell'email

			// Configura SPF (Sender Policy Framework) se possibile (sostituisci il dominio con il tuo)
			$headers .= "Received-SPF: pass (friabili.it: domain of mydndparty@friabili.it designates 89.46.105.27 as permitted sender) client-ip=89.46.105.27;\r\n";


			// Aggiungi header per prevenire il phishing (DMARC) se possibile (sostituisci il dominio con il tuo)
			$headers .= "Authentication-Results: friabili.it; dmarc=pass header.from=mydndparty@friabili.it\r\n";
			
			
			
			// Invia l'email
			if (mail($destinatario, $oggetto, $corpo_email, $headers)) {
				echo "Email inviata con successo!";
			} else {
				echo "Errore nell'invio dell'email.";
			}
				
		}
		
    	// ---------------------------- ELIMINA PERSONAGGIO
	   	if (isset($_GET['link-istruzioni']) && $_GET['link-istruzioni']==="S") {
		

		echo "

			<h1>Istruzioni per l'Avventura Epica</h1>
			<div>Benvenuti, eroi e narratori, nel regno di MYDNDPARTY! Qui, dove le epiche avventure prendono vita, ci immergiamo in un mondo dove il gioco di ruolo è re, tessuto di sangue, dadi e miniature. Ma temete non! Nel nostro calabrone di storie e battaglie, la tecnologia è il nostro fedele scudiero, che sciolglie i nodi del destino con un tocco digitale, trasformando attese in momenti di gloriosa strategia.
				</br>
		Con MYDNDPARTY, diventa maestro del tuo racconto: traccia rotte inesplorate nel tuo diario di bordo, gestisci con un soffio il vasto arsenale nel tuo inventario e conta le ricchezze accumulate in innumerevoli quest. E quando la lama del destino pendola in bilico, comanda l'incertezza e l'entusiasmo con una gestione dinamica dell'iniziativa, rendendo ogni duello un ballo mozzafiato tra eroi e nemici.
		</br>
		Siamo al lancio del nostro vessillo in questa alpha avventura! Il nostro gruppo di coraggiosi pionieri è già sul campo, forgiano e affinano questo mondo con ogni impresa. E voi, arditi viaggiatori, siete invitati a unirvi a questa costruzione epica. Se il cuore vi arde di contribuire a questo tapestry di fantasia, le porte sono spalancate: contattatemi attraverso questa form o evocate il mio spirito via telegram @davideconqualcosa.
		</br>
		Che i dadi siano sempre a vostro favore, avventurieri di MYDNDPARTY! Avanti verso l'orizzonte delle possibilità!</div>
			<ol>
				<li><strong>Crea i tuoi gruppi avventurieri</strong> e dai un nome alla combriccola.</li>
				<li><strong>Crea i personaggi</strong> e abbinali ai gruppi creati.</li>
				<li><strong>Crea gli oggetti e l'equipaggiamento</strong> per i tuoi personaggi.</li>
				<li><strong>Inizializza le monete</strong> per ogni gruppo.</li>
				<li><strong>COMBATTI!</strong> Metti alla prova le tue squadre in epiche battaglie.</li>
			</ol>
		"; 
				} elseif (isset($_GET['link-formContatti']) && $_GET['link-formContatti']==="S") {

		echo '
				
					<label for="name">Nome:</label>
					<input type="text" id="name" name="name" required>

					<label for="email">Email:</label>
					<input type="email" id="email" name="email" required>

					<label for="message">Messaggio:</label>
					<textarea id="message" name="message" required></textarea>

					<!-- Qui verrà inserito il CAPTCHA -->
					
					</br>
					<button id="inviaFormContatto" data-action="inviaFormContatto">INVIA</button>
					
			
			';		
		} elseif (isset($_GET['link-impostazioni']) && $_GET['link-impostazioni']==="S") {

		echo '
					<label for="name">Nome:</label>
					<input type="text" id="name" name="name" required>

					<label for="email">Email:</label>
					<input type="email" id="email" name="email" required>

					<label for="message">Messaggio:</label>
					<textarea id="message" name="message" required></textarea>

					<!-- Qui verrà inserito il CAPTCHA -->
					
					</br>
					<button id="inviaFormContatto" data-action="inviaFormContatto">INVIA</button>

			';		
		}
}