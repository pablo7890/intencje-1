<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Harmonogramy mszy świętych</h1>
    
    <!-- STATYSTYKI - NOWY WYGLĄD -->
    <div class="ki-harmonogramy-stats">
        <div class="ki-stat-cards">
            <div class="ki-stat-card">
                <div class="ki-stat-icon">📅</div>
                <div class="ki-stat-content">
                    <div class="ki-stat-number"><?php echo count($harmonogramy); ?></div>
                    <div class="ki-stat-label">Wszystkie harmonogramy</div>
                </div>
            </div>
            
            <div class="ki-stat-card ki-stat-card-active">
                <div class="ki-stat-icon">✅</div>
                <div class="ki-stat-content">
                    <div class="ki-stat-number">
                        <?php echo count(array_filter($harmonogramy, function($h) { 
                            return $h->archiwum == 0; 
                        })); ?>
                    </div>
                    <div class="ki-stat-label">Aktywne</div>
                </div>
            </div>
            
            <div class="ki-stat-card ki-stat-card-archived">
                <div class="ki-stat-icon">📁</div>
                <div class="ki-stat-content">
                    <div class="ki-stat-number">
                        <?php echo count(array_filter($harmonogramy, function($h) { 
                            return $h->archiwum == 1; 
                        })); ?>
                    </div>
                    <div class="ki-stat-label">Zarchiwizowane</div>
                </div>
            </div>

            <div class="ki-stat-card ki-stat-card-current">
                <div class="ki-stat-icon">🎯</div>
                <div class="ki-stat-content">
                    <div class="ki-stat-number"><?php echo date('Y'); ?></div>
                    <div class="ki-stat-label">Rok bieżący</div>
                </div>
            </div>
        </div>
    </div>

    <!-- PRZYCISK TWORZENIA NOWEGO HARMONOGRAMU -->
    <div class="ki-nowy-harmonogram">
        <button type="button" class="button button-primary button-large" 
                onclick="pokazModalNowegoHarmonogramu()">
            <span class="dashicons dashicons-plus" style="margin-right: 8px;"></span>
            Utwórz nowy harmonogram
        </button>
    </div>

    <!-- LISTA HARMONOGRAMÓW - POPRAWIONA TABELA -->
    <div class="ki-table-container">
        <table class="wp-list-table widefat fixed striped ki-harmonogramy-table">
            <thead>
                <tr>
                    <th width="80">Rok</th>
                    <th width="200">Nazwa</th>
                    <th width="120">Status</th>
                    <th width="140">Data utworzenia</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($harmonogramy)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">
                            <div style="color: #666; font-size: 16px;">
                                <span class="dashicons dashicons-calendar-alt" style="font-size: 48px; width: 48px; height: 48px; margin-bottom: 15px; display: block; color: #ccc;"></span>
                                Brak harmonogramów.<br>
                                <small>Utwórz pierwszy harmonogram, aby rozpocząć.</small>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($harmonogramy as $harmonogram): ?>
                        <tr>
                            <td class="ki-rok-column" data-label="Rok">
                                <strong style="font-size: 18px; color: #2c3338;"><?php echo esc_html($harmonogram->rok); ?></strong>
                            </td>
                            <td data-label="Nazwa">
                                <div style="font-weight: 500; color: #2c3338;"><?php echo esc_html($harmonogram->nazwa); ?></div>
                            </td>
                            <td data-label="Status">
                                <?php if ($harmonogram->archiwum == 1): ?>
                                    <span class="ki-status-badge ki-status-zarchiwizowany">
                                        <span class="dashicons dashicons-portfolio" style="margin-right: 4px;"></span>
                                        W Archiwum
                                    </span>
                                <?php else: ?>
                                    <span class="ki-status-badge ki-status-aktywny">
                                        <span class="dashicons dashicons-yes-alt" style="margin-right: 4px;"></span>
                                        Aktywny
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Data utworzenia">
                                <div style="font-size: 13px; color: #666;">
                                    <?php echo date('d.m.Y', strtotime($harmonogram->data_utworzenia)); ?>
                                    <br>
                                    <small style="color: #999;"><?php echo date('H:i', strtotime($harmonogram->data_utworzenia)); ?></small>
                                </div>
                            </td>
                            <td data-label="Akcje">
                                <div class="ki-akcje-harmonogram">
                                    <?php if ($harmonogram->archiwum == 0): ?>
                                        <!-- HARMONOGRAM AKTYWNY -->
                                        <a href="<?php echo admin_url('admin.php?page=intencje-mszalne&tydzien=' . $harmonogram->rok . '-01-01'); ?>" 
                                        class="button button-primary ki-btn-action" title="Przejdź do edycji intencji">
                                            <span class="dashicons dashicons-edit" style="margin-right: 4px;"></span>
                                            Edytuj intencje
                                        </a>
                                        
                                        <button type="button" class="button ki-btn-action ki-btn-archive"
                                                data-rok="<?php echo $harmonogram->rok; ?>"
                                                title="Archiwizuj ten rok">
                                            <span class="dashicons dashicons-portfolio" style="margin-right: 4px;"></span>
                                            Archiwizuj
                                        </button>

                                    <?php else: ?>
                                        <!-- HARMONOGRAM ZARCHIWIZOWANY -->
                                        <a href="<?php echo admin_url('admin.php?page=intencje-mszalne&tydzien=' . $harmonogram->rok . '-01-01'); ?>" 
                                        class="button ki-btn-action" title="Przejdź do podglądu">
                                            <span class="dashicons dashicons-visibility" style="margin-right: 4px;"></span>
                                            Podgląd intencji
                                        </a>
                                        
                                        <button type="button" class="button button-primary ki-btn-action ki-btn-restore"
                                                data-rok="<?php echo $harmonogram->rok; ?>"
                                                title="Przywróć do edycji">
                                            <span class="dashicons dashicons-backup" style="margin-right: 4px;"></span>
                                            Przywróć
                                        </button>
                                    <?php endif; ?>

                                    <!-- PRZYCISK USUWANIA DLA WSZYSTKICH -->
                                    <button type="button" class="button ki-btn-action ki-btn-delete"
                                            data-rok="<?php echo $harmonogram->rok; ?>"
                                            title="Usuń harmonogram i wszystkie dane">
                                        <span class="dashicons dashicons-trash" style="margin-right: 4px;"></span>
                                        Usuń
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL NOWEGO HARMONOGRAMU -->
<div id="ki-modal-nowy-harmonogram" class="ki-modal" style="display: none;">
    <div class="ki-modal-content">
        <div class="ki-modal-header">
            <h2>Utwórz nowy harmonogram</h2>
        </div>
        
        <div class="ki-modal-body">
            <div class="ki-form-group">
                <label for="ki_rok"><strong>Wybierz rok:</strong></label>
                <select id="ki_rok" name="rok" required style="padding: 12px; font-size: 16px; width: 100%; max-width: 200px;">
                    <option value="">-- Wybierz rok --</option>
                    <?php 
                    $obecny_rok = date('Y');
                    for ($rok = $obecny_rok; $rok <= $obecny_rok + 2; $rok++): 
                        // Sprawdź czy harmonogram już istnieje
                        $istnieje = false;
                        foreach ($harmonogramy as $h) {
                            if ($h->rok == $rok) {
                                $istnieje = true;
                                break;
                            }
                        }
                        
                        if (!$istnieje):
                    ?>
                        <option value="<?php echo $rok; ?>"><?php echo $rok; ?></option>
                    <?php 
                        endif;
                    endfor; 
                    ?>
                </select>
            </div>
