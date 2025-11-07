<?php
if (!defined('ABSPATH')) {
    exit;
}

// Pobierz rok tygodnia
$obecna_niedziela = isset($_GET['tydzien']) ? sanitize_text_field($_GET['tydzien']) : date('Y-m-d', strtotime('last sunday'));
$rok_tygodnia = date('Y', strtotime($obecna_niedziela));

// Nawigacja tygodni
$poprzedni_tydzien = date('Y-m-d', strtotime($obecna_niedziela . ' -7 days'));
$nastepny_tydzien = date('Y-m-d', strtotime($obecna_niedziela . ' +7 days'));

// Pobieramy aktualnie wyświetlaną datę do formularza
$aktualna_data_dla_formularza = $obecna_niedziela;
$aktualny_dzien = date('d', strtotime($aktualna_data_dla_formularza));
$aktualny_miesiac = date('m', strtotime($aktualna_data_dla_formularza));
$aktualny_rok = date('Y', strtotime($aktualna_data_dla_formularza));
?>

<div class="wrap">
    <h1>Intencje mszalne</h1>
    
    <!-- KOMUNIKAT BRAKU HARMONOGRAMU -->
    <div class="ki-komunikat-brak-harmonogramu">
        <div class="ki-komunikat-header">
            <span class="ki-ikona">📋</span>
            <h3>Brak harmonogramu na rok <?php echo $rok_tygodnia; ?></h3>
        </div>
        <p>Nie znaleziono harmonogramu mszy świętych na rok <?php echo $rok_tygodnia; ?>.</p>
        <p>Aby zarządzać intencjami, najpierw utwórz harmonogram mszy na ten rok.</p>
        
        <div class="ki-akcje-brak-harmonogramu">
            <a href="<?php echo admin_url('admin.php?page=intencje-harmonogramy'); ?>" class="button button-primary button-large">
                🛠️ Utwórz harmonogram na rok <?php echo $rok_tygodnia; ?>
            </a>
            
            <a href="?page=intencje-mszalne" class="button">
                📅 Przejdź do bieżącego tygodnia
            </a>
        </div>
    </div>
    
    <!-- ZUNIFIKOWANY PANEL NAWIGACJI -->
    <div class="ki-panel-nawigacji">
        <!-- NAWIGACJA TYGODNI -->
        <span class="ki-tydzien-info">
                Tydzień: <?php echo date('d.m.Y', strtotime($obecna_niedziela)); ?> - <?php echo date('d.m.Y', strtotime($obecna_niedziela . ' +6 days')); ?>
        </span>
        <div class="ki-nawigacja-lewa">
            <a href="?page=intencje-mszalne&tydzien=<?php echo $poprzedni_tydzien; ?>" class="button ki-btn-nawigacja">
                ← Poprzedni
            </a>
            
            <a href="?page=intencje-mszalne" class="button ki-btn-biezacy">
                Bieżący
            </a>
            
            <a href="?page=intencje-mszalne&tydzien=<?php echo $nastepny_tydzien; ?>" class="button ki-btn-nawigacja">
                Następny →
            </a>
        </div>
        
        <!-- PRAWA STRONA - SELEKTOR DATY -->
        <div class="ki-nawigacja-prawa">
            <span class="ki-selektor-tytul">Przejdź do:</span>
            
            <div class="ki-selektor-inputy">
                <select id="ki_dzien_admin">
                    <?php for ($i = 1; $i <= 31; $i++): 
                        $wartosc = sprintf('%02d', $i);
                        $wybrany = ($i == intval($aktualny_dzien)) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $wartosc; ?>" <?php echo $wybrany; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                
                <select id="ki_miesiac_admin">
                    <?php
                    $miesiace = array(1 => 'Styczeń', 2 => 'Luty', 3 => 'Marzec', 4 => 'Kwiecień', 5 => 'Maj', 6 => 'Czerwiec', 
                                    7 => 'Lipiec', 8 => 'Sierpień', 9 => 'Wrzesień', 10 => 'Październik', 11 => 'Listopad', 12 => 'Grudzień');
                    foreach ($miesiace as $nr => $nazwa): 
                        $wartosc = sprintf('%02d', $nr);
                        $wybrany = ($nr == intval($aktualny_miesiac)) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $wartosc; ?>" <?php echo $wybrany; ?>>
                            <?php echo $nazwa; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select id="ki_rok_admin">
                    <?php 
                    $obecny_rok = date('Y');
                    $aktualny_rok = date('Y', strtotime($aktualna_data_dla_formularza));
                    
                    // Pokaż lata z harmonogramami + 2 lata w przód
                    $lata_do_pokazania = array();
                    global $wpdb;
                    $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
                    $harmonogramy = $wpdb->get_results("SELECT rok FROM $table_harmonogramy ORDER BY rok DESC");
                    
                    foreach ($harmonogramy as $h) {
                        $lata_do_pokazania[$h->rok] = true;
                    }
                    
                    for ($rok = $obecny_rok; $rok <= $obecny_rok + 2; $rok++) {
                        $lata_do_pokazania[$rok] = true;
                    }
                    
                    krsort($lata_do_pokazania);
                    
                    foreach ($lata_do_pokazania as $rok => $value): 
                        $wybrany = ($rok == intval($aktualny_rok)) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $rok; ?>" <?php echo $wybrany; ?>>
                            <?php echo $rok; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="button" onclick="przejdzDoDatyAdmin()" class="button ki-btn-skocz">
                    🗓️ Pokaż tydzień
                </button>
            </div>
        </div>
    </div>

    <!-- DODATKOWE INFORMACJE -->
    <div class="ki-dodatkowe-informacje" style="margin-top: 30px; background: #f8f9fa; padding: 25px; border-radius: 12px; border-left: 4px solid #0073aa;">
        <h4 style="margin-top: 0; color: #2c3338;">💡 Czym jest harmonogram Mszy świętych?</h4>
        <p style="margin-bottom: 15px; color: #555;">Harmonogram mszy to zestawienie wszystkich godzin mszy świętych w ciągu całego roku. Pozwala on na:</p>
        <ul style="color: #666; line-height: 1.6;">
            <li><strong>Automatyczne generowanie kalendarza</strong> mszalnego na cały rok</li>
            <li><strong>Definiowanie godzin</strong> dla różnych dni tygodnia i okresów specjalnych</li>
            <li><strong>Zarządzanie wyjątkami</strong> jak święta, adwent, wakacje</li>
            <li><strong>Przypisywanie intencji</strong> do konkretnych godzin</li>
        </ul>
    </div>
</div>

<script>
// Funkcja dla selektora daty w adminie
function przejdzDoDatyAdmin() {
    var dzien = document.getElementById('ki_dzien_admin').value;
    var miesiac = document.getElementById('ki_miesiac_admin').value;
    var rok = document.getElementById('ki_rok_admin').value;
    var pelnaData = rok + '-' + miesiac + '-' + dzien;
    
    var url = '?page=intencje-mszalne&data_start=' + pelnaData;
    window.location.href = url;
}
</script>