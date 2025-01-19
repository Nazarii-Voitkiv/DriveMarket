<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveMarket - O nas</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/about.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <title>O nas - DriveMarket</title>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a href="../index.html"><div class="logo">DriveMarket</div></a>
        <nav class="menu" style="padding-left: 40px;">
            <a href="posts.php">Produkty</a>
            <a href="about.php" class="active">O nas</a>
            <a href="contact.php">Kontakt</a>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Poznaj DriveMarket</h1>
            <p>Twoje zaufane miejsce dla wszystkich części samochodowych</p>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="about-container">
            <div class="about-content">
                <h2>Nasza Historia</h2>
                <p>DriveMarket powstał z pasji do motoryzacji i chęci stworzenia platformy, która ułatwi kierowcom znalezienie odpowiednich części do ich pojazdów. Od 2020 roku nieustannie rozwijamy się, aby zapewnić najlepsze doświadczenia zakupowe dla naszych klientów.</p>
                
                <div class="stats">
                    <div class="stat-item">
                        <span class="stat-number" data-target="10000" data-suffix="+">0</span>
                        <span class="stat-label">Zadowolonych Klientów</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" data-target="50000" data-suffix="+">0</span>
                        <span class="stat-label">Części w Ofercie</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" data-target="99">0</span>
                        <span class="stat-label">Pozytywnych Opinii</span>
                    </div>
                </div>
            </div>

            <div class="mission-vision">
                <div class="mission">
                    <h3>Nasza Misja</h3>
                    <p>Dostarczanie wysokiej jakości części samochodowych w konkurencyjnych cenach, przy jednoczesnym zapewnieniu najlepszej obsługi klienta i fachowego doradztwa.</p>
                </div>
                <div class="vision">
                    <h3>Nasza Wizja</h3>
                    <p>Stać się wiodącą platformą e-commerce w branży motoryzacyjnej, wyznaczając standardy jakości i innowacji w sprzedaży części samochodowych.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="team-container">
            <h2>Nasz Zespół</h2>
            <p class="team-intro">Poznaj ekspertów, którzy stoją za sukcesem DriveMarket</p>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-image">
                        <img src="../images/team/nazar-img.jpg" alt="Nazar Voitkiv">
                    </div>
                    <h3>Nazar Voitkiv</h3>
                    <p class="member-role">PHP Backend Developer & Frontend Designer</p>
                    <p class="member-desc">Od 3 lat specjalizuję się w tworzeniu aplikacji internetowych przy użyciu PHP, HTML, CSS i JavaScript. Wdrażam innowacyjne rozwiązania technologiczne, aby zapewnić najlepsze doświadczenia naszych klientów.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                <rect x="2" y="9" width="4" height="12"></rect>
                                <circle cx="4" cy="4" r="2"></circle>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="team-member">
                    <div class="member-image">
                        <img src="../images/team/serhii-img.jpg" alt="Serhii Nesteruk">
                    </div>
                    <h3>Serhii Nesteruk</h3>
                    <p class="member-role">PHP Backend Developer & Frontend Developer</p>
                    <p class="member-desc">Od 3 lat specjalizuję się w tworzeniu aplikacji internetowych przy użyciu PHP, HTML, CSS i JavaScript. Wdrażam innowacyjne rozwiązania technologiczne, aby zapewnić najlepsze doświadczenia naszych klientów.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                <rect x="2" y="9" width="4" height="12"></rect>
                                <circle cx="4" cy="4" r="2"></circle>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="team-member">
                    <div class="member-image">
                        <img src="../images/team/konrad-img.jpg" alt="Konrad Lipiec">
                    </div>
                    <h3>Konrad Lipiec</h3>
                    <p class="member-role">Team Leader</p>
                    <p class="member-desc">Osoba odpowiedzialna za koordynację pracy zespołu i realizację projektów.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                <rect x="2" y="9" width="4" height="12"></rect>
                                <circle cx="4" cy="4" r="2"></circle>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="values-container">
            <h2>Nasze Wartości</h2>
            <div class="values-grid">
                <div class="value-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <h3>Jakość</h3>
                    <p>Oferujemy tylko sprawdzone i wysokiej jakości części samochodowe</p>
                </div>
                <div class="value-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 6v6l4 2"></path>
                    </svg>
                    <h3>Szybkość</h3>
                    <p>Gwarantujemy szybką realizację zamówień i profesjonalną obsługę</p>
                </div>
                <div class="value-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h3>Zaufanie</h3>
                    <p>Budujemy długotrwałe relacje oparte na wzajemnym zaufaniu</p>
                </div>
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
        // Counter Animation
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    element.textContent = target.toLocaleString() + (element.dataset.suffix || '');
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start).toLocaleString() + (element.dataset.suffix || '');
                }
            }, 16);
        }

        // Intersection Observer for triggering animation when stats are visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statsNumbers = entry.target.querySelectorAll('.stat-number');
                    statsNumbers.forEach(stat => {
                        const target = parseInt(stat.dataset.target);
                        animateCounter(stat, target);
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        // Start observing the stats section
        document.addEventListener('DOMContentLoaded', () => {
            const statsSection = document.querySelector('.stats');
            if (statsSection) {
                observer.observe(statsSection);
            }
        });
    </script>
</body>
</html>
