<?php
/**
 * Script de nettoyage des logs de debug (optionnel)
 * À exécuter seulement si vous voulez supprimer les logs de debug
 */

echo "🧹 Nettoyage des logs de debug...\n\n";

$files_to_clean = [
    'models/admin.php',
    'models/admin/MachineManager.php'
];

foreach ($files_to_clean as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $original_content = $content;
        
        // Supprimer les logs de debug spécifiques
        $content = preg_replace('/\s*error_log\("DEBUG[^"]*"\);\s*\n/', '', $content);
        $content = preg_replace('/\s*error_log\("DEBUG[^"]*" \. [^;]*\);\s*\n/', '', $content);
        
        if ($content !== $original_content) {
            file_put_contents($file, $content);
            echo "✅ Nettoyé: $file\n";
        } else {
            echo "ℹ️  Aucun changement: $file\n";
        }
    }
}

echo "\n🎉 Nettoyage terminé !\n";
echo "Note: Les logs d'erreur importants ont été conservés.\n";
?>