<?php
/**
 * Proxy intelligent pour la console RISO
 */

// Récupérer l'URL demandée
$request_uri = $_SERVER['REQUEST_URI'];
$path = str_replace('/riso-proxy/', '', $request_uri);

// URL de base de la console RISO  
$base_url = 'http://localhost:8023';

// Construire l'URL complète - rediriger vers la page scan si c'est la racine
if (empty($path) || $path === '/') {
    $target_url = $base_url . '/UI/IE/NewUIpage/Page/RC_Scan.phtml';
} else {
    $target_url = $base_url . '/' . ltrim($path, '/');
}

// Gestion spéciale pour les téléchargements
$is_download = strpos($path, 'download.app') !== false || isset($_GET['jobName']);

// Debug
error_log("RISO Proxy: Demande pour $request_uri -> $target_url");

// Initialiser curl
$ch = curl_init($target_url);

// Si c'est un téléchargement, utiliser POST
if ($is_download && isset($_GET['jobName'])) {
    $download_path = str_replace('/riso-proxy/', '', $request_uri);
    $download_path = strtok($download_path, '?'); // Enlever les paramètres GET
    $target_url = $base_url . '/' . ltrim($download_path, '/');
    
    error_log("RISO Proxy: Téléchargement demandé pour " . $_GET['jobName']);
    
    curl_setopt($ch, CURLOPT_URL, $target_url);
    
    // Ajouter les paramètres POST
    $post_data = http_build_query([
        'type' => 'download',
        'jobName' => $_GET['jobName']
    ]);
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_HEADER, true);

// Headers pour faire croire que la requête vient du réseau local
$headers = [
    'Host: 192.168.1.110',
    'Origin: http://192.168.1.110',
    'Referer: http://192.168.1.110/',
    'Accept: */*',
    'Accept-Language: fr-FR,fr;q=0.8',
    'Connection: keep-alive',
    'Sec-GPC: 1'
];

// Gestion des cookies pour maintenir la session
$cookie_file = '/tmp/riso_cookies.txt';
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);

// Utiliser le sessionid de la console RISO
$riso_session = '9761c806fbc167e5ad64203893061650';
curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=$riso_session");

// Gérer les méthodes HTTP (GET, POST, etc.)
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    $post_data = file_get_contents('php://input');
    if ($post_data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    // Ajouter Content-Type aux headers si présent
    if (isset($_SERVER['CONTENT_TYPE'])) {
        $headers[] = 'Content-Type: ' . $_SERVER['CONTENT_TYPE'];
    }
}

// Appliquer tous les headers
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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
$header_end = strpos($response, "\r\n\r\n");
if ($header_end !== false) {
    $headers_raw = substr($response, 0, $header_end);
    $body = substr($response, $header_end + 4);
    
    // Parser les headers pour trouver Content-Type
    $headers_lines = explode("\r\n", $headers_raw);
    foreach ($headers_lines as $header_line) {
        if (stripos($header_line, 'Content-Type:') === 0) {
            $content_type_header = trim(substr($header_line, 13));
            // Extraire le MIME type (enlever les paramètres comme charset)
            if (($pos = strpos($content_type_header, ';')) !== false) {
                $content_type_header = substr($content_type_header, 0, $pos);
            }
            $content_type_header = trim($content_type_header);
            break;
        }
    }
} else {
    $body = $response;
}

// Gestion spéciale pour les téléchargements PDF
if ($is_download && isset($_GET['jobName'])) {
    // Servir directement le PDF sans transformations
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $_GET['jobName'] . '.pdf"');
    header('Cache-Control: no-cache');
    echo $body;
    exit;
}

