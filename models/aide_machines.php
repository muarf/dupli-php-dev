<?php
/**
 * Page publique d'aide et tutoriels des machines
 * Affiche les questions/réponses organisées par machine
 */

require_once __DIR__ . '/admin/AideManager.php';

function Action($conf) {
    // Créer l'instance du gestionnaire d'aides
    $aideManager = new AideManager($conf);
    
    // Obtenir toutes les données d'aide
    $data = $aideManager->getAllAidesData();
    
    // Obtenir la liste de toutes les machines pour le sélecteur
    $data['all_machines'] = $aideManager->getAllMachines();
    
    // Si une machine spécifique est demandée via GET
    if (isset($_GET['machine']) && !empty($_GET['machine'])) {
        $machine_demandee = htmlspecialchars($_GET['machine']);
        $data['aide_selectionnee'] = $aideManager->getAideByMachine($machine_demandee);
        $data['machine_selectionnee'] = $machine_demandee;
    }
    
    // Variables pour la vue
    $array = $data;
    
    return $array;
}
?>
