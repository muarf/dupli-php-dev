<?php
/**
 * Téléchargement direct des scans depuis la console RISO
 */

// Récupérer le nom du scan
$scanName = isset($_GET['scan']) ? urldecode($_GET['scan']) : '';

if (empty($scanName)) {
    header('HTTP/1.1 400 Bad Request');
    die('Nom de scan manquant');
}

error_log("Téléchargement demandé pour: $scanName");

// URL du backend RISO
$base_url = 'http://localhost:8023';

// Session RISO
$riso_session = '9761c806fbc167e5ad64203893061650';

// D'abord, récupérer l'ID du scan via la liste
$list_url = $base_url . '/Backend/APP/UI_6_3_6_1_list.app';

$ch = curl_init($list_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'type' => 's',
    'currentPage' => '1',
    'currentRows' => '100',
    'sortParse' => '',
    'sortOrder' => '',
    'loginUserRole' => '1',
    'loginUserName' => '',
    'loginUserID' => '',
    'searchCondition' => ''
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=$riso_session");
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

error_log("Liste des scans: HTTP $http_code");

if (!$response || $http_code != 200) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Erreur lors de la récupération de la liste');
}

// Parser le XML
$xml = @simplexml_load_string($response);
if (!$xml) {
    error_log("Impossible de parser le XML. Réponse: " . substr($response, 0, 500));
    header('HTTP/1.1 500 Internal Server Error');
    die('Erreur de parsing XML');
}

// Chercher le scan
$jobID = null;
$nodes = $xml->xpath('//job_name[contains(text(), "' . $scanName . '")]');

if (empty($nodes)) {
    error_log("Scan '$scanName' non trouvé");
    header('HTTP/1.1 404 Not Found');
    die('Scan non trouvé');
}

// Récupérer le job_id
$jobName = $nodes[0];
$parent = $jobName->xpath('..');
if (!empty($parent)) {
    $jobID = (string)$parent[0]->job_id;
}

if (!$jobID) {
    error_log("Impossible de récupérer l'ID du scan");
    header('HTTP/1.1 500 Internal Server Error');
    die('Erreur lors de la récupération de l\'ID du scan');
}

error_log("ID du scan trouvé: $jobID");

// Maintenant télécharger le scan
$download_url = $base_url . '/Backend/APP/UI_6_3_6_1_download.app';

$ch2 = curl_init($download_url);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query([
    'type' => 'download',
    'jobID' => $jobID,
    'jobName' => $scanName
]));
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIE, "PHPSESSID=$riso_session");
curl_setopt($ch2, CURLOPT_TIMEOUT, 60);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);

$pdf_content = curl_exec($ch2);
$pdf_http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch2, CURLINFO_CONTENT_TYPE);
curl_close($ch2);

error_log("Téléchargement: HTTP $pdf_http_code, Content-Type: $content_type, Size: " . strlen($pdf_content));

if ($pdf_content && $pdf_http_code == 200) {
    // Vérifier si c'est un PDF ou un HTML d'erreur
    if (strpos($pdf_content, '%PDF') === 0 || strpos($content_type, 'application/pdf') !== false) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . htmlspecialchars($scanName) . '.pdf"');
        header('Content-Length: ' . strlen($pdf_content));
        echo $pdf_content;
        exit;
    } else {
        error_log("Ce n'est pas un PDF. Preview: " . substr($pdf_content, 0, 200));
        header('HTTP/1.1 500 Internal Server Error');
        die('Le fichier téléchargé n\'est pas un PDF valide');
    }
} else {
    error_log("Erreur lors du téléchargement. HTTP: $pdf_http_code");
    header('HTTP/1.1 500 Internal Server Error');
    die('Erreur lors du téléchargement du scan');
}
?>
