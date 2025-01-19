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
    <link rel="stylesheet" href="../styles/auth.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a href="../index.html"><div class="logo">DriveMarket</div></a>
        <nav class="menu" style="padding-left: 40px;">
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
            <a href="../handlers/login-handler.php?google-login=true" class="google-login-btn">
                <svg class="social-icon" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M21.35 11.1h-9.17v2.73h6.51c-.33 3.81-3.5 5.44-6.5 5.44C8.36 19.27 5 16.25 5 12c0-4.1 3.2-7.27 7.2-7.27 3.09 0 4.9 1.97 4.9 1.97L19 4.72S16.56 2 12.1 2C6.42 2 2.03 6.8 2.03 12c0 5.05 4.13 10 10.22 10 5.35 0 9.25-3.67 9.25-9.09 0-1.15-.15-1.81-.15-1.81z"/>
                </svg>
                Continue with Google
            </a>
            <a href="../handlers/facebook-login-handler.php?facebook-login=true" class="facebook-login-btn">
                <svg class="social-icon" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12 2.04c-5.5 0-10 4.49-10 10.02c0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89c1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 0 0 8.44-9.9c0-5.53-4.5-10.02-10-10.02z"/>
                </svg>
                Continue with Facebook
            </a>
        </form>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>DriveMarket</h3>
                <p>Twój najlepszy sklep online z częściami samochodowymi. Znajdź wszystko, czego potrzebujesz do swojego pojazdu.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Szybkie linki</h4>
                <nav class="footer-links">
                    <a href="#">Strona główna</a>
                    <a href="templates/posts.php">Produkty</a>
                    <a href="#">O nas</a>
                    <a href="#">Kontakt</a>
                </nav>
            </div>
            <div class="footer-section">
                <h4>Wsparcie</h4>
                <nav class="footer-links">
                    <a href="#">FAQ</a>
                    <a href="#">Polityka prywatności</a>
                    <a href="#">Regulamin</a>
                    <a href="#">Dostawa</a>
                </nav>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 DriveMarket. Wszystkie prawa zastrzeżone.</p>
        </div>
    </footer>

    <script src="../scripts/auth.js"></script>
</body>
</html>
