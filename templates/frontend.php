<?php
if (!defined('ABSPATH')) {
    exit;
}

// Pobieramy aktualny tydzień - od NIEDZIELI
$obecna_niedziela = date('Y-m-d', strtotime('sunday this week'));
if (isset($_GET['tydzien'])) {
    $obecna_niedziela = sanitize_text_field($_GET['tydzien']);
}

// Obsługa wyboru konkretnej daty
if (isset($_GET['data_start']) && !empty($_GET['data_start'])) {
    $wybrana_data = sanitize_text_field($_GET['data_start']);
    // POPRAWKA: Pobieramy niedzielę TEGO tygodnia w którym jest wybrana data
    $obecna_niedziela = date('Y-m-d', strtotime('sunday last week', strtotime($wybrana_data)));
}

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
$poprzedni_tydzien = date('Y-m-d', strtotime($obecna_niedziela . ' -1 week'));
$nastepny_tydzien = date('Y-m-d', strtotime($obecna_niedziela . ' +1 week'));

// Generujemy opcje dla selektora dat
$obecny_rok = date('Y');

// Pobieramy aktualnie wyświetlaną datę do formularza
$aktualna_data_dla_formularza = $obecna_niedziela; // Niedziela tygodnia
$aktualny_dzien = date('d', strtotime($aktualna_data_dla_formularza));
$aktualny_miesiac = date('m', strtotime($aktualna_data_dla_formularza));
$aktualny_rok = date('Y', strtotime($aktualna_data_dla_formularza));
?>

<div class="ki-frontend">
    <!-- Nagłówek i nawigacja -->
    <div class="ki-naglowek">
        <h2>Intencje mszalne</h2>
        
        <div class="ki-harmonogram-info" style="text-align: center; margin: 5px 0 15px 0; font-size: 14px; color: #666;">
            <?php 
            global $wpdb;
            $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
            $aktywny = $wpdb->get_row("SELECT * FROM $table_harmonogramy WHERE status = 'aktywny' LIMIT 1");
            
            if ($aktywny) {
                echo "📅 Aktualny harmonogram: <strong>{$aktywny->nazwa}</strong>";
            } else {
                echo "📅 Harmonogram tymczasowy";
            }
            ?>
        </div>

        <div class="ki-nawigacja">
            <a href="?tydzien=<?php echo $poprzedni_tydzien; ?>" class="ki-przycisk">← Poprzedni tydzień</a>
            <span class="ki-tydzien-info">
                <?php echo date('d.m.Y', strtotime($obecna_niedziela)); ?> - <?php echo date('d.m.Y', strtotime($obecna_niedziela . ' +6 days')); ?>
            </span>
            <a href="?tydzien=<?php echo $nastepny_tydzien; ?>" class="ki-przycisk">Następny tydzień →</a>
            <button class="ki-przycisk ki-drukuj">📄 Pobierz PDF</button>
            <a href="?" class="ki-przycisk">Bieżący tydzień</a>
        </div>

        <!-- PROSTY selektor daty -->
        <div style="margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
            <h3 style="margin: 0 0 15px 0; color: #333; text-align: center; font-size: 16px;">🔍 Przejdź do konkretnego tygodnia:</h3>
            
            <div style="display: flex; align-items: flex-end; gap: 8px; flex-wrap: wrap; justify-content: center;">
                <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                    <label style="color: #333; font-weight: 600; text-align: center; font-size: 12px;">Dzień:</label>
                    <select id="ki_dzien" style="padding: 5px 8px; border: 1px solid #ccc; border-radius: 4px; min-width: 55px; text-align: center; font-size: 12px; height: 32px;">
                        <?php 
                        for ($i = 1; $i <= 31; $i++): 
                            $wartosc = sprintf('%02d', $i);
                            $wybrany = ($i == intval($aktualny_dzien)) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $wartosc; ?>" <?php echo $wybrany; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                    <label style="color: #333; font-weight: 600; text-align: center; font-size: 12px;">Miesiąc:</label>
                    <select id="ki_miesiac" style="padding: 5px 8px; border: 1px solid #ccc; border-radius: 4px; min-width: 95px; text-align: center; font-size: 12px; height: 32px;">
                        <?php
                        $miesiace = array(
                            1 => 'Styczeń', 2 => 'Luty', 3 => 'Marzec', 4 => 'Kwiecień',
                            5 => 'Maj', 6 => 'Czerwiec', 7 => 'Lipiec', 8 => 'Sierpień', 
                            9 => 'Wrzesień', 10 => 'Październik', 11 => 'Listopad', 12 => 'Grudzień'
                        );
                        foreach ($miesiace as $nr => $nazwa): 
                            $wartosc = sprintf('%02d', $nr);
                            $wybrany = ($nr == intval($aktualny_miesiac)) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $wartosc; ?>" <?php echo $wybrany; ?>>
                                <?php echo $nazwa; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                    <label style="color: #333; font-weight: 600; text-align: center; font-size: 12px;">Rok:</label>
                    <select id="ki_rok" style="padding: 5px 8px; border: 1px solid #ccc; border-radius: 4px; min-width: 75px; text-align: center; font-size: 12px; height: 32px;">
                        <?php for ($rok = $obecny_rok; $rok <= $obecny_rok + 2; $rok++): 
                            $wybrany = ($rok == intval($aktualny_rok)) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $rok; ?>" <?php echo $wybrany; ?>>
                                <?php echo $rok; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <button type="button" onclick="przejdzDoDatyFrontend()" style="padding: 5px 12px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; font-weight: bold; color: #333; cursor: pointer; font-size: 12px; height: 32px;">
                    POKAŻ TYDZIEŃ
                </button>
            </div>
            
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; font-size: 12px;">
                <strong>Szybki wybór:</strong>
                <a href="?data_start=<?php echo date('Y-m-d'); ?>" style="color: #333; margin: 0 4px; text-decoration: underline; font-size: 12px;">Bieżący tydzień</a> • 
                <a href="?data_start=<?php echo date('Y-m-d', strtotime('+1 week')); ?>" style="color: #333; margin: 0 4px; text-decoration: underline; font-size: 12px;">Następny tydzień</a> • 
                <a href="?data_start=2025-05-03" style="color: #333; margin: 0 4px; text-decoration: underline; font-size: 12px;">3 Maja 2025</a> • 
                <a href="?data_start=2025-12-24" style="color: #333; margin: 0 4px; text-decoration: underline; font-size: 12px;">24 Grudnia 2025</a>
            </div>
        </div>
         
    </div>

    <!-- Lista intencji -->
    <div class="ki-lista-intencji">
        <?php foreach ($dni_tygodnia as $dzien): ?>
            <?php
            $godziny_dnia = $this->get_godziny_dla_dnia($dzien['data']);
            if (!empty($godziny_dnia)): 
            ?>
                <div class="ki-dzien-item">
                    <div class="ki-dzien-header">
                        <h3><?php echo $dzien['nazwa']; ?></h3>
                        <span class="ki-data"><?php echo $dzien['pelna_data']; ?></span>
                    </div>
                    
                    <div class="ki-intencje-dnia">
                        <?php foreach ($godziny_dnia as $godzina): ?>
                            <?php
                            $intencja = $this->pobierz_intencje($dzien['data'], $godzina);
                            ?>
                            <div class="ki-msza-item">
                                <div class="ki-godzina"><?php echo $godzina; ?></div>
                                <div class="ki-intencja"><?php echo esc_html($intencja); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <!-- Informacja dla wiernych -->
    <div class="ki-info">
        <p><strong>Informacja:</strong> Intencje mszalne można zamawiać w biurze parafialnym.</p>
    </div>
