<?php
session_start();
if (isset($_COOKIE['token'])) {
    header('Location: ../index.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveMarket - Zaloguj się</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/login.css">
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

    <!-- Login Section -->
    <section class="login">
        <h1>Zaloguj się</h1>
        <?php
        if (isset($_GET['error'])) {
            switch($_GET['error']) {
                case 'empty_email':
                    echo '<p class="error-message">Proszę wprowadzić adres e-mail!</p>';
                    break;
                case 'empty_password':
                    echo '<p class="error-message">Proszę wprowadzić hasło!</p>';
                    break;
                case 'wrong_password':
                    echo '<p class="error-message">Nieprawidłowe hasło!</p>';
                    break;
                case 'wrong_email':
                    echo '<p class="error-message">Nieprawidłowy adres e-mail!</p>';
                    break;
            }
        }
        ?>
        <form action="../handlers/login-handler.php" method="POST">
            <label for="username">E-mail:</label>
            <input type="email" id="username" name="email" required>

            <label for="password">Hasło:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="auth-btn" name="login">Zaloguj się</button>
            <h2>Or login with:</h2>
            <a href="../handlers/login-handler.php?google-login=true" class="google-login-btn">Google</a>
            <a href="../handlers/facebook-login-handler.php?facebook-login=true" class="facebook-login-btn">Facebook</a>
        </form>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 DriveMarket. Wszystkie prawa zastrzeżone.</p>
    </footer>

    <script src="../scripts/auth.js"></script>
</body>
</html>
