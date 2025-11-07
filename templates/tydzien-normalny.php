<?php
if (!defined('ABSPATH')) {
    exit;
}

// POPRAWIONE: Dokładne obliczenie niedzieli bieżącego tygodnia
$dzis = date('Y-m-d');
$dzien_tygodnia = date('w', strtotime($dzis));

if ($dzien_tygodnia == 0) {
    $obecna_niedziela = $dzis;
} else {
    $obecna_niedziela = date('Y-m-d', strtotime("last sunday", strtotime($dzis)));
}

if (isset($_GET['tydzien'])) {
    $obecna_niedziela = sanitize_text_field($_GET['tydzien']);
}

// POPRAWIONE: Obsługa wyboru konkretnej daty
if (isset($_GET['data_start']) && !empty($_GET['data_start'])) {
    $wybrana_data = sanitize_text_field($_GET['data_start']);
    
    $timestamp = strtotime($wybrana_data);
    $dzien_tygodnia = date('w', $timestamp);
    
    $obecna_niedziela = date('Y-m-d', strtotime("-$dzien_tygodnia days", $timestamp));
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

    <!-- DODAJ TEN KOMUNIKAT NA GÓRZE WIDOKU -->
    <?php
    $obecny_rok = date('Y');
    $tydzien_rok = date('Y', strtotime($obecna_niedziela));
    // Sprawdź czy tydzień zawiera dni z różnych lat (prosta wersja)
    $data_ostatniego_dnia = date('Y-m-d', strtotime("$obecna_niedziela +6 days"));
    $rok_ostatniego_dnia = date('Y', strtotime($data_ostatniego_dnia));
    $czy_mieszany_tydzien = ($tydzien_rok != $rok_ostatniego_dnia);
    
    if ($czy_mieszany_tydzien): ?>
    <div class="notice notice-info">
        <p>📅 <strong>Uwaga:</strong> Ten tydzień zawiera dni z roku <strong><?php echo $tydzien_rok; ?></strong> i <strong><?php echo $rok_ostatniego_dnia; ?></strong>. 
        Dni należące do lat bez aktywnego harmonogramu mają zablokowaną edycję.</p>
    </div>
    <?php endif; ?>

    <!-- WIDOK DESKTOP - KARTY DNI -->
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
                            <button class="ki-edytuj-godziny-btn" 
                                    data-data="<?php echo $dzien['data']; ?>"
                                    title="Edytuj godziny mszy">
                                🕐
                            </button>
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
                                    <div class="ki-mobile-intencja-edytowalna" 
                                        data-data="<?php echo $dzien['data']; ?>"
                                        data-godzina="<?php echo $godzina; ?>">
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
                            <button class="ki-edytuj-godziny-btn" 
                                    data-data="<?php echo $dzien['data']; ?>"
                                    title="Edytuj godziny mszy">
                                🕐
                            </button>
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
                                    <div class="ki-mobile-intencja-edytowalna" 
                                        data-data="<?php echo $dzien['data']; ?>"
                                        data-godzina="<?php echo $godzina; ?>">
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
    
    <!-- Instrukcja -->
    <div class="ki-instrukcja">
        <p><strong>💡 Instrukcja:</strong> Kliknij na intencję aby edytować. Każda msza może mieć wiele intencji. <span class="ki-instrukcja-dodatkowa">Intencje za parafian oznaczono ikoną 🏛️</span></p>
    </div>
</div>

<script>
// CZEKAJ NA ZAŁADOWANIE jQuery
if (typeof jQuery === 'undefined') {
    console.error('jQuery nie jest załadowane!');
} else {
    console.log('jQuery załadowane:', jQuery.fn.jquery);
}

// Dane dla AJAX
var ki_ajax = {
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('ki_nonce'); ?>'
};

// Funkcja dla selektora daty w adminie
function przejdzDoDatyAdmin() {
    var dzien = document.getElementById('ki_dzien_admin').value;
    var miesiac = document.getElementById('ki_miesiac_admin').value;
    var rok = document.getElementById('ki_rok_admin').value;
    var pelnaData = rok + '-' + miesiac + '-' + dzien;
    
    var url = '?page=intencje-mszalne&data_start=' + pelnaData;
    window.location.href = url;
}

// Zmienne globalne
var formularzOtwarty = false;
var aktualnyFormularz = null;

// ===== OBSŁUGA KLIKNIĘCIA W INTENCJĘ =====
document.addEventListener('DOMContentLoaded', function() {
    // Sprawdź czy jQuery jest dostępne
    if (typeof jQuery === 'undefined') {
        console.error('BŁĄD: jQuery nie jest dostępne!');
        return;
    }
    
    var $ = jQuery;
    
    // Oznaczanie wolnych/zajętych intencji
    $('.ki-mobile-intencja-edytowalna').each(function() {
        var tekst = $(this).text().trim();
        if (tekst === 'Wolna intencja' || tekst === '') {
            $(this).addClass('ki-wolna');
        } else {
            $(this).addClass('ki-zajeta');
        }
    });
    
    $('body').on('click', '.ki-mobile-intencja-edytowalna', function(e) {
        if (typeof $ === 'undefined') {
            console.error('jQuery $ nie jest zdefiniowane!');
            return;
        }
        
        // USUŃ WSZYSTKIE MODALE I EVENTY
        $('.ki-formularz-edycji-modal').remove();
        $(document).off('keydown.ki-modal');
        
        if (formularzOtwarty) return;
        
        formularzOtwarty = true;
        aktualnyFormularz = $(this);
        
        var $element = $(this);
        var data = $element.data('data');
        var godzina = $element.data('godzina');
        
        console.log("Kliknięto intencję:", data, godzina);
        pobierzIntencjeMszy(data, godzina, $element);
    });

    // Inicjalizuj edycję godzin
    inicjalizujEdycjeGodzin();
});

// ===== FUNKCJE SYSTEMU INTENCJI =====
function pobierzIntencjeMszy(data, godzina, $element) {
    // Sprawdź czy jQuery jest dostępne
    if (typeof jQuery === 'undefined') {
        console.error('jQuery nie jest dostępne w pobierzIntencjeMszy');
        return;
    }
    
    var $ = jQuery;
    
    $.ajax({
        url: ki_ajax.url,
        type: 'POST',
        data: {
            action: 'pobierz_intencje_mszy',
            nonce: ki_ajax.nonce,
            data: data,
            godzina: godzina
        },
        success: function(response) {
            console.log("Odpowiedź AJAX:", response);
            if (response.success) {
                pokazFormularzIntencji($element, data, godzina, response.data);
            } else {
                alert('Błąd ładowania intencji: ' + response.data);
                formularzOtwarty = false;
            }
        },
        error: function(xhr, status, error) {
            console.error('Błąd AJAX:', error);
            alert('Błąd połączenia z serwerem');
            formularzOtwarty = false;
        }
    });
}

function pokazFormularzIntencji($element, data, godzina, intencje) {
    if (typeof jQuery === 'undefined') {
        console.error('jQuery nie jest dostępne w pokazFormularzIntencji');
        return;
    }
    
    var $ = jQuery;
    
    // USUŃ WSZYSTKIE POTENCJALNE MODALE
    $('.ki-formularz-edycji-modal').remove();
    
    // BLOKADA SCROLL
    $('body').css('overflow', 'hidden');
    
    // Lista istniejących intencji
    var listaIntencjiHTML = '';
    if (intencje.length > 0) {
        listaIntencjiHTML = `
            <div style="margin-bottom:20px; max-height:200px; overflow-y:auto; padding:10px; background:#f8f9fa; border-radius:4px;">
                <h4 style="margin:0 0 10px 0; color:#333;">Aktualne intencje:</h4>
        `;
        
        intencje.forEach(function(intencja, index) {
            var ofiaraText = '';
            var emojiText = '';
            
            if (intencja.za_parafian == 1) {
                emojiText = ' 🏛️';
                ofiaraText = '';
            } else {
                emojiText = '';
                ofiaraText = ' (' + parseFloat(intencja.ofiara || 0).toFixed(2) + ' zł)';
            }
            
            listaIntencjiHTML += `
                <div class="ki-intencja-item" data-intencja-id="${intencja.id}" style="display:flex; justify-content:space-between; align-items:center; padding:8px 10px; margin-bottom:5px; background:white; border-radius:4px; border-left:3px solid ${intencja.za_parafian ? '#46b450' : '#0073aa'};">
                    <div style="flex:1;">
                        <strong>${intencja.intencja_text}</strong>
                        ${emojiText}${ofiaraText}
                    </div>
                    <div style="display:flex; gap:5px; margin-left:10px;">
                        ${index > 0 ? `<button type="button" class="button ki-przesun-gore" style="padding:2px 6px; font-size:12px;" title="Przesuń w górę">⬆️</button>` : ''}
                        ${index < intencje.length - 1 ? `<button type="button" class="button ki-przesun-dol" style="padding:2px 6px; font-size:12px;" title="Przesuń w dół">⬇️</button>` : ''}
                        <button type="button" class="button ki-edytuj-intencje" style="padding:2px 6px; font-size:12px; background:#0073aa; color:white;" title="Edytuj">✏️</button>
                        <button type="button" class="button ki-usun-intencje" style="padding:2px 6px; font-size:12px; background:#dc3232; color:white;" title="Usuń">🗑️</button>
                    </div>
                </div>
            `;
        });
        
        listaIntencjiHTML += `</div>`;
    } else {
        listaIntencjiHTML = `
            <div style="margin-bottom:15px; padding:15px; background:#f0f0f0; border-radius:4px; text-align:center;">
                Brak intencji dla tej mszy
            </div>
        `;
    }
    
    // UTWÓRZ MODAL
    var modalHTML = `
        <div class="ki-formularz-edycji-modal" style="position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999; display:flex; align-items:center; justify-content:center;">
            <div class="ki-modal-overlay" style="position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7);"></div>
            <div class="ki-modal-content" style="position:relative; background:white; border-radius:12px; padding:30px; max-width:600px; width:90vw; max-height:85vh; overflow-y:auto; z-index:10000; border:2px solid #0073aa; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <h3 style="margin:0 0 20px 0; color:#333; text-align:center; font-size:18px; border-bottom:2px solid #f0f0f0; padding-bottom:15px;">
                    Dodawanie intencji<br>
                    <small style="font-size:14px; color:#666; display:block; margin-top:5px;">${data} ${godzina}</small>
                </h3>
                
                ${listaIntencjiHTML}
                
                <div class="ki-nowa-intencja" style="background:#f8f9fa; padding:20px; border-radius:6px; border:1px solid #ddd;">
                    <h4 style="margin:0 0 15px 0; color:#333;">Dodaj nową intencję:</h4>
                    
                    <input type="hidden" id="ki-edytowana-intencja-id" value="">
                    
                    <div style="margin-bottom:15px;">
                        <input type="checkbox" name="za_parafian" id="checkbox_${data}_${godzina.replace(':', '')}_${Date.now()}">
                        <label style="font-weight:bold; margin-left:5px;">za parafian</label>
                    </div>
                    
                    <div style="margin-bottom:15px;">
                        <label style="font-weight:bold; display:block; margin-bottom:5px;">Ofiara (zł):</label>
                        <input type="number" name="ofiara" step="0.01" min="0" value="0.00" style="width:100px; padding:8px 10px; border:1px solid #ccc; border-radius:4px;">
                    </div>
                    
                    <div style="margin-bottom:20px;">
                        <label style="font-weight:bold; display:block; margin-bottom:8px;">Intencja:</label>
                        <textarea name="intencja" style="width:100%; min-height:100px; padding:12px; border:1px solid #ccc; border-radius:6px; font-size:14px; resize:vertical;"></textarea>
                    </div>
                    
                    <button type="button" class="button button-primary ki-dodaj-intencje" style="width:100%; padding:12px; background:#0073aa; color:white; border:none; border-radius:4px; font-size:15px; font-weight:bold;">➕ Dodaj intencję</button>
                    <button type="button" class="button button-success ki-zapisz-edycje" style="width:100%; padding:12px; background:#46b450; color:white; border:none; border-radius:4px; font-size:15px; font-weight:bold; margin-top:10px; display:none;">💾 Zapisz zmiany</button>
                    <button type="button" class="button ki-anuluj-edycje" style="width:100%; padding:12px; background:#727272; color:white; border:none; border-radius:4px; font-size:15px; margin-top:10px; display:none;">❌ Anuluj edycję</button>
                </div>
                
                <div style="margin-top:25px; padding-top:20px; border-top:1px solid #eee; text-align:center;">
                    <button type="button" class="button ki-zamknij-modal" style="padding:10px 20px; background:#727272; color:white; border:none; border-radius:4px;">Zamknij</button>
                </div>
            </div>
        </div>
    `;
    
    // DODAJ BEZPOŚREDNIO DO BODY
    $('body').append(modalHTML);
    
    // OBSŁUGA ZAMYKANIA
    $('.ki-modal-overlay, .ki-zamknij-modal').on('click', function() {
        zamknijModal();
    });
    
    // ESC
    $(document).on('keydown.ki-modal', function(e) {
        if (e.keyCode === 27) {
            zamknijModal();
        }
    });
    
    // ZAPOBIEGAJ PROPAGACJI
    $('.ki-modal-content').on('click', function(e) {
        e.stopPropagation();
    });
    
    // OBSŁUGA CHECKBOX
    $('input[name="za_parafian"]').on('change', function() {
        var $textarea = $('textarea[name="intencja"]');
        var $ofiara = $('input[name="ofiara"]');
        
        if ($(this).is(':checked')) {
            $textarea.val('za parafian').prop('readonly', true).css('background-color', '#f5f5f5');
            $ofiara.val('0.00').prop('readonly', true);
        } else {
            $textarea.val('').prop('readonly', false).css('background-color', '');
            $ofiara.val('0.00').prop('readonly', false);
        }
    });
    
    // DODAWANIE INTENCJI
    $('.ki-dodaj-intencje').on('click', function() {
        var intencja = $('textarea[name="intencja"]').val().trim();
        var za_parafian = $('input[name="za_parafian"]').is(':checked') ? 1 : 0;
        var ofiara = $('input[name="ofiara"]').val();
        
        if (!intencja) {
            alert('Proszę wpisać intencję');
            return;
        }
        
        if (za_parafian) {
            intencja = "za parafian";
        }
        
        dodajNowaIntencje(data, godzina, intencja, za_parafian, ofiara, $element);
    });
    
    // USUWANIE INTENCJI
    $('.ki-usun-intencje').on('click', function() {
        var $item = $(this).closest('.ki-intencja-item');
        var intencjaId = $item.data('intencja-id');
        
        if (confirm('Czy na pewno chcesz usunąć tę intencję?')) {
            usunIntencje(intencjaId, data, godzina, $element);
        }
    });
    
    // ZMIANA KOLEJNOŚCI
    $('.ki-przesun-gore').on('click', function() {
        var $item = $(this).closest('.ki-intencja-item');
        var intencjaId = $item.data('intencja-id');
        zmienKolejnoscIntencji(intencjaId, 'up', data, godzina, $element);
    });
    
    $('.ki-przesun-dol').on('click', function() {
        var $item = $(this).closest('.ki-intencja-item');
        var intencjaId = $item.data('intencja-id');
        zmienKolejnoscIntencji(intencjaId, 'down', data, godzina, $element);
    });
    
    // EDYCJA INTENCJI
    $('.ki-edytuj-intencje').on('click', function() {
        var $item = $(this).closest('.ki-intencja-item');
        var intencjaId = $item.data('intencja-id');
        
        // Znajdź intencję w danych
        var intencjaDoEdycji = null;
        intencje.forEach(function(intencja) {
            if (intencja.id == intencjaId) {
                intencjaDoEdycji = intencja;
            }
        });
        
        if (intencjaDoEdycji) {
            wlaczTrybEdycji(intencjaDoEdycji);
        }
    });
    
    // ZAPISZ EDYCJĘ
    $('.ki-zapisz-edycje').on('click', function() {
        var intencjaId = $('#ki-edytowana-intencja-id').val();
        var nowaIntencja = $('textarea[name="intencja"]').val().trim();
        var za_parafian = $('input[name="za_parafian"]').is(':checked') ? 1 : 0;
        var ofiara = $('input[name="ofiara"]').val();
        
        if (!nowaIntencja) {
            alert('Proszę wpisać intencję');
            return;
        }
        
        if (za_parafian) {
            nowaIntencja = "za parafian";
        }
        
        zapiszEdycjeIntencji(intencjaId, data, godzina, nowaIntencja, za_parafian, ofiara, $element);
    });
    
    // ANULUJ EDYCJĘ
    $('.ki-anuluj-edycje').on('click', function() {
        wylaczTrybEdycji();
    });
}

function zamknijModal() {
    if (typeof jQuery === 'undefined') return;
    
    var $ = jQuery;
    $('.ki-formularz-edycji-modal').remove();
    $('body').css('overflow', '');
    $(document).off('keydown.ki-modal');
    formularzOtwarty = false;
    aktualnyFormularz = null;
}

// ===== POZOSTAŁE FUNKCJE =====
function wlaczTrybEdycji(intencja) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    console.log("Włączam edycję intencji:", intencja);
    
    // Wypełnij formularz danymi intencji
    $('textarea[name="intencja"]').val(intencja.intencja_text);
    $('input[name="ofiara"]').val(intencja.ofiara);
    $('#ki-edytowana-intencja-id').val(intencja.id);
    
    // Ustaw checkbox
    $('input[name="za_parafian"]').prop('checked', intencja.za_parafian == 1);
    
    // Zmień stan formularza
    if (intencja.za_parafian == 1) {
        $('textarea[name="intencja"]').prop('readonly', true).css('background-color', '#f5f5f5');
        $('input[name="ofiara"]').prop('readonly', true);
    } else {
        $('textarea[name="intencja"]').prop('readonly', false).css('background-color', '');
        $('input[name="ofiara"]').prop('readonly', false);
    }
    
    // Zmień tytuły
    $('.ki-modal-content h3').html('Edycja intencji<br><small style="font-size:14px; color:#666;">ID: ' + intencja.id + '</small>');
    $('.ki-nowa-intencja h4').text('Edytuj intencję:');
    
    // Pokaz/ukryj przyciski
    $('.ki-dodaj-intencje').hide();
    $('.ki-zapisz-edycje').show();
    $('.ki-anuluj-edycje').show();
}

