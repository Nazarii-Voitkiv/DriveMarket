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
</head>
<body>
    <header class="header">
        <div class="logo">DriveMarket</div>
        <nav class="menu">
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
                            if (!empty($image['data']) && !empty($image['type'])) {
                                echo '<div class="photo-item">';
                                echo '<img src="data:' . htmlspecialchars($image['type']) . ';base64,' . 
                                     htmlspecialchars($image['data']) . '" alt="Zdjęcie pojazdu" />';
                                echo '</div>';
                            }
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
                        ?>
                    </div>
                </div>

                <div class="details-section">
                    <h2>Szczegóły</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="label">Rok produkcji:</span>
                            <span class="value"><?php echo htmlspecialchars($listing['prod_year'] ?? '—'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Telefon kontaktowy:</span>
                            <span class="value"><?php echo htmlspecialchars($listing['seller_phone'] ?? '—'); ?></span>
                        </div>
                        <?php if (!empty($listing['description'])): ?>
                        <div class="detail-item full-width">
                            <span class="label">Opis:</span>
                            <div class="description">
                                <?php echo nl2br(htmlspecialchars($listing['description'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2024 DriveMarket. Wszystkie prawa zastrzeżone.</p>
    </footer>

    <script src="../scripts/auth.js"></script>
</body>
</html>
