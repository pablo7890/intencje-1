<?php
/**
 * Plugin Name: Intencje mszalne
 * Description: Zaawansowany system intencji mszalnych z harmonogramami
 * Version: 3.2
 * Author: Twoja Parafia
 */

// Zabezpieczenie przed bezpośrednim dostępem
if (!defined('ABSPATH')) {
    exit;
}

// Definiujemy stałe pluginu
define('KI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('KI_DB_VERSION', '3.2');

// Główna klasa pluginu
class IntencjeMszalne {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'aktualizuj_baze'));
        add_action('plugins_loaded', array($this, 'sprawdz_wersje_bazy'));
    }

    /**
     * TWORZENIE TABEL - Z KOLUMNĄ ARCHIWUM
     */
    public function aktualizuj_baze() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabela harmonogramów rocznych
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        $sql_harmonogramy = "CREATE TABLE $table_harmonogramy (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            rok smallint(4) NOT NULL,
            nazwa varchar(100) NOT NULL,
            archiwum tinyint(1) DEFAULT 0,
            data_utworzenia datetime DEFAULT CURRENT_TIMESTAMP,
            data_aktywacji datetime NULL,
            PRIMARY KEY (id),
            UNIQUE KEY rok (rok)
        ) $charset_collate;";
        
        // Tabela kalendarza (faktyczne dni) - UPROSZCZONA!
        $table_kalendarz = $wpdb->prefix . 'msze_kalendarz';
        $sql_kalendarz = "CREATE TABLE $table_kalendarz (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            data date NOT NULL,
            godziny_msz text NOT NULL,
            harmonogram_id mediumint(9) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY data_harmonogram (data, harmonogram_id),
            KEY harmonogram_id (harmonogram_id)
        ) $charset_collate;";

        // Tabela msze (faktyczne msze) - BEZ ZMIAN
        $table_msze = $wpdb->prefix . 'msze';
        $sql_msze = "CREATE TABLE $table_msze (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            data date NOT NULL,
            godzina time NOT NULL,
            typ_mszy varchar(30) DEFAULT 'standardowa',
            uwagi text DEFAULT '',
            max_intencji smallint(3) DEFAULT 1,
            data_utworzenia datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY data_godzina (data, godzina),
            KEY data (data)
        ) $charset_collate;";

        // Tabela intencji - BEZ ZMIAN
        $table_intencje = $wpdb->prefix . 'intencje_mszalne';
        $sql_intencje = "CREATE TABLE $table_intencje (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            msza_id mediumint(9) NOT NULL,
            intencja_text text NOT NULL,
            za_parafian tinyint(1) DEFAULT 0,
            ofiara decimal(10,2) DEFAULT 0.00,
            data_przyjecia date NOT NULL,
            kolejnosc smallint(3) DEFAULT 1,
            status varchar(20) DEFAULT 'aktywna',
            data_utworzenia datetime DEFAULT CURRENT_TIMESTAMP,
            data_modyfikacji datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY msza_id (msza_id),
            KEY data_przyjecia (data_przyjecia),
            KEY za_parafian (za_parafian),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_harmonogramy);
        dbDelta($sql_kalendarz);
        dbDelta($sql_msze);
        dbDelta($sql_intencje);
        
        add_option('ki_db_version', KI_DB_VERSION);
    }
    
    public function sprawdz_wersje_bazy() {
        if (get_option('ki_db_version') != KI_DB_VERSION) {
            $this->aktualizuj_baze();
        }
    }

    public function init() {
        // Rejestrujemy style i skrypty
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        
        // Menu admina
        add_action('admin_menu', array($this, 'admin_menu'));
        
        // Shortcode
        add_shortcode('intencje_mszalne', array($this, 'shortcode'));
        
        // AJAX dla intencji
        add_action('wp_ajax_dodaj_intencje_do_mszy', array($this, 'dodaj_intencje_do_mszy'));
        add_action('wp_ajax_usun_intencje', array($this, 'usun_intencje'));
        add_action('wp_ajax_pobierz_intencje_mszy', array($this, 'pobierz_intencje_mszy_ajax'));
        add_action('wp_ajax_zmien_kolejnosc_intencji', array($this, 'zmien_kolejnosc_intencji'));
        add_action('wp_ajax_edytuj_intencje', array($this, 'edytuj_intencje'));

        // AJAX dla harmonogramów
        add_action('wp_ajax_utworz_harmonogram', array($this, 'utworz_harmonogram'));
        add_action('wp_ajax_pobierz_harmonogram', array($this, 'pobierz_harmonogram'));
        add_action('wp_ajax_zapisz_harmonogram', array($this, 'zapisz_harmonogram'));
        add_action('wp_ajax_aktywuj_harmonogram', array($this, 'aktywuj_harmonogram'));
        add_action('wp_ajax_utworz_i_aktywuj_harmonogram', array($this, 'utworz_i_aktywuj_harmonogram'));

        // AJAX dla edycji godzin
        add_action('wp_ajax_pobierz_godziny_dnia', array($this, 'pobierz_godziny_dnia'));
        add_action('wp_ajax_usun_godzine_z_dnia', array($this, 'usun_godzine_z_dnia'));
        add_action('wp_ajax_dodaj_godzine_do_dnia', array($this, 'dodaj_godzine_do_dnia'));

        // NOWE AJAX DLA ARCHIWIZACJI
        add_action('wp_ajax_przyworc_rok_ajax', array($this, 'przyworc_rok_ajax'));
        add_action('wp_ajax_usun_harmonogram_ajax', array($this, 'usun_harmonogram_ajax'));
        add_action('wp_ajax_archiwizuj_rok_ajax', array($this, 'archiwizuj_rok_ajax'));

        add_action('wp_ajax_sprawdz_harmonogram_roku_ajax', array($this, 'sprawdz_harmonogram_roku_ajax'));


    }
    
    public function admin_scripts($hook) {
        if ($hook != 'toplevel_page_intencje-mszalne' && $hook != 'intencje-mszalne_page_intencje-harmonogramy') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('ki-admin-js', KI_PLUGIN_URL . 'assets/admin.js', array('jquery'), '3.19', true);
        wp_enqueue_style('ki-admin-css', KI_PLUGIN_URL . 'assets/admin.css', array(), '3.43');
        
        // Dane dla AJAX
        wp_localize_script('ki-admin-js', 'ki_ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ki_nonce')
        ));
    }
    
    public function frontend_scripts() {
        wp_enqueue_style('ki-frontend-css', KI_PLUGIN_URL . 'assets/frontend.css', array(), '3.0');
    }
    
    public function admin_menu() {
        add_menu_page(
            'Intencje mszalne',
            'Intencje mszalne',
            'manage_options',
            'intencje-mszalne',
            array($this, 'admin_page'),
            'dashicons-calendar-alt',
            30
        );

        add_submenu_page(
            'intencje-mszalne',
            'Harmonogramy mszy',
            'Harmonogramy',
            'manage_options',
            'intencje-harmonogramy',
            array($this, 'admin_page_harmonogramy')
        );
    }
    
    public function admin_page() {
        // Bezpieczna archiwizacja - tylko 1-15 stycznia
        $this->sprawdz_automatyczna_archiwizacja();
        
        // DEBUG: Sprawdź czy harmonogramy działają
        if (isset($_GET['debug'])) {
            $this->debug_harmonogramy();
        }
        
        include KI_PLUGIN_PATH . 'templates/admin.php';
    }
    
    /**
     * FUNKCJA DEBUGUJĄCA
     */
    private function debug_harmonogramy() {
        global $wpdb;
        
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        $table_kalendarz = $wpdb->prefix . 'msze_kalendarz';
        
        echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">';
        echo '<h3>DEBUG HARMONOGRAMY</h3>';
        
        // Sprawdź harmonogramy
        $harmonogramy = $wpdb->get_results("SELECT * FROM $table_harmonogramy");
        echo '<p><strong>Harmonogramy w bazie:</strong> ' . count($harmonogramy) . '</p>';
        
        foreach ($harmonogramy as $h) {
            echo "<p>Harmonogram {$h->id}: Rok {$h->rok}, Archiwum: {$h->archiwum}</p>";
            
            // Sprawdź dane opcji
            $dane = get_option('ki_harmonogram_' . $h->id);
            echo "<p>Dane opcji: " . (empty($dane) ? 'BRAK' : 'ISTNIEJE') . "</p>";
            
            // Sprawdź dni w kalendarzu
            $liczba_dni = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_kalendarz WHERE harmonogram_id = %d", $h->id
            ));
            echo "<p>Dni w kalendarzu: $liczba_dni</p>";
        }
        
        echo '</div>';
    }
    
    public function admin_page_harmonogramy() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        global $wpdb;
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        
        // SPRAWDŹ CZY TWORZENIE NOWEGO HARMONOGRAMU (bez zapisywania!)
        $nowy_rok = isset($_GET['nowy_rok']) ? intval($_GET['nowy_rok']) : 0;
        
        if ($nowy_rok > 0) {
            // ✅ ZABEZPIECZENIE: Sprawdź czy harmonogram już istnieje
            $istniejacy_harmonogram = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_harmonogramy WHERE rok = %d", 
                $nowy_rok
            ));
            
            if ($istniejacy_harmonogram) {
                // ❌ Harmonogram już istnieje - pokaż komunikat błędu
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>🚫 Błąd:</strong> Harmonogram na rok <strong>' . $nowy_rok . '</strong> już istnieje!</p>';
                echo '<p>Nie można utworzyć duplikatu harmonogramu. Wybierz inny rok lub edytuj istniejący harmonogram.</p>';
                echo '</div>';
                
                // Pokaż listę harmonogramów zamiast formularza
                $this->pokaz_liste_harmonogramow();
                return;
            }
            
            // POKAŻ FORMULARZ DLA NOWEGO ROKU (bez zapisywania w bazie!)
            include KI_PLUGIN_PATH . 'templates/nowy-harmonogram.php';
            return;
        }
        
        // SPRAWDZAMY CZY EDYCJA ISTNIEJĄCEGO HARMONOGRAMU
        $edytuj_id = isset($_GET['edytuj']) ? intval($_GET['edytuj']) : 0;
        
        if ($edytuj_id > 0) {
            // POKAŻ STRONĘ EDYCJI HARMONOGRAMU
            $harmonogram = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_harmonogramy WHERE id = %d", 
                $edytuj_id
            ));
            
            if (!$harmonogram) {
                echo '<div class="error"><p>Harmonogram nie istnieje</p></div>';
                return;
            }
            
            include KI_PLUGIN_PATH . 'templates/edytuj-harmonogram.php';
            return;
        }
        
        // POKAŻ LISTĘ HARMONOGRAMÓW
        $harmonogramy = $wpdb->get_results("
            SELECT * FROM $table_harmonogramy 
            ORDER BY rok DESC
        ");
        
        include KI_PLUGIN_PATH . 'templates/harmonogramy.php';
    }
    
    /**
     * POKAŻ LISTĘ HARMONOGRAMÓW (wydzielona funkcja)
     */
    private function pokaz_liste_harmonogramow() {
        global $wpdb;
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        
        $harmonogramy = $wpdb->get_results("
            SELECT * FROM $table_harmonogramy 
            ORDER BY rok DESC
        ");
        
        include KI_PLUGIN_PATH . 'templates/harmonogramy.php';
    }

    public function shortcode($atts) {
        ob_start();
        include KI_PLUGIN_PATH . 'templates/frontend.php';
        return ob_get_clean();
    }

    /**
     * NOWE FUNKCJE ARCHIWIZACJI
     */
    public function sprawdz_automatyczna_archiwizacja() {
        $dzisiaj = date('Y-m-d');
        $miesiac = date('m');
        $dzien = date('d');
        
        // Sprawdzaj TYLKO między 1 a 15 stycznia
        if ($miesiac != '01' || $dzien < 1 || $dzien > 15) {
            return;
        }
        
        $rok_do_archiwizacji = date('Y') - 1; // zeszły rok
        
        // ZABEZPIECZENIE 1: Sprawdź czy harmonogram dla zeszłego roku w ogóle istnieje
        if (!$this->czy_istnieje_harmonogram($rok_do_archiwizacji)) {
            error_log("🚫 Brak harmonogramu do archiwizacji dla roku: $rok_do_archiwizacji");
            return;
        }
        
        // ZABEZPIECZENIE 2: Sprawdz czy już zarchiwizowany
        if (!$this->czy_rok_zarchiwizowany($rok_do_archiwizacji)) {
            $this->archiwizuj_rok($rok_do_archiwizacji);
            error_log("✅ Automatyczna archiwizacja roku: $rok_do_archiwizacji (1-15 stycznia)");
        } else {
            error_log("ℹ️ Rok $rok_do_archiwizacji już był zarchiwizowany");
        }
    }

    public function czy_istnieje_harmonogram($rok) {
        global $wpdb;
        $table = $wpdb->prefix . 'msze_harmonogramy';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE rok = %d", $rok
        ));
        
        return $count > 0;
    }

    public function czy_rok_zarchiwizowany($rok) {
        global $wpdb;
        $table = $wpdb->prefix . 'msze_harmonogramy';
        
        $archiwum = $wpdb->get_var($wpdb->prepare(
            "SELECT archiwum FROM $table WHERE rok = %d", $rok
        ));
        
        // Jeśli nie znaleziono rekordu, zwróć false
        if ($archiwum === null) {
            return false;
        }
        
        return $archiwum == 1;
    }

    public function archiwizuj_rok($rok) {
        global $wpdb;
        $table = $wpdb->prefix . 'msze_harmonogramy';
        
        error_log("=== ARCHIWIZUJ ROK ===");
        error_log("Rok do archiwizacji: " . $rok);
        
        $result = $wpdb->update(
            $table,
            array('archiwum' => 1),
            array('rok' => $rok),
            array('%d'),
            array('%d')
        );
        
        error_log("Wynik update: " . ($result !== false ? 'SUKCES' : 'BŁĄD'));
        error_log("Błąd bazy: " . $wpdb->last_error);
        error_log("====================");
        
        return $result !== false;
    }

    /**
     * ARCHIWIZUJ ROK
     */
    public function archiwizuj_rok_ajax() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $rok = intval($_POST['rok']);
        error_log("=== ARCHIWIZUJ ROK AJAX ===");
        error_log("Rok z AJAX: " . $rok);
        
        $result = $this->archiwizuj_rok($rok);
        
        if ($result) {
            error_log("ARCHIWIZACJA SUKCES");
            wp_send_json_success('Rok zarchiwizowany');
        } else {
            error_log("ARCHIWIZACJA BŁĄD");
            wp_send_json_error('Błąd archiwizacji roku');
        }
    }

    public function przyworc_rok($rok) {
        global $wpdb;
        $table = $wpdb->prefix . 'msze_harmonogramy';
        
        $result = $wpdb->update(
            $table,
            array('archiwum' => 0),
            array('rok' => $rok),
            array('%d'),
            array('%d')
        );

        return $result !== false;
    }

    public function usun_harmonogram($rok) {
        global $wpdb;
        
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        $table_kalendarz = $wpdb->prefix . 'msze_kalendarz';
        $table_msze = $wpdb->prefix . 'msze';
        $table_intencje = $wpdb->prefix . 'intencje_mszalne';
        
        // 1. Znajdź harmonogram_id
        $harmonogram = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_harmonogramy WHERE rok = %d", $rok
        ));
        
        if (!$harmonogram) {
            return false; // Nie ma co usuwać
        }
        
        $harmonogram_id = $harmonogram->id;
        
        // 2. Usuń intencje poprzez msze
        $msze_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM $table_msze WHERE YEAR(data) = %d", $rok
        ));
        
        if (!empty($msze_ids)) {
            $msze_ids_placeholders = implode(',', array_fill(0, count($msze_ids), '%d'));
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_intencje WHERE msza_id IN ($msze_ids_placeholders)",
                $msze_ids
            ));
        }
        
        // 3. Usuń msze
        $wpdb->delete($table_msze, array('YEAR(data)' => $rok), array('%d'));
        
        // 4. Usuń dni z kalendarza
        $wpdb->delete($table_kalendarz, array('harmonogram_id' => $harmonogram_id), array('%d'));
        
        // 5. Usuń harmonogram
        $wpdb->delete($table_harmonogramy, array('id' => $harmonogram_id), array('%d'));
        
        return true;
    }

    public function pobierz_status_roku($rok) {
        if (!$this->czy_istnieje_harmonogram($rok)) {
            return 'brak';
        }
        
        if ($this->czy_rok_zarchiwizowany($rok)) {
            return 'zarchiwizowany';
        }
        
        return 'aktywny';
    }

    public function czy_rok_edytowalny($rok) {
        $status = $this->pobierz_status_roku($rok);
        return $status === 'aktywny';
    }

    /**
     * NOWE FUNKCJE AJAX DLA ARCHIWIZACJI
     */
    public function przyworc_rok_ajax() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $rok = intval($_POST['rok']);
        
        if ($this->przyworc_rok($rok)) {
            wp_send_json_success('Rok przywrócony do edycji');
        } else {
            wp_send_json_error('Błąd przywracania roku');
        }
    }

    public function usun_harmonogram_ajax() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $rok = intval($_POST['rok']);
        
        if ($this->usun_harmonogram($rok)) {
            wp_send_json_success('Harmonogram usunięty');
        } else {
            wp_send_json_error('Błąd usuwania harmonogramu');
        }
    }

    /**
     * POZOSTAŁE FUNKCJE SYSTEMU INTENCJI (BEZ ZMIAN)
     */
    public function get_godziny_dla_dnia($data) {
        global $wpdb;
        
        $table_kalendarz = $wpdb->prefix . 'msze_kalendarz';
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        
        // Pobierz rok z daty
        $rok_daty = date('Y', strtotime($data));
        
        // Szukaj w kalendarzu dla harmonogramu tego roku
        $dzien = $wpdb->get_row($wpdb->prepare(
            "SELECT k.godziny_msz 
             FROM $table_kalendarz k 
             INNER JOIN $table_harmonogramy h ON k.harmonogram_id = h.id 
             WHERE k.data = %s AND h.rok = %d",
            $data, $rok_daty
        ));
        
        if ($dzien && !empty($dzien->godziny_msz)) {
            $godziny = array_map('trim', explode(',', $dzien->godziny_msz));
            return array_filter($godziny);
        }
        
        return array();
    }

    public function pobierz_intencje_dla_mszy($data, $godzina) {
        global $wpdb;
        $table_msze = $wpdb->prefix . 'msze';
        $table_intencje = $wpdb->prefix . 'intencje_mszalne';
        
        $msza = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_msze WHERE data = %s AND godzina = %s",
            $data, $godzina
        ));
        
        if (!$msza) {
            return array();
        }
        
        $intencje = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_intencje 
            WHERE msza_id = %d AND status = 'aktywna'
            ORDER BY kolejnosc ASC",
            $msza->id
        ));
        
        return $intencje;
    }

    public function pobierz_intencje($data, $godzina) {
        $intencje = $this->pobierz_intencje_dla_mszy($data, $godzina);
        
        if (empty($intencje)) {
            return 'Wolna intencja';
        }
        
        // JEDNA INTENCJA - BEZ NUMERU
        if (count($intencje) === 1) {
            $intencja = $intencje[0];
            $tekst = $intencja->intencja_text;
            
            if ($intencja->za_parafian == 1) {
                $tekst .= ' 🏛️';
            } else {
                if (is_admin() && $intencja->ofiara > 0) {
                    $tekst .= ' (' . number_format($intencja->ofiara, 2) . ' zł)';
                }
            }
            
            return $tekst;
        }
        
        // W FUNKCJI pobierz_intencje() ZAMIAST HTML UŻYJ:
        // WIELE INTENCJI - NUMEROWANE Z <br>
        $tekst_intencji = array();
        $numer = 1;

        foreach ($intencje as $intencja) {
            $tekst = $numer . ') ' . $intencja->intencja_text;
            
            if ($intencja->za_parafian == 1) {
                $tekst .= ' 🏛️';
            } else {
                if (is_admin() && $intencja->ofiara > 0) {
                    $tekst .= ' (' . number_format($intencja->ofiara, 2) . ' zł)';
                }
            }
            
            $tekst_intencji[] = $tekst;
            $numer++;
        }

        return implode("<br><br>", $tekst_intencji);
    }

    public function przetlumacz_dzien($dzien_en) {
        $tlumaczenia = array(
            'Mon' => 'Poniedziałek',
            'Tue' => 'Wtorek', 
            'Wed' => 'Środa',
            'Thu' => 'Czwartek',
            'Fri' => 'Piątek',
            'Sat' => 'Sobota',
            'Sun' => 'Niedziela'
        );
        return $tlumaczenia[$dzien_en] ?? $dzien_en;
    }

    // POZOSTAŁE FUNKCJE AJAX I SYSTEMU...
    public function utworz_harmonogram() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        global $wpdb;
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        
        $rok = intval($_POST['rok']);
        
        // Sprawdzamy czy rok już istnieje
        $istniejacy = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_harmonogramy WHERE rok = %d", 
            $rok
        ));
        
        if ($istniejacy) {
            wp_send_json_error('Harmonogram na rok ' . $rok . ' już istnieje');
        }
        
        // Tworzymy nowy harmonogram
        $result = $wpdb->insert(
            $table_harmonogramy,
            array(
                'rok' => $rok,
                'nazwa' => 'Harmonogram ' . $rok,
                'archiwum' => 0
            ),
            array('%d', '%s', '%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Błąd zapisu do bazy');
        }
        
        $nowy_id = $wpdb->insert_id;
        
        wp_send_json_success(array(
            'id' => $nowy_id,
            'message' => 'Utworzono harmonogram na rok ' . $rok
        ));
    }

    /**
     * UTWÓRZ I AKTYWUJ HARMONOGRAM - JEDNA OPERACJA (POPRAWIONA Z WALIDACJĄ)
     */
    public function utworz_i_aktywuj_harmonogram() {
        // ZAPISZ WSZYSTKIE DANE WEJŚCIOWE DO LOGÓW
        error_log("=== UTWORZ_I_AKTYWUJ_HARMONOGRAM ===");
        
        // SPRAWDŹ NONCE
        if (!check_ajax_referer('ki_nonce', 'nonce', false)) {
            error_log("BŁĄD: Nieprawidłowy nonce");
            wp_send_json_error('Błąd bezpieczeństwa - nieprawidłowy nonce');
        }
        
        if (!current_user_can('manage_options')) {
            error_log("BŁĄD: Brak uprawnień");
            wp_send_json_error('Brak uprawnień');
        }
        
        // SPRAWDŹ WYMAGANE PARAMETRY
        if (!isset($_POST['rok']) || empty($_POST['rok'])) {
            error_log("BŁĄD: Brak parametru 'rok'");
            wp_send_json_error('Brak wymaganego parametru: rok');
        }
        
        // DODATKOWE ZABEZPIECZENIE: Sprawdź czy harmonogram już istnieje
        global $wpdb;
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        
        $istniejacy = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_harmonogramy WHERE rok = %d", 
            $rok
        ));
        
        if ($istniejacy) {
            error_log("BŁĄD: Próba utworzenia duplikatu harmonogramu dla roku: " . $rok);
            wp_send_json_error('Harmonogram na rok ' . $rok . ' już istnieje! Zabezpieczenie przed duplikacją.');
        }

        if (!isset($_POST['dane']) || empty($_POST['dane'])) {
            error_log("BŁĄD: Brak parametru 'dane'");
            wp_send_json_error('Brak wymaganego parametru: dane');
        }
        
        $rok = intval($_POST['rok']);
        
        // SPRÓBUJ PARSOWAĆ DANE JSON
        $dane_json = wp_unslash($_POST['dane']);
        $dane = json_decode($dane_json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("BŁĄD JSON: " . json_last_error_msg());
            wp_send_json_error('Błąd parsowania danych JSON: ' . json_last_error_msg());
        }
        
        // WALIDUJ DANE - SPRAWDŹ CZY SĄ PODSTAWOWE GODZINY
        if (empty($dane['podstawowe']['niedziele']) || 
            empty($dane['podstawowe']['powszednie']) || 
            empty($dane['podstawowe']['soboty'])) {
            error_log("BŁĄD: Brak podstawowych godzin mszy");
            wp_send_json_error('Proszę wypełnić przynajmniej godziny podstawowe (niedziele, dni powszednie, soboty)');
        }
        
        error_log("Rok: " . $rok);
        error_log("Dane harmonogramu otrzymane");
        
        global $wpdb;
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        
        // 1. Sprawdź czy harmonogram już istnieje
        $istniejacy = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_harmonogramy WHERE rok = %d", 
            $rok
        ));
        
        if ($istniejacy) {
            error_log("BŁĄD: Harmonogram na rok $rok już istnieje");
            wp_send_json_error('Harmonogram na rok ' . $rok . ' już istnieje');
        }
        
        // 2. Utwórz nowy harmonogram
        error_log("Tworzenie harmonogramu dla roku: $rok");
        $result = $wpdb->insert(
            $table_harmonogramy,
            array(
                'rok' => $rok,
                'nazwa' => 'Harmonogram ' . $rok,
                'archiwum' => 0,
                'data_utworzenia' => current_time('mysql')
            ),
            array('%d', '%s', '%d', '%s')
        );
        
        if ($result === false) {
            error_log("BŁĄD: Nie udało się utworzyć harmonogramu w bazie: " . $wpdb->last_error);
            wp_send_json_error('Błąd tworzenia harmonogramu w bazie: ' . $wpdb->last_error);
        }
        
        $harmonogram_id = $wpdb->insert_id;
        error_log("Utworzono harmonogram ID: $harmonogram_id");
        
        // 3. Zapisz dane harmonogramu
        $zapisano = update_option('ki_harmonogram_' . $harmonogram_id, $dane);

        // DEBUG: Sprawdź co zostało zapisane
        error_log("ZAPISANE DANE HARMONOGRAMU ID: " . $harmonogram_id);
        error_log(print_r($dane, true));
        
        if (!$zapisano) {
            error_log("BŁĄD: Nie udało się zapisać danych harmonogramu");
            // Jeśli zapis danych się nie udał, usuń harmonogram
            $wpdb->delete($table_harmonogramy, array('id' => $harmonogram_id), array('%d'));
            wp_send_json_error('Błąd zapisu danych harmonogramu');
        }
        
        error_log("Dane harmonogramu zapisane pomyślnie");
        
        // 4. Wygeneruj kalendarz
        error_log("Generowanie kalendarza dla harmonogramu ID: $harmonogram_id");
        $kalendarz_utworzony = $this->wygeneruj_kalendarz_roczny($harmonogram_id);
        
        if ($kalendarz_utworzony) {
            error_log("SUKCES: Harmonogram utworzony i kalendarz wygenerowany");
            wp_send_json_success('Harmonogram utworzony i kalendarz wygenerowany');
        } else {
            error_log("BŁĄD: Generowanie kalendarza się nie udało");
            // Jeśli generowanie kalendarza się nie udało, usuń harmonogram
            $wpdb->delete($table_harmonogramy, array('id' => $harmonogram_id), array('%d'));
            delete_option('ki_harmonogram_' . $harmonogram_id);
            wp_send_json_error('Błąd generowania kalendarza - sprawdź error_log dla szczegółów');
        }
    }

    public function pobierz_harmonogram() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $harmonogram_id = intval($_POST['harmonogram_id']);
        
        // Domyślne dane harmonogramu
        $harmonogram_data = array(
            'podstawowe' => array(
                'niedziele' => '07:30,09:00,10:00,11:15,12:30,18:00',
                'powszednie' => '07:30,08:30,18:00,19:00',
                'soboty' => '07:30,08:30,18:00'
            ),
            'okresy' => array(
                'adwent' => array(
                    'nazwa' => 'Adwent',
                    'daty_od' => date('Y') . '-12-01',
                    'daty_do' => date('Y') . '-12-23',
                    'niedziele' => '06:30,09:00,10:00,11:15,12:30,18:00',
                    'powszednie' => '06:30,08:30,18:00,19:00',
                    'soboty' => '06:30,08:30,18:00'
                ),
                'wakacje' => array(
                    'nazwa' => 'Wakacje',
                    'daty_od' => date('Y') . '-07-01',
                    'daty_do' => date('Y') . '-08-31',
                    'niedziele' => '07:30,09:00,10:00,11:15,12:30,18:00',
                    'powszednie' => '08:30,18:00,19:00',
                    'soboty' => '08:30,18:00'
                )
            ),
            'dni_specjalne' => array()
        );
        
        wp_send_json_success($harmonogram_data);
    }

    /**
     * ZAPISZ HARMONOGRAM - POPRAWIONA
     */
    public function zapisz_harmonogram() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $harmonogram_id = intval($_POST['harmonogram_id']);
        $dane = wp_unslash($_POST['dane']); // UŻYWAMY wp_unslash!
        
        // DEBUG: Sprawdzamy co przychodzi
        error_log("Zapisywanie harmonogramu ID: " . $harmonogram_id);
        error_log("Dane: " . print_r($dane, true));
        
        // Zapisz do opcji WordPress
        $result = update_option('ki_harmonogram_' . $harmonogram_id, $dane);
        
        error_log("Wynik zapisu: " . ($result ? 'SUKCES' : 'BŁĄD'));
        
        if ($result) {
            wp_send_json_success('Harmonogram zapisany');
        } else {
            wp_send_json_error('Błąd zapisu do bazy');
        }
    }

    /**
     * AKTYWUJ HARMONOGRAM I GENERUJ KALENDARZ - POPRAWIONA
     */
    public function aktywuj_harmonogram() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $harmonogram_id = intval($_POST['harmonogram_id']);
        
        global $wpdb;
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        
        // 1. Zaktualizuj datę aktywacji
        $wpdb->update(
            $table_harmonogramy,
            array('data_aktywacji' => current_time('mysql')),
            array('id' => $harmonogram_id),
            array('%s'),
            array('%d')
        );
        
        // 2. Wygeneruj kalendarz
        $result = $this->wygeneruj_kalendarz_roczny($harmonogram_id);
        
        if ($result) {
            wp_send_json_success('Harmonogram aktywowany i kalendarz wygenerowany');
        } else {
            wp_send_json_error('Błąd generowania kalendarza');
        }
    }

    /**
     * WYGENERUJ KALENDARZ ROCZNY - POPRAWIONA
     */
    private function wygeneruj_kalendarz_roczny($harmonogram_id) {
        global $wpdb;
        
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        $table_kalendarz = $wpdb->prefix . 'msze_kalendarz';
        
        // Pobierz rok harmonogramu
        $harmonogram = $wpdb->get_row($wpdb->prepare(
            "SELECT rok FROM $table_harmonogramy WHERE id = %d",
            $harmonogram_id
        ));
        
        if (!$harmonogram) {
            error_log("BŁĄD: Nie znaleziono harmonogramu ID: " . $harmonogram_id);
            return false;
        }
        
        $rok = $harmonogram->rok;
        
        // Pobierz dane harmonogramu
        $harmonogram_data = get_option('ki_harmonogram_' . $harmonogram_id, array());
        
        error_log("Generowanie kalendarza dla roku: " . $rok);
        error_log("Dane harmonogramu: " . print_r($harmonogram_data, true));
        
        if (empty($harmonogram_data)) {
            error_log("BŁĄD: Brak danych harmonogramu dla ID: " . $harmonogram_id);
            return false;
        }
        
        // Sprawdź czy rok przestępny
        $czy_przestepny = date('L', strtotime("$rok-01-01"));
        $liczba_dni = $czy_przestepny ? 366 : 365;
        
        error_log("Generowanie $liczba_dni dni dla roku $rok");
        
        $data_start = "$rok-01-01";
        $licznik_udanych = 0;
        $licznik_bledow = 0;
        
        for ($i = 0; $i < $liczba_dni; $i++) {
            $data = date('Y-m-d', strtotime("$data_start +$i days"));
            
            $godziny = $this->oblicz_godziny_dla_dnia_z_harmonogramu($data, $harmonogram_data);
            
            // DEBUG: Sprawdzamy pierwsze 10 dni
            if ($i < 10) {
                error_log("Dzień $i ($data) -> Godziny: " . ($godziny ?: 'BRAK'));
            }
            
            $result = $wpdb->replace(
                $table_kalendarz,
                array(
                    'data' => $data,
                    'godziny_msz' => $godziny,
                    'harmonogram_id' => $harmonogram_id
                ),
                array('%s', '%s', '%d')
            );
            
            if ($result === false) {
                error_log("BŁĄD zapisu dnia: $data - " . $wpdb->last_error);
                $licznik_bledow++;
            } else {
                $licznik_udanych++;
            }
        }
        
        error_log("Wygenerowano kalendarz: $licznik_udanych sukcesów, $licznik_bledow błędów");
        
        return $licznik_udanych > 0;
    }
  
    /**
     * OBLICZ GODZINY DLA DNIA - POPRAWIONA WERSJA (IGNORUJE PUSTE OKRESY)
     */
    /**
     * OBLICZ GODZINY DLA DNIA - POPRAWIONA WERSJA
     */
    private function oblicz_godziny_dla_dnia_z_harmonogramu($data, $harmonogram_data) {
        error_log("Obliczanie godzin dla: $data");
        error_log("Dane harmonogramu: " . print_r($harmonogram_data, true));
        
        // 1. Sprawdź czy to dzień specjalny
        if (isset($harmonogram_data['dni_specjalne']) && is_array($harmonogram_data['dni_specjalne'])) {
            $dzien_miesiac = date('m-d', strtotime($data));
            $pelna_data = date('Y-m-d', strtotime($data));
            
            error_log("Sprawdzam dni specjalne dla: $dzien_miesiac, $pelna_data");
            
            // Sprawdź konkretne święta po dacie
            foreach ($harmonogram_data['dni_specjalne'] as $klucz => $godziny) {
                error_log("Sprawdzam święto: $klucz => $godziny");
                
                // Sprawdź czy klucz zawiera datę
                if (strpos($klucz, $pelna_data) !== false || 
                    strpos($klucz, $dzien_miesiac) !== false ||
                    $klucz === 'swieto_24_grudnia' && $dzien_miesiac === '12-24' ||
                    $klucz === 'swieto_25_grudnia' && $dzien_miesiac === '12-25' ||
                    $klucz === 'swieto_26_grudnia' && $dzien_miesiac === '12-26' ||
                    $klucz === 'swieto_1_stycznia' && $dzien_miesiac === '01-01' ||
                    $klucz === 'swieto_6_stycznia' && $dzien_miesiac === '01-06' ||
                    $klucz === 'swieto_3_maja' && $dzien_miesiac === '05-03' ||
                    $klucz === 'swieto_15_sierpnia' && $dzien_miesiac === '08-15') {
                    
                    if (!empty($godziny)) {
                        error_log("Znaleziono święto: $klucz -> $godziny");
                        return $godziny;
                    }
                }
            }
            
            // Specjalne sprawdzenie dla custom dat
            foreach ($harmonogram_data['dni_specjalne'] as $klucz => $godziny) {
                if (strpos($klucz, 'custom_') === 0) {
                    $custom_data = str_replace('custom_', '', $klucz);
                    if ($custom_data === $pelna_data && !empty($godziny)) {
                        error_log("Znaleziono custom dzień: $custom_data -> $godziny");
                        return $godziny;
                    }
                }
            }
        }
        
        // 2. Sprawdź okresy specjalne
        if (isset($harmonogram_data['okresy']) && is_array($harmonogram_data['okresy'])) {
            foreach ($harmonogram_data['okresy'] as $okres_nazwa => $okres) {
                error_log("Sprawdzam okres: $okres_nazwa");
                
                // Sprawdź czy okres ma poprawne dane
                if (!empty($okres['daty_od']) && !empty($okres['daty_do']) && 
                    $okres['daty_od'] != '0000-00-00' && $okres['daty_do'] != '0000-00-00') {
                    
                    // Sprawdź czy data mieści się w okresie
                    if ($data >= $okres['daty_od'] && $data <= $okres['daty_do']) {
                        error_log("Data $data należy do okresu $okres_nazwa ({$okres['daty_od']} - {$okres['daty_do']})");
                        
                        // Określ typ dnia w okresie
                        $dzien_tygodnia = date('N', strtotime($data));
                        
                        if ($dzien_tygodnia == 7) { // Niedziela
                            $godziny = $okres['niedziele'] ?? '';
                        } elseif ($dzien_tygodnia == 6) { // Sobota
                            $godziny = $okres['soboty'] ?? '';
                        } else { // Pon-Pt
                            $godziny = $okres['powszednie'] ?? '';
                        }
                        
                        if (!empty($godziny)) {
                            error_log("Znaleziono okres $okres_nazwa: $godziny");
                            return $godziny;
                        } else {
                            error_log("Brak godzin dla okresu $okres_nazwa, dzień: $dzien_tygodnia");
                        }
                    }
                } else {
                    error_log("Pominięto okres $okres_nazwa - brak dat lub daty nieprawidłowe");
                }
            }
        }
        
        // 3. Domyślne godziny z podstawowych
        $dzien_tygodnia = date('N', strtotime($data));
        if ($dzien_tygodnia == 7) { // Niedziela
            $godziny = $harmonogram_data['podstawowe']['niedziele'] ?? '';
        } elseif ($dzien_tygodnia == 6) { // Sobota
            $godziny = $harmonogram_data['podstawowe']['soboty'] ?? '';
        } else { // Pon-Pt
            $godziny = $harmonogram_data['podstawowe']['powszednie'] ?? '';
        }
        
        error_log("Używam godzin podstawowych: $godziny (dzień: $dzien_tygodnia)");
        return $godziny;
    }
    /**
     * ZAPISZ I AKTYWUJ HARMONOGRAM - JEDNA OPERACJA
     */
    public function zapisz_i_aktywuj_harmonogram() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $harmonogram_id = intval($_POST['harmonogram_id']);
        $dane = wp_unslash($_POST['dane']);
        
        // 1. Zapisz dane harmonogramu
        $zapisano = update_option('ki_harmonogram_' . $harmonogram_id, $dane);
        
        if (!$zapisano) {
            wp_send_json_error('Błąd zapisu harmonogramu');
        }
        
        // 2. Wygeneruj kalendarz
        $kalendarz_utworzony = $this->wygeneruj_kalendarz_roczny($harmonogram_id);
        
        if ($kalendarz_utworzony) {
            wp_send_json_success('Harmonogram utworzony i kalendarz wygenerowany');
        } else {
            wp_send_json_error('Błąd generowania kalendarza');
        }
    }

    public function dodaj_intencje_do_mszy() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        // SPRAWDŹ CZY ROK JEST EDYTOWALNY
        $data = sanitize_text_field($_POST['data']);
        // ✅ ZABEZPIECZENIE: Sprawdź czy data jest edytowalna
        $sprawdz = $this->sprawdz_czy_data_edytowalna($data);
        if (!$sprawdz['edytowalne']) {
            wp_send_json_error($sprawdz['powod']);
        }

        $rok = date('Y', strtotime($data));
        if (!$this->czy_rok_edytowalny($rok)) {
            wp_send_json_error('Rok zarchiwizowany - edycja zablokowana');
        }
        
        global $wpdb;
        $table_msze = $wpdb->prefix . 'msze';
        $table_intencje = $wpdb->prefix . 'intencje_mszalne';
        
        $data = sanitize_text_field($_POST['data']);
        $godzina = sanitize_text_field($_POST['godzina']);
        $intencja = sanitize_textarea_field($_POST['intencja']);
        
        $za_parafian = 0;
        if (isset($_POST['za_parafian']) && $_POST['za_parafian'] == '1') {
            $za_parafian = 1;
        }
        
        $ofiara = 0.00;
        if ($za_parafian) {
            $ofiara = 0.00;
        } else {
            $ofiara = isset($_POST['ofiara']) ? floatval($_POST['ofiara']) : 0.00;
        }
        
        // Znajdź lub utwórz msze
        $msza = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_msze WHERE data = %s AND godzina = %s",
            $data, $godzina
        ));
        
        if (!$msza) {
            $wpdb->insert(
                $table_msze,
                array(
                    'data' => $data,
                    'godzina' => $godzina,
                    'max_intencji' => 5,
                    'typ_mszy' => 'standardowa'
                ),
                array('%s', '%s', '%d', '%s')
            );
            $msza_id = $wpdb->insert_id;
        } else {
            $msza_id = $msza->id;
        }
        
        // Sprawdź ile jest już intencji
        $liczba_intencji = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_intencje 
            WHERE msza_id = %d AND status = 'aktywna'",
            $msza_id
        ));
        
        $max_intencji = $msza->max_intencji ?? 5;
        
        if ($liczba_intencji >= $max_intencji) {
            wp_send_json_error("Osiągnięto maksymalną liczbę intencji ($max_intencji) dla tej mszy");
        }
        
        // Dodaj nową intencję
        $result = $wpdb->insert(
            $table_intencje,
            array(
                'msza_id' => $msza_id,
                'intencja_text' => $intencja,
                'za_parafian' => $za_parafian,
                'ofiara' => $ofiara,
                'data_przyjecia' => current_time('mysql'),
                'kolejnosc' => $liczba_intencji + 1
            ),
            array('%d', '%s', '%d', '%f', '%s', '%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Błąd zapisu do bazy');
        }
        
        $intencja_id = $wpdb->insert_id;
        
        wp_send_json_success(array(
            'message' => 'Dodano intencję!',
            'intencja_id' => $intencja_id,
            'liczba_intencji' => $liczba_intencji + 1
        ));
    }

    public function pobierz_intencje_mszy_ajax() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $data = sanitize_text_field($_POST['data']);
        $godzina = sanitize_text_field($_POST['godzina']);
        
        $intencje = $this->pobierz_intencje_dla_mszy($data, $godzina);
        
        wp_send_json_success($intencje);
    }

    public function usun_intencje() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        // SPRAWDŹ CZY ROK JEST EDYTOWALNY
        $intencja_id = intval($_POST['intencja_id']);
        global $wpdb;
        $table_intencje = $wpdb->prefix . 'intencje_mszalne';
        $table_msze = $wpdb->prefix . 'msze';
        
        // Pobierz datę intencji
        $intencja_data = $wpdb->get_row($wpdb->prepare(
            "SELECT m.data FROM $table_intencje i 
             INNER JOIN $table_msze m ON i.msza_id = m.id 
             WHERE i.id = %d",
            $intencja_id
        ));
        
        if ($intencja_data) {
            $rok = date('Y', strtotime($intencja_data->data));
            if (!$this->czy_rok_edytowalny($rok)) {
                wp_send_json_error('Rok zarchiwizowany - edycja zablokowana');
            }
        }
        
        $wpdb->update(
            $table_intencje,
            array('status' => 'usunieta'),
            array('id' => $intencja_id),
            array('%s'),
            array('%d')
        );
        
        wp_send_json_success('Intencja usunięta');
    }

    public function zmien_kolejnosc_intencji() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        // SPRAWDŹ CZY ROK JEST EDYTOWALNY
        $intencja_id = intval($_POST['intencja_id']);
        global $wpdb;
        $table_intencje = $wpdb->prefix . 'intencje_mszalne';
        $table_msze = $wpdb->prefix . 'msze';
        
        // Pobierz datę intencji
        $intencja_data = $wpdb->get_row($wpdb->prepare(
            "SELECT m.data FROM $table_intencje i 
             INNER JOIN $table_msze m ON i.msza_id = m.id 
             WHERE i.id = %d",
            $intencja_id
        ));
        
        if ($intencja_data) {
            $rok = date('Y', strtotime($intencja_data->data));
            if (!$this->czy_rok_edytowalny($rok)) {
                wp_send_json_error('Rok zarchiwizowany - edycja zablokowana');
            }
        }
        
        $kierunek = sanitize_text_field($_POST['kierunek']);
        
        // Pobierz aktualną intencję
        $intencja = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_intencje WHERE id = %d AND status = 'aktywna'",
            $intencja_id
        ));
        
        if (!$intencja) {
            wp_send_json_error('Intencja nie istnieje');
        }
        
        // Pobierz WSZYSTKIE intencje dla tej mszy
        $wszystkie_intencje = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_intencje 
            WHERE msza_id = %d AND status = 'aktywna'
            ORDER BY kolejnosc ASC",
            $intencja->msza_id
        ));
        
        // Znajdź aktualny index
        $aktualny_index = -1;
        foreach ($wszystkie_intencje as $index => $item) {
            if ($item->id == $intencja_id) {
                $aktualny_index = $index;
                break;
            }
        }
        
        if ($aktualny_index === -1) {
            wp_send_json_error('Błąd wewnętrzny');
        }
        
        // Sprawdź czy można przesunąć
        if ($kierunek === 'up' && $aktualny_index > 0) {
            $sasiedni_index = $aktualny_index - 1;
        } elseif ($kierunek === 'down' && $aktualny_index < count($wszystkie_intencje) - 1) {
            $sasiedni_index = $aktualny_index + 1;
        } else {
            wp_send_json_error('Nie można przesunąć');
        }
        
        $sasiednia_intencja = $wszystkie_intencje[$sasiedni_index];
        
        // Zamień kolejności
        $result1 = $wpdb->update(
            $table_intencje,
            array('kolejnosc' => $sasiednia_intencja->kolejnosc),
            array('id' => $intencja_id),
            array('%d'),
            array('%d')
        );
        
        $result2 = $wpdb->update(
            $table_intencje,
            array('kolejnosc' => $intencja->kolejnosc),
            array('id' => $sasiednia_intencja->id),
            array('%d'),
            array('%d')
        );
        
        if ($result1 !== false && $result2 !== false) {
            wp_send_json_success('Kolejność zmieniona');
        } else {
            wp_send_json_error('Błąd bazy danych');
        }
    }

    public function edytuj_intencje() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        // SPRAWDŹ CZY ROK JEST EDYTOWALNY
        $intencja_id = intval($_POST['intencja_id']);
        global $wpdb;
        $table_intencje = $wpdb->prefix . 'intencje_mszalne';
        $table_msze = $wpdb->prefix . 'msze';
        
        // Pobierz datę intencji
        $intencja_data = $wpdb->get_row($wpdb->prepare(
            "SELECT m.data FROM $table_intencje i 
             INNER JOIN $table_msze m ON i.msza_id = m.id 
             WHERE i.id = %d",
            $intencja_id
        ));
        
        if ($intencja_data) {
            $rok = date('Y', strtotime($intencja_data->data));
            if (!$this->czy_rok_edytowalny($rok)) {
                wp_send_json_error('Rok zarchiwizowany - edycja zablokowana');
            }
        }
        
        $intencja_text = sanitize_textarea_field($_POST['intencja']);
        
        $za_parafian = 0;
        if (isset($_POST['za_parafian']) && $_POST['za_parafian'] == '1') {
            $za_parafian = 1;
        }
        
        $ofiara = 0.00;
        if ($za_parafian) {
            $ofiara = 0.00;
            $intencja_text = "za parafian";
        } else {
            $ofiara = isset($_POST['ofiara']) ? floatval($_POST['ofiara']) : 0.00;
        }
        
        $result = $wpdb->update(
            $table_intencje,
            array(
                'intencja_text' => $intencja_text,
                'za_parafian' => $za_parafian,
                'ofiara' => $ofiara,
                'data_modyfikacji' => current_time('mysql')
            ),
            array('id' => $intencja_id),
            array('%s', '%d', '%f', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('Intencja zaktualizowana');
        } else {
            wp_send_json_error('Błąd aktualizacji intencji');
        }
    }

    public function pobierz_godziny_dnia() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $data = sanitize_text_field($_POST['data']);
        
        global $wpdb;
        $table_kalendarz = $wpdb->prefix . 'msze_kalendarz';
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        
        // Pobierz godziny z KALENDARZA
        $dzien = $wpdb->get_row($wpdb->prepare(
            "SELECT k.godziny_msz 
            FROM $table_kalendarz k 
            INNER JOIN $table_harmonogramy h ON k.harmonogram_id = h.id 
            WHERE k.data = %s AND h.rok = YEAR(%s)",
            $data, $data
        ));
        
        $godziny_dnia = array();
        if ($dzien && !empty($dzien->godziny_msz)) {
            $godziny_dnia = array_map('trim', explode(',', $dzien->godziny_msz));
        }
        
        // Dla każdej godziny pobierz intencje
        $godziny_z_intencjami = array();
        foreach ($godziny_dnia as $godzina) {
            $intencje = $this->pobierz_intencje_dla_mszy($data, $godzina);
            $godziny_z_intencjami[] = array(
                'godzina' => $godzina,
                'intencje' => $intencje,
                'liczba_intencji' => count($intencje)
            );
        }
        
        wp_send_json_success(array(
            'data' => $data,
            'godziny' => $godziny_z_intencjami
        ));
    }

    public function usun_godzine_z_dnia() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        // SPRAWDŹ CZY ROK JEST EDYTOWALNY
        $data = sanitize_text_field($_POST['data']);
        // ZABEZPIECZENIE: Sprawdź czy data jest edytowalna
        $sprawdz = $this->sprawdz_czy_data_edytowalna($data);
        if (!$sprawdz['edytowalne']) {
            wp_send_json_error($sprawdz['powod']);
        }

        $rok = date('Y', strtotime($data));
        if (!$this->czy_rok_edytowalny($rok)) {
            wp_send_json_error('Rok zarchiwizowany - edycja zablokowana');
        }
        
        $godzina = sanitize_text_field($_POST['godzina']);
        
        global $wpdb;
        $table_kalendarz = $wpdb->prefix . 'msze_kalendarz';
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        $table_msze = $wpdb->prefix . 'msze';
        
        // 1. Sprawdź czy są intencje
        $intencje = $this->pobierz_intencje_dla_mszy($data, $godzina);
        
        if (!empty($intencje)) {
            wp_send_json_error('Nie można usunąć - msza ma intencje');
        }
        
        // 2. Pobierz aktualny harmonogram
        $harmonogram = $wpdb->get_row($wpdb->prepare(
            "SELECT k.id, k.godziny_msz, k.harmonogram_id 
            FROM $table_kalendarz k 
            INNER JOIN $table_harmonogramy h ON k.harmonogram_id = h.id 
            WHERE k.data = %s AND h.rok = YEAR(%s)",
            $data, $data
        ));
        
        if (!$harmonogram) {
            wp_send_json_error('Nie znaleziono harmonogramu dla tej daty');
        }
        
        // 3. Usuń godzinę z kalendarza
        $aktualne_godziny = array_map('trim', explode(',', $harmonogram->godziny_msz));
        $nowe_godziny = array_filter($aktualne_godziny, function($g) use ($godzina) {
            return $g !== $godzina;
        });
        
        $nowe_godziny_string = implode(',', $nowe_godziny);
        
        $result = $wpdb->update(
            $table_kalendarz,
            array('godziny_msz' => $nowe_godziny_string),
            array('id' => $harmonogram->id),
            array('%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Błąd aktualizacji kalendarza');
        }
        
        // 4. Usuń również z tabeli msze (jeśli istnieje)
        $wpdb->delete(
            $table_msze,
            array(
                'data' => $data,
                'godzina' => $godzina
            ),
            array('%s', '%s')
        );
        
        wp_send_json_success('Godzina usunięta');
    }

    public function dodaj_godzine_do_dnia() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        // SPRAWDŹ CZY ROK JEST EDYTOWALNY
        $data = sanitize_text_field($_POST['data']);
        // ✅ ZABEZPIECZENIE: Sprawdź czy data jest edytowalna
        $sprawdz = $this->sprawdz_czy_data_edytowalna($data);
        if (!$sprawdz['edytowalne']) {
            wp_send_json_error($sprawdz['powod']);
        }

        $rok = date('Y', strtotime($data));
        if (!$this->czy_rok_edytowalny($rok)) {
            wp_send_json_error('Rok zarchiwizowany - edycja zablokowana');
        }
        
        $godzina = sanitize_text_field($_POST['godzina']);
        
        global $wpdb;
        $table_kalendarz = $wpdb->prefix . 'msze_kalendarz';
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        $table_msze = $wpdb->prefix . 'msze';
        
        // 1. Pobierz aktualny harmonogram
        $harmonogram = $wpdb->get_row($wpdb->prepare(
            "SELECT k.id, k.godziny_msz, k.harmonogram_id 
            FROM $table_kalendarz k 
            INNER JOIN $table_harmonogramy h ON k.harmonogram_id = h.id 
            WHERE k.data = %s AND h.rok = YEAR(%s)",
            $data, $data
        ));
        
        if (!$harmonogram) {
            wp_send_json_error('Nie znaleziono harmonogramu dla tej daty');
        }
        
        // 2. Sprawdź czy godzina już istnieje w kalendarzu
        $aktualne_godziny = array_map('trim', explode(',', $harmonogram->godziny_msz));
        if (in_array($godzina, $aktualne_godziny)) {
            wp_send_json_error('Godzina już istnieje');
        }
        
        // 3. Dodaj godzinę do kalendarza
        $aktualne_godziny[] = $godzina;
        sort($aktualne_godziny);
        $nowe_godziny_string = implode(',', $aktualne_godziny);
        
        $result = $wpdb->update(
            $table_kalendarz,
            array('godziny_msz' => $nowe_godziny_string),
            array('id' => $harmonogram->id),
            array('%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Błąd aktualizacji kalendarza');
        }
        
        // 4. Dodaj też do tabeli msze (jeśli nie istnieje)
        $istnieje_w_msze = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_msze WHERE data = %s AND godzina = %s",
            $data, $godzina
        ));
        
        if (!$istnieje_w_msze) {
            $wpdb->insert(
                $table_msze,
                array(
                    'data' => $data,
                    'godzina' => $godzina,
                    'max_intencji' => 5
                ),
                array('%s', '%s', '%d')
            );
        }
        
        wp_send_json_success('Godzina dodana');
    }

    /**
     * SPRAWDŹ CZY DANY ROK MA AKTYWNY HARMONOGRAM (Z DEBUGOWANIEM)
     */
    public function czy_rok_ma_aktywny_harmonogram($rok) {
        global $wpdb;
        $table_harmonogramy = $wpdb->prefix . 'msze_harmonogramy';
        
        $harmonogram = $wpdb->get_row($wpdb->prepare(
            "SELECT id, archiwum FROM $table_harmonogramy WHERE rok = %d", 
            $rok
        ));
        
        // Debug log
        error_log("Sprawdzanie roku {$rok}: " . ($harmonogram ? "Znaleziono (archiwum: {$harmonogram->archiwum})" : "Nie znaleziono"));
        
        // Harmonogram istnieje i NIE jest zarchiwizowany
        return $harmonogram && $harmonogram->archiwum == 0;
    }
    

    /**
     * SPRAWDŹ CZY DATA MOŻE BYĆ EDYTOWANA (POPRAWIONA LOGIKA)
     */
    private function sprawdz_czy_data_edytowalna($data) {
        $rok = date('Y', strtotime($data));
        
        // ✅ NOWA LOGIKA: Sprawdź czy TEN konkretny rok ma aktywny harmonogram
        if (!$this->czy_rok_ma_aktywny_harmonogram($rok)) {
            $powod = 'Brak aktywnego harmonogramu na rok ' . $rok;
            
            // Sprawdź szczegóły dla lepszego komunikatu
            if (!$this->czy_istnieje_harmonogram($rok)) {
                $powod = 'Brak harmonogramu na rok ' . $rok;
            } elseif ($this->czy_rok_zarchiwizowany($rok)) {
                $powod = 'Rok ' . $rok . ' jest zarchiwizowany - edycja zablokowana';
            }
            
            return array(
                'edytowalne' => false,
                'powod' => $powod
            );
        }
        
        return array('edytowalne' => true, 'powod' => '');
    }


    /**
     * NOWA FUNKCJA AJAX DO SPRAWDZANIA HARMONOGRAMU
     */
    public function sprawdz_harmonogram_roku_ajax() {
        check_ajax_referer('ki_nonce', 'nonce');
        
        $rok = intval($_POST['rok']);
        $czy_aktywny = $this->czy_rok_ma_aktywny_harmonogram($rok);
        
        wp_send_json_success(array(
            'rok' => $rok,
            'czy_aktywny' => $czy_aktywny,
            'komunikat' => $czy_aktywny ? 'Aktywny' : 'Brak aktywnego harmonogramu'
        ));
    }
}

// Uruchamiamy plugin
new IntencjeMszalne();