function wylaczTrybEdycji() {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    // Wyczyść formularz
    $('textarea[name="intencja"]').val('').prop('readonly', false).css('background-color', '');
    $('input[name="za_parafian"]').prop('checked', false);
    $('input[name="ofiara"]').val('0.00').prop('readonly', false);
    $('#ki-edytowana-intencja-id').val('');
    
    // Przywróć tytuły
    $('.ki-modal-content h3').html('Dodawanie intencji<br><small style="font-size:14px; color:#666;">' + 'data' + ' ' + 'godzina' + '</small>');
    $('.ki-nowa-intencja h4').text('Dodaj nową intencję:');
    
    // Pokaz/ukryj przyciski
    $('.ki-dodaj-intencje').show();
    $('.ki-zapisz-edycje').hide();
    $('.ki-anuluj-edycje').hide();
}

function dodajNowaIntencje(data, godzina, intencja, za_parafian, ofiara, $element) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    console.log("Dodawanie intencji:", data, godzina, intencja);
    
    var $przycisk = $('.ki-dodaj-intencje');
    var originalText = $przycisk.text();
    $przycisk.text('Dodawanie...').prop('disabled', true);

    $.ajax({
        url: ki_ajax.url,
        type: 'POST',
        data: {
            action: 'dodaj_intencje_do_mszy',
            nonce: ki_ajax.nonce,
            data: data,
            godzina: godzina,
            intencja: intencja,
            za_parafian: za_parafian,
            ofiara: ofiara
        },
        success: function(response) {
            console.log("Odpowiedź:", response);
            
            if (response.success) {
                pokazPowiadomienie('Intencja dodana!', 'success');
                setTimeout(function() {
                    location.reload();
                }, 800);
            } else {
                pokazPowiadomienie('Błąd: ' + response.data, 'error');
                $przycisk.text(originalText).prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            pokazPowiadomienie('Błąd połączenia', 'error');
            $przycisk.text(originalText).prop('disabled', false);
        }
    });
}

