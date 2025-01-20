<?php
session_start();
if (!isset($_COOKIE['token'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>DriveMarket - Mój profil</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/profile.css">
</head>
<body>
    <header class="header">
        <div class="logo">DriveMarket</div>
        <nav class="menu">
            <a href="../index.html">Strona główna</a>
            <a href="posts.php">Produkty</a>
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

    <div class="profile-container">
        <h1>Panel użytkownika</h1>
        
        <div class="profile-info">
            <div class="info-section">
                <h2>Informacje osobiste</h2>
                <div class="info-item">
                    <span class="label">Imię:</span>
                    <div class="editable-field">
                        <span id="userName" class="value"></span>
                        <button id="editNameBtn" class="edit-btn">Zmień</button>
                        <div id="nameEditForm" class="edit-form" style="display: none;">
                            <input type="text" id="newNameInput">
                            <button id="saveNameBtn">Zapisz</button>
                            <button id="cancelNameBtn">Anuluj</button>
                        </div>
                    </div>
                </div>
                <div class="info-item">
                    <span class="label">Email:</span>
                    <span id="userEmail" class="value"></span>
                </div>
            </div>

            <div class="info-section">
                <h2>Zmiana awatara</h2>
                <div class="current-avatar">
                    <div class="avatar-preview" id="avatarPreview"></div>
                </div>
                <form id="avatarForm" class="avatar-form">
                    <div class="form-group">
                        <label for="avatarInput">Wybierz zdjęcie:</label>
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" required>
                    </div>
                    <button type="submit">Aktualizuj awatar</button>
                </form>
            </div>

            <div class="password-section" id="passwordSection">
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 DriveMarket. Wszystkie prawa zastrzeżone.</p>
    </footer>

    <script src="../scripts/auth.js"></script>
    <script src="../scripts/profile.js"></script>
</body>
</html>