
<?php
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