function zapiszEdycjeIntencji(intencjaId, data, godzina, intencja, za_parafian, ofiara, $element) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    console.log("=== ZAPIS EDYCJI ===");
    console.log("ID:", intencjaId);
    console.log("Intencja:", intencja);
    console.log("Za parafian:", za_parafian);
    console.log("Ofiara:", ofiara);
    
    var $przycisk = $('.ki-zapisz-edycje');
    var originalText = $przycisk.text();
    $przycisk.text('Zapisywanie...').prop('disabled', true);

    $.ajax({
        url: ki_ajax.url,
        type: 'POST',
        data: {
            action: 'edytuj_intencje',
            nonce: ki_ajax.nonce,
            intencja_id: intencjaId,
            intencja: intencja,
            za_parafian: za_parafian,
            ofiara: ofiara
        },
        success: function(response) {
            console.log("Odpowiedź edycji:", response);
            
            if (response.success) {
                pokazPowiadomienie('Intencja zaktualizowana!', 'success');
                setTimeout(function() {
                    location.reload();
                }, 800);
            } else {
                pokazPowiadomienie('Błąd: ' + response.data, 'error');
                $przycisk.text(originalText).prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            pokazPowiadomienie('Błąd połączenia', 'error');
            $przycisk.text(originalText).prop('disabled', false);
        }
    });
}

