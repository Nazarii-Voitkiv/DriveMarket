async function initializeAvatar() {
    try {
        const response = await fetch('/DriveMarket/handlers/get-user-data.php');
        const userData = await response.json();
        
        const headerAvatar = document.querySelector('.avatar-circle');
        const userInitial = headerAvatar.querySelector('.user-initial');
        const avatarImage = headerAvatar.querySelector('.avatar-image');
        
        if (headerAvatar) {
            if (userData.avatar_url) {
                avatarImage.src = userData.avatar_url;
                avatarImage.style.display = 'block';
                userInitial.style.display = 'none';
            } else if (userData.user_name) {
                userInitial.textContent = userData.user_name.charAt(0).toUpperCase();
                userInitial.style.display = 'block';
                avatarImage.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Initialize avatar when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeAvatar);
