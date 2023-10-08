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

Autenticazione a due fattori per l'accesso da amministratore all'interfaccia di gestione di un e-commerce.

## Script principali

```php
<?php
// Esempio di codice PHP relativo a una funzionalità specifica.
// Puoi aggiungere più blocchi di codice per mostrare diverse parti del progetto.
?>
```
