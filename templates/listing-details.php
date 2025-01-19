<?php
session_start();
require '../vendor/autoload.php';
$config = include('../config.php');

// Get listing ID from URL
$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$listing_id) {
    header('Location: posts.php');
    exit;
}

// Connect to database
try {
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}",
        $config['db_username'],
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get listing details
    $query = "SELECT * FROM listings WHERE listing_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$listing) {
        header('Location: posts.php');
        exit;
    }

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveMarket - <?php echo htmlspecialchars($listing['brand'] . ' ' . $listing['model']); ?></title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/listing-details.css">
    <link rel="stylesheet" href="../styles/footer.css">
</head>
<body>
    <header class="header">
        <a href="../index.html"><div class="logo">DriveMarket</div></a>
        <nav class="menu" style="padding-left: 40px;">
            <a href="../index.html">Strona główna</a>
            <a href="posts.php">Ogłoszenia</a>
            <a href="#">Kontakt</a>
        </nav>
        <div class="auth-container">
            <div class="auth-buttons">
                <a href="login.php" class="auth-btn">Zaloguj się</a>
                <a href="register.php" class="auth-btn">Zarejestruj się</a>
            </div>
            <div class="user-menu" style="display: none;">
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

    <main class="listing-details">
        <div class="listing-container">
            <h1><?php echo htmlspecialchars($listing['brand'] . ' ' . $listing['model']); ?></h1>
            
            <div class="photos-gallery">
                <?php
                if (!empty($listing['images'])) {
                    $images = json_decode($listing['images'], true);
                    if (is_array($images) && count($images) > 0) {
                        foreach ($images as $image) {
                            echo '<div class="photo-item">';
                            echo '<img src="../' . htmlspecialchars($image) . '" alt="Zdjęcie pojazdu" />';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="no-photos">Brak zdjęć</div>';
                    }
                } else {
                    echo '<div class="no-photos">Brak zdjęć</div>';
                }
                ?>
            </div>

            <div class="listing-info">
                <div class="price-section">
                    <h2>Cena</h2>
                    <div class="price">
                        <?php 
                        $formattedPrice = isset($listing['price']) 
                            ? number_format($listing['price'], 0, '.', ' ') 
                            : '—';
                        echo '<strong>' . $formattedPrice . '</strong> PLN';
                        if (isset($listing['price_type']) && $listing['price_type'] === 'netto') {
                            echo ' <span class="price-type">(netto)</span>';
                        }
                        ?>
                    </div>
                </div>

                <div class="details-section">
                    <h2>Szczegóły pojazdu</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="label">Marka:</span>
                            <span class="value"><?php echo htmlspecialchars($listing['brand'] ?? '—'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Model:</span>
                            <span class="value"><?php echo htmlspecialchars($listing['model'] ?? '—'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Rok produkcji:</span>
                            <span class="value"><?php echo htmlspecialchars($listing['prod_year'] ?? '—'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Przebieg:</span>
                            <span class="value"><?php echo number_format($listing['mileage'] ?? 0, 0, '.', ' '); ?> km</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">VIN:</span>
                            <span class="value"><?php echo htmlspecialchars($listing['vin'] ?? '—'); ?></span>
                        </div>
                        <?php if (!empty($listing['reg_number']) && empty($listing['hide_reg_info'])): ?>
                        <div class="detail-item">
                            <span class="label">Numer rejestracyjny:</span>
                            <span class="value"><?php echo htmlspecialchars($listing['reg_number']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <span class="label">Stan:</span>
                            <span class="value"><?php echo $listing['damaged'] === 'tak' ? 'Uszkodzony' : 'Nieuszkodzony'; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Importowany:</span>
                            <span class="value"><?php echo $listing['imported'] === 'tak' ? 'Tak' : 'Nie'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="details-section">
                    <h2>Dane techniczne</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="label">Rodzaj paliwa:</span>
                            <span class="value"><?php echo htmlspecialchars($listing['fuel_type'] ?? '—'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Typ nadwozia:</span>
                            <span class="value"><?php echo htmlspecialchars($listing['body_type'] ?? '—'); ?></span>
                        </div>
                        <?php if (!empty($listing['equipment'])): ?>
                        <div class="detail-item full-width">
                            <span class="label">Wyposażenie:</span>
                            <div class="equipment-list">
                                <?php
                                $equipment = json_decode($listing['equipment'], true);
                                if (is_array($equipment)) {
                                    foreach ($equipment as $item) {
                                        echo "<span class='equipment-item'>" . htmlspecialchars($item) . "</span>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($listing['description'])): ?>
                <div class="details-section">
                    <h2>Opis</h2>
                    <div class="description">
                        <?php echo nl2br(htmlspecialchars($listing['description'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="details-section">
                    <h2>Kontakt</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="label">Telefon:</span>
                            <span class="value contact-phone"><?php echo htmlspecialchars($listing['seller_phone'] ?? '—'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Lokalizacja:</span>
                            <span class="value"><?php echo htmlspecialchars($listing['location'] ?? '—'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