function usunIntencje(intencjaId, data, godzina, $element) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    $.ajax({
        url: ki_ajax.url,
        type: 'POST',
        data: {
            action: 'usun_intencje',
            nonce: ki_ajax.nonce,
            intencja_id: intencjaId
        },
        success: function(response) {
            if (response.success) {
                pokazPowiadomienie('Intencja usunięta!', 'success');
                setTimeout(function() {
                    location.reload();
                }, 800);
            } else {
                pokazPowiadomienie('Błąd: ' + response.data, 'error');
            }
        },
        error: function() {
            pokazPowiadomienie('Błąd połączenia', 'error');
        }
    });
}

function zmienKolejnoscIntencji(intencjaId, kierunek, data, godzina, $element) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    $.ajax({
        url: ki_ajax.url,
        type: 'POST',
        data: {
            action: 'zmien_kolejnosc_intencji',
            nonce: ki_ajax.nonce,
            intencja_id: intencjaId,
            kierunek: kierunek
        },
        success: function(response) {
            if (response.success) {
                pokazPowiadomienie('Kolejność zmieniona!', 'success');
                setTimeout(function() {
                    location.reload();
                }, 800);
            } else {
                pokazPowiadomienie('Błąd: ' + response.data, 'error');
            }
        },
        error: function() {
            pokazPowiadomienie('Błąd połączenia', 'error');
        }
    });
}

