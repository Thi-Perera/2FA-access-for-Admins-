<?php

function censuraEmail($email) { // funziona per censurare parzialmente le mail
    list($parteLocale, $dominio) = explode('@', $email);
    $primeDueLettere = substr($parteLocale, 0, 2);
    $lunghezzaParteLocale = strlen($parteLocale) - 2;
    $asterischi = str_repeat('*', $lunghezzaParteLocale);
    $emailCensurata = $primeDueLettere . $asterischi . '@' . $dominio;
    return $emailCensurata;
}

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
