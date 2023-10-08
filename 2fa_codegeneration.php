<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Autenticazione a due fattori</title>
    <style>
        /* Copia il codice CSS qui */
        form {
            max-width: 300px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        input[type="text"] {
            width: 90%;
            padding: 10px;
            margin-top: 15px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button[type="submit"] {
            background-color: orange;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #ff9900;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .alert-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>

<?php 
    // Include database connection
    require("inc/db.php");


?>


<?php

    $id_utente = $_GET['id_utente'];
    $code_2fa = $_GET['code_2fa'];
    $hashed_code_2fa = $_GET['hashed_code_2fa'];
    $emailCensurata = $_GET['emailCensurata'];
	?>
    <!-- Il tuo modulo HTML qui -->
    <form id="MyForm" method="post">
        <h1>Verifica a Due fattori</h1>
        <small>È stato inviato un codice di verifica alla tua email: <?php echo $emailCensurata ?></small>
        <input type="hidden" id="id_utente" name="id_utente" value="<?=$id_utente;?>">
        <input type="text" id="code_2fa" placeholder="Authcode" name="code_2fa">
        <button type="submit" name="login" >Conferma</button>
        <div id="messaggio-errore"></div>
    </form>


       <!-- Script JavaScript  -->
       <script>
        $(document).ready(function() {
            $('#MyForm').submit(function(event) {
                event.preventDefault(); // Evita l'invio del modulo di default

                var formData = $(this).serialize(); // Serializza i dati del modulo

                $.ajax({
                    url: '2fa_verify.php', 
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if(response.success){
                            window.location.href = '../admin/index.php';
                        }
                        else{
                            $('#messaggio-errore').text(response.message).css('color', 'red');
                        }
                        
                    }
                });
            });
        });
        </script>

</body>
</html>