function pokazPowiadomienie(wiadomosc, typ = 'success') {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    var $powiadomienie = $('<div class="ki-powiadomienie" style="position:fixed; top:20px; right:20px; padding:15px 20px; border-radius:6px; color:white; font-weight:bold; z-index:10000; box-shadow:0 4px 12px rgba(0,0,0,0.3); max-width:300px;">' + wiadomosc + '</div>');
    
    if (typ === 'success') {
        $powiadomienie.css('background', '#46b450');
    } else {
        $powiadomienie.css('background', '#dc3232');
    }
    
    $('body').append($powiadomienie);
    
    setTimeout(function() {
        $powiadomienie.animate({opacity:0}, 300, function() {
            $powiadomienie.remove();
        });
    }, 3000);
}


// ===== SYSTEM EDYCJI GODZIN MSZY =====

// Funkcja inicjalizacji po załadowaniu DOM
function inicjalizujEdycjeGodzin() {
    if (typeof jQuery === 'undefined') {
        console.error('jQuery nie jest dostępne dla edycji godzin');
        return;
    }
    
    var $ = jQuery;
    
    // Obsługa kliknięcia w przycisk edycji godzin
    $('body').on('click', '.ki-edytuj-godziny-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var data = $(this).data('data');
        console.log("Edytuj godziny dla:", data);
        pobierzGodzinyDnia(data);
    });
}

