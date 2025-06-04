<?php
$host = 'localhost';
$username = 'usersauv';
$password = 'S@uvBaseosix25*';
$database = 'osix';
$fileName = "database_dump_" . date("Y-m-d_H-i-s") . ".sql";
$port = '3367';

$conn = new mysqli($host, $username, $password, $database, $port);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$tables = [];
$query = $conn->query("SHOW TABLES");
while ($row = $query->fetch_row()) {
    $tables[] = $row[0];
}

$sqlDump = "-- Dump de la base de données '$database'\n";
$sqlDump .= "-- Généré le " . date("Y-m-d H:i:s") . "\n\n";

foreach ($tables as $table) {
    // Obtenir la définition de la table
    $createTableQuery = $conn->query("SHOW CREATE TABLE `$table`")->fetch_assoc();
    $sqlDump .= "-- Structure de la table `$table`\n";
    $sqlDump .= $createTableQuery['Create Table'] . ";\n\n";

    // Obtenir les données de la table
    $rows = $conn->query("SELECT * FROM `$table`");
    if ($rows->num_rows > 0) {
        $sqlDump .= "-- Données de la table `$table`\n";

        while ($row = $rows->fetch_assoc()) {
            $values = [];
            foreach ($row as $value) {
                // Gérer les valeurs NULL et les échapper correctement
                if (is_null($value)) {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . $conn->real_escape_string($value) . "'";
                }
            }
            // Construire la requête INSERT
            $sqlDump .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
        }
    }
    $sqlDump .= "\n\n";
}

// Écrire dans un fichier
file_put_contents($fileName, $sqlDump);

// Envoyer le fichier et le supprimer après téléchargement
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
readfile($fileName);
unlink($fileName);

exit;