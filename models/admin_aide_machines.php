<?php
/**
 * Page d'administration des aides machines
 * Gère les opérations CRUD pour les aides et tutoriels
 */

require_once __DIR__ . '/admin/AideManager.php';

function Action($conf) {
    // Créer l'instance du gestionnaire d'aides
    $aideManager = new AideManager($conf);
    
    // Variables pour les messages
    $aide_error = null;
    $aide_success = null;
    
    // Gestion des actions POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_aide':
                    if (!empty($_POST['machine']) && !empty($_POST['contenu_aide'])) {
                        $result = $aideManager->addAide(
                            htmlspecialchars($_POST['machine']),
                            $_POST['contenu_aide'] // Ne pas échapper le HTML pour permettre le formatage
                        );
                        
                        if (isset($result['success'])) {
                            $aide_success = $result['success'];
                        } else {
                            $aide_error = $result['error'];
                        }
                    } else {
                        $aide_error = 'Tous les champs sont obligatoires.';
                    }
                    break;
                    
                case 'edit_aide':
                    if (!empty($_POST['id']) && !empty($_POST['machine']) && !empty($_POST['contenu_aide'])) {
                        $result = $aideManager->updateAide(
                            $_POST['id'],
                            htmlspecialchars($_POST['machine']),
                            $_POST['contenu_aide']
                        );
                        
                        if (isset($result['success'])) {
                            $aide_success = $result['success'];
                        } else {
                            $aide_error = $result['error'];
                        }
                    } else {
                        $aide_error = 'Tous les champs sont obligatoires.';
                    }
                    break;
                    
                case 'delete_aide':
                    if (!empty($_POST['id'])) {
                        $result = $aideManager->deleteAide($_POST['id']);
                        
                        if (isset($result['success'])) {
                            $aide_success = $result['success'];
                        } else {
                            $aide_error = $result['error'];
                        }
                    } else {
                        $aide_error = 'ID manquant pour la suppression.';
                    }
                    break;
            }
        }
    }
    
    // Gestion des requêtes AJAX
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        
        switch ($_GET['ajax']) {
            case 'get_aide':
                if (isset($_GET['id'])) {
                    $aide = $aideManager->getAide($_GET['id']);
                    if ($aide) {
                        echo json_encode(['success' => true, 'aide' => $aide]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Aide non trouvée']);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'ID manquant']);
                }
                exit;
                
            case 'get_machines':
                $machines = $aideManager->getAllMachines();
                echo json_encode(['success' => true, 'machines' => $machines]);
                exit;
        }
    }
    
    // Obtenir toutes les données pour l'affichage
    $data = $aideManager->getAllAidesData();
    $data['all_machines'] = $aideManager->getAllMachines();
    
    // Ajouter les messages
    $data['aide_error'] = $aide_error;
    $data['aide_success'] = $aide_success;
    
    return $data;
}
?>
