// assets/admin.js

// ===== NOWY SYSTEM ZARZĄDZANIA GODZINAMI =====
class GodzinyManager {
    constructor() {
        this.rok = document.querySelector('.ki-harmonogram-formularz')?.dataset?.rok || new Date().getFullYear();
        console.log('GodzinyManager: Rok', this.rok);
        this.init();
    }

    init() {
        console.log('GodzinyManager: Inicjalizacja rozpoczęta');
        this.initGodzinyPodstawowe();
        this.initOkresySpecjalne();
        this.initSwietaStale();
        this.initWyjatkiCustom();
        this.initWalidacja();
        console.log('GodzinyManager: Inicjalizacja zakończona');
    }

    // GODZINY PODSTAWOWE
    initGodzinyPodstawowe() {
        console.log('GodzinyManager: Ładowanie godzin podstawowych');
        
        const domyslneGodziny = {
            'podstawowe_powszednie': ['07:30', '08:30', '18:00', '19:00'],
            'podstawowe_soboty': ['07:30', '08:30', '18:00'],
            'podstawowe_niedziele': ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00']
        };

        Object.keys(domyslneGodziny).forEach(key => {
            console.log('Ładowanie godzin dla:', key);
            const container = this.findGodzinyContainer(key);
            if (container) {
                domyslneGodziny[key].forEach(godzina => {
                    this.dodajGodzineTag(container, godzina);
                });
            }
        });

        // Inicjalizuj dodawanie godzin dla każdego kontenera
        this.initDodawanieGodzin('podstawowe_powszednie');
        this.initDodawanieGodzin('podstawowe_soboty');
        this.initDodawanieGodzin('podstawowe_niedziele');
    }

    // OKRESY SPECJALNE
    initOkresySpecjalne() {
        console.log('GodzinyManager: Ładowanie okresów specjalnych');
        
        const okresy = {
            'koledy': {
                daty: [`${this.rok}-01-02`, `${this.rok}-01-31`],
                godziny: {
                    powszednie: ['07:30', '08:30', '15:00'],
                    soboty: ['07:30', '08:30', '18:00'],
                    niedziele: ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00']
                }
            },
            'wakacje': {
                daty: [`${this.rok}-07-01`, `${this.rok}-08-31`],
                godziny: {
                    powszednie: ['08:30', '18:00'],
                    soboty: ['08:30', '18:00'],
                    niedziele: ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00']
                }
            },
            'adwent': {
                daty: [`${this.rok}-12-01`, `${this.rok}-12-23`],
                godziny: {
                    powszednie: ['06:30', '08:30', '18:00', '19:00'],
                    soboty: ['06:30', '08:30', '18:00'],
                    niedziele: ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00']
                }
            }
        };

        Object.keys(okresy).forEach(okres => {
            console.log('Ładowanie okresu:', okres);
            
            // Ustaw daty
            this.ustawWartoscInputa(`okres_${okres}_od`, okresy[okres].daty[0]);
            this.ustawWartoscInputa(`okres_${okres}_do`, okresy[okres].daty[1]);

            // Ustaw godziny
            Object.keys(okresy[okres].godziny).forEach(typ => {
                const containerId = `okres_${okres}_${typ}`;
                const container = this.findGodzinyContainer(containerId);
                if (container) {
                    okresy[okres].godziny[typ].forEach(godzina => {
                        this.dodajGodzineTag(container, godzina);
                    });
                }
            });

            // Inicjalizuj dodawanie godzin
            this.initDodawanieGodzin(`okres_${okres}_powszednie`);
            this.initDodawanieGodzin(`okres_${okres}_soboty`);
            this.initDodawanieGodzin(`okres_${okres}_niedziele`);
        });
    }

