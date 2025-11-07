<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>
        Edycja harmonogramu: <?php echo esc_html($harmonogram->nazwa); ?>
        <a href="<?php echo admin_url('admin.php?page=intencje-harmonogramy'); ?>" class="page-title-action">
            ← Powrót do listy
        </a>
    </h1>
    
    <!-- INFORMACJE O HARMONOGRAMIE -->
    <div class="ki-harmonogram-info">
        <div class="ki-info-box">
            <strong>Rok:</strong> <?php echo esc_html($harmonogram->rok); ?>
        </div>
        <div class="ki-info-box">
            <strong>Status:</strong> 
            <span class="ki-status-tworzony">🛠️ W przygotowaniu</span>
        </div>
        <div class="ki-info-box">
            <strong>Utworzono:</strong> <?php echo date('d.m.Y H:i', strtotime($harmonogram->data_utworzenia)); ?>
        </div>
    </div>

    <!-- FORMLARZ HARMONOGRAMU -->
    <div class="ki-harmonogram-formularz">
        <h2>Konfiguracja harmonogramu na rok <?php echo esc_html($harmonogram->rok); ?></h2>
        
        <!-- GODZINY PODSTAWOWE -->
        <div class="ki-sekcja">
            <h3>🏠 Godziny podstawowe (cały rok)</h3>
            <p class="description">Godziny mszy obowiązujące przez większość roku.</p>
            
            <div class="ki-godziny-grupa">
                <label><strong>Niedziele i święta:</strong></label>
                <input type="text" id="ki_podstawowe_niedziele" 
                       value="07:30,09:00,10:00,11:15,12:30,18:00"
                       placeholder="07:30,09:00,10:00,11:15,12:30,18:00"
                       style="width: 300px; padding: 8px;">
                <small>Format: Godzina,Godzina,Godzina (oddzielone przecinkami)</small>
            </div>
            
            <div class="ki-godziny-grupa">
                <label><strong>Poniedziałek - Piątek:</strong></label>
                <input type="text" id="ki_podstawowe_powszednie" 
                       value="07:30,08:30,18:00,19:00"
                       placeholder="07:30,08:30,18:00,19:00"
                       style="width: 300px; padding: 8px;">
            </div>
            
            <div class="ki-godziny-grupa">
                <label><strong>Soboty:</strong></label>
                <input type="text" id="ki_podstawowe_soboty" 
                       value="07:30,08:30,18:00"
                       placeholder="07:30,08:30,18:00"
                       style="width: 300px; padding: 8px;">
            </div>
        </div>

        <!-- OKRESY SPECJALNE -->
        <div class="ki-sekcja">
            <h3>📅 Okresy specjalne</h3>
            <p class="description">Okresy w roku z innymi godzinami mszy.</p>
            
            <!-- ADWENT -->
            <div class="ki-okres">
                <h4>🕯️ Adwent</h4>
                <div class="ki-okres-daty">
                    <div class="ki-data-grupa">
                        <label>Od:</label>
                        <input type="date" id="ki_adwent_od" 
                               value="<?php echo $harmonogram->rok; ?>-12-01"
                               style="padding: 6px;">
                    </div>
                    <div class="ki-data-grupa">
                        <label>Do:</label>
                        <input type="date" id="ki_adwent_do" 
                               value="<?php echo $harmonogram->rok; ?>-12-23"
                               style="padding: 6px;">
                    </div>
                </div>
                <div class="ki-okres-godziny">
                    <div class="ki-godziny-grupa">
                        <label>Niedziele adwentowe:</label>
                        <input type="text" id="ki_adwent_niedziele" 
                               value="07:30,09:00,10:00,11:15,12:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Pon-Piąt adwentowe:</label>
                        <input type="text" id="ki_adwent_powszednie" 
                               value="07:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Soboty adwentowe:</label>
                        <input type="text" id="ki_adwent_soboty" 
                               value="07:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                </div>
            </div>

            <!-- WIELKI POST -->
            <div class="ki-okres">
                <h4>⛪ Wielki Post</h4>
                <div class="ki-okres-daty">
                    <div class="ki-data-grupa">
                        <label>Od:</label>
                        <input type="date" id="ki_wielki_post_od" 
                               value="<?php echo $harmonogram->rok; ?>-03-05"
                               style="padding: 6px;">
                    </div>
                    <div class="ki-data-grupa">
                        <label>Do:</label>
                        <input type="date" id="ki_wielki_post_do" 
                               value="<?php echo $harmonogram->rok; ?>-04-16"
                               style="padding: 6px;">
                    </div>
                </div>
                <div class="ki-okres-godziny">
                    <div class="ki-godziny-grupa">
                        <label>Niedziele wielkopostne:</label>
                        <input type="text" id="ki_wielki_post_niedziele" 
                               value="07:30,09:00,10:00,11:15,12:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Pon-Piąt wielkopostne:</label>
                        <input type="text" id="ki_wielki_post_powszednie" 
                               value="07:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Droga krzyżowa (piątki):</label>
                        <input type="text" id="ki_droga_krzyzowa" 
                               value="17:30"
                               style="width: 250px; padding: 6px;">
                    </div>
                </div>
            </div>

            <!-- WAKACJE -->
            <div class="ki-okres">
                <h4>☀️ Wakacje letnie</h4>
                <div class="ki-okres-daty">
                    <div class="ki-data-grupa">
                        <label>Od:</label>
                        <input type="date" id="ki_wakacje_od" 
                               value="<?php echo $harmonogram->rok; ?>-07-01"
                               style="padding: 6px;">
                    </div>
                    <div class="ki-data-grupa">
                        <label>Do:</label>
                        <input type="date" id="ki_wakacje_do" 
                               value="<?php echo $harmonogram->rok; ?>-08-31"
                               style="padding: 6px;">
                    </div>
                </div>
                <div class="ki-okres-godziny">
                    <div class="ki-godziny-grupa">
                        <label>Niedziele wakacyjne:</label>
                        <input type="text" id="ki_wakacje_niedziele" 
                               value="07:30,09:00,10:00,11:15,12:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Pon-Piąt wakacyjne:</label>
                        <input type="text" id="ki_wakacje_powszednie" 
                               value="08:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Soboty wakacyjne:</label>
                        <input type="text" id="ki_wakacje_soboty" 
                               value="08:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                </div>
            </div>

            <!-- OKRES KOLĘDOWY -->
            <div class="ki-okres">
                <h4>🎄 Okres kolędowy (styczeń)</h4>
                <div class="ki-okres-daty">
                    <div class="ki-data-grupa">
                        <label>Od:</label>
                        <input type="date" id="ki_koledowy_od" 
                               value="<?php echo $harmonogram->rok; ?>-01-02"
                               style="padding: 6px;">
                    </div>
                    <div class="ki-data-grupa">
                        <label>Do:</label>
                        <input type="date" id="ki_koledowy_do" 
                               value="<?php echo $harmonogram->rok; ?>-01-31"
                               style="padding: 6px;">
                    </div>
                </div>
                <div class="ki-okres-godziny">
                    <div class="ki-godziny-grupa">
                        <label>Niedziele kolędowe:</label>
                        <input type="text" id="ki_koledowy_niedziele" 
                               value="07:30,09:00,10:00,11:15,12:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Pon-Piąt kolędowe:</label>
                        <input type="text" id="ki_koledowy_powszednie" 
                               value="08:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Soboty kolędowe:</label>
                        <input type="text" id="ki_koledowy_soboty" 
                               value="08:30,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- DNI SPECJALNE -->
        <div class="ki-sekcja">
            <h3>🎆 Święta i dni specjalne</h3>
            <p class="description">Pojedyncze dni z wyjątkowymi godzinami mszy.</p>
            
            <!-- BOŻE NARODZENIE -->
            <div class="ki-dzien-specjalny">
                <h4>🎄 Boże Narodzenie <?php echo $harmonogram->rok; ?></h4>
                <div class="ki-dzien-specjalny-daty">
                    <div class="ki-godziny-grupa">
                        <label>Wigilia (24.12):</label>
                        <input type="text" id="ki_wigilia" 
                               value="06:30,08:00,10:00,12:00,15:00,18:00,24:00"
                               style="width: 300px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Boże Narodzenie (25.12):</label>
                        <input type="text" id="ki_boze_narodzenie" 
                               value="06:30,08:00,09:30,11:00,12:30,16:00,18:00"
                               style="width: 300px; padding: 6px;">
                    </div>
                </div>
            </div>

            <!-- WIELKANOC -->
            <div class="ki-dzien-specjalny">
                <h4>🐣 Triduum Paschalne <?php echo $harmonogram->rok; ?></h4>
                <?php
                // Oblicz datę Wielkanocy
                $easter_date = date('Y-m-d', easter_date($harmonogram->rok));
                $good_friday = date('Y-m-d', strtotime($easter_date . ' -2 days'));
                $holy_saturday = date('Y-m-d', strtotime($easter_date . ' -1 days'));
                ?>
                <div class="ki-dzien-specjalny-daty">
                    <div class="ki-godziny-grupa">
                        <label>Wielki Piątek (<?php echo date('d.m', strtotime($good_friday)); ?>):</label>
                        <input type="text" id="ki_wielki_piatek" 
                               value="08:00,10:00,15:00,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Wigilia Paschalna (<?php echo date('d.m', strtotime($holy_saturday)); ?>):</label>
                        <input type="text" id="ki_wigilia_paschalna" 
                               value="20:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                    <div class="ki-godziny-grupa">
                        <label>Wielkanoc (<?php echo date('d.m', strtotime($easter_date)); ?>):</label>
                        <input type="text" id="ki_wielkanoc" 
                               value="06:00,08:00,09:30,11:00,12:30,16:00,18:00"
                               style="width: 250px; padding: 6px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- PRZYCISKI AKCJI -->
        <div class="ki-akcje-formularza">
            <button type="button" class="button" onclick="window.location.href='<?php echo admin_url('admin.php?page=intencje-harmonogramy'); ?>'">
                ❌ Anuluj
            </button>
            
            <button type="button" class="button button-primary button-large" onclick="utworzHarmonogram()">
                🚀 Utwórz harmonogram i generuj kalendarz
            </button>
        </div>
    </div>
