<?php
require "../vendor/autoload.php";
$config = include('../config.php');

createDatabaseIfNotExists($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);

$connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
createUserTableIfNotExists($connect);
createListingsTableIfNotExists($connect);

use Firebase\JWT\JWT;
use Google\Client;

$client = new Client();
$client->setClientId($config['google_client_id']);
$client->setClientSecret($config['google_client_secret']);
$client->setRedirectUri("http://localhost/DriveMarket/handlers/login-handler.php");
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['google-login'])) {
    header("Location: " . $client->createAuthUrl());
    exit();
}

if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            throw new Exception('OAuth Error: ' . $token['error_description']);
        }
        
        $client->setAccessToken($token['access_token']);

        $google_service = new Google\Service\Oauth2($client);
        $data = $google_service->userinfo->get();

        $user_email = $data->email;
        $user_name = $data->name;
        $avatar_url = $data->picture;

        $connect = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
            $config['db_username'], 
            $config['db_password']
        );
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        createUserTableIfNotExists($connect);

        $query = "SELECT * FROM user WHERE user_email = ?";
        $statement = $connect->prepare($query);
        $statement->execute([$user_email]);
        $existing_user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$existing_user) {
            $insert_query = "INSERT INTO user (user_name, user_email, avatar_url) VALUES (?, ?, ?)";
            $statement = $connect->prepare($insert_query);
            $statement->execute([$user_name, $user_email, $avatar_url]);
            
            $user_id = $connect->lastInsertId();
        } else {
            $user_id = $existing_user['user_id'];
        }

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

    } catch (Exception $e) {
        header("Location: ../templates/login.php?error=google_auth_failed");
        exit();
    }
}

if (isset($_POST["login"])) {
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    createUserTableIfNotExists($connect);

    if (empty($_POST["email"])) {
        header("Location: ../templates/login.php?error=empty_email");
        exit();
    } elseif (empty($_POST["password"])) {
        header("Location: ../templates/login.php?error=empty_password");
        exit();
    } else {
        $query = "SELECT * FROM user WHERE user_email = ?";
        $statement = $connect->prepare($query);
        $statement->execute([$_POST["email"]]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            if (password_verify($_POST['password'], $data['user_password'])) {
                $token = JWT::encode(
                    array(
                        'iat' => time(),
                        'nbf' => time(),
                        'exp' => time() + 3600,
                        'data' => array(
                            'user_id' => $data['user_id'],
                            'user_name' => $data['user_name']
                        )
                    ),
                    $config['jwt_key'],
                    'HS256'
                );

                setcookie("token", $token, [
                    'expires' => time() + 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => true,
                    'httponly' => false,
                    'samesite' => 'Lax'
                ]);
                header('location:../index.html');
                exit();
            } else {
                header("Location: ../templates/login.php?error=wrong_password");
                exit();
            }
        } else {
            header("Location: ../templates/login.php?error=wrong_email");
            exit();
        }
    }
}
?>