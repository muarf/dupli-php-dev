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

// Debug
error_log("RISO Proxy: Demande pour $request_uri -> $target_url");

// Récupérer le contenu
$ch = curl_init($target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_HEADER, true);

// Gérer les méthodes HTTP (GET, POST, etc.)
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    $post_data = file_get_contents('php://input');
    if ($post_data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    // Copier les headers Content-Type si présents
    if (isset($_SERVER['CONTENT_TYPE'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: ' . $_SERVER['CONTENT_TYPE']
        ]);
    }
}

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type_header = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($response === false) {
    error_log("RISO Proxy: Erreur cURL pour $target_url");
    http_response_code(404);
    die('Erreur de connexion');
}

if ($http_code !== 200) {
    error_log("RISO Proxy: HTTP $http_code pour $target_url");
    http_response_code($http_code);
    die("Erreur HTTP: $http_code");
}

// Séparer headers et contenu
list($headers, $body) = explode("\r\n\r\n", $response, 2);

// Déterminer le type de contenu
$content_type = 'text/html';
if ($content_type_header) {
    $content_type = $content_type_header;
} elseif (strpos($path, '.css') !== false) {
    $content_type = 'text/css';
} elseif (strpos($path, '.js') !== false) {
    $content_type = 'application/javascript';
} elseif (strpos($path, '.png') !== false) {
    $content_type = 'image/png';
} elseif (strpos($path, '.gif') !== false) {
    $content_type = 'image/gif';
} elseif (strpos($path, '.jpg') !== false || strpos($path, '.jpeg') !== false) {
    $content_type = 'image/jpeg';
}

// Headers de réponse
header('Content-Type: ' . $content_type);
header('X-Frame-Options: ALLOWALL');

// Pour les images et fichiers binaires, pas de cache
if (strpos($content_type, 'image/') === 0) {
    header('Cache-Control: no-cache');
} else {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Pour le HTML, réécrire les URLs
if (strpos($content_type, 'text/html') === 0) {
    // Modifier userStatus pour forcer l'affichage de la page système
    $body = preg_replace('/id="userStatus" value="[^"]*"/', 'id="userStatus" value="1"', $body);
    
    // Forcer l'affichage de la page système et masquer la page login
    $body = str_replace('class="style_page_system"', 'class="style_page_system" style="display: block !important;"', $body);
    $body = str_replace('class="style_page_login"', 'class="style_page_login" style="display: none !important;"', $body);
    
    // Réécrire les URLs relatives pour pointer vers le proxy
    $body = preg_replace('/href="([^"]*)"/', 'href="/riso-proxy/$1"', $body);
    $body = preg_replace('/src="([^"]*)"/', 'src="/riso-proxy/$1"', $body);
    $body = preg_replace('/url\(([^)]+)\)/', 'url(/riso-proxy/$1)', $body);
    
    // Corriger les doubles slashes
    $body = str_replace('/riso-proxy//', '/riso-proxy/', $body);
}

// Pour le JavaScript, réécrire les URLs AJAX et masquer les erreurs
if (strpos($content_type, 'javascript') !== false) {
    // Réécrire les URLs dans les requêtes AJAX
    $body = preg_replace('/(["\'])\/Backend\//', '$1/riso-proxy/Backend/', $body);
    $body = preg_replace('/(["\'])Backend\//', '$1/riso-proxy/Backend/', $body);
    
    // Ajouter une gestion d'erreur silencieuse pour les requêtes AJAX
    $body .= "\n// Masquer les erreurs AJAX pour une meilleure UX\n";
    $body .= "if (typeof XMLHttpRequest !== 'undefined') {\n";
    $body .= "  const originalSend = XMLHttpRequest.prototype.send;\n";
    $body .= "  XMLHttpRequest.prototype.send = function(...args) {\n";
    $body .= "    this.addEventListener('error', function() { console.log('RISO API non disponible via proxy'); });\n";
    $body .= "    return originalSend.apply(this, args);\n";
    $body .= "  };\n";
    $body .= "}\n";
}

echo $body;
?>
