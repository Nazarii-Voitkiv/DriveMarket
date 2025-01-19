<?php
require "../vendor/autoload.php";
$config = include('../config.php');

// Tworzenie bazy danych, jeżeli nie istnieje
createDatabaseIfNotExists($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);

// Połączenie z bazą danych
$connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tworzenie tabeli użytkowników i ogłoszeń, jeżeli nie istnieją
createUserTableIfNotExists($connect);
createListingsTableIfNotExists($connect);

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Firebase\JWT\JWT;

// Rozpoczęcie sesji
session_start();

// Konfiguracja SDK Facebooka
$fb = new Facebook([
    'app_id' => $config['facebook_app_id'],
    'app_secret' => $config['facebook_app_secret'],
    'default_graph_version' => 'v12.0'
]);

// Uzyskanie helpera do logowania
$helper = $fb->getRedirectLoginHelper();

// Obsługa stanu
if (isset($_GET['state'])) {
    $helper->getPersistentDataHandler()->set('state', $_GET['state']);
}

// Przekierowanie do logowania na Facebooku
if (isset($_GET['facebook-login'])) {
    $permissions = ['email'];
    $loginUrl = $helper->getLoginUrl("http://localhost/DriveMarket/handlers/facebook-login-handler.php", $permissions);
    header("Location: " . $loginUrl);
    exit();
}

try {
    if (isset($_GET['code'])) {
        // Uzyskanie tokenu dostępu
        $accessToken = $helper->getAccessToken();
        
        if (isset($accessToken)) {
            // Pobranie danych użytkownika z Facebooka
            $response = $fb->get('/me?fields=email,name,picture.type(large)', $accessToken);
            $user = $response->getGraphUser();
            
            $user_email = $user->getEmail();
            $user_name = $user->getName();
            $avatar_url = $user['picture']['url'];

            // Sprawdzenie, czy użytkownik istnieje w bazie danych
            $query = "SELECT * FROM user WHERE user_email = ?";
            $statement = $connect->prepare($query);
            $statement->execute([$user_email]);
            $existing_user = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$existing_user) {
                // Dodanie nowego użytkownika do bazy danych
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
            header("Location: ../index.html");
            exit();
        }
    }
} catch(Exception $e) {
    // Obsługa błędów i wyświetlenie komunikatu
    echo 'Error: ' . $e->getMessage();
    exit();
}