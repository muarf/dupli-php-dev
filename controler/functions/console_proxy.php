<?php
/**
 * Proxy pour les consoles machines
 * Contourne les problèmes CORS et parse le contenu
 */

require_once __DIR__ . '/../functions/database.php';

/**
 * Effectuer une requête proxy vers une console
 */
function proxyConsoleRequest($url, $session_data = null) {
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    // Gérer les cookies de session si fournis
    if ($session_data && isset($session_data['cookies'])) {
        curl_setopt($ch, CURLOPT_COOKIE, $session_data['cookies']);
    }
    
    // Gérer les headers
    $headers = [];
    if ($session_data && isset($session_data['headers'])) {
        $headers = array_merge($headers, $session_data['headers']);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error];
    }
    
    return [
        'content' => $response,
        'http_code' => $httpCode
    ];
}

/**
 * Parser la page RC_Scan.phtml pour extraire les scans
 */
function parseScansFromRISO($html) {
    $scans = [];
    
    try {
        // Utiliser DOMDocument pour parser le HTML
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Dans la console RISO, les scans sont dans des lignes de tableau
        // Chercher toutes les lignes de données
        $rows = $xpath->query("//tr[td[@id='id_img_name']]");
        
        foreach ($rows as $row) {
            $cells = $xpath->query("./td", $row);
            
            if ($cells->length >= 4) {
                $nameNode = $cells->item(0);
                $ownerNode = $cells->item(1);
                $pagesNode = $cells->item(2);
                $dateNode = $cells->item(3);
                
                if ($nameNode && $ownerNode && $pagesNode && $dateNode) {
                    $name = trim($nameNode->textContent);
                    $owner = trim($ownerNode->textContent);
                    $pages = trim($pagesNode->textContent);
                    $date = trim($dateNode->textContent);
                    
                    // Nettoyer le nom (enlever le logo etc.)
                    $name = preg_replace('/^(logo)?\s*/i', '', $name);
                    
                    if (!empty($name)) {
                        $scans[] = [
                            'name' => $name,
                            'owner' => $owner,
                            'pages' => $pages,
                            'date' => $date
                        ];
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        error_log("Erreur parsing RISO: " . $e->getMessage());
    }
    
    return $scans;
}

/**
 * Obtenir les scans d'une console
 */
function getConsoleScans($wrapper_id) {
    try {
        $db = pdo_connect();
        $stmt = $db->prepare("SELECT * FROM machine_console_wrappers WHERE id = ?");
        $stmt->execute([$wrapper_id]);
        $wrapper = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$wrapper) {
            return ['error' => 'Wrapper non trouvé'];
        }
        
        // Récupérer les scans via le backend de la console RISO
        $baseUrl = rtrim($wrapper['console_url'], '/');
        $scans = [];
        
        if ($wrapper['console_type'] === 'riso_comcolor') {
            // Appeler le backend RISO directement pour récupérer les scans
            $backendUrl = $baseUrl . '/Backend/APP/UI_6_3_6_1_list.app';
            
            // Données POST pour récupérer les scans
            $postData = http_build_query([
                'type' => 's',
                'currentPage' => '1',
                'currentRows' => '10',
                'sortParse' => '',
                'sortOrder' => '',
                'loginUserRole' => '1',
                'loginUserName' => '',
                'loginUserID' => '',
                'searchCondition' => ''
            ]);
            
            // Faire la requête POST vers le backend
            $ch = curl_init($backendUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($response && $httpCode == 200) {
                // Parser le XML de réponse
                $xml = simplexml_load_string($response);
                if ($xml) {
                    foreach ($xml->xpath('//job_id') as $job) {
                        $scan = [
                            'name' => (string)$job->job_name,
                            'owner' => (string)$job->owner_name,
                            'pages' => (string)$job->page_count,
                            'date' => (string)$job->date
                        ];
                        $scans[] = $scan;
                    }
                }
            }
        }
        
        return ['scans' => $scans];
        
    } catch (Exception $e) {
        return ['error' => 'Erreur : ' . $e->getMessage()];
    }
}

/**
 * Télécharger un scan depuis une console
 */
function downloadConsoleScan($wrapper_id, $scan_name) {
    try {
        $db = pdo_connect();
        $stmt = $db->prepare("SELECT * FROM machine_console_wrappers WHERE id = ?");
        $stmt->execute([$wrapper_id]);
        $wrapper = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$wrapper) {
            return ['error' => 'Wrapper non trouvé'];
        }
        
        // Construire l'URL de téléchargement
        // La structure exacte dépendra de l'API de la console
        $baseUrl = rtrim($wrapper['console_url'], '/');
        $downloadUrl = $baseUrl . '/download/' . urlencode($scan_name);
        
        // Proxy la requête de téléchargement
        $result = proxyConsoleRequest($downloadUrl);
        
        if (isset($result['error'])) {
            return $result;
        }
        
        return [
            'success' => true,
            'content' => $result['content'],
            'filename' => $scan_name
        ];
        
    } catch (Exception $e) {
        return ['error' => 'Erreur : ' . $e->getMessage()];
    }
}

/**
 * Authentifier auprès d'une console
 */
function authenticateConsole($wrapper_id, $username, $password) {
    try {
        $db = pdo_connect();
        $stmt = $db->prepare("SELECT * FROM machine_console_wrappers WHERE id = ?");
        $stmt->execute([$wrapper_id]);
        $wrapper = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$wrapper) {
            return ['error' => 'Wrapper non trouvé'];
        }
        
        $baseUrl = rtrim($wrapper['console_url'], '/');
        $loginUrl = $baseUrl . '/'; // URL de login
        
        // Données de login
        $postData = [
            'username' => $username,
            'password' => $password
        ];
        
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, sys_get_temp_dir() . '/console_cookies_' . $wrapper_id . '.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, sys_get_temp_dir() . '/console_cookies_' . $wrapper_id . '.txt');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            return ['success' => true, 'session' => 'cookie file'];
        } else {
            return ['error' => 'Échec de l\'authentification'];
        }
        
    } catch (Exception $e) {
        return ['error' => 'Erreur : ' . $e->getMessage()];
    }
}
?>

