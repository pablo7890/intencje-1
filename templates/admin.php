<?php
if (!defined('ABSPATH')) {
    exit;
}

// DEBUG HARMONOGRAMU
global $wpdb;
$table = $wpdb->prefix . 'msze_harmonogramy';
$harmonogram = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table WHERE rok = %d", $rok_tygodnia
));

echo "<!-- DEBUG: ";
echo "Rok: $rok_tygodnia, ";
echo "Znaleziony harmonogram: " . ($harmonogram ? 'TAK' : 'NIE') . ", ";
if ($harmonogram) {
    echo "Archiwum: " . $harmonogram->archiwum . ", ";
    echo "Nazwa: " . $harmonogram->nazwa;
}
echo " -->";

// Pobierz datę - UWZGLĘDNIJ RĘCZNY WYBÓR!
if (isset($_GET['data_start']) && !empty($_GET['data_start'])) {
    $wybrana_data = sanitize_text_field($_GET['data_start']);
    $obecna_niedziela = date('Y-m-d', strtotime('last sunday', strtotime($wybrana_data)));
} elseif (isset($_GET['tydzien'])) {
    $obecna_niedziela = sanitize_text_field($_GET['tydzien']);
} else {
    $obecna_niedziela = date('Y-m-d', strtotime('last sunday'));
}

$rok_tygodnia = date('Y', strtotime($obecna_niedziela));
$status = $this->pobierz_status_roku($rok_tygodnia);

// Renderuj odpowiedni widok
switch ($status) {
    case 'brak':
        include KI_PLUGIN_PATH . 'templates/tydzien-brak-harmonogramu.php';
        break;
        
    case 'zarchiwizowany':
        include KI_PLUGIN_PATH . 'templates/tydzien-zarchiwizowany.php';
        break;
        
    case 'aktywny':
    default:
        include KI_PLUGIN_PATH . 'templates/tydzien-normalny.php';
        break;
}
?>