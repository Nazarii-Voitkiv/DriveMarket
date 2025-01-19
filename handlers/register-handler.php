<?php

use Firebase\JWT\JWT;

require '../vendor/autoload.php';
$config = include('../config.php');

// Tworzenie bazy danych, jeżeli nie istnieje
createDatabaseIfNotExists($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);

// Nawiązanie połączenia z bazą danych
$connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tworzenie tabeli użytkowników, jeżeli nie istnieje
createUserTableIfNotExists($connect);
// Tworzenie tabeli ogłoszeń, jeżeli nie istnieje
createListingsTableIfNotExists($connect);

// Sprawdzanie, czy formularz rejestracji został przesłany
if (isset($_POST["register"])) {
    try {
        // Ponowne nawiązanie połączenia z bazą danych
        $connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        createUserTableIfNotExists($connect);

        // Walidacja pól formularza
        if (empty($_POST["username"])) {
            header("Location: ../templates/register.php?error=empty_username");
            exit();
        } elseif (empty($_POST["email"])) {
            header("Location: ../templates/register.php?error=empty_email");
            exit();
        } elseif (empty($_POST["password"])) {
            header("Location: ../templates/register.php?error=empty_password");
            exit();
        }

        // Sprawdzenie, czy użytkownik już istnieje
        $query = "SELECT * FROM user WHERE user_email = ?";
        $statement = $connect->prepare($query);
        $statement->execute([$_POST["email"]]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            header("Location: ../templates/register.php?error=email_exists");
            exit();
        }

        // Hashowanie hasła
        $hashed_password = password_hash($_POST["password"], PASSWORD_BCRYPT);

        // Wstawienie nowego użytkownika do bazy danych
        $insert_query = "INSERT INTO user (user_name, user_email, user_password) VALUES (?, ?, ?)";
        $statement = $connect->prepare($insert_query);
        
        if ($statement->execute([$_POST["username"], $_POST["email"], $hashed_password])) {
            // Pobranie ID nowo utworzonego użytkownika
            $user_id = $connect->lastInsertId();
            $key = $config['jwt_key'];
            // Generowanie tokenu JWT
            $token = JWT::encode(
                array(
                    'iat' => time(),
                    'nbf' => time(),
                    'exp' => time() + 3600,
                    'data' => array(
                        'user_id' => $user_id,
                        'user_name' => $_POST["username"]
                    )
                ),
                $key,
                'HS256'
            );

            // Ustawienie ciasteczka z tokenem
            setcookie("token", $token, [
                'expires' => time() + 3600,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => false,
                'samesite' => 'Lax'
            ]);

            // Przekierowanie na stronę główną po pomyślnej rejestracji
            header('location:../index.html');
            exit();
        } else {
            // Obsługa niepowodzenia rejestracji
            header("Location: ../templates/register.php?error=registration_failed");
            exit();
        }
    } catch (PDOException $e) {
        // Obsługa błędu bazy danych
        header("Location: ../templates/register.php?error=database_error");
        exit();
    }
}
?>