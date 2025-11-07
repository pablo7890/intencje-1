<?php
if (!defined('ABSPATH')) {
    exit;
}

$nowy_rok = isset($_GET['nowy_rok']) ? intval($_GET['nowy_rok']) : 0;

// ✅ DODATKOWE ZABEZPIECZENIE W WIDOKU
global $wpdb;
$table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
$istniejacy_harmonogram = $wpdb->get_var($wpdb->prepare(
    "SELECT id FROM $table_harmonogramy WHERE rok = %d", 
    $nowy_rok
));

if ($istniejacy_harmonogram) {
    // ❌ Ten kod nie powinien się wykonać dzięki zabezpieczeniu w głównej funkcji,
    // ale na wszelki wypadek dodajemy dodatkowe zabezpieczenie
    ?>
    <div class="wrap">
        <div class="ki-komunikat-blad" style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; border: 1px solid #f5c6cb; text-align: center;">
            <h2 style="color: #721c24; margin-top: 0;">🚫 Harmonogram już istnieje!</h2>
            <p>Harmonogram na rok <strong><?php echo $nowy_rok; ?></strong> już istnieje w systemie.</p>
            <p>Nie można utworzyć duplikatu harmonogramu.</p>
            <div style="margin-top: 20px;">
                <a href="<?php echo admin_url('admin.php?page=intencje-harmonogramy'); ?>" class="button button-primary">
                    ← Powrót do listy harmonogramów
                </a>
                <a href="<?php echo admin_url('admin.php?page=intencje-mszalne&tydzien=' . $nowy_rok . '-01-01'); ?>" class="button">
                    🔍 Przejdź do istniejącego harmonogramu
                </a>
            </div>
        </div>
    </div>
    <?php
    return;
}
?>

