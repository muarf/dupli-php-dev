<?php
// Debug simple du proxy RISO
$request_uri = $_SERVER['REQUEST_URI'];
$path = str_replace('/riso-proxy/', '', $request_uri);

echo "<h1>Debug Proxy RISO</h1>";
echo "<p><strong>REQUEST_URI:</strong> $request_uri</p>";
echo "<p><strong>Path:</strong> $path</p>";
echo "<p><strong>Target URL:</strong> http://localhost:8023/$path</p>";

// Test de connexion
$ch = curl_init("http://localhost:8023/$path");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>";
if ($error) {
    echo "<p><strong>Erreur cURL:</strong> $error</p>";
}

// Test du tunnel SSH
echo "<h2>Test tunnel SSH</h2>";
$ch = curl_init("http://localhost:8023/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_NOBODY, true);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>Test tunnel (/):</strong> HTTP $http_code</p>";
if ($error) {
    echo "<p><strong>Erreur tunnel:</strong> $error</p>";
}
?>