</div>

<script>
let harmonogramZapisany = false;

// Załaduj dane przy starcie
document.addEventListener('DOMContentLoaded', function() {
    zaladujHarmonogram();
});

function zaladujHarmonogram() {
    console.log("Ładowanie harmonogramu ID: <?php echo $harmonogram->id; ?>");
    
    const formData = new FormData();
    formData.append('action', 'pobierz_harmonogram');
    formData.append('nonce', '<?php echo wp_create_nonce('ki_nonce'); ?>');
    formData.append('harmonogram_id', <?php echo $harmonogram->id; ?>);
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log("Odpowiedź ładowania:", data);
        if (data.success) {
            wypelnijFormularz(data.data);
        } else {
            console.error('Błąd ładowania harmonogramu:', data.data);
            // Użyj domyślnych wartości
            uzyjDomyślnychWartosci();
        }
    })
    .catch(error => {
        console.error('Błąd:', error);
        uzyjDomyślnychWartosci();
    });
}

function uzyjDomyślnychWartosci() {
    console.log("Używam domyślnych wartości harmonogramu");
    // Formularz już ma domyślne wartości w inputach
    harmonogramZapisany = true;
    
    // SPRAWDŹ CZY ELEMENT ISTNIEJE PRZED ZMIANĄ STYLU
    const przyciskAktywuj = document.getElementById('ki-aktywuj-harmonogram');
    if (przyciskAktywuj) {
        przyciskAktywuj.style.display = 'inline-block';
    }
    console.log("Formularz gotowy do utworzenia harmonogramu");
}