    // ŚWIĘTA STAŁE
    initSwietaStale() {
        console.log('GodzinyManager: Ładowanie świąt stałych');
        
        const swieta = [
            { 
                id: 'swieto_1_stycznia', 
                data: `${this.rok}-01-01`,
                godziny: ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00'] 
            },
            { 
                id: 'swieto_6_stycznia', 
                data: `${this.rok}-01-06`,
                godziny: ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00'] 
            },
            { 
                id: 'swieto_3_maja', 
                data: `${this.rok}-05-03`,
                godziny: ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00'] 
            },
            { 
                id: 'swieto_15_sierpnia', 
                data: `${this.rok}-08-15`,
                godziny: ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00'] 
            },
            { 
                id: 'swieto_24_grudnia', 
                data: `${this.rok}-12-24`,
                godziny: ['07:30', '08:30'] 
            },
            { 
                id: 'swieto_25_grudnia', 
                data: `${this.rok}-12-25`,
                godziny: ['00:00', '09:00', '10:00', '11:15', '12:30', '18:00'] 
            },
            { 
                id: 'swieto_26_grudnia', 
                data: `${this.rok}-12-26`,
                godziny: ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00'] 
            }
        ];

        swieta.forEach(swieto => {
            const container = this.findGodzinyContainer(swieto.id);
            if (container) {
                swieto.godziny.forEach(godzina => {
                    this.dodajGodzineTag(container, godzina);
                });
                this.initDodawanieGodzin(swieto.id);
            }
        });

        // Święta ruchome
        this.initSwietaRuchome();
    }

    initSwietaRuchome() {
        console.log('GodzinyManager: Obliczanie świąt ruchomych');
        
        const wielkanocData = this.obliczWielkanoc(this.rok);
        
        // Ustaw daty i godziny dla świąt ruchomych
        const swietaRuchome = [
            { 
                id: 'swieto_popielec', 
                data: this.dodajDni(wielkanocData, -46),
                godziny: ['07:30', '08:30', '16:30', '18:00', '19:30'] 
            },
            { 
                id: 'swieto_wielki_czwartek', 
                data: this.dodajDni(wielkanocData, -3),
                godziny: ['19:00'] 
            },
            { 
                id: 'swieto_wielki_piatek', 
                data: this.dodajDni(wielkanocData, -2),
                godziny: [] // Brak mszy
            },
            { 
                id: 'swieto_wielka_sobota', 
                data: this.dodajDni(wielkanocData, -1),
                godziny: ['19:00'] 
            },
            { 
                id: 'swieto_wielkanoc', 
                data: wielkanocData,
                godziny: ['06:00', '09:00', '10:00', '11:15', '12:30', '18:00'] 
            },
            { 
                id: 'swieto_pon_wielkanocny', 
                data: this.dodajDni(wielkanocData, 1),
                godziny: ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00'] 
            },
            { 
                id: 'swieto_boze_cialo', 
                data: this.dodajDni(wielkanocData, 60),
                godziny: ['07:30', '09:00', '10:00', '11:15', '12:30', '18:00'] 
            }
        ];

        swietaRuchome.forEach(swieto => {
            // Ustaw datę w interfejsie
            this.ustawDateSwieta(`${swieto.id}_data`, swieto.data);
            
            // Ustaw godziny
            const container = this.findGodzinyContainer(swieto.id);
            if (container) {
                swieto.godziny.forEach(godzina => {
                    this.dodajGodzineTag(container, godzina);
                });
                this.initDodawanieGodzin(swieto.id);
            }
        });
    }

