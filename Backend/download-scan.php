<?php
/**
 * Télécharger un scan depuis la console RISO via le proxy
 */

require_once __DIR__ . '/../controler/functions/database.php';
require_once __DIR__ . '/../models/admin/ConsoleWrapperManager.php';

// Récupérer le nom du scan et l'ID du wrapper depuis POST ou GET
$scanName = $_POST['scanName'] ?? $_GET['scanName'] ?? '';
$wrapperId = $_POST['wrapper_id'] ?? $_GET['wrapper_id'] ?? 0;

if (empty($scanName) || $wrapperId <= 0) {
    http_response_code(400);
    die('Paramètres manquants');
}

// Récupérer le wrapper depuis la base de données
$consoleWrapperManager = new ConsoleWrapperManager(null);
$wrapper = $consoleWrapperManager->getWrapperById($wrapperId);

if (!$wrapper) {
    http_response_code(404);
    die('Console non trouvée');
}

// Construire l'URL de la console RISO depuis la config
$riso_url = rtrim($wrapper['console_url'], '/');
// URL directe vers le backend RISO pour le téléchargement
$download_url = $riso_url . '/Backend/APP/UI_6_3_6_1_download.app';

// Initialiser cURL
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $download_url);
curl_setopt($ch, CURLOPT_POST, true);
// Les paramètres POST exacts comme observés dans le navigateur
curl_setopt($ch, CURLOPT_POSTFIELDS, 'jobName=' . urlencode($scanName));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Ajouter les cookies de la session actuelle pour maintenir l'authentification
if (!empty($_SERVER['HTTP_COOKIE'])) {
    curl_setopt($ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE']);
}

// En-têtes pour simuler une requête AJAX
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

// Déterminer le type de contenu
if (strpos($content_type, 'application/pdf') !== false || strpos($content_type, 'application/zip') !== false) {
    header('Content-Type: ' . $content_type);
} else {
    header('Content-Type: application/pdf');
}

header('Content-Disposition: attachment; filename="' . $scanName . '.pdf"');

if ($http_code === 200 && !empty($response)) {
    echo $response;
} else {
    http_response_code($http_code ?: 500);
    echo 'Erreur lors du téléchargement (Code: ' . $http_code . ')';
}
?>

