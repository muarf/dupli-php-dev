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
    curl_close($ch);
    
    if ($response === false || $httpCode !== 200) {
        die('Erreur lors de la récupération de la console');
    }
    
    // Remplacer les URLs relatives par des URLs absolues
    $baseUrl = rtrim($url, '/');
    $response = preg_replace('/href="(\/[^"]+)"/', 'href="' . $baseUrl . '$1"', $response);
    $response = preg_replace('/src="(\/[^"]+)"/', 'src="' . $baseUrl . '$1"', $response);
    $response = preg_replace('/url\((\/[^)]+)\)/', 'url(' . $baseUrl . '$1)', $response);
    
    // Afficher le contenu
    return $response;
}
?>