    // POMOCNICZE METODY
    findGodzinyContainer(containerId) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn('Nie znaleziono kontenera:', containerId);
            return null;
        }
        
        const godzinyContainer = container.querySelector('.ki-godziny-input-container');
        if (!godzinyContainer) {
            console.warn('Nie znaleziono .ki-godziny-input-container w:', containerId);
            return null;
        }
        
        return godzinyContainer;
    }

    ustawWartoscInputa(inputId, wartosc) {
        const input = document.getElementById(inputId);
        if (input) {
            input.value = wartosc;
        }
    }

    ustawDateSwieta(elementId, data) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = this.formatDate(data);
        }
    }

    // DODAWANIE GODZIN
    initDodawanieGodzin(containerId) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn('initDodawanieGodzin: Nie znaleziono kontenera', containerId);
            return;
        }

        const input = container.querySelector('.ki-time-input');
        const button = container.querySelector('.ki-add-godzina-btn');

        if (!input || !button) {
            console.warn('initDodawanieGodzin: Brak inputu lub przycisku w', containerId);
            return;
        }

        console.log('initDodawanieGodzin: Inicjalizacja dla', containerId);

        const dodajGodzine = () => {
            const godzina = input.value.trim();
            console.log('Dodawanie godziny:', godzina, 'do', containerId);
            
            if (!godzina) {
                alert('Proszę podać godzinę');
                return;
            }

            if (!this.walidujGodzineFormat(godzina)) {
                alert('Proszę podać poprawną godzinę w formacie HH:MM (np. 07:30)');
                return;
            }

            const godzinyContainer = container.querySelector('.ki-godziny-input-container');
            if (godzinyContainer) {
                this.dodajGodzineTag(godzinyContainer, godzina);
                input.value = '';
                input.focus();
            }
        };

        button.addEventListener('click', dodajGodzine);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                dodajGodzine();
            }
        });
    }

    dodajGodzineTag(container, godzina) {
        if (!this.walidujGodzineFormat(godzina)) {
            console.warn('Nieprawidłowy format godziny:', godzina);
            return;
        }

        const tag = document.createElement('div');
        tag.className = 'ki-godzina-tag';
        tag.innerHTML = `
            ${godzina}
            <span class="dashicons dashicons-no-alt" onclick="this.parentElement.remove()"></span>
        `;

        container.appendChild(tag);
        console.log('Dodano tag godziny:', godzina);
    }

    // WALIDACJA
    walidujGodzineFormat(godzina) {
        const regex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
        return regex.test(godzina);
    }

    initWalidacja() {
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('ki-time-input')) {
                this.walidujGodzine(e.target);
            }
        });

        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('ki-date-input')) {
                this.walidujDate(e.target);
            }
        });
    }

    walidujGodzine(input) {
        const godzina = input.value;
        if (godzina && !this.walidujGodzineFormat(godzina)) {
            input.style.borderColor = '#dc3232';
        } else {
            input.style.borderColor = '';
        }
    }

    walidujDate(input) {
        const data = new Date(input.value);
        const rok = data.getFullYear();
        
        if (rok != this.rok) {
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    }

    // WYJĄTKI CUSTOM
    initWyjatkiCustom() {
        const przycisk = document.getElementById('ki_dodaj_wyjatek');
        if (przycisk) {
            przycisk.addEventListener('click', () => {
                this.dodajWyjatekCustom();
            });
        }
    }

    dodajWyjatekCustom() {
        const dataInput = document.getElementById('ki_wyjatek_data');
        const godzinyInput = document.getElementById('ki_wyjatek_godziny');
        
        const data = dataInput.value;
        const godziny = godzinyInput.value.split(',').map(g => g.trim()).filter(g => g);
        
        if (!data) {
            alert('Proszę podać datę');
            return;
        }

        if (godziny.length === 0) {
            alert('Proszę podać przynajmniej jedną godzinę');
            return;
        }

        // Walidacja
        const dataObj = new Date(data);
        if (dataObj.getFullYear() != this.rok) {
            alert(`Data musi być w roku ${this.rok}`);
            return;
        }

        for (const godzina of godziny) {
            if (!this.walidujGodzineFormat(godzina)) {
                alert(`Nieprawidłowy format godziny: ${godzina}`);
                return;
            }
        }

        this.dodajWyjatekDoListy(data, godziny);
        dataInput.value = '';
        godzinyInput.value = '';
    }

    dodajWyjatekDoListy(data, godziny) {
        const container = document.getElementById('ki_wyjatki_lista');
        if (!container) return;

        const item = document.createElement('div');
        item.className = 'ki-wyjatek-item';
        item.innerHTML = `
            <div class="ki-wyjatek-info">
                <div class="ki-wyjatek-date">${this.formatujDateDlaWyswietlenia(data)}</div>
                <div class="ki-wyjatek-godziny">${godziny.join(', ')}</div>
            </div>
            <div class="ki-wyjatek-remove" onclick="this.parentElement.remove()">
                <span class="dashicons dashicons-trash"></span>
            </div>
        `;
        container.appendChild(item);
    }

    // MATEMATYKA DAT
    obliczWielkanoc(rok) {
        const a = rok % 19;
        const b = Math.floor(rok / 100);
        const c = rok % 100;
        const d = Math.floor(b / 4);
        const e = b % 4;
        const f = Math.floor((b + 8) / 25);
        const g = Math.floor((b - f + 1) / 3);
        const h = (19 * a + b - d - g + 15) % 30;
        const i = Math.floor(c / 4);
        const k = c % 4;
        const l = (32 + 2 * e + 2 * i - h - k) % 7;
        const m = Math.floor((a + 11 * h + 22 * l) / 451);
        const n = Math.floor((h + l - 7 * m + 114) / 31);
        const p = (h + l - 7 * m + 114) % 31;
        
        return new Date(rok, n - 1, p + 1);
    }

    dodajDni(data, dni) {
        const result = new Date(data);
        result.setDate(result.getDate() + dni);
        return result;
    }

    formatDate(data) {
        return data.toISOString().split('T')[0];
    }

    formatujDateDlaWyswietlenia(dataString) {
        const data = new Date(dataString);
        return data.toLocaleDateString('pl-PL', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    // POBIRANIE DANYCH
    pobierzDaneFormularza() {
        const dane = {
            podstawowe: {
                powszednie: this.pobierzGodzinyZContainera('podstawowe_powszednie').join(','),
                soboty: this.pobierzGodzinyZContainera('podstawowe_soboty').join(','),
                niedziele: this.pobierzGodzinyZContainera('podstawowe_niedziele').join(',')
            },
            okresy: {
                koledy: {
                    nazwa: 'Okres kolędowy',
                    daty_od: document.getElementById('okres_koledy_od')?.value || '',
                    daty_do: document.getElementById('okres_koledy_do')?.value || '',
                    powszednie: this.pobierzGodzinyZContainera('okres_koledy_powszednie').join(','),
                    soboty: this.pobierzGodzinyZContainera('okres_koledy_soboty').join(','),
                    niedziele: this.pobierzGodzinyZContainera('okres_koledy_niedziele').join(',')
                },
                wakacje: {
                    nazwa: 'Wakacje',
                    daty_od: document.getElementById('okres_wakacje_od')?.value || '',
                    daty_do: document.getElementById('okres_wakacje_do')?.value || '',
                    powszednie: this.pobierzGodzinyZContainera('okres_wakacje_powszednie').join(','),
                    soboty: this.pobierzGodzinyZContainera('okres_wakacje_soboty').join(','),
                    niedziele: this.pobierzGodzinyZContainera('okres_wakacje_niedziele').join(',')
                },
                adwent: {
                    nazwa: 'Adwent',
                    daty_od: document.getElementById('okres_adwent_od')?.value || '',
                    daty_do: document.getElementById('okres_adwent_do')?.value || '',
                    powszednie: this.pobierzGodzinyZContainera('okres_adwent_powszednie').join(','),
                    soboty: this.pobierzGodzinyZContainera('okres_adwent_soboty').join(','),
                    niedziele: this.pobierzGodzinyZContainera('okres_adwent_niedziele').join(',')
                }
            },
            dni_specjalne: {}
        };

        // Święta
        const swieta = [
            'swieto_1_stycznia', 'swieto_6_stycznia', 'swieto_3_maja', 'swieto_15_sierpnia',
            'swieto_24_grudnia', 'swieto_25_grudnia', 'swieto_26_grudnia',
            'swieto_popielec', 'swieto_wielki_czwartek', 'swieto_wielki_piatek',
            'swieto_wielka_sobota', 'swieto_wielkanoc', 'swieto_pon_wielkanocny',
            'swieto_boze_cialo'
        ];

        swieta.forEach(swieto => {
            const godziny = this.pobierzGodzinyZContainera(swieto);
            if (godziny.length > 0) {
                dane.dni_specjalne[swieto] = godziny.join(',');
            }
        });

        // Wyjątki custom
        const wyjatkiItems = document.querySelectorAll('.ki-wyjatek-item');
        wyjatkiItems.forEach(item => {
            const dataElement = item.querySelector('.ki-wyjatek-date');
            const godzinyElement = item.querySelector('.ki-wyjatek-godziny');
            
            if (dataElement && godzinyElement) {
                const data = dataElement.textContent;
                const godziny = godzinyElement.textContent;
                const [dzien, miesiac, rok] = data.split('.');
                const dataFormatted = `${rok}-${miesiac}-${dzien}`;
                dane.dni_specjalne[`custom_${dataFormatted}`] = godziny;
            }
        });

        return dane;
    }

    pobierzGodzinyZContainera(containerId) {
        const container = this.findGodzinyContainer(containerId);
        if (!container) return [];
        
        const tags = container.querySelectorAll('.ki-godzina-tag');
        return Array.from(tags).map(tag => tag.childNodes[0].textContent.trim());
    }
}

// INICJALIZACJA
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== INICJALIZACJA GODZINY MANAGER ===');
    
    // Sprawdź czy jesteśmy na stronie formularza harmonogramu
    const formularzHarmonogramu = document.querySelector('.ki-harmonogram-formularz');
    if (formularzHarmonogramu) {
        console.log('Znaleziono formularz harmonogramu - inicjalizacja GodzinyManager');
        window.godzinyManager = new GodzinyManager();
    } else {
        console.log('Nie znaleziono formularza harmonogramu - pomijam inicjalizację');
    }
});