<div class="wrap">
    <h1>
        🗓️ Tworzenie harmonogramu mszy na rok: <?php echo esc_html($nowy_rok); ?>
        <a href="<?php echo admin_url('admin.php?page=intencje-harmonogramy'); ?>" class="page-title-action">
            ← Powrót do listy
        </a>
    </h1>
    
    <!-- INFORMACJE -->
    <div class="ki-harmonogram-info">
        <div class="ki-info-box">
            <strong>Rok:</strong> <?php echo esc_html($nowy_rok); ?>
        </div>
        <div class="ki-info-box">
            <strong>Status:</strong> 
            <span class="ki-status-tworzony">🛠️ Nowy harmonogram</span>
        </div>
    </div>

    <!-- FORMLARZ HARMONOGRAMU -->
    <div class="ki-harmonogram-formularz" data-rok="<?php echo $nowy_rok; ?>">
        
        <!-- GODZINY PODSTAWOWE -->
        <div class="ki-form-section">
            <h3 class="ki-section-header">
                <span class="dashicons dashicons-admin-home"></span>
                Godziny podstawowe (cały rok)
            </h3>
            <div class="ki-section-content">
                <table class="ki-godziny-table">
                    <thead>
                        <tr>
                            <th width="30%">Dzień tygodnia</th>
                            <th width="70%">Godziny mszy świętych</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Poniedziałek - Piątek</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="podstawowe_powszednie">
                                    <div class="ki-godziny-input-container">
                                        <!-- Godziny będą dodawane dynamicznie -->
                                    </div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Sobota</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="podstawowe_soboty">
                                    <div class="ki-godziny-input-container">
                                        <!-- Godziny będą dodawane dynamicznie -->
                                    </div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Niedziela i święta</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="podstawowe_niedziele">
                                    <div class="ki-godziny-input-container">
                                        <!-- Godziny będą dodawane dynamicznie -->
                                    </div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- OKRESY SPECJALNE -->
        <div class="ki-form-section">
            <h3 class="ki-section-header">
                <span class="dashicons dashicons-calendar-alt"></span>
                Okresy specjalne
            </h3>
            <div class="ki-section-content">
                
                <!-- OKRES KOLĘDOWY -->
                <div class="ki-okres-tytul">
                    🎄 Okres kolędowy
                </div>

                <div class="ki-okres-daty">
                    <div class="ki-data-group">
                        <label for="okres_koledy_od">Data rozpoczęcia:</label>
                        <input type="date" id="okres_koledy_od" class="ki-date-input">
                    </div>
                    <div class="ki-data-group">
                        <label for="okres_koledy_do">Data zakończenia:</label>
                        <input type="date" id="okres_koledy_do" class="ki-date-input">
                    </div>
                </div>

                <table class="ki-godziny-table">
                    <thead>
                        <tr>
                            <th width="30%">Dzień tygodnia</th>
                            <th width="70%">Godziny mszy świętych</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Poniedziałek - Piątek</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="okres_koledy_powszednie">
                                    <div class="ki-godziny-input-container"></div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Sobota</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="okres_koledy_soboty">
                                    <div class="ki-godziny-input-container"></div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Niedziela</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="okres_koledy_niedziele">
                                    <div class="ki-godziny-input-container"></div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- WAKACJE -->
                <div class="ki-okres-tytul" style="margin-top: 40px;">
                    ☀️ Wakacje letnie
                </div>

                <div class="ki-okres-daty">
                    <div class="ki-data-group">
                        <label for="okres_wakacje_od">Data rozpoczęcia:</label>
                        <input type="date" id="okres_wakacje_od" class="ki-date-input">
                    </div>
                    <div class="ki-data-group">
                        <label for="okres_wakacje_do">Data zakończenia:</label>
                        <input type="date" id="okres_wakacje_do" class="ki-date-input">
                    </div>
                </div>

                <table class="ki-godziny-table">
                    <thead>
                        <tr>
                            <th width="30%">Dzień tygodnia</th>
                            <th width="70%">Godziny mszy świętych</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Poniedziałek - Piątek</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="okres_wakacje_powszednie">
                                    <div class="ki-godziny-input-container"></div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Sobota</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="okres_wakacje_soboty">
                                    <div class="ki-godziny-input-container"></div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Niedziela</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="okres_wakacje_niedziele">
                                    <div class="ki-godziny-input-container"></div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- ADWENT -->
                <div class="ki-okres-tytul" style="margin-top: 40px;">
                    🕯️ Adwent
                </div>

                <div class="ki-okres-daty">
                    <div class="ki-data-group">
                        <label for="okres_adwent_od">Data rozpoczęcia:</label>
                        <input type="date" id="okres_adwent_od" class="ki-date-input">
                    </div>
                    <div class="ki-data-group">
                        <label for="okres_adwent_do">Data zakończenia:</label>
                        <input type="date" id="okres_adwent_do" class="ki-date-input">
                    </div>
                </div>

                <table class="ki-godziny-table">
                    <thead>
                        <tr>
                            <th width="30%">Dzień tygodnia</th>
                            <th width="70%">Godziny mszy świętych</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Poniedziałek - Piątek</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="okres_adwent_powszednie">
                                    <div class="ki-godziny-input-container"></div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Sobota</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="okres_adwent_soboty">
                                    <div class="ki-godziny-input-container"></div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Niedziela</strong></td>
                            <td>
                                <div class="ki-godziny-group" id="okres_adwent_niedziele">
                                    <div class="ki-godziny-input-container"></div>
                                    <div class="ki-add-godzina">
                                        <input type="time" class="ki-time-input" step="300">
                                        <button type="button" class="ki-add-godzina-btn">
                                            <span class="dashicons dashicons-plus"></span>
                                            Dodaj godzinę
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ŚWIĘTA STAŁE -->
        <div class="ki-form-section">
            <h3 class="ki-section-header">
                <span class="dashicons dashicons-star-filled"></span>
                Święta stałe i ruchome
            </h3>
            <div class="ki-section-content">
                <div class="ki-swieta-grid">
                    
                    <!-- Święta stałe -->
                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">1 stycznia - Świętej Bożej Rodzicielki</div>
                            <div class="ki-swieta-data"><?php echo $nowy_rok; ?>-01-01</div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_1_stycznia">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">6 stycznia - Trzech Króli</div>
                            <div class="ki-swieta-data"><?php echo $nowy_rok; ?>-01-06</div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_6_stycznia">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">3 maja - NMP Królowej Polski</div>
                            <div class="ki-swieta-data"><?php echo $nowy_rok; ?>-05-03</div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_3_maja">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">15 sierpnia - Wniebowzięcie NMP</div>
                            <div class="ki-swieta-data"><?php echo $nowy_rok; ?>-08-15</div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_15_sierpnia">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Święta ruchome -->
                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">Środa Popielcowa</div>
                            <div class="ki-swieta-data" id="swieto_popielec_data"></div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_popielec">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">Wielki Czwartek</div>
                            <div class="ki-swieta-data" id="swieto_wielki_czwartek_data"></div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_wielki_czwartek">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">Wielki Piątek</div>
                            <div class="ki-swieta-data" id="swieto_wielki_piatek_data"></div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_wielki_piatek">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">Wielka Sobota</div>
                            <div class="ki-swieta-data" id="swieto_wielka_sobota_data"></div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_wielka_sobota">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">Wielkanoc</div>
                            <div class="ki-swieta-data" id="swieto_wielkanoc_data"></div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_wielkanoc">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">Poniedziałek Wielkanocny</div>
                            <div class="ki-swieta-data" id="swieto_pon_wielkanocny_data"></div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_pon_wielkanocny">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">Boże Ciało</div>
                            <div class="ki-swieta-data" id="swieto_boze_cialo_data"></div>
                            <input type="hidden" id="swieto_boze_cialo_data_input">
                        </div>
                        <div class="ki-godziny-group" id="swieto_boze_cialo">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Boże Narodzenie -->
                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">24 grudnia - Wigilia</div>
                            <div class="ki-swieta-data"><?php echo $nowy_rok; ?>-12-24</div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_24_grudnia">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">25 grudnia - Boże Narodzenie</div>
                            <div class="ki-swieta-data"><?php echo $nowy_rok; ?>-12-25</div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_25_grudnia">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="ki-swieta-item">
                        <div class="ki-swieta-header">
                            <div class="ki-swieta-nazwa">26 grudnia</div>
                            <div class="ki-swieta-data"><?php echo $nowy_rok; ?>-12-26</div>
                        </div>
                        <div class="ki-godziny-group" id="swieto_26_grudnia">
                            <div class="ki-godziny-input-container"></div>
                            <div class="ki-add-godzina">
                                <input type="time" class="ki-time-input" step="300">
                                <button type="button" class="ki-add-godzina-btn">
                                    <span class="dashicons dashicons-plus"></span>
                                    Dodaj godzinę
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- WŁASNE WYJĄTKI -->
        <div class="ki-form-section">
            <h3 class="ki-section-header">
                <span class="dashicons dashicons-flag"></span>
                Własne wyjątki
            </h3>
            <div class="ki-section-content">
                <div class="ki-wyjatek-custom">
                    <h4>Dodaj własny wyjątek</h4>
                    <div class="ki-wyjatek-form">
                        <div class="ki-data-group">
                            <label for="ki_wyjatek_data">Data:</label>
                            <input type="date" id="ki_wyjatek_data" class="ki-date-input">
                        </div>
                        <div class="ki-data-group">
                            <label for="ki_wyjatek_godziny">Godziny (oddzielone przecinkami):</label>
                            <input type="text" id="ki_wyjatek_godziny" placeholder="07:30, 09:00, 18:00" class="ki-time-input">
                        </div>
                        <div>
                            <button type="button" id="ki_dodaj_wyjatek" class="ki-add-godzina-btn">
                                <span class="dashicons dashicons-plus"></span>
                                Dodaj wyjątek
                            </button>
                        </div>
                    </div>
                    
                    <div class="ki-wyjatek-added">
                        <h5>Dodane wyjątki:</h5>
                        <div id="ki_wyjatki_lista">
                            <!-- Wyjątki będą dodawane dynamicznie -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRZYCISKI AKCJI -->
        <div class="ki-form-actions">
            <button type="button" class="ki-btn-secondary" onclick="window.location.href='<?php echo admin_url('admin.php?page=intencje-harmonogramy'); ?>'">
                ❌ Anuluj
            </button>
            
            <button type="button" class="ki-btn-primary" onclick="utworzHarmonogram()">
                🚀 Utwórz harmonogram i generuj kalendarz
            </button>
        </div>
    </div>
