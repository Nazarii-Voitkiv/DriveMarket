<?php
// Ustawiamy flagę raportowania błędów na E_ALL i włączamy wyświetlanie błędów
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Włączamy autoloadera vendorów
require __DIR__ . '/../vendor/autoload.php';
// Wczytujemy konfigurację
$config = include(__DIR__ . '/../config.php');

// Sprawdzamy, czy został wysłany formularz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobieramy dane z formularza
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');

    // Sprawdzamy, czy wszystkie pola zostały wypełnione
    if (empty($name) || empty($email) || empty($message)) {
        // Zwracamy błąd 400, jeśli coś jest nieprawidłowo
        http_response_code(400);
        echo "Wypełnij wszystkie pola.";
        exit;
    }

    // Tworzymy wiadomość email
    try {
        $emailToSend = new \SendGrid\Mail\Mail(); 
        $emailToSend->setFrom("serhiinesteruk0@gmail.com", $name); 
        $emailToSend->setSubject("Ta wiadomość z formularza opinii");
        $emailToSend->addTo("serhiinesteruk0@gmail.com", "Serhii Nesteruk"); 
        $emailToSend->addContent(
            "text/plain", 
            "Ім'я: $name\nEmail: $email\nWiadomość:\n$message"
        );
        $emailToSend->addContent(
            "text/html", 
            "<strong>Ім'я:</strong> $name<br><strong>Email:</strong> $email<br><br><strong>Powiadomienie:</strong><br>$message"
        );

        // Ustawiamy klucz API SendGrid
        $apiKey = $config['sendgrid_api_key']; 
        // Tworzymy instancję SendGrid
        $sendgrid = new \SendGrid($apiKey);

        // Wysyłamy wiadomość
        $response = $sendgrid->send($emailToSend);

        // Sprawdzamy, czy wiadomość została pomyślnie wysłana
        if ($response->statusCode() === 202) {
            echo "Dziekujemy, $name! Twoja wiadomość została pomyślnie wysłana.";
        } else {
            // Zwracamy błąd 500, jeśli coś jest nieprawidłowo
            http_response_code(500);
            echo "Niestety, nie można wysłać wiadomości. Kod stanu: " . $response->statusCode();
            echo "\nOdpowiedź serwera: " . $response->body();
        }
    } catch (Exception $e) {
        // Zwracamy błąd 500, jeśli coś jest nieprawidłowo
        http_response_code(500);
        echo 'Błąd: ' . $e->getMessage();
    }
} else {
    // Zwracamy błąd 405, jeśli próbowano wysłać formularz inną metodą
    http_response_code(405);
    echo "Ta metoda jest niedozwolona.";
}
?>
