function getCookie(name) {
    // Pobiera wartość ciasteczka o podanej nazwie
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        // Dzieli ciasteczko na nazwę i wartość
        const [cookieName, cookieValue] = cookie.split('=').map(c => c.trim());
        if (cookieName === name) {
            // Zwraca wartość ciasteczka, jeśli nazwa się zgadza
            return cookieValue;
        }
    }
    // Zwraca null, jeśli ciasteczko o podanej nazwie nie istnieje
    return null;
}

function checkAuth() {
    // Sprawdza, czy użytkownik jest uwierzytelniony na podstawie tokenu JWT
    const token = getCookie('token');
    if (!token) {
        // Zwraca informację o braku uwierzytelnienia, jeśli brak tokenu
        return { isAuthenticated: false };
    }

    try {
        // Dzieli token na części (header, payload, signature)
        const parts = token.split('.');
        if (parts.length !== 3) {
            // Zwraca informację o braku uwierzytelnienia, jeśli token jest niepoprawny
            return { isAuthenticated: false };
        }
        // Dekoduje część payload tokenu z base64
        const base64Url = parts[1];
        const base64    = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(
            atob(base64)
            .split('')
            .map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
            .join('')
        );
        const payload = JSON.parse(jsonPayload);

        // Zwraca informację o uwierzytelnieniu oraz dane użytkownika
        return {
            isAuthenticated: true,
            userData: payload.data
        };
    } catch (err) {
        // Loguje błąd parsowania JWT
        console.error('JWT parse error:', err);
        // Zwraca informację o braku uwierzytelnienia
        return { isAuthenticated: false };
    }
}

function logout() {
    // Usuwa token przez ustawienie ciasteczka na przeszłą datę
    document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    // Przekierowuje użytkownika na stronę główną po wylogowaniu
    window.location.href = '/DriveMarket/index.html';
}
// Funkcja do przełączania widoczności menu rozwijanego
function toggleDropdown() {
    const dropdownMenu = document.getElementById('dropdownMenu');
    dropdownMenu.classList.toggle('show');
}

// Ukryj menu rozwijane po kliknięciu poza nim
document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-menu')) {
        const dropdownMenu = document.getElementById('dropdownMenu');
        if (dropdownMenu) dropdownMenu.classList.remove('show');
    }
});

// Aktualizuje awatar w nagłówku
function updateHeaderAvatar(avatarUrl) {
    const headerAvatar = document.querySelector('.avatar-circle');
    if (headerAvatar) {
        headerAvatar.innerHTML = `
            <img src="${avatarUrl}" 
                 alt="User avatar"
                 style="width: 100%; height: 100%; object-fit: cover;">`;
    }
}

// Aktualizuje interfejs użytkownika w zależności od stanu uwierzytelnienia
async function updateUserInterface() {
    // Sprawdza stan uwierzytelnienia użytkownika
    const authState = checkAuth();
    const authButtons = document.querySelector('.auth-buttons');
    const userMenu = document.querySelector('.user-menu');

    if (authState.isAuthenticated) {
        // Ukrywa przyciski uwierzytelnienia, gdy użytkownik jest zalogowany
        if (authButtons) authButtons.style.display = 'none';
        if (userMenu) {
            // Wyświetla menu użytkownika, gdy jest zalogowany
            userMenu.style.display = 'block';

            try {
                // Pobiera dane użytkownika z serwera
                const response = await fetch('/DriveMarket/handlers/get-user-data.php', {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });

                const userData = await response.json();
                if (userData && userData.avatar_url) {
                    // Aktualizuje awatar w nagłówku, jeśli jest dostępny
                    updateHeaderAvatar(userData.avatar_url);
                } else {
                    // Wyświetla inicjały użytkownika, gdy awatar nie jest dostępny
                    const headerAvatar = document.querySelector('.avatar-circle');
                    if (headerAvatar) {
                        headerAvatar.innerHTML = `<span id="userInitials">${userData.user_name.charAt(0).toUpperCase()}</span>`;
                    }
                }
            } catch (error) {
                // Obsługuje błędy podczas pobierania danych użytkownika
                console.error('Error:', error);
                if (authState.userData) {
                    // Wyświetla inicjały użytkownika, gdy wystąpi błąd
                    const headerAvatar = document.querySelector('.avatar-circle');
                    if (headerAvatar) {
                        headerAvatar.innerHTML = `<span id="userInitials">${authState.userData.user_name.charAt(0).toUpperCase()}</span>`;
                    }
                }
            }
        }
    } else {
        // Wyświetla przyciski uwierzytelnienia, gdy użytkownik nie jest zalogowany
        if (authButtons) authButtons.style.display = 'flex';
        // Ukrywa menu użytkownika, gdy nie jest zalogowany
        if (userMenu) userMenu.style.display = 'none';
    }
}

// Wykonuje aktualizację interfejsu użytkownika po załadowaniu strony
document.addEventListener('DOMContentLoaded', updateUserInterface);

// Okresowo aktualizuje interfejs użytkownika co 30 sekund
setInterval(updateUserInterface, 30000);

// Udostępnia funkcje globalnie
window.updateUserInterface = updateUserInterface;
window.updateHeaderAvatar = updateHeaderAvatar;