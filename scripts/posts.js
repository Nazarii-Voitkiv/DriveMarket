document.addEventListener('DOMContentLoaded', async function() {
    try {
        const response = await fetch('../handlers/get-user-data.php');
        const userData = await response.json();
        
        // Sprawdzenie, czy awatar użytkownika jest dostępny
        if (userData.avatar_url) {
            const headerAvatar = document.querySelector('.avatar-circle');
            if (headerAvatar) {
                headerAvatar.innerHTML = `
                    <img src="${userData.avatar_url}" 
                         alt="Awatar użytkownika"
                         style="width: 100%; height: 100%; object-fit: cover;">`;
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }

    // Pobieranie elementów DOM dla różnych filtrów
    const markaSelect = document.getElementById('marka');
    const modelSelect = document.getElementById('model');
    const generacjaSelect = document.getElementById('generacja');
    const typNadwoziaSelect = document.getElementById('typ_nadwozia');
    const rodzajPaliwaSelect = document.getElementById('rodzaj_paliwa');
    const stanUskodzeniaSelect = document.getElementById('stan_uszkodzenia');
    const krajPochodzenia = document.getElementById('kraj_pochodzenia');
    const cenaOdInput = document.getElementById('cena_od');
    const cenaDoInput = document.getElementById('cena_do');
    const rokOdSelect = document.getElementById('rok_od');
    const rokDoSelect = document.getElementById('rok_do');
    const przebiegOdInput = document.getElementById('przebieg_od');
    const przebiegDoInput = document.getElementById('przebieg_do');
    const sortSelect = document.getElementById('sortowanie');

    // Wyłączenie niektórych selektorów na początku
    modelSelect.disabled = true;
    generacjaSelect.disabled = true;

    // Pobieranie elementów do obsługi modalnego okna statusu
    const statusSection = document.getElementById('statusSection');
    const statusModal = document.getElementById('statusModal');
    const closeModal = document.querySelector('.close-modal');
    const statusCheckboxes = document.querySelectorAll('.status-option input[type="checkbox"]');

    // Obsługa otwierania modalnego okna przy kliknięciu
    if (statusSection) {
        statusSection.addEventListener('click', function(e) {
            e.preventDefault();
            statusModal.classList.add('show');
        });
    }

    // Obsługa zamykania modalnego okna przy kliknięciu w przycisk zamykania
    if (closeModal) {
        closeModal.addEventListener('click', function(e) {
            e.preventDefault();
            statusModal.classList.remove('show');
        });
    }

    // Zamykanie modalnego okna przy kliknięciu poza nim
    window.addEventListener('click', function(event) {
        if (event.target === statusModal) {
            statusModal.classList.remove('show');
        }
    });

    // Zatrzymanie propagacji kliknięcia wewnątrz modalnego okna
    if (statusModal) {
        statusModal.querySelector('.modal-content').addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Obsługa zmiany stanu checkboxów
    statusCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateListings();
        });
    });

    // Funkcja do aktualizacji listy ogłoszeń
    async function updateListings() {
        const params = new URLSearchParams();

        // Dodawanie podstawowych parametrów
        if (markaSelect.value) params.append('marka', markaSelect.value);
        if (modelSelect.value) params.append('model', modelSelect.value);
        if (generacjaSelect.value) params.append('generacja', generacjaSelect.value);
        if (typNadwoziaSelect.value) params.append('typ_nadwozia', typNadwoziaSelect.value);
        if (rodzajPaliwaSelect.value) params.append('rodzaj_paliwa', rodzajPaliwaSelect.value);
        if (krajPochodzenia.value) params.append('kraj_pochodzenia', krajPochodzenia.value);

        // Dodawanie wybranych statusów
        const selectedStatuses = Array.from(statusCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        if (selectedStatuses.length > 0) {
            params.append('status', JSON.stringify(selectedStatuses));
        }

        // Dodawanie parametru stanu uszkodzenia
        if (stanUskodzeniaSelect.value && stanUskodzeniaSelect.value !== 'dowolny') {
            params.append('damaged', stanUskodzeniaSelect.value);
        }

        // Dodawanie parametrów ceny
        if (cenaOdInput.value) {
            params.append('cena_od', cenaOdInput.value);
        }
        if (cenaDoInput.value) {
            params.append('cena_do', cenaDoInput.value);
        }

        // Dodawanie parametrów roku
        if (rokOdSelect.value) {
            params.append('rok_od', rokOdSelect.value);
        }
        if (rokDoSelect.value) {
            params.append('rok_do', rokDoSelect.value);
        }

        // Dodawanie parametrów przebiegu
        if (przebiegOdInput.value) {
            params.append('przebieg_od', przebiegOdInput.value);
        }
        if (przebiegDoInput.value) {
            params.append('przebieg_do', przebiegDoInput.value);
        }

        // Dodawanie parametru sortowania
        if (sortSelect.value) {
            params.append('sort', sortSelect.value);
        }

        try {
            const response = await fetch(`../handlers/get-filtered-listings.php?${params.toString()}`);
            const listings = await response.json();
            
            // Aktualizacja kontenera z ogłoszeniami
            const container = document.querySelector('.post-container');
            container.innerHTML = ''; // Czyszczenie kontenera

            if (listings.length > 0) {
                listings.forEach(listing => {
                    const postElement = createPostElement(listing);
                    container.appendChild(postElement);
                });
            } else {
                container.innerHTML = '<p>Brak ogłoszeń do wyświetlenia.</p>';
            }
        } catch (error) {
            console.error('Pomysł ładowania ogłoszeń:', error);
            const container = document.querySelector('.post-container');
            container.innerHTML = '<p>Nie udało się załadować ogłoszeń.</p>';
        }
    }

    // Funkcja do tworzenia elementu ogłoszenia
    function createPostElement(listing) {
        const postLink = document.createElement('a');
        postLink.href = `listing-details.php?id=${listing.listing_id}`;
        postLink.className = 'post-link';

        const postDiv = document.createElement('div');
        postDiv.className = 'post';

        // Dodawanie obrazu
        const postBox = document.createElement('div');
        postBox.className = 'post-box';

        if (listing.images) {
            try {
                const images = JSON.parse(listing.images);
                if (images && images.length > 0) {
                    const img = document.createElement('img');
                    img.src = '../' + images[0];
                    img.alt = 'Zdjęcie pojazdu';
                    postBox.appendChild(img);
                } else {
                    postBox.innerHTML = '';
                }
            } catch (e) {
                console.error('Błąd parsowania obrazów:', e);
                postBox.innerHTML = '';
            }
        } else {
            postBox.innerHTML = '';
        }

        postDiv.appendChild(postBox);

        // Dodawanie informacji o ogłoszeniu
        const postInfo = document.createElement('div');
        postInfo.className = 'post-info';

        // Tytuł (marka + model)
        const title = document.createElement('h2');
        title.textContent = `${listing.brand} ${listing.model}`;
        postInfo.appendChild(title);

        // Cena
        const priceTag = document.createElement('div');
        priceTag.className = 'price-tag';
        let price = parseFloat(listing.price || 0);
        if (listing.price_type === 'netto') {
            price = Math.round(price * 1.23);
            priceTag.innerHTML = `<strong>${price.toLocaleString('pl-PL')}</strong> PLN (z VAT)`;
        } else {
            priceTag.innerHTML = `<strong>${price.toLocaleString('pl-PL')}</strong> PLN`;
        }
        postInfo.appendChild(priceTag);

        // Rok produkcji
        const yearP = document.createElement('p');
        yearP.innerHTML = `<strong>Rok:</strong> ${listing.prod_year || 'Brak danych'}`;
        postInfo.appendChild(yearP);

        // Typ paliwa
        const fuelP = document.createElement('p');
        fuelP.innerHTML = `<strong>Paliwo:</strong> ${listing.fuel_type || 'Brak danych'}`;
        postInfo.appendChild(fuelP);

        // Przebieg
        const mileageP = document.createElement('p');
        if (listing.mileage) {
            mileageP.innerHTML = `<strong>Przebieg:</strong> ${parseInt(listing.mileage).toLocaleString('pl-PL')} km`;
        } else {
            mileageP.innerHTML = `<strong>Przebieg:</strong> Brak danych`;
        }
        postInfo.appendChild(mileageP);

        // Stan
        const damageP = document.createElement('p');
        if (listing.damaged === null) {
            damageP.innerHTML = `<strong>Stan:</strong> Dowolny`;
        } else if (listing.damaged === 0) {
            damageP.innerHTML = `<strong>Stan:</strong> Nieuszkodzony`;
        } else {
            damageP.innerHTML = `<strong>Stan:</strong> Uszkodzony`;
        }
        postInfo.appendChild(damageP);

        // Kraj pochodzenia
        if (listing.kraj_pochodzenia) {
            const originP = document.createElement('p');
            originP.innerHTML = `<strong>Kraj pochodzenia:</strong> ${listing.kraj_pochodzenia.charAt(0).toUpperCase() + listing.kraj_pochodzenia.slice(1)}`;
            postInfo.appendChild(originP);
        }

        // Kontakt telefoniczny
        const phoneP = document.createElement('p');
        phoneP.innerHTML = `<strong>Kontakt:</strong> ${listing.seller_phone || 'Brak telefonu'}`;
        postInfo.appendChild(phoneP);

        postDiv.appendChild(postInfo);
        postLink.appendChild(postDiv);

        return postLink;
    }

    // Funkcja do walidacji przebiegu
    function validateMileage(input) {
        // Usuwanie wszystkich niecyfrowych znaków
        let value = input.value.replace(/\D/g, '');
        
        // Ograniczenie maksymalnej wartości
        if (value > 9999999) {
            value = '9999999';
        }
        
        input.value = value;
    }

    // Dodawanie obsługi zdarzeń dla filtrów
    markaSelect.addEventListener('change', async function() {
        const selectedMarka = this.value;
        modelSelect.disabled = !selectedMarka;
        generacjaSelect.disabled = true;
        
        // Czyszczenie selektorów
        modelSelect.innerHTML = '<option value="">Model pojazdu</option>';
        generacjaSelect.innerHTML = '<option value="">Generacja</option>';
        
        if (selectedMarka) {
            try {
                const response = await fetch(`../handlers/get-models.php?marka=${encodeURIComponent(selectedMarka)}`);
                const models = await response.json();
                
                models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model;
                    option.textContent = model;
                    modelSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Błąd ładowania modeli:', error);
            }
        }
        
        updateListings();
    });

    modelSelect.addEventListener('change', async function() {
        const selectedModel = this.value;
        const selectedMarka = markaSelect.value;
        generacjaSelect.disabled = !selectedModel;
        
        // Czyszczenie selektora generacja
        generacjaSelect.innerHTML = '<option value="">Generacja</option>';
        
        if (selectedModel && selectedMarka) {
            try {
                const response = await fetch(`../handlers/get-generations.php?marka=${encodeURIComponent(selectedMarka)}&model=${encodeURIComponent(selectedModel)}`);
                const generations = await response.json();
                
                if (generations.length > 0) {
                    generations.forEach(generation => {
                        const option = document.createElement('option');
                        option.value = generation;
                        option.textContent = generation;
                        generacjaSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = "";
                    option.textContent = "Brak dostępnych generacji";
                    option.disabled = true;
                    generacjaSelect.appendChild(option);
                }
            } catch (error) {
                console.error('Błąd ładowania generacji:', error);
            }
        }
        
        updateListings();
    });

    // Dodawanie obsługi zdarzeń dla wszystkich filtrów
    generacjaSelect.addEventListener('change', updateListings);
    typNadwoziaSelect.addEventListener('change', updateListings);
    rodzajPaliwaSelect.addEventListener('change', updateListings);
    stanUskodzeniaSelect.addEventListener('change', updateListings);
    krajPochodzenia.addEventListener('change', updateListings);
    rokOdSelect.addEventListener('change', updateListings);
    rokDoSelect.addEventListener('change', updateListings);

    // Dodawanie opóźnienia dla filtrowania po cenie i przebiegu
    let filterTimeout;
    function handleFilterInput() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(updateListings, 500);
    }

    // Dodawanie obsługi zdarzeń dla pól ceny
    cenaOdInput.addEventListener('input', handleFilterInput);
    cenaDoInput.addEventListener('input', handleFilterInput);

    // Dodawanie obsługi zdarzeń dla pól przebiegu z walidacją
    przebiegOdInput.addEventListener('input', function() {
        validateMileage(this);
        handleFilterInput();
    });

    przebiegDoInput.addEventListener('input', function() {
        validateMileage(this);
        handleFilterInput();
    });

    // Dodawanie walidacji dla roku "do", aby nie był mniejszy niż rok "od"
    rokOdSelect.addEventListener('change', function() {
        if (rokDoSelect.value && parseInt(rokDoSelect.value) < parseInt(this.value)) {
            rokDoSelect.value = this.value;
        }
        updateListings();
    });

    rokDoSelect.addEventListener('change', function() {
        if (rokOdSelect.value && parseInt(this.value) < parseInt(rokOdSelect.value)) {
            this.value = rokOdSelect.value;
        }
        updateListings();
    });

    // Obsługa rozszerzonych filtrów
    const moreFiltersLink = document.querySelector('.more-filters-link');
    const extendedFilters = document.querySelector('.extended-filters');
    let filtersVisible = false;

    moreFiltersLink.addEventListener('click', function(e) {
        e.preventDefault();
        filtersVisible = !filtersVisible;
        
        if (filtersVisible) {
            extendedFilters.style.display = 'block';
            moreFiltersLink.textContent = 'Ukryj filtry';
            // Dodawanie klasy show po krótkim opóźnieniu dla animacji
            setTimeout(() => {
                extendedFilters.classList.add('show');
            }, 10);
        } else {
            extendedFilters.classList.remove('show');
            moreFiltersLink.textContent = 'Pokaż więcej filtrów';
            // Ukrywanie elementu po zakończeniu animacji
            setTimeout(() => {
                extendedFilters.style.display = 'none';
            }, 300);
        }
    });

    // Obsługa kliknięć na nagłówki sekcji
    const filterHeaders = document.querySelectorAll('.filter-header');
    filterHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const content = this.querySelector('.filter-content');
            if (content) {
                const isVisible = content.style.display !== 'none';
                content.style.display = isVisible ? 'none' : 'block';
            }
        });
    });

    // Ładowanie początkowej listy ogłoszeń
    updateListings();

    // Dodawanie obsługi sortowania
    sortSelect.addEventListener('change', function() {
        updateListings();
    });
});