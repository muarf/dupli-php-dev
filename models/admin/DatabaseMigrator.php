<?php
/**
 * Système de migration de base de données
 * Vérifie et applique les migrations nécessaires
 */

require_once __DIR__ . '/../../controler/conf.php';
require_once __DIR__ . '/../../controler/functions/database.php';

class DatabaseMigrator {
    private $db;
    
    public function __construct() {
        $this->db = pdo_connect();
    }
    
    /**
     * Obtenir toutes les migrations disponibles
     */
    private function getMigrations() {
        return [
            'add_machine_console_wrappers_table' => function($db) {
                // Vérifier si la table existe déjà
                $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='machine_console_wrappers'");
                $table_exists = $stmt->fetch();
                
                if (!$table_exists) {
                    $create_table_sql = "CREATE TABLE IF NOT EXISTS machine_console_wrappers (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        machine_name TEXT NOT NULL,
                        console_url TEXT NOT NULL,
                        console_type TEXT DEFAULT 'riso_comcolor',
                        username TEXT,
                        password TEXT,
                        scan_endpoint TEXT DEFAULT 'UI/IE/NewUIpage/Page/RC_Scan.phtml',
                        enabled INTEGER DEFAULT 1,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )";
                    
                    $db->exec($create_table_sql);
                    return "Table machine_console_wrappers créée";
                }
                return "Table machine_console_wrappers existe déjà";
            }
            
            // Ajouter d'autres migrations ici au fur et à mesure
        ];
    }
    
    /**
     * Obtenir les migrations déjà appliquées
     */
    private function getAppliedMigrations() {
        try {
            // Créer la table des migrations si elle n'existe pas
            $this->db->exec("CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration_name TEXT NOT NULL UNIQUE,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Récupérer les migrations appliquées
            $stmt = $this->db->query("SELECT migration_name FROM migrations");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Marquer une migration comme appliquée
     */
    private function markMigrationAsApplied($migrationName) {
        $stmt = $this->db->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
        $stmt->execute([$migrationName]);
    }
    
    /**
     * Créer une sauvegarde de la base de données
     */
    public function createBackup() {
        try {
            global $conf;
            
            if (!isset($conf['db_path'])) {
                return ['success' => false, 'error' => 'Chemin de base de données non défini'];
            }
            
            $backupDir = dirname($conf['db_path']) . '/backups';
            
            // Créer le dossier backups s'il n'existe pas
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // Nom du fichier de backup avec timestamp
            $backupFilename = 'backup_' . date('Y-m-d_H-i-s') . '.sqlite';
            $backupPath = $backupDir . '/' . $backupFilename;
            
            // Copier le fichier de base de données
            if (copy($conf['db_path'], $backupPath)) {
                return [
                    'success' => true,
                    'backup_path' => $backupPath,
                    'message' => 'Backup créé: ' . $backupFilename
                ];
            } else {
                return ['success' => false, 'error' => 'Échec de la copie du fichier'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Exécuter toutes les migrations en attente
     */
    public function runMigrations() {
        $results = [];
        $appliedMigrations = $this->getAppliedMigrations();
        $allMigrations = $this->getMigrations();
        
        foreach ($allMigrations as $migrationName => $migrationFunction) {
            if (!in_array($migrationName, $appliedMigrations)) {
                try {
                    $result = $migrationFunction($this->db);
                    $this->markMigrationAsApplied($migrationName);
                    $results[] = [
                        'migration' => $migrationName,
                        'success' => true,
                        'message' => $result
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        'migration' => $migrationName,
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Exécuter une mise à jour complète avec backup
     */
    public function update() {
        $output = [];
        
        // Créer une sauvegarde
        $output[] = "Création d'une sauvegarde...";
        $backup = $this->createBackup();
        
        if ($backup['success']) {
            $output[] = "✓ " . $backup['message'];
        } else {
            $output[] = "⚠ Erreur lors de la sauvegarde: " . $backup['error'];
            // Continuer quand même
        }
        
        // Exécuter les migrations
        $output[] = "Exécution des migrations...";
        $migrations = $this->runMigrations();
        
        foreach ($migrations as $migration) {
            if ($migration['success']) {
                $output[] = "✓ " . $migration['migration'] . ": " . $migration['message'];
            } else {
                $output[] = "✗ " . $migration['migration'] . ": " . $migration['error'];
            }
        }
        
        return $output;
    }
}

// Si exécuté en ligne de commande
if (php_sapi_name() === 'cli') {
    echo "=== Migration de la base de données ===\n\n";
    
    $migrator = new DatabaseMigrator();
    $results = $migrator->update();
    
    foreach ($results as $result) {
        echo $result . "\n";
    }
    
    echo "\n✓ Migration terminée\n";
}
?>