<!--
            <div class="ki-uwaga-modal">
                <div class="dashicons dashicons-info" style="float: left; margin-right: 10px; color: #ffb900; font-size: 20px;"></div>
                <p><strong>Informacja:</strong> Harmonogram zostanie utworzony dopiero po wypełnieniu formularza godzin i kliknięciu "Utwórz harmonogram".</p>
            </div>
                -->
        </div>
        
        <div class="ki-modal-footer">
            <button type="button" class="button" onclick="zamknijModalNowegoHarmonogramu()">Anuluj</button>
            <button type="button" id="ki-przejdz-do-formularza" class="button button-primary">
                <span class="dashicons dashicons-arrow-right-alt" style="margin-right: 8px;"></span>
                Dalej
            </button>
        </div>
    </div>
</div>

<script>
// Funkcje do modala
function pokazModalNowegoHarmonogramu() {
    document.getElementById('ki-modal-nowy-harmonogram').style.display = 'flex';
}

function zamknijModalNowegoHarmonogramu() {
    document.getElementById('ki-modal-nowy-harmonogram').style.display = 'none';
}

// Główny kod jQuery
jQuery(document).ready(function($) {
    $('#ki-przejdz-do-formularza').on('click', function(e) {
        e.preventDefault();
        
        var rok = $('#ki_rok').val();
        
        if (!rok) {
            alert('Proszę wybrać rok');
            return;
        }
        
        // PRZEJDŹ DO FORMULARZA BEZ TWORZENIA HARMONOGRAMU
        window.location.href = '<?php echo admin_url('admin.php?page=intencje-harmonogramy&nowy_rok='); ?>' + rok;
    });
});
</script>