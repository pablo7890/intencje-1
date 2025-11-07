<?php
if (!defined('ABSPATH')) {
    exit;
}

// Pobierz rok tygodnia
$obecna_niedziela = isset($_GET['tydzien']) ? sanitize_text_field($_GET['tydzien']) : date('Y-m-d', strtotime('last sunday'));
$rok_tygodnia = date('Y', strtotime($obecna_niedziela));

// Generujemy dni tygodnia OD NIEDZIELI
$dni_tygodnia = array();
for ($i = 0; $i < 7; $i++) {
    $data = date('Y-m-d', strtotime($obecna_niedziela . " +$i days"));
    $dni_tygodnia[] = array(
        'data' => $data,
        'nazwa' => $this->przetlumacz_dzien(date('D', strtotime($data))),
        'dzien' => date('d.m', strtotime($data)),
        'pelna_data' => date('d.m.Y', strtotime($data))
    );
}

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
    <h1>Intencje mszalne - Rok <?php echo $rok_tygodnia; ?> (ZARCHIWIZOWANY)</h1>
    
    <!-- KOMUNIKAT ARCHIWUM -->
    <div class="ki-komunikat-archiwum">
        <div class="ki-komunikat-header">
            <span class="ki-ikona">📁</span>
            <h3>Ten rok jest zarchiwizowany</h3>
        </div>
        <p>Możesz tylko przeglądać intencje. Aby edytować, przywróć rok do edycji.</p>
        <button type="button" class="button button-primary" onclick="przyworcRok(<?php echo $rok_tygodnia; ?>)">
            🔓 Przywróć edycję roku <?php echo $rok_tygodnia; ?>
        </button>
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

    <!-- WIDOK DNI - TYLKO PODGLĄD -->
    <div class="ki-admin-desktop">
        <div class="ki-dni-container">
            <?php foreach ($dni_tygodnia as $dzien): ?>
                <?php
                $godziny_dnia = $this->get_godziny_dla_dnia($dzien['data']);
                $czy_dzisiaj = date('Y-m-d') == $dzien['data'];
                $klasa_dzis = $czy_dzisiaj ? 'ki-dzien-dzisiaj' : '';
                ?>
                
                <div class="ki-mobile-dzien <?php echo $klasa_dzis; ?>">
                    <?php if ($czy_dzisiaj): ?>
                        <div class="ki-dzisiaj-badge">DZISIAJ</div>
                    <?php endif; ?>
                    
                    <div class="ki-mobile-dzien-header">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                            <div style="text-align: left; flex: 1;">
                                <h3 style="margin: 0 0 2px 0; font-size: 13px; font-weight: 600; color: #333;">
                                    <?php echo $dzien['nazwa']; ?>
                                </h3>
                                <span class="ki-mobile-data" style="font-size: 11px; color: #666;">
                                    <?php echo $dzien['pelna_data']; ?>
                                </span>
                            </div>
                            <span class="ki-tylko-podglad" title="Tylko podgląd - rok zarchiwizowany">🔒</span>
                        </div>
                    </div>
                    
                    <div class="ki-mobile-intencje">
                        <?php if (!empty($godziny_dnia)): ?>
                            <?php foreach ($godziny_dnia as $godzina): ?>
                                <?php
                                $intencja = $this->pobierz_intencje($dzien['data'], $godzina);
                                ?>
                                <div class="ki-mobile-msza">
                                    <div class="ki-mobile-godzina"><?php echo $godzina; ?></div>
                                    <div class="ki-mobile-intencja-tylko-podglad">
                                        <?php echo $intencja; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="ki-brak-mszy-dzien">
                                Brak mszy tego dnia
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- WIDOK MOBILE -->
    <div class="ki-admin-mobile">
        <div class="ki-mobile-dni">
            <?php foreach ($dni_tygodnia as $dzien): ?>
                <?php
                $godziny_dnia = $this->get_godziny_dla_dnia($dzien['data']);
                $czy_dzisiaj = date('Y-m-d') == $dzien['data'];
                $klasa_dzis = $czy_dzisiaj ? 'ki-dzien-dzisiaj' : '';
                ?>
                
                <div class="ki-mobile-dzien <?php echo $klasa_dzis; ?>">
                    <?php if ($czy_dzisiaj): ?>
                        <div class="ki-dzisiaj-badge">DZISIAJ</div>
                    <?php endif; ?>
                    
                    <div class="ki-mobile-dzien-header">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                            <div style="text-align: left; flex: 1;">
                                <h3 style="margin: 0 0 2px 0; font-size: 13px; font-weight: 600; color: #333;">
                                    <?php echo $dzien['nazwa']; ?>
                                </h3>
                                <span class="ki-mobile-data" style="font-size: 11px; color: #666;">
                                    <?php echo $dzien['pelna_data']; ?>
                                </span>
                            </div>
                            <span class="ki-tylko-podglad" title="Tylko podgląd - rok zarchiwizowany">🔒</span>
                        </div>
                    </div>
                    
                    <div class="ki-mobile-intencje">
                        <?php if (!empty($godziny_dnia)): ?>
                            <?php foreach ($godziny_dnia as $godzina): ?>
                                <?php
                                $intencja = $this->pobierz_intencje($dzien['data'], $godzina);
                                ?>
                                <div class="ki-mobile-msza">
                                    <div class="ki-mobile-godzina"><?php echo $godzina; ?></div>
                                    <div class="ki-mobile-intencja-tylko-podglad">
                                        <?php echo $intencja; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="ki-brak-mszy-dzien">
                                Brak mszy tego dnia
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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

// Funkcja przywracania roku z archiwum
function przyworcRok(rok) {
    if (!confirm('Czy na pewno chcesz przywrócić rok ' + rok + ' do edycji?\n\nBędziesz mógł edytować godziny mszy i intencje.')) {
        return;
    }
    
    var przycisk = document.querySelector('.ki-komunikat-archiwum button');
    var originalText = przycisk.textContent;
    przycisk.textContent = 'Przywracanie...';
    przycisk.disabled = true;
    
    jQuery.ajax({
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
                przycisk.textContent = originalText;
                przycisk.disabled = false;
            }
        },
        error: function() {
            alert('❌ Błąd połączenia z serwerem');
            przycisk.textContent = originalText;
            przycisk.disabled = false;
        }
    });
}
</script>