// Déterminer le type de contenu
$content_type = 'text/html';
if (isset($content_type_header) && !empty($content_type_header)) {
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

// Debug: logger la taille du body pour les fichiers JS
if (strpos($content_type, 'javascript') !== false) {
    error_log("RISO Proxy: JS file, body size: " . strlen($body) . " bytes");
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
    // URLs absolutes commençant par /
    $body = preg_replace('/href="\/(?!riso-proxy\/)([^"]*)"/', 'href="/riso-proxy/$1"', $body);
    $body = preg_replace('/src="\/(?!riso-proxy\/)([^"]*)"/', 'src="/riso-proxy/$1"', $body);
    
    // URLs relatives avec ../ (depuis Page vers racine UI/IE/NewUIpage)
    $body = preg_replace('/href="\.\.\/([^"]*)"/', 'href="/riso-proxy/UI/IE/NewUIpage/$1"', $body);
    $body = preg_replace('/src="\.\.\/([^"]*)"/', 'src="/riso-proxy/UI/IE/NewUIpage/$1"', $body);
    
    // URLs relatives simples (dans le même dossier Page) - éviter de modifier les URLs externes
    $body = preg_replace('/href="([^"\/h][^"]*)"/', 'href="/riso-proxy/UI/IE/NewUIpage/Page/$1"', $body);
    $body = preg_replace('/src="([^"\/h][^"]*)"/', 'src="/riso-proxy/UI/IE/NewUIpage/Page/$1"', $body);
    $body = preg_replace('/url\(([^)]+)\)/', 'url(/riso-proxy/$1)', $body);
    
    // Retirer l'attribut onload inline pour éviter l'appel avant que les scripts soient chargés
    $body = preg_replace('/onload\s*=\s*["\']getDataByServer\(([^)]+)\)["\']/', '', $body);
    
    // Forcer l'affichage de la page système uniquement (masquer complètement le login)
    $body = str_replace('style="display: block !important;"', 'style="display: block !important; position: relative; z-index: 999;"', $body);
    $body = str_replace('style="display: none !important;"', 'style="display: none !important; position: absolute; top: -9999px; visibility: hidden;"', $body);
    
        // Corriger les doubles et multiples slashes
        $body = preg_replace('/\/riso-proxy\/+\/riso-proxy\//', '/riso-proxy/', $body);
        $body = str_replace('/riso-proxy//', '/riso-proxy/', $body);
}

    // Pour le JavaScript, réécrire les URLs AJAX pour qu'elles passent par le proxy
    if (strpos($content_type, 'javascript') !== false) {
        // Réécrire les URLs dans les requêtes AJAX pour qu'elles passent par le proxy
        $body = preg_replace('/(["\'])\/Backend\//', '$1/riso-proxy/Backend/', $body);
        $body = preg_replace('/(["\'])Backend\//', '$1/riso-proxy/Backend/', $body);
        $body = preg_replace('/(["\'])\/UI\//', '$1/riso-proxy/UI/', $body);
        
        // Corriger les accès à contentDocument qui causent le clignotement
        $body = str_replace(
            '.contentDocument',
            '?.contentDocument || null',
            $body
        );
        
        // Corriger les accès aux propriétés xml nulles
        $body = preg_replace(
            '/\.xml==""/',
            '.xml == "" || !obj.responseXML',
            $body
        );
        
        // Corriger obj.responseXML.xml qui cause le clignotement
        $body = str_replace(
            'obj.responseXML.xml',
            '(obj.responseXML ? obj.responseXML.xml : "")',
            $body
        );
        
        // Note: Les corrections complexes de JavaScript peuvent causer des erreurs de syntaxe
        // Mieux vaut ne rien faire et laisser le script s'exécuter
    }

    // Pour le HTML, ajouter des corrections JavaScript
    if (strpos($content_type, 'text/html') !== false) {
        // Script de correction global - doit s'exécuter immédiatement, sans boucle d'attente
        $fix_script = "<script type='text/javascript'>
        // S'exécuter immédiatement sans attendre le DOM
        console.log('RISO Proxy Fix Script Loading...');
        
        // Définir les fonctions/classes CRITIQUES immédiatement avant tout autre script
        // maskDialog est un constructeur dans le code RISO
        window.maskDialog = function(width, height) { 
            console.log('maskDialog constructor called with', width, height);
            this.mainForm = null;
            this.show = function() { console.log('maskDialog.show()'); };
            this.hide = function() { console.log('maskDialog.hide()'); };
            return this;
        };
        
        // Protéger GLOBALEMENT contre les erreurs de contentDocument
        Object.defineProperty(HTMLElement.prototype, 'contentDocument', {
            get: function() {
                try {
                    // Tentative normale d'accès
                    return this.contentWindow ? this.contentWindow.document : null;
                } catch(e) {
                    return null;
                }
            }
        });
        
        // Appeler getDataByServer après un délai pour laisser les scripts se charger
        console.log('Proxy fix script loaded');
        
        setTimeout(function() {
            var attempts = 0;
            var maxAttempts = 15;
            
            var interval = setInterval(function() {
                attempts++;
                
                if (typeof getDataByServer === 'function') {
                    console.log('getDataByServer found! Calling with 10, 1');
                    clearInterval(interval);
                    getDataByServer(10, 1);
                } else if (attempts >= maxAttempts) {
                    console.error('getDataByServer never loaded after', maxAttempts, 'attempts');
                    clearInterval(interval);
                }
            }, 200);
        }, 500);
        
        // Corrections globales pour l'iframe RISO
        (function() {
            'use strict';
            window.parent = window.parent || {};
            if (!window.parent.mouseMoved) {
                window.parent.mouseMoved = function() {};
            }
            
            // Protéger contre les erreurs getElementsByTagName sur undefined
            var originalGetElementsByTagName = Element.prototype.getElementsByTagName;
            Element.prototype.getElementsByTagName = function(tagName) {
                if (!this) return [];
                return originalGetElementsByTagName.call(this, tagName);
            };
            
            // Protéger accès aux propriétés manhome - doit être défini AVANT que le code RISO ne l'utilise
            try {
                window.manhome = null;
            } catch(e) {}
            
            // Protéger setMainHomeHeight contre les erreurs
            var originalDefineProperty = Object.defineProperty;
            Object.defineProperty = function(obj, prop, descriptor) {
                if (prop === 'manhome') {
                    descriptor.get = function() { return null; };
                    descriptor.set = function(val) { /* noop */ };
                }
                return originalDefineProperty.call(this, obj, prop, descriptor);
            };
            
            // Redéfinir setMainHomeHeight pour éviter les erreurs de contentDocument null
            if (typeof setMainHomeHeight === 'function') {
                var originalSetMainHomeHeight = setMainHomeHeight;
                window.setMainHomeHeight = function() {
                    try {
                        return originalSetMainHomeHeight.apply(this, arguments);
                    } catch(e) {
                        // Ignorer l'erreur
                        return;
                    }
                };
            } else {
                // Si la fonction n'existe pas encore, la définir avec protection
                window.setMainHomeHeight = function() {
                    try {
                        // Essayer d'exécuter le code original si disponible
                        if (window.parent && window.parent.document) {
                            // Code original sécurisé
                        }
                    } catch(e) {
                        // Ignorer l'erreur
                        return;
                    }
                };
            }
            
            // Intercepter fetch pour router toutes les requêtes via le proxy
            const originalFetch = window.fetch;
            window.fetch = function(url, options) {
                if (typeof url === 'string' && !url.includes('/riso-proxy/') && !url.startsWith('http')) {
                    if (url.startsWith('/')) {
                        url = '/riso-proxy' + url;
                    } else if (!url.startsWith('/')) {
                        url = '/riso-proxy/UI/IE/NewUIpage/Page/' + url;
                    }
                }
                return originalFetch.call(this, url, options);
            };
            
            // Intercepter XMLHttpRequest
            const originalOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url) {
                var args = Array.prototype.slice.call(arguments, 2);
                if (typeof url === 'string' && !url.includes('/riso-proxy/') && !url.startsWith('http')) {
                    if (url.startsWith('/')) {
                        url = '/riso-proxy' + url;
                    } else {
                        url = '/riso-proxy/UI/IE/NewUIpage/Page/' + url;
                    }
                }
                
                // Intercepter les URLs de téléchargement
                if (url.includes('download') || url.includes('zip')) {
                    console.log('Téléchargement détecté:', url);
                    if (window.parent !== window) {
                        window.parent.postMessage({
                            type: 'download-url',
                            url: url,
                            scanName: 'SCAN-0540'
                        }, '*');
                    }
                }
                
                return originalOpen.apply(this, [method, url].concat(args));
            };
        })();
        
        // Extraire les scans et les envoyer au parent
        setTimeout(function() {
            try {
                var rows = document.querySelectorAll('table tr');
                var scans = [];
                
                rows.forEach(function(row) {
                    var cells = row.querySelectorAll('td');
                    if (cells.length >= 4) {
                        var name = cells[0].textContent.trim();
                        var owner = cells[1].textContent.trim();
                        var pages = cells[2].textContent.trim();
                        var date = cells[3].textContent.trim();
                        
                        // Vérifier que c'est un scan (contient SCAN-)
                        if (name && name.indexOf('SCAN-') >= 0 && name !== 'Nom de document') {
                            scans.push({name: name, owner: owner, pages: pages, date: date});
                        }
                    }
                });
                
                if (scans.length > 0 && window.parent !== window) {
                    window.parent.postMessage({type: 'scans-data', scans: scans}, '*');
                }
            } catch(e) {
                console.error('Erreur extraction scans:', e);
            }
        }, 3000);
        
        // Intercepter les téléchargements créés
        var originalCreateScanJobZip = window.createScanJobZip;
        if (typeof createScanJobZip === 'function') {
            window.createScanJobZip = function() {
                console.log('createScanJobZip appelé');
                var result = originalCreateScanJobZip.apply(this, arguments);
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: 'zip-created',
                        message: 'Le fichier ZIP est prêt pour téléchargement'
                    }, '*');
                }
                return result;
            };
        }
        </script>";
        
        // Injecter le script au tout début du head pour qu'il s'exécute en premier
        if (preg_match('/<head>/i', $body)) {
            $body = preg_replace('/<head>/i', '<head>' . $fix_script, $body);
        } elseif (preg_match('/<\/head>/i', $body)) {
            $body = preg_replace('/<\/head>/i', $fix_script . '</head>', $body);
        } else {
            // Fallback : injecter au début du body
            $body = str_replace('<body', $fix_script . '<body', $body);
        }
        
        // Réécrire les src des scripts externes uniquement
        $body = preg_replace_callback(
            '/<script([^>]*?src=["\'])([^"\']+)(["\'][^>]*?)><\/script>/is',
            function($matches) {
                $before = $matches[1];
                $src = $matches[2];
                $after = $matches[3];
                
                // Ne pas modifier si déjà dans riso-proxy ou externe
                if (strpos($src, '/riso-proxy/') !== false || strpos($src, 'http') !== false) {
                    return $matches[0];
                }
                
                // Réécrire le src
                if ($src[0] === '/') {
                    $newSrc = '/riso-proxy' . $src;
                } else {
                    $newSrc = '/riso-proxy/UI/IE/NewUIpage/Page/' . $src;
                }
                
                return '<script' . $before . $newSrc . $after . '></script>';
            },
            $body
        );
    }

echo $body;
?>
