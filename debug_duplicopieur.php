<?php
// Script de debug temporaire pour diagnostiquer le problème des duplicopieurs
require_once __DIR__ . '/controler/functions/database.php';

$db = pdo_connect();

echo "<h2>🔍 Debug Duplicopieurs - Données dans la base</h2>";

echo "<h3>1. Duplicopieurs actifs dans la table :</h3>";
$query = $db->query('SELECT id, marque, modele FROM duplicopieurs WHERE actif = 1');
while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
    $nom_complet = $result['marque'];
    if ($result['marque'] !== $result['modele']) {
        $nom_complet = $result['marque'] . ' ' . $result['modele'];
    }
    echo "- ID: " . $result['id'] . " | Nom: " . $nom_complet . " | strtolower: " . strtolower($nom_complet) . "<br>";
}

echo "<h3>2. Machines distinctes dans la table cons :</h3>";
$query = $db->query('SELECT DISTINCT machine, COUNT(*) as count FROM cons GROUP BY machine ORDER BY machine');
while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
    echo "- Machine: '" . $result['machine'] . "' (" . $result['count'] . " enregistrements)<br>";
}

echo "<h3>3. Types de changements dans cons :</h3>";
$query = $db->query('SELECT machine, type, COUNT(*) as count FROM cons GROUP BY machine, type ORDER BY machine, type');
while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
    echo "- Machine: '" . $result['machine'] . "' | Type: '" . $result['type'] . "' | Count: " . $result['count'] . "<br>";
}

echo "<h3>4. Test de la fonction get_cons('dupli') :</h3>";
require_once __DIR__ . '/controler/functions/consommation.php';
$old_result = get_cons('dupli');
echo "<pre>";
print_r($old_result);
echo "</pre>";

echo "<h3>5. Test des variantes de noms :</h3>";
$variants = ['Duplicopieur', 'duplicopieur', 'dupli', 'a3', 'a4'];
foreach ($variants as $variant) {
    $query = $db->prepare('SELECT COUNT(*) as count FROM cons WHERE machine = ?');
    $query->execute([$variant]);
    $count = $query->fetchColumn();
    echo "- Variant '" . $variant . "' : " . $count . " résultats<br>";
}

echo "<h3>6. Derniers 10 enregistrements dans cons :</h3>";
echo "<table border='1'><tr><th>Machine</th><th>Type</th><th>Date</th><th>NB_P</th><th>NB_M</th><th>Tambour</th></tr>";
$query = $db->query('SELECT * FROM cons ORDER BY date DESC LIMIT 10');
while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($result['machine']) . "</td>";
    echo "<td>" . htmlspecialchars($result['type']) . "</td>";
    echo "<td>" . date('Y-m-d H:i', $result['date']) . "</td>";
    echo "<td>" . $result['nb_p'] . "</td>";
    echo "<td>" . $result['nb_m'] . "</td>";
    echo "<td>" . htmlspecialchars($result['tambour'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>