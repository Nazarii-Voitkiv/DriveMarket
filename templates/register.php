<?php
session_start();
if (isset($_COOKIE['token'])) {
    header('Location: ../index.html');
    exit();
}

require '../handlers/register-handler.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveMarket - Zarejestruj się</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/register.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">DriveMarket</div>
        <nav class="menu">
            <a href="../index.html">Strona główna</a>
            <a href="#">Produkty</a>
            <a href="#">Kontakt</a>
        </nav>
        <div class="auth-container">
            <div class="auth-buttons">
                <a href="login.php" class="auth-btn">Zaloguj się</a>
                <a href="register.php" class="auth-btn">Zarejestruj się</a>
            </div>
            <div class="user-menu">
                <div class="avatar-circle" onclick="toggleDropdown()">
                    <span id="userInitials">A</span>
                </div>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profile.php">Profil</a>
                    <a href="create-listing.php">Dodaj ogłoszenie</a>
                    <a href="my-listings.php">Moje ogłoszenia</a>
                    <button onclick="logout()">Wyloguj się</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Register Section -->
    <section class="register">
        <h1>Zarejestruj się</h1>
        <?php
        if (isset($_GET['error'])) {
            switch($_GET['error']) {
                case 'email_exists':
                    echo '<p class="error-message">Użytkownik z takim adresem e-mail już istnieje!</p>';
                    break;
                case 'empty_username':
                    echo '<p class="error-message">Proszę wprowadzić nazwę użytkownika!</p>';
                    break;
                case 'empty_email':
                    echo '<p class="error-message">Proszę wprowadzić adres e-mail!</p>';
                    break;
                case 'empty_password':
                    echo '<p class="error-message">Proszę wprowadzić hasło!</p>';
                    break;
                case 'registration_failed':
                    echo '<p class="error-message">Błąd rejestracji. Spróbuj ponownie.</p>';
                    break;
                case 'database_error':
                    echo '<p class="error-message">Błąd bazy danych. Spróbuj później.</p>';
                    break;
            }
        }
        ?>
        <form action="#" method="POST">
            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Hasło:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Potwierdź hasło:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" class="auth-btn" name="register" onclick="return validatePasswords()">Zarejestruj się</button>
        </form>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 DriveMarket. Wszystkie prawa zastrzeżone.</p>
    </footer>

    <script src="../scripts/auth.js"></script>
    <script>
    function validatePasswords() {
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirm_password').value;
        if (password !== confirmPassword) {
            alert('Hasła nie pasują do siebie!');
            return false;
        }
        return true;
    }
    </script>
</body>
</html>
