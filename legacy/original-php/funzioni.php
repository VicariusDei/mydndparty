<?php

function getUserId($conn, $loginuser) {
    $sql = "SELECT id FROM utenti WHERE (username = ? OR email = ?)";
    
    // Preparare la query
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Errore nella preparazione della query: " . $conn->error);
    }
    
    // Bind dei parametri
    $stmt->bind_param("ss", $loginuser, $loginuser);
    
    // Eseguire la query
    if (!$stmt->execute()) {
        die("Errore nell'esecuzione della query: " . $stmt->error);
    }
    
    // Ottieni il risultato della query
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["id"];
    } else {
        return null;
    }
}



function getGruppoAttivo($conn, $loginuser) {
    $sql = "SELECT gruppi.id as result 
            FROM gruppi
            INNER JOIN utenti ON gruppi.idUser = utenti.id
            WHERE (utenti.username = ? OR utenti.email = ?) AND gruppi.Attivo = 'S'";
    
    // Preparare la query
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return null;
    }
    
    // Bind dei parametri
    $stmt->bind_param("ss", $loginuser, $loginuser);
    
    // Eseguire la query
    if (!$stmt->execute()) {
        $stmt->close(); // Chiudere lo statement prima di uscire
        return null;
    }
    
    // Ottieni il risultato della query
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close(); // Chiudere lo statement prima di uscire
        return $row["result"];
    } else {
        $stmt->close(); // Chiudere lo statement prima di uscire
        return null;
    }
}


function getDadoPredefinito($conn, $loginuser) {
    $sql = "SELECT cfgUtenti.dado as result
            	FROM cfgUtenti
            	INNER JOIN utenti ON cfgUtenti.idUtente = utenti.id
            	WHERE (utenti.username = ? OR utenti.email = ?)";
    
    // Preparare la query
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return null;
    }
    
    // Bind dei parametri
    $stmt->bind_param("ss", $loginuser, $loginuser);
    
    // Eseguire la query
    if (!$stmt->execute()) {
        $stmt->close(); // Chiudere lo statement prima di uscire
        return null;
    }
    
    // Ottieni il risultato della query
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close(); // Chiudere lo statement prima di uscire
        return $row["result"];
    } else {
        $stmt->close(); // Chiudere lo statement prima di uscire
        return null;
    }
}



function logStatement($conn, $fileName, $message, $severity, $userId = null) {
    // Esegui l'echo del messaggio
    //echo $message . PHP_EOL; // PHP_EOL inserisce un newline
	
  
    // Inserisci il messaggio di log nel database
    if (!empty($message)) {
        // Escapa il messaggio per evitare SQL Injection
        $escapedMessage = mysqli_real_escape_string($conn, $message);

        // Ottieni l'ID dell'utente corrente (se disponibile)
        $user_id = ($userId !== 0) ? intval($userId) : null;

        // Prepara la query di inserimento
        $query = "INSERT INTO log (message, timestamp, fileName, severity, user_id) VALUES ('$escapedMessage', NOW(), '$fileName', $severity, $user_id)";

        // Esegui la query di inserimento
        if ($conn->query($query) === TRUE) {
            // Inserimento riuscito
            return true;
        } else {
            // Errore nell'inserimento
            //echo "Errore nell'inserimento del log: " . $conn->error . PHP_EOL;
            return false;
        }
    }

    return false;
}



function getData($conn, $loginuser) {
    // Prima query per ottenere l'ID del gruppo attivo
    $sqlGruppoAttivo = "SELECT gruppi.id as id 
                        FROM gruppi
                        INNER JOIN utenti ON gruppi.idUser = utenti.id
                        WHERE (utenti.username = ? OR utenti.email = ?) AND gruppi.Attivo = 'S'";

    // Seconda query per ottenere il dado predefinito
    $sqlDadoPredefinito = "SELECT cfgUtenti.dado as result
                           FROM cfgUtenti
                           INNER JOIN utenti ON cfgUtenti.idUser = utenti.id
                           WHERE (utenti.username = ? OR utenti.email = ?)";

    // Preparazione e esecuzione della prima query
    $stmt = $conn->prepare($sqlGruppoAttivo);
    if (!$stmt || !$stmt->bind_param("ss", $loginuser, $loginuser) || !$stmt->execute()) {
        return null; // Gestire errore
    }
    $resultGruppo = $stmt->get_result();
    $idGruppo = ($resultGruppo->num_rows > 0) ? $resultGruppo->fetch_assoc()["id"] : null;
    $stmt->close();

    // Preparazione e esecuzione della seconda query
    $stmt = $conn->prepare($sqlDadoPredefinito);
    if (!$stmt || !$stmt->bind_param("ss", $loginuser, $loginuser) || !$stmt->execute()) {
        return null; // Gestire errore
    }
    $resultDado = $stmt->get_result();
    $dadoPredefinito = ($resultDado->num_rows > 0) ? $resultDado->fetch_assoc()["result"] : null;
    $stmt->close();

    // Ritornare entrambi i risultati in un array associativo
    return [
        "idGruppo" => $idGruppo,
        "dadoPredefinito" => $dadoPredefinito
    ];
}





?>    