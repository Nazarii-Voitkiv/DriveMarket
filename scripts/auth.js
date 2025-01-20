function getCookie(name) {
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [cookieName, cookieValue] = cookie.split('=').map(c => c.trim());
        if (cookieName === name) {
            return cookieValue;
        }
    }
    return null;
}

function checkAuth() {
    const token = getCookie('token');
    if (!token) {
        return { isAuthenticated: false };
    }

    try {
        const parts = token.split('.');
        if (parts.length !== 3) {
            return { isAuthenticated: false };
        }
        const base64Url = parts[1];
        const base64    = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(
            atob(base64)
            .split('')
            .map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
            .join('')
        );
        const payload = JSON.parse(jsonPayload);

        return {
            isAuthenticated: true,
            userData: payload.data
        };
    } catch (err) {
        console.error('JWT parse error:', err);
        return { isAuthenticated: false };
    }
}

function logout() {
    document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    window.location.href = '/DriveMarket/index.html';
}

function toggleDropdown() {
    const dropdownMenu = document.getElementById('dropdownMenu');
    dropdownMenu.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-menu')) {
        const dropdownMenu = document.getElementById('dropdownMenu');
        if (dropdownMenu) dropdownMenu.classList.remove('show');
    }
});

function updateHeaderAvatar(avatarUrl) {
    const headerAvatar = document.querySelector('.avatar-circle');
    if (headerAvatar) {
        headerAvatar.innerHTML = `
            <img src="${avatarUrl}" 
                 alt="User avatar"
                 style="width: 100%; height: 100%; object-fit: cover;">`;
    }
}

async function updateUserInterface() {
    const authState = checkAuth();
    const authButtons = document.querySelector('.auth-buttons');
    const userMenu = document.querySelector('.user-menu');

    if (authState.isAuthenticated) {
        if (authButtons) authButtons.style.display = 'none';
        if (userMenu) {
            userMenu.style.display = 'block';

            try {
                const response = await fetch('/DriveMarket/handlers/get-user-data.php', {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });

                const userData = await response.json();
                if (userData && userData.avatar_url) {
                    updateHeaderAvatar(userData.avatar_url);
                } else {
                    const headerAvatar = document.querySelector('.avatar-circle');
                    if (headerAvatar) {
                        headerAvatar.innerHTML = `<span id="userInitials">${userData.user_name.charAt(0).toUpperCase()}</span>`;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (authState.userData) {
                    const headerAvatar = document.querySelector('.avatar-circle');
                    if (headerAvatar) {
                        headerAvatar.innerHTML = `<span id="userInitials">${authState.userData.user_name.charAt(0).toUpperCase()}</span>`;
                    }
                }
            }
        }
    } else {
        if (authButtons) authButtons.style.display = 'flex';
        if (userMenu) userMenu.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', updateUserInterface);

setInterval(updateUserInterface, 30000);

window.updateUserInterface = updateUserInterface;
window.updateHeaderAvatar = updateHeaderAvatar;