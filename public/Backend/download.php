<?php
/**
 * Téléchargement direct des scans PDF depuis le backend RISO
 */

// Récupérer le nom du scan
$jobName = isset($_GET['jobName']) ? urldecode($_GET['jobName']) : '';

if (empty($jobName)) {
    http_response_code(400);
    die('Nom de scan manquant');
}

// URL du backend RISO
$base_url = 'http://localhost:8023';
$target_url = $base_url . '/Backend/scan_download.php';

// Session RISO
$riso_session = '9761c806fbc167e5ad64203893061650';

// Télécharger le scan avec une requête GET simple
$ch = curl_init($target_url . '?jobName=' . urlencode($jobName));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=$riso_session");
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$pdf_content = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($pdf_content && $http_code == 200) {
    // Vérifier si c'est un PDF
    if (strpos($pdf_content, '%PDF') === 0 || strlen($pdf_content) > 1000) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . htmlspecialchars($jobName) . '.pdf"');
        header('Content-Length: ' . strlen($pdf_content));
        echo $pdf_content;
        exit;
    }
}

// Si ça ne fonctionne pas, essayer un autre endpoint
$ch2 = curl_init($base_url . '/Backend/APP/UI_6_3_6_1_download.app?jobName=' . urlencode($jobName));
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIE, "PHPSESSID=$riso_session");
curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);

$pdf_content = curl_exec($ch2);
$http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

if ($pdf_content && $http_code == 200 && strlen($pdf_content) > 1000) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . htmlspecialchars($jobName) . '.pdf"');
    header('Content-Length: ' . strlen($pdf_content));
    echo $pdf_content;
    exit;
}

// Redirection vers la console RISO en dernier recours
header('Location: http://localhost:8023/UI/IE/NewUIpage/Page/RC_Scan.phtml');
exit;
?>

