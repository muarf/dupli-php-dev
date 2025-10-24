<?php
/**
 * Script de mise à jour de la base de données
 * Ajoute la table machine_console_wrappers si elle n'existe pas
 */

require_once __DIR__ . '/controler/conf.php';
require_once __DIR__ . '/controler/functions/database.php';

echo "=== Mise à jour de la base de données : Table machine_console_wrappers ===\n";

try {
    // Connexion à la base locale
    $db = pdo_connect();
    echo "✓ Connexion à la base locale réussie\n";
    
    // Vérifier si la table machine_console_wrappers existe
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='machine_console_wrappers'");
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "⚠ Table machine_console_wrappers n'existe pas, création...\n";
        
        // Créer la table machine_console_wrappers
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
        echo "✓ Table machine_console_wrappers créée\n";
        
    } else {
        echo "✓ Table machine_console_wrappers existe déjà\n";
    }
    
    // Vérifier l'existence de la colonne updated_at
    $stmt = $db->query("PRAGMA table_info(machine_console_wrappers)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $has_updated_at = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'updated_at') {
            $has_updated_at = true;
            break;
        }
    }
    
    if (!$has_updated_at) {
        echo "⚠ Colonne updated_at manquante, ajout...\n";
        $db->exec("ALTER TABLE machine_console_wrappers ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        echo "✓ Colonne updated_at ajoutée\n";
    }
    
    echo "\n✓ Mise à jour terminée avec succès!\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>

