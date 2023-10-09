# Autenticazione a due fattori per LIbriperera

Autenticazione a due fattori per l'accesso da amministratore all'interfaccia di gestione di un e-commerce.

## Tecnologie Utilizzate
- PHP: linguaggio di scripting per comunicare con il database Altervista.
- MySQL: sistema di gestione di database relazionali (RDBMS), PHP comunica con MySQL per l'inserimento, la lettura, l'aggiornamento dei dati per l'autenticazione.
- HTML (HyperText Markup Language): utilizzato per creare la struttura e il contenuto delle pagine web (es. form).
- Javascript: linguaggio di scripting lato client che viene utilizzato per creare interattività nelle pagine web.
- jQuery: una libreria JavaScript, semplifica le operazioni come la manipolazione del DOM (Document Object Model) e le chiamate AJAX (Asynchronous JavaScript and XML).
- JSON (JavaScript Object Notation): Formato di dati leggero e facile da leggere utilizzato per lo scambio di dati tra il browser e il server.
- CSS (Cascading Style Sheets): Utilizzato per lo stile e la formattazione delle pagine web.
- Bcrypt: Algoritmo di hashing utilizzato per proteggere le password e altri dati sensibili memorizzati nel database.

## Funzionalità Principali

- Descrizione delle principali funzionalità del progetto:
- Registrazione
    - Registrazione Cliente: Effettuabile dalla pagina principale, è richiesto un username e mail univoci, e una password.
    - Registrazione Admin: Effettuabile solo dalla pagina Privata di registrazione di un altro admin, è richiesto un username e mail univoci, e una password.

      
- Login
    - Login Cliente: Effettuabile dalla pagina principale, è richiesto l'username e password dell'utente.
    - Login Admin:  Effettuabile dalla pagina principale e dalla pagina di Login privata di un admin, è richiesto l'username e password dell'utente.

      
- Secondo Fattore di Autenticazione: il secondo fattore è la mail. Il codice generato dal sistema viene inviato alla mail dell'utente che tenta di accedere al sito con le sue credenziali dopo aver superato con successo il primo fattore.

## Struttura Database Altervista

Parte del Diagramma E-R del database che riguarda l'autenticazione:

