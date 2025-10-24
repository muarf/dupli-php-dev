<?php
/**
 * Script de mise à jour de la base de données
 * À appeler lors des mises à jour Electron
 */

require_once __DIR__ . '/models/admin/DatabaseMigrator.php';

echo "=== Mise à jour de la base de données ===\n\n";

try {
    $migrator = new DatabaseMigrator();
    $results = $migrator->update();
    
    foreach ($results as $result) {
        echo $result . "\n";
    }
    
    echo "\n✓ Mise à jour terminée avec succès!\n";
    exit(0);
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>

