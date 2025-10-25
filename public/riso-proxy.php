<?php
/**
 * Proxy intelligent pour la console RISO
 */

// Récupérer l'URL demandée
$request_uri = $_SERVER['REQUEST_URI'];
$path = str_replace('/riso-proxy/', '', $request_uri);

// URL de base de la console RISO  
$base_url = 'http://localhost:8023';

// Construire l'URL complète
$target_url = $base_url . '/' . ltrim($path, '/');

// Headers pour éviter le cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Récupérer le contenu
$ch = curl_init($target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $http_code !== 200) {
    http_response_code(404);
    die('Ressource non trouvée');
}

// Déterminer le type de contenu
$content_type = 'text/html';
if (strpos($path, '.css') !== false) {
    $content_type = 'text/css';
} elseif (strpos($path, '.js') !== false) {
    $content_type = 'application/javascript';
} elseif (strpos($path, '.png') !== false) {
    $content_type = 'image/png';
} elseif (strpos($path, '.gif') !== false) {
    $content_type = 'image/gif';
}

header('Content-Type: ' . $content_type);

// Pour le HTML, réécrire les URLs
if ($content_type === 'text/html') {
    // Modifier userStatus pour forcer l'affichage de la page système
    $response = preg_replace('/id="userStatus" value="[^"]*"/', 'id="userStatus" value="1"', $response);
    
    // Forcer l'affichage de la page système et masquer la page login
    $response = str_replace('class="style_page_system"', 'class="style_page_system" style="display: block !important;"', $response);
    $response = str_replace('class="style_page_login"', 'class="style_page_login" style="display: none !important;"', $response);
    
    // Réécrire les URLs pour pointer vers le proxy
    $response = preg_replace('/href="(\/[^"]+)"/', 'href="/riso-proxy$1"', $response);
    $response = preg_replace('/src="(\/[^"]+)"/', 'src="/riso-proxy$1"', $response);
    $response = preg_replace('/url\((\/[^)]+)\)/', 'url(/riso-proxy$1)', $response);
}

echo $response;
?>
