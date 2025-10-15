<?php
require_once __DIR__ . '/../../controler/functions/database.php';

/**
 * Gestionnaire des aides machines
 * Gère les opérations CRUD pour les aides et tutoriels des machines
 */

class AideManager {
    private $conf;
    private $con;
    
    public function __construct($conf) {
        $this->conf = $conf;
        // Initialiser la connexion
        $this->con = new stdClass();
    }
    
    /**
     * Ajouter une aide pour une machine
     */
    public function addAide($machine, $contenu_aide) {
        try {
            $db = pdo_connect();
            
            // Vérifier si une aide existe déjà pour cette machine
            $checkStmt = $db->prepare("SELECT id FROM aide_machines WHERE machine = ?");
            $checkStmt->execute([$machine]);
            
            if ($checkStmt->fetch()) {
                return ['error' => 'Une aide existe déjà pour cette machine. Utilisez la modification pour la mettre à jour.'];
            }
            
            $sql = "INSERT INTO aide_machines (machine, contenu_aide) VALUES (?, ?)";
            $stmt = $db->prepare($sql);
            
            $result = $stmt->execute([$machine, $contenu_aide]);
            
            if ($result) {
                return ['success' => 'Aide ajoutée avec succès'];
            } else {
                return ['error' => 'Erreur lors de l\'ajout'];
            }
            
        } catch (Exception $e) {
            return ['error' => 'Erreur : ' . $e->getMessage()];
        }
    }
    
    /**
     * Mettre à jour une aide existante
     */
    public function updateAide($id, $machine, $contenu_aide) {
        try {
            $db = pdo_connect();
            
            $sql = "UPDATE aide_machines SET machine = ?, contenu_aide = ?, date_modification = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            $result = $stmt->execute([$machine, $contenu_aide, $id]);
            
            if ($result) {
                return ['success' => 'Aide mise à jour avec succès'];
            } else {
                return ['error' => 'Erreur lors de la mise à jour'];
            }
            
        } catch (Exception $e) {
            return ['error' => 'Erreur : ' . $e->getMessage()];
        }
    }
    
    /**
     * Supprimer une aide
     */
    public function deleteAide($id) {
        try {
            $db = pdo_connect();
            
            $sql = "DELETE FROM aide_machines WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return ['success' => 'Aide supprimée avec succès'];
            } else {
                return ['error' => 'Erreur lors de la suppression'];
            }
            
        } catch (Exception $e) {
            return ['error' => 'Erreur : ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtenir toutes les aides
     */
    public function getAllAides() {
        try {
            $db = pdo_connect();
            
            $sql = "SELECT * FROM aide_machines ORDER BY machine ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtenir une aide spécifique par ID
     */
    public function getAide($id) {
        try {
            $db = pdo_connect();
            
            $sql = "SELECT * FROM aide_machines WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Obtenir une aide par nom de machine
     */
    public function getAideByMachine($machine) {
        try {
            $db = pdo_connect();
            
            $sql = "SELECT * FROM aide_machines WHERE machine = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$machine]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Obtenir toutes les machines ayant une aide
     */
    public function getMachinesWithAide() {
        try {
            $db = pdo_connect();
            
            $sql = "SELECT machine FROM aide_machines ORDER BY machine ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtenir toutes les données d'aide pour l'affichage
     */
    public function getAllAidesData() {
        $data = array();
        
        // Obtenir toutes les aides
        $data['aides'] = $this->getAllAides();
        
        // Organiser par machine
        $data['aides_by_machine'] = array();
        foreach ($data['aides'] as $aide) {
            $data['aides_by_machine'][$aide['machine']] = $aide;
        }
        
        return $data;
    }
    
    /**
     * Obtenir la liste de toutes les machines disponibles (duplicopieurs + photocopieurs)
     */
    public function getAllMachines() {
        try {
            $db = pdo_connect();
            $machines = array();
            
            // Récupérer les duplicopieurs
            $sql_dupli = "SELECT CONCAT(marque, ' ', modele) as nom FROM duplicopieurs WHERE actif = 1 ORDER BY marque, modele";
            $stmt_dupli = $db->prepare($sql_dupli);
            $stmt_dupli->execute();
            $duplicopieurs = $stmt_dupli->fetchAll(PDO::FETCH_COLUMN);
            
            // Récupérer les photocopieurs
            $sql_photo = "SELECT CONCAT(marque, ' ', modele) as nom FROM photocopieurs WHERE actif = 1 ORDER BY marque, modele";
            $stmt_photo = $db->prepare($sql_photo);
            $stmt_photo->execute();
            $photocopieurs = $stmt_photo->fetchAll(PDO::FETCH_COLUMN);
            
            // Fusionner et trier
            $machines = array_merge($duplicopieurs, $photocopieurs);
            sort($machines);
            
            return $machines;
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
