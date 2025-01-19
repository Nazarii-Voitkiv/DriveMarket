<?php
require "../vendor/autoload.php";
$config = include('../config.php');

// Tworzenie bazy danych, jeśli nie istnieje
createDatabaseIfNotExists($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);

// Nawiązanie połączenia z bazą danych
$connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tworzenie tabeli użytkowników, jeśli nie istnieje
createUserTableIfNotExists($connect);

// Tworzenie tabeli ogłoszeń, jeśli nie istnieje
createListingsTableIfNotExists($connect);

use Firebase\JWT\JWT;
use Google\Client;

// Konfiguracja klienta Google
$client = new Client();
$client->setClientId($config['google_client_id']);
$client->setClientSecret($config['google_client_secret']);
$client->setRedirectUri("http://localhost/DriveMarket/handlers/login-handler.php");
$client->addScope("email");
$client->addScope("profile");

// Przekierowanie do strony logowania Google, jeśli zainicjowano logowanie
if (isset($_GET['google-login'])) {
    header("Location: " . $client->createAuthUrl());
    exit();
}

// Przetwarzanie kodu autoryzacyjnego Google
if (isset($_GET['code'])) {
    try {
        // Pobranie tokenu dostępu za pomocą kodu autoryzacyjnego
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        // Obsługa błędów OAuth
        if (isset($token['error'])) {
            throw new Exception('OAuth Error: ' . $token['error_description']);
        }
        
        $client->setAccessToken($token['access_token']);

        // Pobieranie danych użytkownika z Google
        $google_service = new Google\Service\Oauth2($client);
        $data = $google_service->userinfo->get();

        $user_email = $data->email;
        $user_name = $data->name;
        $avatar_url = $data->picture;

        // Ponowne nawiązanie połączenia z bazą danych
        $connect = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
            $config['db_username'], 
            $config['db_password']
        );
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        createUserTableIfNotExists($connect);

        // Sprawdzenie, czy użytkownik istnieje w bazie danych
        $query = "SELECT * FROM user WHERE user_email = ?";
        $statement = $connect->prepare($query);
        $statement->execute([$user_email]);
        $existing_user = $statement->fetch(PDO::FETCH_ASSOC);

        // Dodanie nowego użytkownika, jeśli nie istnieje
        if (!$existing_user) {
            $insert_query = "INSERT INTO user (user_name, user_email, avatar_url) VALUES (?, ?, ?)";
            $statement = $connect->prepare($insert_query);
            $statement->execute([$user_name, $user_email, $avatar_url]);
            
            $user_id = $connect->lastInsertId();
        } else {
            $user_id = $existing_user['user_id'];
        }

        // Generowanie tokenu JWT dla użytkownika
        $jwt_token = JWT::encode(
            [
                'iat' => time(),
                'exp' => time() + 3600,
                'data' => [
                    'user_id' => $user_id,
                    'user_name' => $user_name,
                    'avatar_url' => $avatar_url
                ]
            ],
            $config['jwt_key'],
            'HS256'
        );

        // Ustawienie ciasteczka z tokenem JWT
        setcookie("token", $jwt_token, [
            'expires' => time() + 3600,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => false,
            'samesite' => 'Lax'
        ]);

        // Przekierowanie na stronę główną
        header("Location: ../index.html");
        exit();

    } catch (Exception $e) {
        // Przekierowanie na stronę logowania z komunikatem o błędzie
        header("Location: ../templates/login.php?error=google_auth_failed");
        exit();
    }
}
if (isset($_POST["login"])) {
    // Nawiązanie połączenia z bazą danych przy użyciu PDO
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tworzenie tabeli użytkowników, jeśli nie istnieje
    createUserTableIfNotExists($connect);

    // Sprawdzenie, czy pole email jest puste
    if (empty($_POST["email"])) {
        // Przekierowanie na stronę logowania z komunikatem o błędzie
        header("Location: ../templates/login.php?error=empty_email");
        exit();
    } elseif (empty($_POST["password"])) { // Sprawdzenie, czy pole hasło jest puste
        // Przekierowanie na stronę logowania z komunikatem o błędzie
        header("Location: ../templates/login.php?error=empty_password");
        exit();
    } else {
        // Przygotowanie zapytania SQL do pobrania użytkownika o podanym adresie email
        $query = "SELECT * FROM user WHERE user_email = ?";
        $statement = $connect->prepare($query);
        $statement->execute([$_POST["email"]]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        // Sprawdzenie, czy użytkownik istnieje
        if ($data) {
            // Weryfikacja hasła
            if (password_verify($_POST['password'], $data['user_password'])) {
                // Generowanie tokenu JWT dla użytkownika
                $token = JWT::encode(
                    [
                        'iat' => time(), // Czas utworzenia tokenu
                        'nbf' => time(), // Czas od którego token jest ważny
                        'exp' => time() + 3600, // Czas wygaśnięcia tokenu (1 godzina)
                        'data' => [
                            'user_id' => $data['user_id'], // ID użytkownika
                            'user_name' => $data['user_name'] // Nazwa użytkownika
                        ]
                    ],
                    $config['jwt_key'], // Klucz JWT do podpisywania tokenu
                    'HS256' // Algorytm używany do podpisu
                );

                // Ustawienie ciasteczka z tokenem JWT
                setcookie("token", $token, [
                    'expires' => time() + 3600, // Czas wygaśnięcia ciasteczka (1 godzina)
                    'path' => '/', // Ścieżka ciasteczka
                    'domain' => '', // Domena ciasteczka (bieżąca)
                    'secure' => true, // Ciasteczko tylko dla HTTPS
                    'httponly' => false, // Ciasteczko nie jest tylko dla HTTP
                    'samesite' => 'Lax' // Polityka SameSite dla ciasteczka
                ]);

                // Przekierowanie do strony głównej po pomyślnym logowaniu
                header('location:../index.html');
                exit();
            } else {
                // Przekierowanie na stronę logowania z komunikatem o błędnym haśle
                header("Location: ../templates/login.php?error=wrong_password");
                exit();
            }
        } else {
            // Przekierowanie na stronę logowania z komunikatem o błędnym adresie email
            header("Location: ../templates/login.php?error=wrong_email");
            exit();
        }
    }
}
?>