// Pobierz godziny i intencje dla dnia
function pobierzGodzinyDnia(data) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    $.ajax({
        url: ki_ajax.url,
        type: 'POST',
        data: {
            action: 'pobierz_godziny_dnia',
            nonce: ki_ajax.nonce,
            data: data
        },
        success: function(response) {
            console.log("Odpowiedź godzin:", response);
            if (response.success) {
                pokazModalEdycjiGodzin(data, response.data.godziny);
            } else {
                alert('Błąd ładowania godzin: ' + response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Błąd AJAX:', error);
            alert('Błąd połączenia z serwerem');
        }
    });
}

// Pokaz modal edycji godzin
function pokazModalEdycjiGodzin(data, godziny) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    // Usuń istniejące modale
    $('.ki-modal-godziny').remove();
    
    // Blokada scroll
    $('body').css('overflow', 'hidden');
    
    // Generuj listę godzin
    var listaGodzinHTML = '';
    
    if (godziny.length > 0) {
        godziny.forEach(function(godzinaData) {
            var godzina = godzinaData.godzina;
            var intencje = godzinaData.intencje;
            var liczbaIntencji = godzinaData.liczba_intencji;
            
            var intencjeHTML = '';
            if (liczbaIntencji === 0) {
                intencjeHTML = '<div class="ki-intencje-details" style="color: #46b450;">✓ Wolna intencja</div>';
            } else {
                var listaIntencji = intencje.map(function(intencja, index) {
                    var ofiara = intencja.za_parafian ? '' : ' (' + parseFloat(intencja.ofiara || 0).toFixed(2) + ' zł)';
                    var emoji = intencja.za_parafian ? ' 🏛️' : '';
                    return '• ' + intencja.intencja_text + emoji + ofiara;
                }).join('<br>');
                
                intencjeHTML = '<div class="ki-intencje-details" style="color: #dc3232;">' + 
                              liczbaIntencji + ' intencje:<br>' + listaIntencji + 
                              '</div>';
            }
            
            var przyciskUsun = liczbaIntencji === 0 ? 
                '<button class="ki-btn-usun-godzine" data-godzina="' + godzina + '">🗑️ Usuń</button>' :
                '<button class="ki-btn-usun-godzine" disabled title="Nie można usunąć - są intencje">🚫 Usuń</button>';
            
            listaGodzinHTML += `
                <div class="ki-godzina-item">
                    <div class="ki-godzina-info">
                        <div class="ki-godzina-text">${godzina}</div>
                        ${intencjeHTML}
                    </div>
                    <div class="ki-godzina-actions">
                        ${przyciskUsun}
                    </div>
                </div>
            `;
        });
    } else {
        listaGodzinHTML = `
            <div style="text-align: center; padding: 30px; color: #666; font-style: italic;">
                Brak mszy w tym dniu
            </div>
        `;
    }

    // Instrukcja
    var instrukcjaHTML = `
        <div style="margin-top: 20px; padding: 15px; background: #f0f6ff; border: 1px solid #0073aa; border-radius: 6px; border-left: 4px solid #0073aa;">
            <h4 style="margin: 0 0 8px 0; color: #0073aa; font-size: 14px;">💡 Informacja</h4>
            <p style="margin: 0; font-size: 13px; color: #333; line-height: 1.4;">
                Godzin z intencjami nie można usunąć. Aby usunąć taką godzinę, 
                najpierw przejdź do widoku tygodnia, usuń wszystkie intencje dla danej mszy, 
                a następnie wróć tutaj, aby usunąć godzinę.
            </p>
        </div>
    `;
    
    // Utwórz modal
    var modalHTML = `
        <div class="ki-modal-godziny">
            <div class="ki-modal-godziny-overlay"></div>
            <div class="ki-modal-godziny-content">
                <h3 style="margin: 0 0 20px 0; color: #333; text-align: center; font-size: 18px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                    Edytuj godziny mszy<br>
                    <small style="font-size: 14px; color: #666; display: block; margin-top: 5px;">${data}</small>
                </h3>
                
                <div class="ki-lista-godzin">
                    ${listaGodzinHTML}
                </div>
                
                <div class="ki-dodaj-godzine-grupa">
                    <input type="time" class="ki-input-godzina" id="ki-nowa-godzina" step="300">
                    <button class="button button-primary ki-dodaj-godzine">➕ Dodaj godzinę</button>
                </div>
                
                ${instrukcjaHTML}

                <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                    <button type="button" class="button ki-zamknij-godziny-modal" style="padding: 10px 20px; background: #727272; color: white; border: none; border-radius: 4px;">Zamknij</button>
                </div>
            </div>
        </div>
    `;
    
    // Dodaj do body
    $('body').append(modalHTML);
    
    // Obsługa zamykania
    $('.ki-modal-godziny-overlay, .ki-zamknij-godziny-modal').on('click', function() {
        zamknijModalGodzin();
    });
    
    // ESC zamyka modal
    $(document).on('keydown.ki-godziny-modal', function(e) {
        if (e.keyCode === 27) {
            zamknijModalGodzin();
        }
    });
    
    // Zapobiegaj propagacji
    $('.ki-modal-godziny-content').on('click', function(e) {
        e.stopPropagation();
    });
    
    // Obsługa usuwania godzin
    $('.ki-btn-usun-godzine:not(:disabled)').on('click', function() {
        var godzina = $(this).data('godzina');
        usunGodzine(data, godzina);
    });
    
    // Obsługa disabled przycisków (pokaz szczegóły)
    $('.ki-btn-usun-godzine:disabled').on('click', function() {
        var $godzinaItem = $(this).closest('.ki-godzina-item');
        var godzina = $godzinaItem.find('.ki-godzina-text').text();
        var intencjeDetails = $godzinaItem.find('.ki-intencje-details').html();
        
        pokazSzczegolyIntencji(godzina, intencjeDetails);
    });
    
    // Obsługa dodawania nowej godziny
    $('.ki-dodaj-godzine').on('click', function() {
        var nowaGodzina = $('#ki-nowa-godzina').val();
        if (!nowaGodzina) {
            alert('Proszę wybrać godzinę');
            return;
        }
        
        dodajGodzine(data, nowaGodzina);
    });
    
    // Enter w polu godziny
    $('#ki-nowa-godzina').on('keypress', function(e) {
        if (e.keyCode === 13) {
            $('.ki-dodaj-godzine').click();
        }
    });
}