function wypelnijFormularz(dane) {
    console.log("Wypełniam formularz danymi:", dane);
    
    // Godziny podstawowe
    if (dane.podstawowe) {
        document.getElementById('ki_podstawowe_niedziele').value = dane.podstawowe.niedziele || '07:30,09:00,10:00,11:15,12:30,18:00';
        document.getElementById('ki_podstawowe_powszednie').value = dane.podstawowe.powszednie || '07:30,08:30,18:00,19:00';
        document.getElementById('ki_podstawowe_soboty').value = dane.podstawowe.soboty || '07:30,08:30,18:00';
    }
    
    // Okresy specjalne
    if (dane.okresy) {
        // Adwent
        if (dane.okresy.adwent) {
            document.getElementById('ki_adwent_od').value = dane.okresy.adwent.daty_od || '<?php echo $harmonogram->rok; ?>-12-01';
            document.getElementById('ki_adwent_do').value = dane.okresy.adwent.daty_do || '<?php echo $harmonogram->rok; ?>-12-23';
            document.getElementById('ki_adwent_niedziele').value = dane.okresy.adwent.niedziele || '07:30,09:00,10:00,11:15,12:30,18:00';
            document.getElementById('ki_adwent_powszednie').value = dane.okresy.adwent.powszednie || '07:30,18:00';
            document.getElementById('ki_adwent_soboty').value = dane.okresy.adwent.soboty || '07:30,18:00';
        }
        
        // Wielki Post
        if (dane.okresy.wielki_post) {
            document.getElementById('ki_wielki_post_od').value = dane.okresy.wielki_post.daty_od || '<?php echo $harmonogram->rok; ?>-03-05';
            document.getElementById('ki_wielki_post_do').value = dane.okresy.wielki_post.daty_do || '<?php echo $harmonogram->rok; ?>-04-16';
            document.getElementById('ki_wielki_post_niedziele').value = dane.okresy.wielki_post.niedziele || '07:30,09:00,10:00,11:15,12:30,18:00';
            document.getElementById('ki_wielki_post_powszednie').value = dane.okresy.wielki_post.powszednie || '07:30,18:00';
            document.getElementById('ki_droga_krzyzowa').value = dane.okresy.wielki_post.droga_krzyzowa || '17:30';
        }
        
        // Wakacje
        if (dane.okresy.wakacje) {
            document.getElementById('ki_wakacje_od').value = dane.okresy.wakacje.daty_od || '<?php echo $harmonogram->rok; ?>-07-01';
            document.getElementById('ki_wakacje_do').value = dane.okresy.wakacje.daty_do || '<?php echo $harmonogram->rok; ?>-08-31';
            document.getElementById('ki_wakacje_niedziele').value = dane.okresy.wakacje.niedziele || '07:30,09:00,10:00,11:15,12:30,18:00';
            document.getElementById('ki_wakacje_powszednie').value = dane.okresy.wakacje.powszednie || '08:30,18:00';
            document.getElementById('ki_wakacje_soboty').value = dane.okresy.wakacje.soboty || '08:30,18:00';
        }
        
        // Okres kolędowy
        if (dane.okresy.koledowy) {
            document.getElementById('ki_koledowy_od').value = dane.okresy.koledowy.daty_od || '<?php echo $harmonogram->rok; ?>-01-02';
            document.getElementById('ki_koledowy_do').value = dane.okresy.koledowy.daty_do || '<?php echo $harmonogram->rok; ?>-01-31';
            document.getElementById('ki_koledowy_niedziele').value = dane.okresy.koledowy.niedziele || '07:30,09:00,10:00,11:15,12:30,18:00';
            document.getElementById('ki_koledowy_powszednie').value = dane.okresy.koledowy.powszednie || '08:30,18:00';
            document.getElementById('ki_koledowy_soboty').value = dane.okresy.koledowy.soboty || '08:30,18:00';
        }
    }
    
    // Dni specjalne
    if (dane.dni_specjalne) {
        if (dane.dni_specjalne.wigilia) {
            document.getElementById('ki_wigilia').value = dane.dni_specjalne.wigilia || '06:30,08:00,10:00,12:00,15:00,18:00,24:00';
        }
        if (dane.dni_specjalne.boze_narodzenie) {
            document.getElementById('ki_boze_narodzenie').value = dane.dni_specjalne.boze_narodzenie || '06:30,08:00,09:30,11:00,12:30,16:00,18:00';
        }
        if (dane.dni_specjalne.wielki_piatek) {
            document.getElementById('ki_wielki_piatek').value = dane.dni_specjalne.wielki_piatek || '08:00,10:00,15:00,18:00';
        }
        if (dane.dni_specjalne.wigilia_paschalna) {
            document.getElementById('ki_wigilia_paschalna').value = dane.dni_specjalne.wigilia_paschalna || '20:00';
        }
        if (dane.dni_specjalne.wielkanoc) {
            document.getElementById('ki_wielkanoc').value = dane.dni_specjalne.wielkanoc || '06:00,08:00,09:30,11:00,12:30,16:00,18:00';
        }
    }
    
    // SPRAWDŹ CZY ELEMENT ISTNIEJE PRZED ZMIANĄ STYLU
    const przyciskAktywuj = document.getElementById('ki-aktywuj-harmonogram');
    if (przyciskAktywuj) {
        przyciskAktywuj.style.display = 'inline-block';
    }
    console.log("Formularz wypełniony pomyślnie");
}

