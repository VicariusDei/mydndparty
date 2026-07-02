document.addEventListener("DOMContentLoaded", function () {

	document.addEventListener("click", function (event) {
		var e = event.target;
		var id, dadoNew, a, xhr;
		
		if (e && e.tagName === "BUTTON" && e.getAttribute("data-action") === "salvaUtente") {
			id = e.getAttribute("data-id");
			dadoNew = document.getElementById("dadoNew").value;
			a = "carica_utente.php?salvaUtente=S&id=" + id + "&dadoNew=" + dadoNew;
			xhr = new XMLHttpRequest();
            xhr.open("GET", a, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {

                    document.getElementById("table-utente").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
		} 
		
		if (e && e.tagName === "BUTTON" && e.getAttribute("data-action") === "annullaSalvaUtente") {
			caricaUtente();
		} 
		
		if (e && e.tagName === "BUTTON" && e.getAttribute("data-action") === "modificaUtente") {
			id = e.getAttribute("data-id");
			xhr = new XMLHttpRequest();
            xhr.open("GET", "carica_utente.php?modificaUtente=S&id=" + id, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Inserisci i dati direttamente nell'elemento tbody della tabella
                    document.getElementById("table-utente").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
		} 
		
    });
	
	
	
	document.addEventListener("click", function (event) {
		var e = event.target;
		if (e && e.tagName === "INPUT" && e.getAttribute("data-action") === "apriTastieraNumerica") {
			const idPersonaggio = e.getAttribute("id").split('_')[1];
			const personaggio = e.getAttribute("data-support");
			//const idPersonaggio = this.id.split('_')[1]; // Estrae l'ID del personaggio dall'ID dell'input
            apriTastieraNumerica(idPersonaggio, personaggio);
		} 
    });
	

	
	function apriModale(itemTitolo, itemDescription, modalType, callback) {
		const modalContent = document.createElement('div');
		modalContent.classList.add('modal-content');
		modalContent.innerHTML = `
			<span class="close">&times;</span>
			<p><strong>${itemTitolo}</strong></p>
			<p>${itemDescription}</p>
		`;

		const modal = document.createElement('div');
		modal.classList.add('modal');
		modal.appendChild(modalContent);

		document.body.appendChild(modal);

		modal.style.display = 'block';

		if (modalType === 'conferma') { //l'utente deve inserire si o no
			modalContent.innerHTML += `
				<button class="modal-confirm">SÃ¬</button>
				<button class="modal-cancel">No</button>
			`;

			const confirmButton = modal.querySelector('.modal-confirm');
			confirmButton.addEventListener('click', function () {
				modal.remove();
				callback(true); // Chiamiamo la funzione di callback con true
			});

			const cancelButton = modal.querySelector('.modal-cancel');
			cancelButton.addEventListener('click', function () {
				modal.remove();
				callback(false); // Chiamiamo la funzione di callback con false
			});
		} else if (modalType === 'input') { //l'utente deve inserire un input
			modalContent.innerHTML += `
				<input type="text" id="inputValue" placeholder="Inserisci valore">
				<button id="okButton">OK</button>
			`;

			const okButton = modal.querySelector('#okButton');
			const inputValue = modal.querySelector('#inputValue');

			inputValue.focus();

			inputValue.addEventListener('keyup', function (event) {
				if (event.key === 'Enter') {
					const inputValue = modal.querySelector('#inputValue').value;
					callback(inputValue);
					modal.remove();
				}
			});

			okButton.addEventListener('click', function () {
				const inputValue = modal.querySelector('#inputValue').value;
				callback(inputValue);
				modal.remove();
			});
		} else if (modalType === 'ok') { //l'utente riceve solo un messaggio di avviso
			modalContent.innerHTML += `
				<button class="modal-ok">OK</button>
			`;

			const okButton = modal.querySelector('.modal-ok');
			okButton.addEventListener('click', function () {
				modal.remove();
			});
		}
		
		const closeButton = modal.querySelector('.close');
		closeButton.addEventListener('click', function () {
			modal.remove();
		});

	}


	function apriModaleEffetto(id, personaggio) {
		const modalContent = document.createElement('div');
		modalContent.classList.add('modal-content');
		modalContent.innerHTML = `
			<span class="close">&times;</span>
			<p class="intestazione"><strong>Applica l'effetto a ${personaggio}</strong></p>
			<div class="form-group">
				<input type="hidden" id="idcombattenteeffetto" value="${id}">
				<label for="tipoeffetto" class="form-label">Seleziona un'opzione:</label>
				<select id="tipoeffetto" class="modal-combobox">
					<option value="Bloccato">Bloccato</option>
					<option value="Benedetto">Benedetto</option>
					<option value="Paralizzato">Paralizzato</option>
					<option value="Intralciato">Intralciato</option>
					<option value="Sonno">Sonno</option>
					<option value="Velocizzato">Velocizzato</option>
				</select>
			</div>
			<div class="form-group">
				<label for="durataeffetto" class="form-label">Durata in round:</label>
				<input type="number" id="durataeffetto" class="form-input" value="0">&nbsp;
				<button id="incrementaDurata" class="button-with-image-aggiungi"></button>&nbsp;
    			<button id="decrementaDurata" class="button-with-image-sottrai"></button>
			</div>
			<div class="form-group">
				<label for="permanente" class="form-label">Permanente:</label>
				<input type="checkbox" id="permanente" class="form-input">
			</div><br>
		`;

		
		const modal = document.createElement('div');
		modal.classList.add('modal');
		modal.appendChild(modalContent);

		document.body.appendChild(modal);

		modal.style.display = 'block';

		modalContent.innerHTML += `
			<button class="modal-confirm">ðŸ’¾</button>
			<button class="modal-cancel">ðŸ”™</button>
		`;

		const confirmButton = modal.querySelector('.modal-confirm');
		confirmButton.addEventListener('click', function () {
			
			const idcombattenteeffetto = document.getElementById('idcombattenteeffetto').value;
			const tipoeffetto = document.getElementById('tipoeffetto').value;
			const durataeffetto = document.getElementById('durataeffetto').value;
			const permanente = document.getElementById('permanente').checked ? 'S' : 'N'; // convertito in 1 o 0

			var $a;
			$a = "carica_combattimento.php?applicaeffetto=S"
			$a = $a+"&idcombattenteeffetto="+ idcombattenteeffetto; 
			$a = $a+"&tipoeffetto="			+ tipoeffetto;
			$a = $a+"&durataeffetto="       + (durataeffetto <= 0 ? 0 : durataeffetto);
			$a = $a+"&permanente="      	+ permanente;	
	
			// Creare e inviare una richiesta XMLHttpRequest
			const xhr = new XMLHttpRequest();
			xhr.open('GET', $a, true);
			xhr.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					// Gestire la risposta qui, ad esempio aggiornare l'interfaccia utente o mostrare un messaggio
					caricaCombattimento();
				}
			};
			xhr.send();
	
			modal.remove();
			
			//callback(true); // Chiamiamo la funzione di callback con true
		});

		const cancelButton = modal.querySelector('.modal-cancel');
		cancelButton.addEventListener('click', function () {
			modal.remove();
		});
		
		const closeButton = modal.querySelector('.close');
		closeButton.addEventListener('click', function () {
			modal.remove();
		});
		
		const durataEffettoInput = document.getElementById('durataeffetto');
		const incrementaDurataButton = document.getElementById('incrementaDurata');
		const decrementaDurataButton = document.getElementById('decrementaDurata');

		// Aggiungi gestori di eventi ai pulsanti
		incrementaDurataButton.addEventListener('click', () => {
			const valoreAttuale = parseInt(durataEffettoInput.value, 10);
			durataEffettoInput.value = valoreAttuale + 1;
		});

		decrementaDurataButton.addEventListener('click', () => {
			const valoreAttuale = parseInt(durataEffettoInput.value, 10);
			if (valoreAttuale > 0) {
				durataEffettoInput.value = valoreAttuale - 1;
			}
		});
		
	}

function apriTastieraNumerica(id, personaggio) {
    const modalContent = document.createElement('div');
    modalContent.classList.add('modal-content-tastiera-numerica');
    modalContent.innerHTML = `
        <span class="close">&times;</span>
        <p class="intestazione"></p>
        <div class="modal-content">
            <table>
 				<tr>
                    <td>
						<strong>Imposta il bonus iniziativa per ${personaggio}</strong>
					</td>
				<tr>
                <tr>
                    <td>
                        <button class="numero" data-numero="1">1</button>
                        <button class="numero" data-numero="2">2</button>
                        <button class="numero" data-numero="3">3</button><br>
                        <button class="numero" data-numero="4">4</button>
                        <button class="numero" data-numero="5">5</button>
                        <button class="numero" data-numero="6">6</button><br>
                        <button class="numero" data-numero="7">7</button>
                        <button class="numero" data-numero="8">8</button>
                        <button class="numero" data-numero="9">9</button><br>
                        <button class="numero" data-numero="0">0</button>
					</td>
					<tr>
                    	<td>
							<input type="text" id="valoreNumerico" readonly style="width:100px; font-size:1.5em; text-align:right; margin-bottom:10px;" /><button id="backspace">DEL</button>
                    	</td>
                	</tr>
					<tr>
                    	<td>
							<button id="okButton">OK</button>
							<button id="cancelButton">Cancel</button>
                    	</td>
                	</tr>


            </table>
        </div>`;
    
    const modal = document.createElement('div');
    modal.classList.add('modal');
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    modal.style.display = 'block';
    
    // Prevent scrolling and ensure the page stays at the top
    document.body.style.overflow = 'hidden';
    window.scrollTo(0, 0);
    
    // Function to close the modal and restore overflow
    function closeModal() {
        modal.remove();
        document.body.style.overflow = '';
    }
    
    // Event listener for the close button (X)
    modal.querySelector('.close').addEventListener('click', () => closeModal());
    
    // Event listeners for number buttons
    const numeri = modal.querySelectorAll('.numero');
    numeri.forEach(bottone => {
        bottone.addEventListener('click', (evento) => {
            const numeroSelezionato = evento.target.getAttribute('data-numero');
            const inputField = document.getElementById('valoreNumerico');
            inputField.value += numeroSelezionato;
        });
    });
    
    // Event listener for the OK button
    modal.querySelector('#okButton').addEventListener('click', () => {
        const valoreNumerico = document.getElementById('valoreNumerico').value;
        salvaIniziativaCombattente(id, valoreNumerico, 'bonusIniziativa');
        closeModal();
    });
    
    // Event listener for the Cancel button
    modal.querySelector('#cancelButton').addEventListener('click', () => closeModal());
    
    // Event listener for the backspace button
    modal.querySelector('#backspace').addEventListener('click', () => {
        const inputField = document.getElementById('valoreNumerico');
        inputField.value = inputField.value.slice(0, -1);
    });
}

	
	function apriTastieraNumerica_OLD(id, personaggio) {
    const modalContent = document.createElement('div');
    modalContent.classList.add('modal-content-tastiera-numerica');
    modalContent.innerHTML = `
        <span class="close">&times;</span>
        <p class="intestazione"><strong>Imposta il bonus iniziativa per  ${personaggio}</strong></p>
        <div>
			<table>
				<thead>
    <tr>
      <th><strong>Imposta il bonus iniziativa per  ${personaggio}</strong></th>
      
    </tr>
  </thead>
				<tr>
					<td>
						<button class="numero" data-numero="1">1</button>
						<button class="numero" data-numero="2">2</button>
						<button class="numero" data-numero="3">3</button><br>
						<button class="numero" data-numero="4">4</button>
						<button class="numero" data-numero="5">5</button>
						<button class="numero" data-numero="6">6</button><br>
						<button class="numero" data-numero="7">7</button>
						<button class="numero" data-numero="8">8</button>
						<button class="numero" data-numero="9">9</button><br>
						<button class="numero" data-numero="0">0</button>

					</TD>
				</tr>	
			</table>
		</div>`;
	
	const modal = document.createElement('div');
    modal.classList.add('modal');
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    modal.style.display = 'block';
	
	// Variabile per memorizzare il valore selezionato dalla tastiera numerica
	let bonusiniziativa = 0;
	
    // Aggiungi event listener per gestire la chiusura della modale
    modal.querySelector('.close').addEventListener('click', () => modal.remove());
	
	// Aggiungi event listener ai bottoni numerici
    const numeri = modal.querySelectorAll('.numero');
    numeri.forEach(bottone => {
        bottone.addEventListener('click', (evento) => {
            const numeroSelezionato = evento.target.getAttribute('data-numero');
            salvaIniziativaCombattente(id, numeroSelezionato, 'bonusIniziativa');
			modal.remove();
        });
    });
    /*modal.querySelector('.modal-confirm').addEventListener('click', () => {
        // qui puoi gestire il valore inserito e poi chiudere la modale
        const valoreNumerico = document.getElementById('valoreNumerico').value;
        console.log(valoreNumerico); // o qualsiasi altra logica di gestione
        modal.remove();
    });
    modal.querySelector('.modal-cancel').addEventListener('click', () => modal.remove());*/
}

	
    /*--------------------GESTIONE UTENTE ---------------- */
	function caricaUtente() {
        // Effettua una richiesta AJAX per ottenere i dati dal server PHP
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "carica_utente.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Inserisci i dati direttamente nell'elemento tbody della tabella
                    document.getElementById("table-utente").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

	

	
    /*-----------------MOSTRA DESCRIZIONE ESTESA DEGLI OGGETTI: carica_dati.php ------------- */
    document.addEventListener("click", function(event) {
		if (event.target && event.target.tagName === "IMG" && event.target.getAttribute("data-action") === "mostraNote") {
			const itemDescription = event.target.getAttribute("data-id");
			const itemTitolo = event.target.getAttribute("data-des");

			apriModale(itemTitolo,itemDescription,"ok");

		}
    });


    /*------------------------------------MONETE: carica_monete.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON") {
            var action = event.target.getAttribute("data-action");
            var idMoneta = event.target.getAttribute("data-id");

            if (action === "depositaMonete" || action === "prelevaMonete" || action === "aggiungiMonete" || action === "togliMonete") {
                azioniMonete(idMoneta, action, event.target);
            } 
        }

    });
	
	document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "carica-template-monete") {
          		caricaTemplateMonete(event.target);
            } 
    });


    function azioniMonete(idMoneta, azione, button) {
		let quantita;
	
		apriModale("Gestione Monete", "Quante monete " + generaTestoAzione(azione) + "?","input", function(inputValue) { 
			quantita=inputValue;
			//var quantita = prompt("Quante monete " + generaTestoAzione(azione) + "?");
			if (quantita === null || isNaN(quantita) || quantita <= 0) {
				return;
			}

			button.disabled = true;
			button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';

			var xhr = new XMLHttpRequest();
			xhr.open("GET", "carica_monete.php?"+azione+"=S&quantita=" + quantita + "&idMoneta=" + idMoneta, true);
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhr.onreadystatechange = function () {
				if (xhr.readyState === 4) {if (xhr.status === 200) {  document.getElementById("table-monete").innerHTML = xhr.responseText; } }
			};
			xhr.send();

			});
    }

	
    function generaTestoAzione(azione) {
        switch (azione) {
            case "depositaMonete":
                return "depositare";
            case "prelevaMonete":
                return "prelevare";
            case "aggiungiMonete":
                return "aggiungere";
            case "togliMonete":
                return "sottrarre";
            default:
                return "";
        }
    }

     // Funzione per caricare i dati dalla tabella MySQL e visualizzarli nella tabella HTML
    function caricaMonete() {
        // Effettua una richiesta AJAX per ottenere i dati dal server PHP
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "carica_monete.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Inserisci i dati direttamente nell'elemento tbody della tabella
                    document.getElementById("table-monete").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
	
	function caricaTemplateMonete() {
		var tbodyMonete = document.getElementById("controlloRigheMonete");
		if (tbodyMonete.rows.length > 0) {
		
			apriModale("Attenzione", "Tutte le monete verranno azzerate, procedere?", "conferma", function (risposta) {
				if (!risposta) { 
					return;
				}
			});
          
        }
		
		var xhr = new XMLHttpRequest();
		xhr.open("GET", "carica_monete.php?caricaTemplateMonete=S", true);
		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4 && xhr.status === 200) {
				// Inserisci i dati direttamente nell'elemento tbody della tabella
				document.getElementById("table-monete").innerHTML = xhr.responseText;
			}
		};
		xhr.send();
 	}

    /*------------------------------------SALVA MODIFICA OGGETTO: carica_dati.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "salva-modifica-oggetto") {
            var idOggetto       = document.getElementById("modifica_oggettoId").value;
            var nome            = document.getElementById("nuovo_nome").value;
            var identificato    = document.getElementById("nuovo_ide").value;
            var quantita        = document.getElementById("nuova_qta").value;
            var valore          = document.getElementById("nuovo_val").value;
            var categoria       = document.getElementById("nuova_categoria").value;
            var note            = document.getElementById("nuove_note").value;
            salvaModificaOggetto(event.target, idOggetto, nome, quantita, valore, categoria, note,identificato);
        }
    });

    function salvaModificaOggetto(button, idOggetto, nome, quantita, valore, categoria, note, identificato) {
    
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';
        //recupero variabili + passaggio
        var xhr = new XMLHttpRequest();
		var $a;
        $a = "carica_dati.php?salvaModificaOggetto=S&nome=" + encodeURIComponent(nome); 
        $a = $a+"&identificato="                            + identificato;
        $a = $a+"&quantita="                                + quantita;
        $a = $a+"&valore="                                  + valore;
        $a = $a+"&categoria="                               + categoria;
        $a = $a+"&note="                                    + encodeURIComponent(note.trim());
        $a = $a+"&idOggetto="                               + idOggetto;

        xhr.open("GET", $a, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {if (xhr.status === 200) { caricaDati(); } }
        };
        xhr.send();
    }


    /*------------------------------------MODIFICA OGGETTO: carica_dati.php  ----------------------------- */
    document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "modifica-oggetto") {
                modificaOggetto(event.target.getAttribute("data-id"), event.target);
            } 
    });

    function modificaOggetto(idOggetto, button) {
    
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';
         
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_dati.php?modificaOggetto=S&idOggetto=" + idOggetto, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {if (xhr.status === 200) { document.getElementById("table-inventario").innerHTML = xhr.responseText; } }
        };
        xhr.send();
    }


    /*------------------------------------ELIMINA OGGETTO: carica_dati.php  ----------------------------- */
    document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "elimina-oggetto") {
                eliminaOggetto(event.target.getAttribute("data-id"), event.target);
            } 
    });

    function eliminaOggetto(idOggetto, button) {
    
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';
    
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_dati.php?eliminaOggetto=S&idOggetto=" + idOggetto, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {if (xhr.status === 200) { caricaDati(); } }
        };
        xhr.send();
    }


    /*------------------------------------SALVA NUOVO OGGETTO: carica_dati.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "salva-nuovo-oggetto") {
            var nome = document.getElementById("nome").value;
            var quantita = document.getElementById("quantita").value;
            var valore = document.getElementById("valore").value;
            var categoria = document.getElementById("categoria").value;
            var note = document.getElementById("note").value;
            var identificato = document.getElementById("identificato").value;
            salvaNuovoOggetto(event.target, nome, quantita, valore, categoria, note, identificato);
        }
    });

    function salvaNuovoOggetto(button, nome, quantita, valore, categoria, note, identificato) {
    
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';
        //recupero variabili + passaggio
        var xhr = new XMLHttpRequest();
		var $a;
        $a = "carica_dati.php?salvaNuovoOggetto=S&nome="+nome; 
        $a = $a+"&quantita="+quantita;
        $a = $a+"&valore="+valore;
        $a = $a+"&categoria="+categoria;
        $a = $a+"&categoria="+categoria;
        $a = $a+"&note="+note;
        $a = $a+"&identificato="+note;
		
        xhr.open("GET", $a, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {if (xhr.status === 200) { caricaDati(); } }
        };
        xhr.send();
    }

    /*------------------------------------ANNULLA AGGIUNGI/MODIFICA OGGETTO: carica_compagnia.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "annulla-aggiungi-oggetto") {
            annullaAggiungiOggetto(event.target);
        }
    });
	
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "annulla-modifica-oggetto") {
            annullaAggiungiOggetto(event.target);
        }
    });

	function annullaAggiungiOggetto(button) {

        button.disabled = true;
        button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';
        
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_dati.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById("table-inventario").innerHTML = xhr.responseText; 
            } };
        
        xhr.send();
        
    }



    /*------------------------------------AGGIUNGI OGGETTO: carica_dati.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "nuovo-oggetto") {
            //recupero variabili + passaggio variabili
            aggiungiOggetto(event.target);
        }
    });

    function aggiungiOggetto(button) {
    
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';
    
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_dati.php?nuovoOggetto=S", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
         if (xhr.readyState === 4) {if (xhr.status === 200) { document.getElementById("table-inventario").innerHTML = xhr.responseText; } }   
        };
        xhr.send();
    }

    
    /*------------------------------------ELIMINA PERSONAGGIO: carica_compagnia.php  ----------------------------- */
    document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "elimina-personaggio") {
                eliminaPersonaggio(event.target.getAttribute("data-id"), event.target);
            } 
    });

    function eliminaPersonaggio(id, button) {
    
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';
    
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_compagnia.php?eliminaPersonaggio=S&id=" + id, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {if (xhr.status === 200) { caricaCompagnia(); } }
        };
        xhr.send();
    }




    /*------------------------------------FORM AGGIUNGI PERSONAGGIO: carica_compagnia.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "aggiungi-personaggio") {
            aggiungiPersonaggio(event.target);
        }
    });

    
    function aggiungiPersonaggio(button) {
    
        button.disabled = true;
        button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';
    
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_compagnia.php?aggiungiPersonaggio=S", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById("table-compagnia").innerHTML = xhr.responseText; 
				
            } };
        
        xhr.send();
    }

    /*------------------------------------ANNULLA AGGIUNGI/MODIFICA PERSONAGGIO: carica_compagnia.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "annulla-aggiungi-personaggio") {
            annullaAggiungiPersonaggio(event.target);
        }
    });
	
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "annulla-modifica-personaggio") {
            annullaAggiungiPersonaggio(event.target);
        }
    });

    
    function annullaAggiungiPersonaggio(button) {

        button.disabled = true;
        button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';
        
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_compagnia.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById("table-compagnia").innerHTML = xhr.responseText; 
            } };
        
        xhr.send();
        
    }


    /*------------------------------------SALVA AGGIUNGI PERSONAGGIO: carica_compagnia.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "salva-aggiungi-personaggio") {
            var nomeGiocatorenew = document.getElementById("newNomeGiocatore").value;
            var nomePersonaggionew = document.getElementById("newNomePersonaggio").value;
            var bonusiniziativanew = document.getElementById("newBonusIniziativa").value;
            var mottonew = document.getElementById("newMotto").value;
            var classenew = document.getElementById("newClasse").value;
            var razzanew = document.getElementById("newRazza").value;
			var grupponew = document.getElementById("newGruppo").value;

            salvaAggiungiPersonaggio(event.target, nomeGiocatorenew, nomePersonaggionew, bonusiniziativanew, mottonew, classenew, razzanew, grupponew);
            
        }
    });

    
    function salvaAggiungiPersonaggio(button,nomeGiocatorenew, nomePersonaggionew,bonusiniziativanew,mottonew,classenew,razzanew, grupponew) {
        button.disabled = true;
        button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';
        var $a;
        $a= "carica_compagnia.php?salvaNuovoPersonaggio=S&nomeGiocatorenew=" + nomeGiocatorenew + "&nomePersonaggionew=" + nomePersonaggionew+ "&bonusiniziativanew=" + bonusiniziativanew + "&mottonew=" + mottonew + "&classenew=" + classenew + "&razzanew=" + razzanew + "&grupponew=" + grupponew;
 
        var xhr = new XMLHttpRequest();
        xhr.open("GET", $a, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {  caricaCompagnia();  } };

        xhr.send();
    }


    /*------------------------------------FORM MODIFICA PERSONAGGIO: carica_compagnia.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "modifica-personaggio") {
            //alert(event.target.getAttribute("data-id"));
            modificaPersonaggio(event.target.getAttribute("data-id"), event.target); 
        }
    });

    function modificaPersonaggio(id, button) {

        button.disabled = true;
        button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';

        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_compagnia.php?modificaPersonaggio=S&id="+id, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) { document.getElementById("table-compagnia").innerHTML = xhr.responseText;  } };
        
        xhr.send();

    }


    /*------------------------------------SALVA MODIFICA PERSONAGGIO: carica_compagnia.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "salva-modifica-personaggio") {
           
            var nomeGiocatorenew    = document.getElementById("nuovo_nomePG").value;
            var nomePersonaggionew  = document.getElementById("nuovo_nomeGiocatore").value;
            var bonusiniziativanew  = document.getElementById("nuovo_bonusIniziativa").value;
            var mottonew            = document.getElementById("nuovo_Motto").value;
            var classenew           = document.getElementById("nuovo_classe").value;
            var razzanew            = document.getElementById("nuovo_razza").value;
            var idPersonaggio       = document.getElementById("nuovo_personaggioid").value;            
            var idGrupponew			= document.getElementById("nuovo_gruppo").value;   

            salvaModificaPersonaggio(event.target, idPersonaggio, nomeGiocatorenew, nomePersonaggionew, bonusiniziativanew, mottonew, classenew, razzanew, idGrupponew);

        }
    });

    
    function salvaModificaPersonaggio(button, idPersonaggio, nomeGiocatorenew, nomePersonaggionew,bonusiniziativanew,mottonew,classenew,razzanew, idGrupponew) {

        button.disabled = true;
        button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';
        var $a;
        $a= "carica_compagnia.php?salvaModificaPersonaggio=S&nomeGiocatorenew=" + nomeGiocatorenew + "&nomePersonaggionew=" + nomePersonaggionew+ "&bonusiniziativanew=" + bonusiniziativanew + "&mottonew=" + mottonew + "&classenew=" + classenew + "&razzanew=" + razzanew + "&id="+ idPersonaggio + "&idGrupponew="+ idGrupponew;

		
        var xhr = new XMLHttpRequest();
        xhr.open("GET", $a, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {  caricaCompagnia();  } };

        xhr.send();
    }


    /*------------------------------------CARICA COMPAGNIA: carica_compagnia.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "carica-compagnia") {
            compagnia(event.target);
        }
    });

    
    function compagnia(button) {
        //window.location.href = "aggiungi_personaggio.php";

        button.disabled = true;
        button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "carica_compagnia.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) { caricaCompagnia(); } };
        
        xhr.send();
    }



    /*------------------------------------NUOVO AVVERSARIO: carica_combattimento.php  ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.innerHTML === "Nuovo Combattente") {
            nuovoAvversario(event.target.getAttribute("data-id"), event.target);
        }
    });

    function nuovoAvversario(id, button)
    {
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';

        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_combattimento.php?nuovoavversario=S", true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) { caricaCombattimento(); }
        };
        xhr.send();
    }   



    /*------------------------------------NUOVO COMBATTIMENTO  ----------------------------- */
    document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "BUTTON" && event.target.innerHTML === "Nuovo Combattimento") {
                nuovoCombattimento(event.target);
            }
    });

	function nuovoCombattimento(button) {
		let numeroAvversari;
		
		apriModale("Attenzione", "Attenzione, tutti dati dall'attuale combattimento verranno eliminati, confermi?","conferma",
			function (risposta) {
				if (risposta) {
					apriModale("Nuovo Combattimento", "Inserisci il numero di avversari:","input", function(inputValue) {
						numeroAvversari = inputValue;

						if (numeroAvversari === null || isNaN(numeroAvversari) || numeroAvversari <= 0) {
							return;
						} else {
							button.disabled = true;
							button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';

							var xhr = new XMLHttpRequest();
							xhr.open("POST", "carica_combattimento.php?avversari=" + numeroAvversari + "&nuovocombattimento=S", true);
							xhr.onreadystatechange = function() {
								if (xhr.readyState === 4 && xhr.status === 200) {
									caricaCombattimento();
								}
							};

							xhr.send();
						}
					});
				}
			}
		);
	}

    /*--------------------EFFETTI: apre la modale per applicare gli effetti ---------------- */
    document.addEventListener("click", function(event) {
		if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "applica-effetto") {
			const id = event.target.getAttribute("data-id");
			const personaggio = event.target.getAttribute("data-support");

			apriModaleEffetto(id, personaggio);
			

		}
    });


    /*------------------------------------AGGIORNA INIZIATIVA: salva tutti i dati modificati nel form ----------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "salva-combattimento") {
            aggiornaIniziativa( event.target);
        }
    });

    function aggiornaIniziativa(button) {
        // Disabilita il pulsante "Elimina" durante l'operazione
        button.disabled = true;
        
        // Mostra la GIF animata "img/loading.gif" al posto del testo del pulsante
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';

        var dati = [];
        var personaggi      = document.querySelectorAll(".personaggio");
        var iniziative      = document.querySelectorAll(".iniziativa");
        var bonusiniziative = document.querySelectorAll(".bonusiniziativa");
        var lenti           = document.querySelectorAll(".lento");

        for (var i = 0; i < personaggi.length; i++) {
            dati.push({
                id: personaggi[i].id.split("_")[1],
                personaggio: personaggi[i].value,
                iniziativa: iniziative[i].value,
                bonusiniziativa: bonusiniziative[i].value,
                lento: lenti[i].checked
            });
        }
        //alert(JSON.stringify(dati));

        var xhr = new XMLHttpRequest();
        xhr.open("PUT", "carica_combattimento.php?aggiornainiziativa=S", true);
        xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) { caricaCombattimento(); }
        };
        xhr.send(JSON.stringify(dati));
    }


    /*------------------------------------REFRESH COMBATTIMENTO-------------------------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("id") === "refresh") {
            refreshCombattimento(event.target);
        } 
    });

    function refreshCombattimento(button) {
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';
        caricaCombattimento();
    }


    /*------------------------------------ATTIVA COMBATTIMENTO-------------------------------------------- */
    document.addEventListener("change", function (event) {
            if (event.target && event.target.tagName === "INPUT" && event.target.getAttribute("data-action") === "attivaFight") {
                var attivo = document.getElementById("attivo").checked;
                attivaFight(attivo);
            } 
    });

    function attivaFight(fightAttivo) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "carica_combattimento.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) { 
                if (xhr.status === 200) {caricaCombattimento();} 
            }
        };
        xhr.send("fightAttivo=" + fightAttivo); 
    }


    // Funzione per caricare i dati dalla tabella MySQL e visualizzarli nella tabella HTML
    function caricaDati() {
        // Effettua una richiesta AJAX per ottenere i dati dal server PHP
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "carica_dati.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Inserisci i dati direttamente nell'elemento tbody della tabella
                    document.getElementById("table-inventario").innerHTML = xhr.responseText;
                    document.getElementById('splash-screen').style.display = 'none';
                }
            };
            xhr.send();
            
        }

    function caricaCompagnia() {
        // Effettua una richiesta AJAX per ottenere i dati dal server PHP
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "carica_compagnia.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Inserisci i dati direttamente nell'elemento tbody della tabella
                    document.getElementById("table-compagnia").innerHTML = xhr.responseText;
					//$("#table-compagnia").DataTable();
                }
            };
            xhr.send();
        }
    
    function caricaGruppi() {
        // Effettua una richiesta AJAX per ottenere i dati dal server PHP
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "carica_gruppi.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Inserisci i dati direttamente nell'elemento tbody della tabella
                    document.getElementById("table-gruppi").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

    function caricaCombattimento() {
        // Effettua una richiesta AJAX per ottenere i dati dal server PHP
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "carica_combattimento.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Inserisci i dati direttamente nell'elemento tbody della tabella
                    document.getElementById("table-combattimento").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }



    /*------------------------------------SALVA INIZIATIVA-------------------------------------------- */
    document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "INPUT" && event.target.getAttribute("data-action") === "impostaLento") {
                var id = event.target.getAttribute("data-id");
                var lento = document.getElementById("lento_" + id).checked;
                salvaIniziativaCombattente(id, lento, 'lento');
            } 
    });

    //function salvaBonusIniziativaCombattente(id, personaggio, iniziativa, bonusiniziativa, lento, button) {
	function salvaIniziativaCombattente(id, valore, tipo) {
        // Disabilita il pulsante "Elimina" durante l'operazione
        //button.disabled = true;
        
        // Mostra la GIF animata "img/loading.gif" al posto del testo del pulsante
        //button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';

        // Effettua una richiesta AJAX al server PHP per eliminare l'oggetto
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "carica_combattimento.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                //button.disabled = false;
                if (xhr.status === 200) { caricaCombattimento(); }
            }
        };
		var $a;		
        //$a= "id=" + id + "&personaggio=" + personaggio + "&iniziativa=" + iniziativa+ "&bonusiniziativa=" + bonusiniziativa + "&lento=" + //lento + "&salvainiziativacombattente=S";
		$a= "id=" + id + "&valore=" + valore + "&tipo=" + tipo + "&salvainiziativacombattente=S";
        //alert($a);
        xhr.send($a); 
    }


    /*------------------------------------NUOVO ROUND-------------------------------------------- */
    document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "nuovo-round") {
				var dadoIniziativa = document.getElementById("dadoIniziativa").value;
                nuovoRound(event.target, dadoIniziativa);
            } 
    });

    function nuovoRound(button, dadoIniziativa) {
        // Disabilita il pulsante "Elimina" durante l'operazione
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';

        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_combattimento.php?nuovoround=S&dadoIniziativa="+dadoIniziativa, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {if (xhr.status === 200) { caricaCombattimento();
                    //document.getElementById("table-combattimento").innerHTML = xhr.responseText;
                } 
            }
        };
        xhr.send(); 
    }
   
    // ELIMINA OGGETTO
    document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "elimina-oggetto") {
                eliminaOggetto(event.target.getAttribute("data-id"), event.target);
            } 
    });

    /*------------------------------------ELIMINA COMBATTENTE-------------------------------------------- */
    document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "elimina-combattente") {
                eliminaCombattente(event.target.getAttribute("data-id"), event.target);
            } 
    });
	
    function eliminaCombattente(id, button) {
        // Disabilita il pulsante "Elimina" durante l'operazione
        button.disabled = true;
        
        // Mostra la GIF animata "img/loading.gif" al posto del testo del pulsante
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';

        // Effettua una richiesta AJAX al server PHP per eliminare l'oggetto
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "carica_combattimento.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {button.disabled = false; if (xhr.status === 200) { caricaCombattimento(); } }
        };
        xhr.send("id=" + id + "&eliminacombattente=S");
    }

	
	/*------------------------------------ELIMINA EFFETTO COMBATTIMENTO -------------------------------------------- */
	document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "elimina-effetto") {
                eliminaEffetto(event.target.getAttribute("data-id"), event.target);
            } 
    });

	function eliminaEffetto(id, button) {
        // Disabilita il pulsante "Elimina" durante l'operazione
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';
		var $a;
		$a = "carica_combattimento.php?eliminaeffetto=S&id=" + id;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", $a, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {if (xhr.status === 200) { 
					caricaCombattimento();
                } 
            }
        };
        xhr.send(); 
	}

	/*-----------------------------------AGGIUNGI GRUPPO carica_gruppi.php-------------------------------------------- */
	document.addEventListener("click", function (event) {
		if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "aggiungi-gruppo") {
			aggiungiGruppo(event.target); 
		}
	});

	function aggiungiGruppo(button) {

		button.disabled = true;
		button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';

		var xhr = new XMLHttpRequest();
		var url = "carica_gruppi.php"; // URL del file PHP
		var params = "aggiungiGruppo=S"; // Parametri da inviare

		xhr.open("POST", url, true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4) {
				button.disabled = false;
				if (xhr.status === 200) {
					document.getElementById("table-gruppi").innerHTML = xhr.responseText;
				}
			}
		};

		xhr.send(params); // Invio dei parametri con il metodo POST


	}

	
	
	/*-----------------------------------SALVA NUOVO GRUPPO file: carica_gruppi.php-------------------------------------------- */
	document.addEventListener("click", function (event) {
		if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "salva-nuovo-gruppo") {
			var nomeGruppo = document.getElementById("nome_nuovo_gruppo").value;
			var diario		= document.getElementById("diario_nuovo_gruppo").value;
			salvaNuovoGruppo(nomeGruppo,diario,event.target); 
		}
	});

	function salvaNuovoGruppo(nomeGruppo,diario, button) {

		apriModale('Attenzione','Non dimenticare di impostare il gruppo attivo al momento premendo il pulsante ATTIVA', 'ok');

		button.disabled = true;
		button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';

		var xhr = new XMLHttpRequest();
		var url = "carica_gruppi.php"; // URL del file PHP
		var params = "salvaNuovoGruppo=S&nomeGruppo=" + nomeGruppo + "&diario=" + diario; // Parametri da inviare

		xhr.open("POST", url, true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4) {
				button.disabled = false;
				if (xhr.status === 200) { 	caricaGruppi();  
					//document.getElementById("table-gruppi").innerHTML = xhr.responseText;
				}
			}
		};

		xhr.send(params); // Invio dei parametri con il metodo POST


	}


	
	
	
	
	
	/*-----------------------------------ELIMINA GRUPPO carica_gruppi.php-------------------------------------------- */
	document.addEventListener("click", function (event) {
		if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "elimina-gruppo") {
			eliminaGruppo(event.target.getAttribute("data-id"),event.target); 
		}
	});

	function eliminaGruppo(id, button) {
	// Esempio di utilizzo
		apriModale("Attenzione", "Cancellando il gruppo eliminari anche tutte le monete e dovrai controllare l'associazione dei personaggi, procedere?","conferma", function (risposta) {
			if (risposta) {
				button.disabled = true;
				button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';

				var xhr = new XMLHttpRequest();
				var url = "carica_gruppi.php"; // URL del file PHP
				var params = "eliminaGruppo=S&id=" + id; // Parametri da inviare

				xhr.open("POST", url, true);
				xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhr.onreadystatechange = function () {
					if (xhr.readyState === 4) {
						button.disabled = false;
						if (xhr.status === 200) {
							caricaGruppi(); 
							caricaMonete();
							//document.getElementById("table-gruppi").innerHTML = xhr.responseText;
						}
					}
				};

				xhr.send(params); // Invio dei parametri con il metodo POST			

			}
		});




	}

	
	/*-----------------------------------ATTIVA GRUPPO carica_gruppi.php-------------------------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "attiva-gruppo") {
            attivaGruppo(event.target.getAttribute("data-id"),event.target); 
        }
    });

    function attivaGruppo(id, button) {
  
        button.disabled = true;
        button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';
 
        var xhr = new XMLHttpRequest();
        var url = "carica_gruppi.php"; // URL del file PHP
        var params = "attivaGruppo=S&id=" + id; // Parametri da inviare

        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                button.disabled = false;
                if (xhr.status === 200) {
					//document.getElementById("table-gruppi").innerHTML = xhr.responseText;
                    caricaGruppi();
					caricaMonete();
                }
            }
        };

        xhr.send(params); // Invio dei parametri con il metodo POST


    }

	
	/*-----------------------------------MODIFICA GRUPPO carica_gruppi.php-------------------------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "modifica-diario") {
            modificaDiario(event.target.getAttribute("data-id"),event.target); 
        }
    });

    function modificaDiario(id, button) {
  
        button.disabled = true;
        button.innerHTML = '<img class="loading-gif" src="img/loading.gif" alt="Loading...">';

        var xhr = new XMLHttpRequest();
        var url = "carica_gruppi.php"; // URL del file PHP
        var params = "modificaDiario=S&id=" + id; // Parametri da inviare

        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                button.disabled = false;
                if (xhr.status === 200) {
                    document.getElementById("table-gruppi").innerHTML = xhr.responseText;
                }
            }
        };

        xhr.send(params); // Invio dei parametri con il metodo POST


    }


    /*-----------------------------------PULSANTE BACK GRUPPI-------------------------------------------- */
    document.addEventListener("click", function (event) {
            if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "torna-gruppi") {
                tornaGruppi(event.target);
            } 
    });
    
    function tornaGruppi(button) {
        // Disabilita il pulsante "Elimina" durante l'operazione
        button.disabled = true;
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';
        caricaGruppi();
    }

	
	
	

	/*-----------------------------------AVANZA INIZIATIVA-------------------------------------------- */
    document.addEventListener("click", function (event) {
        if (event.target && event.target.tagName === "BUTTON" && event.target.getAttribute("data-action") === "avanza-iniziativa") {
            avanzaIniziativa(event.target); 
        }
    });

    function avanzaIniziativa(button) {
        // Disabilita il pulsante "Elimina" durante l'operazione
        button.disabled = true;
        
        // Mostra la GIF animata "img/loading.gif" al posto del testo del pulsante
        button.innerHTML = '<img  class="loading-gif" src="img/loading.gif" alt="Loading...">';

        // Effettua una richiesta AJAX al server PHP per eliminare l'oggetto
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_combattimento.php?avanzainiziativa=S", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) { if (xhr.status === 200) { caricaCombattimento(); } }
        };
        xhr.send(); 
    }
	


    /*-----------------------------------ASCOLTATORI PER NAVIGATOR BAR-------------------------------------------- */
    document.getElementById("gruppi-tab").addEventListener("click", function () {
        mostraPagina("gruppi-page");
        document.querySelector(".mianavbar a.active").classList.remove("active");
        this.classList.add("active");
    });

    document.getElementById("compagnia-tab").addEventListener("click", function () {
        mostraPagina("compagnia-page");
        document.querySelector(".mianavbar a.active").classList.remove("active");
        this.classList.add("active");
    });

    document.getElementById("inventario-tab").addEventListener("click", function () {
        mostraPagina("inventario-page");
        document.querySelector(".mianavbar a.active").classList.remove("active");
        this.classList.add("active");
    });

    document.getElementById("iniziativa-tab").addEventListener("click", function () {
        mostraPagina("iniziativa-page");
        document.querySelector(".mianavbar a.active").classList.remove("active");
        this.classList.add("active");
    });

    document.getElementById("monete-tab").addEventListener("click", function () {
        mostraPagina("monete-page");
        document.querySelector(".mianavbar a.active").classList.remove("active");
        this.classList.add("active");
    });

   /* document.getElementById("manuali-tab").addEventListener("click", function () {
   //     mostraPagina("manuali-page");
   //     document.querySelector(".mianavbar a.active").classList.remove("active");
   //     this.classList.add("active");
    });*/

	document.getElementById("utente-tab").addEventListener("click", function () {
        mostraPagina("utente-page");
        document.querySelector(".mianavbar a.active").classList.remove("active");
        this.classList.add("active");
    });

	
    function mostraPagina(paginaId) {
        // Nascondi tutte le pagine
        document.getElementById("inventario-page").style.display    = "none";
        document.getElementById("monete-page").style.display        = "none";
        document.getElementById("compagnia-page").style.display     = "none";
        document.getElementById("iniziativa-page").style.display    = "none";
        document.getElementById("manuali-page").style.display       = "none";
        document.getElementById("gruppi-page").style.display        = "none";
        document.getElementById("utente-page").style.display        = "none";
        document.getElementById("istruzioni-page").style.display    = "none";
        // Mostra la pagina specifica
        document.getElementById(paginaId).style.display = "block";
    }

	
	
    /*----------------------------------- ISTRUZIONI E CONTATTO -------------------------------------------- */
	var menuTab = document.querySelector('.lateral-menu-tab');
	
	document.addEventListener("click", function(event) {
		// Verifica se il click è su un link (tag A) e se l'ID corrisponde a quello del link Istruzioni
		if (event.target && event.target.tagName === "A" && (event.target.id === "link-istruzioni" || event.target.id === "link-formContatti"  || event.target.id === "link-impostazioni")) {
			event.preventDefault(); // Previene il comportamento standard del link
			mostraPagina("istruzioni-page");
			caricaIstruzioni(event.target.id); // Chiama la funzione per caricare le istruzioni
			menuTab.classList.toggle('menu-open');
		}
		
		if (event.target && event.target.tagName === "IMG" && event.target.getAttribute("data-action") === "menuIconLateral") {
			menuTab.classList.toggle('menu-open');

		}
		
	});
	


	function caricaIstruzioni(azione) {
        // Effettua una richiesta AJAX per ottenere i dati dal server PHP
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "carica_istruzioni.php?" + azione + "=S", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Inserisci i dati direttamente nell'elemento tbody della tabella
                    document.getElementById("table-istruzioni").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

	document.addEventListener("click", function(event) {
		// Verifica se il click è su un link (tag A) e se l'ID corrisponde a quello del link Istruzioni
		if (event.target && event.target.tagName === "BUTTON" && (event.target.id === "inviaFormContatto")) {
			var nome 		= document.getElementById("name").value;
			var email		= document.getElementById("email").value;
			var messaggio 	= document.getElementById("message").value;
			inviaFormContatto(nome,email,messaggio); 

		}
	});
	
	function inviaFormContatto(nome,email,messaggio) {
		var formData = new FormData();
		formData.append("name", nome);
		formData.append("email", email);
		formData.append("message", messaggio);

		// Effettua una richiesta AJAX per ottenere i dati dal server PHP
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "carica_istruzioni.php", true);
		xhr.onreadystatechange = function () {
		if (xhr.readyState === 4 && xhr.status === 200) {
						// Inserisci i dati direttamente nell'elemento tbody della tabella
						document.getElementById("table-istruzioni").innerHTML = xhr.responseText;
					}
				};
				xhr.send(formData);
	}


    /*----------------------------------- POPOLA LE TAB DEL PROGRAMMA -------------------------------------------- */
    caricaGruppi();
    caricaCompagnia();
    caricaCombattimento();
    caricaDati();
    caricaMonete();
	caricaUtente();

});




