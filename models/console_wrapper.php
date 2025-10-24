<?php
require_once __DIR__ . '/../controler/functions/database.php';
require_once __DIR__ . '/admin/ConsoleWrapperManager.php';
require_once __DIR__ . '/../controler/functions/console_proxy.php';

function Action($conf = null) {
    // Initialiser la configuration si elle n'est pas fournie
    if ($conf === null) {
        include(__DIR__ . '/../controler/conf.php');
    }
    
    // Récupérer l'ID du wrapper depuis les paramètres GET
    $wrapper_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($wrapper_id <= 0) {
        return '<div class="alert alert-danger">Wrapper non trouvé</div>';
    }
    
    // Récupérer le wrapper
    $consoleWrapperManager = new ConsoleWrapperManager($conf);
    $wrapper = $consoleWrapperManager->getWrapperById($wrapper_id);
    
    if (!$wrapper) {
        return '<div class="alert alert-danger">Console non trouvée</div>';
    }
    
    // Si le wrapper est désactivé, ne pas afficher
    if (!$wrapper['enabled']) {
        return '<div class="alert alert-warning">Cette console est désactivée</div>';
    }
    
    // Récupérer les scans
    $scans = [];
    try {
        $scansResult = getConsoleScans($wrapper_id);
        if (isset($scansResult['scans'])) {
            $scans = $scansResult['scans'];
        }
    } catch (Exception $e) {
        // Ignorer les erreurs de récupération des scans
    }
    
    // Préparer les données pour la vue
    $array = [
        'wrapper' => $wrapper,
        'scans' => $scans
    ];
    
    return template("../view/console_wrapper.html.php", $array);
}
?>

