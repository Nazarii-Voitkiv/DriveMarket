document.addEventListener('DOMContentLoaded', async function() {
    try {
        // Pobranie danych użytkownika z serwera
        const resp = await fetch('../handlers/get-user-data.php');
        if (!resp.ok) {
            throw new Error('Failed to fetch user data');
        }
        const userData = await resp.json();

        // Ustawienie nazwy użytkownika i emaila w dokumencie
        document.getElementById('userName').textContent = userData.user_name;
        document.getElementById('userEmail').textContent = userData.user_email;

        // Elementy związane z edycją nazwy użytkownika
        const editNameBtn = document.getElementById('editNameBtn');
        const nameEditForm = document.getElementById('nameEditForm');
        const userName = document.getElementById('userName');
        const newNameInput = document.getElementById('newNameInput');
        const saveNameBtn = document.getElementById('saveNameBtn');
        const cancelNameBtn = document.getElementById('cancelNameBtn');

        // Obsługa kliknięcia przycisku edycji nazwy
        editNameBtn.addEventListener('click', function() {
            editNameBtn.style.display = 'none';
            userName.style.display = 'none';
            nameEditForm.style.display = 'flex';
            newNameInput.value = userName.textContent;
            newNameInput.focus();
        });

        // Obsługa kliknięcia przycisku anulowania edycji
        cancelNameBtn.addEventListener('click', function() {
            nameEditForm.style.display = 'none';
            userName.style.display = 'inline';
            editNameBtn.style.display = 'inline';
        });

        // Obsługa kliknięcia przycisku zapisu nowej nazwy
        saveNameBtn.addEventListener('click', async function() {
            const newName = newNameInput.value;

            try {
                // Wysłanie nowej nazwy do serwera
                const response = await fetch('../handlers/update-name.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ newName })
                });

                const data = await response.json();
                if (data.success) {
                    // Aktualizacja nazwy w interfejsie
                    userName.textContent = newName;
                    nameEditForm.style.display = 'none';
                    userName.style.display = 'inline';
                    editNameBtn.style.display = 'inline';
                } else {
                    alert(data.error || 'Błąd podczas zmiany nazwy');
                }
            } catch (error) {
                alert('Błąd podczas wysyłania żądania');
            }
        });

        // Sekcja zmiany hasła
        const passwordSection = document.getElementById('passwordSection');
        if (userData.has_password) {
            // Formularz zmiany hasła
            passwordSection.innerHTML = `
                <h2>Zmiana hasła</h2>
                <form id="changePasswordForm" class="password-form">
                    <div class="form-group">
                        <input type="password" name="oldPassword" placeholder="Obecne hasło" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="newPassword" placeholder="Nowe hasło" required>
                    </div>
                    <button type="submit">Zmień hasło</button>
                </form>
            `;
        } else {
            // Formularz tworzenia nowego hasła
            passwordSection.innerHTML = `
                <h2>Utwórz hasło</h2>
                <p class="password-notice">Zalecamy utworzenie hasła dla większego bezpieczeństwa konta</p>
                <form id="createPasswordForm" class="password-form">
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Wprowadź hasło" required>
                    </div>
                    <button type="submit">Utwórz hasło</button>
                </form>
            `;
        }

        // Podgląd awatara użytkownika
        const avatarPreview = document.getElementById('avatarPreview');
        if (userData.avatar_url) {
            avatarPreview.innerHTML = `
                <img src="${userData.avatar_url}" alt="Awatar użytkownika"
                     style="width: 100%; height: 100%; object-fit: cover;">`;
        } else {
            avatarPreview.innerHTML = `
                <span class="avatar-initials">
                    ${userData.user_name.charAt(0).toUpperCase()}
                </span>`;
        }

        // Elementy związane z formularzem aktualizacji awatara
        const avatarForm = document.getElementById('avatarForm');
        const avatarInput = document.getElementById('avatarInput');

        // Obsługa zmiany pliku w formularzu awatara
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.innerHTML = `
                        <img src="${e.target.result}" alt="Podgląd"
                             style="width: 100%; height: 100%; object-fit: cover;">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // Obsługa wysłania formularza aktualizacji awatara
        avatarForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('avatar', avatarInput.files[0]);

            try {
                // Wysłanie nowego awatara do serwera
                const response = await fetch('../handlers/update-avatar.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    // Aktualizacja podglądu awatara w interfejsie
                    avatarPreview.innerHTML = `
                        <img src="${data.avatar_url}" alt="Awatar użytkownika"
                             style="width: 100%; height: 100%; object-fit: cover;">`;

                    updateHeaderAvatar(data.avatar_url);
                    avatarForm.reset();
                    alert('Awatar został zaktualizowany');
                } else {
                    alert(data.error || 'Błąd podczas aktualizacji awatara');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Błąd podczas wysyłania żądania');
            }
        });

    } catch (error) {
        console.error('Error:', error);
    }
});