<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/contact.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <title>DriveMarket - Kontakt</title>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a href="../index.html"><div class="logo">DriveMarket</div></a>
        <nav class="menu" style="padding-left: 40px;">
            <a href="posts.php">Produkty</a>
            <a href="about.php">O nas</a>
            <a href="contact.php" class="active">Kontakt</a>
        </nav>
        <div class="auth-container">
            <?php if (!isset($_COOKIE['token'])): ?>
            <div class="auth-buttons">
                <a href="login.php" class="auth-btn">Zaloguj się</a>
                <a href="register.php" class="auth-btn">Zarejestruj się</a>
            </div>
            <?php else: ?>
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
            <?php endif; ?>
        </div>
    </header>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-container">
            <div class="contact-info">
                <h1>Skontaktuj się z nami</h1>
                <p class="subtitle">Masz pytania? Jesteśmy tutaj, aby pomóc!</p>
                
                <div class="info-items">
                    <div class="info-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <div class="info-content">
                            <h3>Telefon</h3>
                            <p>+48 123 456 789</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <div class="info-content">
                            <h3>Email</h3>
                            <p>kontakt@drivemarket.pl</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <div class="info-content">
                            <h3>Adres</h3>
                            <p>ul. Przykładowa 123<br>00-001 Warszawa</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <div id="formMessage" class="message" style="display: none;"></div>
                <form id="contactForm" action="../handlers/contact-handler.php" method="POST">
                    <div class="form-group">
                        <label for="name">Imię i nazwisko</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Wiadomość</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Wyślij wiadomość</button>
                </form>
            </div>
        </div>
    </section>

<!-- Footer -->
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
    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const messageDiv = document.getElementById('formMessage');
            
            fetch('../handlers/contact-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                messageDiv.textContent = data;
                messageDiv.style.display = 'block';
                messageDiv.className = 'message ' + (data.includes('pomyślnie') ? 'success-message' : 'error-message');
                
                if (data.includes('pomyślnie')) {
                    this.reset();
                }
            })
            .catch(error => {
                messageDiv.textContent = 'Wystąpił błąd podczas wysyłania wiadomości. Spróbuj ponownie później.';
                messageDiv.style.display = 'block';
                messageDiv.className = 'message error-message';
            });
        });
    </script>
</body>
</html>