// ===== STARY KOD JQUERY (dla kompatybilności) =====

jQuery(document).ready(function($) {
    
    // ===== HARMONOGRAMY =====
    
    // Tworzenie nowego harmonogramu
    $('body').on('click', '#ki-utworz-harmonogram', function(e) {
        e.preventDefault();
        
        var rok = $('#ki_rok').val();
        
        if (!rok) {
            alert('Proszę wybrać rok');
            return;
        }
        
        var $przycisk = $(this);
        var originalText = $przycisk.text();
        $przycisk.text('Tworzenie...').prop('disabled', true);
        
        $.ajax({
            url: ki_ajax.url,
            type: 'POST',
            data: {
                action: 'utworz_harmonogram',
                nonce: ki_ajax.nonce,
                rok: rok
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Błąd: ' + response.data);
                    $przycisk.text(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('Błąd połączenia');
                $przycisk.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Aktywacja harmonogramu
    $('body').on('click', '.ki-aktywuj-harmonogram', function(e) {
        e.preventDefault();
        
        if (!confirm('CZY NA PEWNO CHCESZ AKTYWOWAĆ TEN HARMONOGRAM?\n\nSpowoduje to wygenerowanie kalendarza na cały rok!')) {
            return;
        }
        
        var harmonogramId = $(this).data('id');
        var $przycisk = $(this);
        var originalText = $przycisk.text();
        
        $przycisk.text('Aktywowanie...').prop('disabled', true);
        
        $.ajax({
            url: ki_ajax.url,
            type: 'POST',
            data: {
                action: 'aktywuj_harmonogram',
                nonce: ki_ajax.nonce,
                harmonogram_id: harmonogramId
            },
            success: function(response) {
                if (response.success) {
                    alert('Harmonogram aktywowany!');
                    location.reload();
                } else {
                    alert('Błąd: ' + response.data);
                    $przycisk.text(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('Błąd połączenia');
                $przycisk.text(originalText).prop('disabled', false);
            }
        });
    });

    // ===== NOWE FUNKCJE ARCHIWIZACJI =====

    

    // W jQuery(document).ready(function($) { ... })

    // Archiwizacja harmonogramu - POPRAWIONE
    $('body').on('click', '.ki-btn-archive', function(e) {
        e.preventDefault();
        
        var rok = $(this).data('rok');
        console.log("Kliknięto archiwizuj dla roku:", rok); 
    
        if (!confirm('Czy na pewno chcesz zarchiwizować rok ' + rok + '?\n\nPo archiwizacji:\n• Będzie możliwy tylko podgląd\n• Edycja intencji i godzin będzie zablokowana')) {
            return;
        }
        
        var $przycisk = $(this);
        var originalText = $przycisk.text();
        $przycisk.text('Archiwizowanie...').prop('disabled', true);
        
        $.ajax({
            url: ki_ajax.url,
            type: 'POST',
            data: {
                action: 'archiwizuj_rok_ajax',
                nonce: ki_ajax.nonce,
                rok: rok
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ Rok ' + rok + ' zarchiwizowany!');
                    location.reload();
                } else {
                    alert('❌ Błąd: ' + response.data);
                    $przycisk.text(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('❌ Błąd połączenia');
                $przycisk.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Przywracanie harmonogramu - POPRAWIONE NAZWY KLAS
    $('body').on('click', '.ki-btn-restore', function(e) {
        e.preventDefault();
        
        var rok = $(this).data('rok');
        
        if (!confirm('Czy na pewno chcesz przywrócić rok ' + rok + ' do edycji?\n\nBędziesz mógł edytować godziny mszy i intencje.')) {
            return;
        }
        
        var $przycisk = $(this);
        var originalText = $przycisk.text();
        $przycisk.text('Przywracanie...').prop('disabled', true);
        
        $.ajax({
            url: ki_ajax.url,
            type: 'POST',
            data: {
                action: 'przyworc_rok_ajax',
                nonce: ki_ajax.nonce,
                rok: rok
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ Rok ' + rok + ' przywrócony do edycji!');
                    location.reload();
                } else {
                    alert('❌ Błąd: ' + response.data);
                    $przycisk.text(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('❌ Błąd połączenia');
                $przycisk.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Usuwanie harmonogramu - POPRAWIONE NAZWY KLAS
    $('body').on('click', '.ki-btn-delete', function(e) {
        e.preventDefault();
        
        var rok = $(this).data('rok');
        
        if (!confirm('⚠️ CZY NA PEWNO CHCESZ USUNĄĆ HARMONOGRAM ' + rok + '?\n\nTa operacja USUNIE:\n• Wszystkie dni z kalendarza\n• Wszystkie godziny mszy\n• Wszystkie intencje\n\nTej operacji NIE MOŻNA COFNĄĆ!')) {
            return;
        }
        
        var $przycisk = $(this);
        var originalText = $przycisk.text();
        $przycisk.text('Usuwanie...').prop('disabled', true);
        
        $.ajax({
            url: ki_ajax.url,
            type: 'POST',
            data: {
                action: 'usun_harmonogram_ajax',
                nonce: ki_ajax.nonce,
                rok: rok
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ Harmonogram ' + rok + ' usunięty!');
                    location.reload();
                } else {
                    alert('❌ Błąd: ' + response.data);
                    $przycisk.text(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('❌ Błąd połączenia');
                $przycisk.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // ===== INTENCJE (stary kod - ZACHOWANY BEZ ZMIAN) =====
    
    // Edycja klikniętej komórki
    $('.ki-intencja-edytowalna').on('click', function() {
        var $element = $(this);
        var currentText = $element.text().trim();
        var data = $element.data('data');
        var godzina = $element.data('godzina');
        
        // Zamieniamy na pole tekstowe
        var $textarea = $('<textarea class="ki-pole-edycji">').val(currentText);
        $element.html($textarea).addClass('edytowanie');
        
        // Fokus na polu
        $textarea.focus();
        
        // Zapis przy Enter
        $textarea.on('keydown', function(e) {
            if (e.keyCode === 13 && !e.shiftKey) { // Enter bez Shift
                e.preventDefault();
                zapiszIntencje($element, $textarea.val(), data, godzina);
            }
            
            if (e.keyCode === 27) { // Esc
                anulujEdycje($element, currentText);
            }
        });
        
        // Zapis przy stracie fokusa
        $textarea.on('blur', function() {
            if (!$element.hasClass('zapisywanie')) {
                zapiszIntencje($element, $textarea.val(), data, godzina);
            }
        });
    });
    
    function zapiszIntencje($element, tekst, data, godzina) {
        $element.addClass('zapisywanie');
        
        // Pokazujemy status
        var $status = $('<div class="ki-status">Zapisywanie...</div>');
        $element.append($status);
        
        // Wysyłamy AJAX
        $.ajax({
            url: ki_ajax.url,
            type: 'POST',
            data: {
                action: 'zapisz_intencje',
                nonce: ki_ajax.nonce,
                data: data,
                godzina: godzina,
                intencja: tekst
            },
            success: function(response) {
                if (response.success) {
                    $status.removeClass().addClass('ki-status sukces').text('✓ Zapisanio!');
                    $element.html(tekst).removeClass('edytowanie zapisywanie');
                    
                    // Chowamy status po 2 sekundach
                    setTimeout(function() {
                        $element.find('.ki-status').fadeOut();
                    }, 2000);
                } else {
                    $status.removeClass().addClass('ki-status blad').text('✗ Błąd: ' + response.data);
                }
            },
            error: function() {
                $status.removeClass().addClass('ki-status blad').text('✗ Błąd połączenia');
            }
        });
    }
    
    function anulujEdycje($element, originalText) {
        $element.html(originalText).removeClass('edytowanie zapisywanie');
    }


    
    
    /**
     * SPRAWDZANIE I BLOKOWANIE DNI BEZ HARMONOGRAMU
     */
    function inicjalizujZabezpieczeniaDni() {
        console.log('Inicjalizacja zabezpieczeń dni...');
        
        const wszystkieDni = document.querySelectorAll('.ki-mobile-dzien');
        const sprawdzenia = [];
        
        // Najpierw usuń wszystkie istniejące blokady
        wszystkieDni.forEach(dzien => {
            odblokujDzien(dzien);
        });
        
        wszystkieDni.forEach(dzien => {
            const dataElement = dzien.querySelector('.ki-mobile-data');
            if (!dataElement) return;
            
            const dataText = dataElement.textContent.trim();
            const [dzienNum, miesiac, rok] = dataText.split('.');
            const pelnaData = `${rok}-${miesiac}-${dzienNum}`;
            
            sprawdzenia.push(sprawdzCzyDzienEdytowalny(dzien, pelnaData));
        });
        
        Promise.all(sprawdzenia).then(() => {
            console.log('Sprawdzanie harmonogramów zakończone');
        });
    }

    /**
     * SPRAWDŹ CZY DZIEŃ JEST EDYTOWALNY
     */
    function sprawdzCzyDzienEdytowalny(dzienElement, data) {
        return new Promise((resolve) => {
            const rok = data.split('-')[0];
            
            sprawdzHarmonogramRoku(rok).then(wynik => {
                if (!wynik.czy_aktywny) {
                    zablokujDzien(dzienElement, rok, wynik.komunikat);
                    console.log(`Zablokowano: ${data} - ${wynik.komunikat}`);
                } else {
                    odblokujDzien(dzienElement);
                    console.log(`Pozostawiono edytowalny: ${data}`);
                }
                resolve();
            }).catch((error) => {
                console.error(`Błąd sprawdzania ${data}:`, error);
                zablokujDzien(dzienElement, rok, 'Błąd sprawdzania harmonogramu');
                resolve();
            });
        });
    }

    /**
     * SPRAWDŹ CZY ROK MA AKTYWNY HARMONOGRAM (AJAX)
     */
    function sprawdzHarmonogramRoku(rok) {
        return new Promise((resolve, reject) => {
            // UŻYWAJ $ Z FUNKCJI jQuery
            $.ajax({
                url: ki_ajax.url,
                type: 'POST',
                data: {
                    action: 'sprawdz_harmonogram_roku_ajax',
                    nonce: ki_ajax.nonce,
                    rok: rok
                },
                success: function(response) {
                    console.log(`Odpowiedź dla roku ${rok}:`, response);
                    if (response.success) {
                        resolve(response.data);
                    } else {
                        reject(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(`Błąd AJAX dla roku ${rok}:`, error);
                    reject('Błąd połączenia: ' + error);
                }
            });
        });
    }

    /**
     * ODBLOKUJ DZIEŃ
     */
    function odblokujDzien(dzienElement) {
        dzienElement.classList.remove('ki-dzien-bez-harmonogramu');
        
        const przyciskEdycji = dzienElement.querySelector('.ki-edytuj-godziny-btn');
        if (przyciskEdycji) {
            przyciskEdycji.style.display = 'flex';
        }
        
        const intencje = dzienElement.querySelectorAll('.ki-mobile-intencja-edytowalna');
        intencje.forEach(intencja => {
            intencja.style.cursor = 'pointer';
            intencja.removeAttribute('title');
        });
        
        const indicator = dzienElement.querySelector('.ki-brak-harmonogramu-indicator');
        if (indicator) indicator.remove();
        
        const tooltip = dzienElement.querySelector('.ki-tooltip-brak-harmonogramu');
        if (tooltip) tooltip.remove();
    }

    /**
     * ZABLOKUJ DZIEŃ BEZ HARMONOGRAMU
     */
    function zablokujDzien(dzienElement, rok, komunikat = '') {
        dzienElement.classList.add('ki-dzien-bez-harmonogramu');
        
        const przyciskEdycji = dzienElement.querySelector('.ki-edytuj-godziny-btn');
        if (przyciskEdycji) {
            przyciskEdycji.style.display = 'none';
        }
        
        const intencje = dzienElement.querySelectorAll('.ki-mobile-intencja-edytowalna');
        intencje.forEach(intencja => {
            intencja.style.cursor = 'not-allowed';
            intencja.title = komunikat || `Brak aktywnego harmonogramu na rok ${rok}`;
        });
        
        if (!dzienElement.querySelector('.ki-brak-harmonogramu-indicator')) {
            const indicator = document.createElement('div');
            indicator.className = 'ki-brak-harmonogramu-indicator';
            indicator.textContent = `Brak ${rok}`;
            indicator.title = komunikat || `Brak aktywnego harmonogramu na rok ${rok}`;
            dzienElement.appendChild(indicator);
        }
        
        if (!dzienElement.querySelector('.ki-tooltip-brak-harmonogramu')) {
            const tooltip = document.createElement('div');
            tooltip.className = 'ki-tooltip-brak-harmonogramu';
            tooltip.textContent = komunikat || `Brak aktywnego harmonogramu na rok ${rok} - edycja zablokowana`;
            dzienElement.appendChild(tooltip);
        }
    }

    // INICJALIZACJA PO ZAŁADOWANIU DOM
    setTimeout(() => {
        inicjalizujZabezpieczeniaDni();
    }, 100);
    
    // REINICJALIZACJA PRZY ZMIANIE TYGODNIA
    $(document).on('ki_tydzien_zaladowany', function() {
        setTimeout(() => {
            inicjalizujZabezpieczeniaDni();
        }, 100);
    });


    console.log('jQuery ready - stary kod załadowany');

});


