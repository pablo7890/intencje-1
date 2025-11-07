<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>
        Tworzenie harmonogramu mszy na rok: <?php echo esc_html($harmonogram->rok); ?>
        <a href="<?php echo admin_url('admin.php?page=intencje-harmonogramy'); ?>" class="page-title-action">
            ← Powrót do listy
        </a>
    </h1>
    
    <!-- INFORMACJE -->
    <div class="ki-harmonogram-info">
        <div class="ki-info-box">
            <strong>Rok:</strong> <?php echo esc_html($harmonogram->rok); ?>
        </div>
        <div class="ki-info-box">
            <strong>Status:</strong> 
            <span class="ki-status-tworzony">🛠️ W przygotowaniu</span>
        </div>
    </div>

    <!-- FORMLARZ HARMONOGRAMU - NOWY WYGLĄD -->
    <div class="ki-harmonogram-formularz-nowy">
        <form id="ki-form-harmonogram-nowy">
            
            <!-- GODZINY PODSTAWOWE -->
            <div class="ki-sekcja-formularza">
                <h3>🏠 Godziny mszy w ciągu roku</h3>
                <p class="ki-opis-sekcji">Podstawowe godziny mszy świętych obowiązujące przez cały rok.</p>
                
                <div class="ki-godziny-grupa-nowa">
                    <label class="ki-label">Niedziele i święta:</label>
                    <input type="text" class="ki-input-godziny" 
                           value="07:30,09:00,10:00,11:15,12:30,18:00"
                           placeholder="07:30,09:00,10:00,11:15,12:30,18:00">
                    <small class="ki-hint">Godziny oddzielone przecinkami</small>
                </div>
                
                <div class="ki-godziny-grupa-nowa">
                    <label class="ki-label">Dni powszednie (poniedziałek-piątek):</label>
                    <input type="text" class="ki-input-godziny" 
                           value="07:30,08:30,18:00,19:00"
                           placeholder="07:30,08:30,18:00,19:00">
                </div>
                
                <div class="ki-godziny-grupa-nowa">
                    <label class="ki-label">Soboty:</label>
                    <input type="text" class="ki-input-godziny" 
                           value="07:30,08:30,18:00"
                           placeholder="07:30,08:30,18:00">
                </div>
            </div>

            <!-- OKRESY SPECJALNE -->
            <div class="ki-sekcja-formularza">
                <h3>📅 Okresy specjalne w roku</h3>
                <p class="ki-opis-sekcji">Okresy z innymi godzinami mszy niż podstawowe.</p>
                
                <!-- OKRES KOLĘDOWY -->
                <div class="ki-okres-nowy">
                    <div class="ki-okres-header">
                        <h4>🎄 Okres kolędowy (styczeń)</h4>
                        <label class="ki-checkbox-label">
                            <input type="checkbox" class="ki-okres-aktywny" checked>
                            <span class="ki-checkbox-custom"></span>
                            Aktywuj ten okres
                        </label>
                    </div>
                    
                    <div class="ki-okres-daty-nowe">
                        <div class="ki-data-grupa-nowa">
                            <label>Od:</label>
                            <input type="date" value="<?php echo $harmonogram->rok; ?>-01-02">
                        </div>
                        <div class="ki-data-grupa-nowa">
                            <label>Do:</label>
                            <input type="date" value="<?php echo $harmonogram->rok; ?>-01-31">
                        </div>
                    </div>
                    
                    <div class="ki-okres-godziny-nowe">
                        <div class="ki-godziny-grupa-nowa">
                            <label>Niedziele kolędowe:</label>
                            <input type="text" value="07:30,09:00,10:00,11:15,12:30,18:00">
                        </div>
                        <div class="ki-godziny-grupa-nowa">
                            <label>Dni powszednie kolędowe:</label>
                            <input type="text" value="08:30,18:00">
                        </div>
                        <div class="ki-godziny-grupa-nowa">
                            <label>Soboty kolędowe:</label>
                            <input type="text" value="08:30,18:00">
                        </div>
                    </div>
                </div>

                <!-- WIELKI POST -->
                <div class="ki-okres-nowy">
                    <div class="ki-okres-header">
                        <h4>🕊️ Wielki Post</h4>
                        <label class="ki-checkbox-label">
                            <input type="checkbox" class="ki-okres-aktywny" checked>
                            <span class="ki-checkbox-custom"></span>
                            Aktywuj ten okres
                        </label>
                    </div>
                    
                    <div class="ki-okres-daty-nowe">
                        <div class="ki-data-grupa-nowa">
                            <label>Od:</label>
                            <input type="date" value="<?php echo $harmonogram->rok; ?>-03-06">
                        </div>
                        <div class="ki-data-grupa-nowa">
                            <label>Do:</label>
                            <input type="date" value="<?php echo $harmonogram->rok; ?>-04-17">
                        </div>
                    </div>
                    
                    <div class="ki-okres-godziny-nowe">
                        <div class="ki-godziny-grupa-nowa">
                            <label>Niedziele wielkopostne:</label>
                            <input type="text" value="07:30,09:00,10:00,11:15,12:30,18:00">
                        </div>
                        <div class="ki-godziny-grupa-nowa">
                            <label>Dni powszednie wielkopostne:</label>
                            <input type="text" value="07:30,18:00">
                        </div>
                        <div class="ki-godziny-grupa-nowa">
                            <label>Droga krzyżowa (piątki):</label>
                            <input type="text" value="17:30">
                        </div>
                    </div>
                </div>

                <!-- WAKACJE -->
                <div class="ki-okres-nowy">
                    <div class="ki-okres-header">
                        <h4>☀️ Wakacje letnie</h4>
                        <label class="ki-checkbox-label">
                            <input type="checkbox" class="ki-okres-aktywny" checked>
                            <span class="ki-checkbox-custom"></span>
                            Aktywuj ten okres
                        </label>
                    </div>
                    
                    <div class="ki-okres-daty-nowe">
                        <div class="ki-data-grupa-nowa">
                            <label>Od:</label>
                            <input type="date" value="<?php echo $harmonogram->rok; ?>-07-01">
                        </div>
                        <div class="ki-data-grupa-nowa">
                            <label>Do:</label>
                            <input type="date" value="<?php echo $harmonogram->rok; ?>-08-31">
                        </div>
                    </div>
                    
                    <div class="ki-okres-godziny-nowe">
                        <div class="ki-godziny-grupa-nowa">
                            <label>Niedziele wakacyjne:</label>
                            <input type="text" value="07:30,09:00,10:00,11:15,12:30,18:00">
                        </div>
                        <div class="ki-godziny-grupa-nowa">
                            <label>Dni powszednie wakacyjne:</label>
                            <input type="text" value="08:30,18:00">
                        </div>
                        <div class="ki-godziny-grupa-nowa">
                            <label>Soboty wakacyjne:</label>
                            <input type="text" value="08:30,18:00">
                        </div>
                    </div>
                </div>

                <!-- ADWENT -->
                <div class="ki-okres-nowy">
                    <div class="ki-okres-header">
                        <h4>🕯️ Adwent</h4>
                        <label class="ki-checkbox-label">
                            <input type="checkbox" class="ki-okres-aktywny" checked>
                            <span class="ki-checkbox-custom"></span>
                            Aktywuj ten okres
                        </label>
                    </div>
                    
                    <div class="ki-okres-daty-nowe">
                        <div class="ki-data-grupa-nowa">
                            <label>Od:</label>
                            <input type="date" value="<?php echo $harmonogram->rok; ?>-12-01">
                        </div>
                        <div class="ki-data-grupa-nowa">
                            <label>Do:</label>
                            <input type="date" value="<?php echo $harmonogram->rok; ?>-12-23">
                        </div>
                    </div>
                    
                    <div class="ki-okres-godziny-nowe">
                        <div class="ki-godziny-grupa-nowa">
                            <label>Niedziele adwentowe:</label>
                            <input type="text" value="07:30,09:00,10:00,11:15,12:30,18:00">
                        </div>
                        <div class="ki-godziny-grupa-nowa">
                            <label>Dni powszednie adwentowe:</label>
                            <input type="text" value="07:30,18:00">
                        </div>
                        <div class="ki-godziny-grupa-nowa">
                            <label>Roraty (rano):</label>
                            <input type="text" value="06:30">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ŚWIĘTA SPECJALNE -->
            <div class="ki-sekcja-formularza">
                <h3>🎆 Święta i dni specjalne</h3>
                <p class="ki-opis-sekcji">Pojedyncze dni z wyjątkowymi godzinami mszy.</p>
                
                <div class="ki-swieta-lista">
                    <!-- BOŻE NARODZENIE -->
                    <div class="ki-dzien-specjalny">
                        <div class="ki-dzien-specjalny-header">
                            <h4>🎄 Boże Narodzenie</h4>
                            <label class="ki-checkbox-label">
                                <input type="checkbox" class="ki-dzien-aktywny" checked>
                                <span class="ki-checkbox-custom"></span>
                                Aktywuj
                            </label>
                        </div>
                        <div class="ki-dzien-specjalny-daty">
                            <div class="ki-data-grupa-nowa">
                                <label>Wigilia (<?php echo $harmonogram->rok; ?>-12-24):</label>
                                <input type="text" value="06:30,08:00,10:00,12:00,15:00,18:00,24:00">
                            </div>
                            <div class="ki-data-grupa-nowa">
                                <label>Boże Narodzenie (<?php echo $harmonogram->rok; ?>-12-25):</label>
                                <input type="text" value="06:30,08:00,09:30,11:00,12:30,16:00,18:00">
                            </div>
                        </div>
                    </div>

                    <!-- WIELKANOC -->
                    <div class="ki-dzien-specjalny">
                        <div class="ki-dzien-specjalny-header">
                            <h4>🐣 Triduum Paschalne</h4>
                            <label class="ki-checkbox-label">
                                <input type="checkbox" class="ki-dzien-aktywny" checked>
                                <span class="ki-checkbox-custom"></span>
                                Aktywuj
                            </label>
                        </div>
                        <div class="ki-dzien-specjalny-daty">
                            <?php
                            // Oblicz datę Wielkanocy
                            $easter_date = date('Y-m-d', easter_date($harmonogram->rok));
                            $good_friday = date('Y-m-d', strtotime($easter_date . ' -2 days'));
                            $holy_saturday = date('Y-m-d', strtotime($easter_date . ' -1 days'));
                            ?>
                            <div class="ki-data-grupa-nowa">
                                <label>Wielki Piątek (<?php echo $good_friday; ?>):</label>
                                <input type="text" value="08:00,10:00,15:00,18:00">
                            </div>
                            <div class="ki-data-grupa-nowa">
                                <label>Wigilia Paschalna (<?php echo $holy_saturday; ?>):</label>
                                <input type="text" value="20:00">
                            </div>
                            <div class="ki-data-grupa-nowa">
                                <label>Wielkanoc (<?php echo $easter_date; ?>):</label>
                                <input type="text" value="06:00,08:00,09:30,11:00,12:30,16:00,18:00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRZYCISKI AKCJI -->
            <div class="ki-akcje-formularza-nowe">
                <button type="button" class="button button-secondary" onclick="window.history.back()">
                    ❌ Anuluj
                </button>
                
                <button type="button" class="button button-primary button-large" onclick="utworzKalendarz()">
                    🚀 Utwórz harmonogram i generuj kalendarz
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function utworzKalendarz() {
    if (!confirm('CZY NA PEWNO CHCESZ UTWORZYĆ HARMONOGRAM?\n\nSpowoduje to wygenerowanie kalendarza mszy na cały rok ' + <?php echo $harmonogram->rok; ?> + '.\n\nTej operacji nie można cofnąć!')) {
        return;
    }
    
    // Tutaj kod do zbierania danych z formularza i wysyłania AJAX
    alert('Harmonogram zostanie utworzony!');
    // Przekierowanie lub AJAX...
}
</script>