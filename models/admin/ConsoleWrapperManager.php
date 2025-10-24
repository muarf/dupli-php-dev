<?php
require_once __DIR__ . '/../../controler/functions/database.php';

/**
 * Gestionnaire pour les wrappers de consoles machines
 */
class ConsoleWrapperManager {
    private $conf;
    
    public function __construct($conf) {
        $this->conf = $conf;
    }
    
    /**
     * Obtenir tous les wrappers
     */
    public function getAllWrappers() {
        try {
            $db = pdo_connect();
            $stmt = $db->query("SELECT * FROM machine_console_wrappers ORDER BY machine_name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtenir les wrappers actifs uniquement
     */
    public function getActiveWrappers() {
        try {
            $db = pdo_connect();
            $stmt = $db->prepare("SELECT * FROM machine_console_wrappers WHERE enabled = 1 ORDER BY machine_name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtenir un wrapper par ID
     */
    public function getWrapperById($id) {
        try {
            $db = pdo_connect();
            $stmt = $db->prepare("SELECT * FROM machine_console_wrappers WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Ajouter un wrapper
     */
    public function addWrapper($data) {
        try {
            $db = pdo_connect();
            
            $stmt = $db->prepare("
                INSERT INTO machine_console_wrappers 
                (machine_name, console_url, console_type, username, password, scan_endpoint, enabled) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['machine_name'],
                $data['console_url'],
                $data['console_type'] ?? 'riso_comcolor',
                $data['username'] ?? null,
                $data['password'] ?? null,
                $data['scan_endpoint'] ?? 'UI/IE/NewUIpage/Page/RC_Scan.phtml',
                $data['enabled'] ?? 1
            ]);
            
            if ($result) {
                return ['success' => 'Wrapper ajouté avec succès'];
            } else {
                return ['error' => 'Erreur lors de l\'ajout'];
            }
        } catch (Exception $e) {
            return ['error' => 'Erreur : ' . $e->getMessage()];
        }
    }
    
    /**
     * Mettre à jour un wrapper
     */
    public function updateWrapper($id, $data) {
        try {
            $db = pdo_connect();
            
            $stmt = $db->prepare("
                UPDATE machine_console_wrappers 
                SET machine_name = ?, console_url = ?, console_type = ?, 
                    username = ?, password = ?, scan_endpoint = ?, enabled = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $data['machine_name'],
                $data['console_url'],
                $data['console_type'] ?? 'riso_comcolor',
                $data['username'] ?? null,
                $data['password'] ?? null,
                $data['scan_endpoint'] ?? 'UI/IE/NewUIpage/Page/RC_Scan.phtml',
                $data['enabled'] ?? 1,
                $id
            ]);
            
            if ($result) {
                return ['success' => 'Wrapper modifié avec succès'];
            } else {
                return ['error' => 'Erreur lors de la modification'];
            }
        } catch (Exception $e) {
            return ['error' => 'Erreur : ' . $e->getMessage()];
        }
    }
    
    /**
     * Supprimer un wrapper
     */
    public function deleteWrapper($id) {
        try {
            $db = pdo_connect();
            $stmt = $db->prepare("DELETE FROM machine_console_wrappers WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return ['success' => 'Wrapper supprimé avec succès'];
            } else {
                return ['error' => 'Erreur lors de la suppression'];
            }
        } catch (Exception $e) {
            return ['error' => 'Erreur : ' . $e->getMessage()];
        }
    }
    
    /**
     * Tester la connexion à une console
     */
    public function testConnection($id) {
        try {
            $wrapper = $this->getWrapperById($id);
            if (!$wrapper) {
                return ['error' => 'Wrapper non trouvé'];
            }
            
            $url = rtrim($wrapper['console_url'], '/') . '/';
            
            // Test de connexion basique
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                return ['success' => 'Connexion réussie (HTTP ' . $httpCode . ')'];
            } else {
                return ['error' => 'Erreur HTTP ' . $httpCode];
            }
        } catch (Exception $e) {
            return ['error' => 'Erreur : ' . $e->getMessage()];
        }
    }
    
    /**
     * Toggle l'état activé/désactivé d'un wrapper
     */
    public function toggleEnabled($id) {
        try {
            $db = pdo_connect();
            
            // Récupérer l'état actuel
            $wrapper = $this->getWrapperById($id);
            if (!$wrapper) {
                return ['error' => 'Wrapper non trouvé'];
            }
            
            $newState = $wrapper['enabled'] ? 0 : 1;
            
            $stmt = $db->prepare("UPDATE machine_console_wrappers SET enabled = ? WHERE id = ?");
            $stmt->execute([$newState, $id]);
            
            return ['success' => 'État modifié', 'enabled' => $newState];
        } catch (Exception $e) {
            return ['error' => 'Erreur : ' . $e->getMessage()];
        }
    }
}
?>

