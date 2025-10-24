<?php
/**
 * Proxy console - affiche une console dans une iframe
 */

require_once __DIR__ . '/../controler/functions/database.php';

function Action($conf = null) {
    // Récupérer l'URL à proxy
    $url = isset($_GET['url']) ? $_GET['url'] : '';
    
    if (empty($url)) {
        die('URL manquante');
    }
    
    // Si c'est une sous-requête (CSS, JS, etc.), reconstruire l'URL complète
    if (strpos($url, '/UI/') !== false) {
        $baseUrl = isset($_GET['base']) ? $_GET['base'] : 'http://localhost:8022';
        $url = $baseUrl . $url;
    }
    
    // Récupérer le contenu de la console
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    // Récupérer les cookies de session si disponibles
    if (isset($_COOKIE)) {
        $cookies = [];
        foreach ($_COOKIE as $name => $value) {
            $cookies[] = $name . '=' . $value;
        }
        if (!empty($cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookies));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        die('Erreur cURL: ' . $error);
    }
    
    if ($httpCode !== 200) {
        die('Code HTTP: ' . $httpCode . ' - URL: ' . $url);
    }
    
    // Remplacer les URLs relatives par des URLs absolues via le proxy
    $baseUrl = rtrim($url, '/');
    $proxyUrl = '?console_proxy&url=' . urlencode($baseUrl);
    
    // Remplacer les liens et scripts
    $response = preg_replace('/href="(\/[^"]+)"/', 'href="' . $proxyUrl . '$1"', $response);
    $response = preg_replace('/src="(\/[^"]+)"/', 'src="' . $proxyUrl . '$1"', $response);
    $response = preg_replace('/url\((\/[^)]+)\)/', 'url(' . $proxyUrl . '$1)', $response);
    
    // Remplacer les URLs dans les iframes
    $response = preg_replace('/src="([^"]*UI\/[^"]+)"/', 'src="' . $proxyUrl . '/$1"', $response);
    
    // Modifier userStatus pour forcer l'affichage de la page système
    $response = preg_replace('/id="userStatus" value="[^"]*"/', 'id="userStatus" value="1"', $response);
    
    // Forcer l'affichage de la page système et masquer la page login
    $response = str_replace('class="style_page_system"', 'class="style_page_system" style="display: block !important;"', $response);
    $response = str_replace('class="style_page_login"', 'class="style_page_login" style="display: none !important;"', $response);
    
    // Afficher le contenu
    return $response;
}
?>
