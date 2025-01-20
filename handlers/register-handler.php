<?php

use Firebase\JWT\JWT;

require '../vendor/autoload.php';
$config = include('../config.php');

createDatabaseIfNotExists($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);

$connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
createUserTableIfNotExists($connect);
createListingsTableIfNotExists($connect);

if (isset($_POST["register"])) {
    try {
        $connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        createUserTableIfNotExists($connect);

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

        $query = "SELECT * FROM user WHERE user_email = ?";
        $statement = $connect->prepare($query);
        $statement->execute([$_POST["email"]]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            header("Location: ../templates/register.php?error=email_exists");
            exit();
        }

        $hashed_password = password_hash($_POST["password"], PASSWORD_BCRYPT);

        $insert_query = "INSERT INTO user (user_name, user_email, user_password) VALUES (?, ?, ?)";
        $statement = $connect->prepare($insert_query);
        
        if ($statement->execute([$_POST["username"], $_POST["email"], $hashed_password])) {
            $user_id = $connect->lastInsertId();
            $key = $config['jwt_key'];
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

            setcookie("token", $token, [
                'expires' => time() + 3600,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => false,
                'samesite' => 'Lax'
            ]);

            header('location:../index.html');
            exit();
        } else {
            header("Location: ../templates/register.php?error=registration_failed");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: ../templates/register.php?error=database_error");
        exit();
    }
}
?>