// Usuń godzinę
function usunGodzine(data, godzina) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    if (!confirm('Czy na pewno chcesz usunąć godzinę ' + godzina + '?')) {
        return;
    }
    
    $.ajax({
        url: ki_ajax.url,
        type: 'POST',
        data: {
            action: 'usun_godzine_z_dnia',
            nonce: ki_ajax.nonce,
            data: data,
            godzina: godzina
        },
        success: function(response) {
            if (response.success) {
                pokazPowiadomienie('Godzina usunięta!', 'success');
                // Odśwież modal
                pobierzGodzinyDnia(data);
            } else {
                alert('Błąd: ' + response.data);
            }
        },
        error: function(xhr, status, error) {
            alert('Błąd połączenia');
        }
    });
}

// Dodaj godzinę
function dodajGodzine(data, godzina) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    $.ajax({
        url: ki_ajax.url,
        type: 'POST',
        data: {
            action: 'dodaj_godzine_do_dnia',
            nonce: ki_ajax.nonce,
            data: data,
            godzina: godzina
        },
        success: function(response) {
            if (response.success) {
                pokazPowiadomienie('Godzina dodana!', 'success');
                // Wyczyść pole i odśwież modal
                $('#ki-nowa-godzina').val('');
                pobierzGodzinyDnia(data);
            } else {
                alert('Błąd: ' + response.data);
            }
        },
        error: function(xhr, status, error) {
            alert('Błąd połączenia');
        }
    });
}

// Pokaz szczegóły intencji (dla disabled przycisków)
function pokazSzczegolyIntencji(godzina, intencjeHTML) {
    // Zamień <br> na \n dla alertu
    var tekstIntencji = intencjeHTML.replace(/<br>/g, '\n').replace(/<[^>]*>/g, '');
    
    var message = `⚠️ Nie można usunąć godziny ${godzina}\n\n`;
    message += tekstIntencji;
    message += `\n\nAby usunąć godzinę, najpierw usuń intencje w widoku tygodnia.`;
    
    alert(message);
}

// Zamknij modal godzin
function zamknijModalGodzin() {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    $('.ki-modal-godziny').remove();
    $('body').css('overflow', '');
    $(document).off('keydown.ki-godziny-modal');
    
    // AUTOMATYCZNE ODSWIEŻENIE STRONY
    console.log("Zamykam modal godzin - odświeżam stronę");
    setTimeout(function() {
        location.reload();
    }, 300); // Małe opóźnienie dla płynności
}

// Inicjalizacja po załadowaniu DOM
document.addEventListener('DOMContentLoaded', function() {
    inicjalizujEdycjeGodzin();
});
</script>