function zapiszHarmonogram() {
    const dane = {
        podstawowe: {
            niedziele: document.getElementById('ki_podstawowe_niedziele').value,
            powszednie: document.getElementById('ki_podstawowe_powszednie').value,
            soboty: document.getElementById('ki_podstawowe_soboty').value
        },
        okresy: {
            adwent: {
                nazwa: 'Adwent',
                daty_od: document.getElementById('ki_adwent_od').value,
                daty_do: document.getElementById('ki_adwent_do').value,
                niedziele: document.getElementById('ki_adwent_niedziele').value,
                powszednie: document.getElementById('ki_adwent_powszednie').value,
                soboty: document.getElementById('ki_adwent_soboty').value
            },
            wielki_post: {
                nazwa: 'Wielki Post',
                daty_od: document.getElementById('ki_wielki_post_od').value,
                daty_do: document.getElementById('ki_wielki_post_do').value,
                niedziele: document.getElementById('ki_wielki_post_niedziele').value,
                powszednie: document.getElementById('ki_wielki_post_powszednie').value,
                droga_krzyzowa: document.getElementById('ki_droga_krzyzowa').value
            },
            wakacje: {
                nazwa: 'Wakacje',
                daty_od: document.getElementById('ki_wakacje_od').value,
                daty_do: document.getElementById('ki_wakacje_do').value,
                niedziele: document.getElementById('ki_wakacje_niedziele').value,
                powszednie: document.getElementById('ki_wakacje_powszednie').value,
                soboty: document.getElementById('ki_wakacje_soboty').value
            },
            koledowy: {
                nazwa: 'Okres kolędowy',
                daty_od: document.getElementById('ki_koledowy_od').value,
                daty_do: document.getElementById('ki_koledowy_do').value,
                niedziele: document.getElementById('ki_koledowy_niedziele').value,
                powszednie: document.getElementById('ki_koledowy_powszednie').value,
                soboty: document.getElementById('ki_koledowy_soboty').value
            }
        },
        dni_specjalne: {
            wigilia: document.getElementById('ki_wigilia').value,
            boze_narodzenie: document.getElementById('ki_boze_narodzenie').value,
            wielki_piatek: document.getElementById('ki_wielki_piatek').value,
            wigilia_paschalna: document.getElementById('ki_wigilia_paschalna').value,
            wielkanoc: document.getElementById('ki_wielkanoc').value
        }
    };
    
    console.log("Zapisywane dane:", dane);
    
    const formData = new FormData();
    formData.append('action', 'zapisz_harmonogram');
    formData.append('nonce', '<?php echo wp_create_nonce('ki_nonce'); ?>');
    formData.append('harmonogram_id', <?php echo $harmonogram->id; ?>);
    formData.append('dane', JSON.stringify(dane));
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log("Odpowiedź zapisu:", data);
        if (data.success) {
            alert('✅ Harmonogram zapisany! Możesz teraz go aktywować.');
            harmonogramZapisany = true;
            document.getElementById('ki-aktywuj-harmonogram').style.display = 'inline-block';
        } else {
            alert('❌ Błąd zapisu: ' + data.data);
        }
    })
    .catch(error => {
        console.error('Błąd:', error);
        alert('❌ Błąd połączenia z serwerem');
    });
}

