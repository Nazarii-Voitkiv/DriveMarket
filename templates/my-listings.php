<?php
session_start();
require '../vendor/autoload.php';
$config = include('../config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Перевірка авторизації
if (!isset($_COOKIE['token'])) {
    header('Location: login.php');
    exit();
}

// Розкодовуємо JWT, щоб отримати user_id
try {
    $decoded = JWT::decode($_COOKIE['token'], new Key($config['jwt_key'], 'HS256'));
    $user_id = $decoded->data->user_id;
} catch (Exception $e) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveMarket - Moje ogłoszenia</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/posts.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <style>
        .post-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }

        .edit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .edit-btn:hover {
            background-color: #45a049;
        }

        .post {
            position: relative;
        }

        .no-image {
            text-align: center;
            background-color: #ccc;
            padding: 30px 0;
        }
    </style>
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
        <div class="auth-buttons" style="display: none;">
            <!-- Якщо користувач не авторизований (але тут у нас є check JWT) -->
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

<section class="posts">
    <h1>Moje ogłoszenia</h1>
    <div class="post-container">
        <?php
        try {
            $connect = new PDO(
                "mysql:host={$config['db_host']};dbname={$config['db_name']}",
                $config['db_username'],
                $config['db_password']
            );
            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Виводимо лише ті оголошення, що належать user_id
            $query = "SELECT * FROM listings WHERE user_id = ? ORDER BY listing_id DESC";
            $stmt = $connect->prepare($query);
            $stmt->execute([$user_id]);
            $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($listings) > 0) {
                foreach ($listings as $listing) {
                    echo '<div class="post">';

                    // Кнопка редагування
                    echo '<div class="post-actions">';
                    echo '<a href="create-listing.php?edit='
                        . htmlspecialchars($listing['listing_id'])
                        . '" class="edit-btn">Edytuj</a>';
                    echo '</div>';

                    // Зображення
                    if (!empty($listing['images'])) {
                        $images = json_decode($listing['images'], true);
                        if (is_array($images) && count($images) > 0) {
                            $firstImage = $images[0];
                            if (!empty($firstImage['data']) && !empty($firstImage['type'])) {
                                echo '<div class="post-box">';
                                echo '<img src="data:' . htmlspecialchars($firstImage['type']) . ';base64,'
                                    . htmlspecialchars($firstImage['data'])
                                    . '" alt="Zdjęcie pojazdu" />';
                                echo '</div>';
                            } else {
                                echo '<div class="post-box"><div class="no-image">Brak zdjęcia</div></div>';
                            }
                        } else {
                            echo '<div class="post-box"><div class="no-image">Brak zdjęcia</div></div>';
                        }
                    } else {
                        echo '<div class="post-box"><div class="no-image">Brak zdjęcia</div></div>';
                    }

                    // Інфо
                    echo '<div class="post-info">';
                    echo '<h2>' . htmlspecialchars($listing['brand']) . ' ' . htmlspecialchars($listing['model']) . '</h2>';

                    // Форматуємо ціну з урахуванням ПДВ
                    $price = isset($listing['price']) ? (float)$listing['price'] : 0;
                    $priceType = isset($listing['price_type']) ? $listing['price_type'] : 'brutto';
                    
                    if ($priceType === 'netto') {
                        $priceWithVat = round($price * 1.23);
                        $formattedPrice = number_format($priceWithVat, 0, '.', ' ');
                        echo '<div class="price-tag"><strong>' . $formattedPrice . '</strong> PLN (z VAT)</div>';
                    } else {
                        $formattedPrice = number_format($price, 0, '.', ' ');
                        echo '<div class="price-tag"><strong>' . $formattedPrice . '</strong> PLN</div>';
                    }

                    echo '<p><strong>Rok:</strong> '
                        . htmlspecialchars($listing['prod_year'] ?? 'Brak danych')
                        . '</p>';
                    echo '<p><strong>Przebieg:</strong> '
                        . htmlspecialchars($listing['mileage'] ?? 'Brak danych')
                        . ' km</p>';
                    echo '<p><strong>Kontakt:</strong> '
                        . htmlspecialchars($listing['seller_phone'] ?? 'Brak telefonu')
                        . '</p>';

                    echo '</div>'; // .post-info
                    echo '</div>'; // .post
                }
            } else {
                echo '<p class="no-listings">Nie masz jeszcze żadnych ogłoszeń. <a href="create-listing.php">Dodaj pierwsze ogłoszenie</a></p>';
            }
        } catch (PDOException $e) {
            echo '<p>Nie udało się załadować ogłoszeń: '
                . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>
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