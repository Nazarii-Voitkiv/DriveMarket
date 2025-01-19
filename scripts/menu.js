document.addEventListener('DOMContentLoaded', async function() {
    const menuAuthLinks = document.querySelector('.menu-auth-links');

    // Sprawdź stan uwierzytelnienia i zaktualizuj menu
    function updateMenuVisibility() {
        const auth = checkAuth();
        if (!auth.isAuthenticated) {
            if (menuAuthLinks) menuAuthLinks.style.display = 'flex';
        } else {
            if (menuAuthLinks) menuAuthLinks.style.display = 'none';
        }
    }

    // Wstępna kontrola
    updateMenuVisibility();

    // Okresowo sprawdzaj stan uwierzytelnienia
    setInterval(updateMenuVisibility, 30000);
});