function aktywujHarmonogram() {
    if (!harmonogramZapisany) {
        alert('Najpierw zapisz harmonogram!');
        return;
    }
    
    if (!confirm('CZY NA PEWNO CHCESZ AKTYWOWAĆ TEN HARMONOGRAM?\n\nSpowoduje to:\n• Wygenerowanie kalendarza mszy na cały rok <?php echo $harmonogram->rok; ?>\n• Ustawienie harmonogramu jako aktywnego\n\nTej operacji nie można cofnąć!')) {
        return;
    }

    const przycisk = document.getElementById('ki-aktywuj-harmonogram');
    const originalText = przycisk.textContent;
    przycisk.textContent = '🔄 Generowanie kalendarza...';
    przycisk.disabled = true;

    const formData = new FormData();
    formData.append('action', 'aktywuj_harmonogram');
    formData.append('nonce', '<?php echo wp_create_nonce('ki_nonce'); ?>');
    formData.append('harmonogram_id', <?php echo $harmonogram->id; ?>);
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log("Odpowiedź aktywacji:", data);
        if (data.success) {
            alert('✅ Harmonogram aktywowany pomyślnie!\n\nKalendarz mszy został wygenerowany na cały rok <?php echo $harmonogram->rok; ?>.\n\nStrona zostanie teraz przeładowana.');
            window.location.href = '<?php echo admin_url('admin.php?page=intencje-harmonogramy'); ?>';
        } else {
            alert('❌ Błąd: ' + data.data);
            przycisk.textContent = originalText;
            przycisk.disabled = false;
        }
    })
    .catch(error => {
        console.error('Błąd:', error);
        alert('❌ Błąd połączenia z serwerem');
        przycisk.textContent = originalText;
        przycisk.disabled = false;
    });
}