</div>

<script>
function przejdzDoDatyFrontend() {
    var dzien = document.getElementById('ki_dzien').value;
    var miesiac = document.getElementById('ki_miesiac').value;
    var rok = document.getElementById('ki_rok').value;
    
    // Tworzymy datę w formacie YYYY-MM-DD
    var pelnaData = rok + '-' + miesiac + '-' + dzien;
    
    // Sprawdzamy czy jest parametr page
    var urlParams = new URLSearchParams(window.location.search);
    var pageParam = urlParams.get('page');
    
    // Budujemy URL
    var url = '?data_start=' + pelnaData;
    if (pageParam) {
        url += '&page=' + pageParam;
    }
    
    // Przekierowujemy
    window.location.href = url;
}

// Funkcja do generowania PDF
function generujPDF() {
    var dataStart = new URLSearchParams(window.location.search).get('data_start') || '<?php echo date('Y-m-d'); ?>';
    
    // Pokazujemy loader
    var przycisk = document.querySelector('.ki-drukuj');
    var originalText = przycisk.textContent;
    przycisk.textContent = 'Generowanie PDF...';
    przycisk.disabled = true;
    
    // Wysyłamy AJAX
    var formData = new FormData();
    formData.append('action', 'generuj_pdf_intencji');
    formData.append('nonce', '<?php echo wp_create_nonce('ki_nonce'); ?>');
    formData.append('data_start', dataStart);
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.blob())
    .then(blob => {
        // Tworzymy link do pobrania
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'intencje_mszalne_' + dataStart + '.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        // Przywracamy przycisk
        przycisk.textContent = originalText;
        przycisk.disabled = false;
    })
    .catch(error => {
        console.error('Błąd:', error);
        przycisk.textContent = originalText;
        przycisk.disabled = false;
        alert('Błąd podczas generowania PDF');
    });
}

// Podpinamy nową funkcję do przycisku
document.querySelector('.ki-drukuj').addEventListener('click', function(e) {
    e.preventDefault();
    generujPDF();
});
</script>
