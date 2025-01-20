document.addEventListener('DOMContentLoaded', async function() {
    try {
        const response = await fetch('../handlers/get-user-data.php');
        const userData = await response.json();
        
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

    modelSelect.disabled = true;
    generacjaSelect.disabled = true;


    const statusSection = document.getElementById('statusSection');
    const statusModal = document.getElementById('statusModal');
    const closeModal = document.querySelector('.close-modal');
    const statusCheckboxes = document.querySelectorAll('.status-option input[type="checkbox"]');

    // Відкриття модального вікна при кліку на секцію статусу
    if (statusSection) {
        statusSection.addEventListener('click', function(e) {
            e.preventDefault();
            statusModal.classList.add('show');
        });
    }

    // Закриття модального вікна при кліку на хрестик
    if (closeModal) {
        closeModal.addEventListener('click', function(e) {
            e.preventDefault();
            statusModal.classList.remove('show');
        });
    }

    // Закриття модального вікна при кліку поза ним
    window.addEventListener('click', function(event) {
        if (event.target === statusModal) {
            statusModal.classList.remove('show');
        }
    });

    // Зупинка події кліку всередині модального вікна
    if (statusModal) {
        statusModal.querySelector('.modal-content').addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Обробка зміни чекбоксів
    statusCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateListings();
        });
    });

    // Функція для оновлення списку оголошень
    async function updateListings() {
        const params = new URLSearchParams();

        // Додаємо базові параметри
        if (markaSelect.value) params.append('marka', markaSelect.value);
        if (modelSelect.value) params.append('model', modelSelect.value);
        if (generacjaSelect.value) params.append('generacja', generacjaSelect.value);
        if (typNadwoziaSelect.value) params.append('typ_nadwozia', typNadwoziaSelect.value);
        if (rodzajPaliwaSelect.value) params.append('rodzaj_paliwa', rodzajPaliwaSelect.value);
        if (krajPochodzenia.value) params.append('kraj_pochodzenia', krajPochodzenia.value);

        // Додаємо вибрані статуси
        const selectedStatuses = Array.from(statusCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        if (selectedStatuses.length > 0) {
            params.append('status', JSON.stringify(selectedStatuses));
        }

        // Додаємо параметр стану пошкодження, якщо вибрано конкретне значення
        if (stanUskodzeniaSelect.value && stanUskodzeniaSelect.value !== 'dowolny') {
            params.append('damaged', stanUskodzeniaSelect.value);
        }

        // Додаємо параметри ціни, якщо вони вказані
        if (cenaOdInput.value) {
            params.append('cena_od', cenaOdInput.value);
        }
        if (cenaDoInput.value) {
            params.append('cena_do', cenaDoInput.value);
        }

        // Додаємо параметри року
        if (rokOdSelect.value) {
            params.append('rok_od', rokOdSelect.value);
        }
        if (rokDoSelect.value) {
            params.append('rok_do', rokDoSelect.value);
        }

        // Додаємо параметри пробігу
        if (przebiegOdInput.value) {
            params.append('przebieg_od', przebiegOdInput.value);
        }
        if (przebiegDoInput.value) {
            params.append('przebieg_do', przebiegDoInput.value);
        }

        try {
            const response = await fetch(`../handlers/get-filtered-listings.php?${params.toString()}`);
            const listings = await response.json();
            
            // Оновлюємо контейнер з оголошеннями
            const container = document.querySelector('.post-container');
            container.innerHTML = ''; // Очищаємо контейнер

            if (listings.length > 0) {
                listings.forEach(listing => {
                    const postElement = createPostElement(listing);
                    container.appendChild(postElement);
                });
            } else {
                container.innerHTML = '<p>Brak ogłoszeń do wyświetlenia.</p>';
            }
        } catch (error) {
            console.error('Помилка завантаження оголошень:', error);
            const container = document.querySelector('.post-container');
            container.innerHTML = '<p>Nie udało się załadować ogłoszeń.</p>';
        }
    }

    // Функція для створення елемента оголошення
    function createPostElement(listing) {
        const postLink = document.createElement('a');
        postLink.href = `listing-details.php?id=${listing.listing_id}`;
        postLink.className = 'post-link';

        const postDiv = document.createElement('div');
        postDiv.className = 'post';

        // Додаємо зображення
        const postBox = document.createElement('div');
        postBox.className = 'post-box';

        if (listing.images) {
            try {
                const images = JSON.parse(listing.images);
                if (images && images.length > 0) {
                    const firstImage = images[0];
                    if (firstImage.data && firstImage.type) {
                        const img = document.createElement('img');
                        img.src = `data:${firstImage.type};base64,${firstImage.data}`;
                        img.alt = 'Zdjęcie pojazdu';
                        postBox.appendChild(img);
                    } else {
                        postBox.innerHTML = '<div class="no-image">Brak zdjęcia</div>';
                    }
                } else {
                    postBox.innerHTML = '<div class="no-image">Brak zdjęcia</div>';
                }
            } catch (e) {
                postBox.innerHTML = '<div class="no-image">Brak zdjęcia</div>';
            }
        } else {
            postBox.innerHTML = '<div class="no-image">Brak zdjęcia</div>';
        }

        postDiv.appendChild(postBox);

        // Додаємо інформацію про оголошення
        const postInfo = document.createElement('div');
        postInfo.className = 'post-info';

        // Заголовок (марка + модель)
        const title = document.createElement('h2');
        title.textContent = `${listing.brand} ${listing.model}`;
        postInfo.appendChild(title);

        // Ціна
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

        // Рік виробництва
        const yearP = document.createElement('p');
        yearP.innerHTML = `<strong>Rok:</strong> ${listing.prod_year || 'Brak danych'}`;
        postInfo.appendChild(yearP);

        // Тип палива
        const fuelP = document.createElement('p');
        fuelP.innerHTML = `<strong>Paliwo:</strong> ${listing.fuel_type || 'Brak danych'}`;
        postInfo.appendChild(fuelP);

        // Пробіг
        const mileageP = document.createElement('p');
        if (listing.mileage) {
            mileageP.innerHTML = `<strong>Przebieg:</strong> ${parseInt(listing.mileage).toLocaleString('pl-PL')} km`;
        } else {
            mileageP.innerHTML = `<strong>Przebieg:</strong> Brak danych`;
        }
        postInfo.appendChild(mileageP);

        // Стан
        const damageP = document.createElement('p');
        if (listing.damaged === null) {
            damageP.innerHTML = `<strong>Stan:</strong> Dowolny`;
        } else if (listing.damaged === 0) {
            damageP.innerHTML = `<strong>Stan:</strong> Nieuszkodzony`;
        } else {
            damageP.innerHTML = `<strong>Stan:</strong> Uszkodzony`;
        }
        postInfo.appendChild(damageP);

        // Країна походження
        if (listing.kraj_pochodzenia) {
            const originP = document.createElement('p');
            originP.innerHTML = `<strong>Kraj pochodzenia:</strong> ${listing.kraj_pochodzenia.charAt(0).toUpperCase() + listing.kraj_pochodzenia.slice(1)}`;
            postInfo.appendChild(originP);
        }

        // Контактний телефон
        const phoneP = document.createElement('p');
        phoneP.innerHTML = `<strong>Kontakt:</strong> ${listing.seller_phone || 'Brak telefonu'}`;
        postInfo.appendChild(phoneP);

        postDiv.appendChild(postInfo);
        postLink.appendChild(postDiv);

        return postLink;
    }

    // Функція для валідації пробігу
    function validateMileage(input) {
        // Видаляємо всі нецифрові символи
        let value = input.value.replace(/\D/g, '');
        
        // Обмежуємо максимальне значення
        if (value > 9999999) {
            value = '9999999';
        }
        
        input.value = value;
    }

    // Додаємо обробники подій для фільтрів
    markaSelect.addEventListener('change', async function() {
        const selectedMarka = this.value;
        modelSelect.disabled = !selectedMarka;
        generacjaSelect.disabled = true;
        
        // Очищаємо селекти
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
                console.error('Помилка завантаження моделей:', error);
            }
        }
        
        updateListings();
    });

    modelSelect.addEventListener('change', async function() {
        const selectedModel = this.value;
        const selectedMarka = markaSelect.value;
        generacjaSelect.disabled = !selectedModel;
        
        // Очищаємо селект generacja
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
                    option.textContent = "Немає доступних генерацій";
                    option.disabled = true;
                    generacjaSelect.appendChild(option);
                }
            } catch (error) {
                console.error('Помилка завантаження генерацій:', error);
            }
        }
        
        updateListings();
    });

    // Додаємо обробники подій для всіх фільтрів
    generacjaSelect.addEventListener('change', updateListings);
    typNadwoziaSelect.addEventListener('change', updateListings);
    rodzajPaliwaSelect.addEventListener('change', updateListings);
    stanUskodzeniaSelect.addEventListener('change', updateListings);
    krajPochodzenia.addEventListener('change', updateListings);
    rokOdSelect.addEventListener('change', updateListings);
    rokDoSelect.addEventListener('change', updateListings);

    // Додаємо затримку для фільтрації за ціною та пробігом
    let filterTimeout;
    function handleFilterInput() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(updateListings, 500);
    }

    // Додаємо обробники для полів ціни
    cenaOdInput.addEventListener('input', handleFilterInput);
    cenaDoInput.addEventListener('input', handleFilterInput);

    // Додаємо обробники для полів пробігу з валідацією
    przebiegOdInput.addEventListener('input', function() {
        validateMileage(this);
        handleFilterInput();
    });

    przebiegDoInput.addEventListener('input', function() {
        validateMileage(this);
        handleFilterInput();
    });

    // Додаємо валідацію для року "до", щоб він не був менше ніж рік "від"
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

    // Обробка розширених фільтрів
    const moreFiltersLink = document.querySelector('.more-filters-link');
    const extendedFilters = document.querySelector('.extended-filters');
    let filtersVisible = false;

    moreFiltersLink.addEventListener('click', function(e) {
        e.preventDefault();
        filtersVisible = !filtersVisible;
        
        if (filtersVisible) {
            extendedFilters.style.display = 'block';
            moreFiltersLink.textContent = 'Ukryj filtry';
            // Додаємо клас show після короткої затримки для анімації
            setTimeout(() => {
                extendedFilters.classList.add('show');
            }, 10);
        } else {
            extendedFilters.classList.remove('show');
            moreFiltersLink.textContent = 'Pokaż więcej filtrów';
            // Приховуємо елемент після завершення анімації
            setTimeout(() => {
                extendedFilters.style.display = 'none';
            }, 300);
        }
    });

    // Обробка кліків по заголовках секцій
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

    // Завантажуємо початковий список оголошень
    updateListings();
});