![Diagramma senza titolo drawio](https://github.com/Thi-Perera/2FA-access-for-Admins-/assets/99124492/4fd84616-9a30-4743-b08f-f62739d824bb)

- Utente:
    - username: nome dell'utente.
    - mail: mail dell'utente.
    - password: password per l'accesso dell'utente.
    - Ruolo: per distinguere un cliente da un Admin( Cliente con privilegi in più)
    - id_utente: che identifica univocamente ogni utente.
    - 
- 2FA_auth:
    - 2FA_code: codice 2fa criptato con algoritmo Bcrypt.
    - 2FA_EXPIRE: data di scadenza del codice 2fa (900 secondi dopo la sua creazione).
    - 2FA_usage: campo status del codice 2fa(usato, non usato).
    - id_utente:  per identificare a quale utente (con ruolo=Admin) appartiene il codice 2fa.

  


Autenticazione a due fattori per l'accesso da amministratore all'interfaccia di gestione di un e-commerce.

## Struttura pagine web

Lista Pagine:
 - account-page.html: Pagina principare che contiene il form per la registrazione dei clienti e il login per cliente e Admin.
 - login.php: Comunica con il db per verificare i dati inseriti per la prima autenticazione, reindirizza gli admin a 2fa_codegeneration.php.
 - 2fa_codegeneration.php: Contiene il Form per l'autenticazione per il secondo fattore, reindirizza a 2fa_verify.
 - 2fa_verify.php: verifica il codice 2fa inserito dall'utente, reindirizza al sito se l'autenticazione va a buon fine.

## Script principali

### verifica del primo fattore

login.php
```php
<?php
require_once('config.php');// connessione al db con my_sqli

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // dati passati dal form, senza il rischio di sql injection
    $username = $connessione->real_escape_string($_POST['username']);
    $password = $connessione->real_escape_string($_POST['password']);

    $sql_select = "SELECT * FROM utente WHERE username = '$username' ";
    $result = $connessione->query($sql_select);

    // routine verifica accesso e ruolo dell'utente
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            session_start();
            if ($row['ruolo'] == 'Admin') {// login Admin

                // creazione 2fa code
                $code_2fa = strval(mt_rand(100000000, 999999999));
                $hashed_code_2fa = password_hash($code_2fa, PASSWORD_BCRYPT);
                $id_utente = $row['id_utente'];

                // salvataggio 2fa code in db
                $update_query = "UPDATE 2FA_auth SET 2FA_code = ?, 2FA_expire = ADDTIME(CURRENT_TIMESTAMP,900), 2FA_code_usage = 'NOT_USED' WHERE id_utente = ?";
                $stmt = $connessione->prepare($update_query);
                $stmt->bind_param("si", $hashed_code_2fa, $id_utente);
                $stmt->execute();

                // Invia l'email con 2fa code 
                $emailto = $row['email'];


                $subject = "Libriperera Login";
                $message = "Here your code for Admin Authentication in Libriperera!\n\n 2fa code: $code_2fa \n\n This code expires in 15 minutes";
                $headers = "From: libriperera@booksite.com";
                mail($emailto, $subject, $message, $headers);

                // Redirect per la verifica del 2fa code

                $emailCensurata = censuraEmail($emailto); // Censurare l'email con asterischi tranne le prime due lettere
                header("Location: admin/2fa_codegeneration.php?username=" . urlencode($row['username']) . "&id_utente=" . urlencode($row['id_utente']) . "&code_2fa=" . urlencode($code_2fa) . "&hashed_code_2fa=" . urlencode($hashed_code_2fa) . "&emailCensurata=" . urlencode($emailCensurata));
                exit();

            } elseif ($row['ruolo'] == 'Cliente') {// login cliente
                $_SESSION['loggato'] = true;
                $_SESSION['id_cliente'] = $row['id_utente'];
                $_SESSION['username'] = $row['username'];
                header("location: home.php");
                exit();
            }

        // routine accesso errato
        } else {
            header("location: error-page/nopassword.html");
            exit();
        }
    } else {
        header("location: error-page/nousername.html");
        exit();
    }
}

$connessione->close();
?>

```

### verifica del secondo fattore

2fa_verify.php
```php
<?php
// connessione al db
require("inc/db.php");

// Dati passati da form
$idutente = $_POST['id_utente'];
$code_2fa = $_POST['code_2fa'];

// Hashing del codice 2FA con Bcrypt
$hashed_code_2fa = password_hash($code_2fa, PASSWORD_BCRYPT);

// routine di verifica del 2FA code per l'utente che sta cercando di accedere a suo account.
$query = $conn->prepare("SELECT * FROM 2FA_auth WHERE id_utente = :idutente AND 2FA_EXPIRE > CURRENT_TIMESTAMP");
$query->bindParam(":idutente", $idutente);
$query->execute();
$response = array();
if ($query->rowCount() > 0) {
    $row = $query->fetch(PDO::FETCH_ASSOC);
    if (password_verify($code_2fa, $row['2FA_code']) && ($row['2FA_code_usage'] != 'USED')) {

        $response = array("success" => true);
        // Codice 2FA corretto, aggiorna il database e impostazioni di sessione
        $query2 = $conn->prepare("UPDATE 2FA_auth SET 2FA_code_usage = 'USED' WHERE id_utente = :idutente AND 2FA_EXPIRE > CURRENT_TIMESTAMP");
        $query2->bindParam(":idutente", $idutente);
        $query2->execute();
        session_start();
        $_SESSION['loggato2'] = true;
        $_SESSION['loggato'] = true;
        $_SESSION['id_cliente'] = $idutente;
        $_SESSION['username'] = $row['username'];
        
    } else { // gestione codice errato
        if($row['2FA_code_usage'] == 'USED'){
            $response = array("success" => false, "message" => "Codice Usato o errato");

            header('Content-Type: application/json'); 

        } else{

            $response = array("success" => false, "message" => "Codice Errato");
            header('Content-Type: application/json'); 

        }
    }
} else {
    if($row['2FA_EXPIRE'] < time()){
    
        $response = array("success" => false, "message" => "Codice scaduto");
        header('Content-Type: application/json'); 

    }else{ 
        
        $response = array("success" => false, "message" => "Codice errato");
        header('Content-Type: application/json');

     }    
}
echo json_encode($response);
exit;
?>
```

### Funzioni particolari
funzione per la censurare parzialmente la mail per suggerire all'utente a quale mail è stata inviato il codice 2fa.
    
```php
function censuraEmail($email) { // funziona per censurare parzialmente le mail
    list($parteLocale, $dominio) = explode('@', $email);
    $primeDueLettere = substr($parteLocale, 0, 2);
    $lunghezzaParteLocale = strlen($parteLocale) - 2;
    $asterischi = str_repeat('*', $lunghezzaParteLocale);
    $emailCensurata = $primeDueLettere . $asterischi . '@' . $dominio;
    return $emailCensurata;
}

```
