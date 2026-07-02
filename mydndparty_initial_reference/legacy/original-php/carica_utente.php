<?php
session_start();
// Include il file di configurazione del database e le funzioni di autenticazione
include('dbConfig.php');
include('funzioni.php');
$conn = new mysqli($servername, $username, $password, $dbname);

// Controlla se l'utente è autenticato
if (isset($_SESSION['loginuser'])) {
// Ottieni l'ID dell'utente autenticato
	$loginuser = $_SESSION['loginuser']; 
	$idUtente = getUserId($conn, $loginuser);
	
	$sql = 'SELECT * 
			FROM utenti, cfgUtenti, cfgSistema, cfgLingua, dadoIniziativa 
			WHERE utenti.id = cfgUtenti.idUtente and cfgUtenti.dado = dadoIniziativa.id and cfgUtenti.lingua = cfgLingua.id and cfgUtenti.sistema = cfgSistema.id and utenti.id = ?';
	
	?>

		<?php if (isset($errorMessage)) : ?>
			<div class="alert alert-danger"><?php echo $errorMessage; ?></div>
		<?php endif; ?>

		<?php if (isset($successMessage)) : ?>
			<div class="alert alert-success"><?php echo $successMessage; ?></div>
		<?php endif; ?>

		<p>Benvenuto, <?php echo $loginuser; ?>!</p>
		<p>attenzione, la pagina è in costruzione! Puoi configurare i tuoi parametri</p>
/		Sistema di gioco

		dado iniziativa

		lingua


		<!-- Modulo per il cambio password -->
		<div>Cambio Password</div>
		<form method="post" action="">
			<div class="form-group">
				<label for="new_password">Nuova Password</label>
				<input type="password" name="new_password" id="new_password" class="form-control" required>
			</div>
			<button type="submit" name="change_password" class="btn btn-primary">Cambia Password</button>
		</form>

		<!-- Modulo per la cancellazione dell'account utente -->
		<div>Cancellazione dell'Account Utente</div>
		<p>Attenzione: La cancellazione dell'account utente è irreversibile.</p>
		<form method="post" action="">
			<button type="submit" name="delete_account" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler cancellare il tuo account?')">Cancella Account</button>
		</form>
	</div>

	</body>
	</html>

<?php
	} else {
		header("Location: login.php"); // Reindirizza l'utente alla pagina di login se non è autenticato
		exit();
}
?>