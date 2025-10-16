<?php
// Simple debug accessible via web
require_once '../controler/functions/database.php';
require_once '../controler/functions/consommation.php';

echo "<h1>Debug Duplicopieurs</h1>";

$db = pdo_connect();

echo "<h2>1. Duplicopieurs dans la base :</h2>";
$query = $db->query('SELECT id, marque, modele FROM duplicopieurs WHERE actif = 1');
while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
    $nom_complet = $result['marque'];
    if ($result['marque'] !== $result['modele']) {
        $nom_complet = $result['marque'] . ' ' . $result['modele'];
    }
    echo "- ID: " . $result['id'] . " | Nom: " . htmlspecialchars($nom_complet) . "<br>";
}

echo "<h2>2. Machines dans cons :</h2>";
$query = $db->query('SELECT DISTINCT machine FROM cons ORDER BY machine');
while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
    echo "- " . htmlspecialchars($result['machine']) . "<br>";
}

echo "<h2>3. Test get_cons('dupli') :</h2>";
$test_dupli = get_cons('dupli');
echo "<pre>";
print_r($test_dupli);
echo "</pre>";

echo "<h2>4. Test get_cons('Duplicopieur') :</h2>";
$test_duplicopieur = get_cons('Duplicopieur');
echo "<pre>";
print_r($test_duplicopieur);
echo "</pre>";
?>