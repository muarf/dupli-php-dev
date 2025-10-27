<?php
// Endpoint pour télécharger directement un scan PDF depuis la console RISO
header('Content-Type: application/pdf');

$jobName = $_GET['jobName'] ?? '';
if (empty($jobName)) {
    http_response_code(400);
    die('Nom de scan requis');
}

// L'IP de la console RISO
$base_url = 'http://192.168.1.110';

// Recréer la session avec les mêmes cookies
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/Backend/APP/UI_6_3_6_1_download.app?jobName=' . urlencode($jobName));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'jobName=' . urlencode($jobName));

// Transmettre les cookies du navigateur
if (!empty($_SERVER['HTTP_COOKIE'])) {
    curl_setopt($ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE']);
}

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode === 200 && !empty($response)) {
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    if (!empty($contentType)) {
        header('Content-Type: ' . $contentType);
    }
    
    $fileName = $jobName . '.pdf';
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    echo $response;
} else {
    http_response_code($httpCode ?: 500);
    echo 'Erreur lors du téléchargement';
}

curl_close($ch);
?>