function utworzHarmonogram() {
    // Zebierz dane z formularza
    const dane = {
        podstawowe: {
            niedziele: document.getElementById('ki_podstawowe_niedziele').value,
            powszednie: document.getElementById('ki_podstawowe_powszednie').value,
            soboty: document.getElementById('ki_podstawowe_soboty').value
        },
        okresy: {
            adwent: {
                nazwa: 'Adwent',
                daty_od: document.getElementById('ki_adwent_od').value,
                daty_do: document.getElementById('ki_adwent_do').value,
                niedziele: document.getElementById('ki_adwent_niedziele').value,
                powszednie: document.getElementById('ki_adwent_powszednie').value,
                soboty: document.getElementById('ki_adwent_soboty').value
            },
            wielki_post: {
                nazwa: 'Wielki Post',
                daty_od: document.getElementById('ki_wielki_post_od').value,
                daty_do: document.getElementById('ki_wielki_post_do').value,
                niedziele: document.getElementById('ki_wielki_post_niedziele').value,
                powszednie: document.getElementById('ki_wielki_post_powszednie').value,
                droga_krzyzowa: document.getElementById('ki_droga_krzyzowa').value
            },
            wakacje: {
                nazwa: 'Wakacje',
                daty_od: document.getElementById('ki_wakacje_od').value,
                daty_do: document.getElementById('ki_wakacje_do').value,
                niedziele: document.getElementById('ki_wakacje_niedziele').value,
                powszednie: document.getElementById('ki_wakacje_powszednie').value,
                soboty: document.getElementById('ki_wakacje_soboty').value
            },
            koledowy: {
                nazwa: 'Okres kolędowy',
                daty_od: document.getElementById('ki_koledowy_od').value,
                daty_do: document.getElementById('ki_koledowy_do').value,
                niedziele: document.getElementById('ki_koledowy_niedziele').value,
                powszednie: document.getElementById('ki_koledowy_powszednie').value,
                soboty: document.getElementById('ki_koledowy_soboty').value
            }
        },
        dni_specjalne: {
            wigilia: document.getElementById('ki_wigilia').value,
            boze_narodzenie: document.getElementById('ki_boze_narodzenie').value,
            wielki_piatek: document.getElementById('ki_wielki_piatek').value,
            wigilia_paschalna: document.getElementById('ki_wigilia_paschalna').value,
            wielkanoc: document.getElementById('ki_wielkanoc').value
        }
    };
    
    if (!confirm('CZY NA PEWNO CHCESZ UTWORZYĆ HARMONOGRAM?\n\nSpowoduje to wygenerowanie kalendarza mszy na cały rok <?php echo $harmonogram->rok; ?>.\n\nTej operacji nie można cofnąć!')) {
        return;
    }

    const przycisk = document.querySelector('.button-primary');
    const originalText = przycisk.textContent;
    przycisk.textContent = '🔄 Tworzenie harmonogramu...';
    przycisk.disabled = true;

    // WYŚLIJ DANE I OD RAZU AKTYWUJ
    const formData = new FormData();
    formData.append('action', 'zapisz_i_aktywuj_harmonogram');
    formData.append('nonce', '<?php echo wp_create_nonce('ki_nonce'); ?>');
    formData.append('harmonogram_id', <?php echo $harmonogram->id; ?>);
    formData.append('dane', JSON.stringify(dane));
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Harmonogram utworzony pomyślnie!\n\nKalendarz mszy został wygenerowany na cały rok <?php echo $harmonogram->rok; ?>.\n\nStrona zostanie teraz przeładowana.');
            window.location.href = '<?php echo admin_url('admin.php?page=intencje-harmonogramy'); ?>';
        } else {
            alert('❌ Błąd: ' + data.data);
            przycisk.textContent = originalText;
            przycisk.disabled = false;
        }
    })
    .catch(error => {
        alert('❌ Błąd połączenia z serwerem');
        przycisk.textContent = originalText;
        przycisk.disabled = false;
    });
}


</script>