/*

    function chiudiModal() {
        var modal = document.getElementById("nuovo-combattimento-modal");
        modal.style.display = "none";
    }




function aggiungiOggetto() {
        window.location.href = "aggiungi_oggetto.html";
}
	

function aggiungiPersonaggio() {
        window.location.href = "aggiungi_personaggio.php";
}
	

function reloadCombattimento(){
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "carica_combattimento.php", true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Inserisci i dati direttamente nell'elemento tbody della tabella
                document.getElementById("table-combattimento").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
 }*/


function filtraCategorie() {
    var checkboxes = document.querySelectorAll('input[type="checkbox"][id^="categoria-"]');
    var tableRows = document.querySelectorAll("#table-inventario tbody tr");
    var selectedCategories = [];

	checkboxes.forEach(function (checkbox) {
        var label = checkbox.parentNode; // Ottieni la label corrispondente
        if (checkbox.checked) {
            var categoriaCheckbox = checkbox.id.replace("categoria-", "");
            selectedCategories.push(categoriaCheckbox);
            label.classList.add('label-selezionata'); // Aggiungi la classe selezionata
        } else {
            label.classList.remove('label-selezionata'); // Rimuovi la classe selezionata
        }
    });

    tableRows.forEach(function (row) {
			var categoriaElement = row.querySelector(".categoria");
			if (categoriaElement) {
				var categoria = categoriaElement.textContent;
				var shouldShow = selectedCategories.length === 0 || selectedCategories.includes(categoria);
				row.style.display = shouldShow ? "table-row" : "none";
			}
		
    });

	var righeVisibili = document.querySelectorAll("#table-inventario tbody tr[style='display: table-row;']");
    righeVisibili.forEach(function (row) {
        row.style.backgroundColor = "#ffffff";
    });

}


	
function openPdfPage(pdfId) {
	// Costruisci l'URL della pagina che contiene l'iframe
	var pdfPageUrl = "pdf_page.html?id=" + pdfId;
	// Apri la nuova pagina
	window.open(pdfPageUrl, "_blank");
}
	
