<?php

// Fonction pour extraire les liens d'une liste et générer un fichier XLS
function generateXLSFromList($list_id, $bdd) {
    // Nom du fichier XLS à télécharger
    $fileName = 'Extraction_Liste_' . $list_id . '.xls';

    // Définir les en-têtes HTTP pour le téléchargement
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Démarrage du contenu XLS
    echo '<table border="1">';
    echo '<tr style="font-weight: bold; background-color: #f2f2f2;">';
    echo '<th>Liste d\'appartenance</th>';
    echo '<th>Nom du lien</th>';
    echo '<th>Type du lien</th>';
    echo '<th>URL du lien</th>';
    echo '<th>Date d\'ajout</th>';
    echo '</tr>';

    // Récupérer les informations sur la liste
    $query = $bdd->prepare('SELECT * FROM sourcelists WHERE id = :list_id');
    $query->execute(['list_id' => $list_id]);
    $list = $query->fetch();

    if (!$list) {
        die('<tr><td colspan="5">Liste introuvable.</td></tr></table>');
    }

    $list_name = $list['name'];
    $links = json_decode($list['links'], true); // Décoder les liens JSON

    // Ajouter les informations des liens dans le fichier XLS
    foreach ($links as $link_id) {
        // Récupérer les détails de chaque lien
        $linkQuery = $bdd->prepare('SELECT * FROM links WHERE id = :link_id');
        $linkQuery->execute(['link_id' => $link_id]);
        $link = $linkQuery->fetch();
        // Récupérer le type du lien
        $linkType = $bdd->query('SELECT name FROM osix.sourcetypes WHERE id = '.$link['type'])->fetchColumn();

        if ($link) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($list_name) . '</td>';        // Liste d'appartenance
            echo '<td>' . htmlspecialchars($link['name']) . '</td>';     // Nom du lien
            echo '<td>' . htmlspecialchars($linkType ?? 'Indéfini') . '</td>'; // Type du lien
            echo '<td>' . htmlspecialchars($link['url']) . '</td>';      // URL du lien
            echo '<td>' . htmlspecialchars($link['date']) . '</td>';     // Date d'ajout du lien
            echo '</tr>';
        }
    }

    // Fin du tableau
    echo '</table>';
    exit;
}

// Vérifier si une demande d'extraction est faite
if (isset($_GET['extract_list_id'])) {
    $list_id = intval($_GET['extract_list_id']);
    try {
        // Connexion à la base de données
        $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
        generateXLSFromList($list_id, $bdd);
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }
}
?>