</div>

<script>
console.log('=== ŁADOWANIE FORMULARZA HARMONOGRAMU ===');

function utworzHarmonogram() {
    if (!window.godzinyManager) {
        alert('Błąd: System zarządzania godzinami nie został załadowany');
        return;
    }

    const dane = window.godzinyManager.pobierzDaneFormularza();
    
    console.log("Dane do wysłania:", dane);
    
    if (!confirm('CZY NA PEWNO CHCESZ UTWORZYĆ HARMONOGRAM NA ROK <?php echo $nowy_rok; ?>?\n\nSpowoduje to:\n• Utworzenie harmonogramu w bazie\n• Wygenerowanie kalendarza mszy na cały rok\n\nTej operacji nie można cofnąć!')) {
        return;
    }

    const przycisk = document.querySelector('.ki-btn-primary');
    const originalText = przycisk.textContent;
    przycisk.textContent = '🔄 Tworzenie harmonogramu...';
    przycisk.disabled = true;

    // UTWÓRZ HARMONOGRAM I OD RAZU WYGENERUJ KALENDARZ
    const formData = new FormData();
    formData.append('action', 'utworz_i_aktywuj_harmonogram');
    formData.append('nonce', '<?php echo wp_create_nonce('ki_nonce'); ?>');
    formData.append('rok', <?php echo $nowy_rok; ?>);
    formData.append('dane', JSON.stringify(dane));
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log("Status odpowiedzi:", response.status);
        console.log("Status tekst:", response.statusText);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("Odpowiedź tworzenia:", data);
        if (data.success) {
            alert('✅ Harmonogram utworzony pomyślnie!\n\nKalendarz mszy został wygenerowany na cały rok <?php echo $nowy_rok; ?>.\n\nStrona zostanie teraz przeładowana.');
            window.location.href = '<?php echo admin_url('admin.php?page=intencje-harmonogramy'); ?>';
        } else {
            alert('❌ Błąd: ' + data.data);
            przycisk.textContent = originalText;
            przycisk.disabled = false;
        }
    })
    .catch(error => {
        console.error('Błąd fetch:', error);
        alert('❌ Błąd połączenia z serwerem: ' + error.message);
        przycisk.textContent = originalText;
        przycisk.disabled = false;
    });
}
</script>