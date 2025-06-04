<?php

// Démarrer la session et connecter à la base de données
session_start();

try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Fonction pour générer une liste de tags, adaptée à la table sources
function generate_tags_list_sources($tags, $bdd) {
    $result = '';
    if (empty(json_decode($tags))) {
        return '<span style="color: grey; text-transform: uppercase; font-size: small;">Aucun tag</span>';
    }
    $tags_list = json_decode($tags, true);
    foreach ($tags_list as $tag) {
        $stmt = $bdd->prepare('SELECT name FROM sourcestags WHERE id = :tag_id');
        $stmt->execute(['tag_id' => $tag]);
        $tag_name = $stmt->fetch();
        $tag_name = $tag_name['name'];
        if ($result != '') {
            $result .= ', ';
        }
        $result .= '<button type="button" class="source-tag" onclick="">#' . htmlspecialchars($tag_name) . '</button>';
    }
    return $result;
}

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lire les données JSON envoyées par la requête AJAX
    $input = json_decode(file_get_contents('php://input'), true);

    // Initialiser les variables et les filtres
    $searchText = isset($input['searchText']) ? $input['searchText'] : '';
    $selected_tags = isset($input['selected_tags']) ? $input['selected_tags'] : [];
    $sort_selection = isset($input['sort_selection']) ? $input['sort_selection'] : 'name-asc';

    // Construire la requête SQL principale pour la table "sources"
    $query = "SELECT *, date_format(date,'%d/%m/%Y')  AS date_formatted, date_format(lastuse,'%d/%m/%Y')  AS lastuse_formatted FROM sources WHERE (1=1)";
    $params = [];

    // Ajouter un filtre de recherche textuel
    if (!empty($searchText)) {
        $query .= " AND (sources.name LIKE :searchText OR sources.doc LIKE :searchText)";
        $params['searchText'] = '%' . $searchText . '%';
    }

    // Ajouter un filtre basé sur les tags sélectionnés
    if (!empty($selected_tags)) {
        $query .= " AND (";
        foreach ($selected_tags as $index => $tag) {
            $query .= ($index > 0 ? " OR " : "") . "JSON_CONTAINS(sources.tags, '[$tag]')";
        }
        $query .= ")";
    }

    // Ajouter l'ordre de tri
    switch ($sort_selection) {
        case 'name-asc':
            $query .= " ORDER BY name ASC";
            break;
        case 'name-desc':
            $query .= " ORDER BY name DESC";
            break;
        case 'date-asc':
            $query .= " ORDER BY date ASC";
            break;
        case 'date-desc':
            $query .= " ORDER BY date DESC";
            break;
        case 'lastuse-asc':
            $query .= " ORDER BY lastuse ASC";
            break;
        case 'lastuse-desc':
            $query .= " ORDER BY lastuse DESC";
            break;
        default:
            $query .= " ORDER BY name ASC";
    }
    // Exécuter la requête
    try {
        $stmt = $bdd->prepare('SET lc_time_names = "fr_FR";'); // Configurer les noms des dates en français
        $stmt->execute();

        $stmt = $bdd->prepare($query);
        $stmt->execute($params);

        // Récupérer les résultats
        $results = $stmt->fetchAll();

        // Vérifier s'il y a des résultats
        if ($results) {
            foreach ($results as $source) {
                $sourceImage = 'Fav_Icons/Default_Source.png'; // Chemin par défaut
                $stmt = $bdd->prepare("SELECT links FROM favoritelinks WHERE user_id = :user_id");
                $stmt->execute(['user_id' => $_SESSION['id_actif']]);
                $favLink = $stmt->fetchColumn();

                if ($favLink) {
                    $links = json_decode($favLink, true);
                    foreach ($links as $link) {
                        if ($link['source'] == $source['id']) {
                            $sourceImage = $links[$source['id']]['image'];
                        }
                    }
                }
                $stmt = $bdd->prepare("SELECT parent, name FROM sourcetypes WHERE id = :source_type_id");
                $stmt->execute(['source_type_id' => $source['type']]);
                $sourceType = $stmt->fetch();
                $sourceType = $sourceType['parent'] == 0 ? $sourceType['name'] : $sourceType['parent'] . ' / ' . $sourceType['name'];
                $source_cotation_grade = $source['cotation'] != "" && isset(json_decode($source['cotation'])->grade) ? " | " . json_decode($source['cotation'])->grade : "";
                $FollowButton = in_array($_SESSION['id_actif'], json_decode($source['followers'])) ? '<button class="follow-button" onclick="Unfollow_Source(' . $source['id'] . ', ' . $_SESSION['id_actif'] . ')"><img src="Includes\Images\Icone%20moins.png" height="15px"></button>' : '<button class="follow-button" onclick="Follow_Source(' . $source['id'] . ', ' . $_SESSION['id_actif'] . ')"><img src="Includes\Images\Icone Plus.png" height="15px"></button>' ;
                $DetailsButton = '<button id="details-source-button" class="edit-icon" onclick="document.getElementById(\'gest-popup-'. $source['id'] .').style.display = \'inline-block\'" onblur="document.getElementById(\'gest-popup'. $source['id'] .').style.display = \'none\'">
                                            <img src="Includes/Images/Loupe.png"> Voir les détails
                </button>';
                $cotationDetails = $source['cotation'] != "" && isset(json_decode($source['cotation'])->eval_status) ? (json_decode(json_decode($source['cotation'])->eval_status == "A évaluer") ? 'Demande de cotation par '.json_decode($source['cotation'])->demand_issuer.' le '.date("d/m/Y", strtotime(json_decode($source['cotation'])->demand_date)).'. ' : "") . ($source_cotation_grade != "" ? 'Source cotée le '.date( 'd/m/Y', strtotime(json_decode($source['cotation'])->date)).' pour '.json_decode($source['cotation'])->useframe.' avec la note de '.json_decode($source['cotation'])->grade.'.'  : "") : "SOURCE NON COTEE";
                echo '<tr>';
                echo '<td class="image_column"><img src="' . htmlspecialchars($sourceImage) . '" alt="' . htmlspecialchars($source['name']) . '" width="40px" height="40px" style="border-radius: 10px"></td>';
                echo '<td style="font-weight: bold">' . htmlspecialchars($source['name']) . $source_cotation_grade . $FollowButton . $DetailsButton .'</td>';
                echo '<td>' . htmlspecialchars($sourceType) . '</td>';
                echo '<td style="max-width: 15vw">' . htmlspecialchars($source['doc']) . '</td>';
                echo '<td>' . generate_tags_list_sources($source['tags'], $bdd) . '</td>';
                echo '<td class="url-column"><a href="' . htmlspecialchars($source['url']) . '" target="_blank">' . htmlspecialchars($source['url']) . '</a></td>';
                echo '<td>' . $cotationDetails . '</td>';
                echo '<td>' . htmlspecialchars($source['date_formatted']) . '</td>';
                echo '<td>' . htmlspecialchars($source['lastuse_formatted']) . '</td>';
                echo '</tr>';
            }
        } else {
            echo "<tr><td colspan='5'>Aucune source trouvée.</td></tr>";
        }
    } catch (Exception $e) {
        echo 'Erreur de requête : ' . $e->getMessage();
    }
} else {
    echo "Requête invalide.";
}

?>
