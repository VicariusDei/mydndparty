<?php
   	ob_start();
   	session_start();
	include('dbConfig.php');
	$conn = new mysqli($servername, $username, $password, $dbname);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>My Dnd Party</title>
    <!-- Stili CSS opzionali per la formattazione 
    <link rel="stylesheet" href="css\styles.css"    type="text/css" >-->
    <link rel="stylesheet" href="css\login.css" type="text/css" >
</head>


<body>
	<?php echo $_GET['attivaProfilo']; ?>
    <div class="container">
       <h1 id="login-title">⚔️ MYDNDPARTY ⚔️</h2>
        <form action="process_login.php" method="post" class="login-form">
            <h2 id="login-title">Login</h2>
            <label for="username" id="username-label">Username o Email:</label>
            <input type="text" id="username" name="username" required class="input-field">
            <br>
            <label for="password" id="password-label">Password:</label>
            <input type="password" id="password" name="password" required class="input-field" autocomplete="current-password">
            <input type="submit" value="Login" class="login-button">
			<a href="registrazione.php" class="registrati-button">Registrati</a> <br>
			<a href="recupero_password.php" class="recupero-button">Recupera Password</a> <br>
			<?php
				if (isset($_GET['badLogin']) && $_GET['badLogin']==="S") {
					echo 'login errata, riprova';
    			}
			
				if (isset($_GET['reg']) && $_GET['reg']==="OK") {
					echo 'registrazione avvenuta, controlla la mail per attivare il profilo';
    			}
				
				if (isset($_GET['furbetto']) && $_GET['furbetto']==="S") {
					echo 'prova di raggirare fallita! effettua il login';
    			}
		
				if (isset($_GET['mailRecuperoPassword']) && $_GET['mailRecuperoPassword']==="OK") {
					echo 'controlla la tua mail!';
    			} elseif ($_GET['mailRecuperoPassword']==="ERROR") {
					echo "grossi problemi con l'invio della mail, io desisterei";
				}
				
				if (isset($_GET['resetPassword']) && $_GET['resetPassword']==="OK") {
					echo 'Cambio password effettuato';
    			} elseif ($_GET['resetPassword']==="ERROR") {				
					echo 'Qualcosa è andato storto, peccato';
    			}
			
				if (isset($_GET['attivaProfilo']) && $_GET['attivaProfilo']==="S") {
					$token = $_GET["token"];

					$query = "update utenti set validata = 'S' where token='" . $token . "'";
					$result = $conn->query($query);

					if ($result === TRUE && $conn->affected_rows > 0) {
						echo "Prova di intelligenza superata, attivazione eseguita! Puoi effettuare il login.";
					} else {
						echo "Problemi di attivazione, fallimento critico!";
						}
					}
					if (isset($_GET['logout']) && $_GET['logout']==="S") {
						echo 'ritirata impietosa, logout effettuato!';
						$_SESSION = array();
						session_destroy();
    				}

			?>
        </form>
        <img src="./img/logo.png" alt="Logo" class="logo">
    </div>




</body>
</html>