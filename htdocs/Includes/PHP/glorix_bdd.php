<?php
session_start();

try {
    // On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$is_admin = isset($_SESSION['is_admin']) ? intval($_SESSION['is_admin']) : 0;


function color_cotation($cotation) {
    // Vérification du format de la cotation : une lettre entre A et F suivie d'un chiffre entre 1 et 6
    if (!preg_match('/^[a-fA-F][1-6]$/', $cotation)) {
        // Si le format n'est pas valide, on retourne la valeur telle quelle
        return $cotation;
    }

    // Mise en majuscule de la lettre
    $lettre = strtoupper($cotation[0]);
    $chiffre = $cotation[1];

    // Définition de la couleur selon le critère
    if ($lettre === 'F' || $chiffre === '6') {
        $couleur = 'blue';
    } elseif ($lettre === 'E' || $chiffre === '5') {
        $couleur = 'red';
    } elseif ($lettre === 'A' || $chiffre === '1') {
        $couleur = 'green';
    } else {
        // Calcul d'une échelle intermédiaire entre le rouge (E ou 5) et le vert (A ou 1)
        $ecartLettre = ord('E') - ord($lettre); // e.g. E->0, D->1, C->2...
        $ecartChiffre = $chiffre - 5;          // e.g. Chiffre 5->0, 4->-1...

        // Conversion en proportion pour calculer un dégradé
        $rouge = max(0, 255 - (64 * $ecartLettre)); // Moins la valeur s'éloigne de E, plus le rouge diminue
        $vert = max(0, 255 + (64 * $ecartChiffre)); // Plus la valeur se rapproche de A ou 1, plus le vert augmente

        $couleur = sprintf('rgb(%d, %d, 0)', $rouge, $vert);
    }

    // Retourne la cotation formatée avec la couleur
    return "<span style='color: $couleur;'>$lettre$chiffre</span>";
}

if (isset($_POST['key']) && $_POST['key'] === 'demand_cotation') {
    // Récupération des données POST
    $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $use_area = isset($_POST['use_area']) ? trim($_POST['use_area']) : '';
    $suggestion = isset($_POST['suggestion']) ? trim($_POST['suggestion']) : 'N/S';
    $group = isset($_POST['group']) ? trim($_POST['group']) : '';

    // Valider les données
    if (!$source_id || !$user_id || !$message || !$use_area) {
        echo 'Données insuffisantes pour traiter la demande.';
        exit;
    }

    $req = $bdd->prepare('SELECT * FROM sources WHERE id = :source_id');
    $req->execute(['source_id' => $source_id]);
    $source = $req->fetch(PDO::FETCH_ASSOC);

    try {
        // Démarrer une transaction pour garantir l'intégrité des données
        $bdd->beginTransaction();

        // Étape 1 : Ajouter une demande dans `news_admin`
        $notification = "
            <h2>" . $source['name'] . " - Demande de COTATION </h2><br><br><br>
            <b>Nom de la source :</b> " . $source['name'] . "<br><br>
            <b>Groupe émettant la demande :</b> " . $group . "<br><br>
            <b>Message :</b>  " . $message . "<br><br>
            <b>Cadre d'emploi :</b>  " . $use_area . "<br><br><br>
            <b>Suggestion :</b>  " . $suggestion . "<br><br><br>
            <button class='news-content-button' onclick='window.location.href = \"administration.php?make_cotation&source_id='.$source_id.'\" + " . $source_id . ";'> ->Procéder à la cotation</button>
            
        ";

        // Récupérer l'ancienne valeur de la colonne cotation
        $stmt = $bdd->prepare("SELECT cotation FROM sources WHERE id = :source_id");
        $stmt->execute(['source_id' => $source_id]);
        $old_cotation = $stmt->fetchColumn(); // La valeur actuelle de 'cotation'

        // Si la colonne contient des données au format JSON (ou similaire), vous pouvez les décoder
        if ($old_cotation) {
            $old_cotation_array = json_decode($old_cotation, true); // Tableau associé à l'ancienne cotation
        } else {
            $old_cotation_array = [];
        }

        // Exemple de mise à jour de certains champs uniquement
        $cotation = json_encode(array_merge($old_cotation_array, [
            'demand_date' => date('Y-m-d H:i:s'),
            'eval_status' => 'A évaluer',
            "demand_issuer" => $group,
            "demand_message" => $message,
            "demand_useframe" => $use_area,
        ]));


        $stmt = $bdd->prepare('INSERT INTO news (issuer, message, receiver, type) VALUES (:asking_user, :message, :receiver, :type)');
        $stmt->execute([
            'asking_user' => $user_id,
            'message' => $notification,
            'receiver' => '{"users": [], "groups": [1], "viewed": []}',
            'type' => 'ADMIN',
        ]);

        // Étape 2 : Modifier le statut de la source en "A évaluer" (id = 2)
        $stmt = $bdd->prepare('UPDATE sources SET status = 2, cotation = :cotation WHERE id = :source_id');
        $stmt->execute(['source_id' => $source_id, 'cotation' => $cotation]);

        // Valider la transaction
        $bdd->commit();

        echo 'OK';
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $bdd->rollBack();
        echo 'Erreur : ' . $e->getMessage();
    }
}

if (isset($_POST['key']) && $_POST['key'] === 'update_ranking') {
    echo 'OK';
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id']);
    $oldRank = intval($data['oldRank']);
    $newRank = intval($data['newRank']);

    // Démarre une transaction pour éviter les incohérences
    $bdd->beginTransaction();

    // Identifier l'élément qui échange sa place
    $stmt = $bdd->prepare('SELECT id FROM sources WHERE ranking = :newRank');
    $stmt->execute(['newRank' => $newRank]);
    $other = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$other) {
        throw new Exception('Élément à la nouvelle position introuvable.');
    }

    // Inverser les positions des deux éléments
    $update1 = $bdd->prepare('UPDATE sources SET ranking = :rank WHERE id = :id');
    $update1->execute(['rank' => $newRank, 'id' => $id]);

    $update2 = $bdd->prepare('UPDATE sources SET ranking = :rank WHERE id = :id');
    $update2->execute(['rank' => $oldRank, 'id' => $other['id']]);

    // Valider la transaction
    $bdd->commit();
    function normalizeRankings($bdd) {
        // Lire les sources par leur position actuelle (triées par ranking ASC)
        $stmt = $bdd->prepare('SELECT id FROM sources WHERE ranking IS NOT NULL ORDER BY ranking ASC');
        $stmt->execute();
        $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Réassigner des valeurs consécutives à chaque ligne
        $newRanking = 1;
        $updateStmt = $bdd->prepare('UPDATE sources SET ranking = :ranking WHERE id = :id');

        foreach ($sources as $source) {
            $updateStmt->execute([
                'ranking' => $newRanking,
                'id' => $source['id'],
            ]);
            $newRanking++;
        }
    }
    normalizeRankings($bdd);
    echo 'OK';

}

if (isset($_POST['key']) && $_POST['key'] === 'erase_source') {
    header('Content-Type: application/json');
    // Vérifier si l'ID de la source est passé en POST
    if (!isset($_POST['source_id'])) {
        echo 'Aucun identifiant de source fourni';
        exit;
    }

    $source_id = intval($_POST['source_id']);

    // Récupérer la colonne followers de la source spécifiée
    $stmt = $bdd->prepare("SELECT followers FROM sources WHERE id = :source_id");
    $stmt->execute(['source_id' => $source_id]);

    $followers = $stmt->fetch(PDO::FETCH_ASSOC)['followers'];

    if (!$followers) {
        echo json_encode(['success' => false, 'message' => 'Source introuvable']);
        exit;
    }

    // Décoder le tableau JSON des followers
    $followers = json_decode($followers, true);
    if (!is_array($followers)) {
        echo json_encode(['success' => false, 'message' => 'Données followers corrompues']);
        exit;
    }

    // Retirer l'identifiant de l'utilisateur du tableau JSON
    if (($key = array_search($user_id, $followers)) !== false) {
        unset($followers[$key]); // Supprimer l'identifiant de l'utilisateur
        $followers = array_values($followers); // Réindexer le tableau

        // Mettre à jour la base de données avec le nouveau tableau JSON
        $stmt = $bdd->prepare("UPDATE sources SET followers = :followers WHERE id = :source_id");
        $success = $stmt->execute([
            'followers' => json_encode($followers),
            'source_id' => $source_id,
        ]);

        if ($success) {
            echo 'OK';
        } else {
            echo json_encode(['success' => false, 'message' => 'Échec de la mise à jour de la source']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé parmi les followers']);
    }
    exit;
}


if (isset($_POST['key']) && $_POST['key'] === 'generate_source_select') {
    // Décoder les IDs des correspondances
    $ids = isset($_POST['ids']) ? json_decode($_POST['ids'], true) : [];
    $ids = is_array($ids) ? $ids : [];
    $bestMatch = (isset($_POST['best_match'])) ? $_POST['best_match'] : null;

    if ($bestMatch !== null && !in_array($bestMatch, $ids)) {
        $ids[] = (int)$bestMatch;
    }


// Générez dynamiquement les placeholders pour la requête
    $inPlaceholder = implode(',', array_fill(0, count($ids), '?'));

// Préparez la requête avec PDO
    $stmt = $bdd->prepare("SELECT id, name, url, cotation, 
    (CAST(SUBSTRING_INDEX(eval, ';', 1) AS DECIMAL(10,2)) / CAST(SUBSTRING_INDEX(eval, ';', -1) AS DECIMAL(10,2))) * 5 AS rating 
    FROM sources WHERE id IN ($inPlaceholder) ORDER BY name ASC");

// Exécutez la requête en passant chaque élément de $ids comme un paramètre
    $stmt->execute($ids);

// Récupérez les résultats
    $suggestedSources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération de toutes les autres sources (Toutes les sources)
    $stmt = $bdd->prepare("SELECT id, name, url, cotation, followers FROM sources ORDER BY name ASC");
    $stmt->execute();
    $allSources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Construction des options HTML
    $suggestedOptions = '';
    $allOptions = '';
    $myOptions = '';
    $selectedUrl = '';

    if (!empty($suggestedSources)) {
        foreach ($suggestedSources as $source) {
            $selection = ($source['id'] == $bestMatch) ? 'selected' : '';
            if ($selection) {
                $selectedUrl = $source['url'];
            }
            if (isset($source['cotation'])) {
                $cotation = json_decode($source['cotation'], true);
                if (isset($cotation['grade'])) {
                    $cotation = $cotation['grade'] . " " . date('d/m/Y', strtotime($cotation['date']));
                }
                else {
                    $cotation = "À COTER";
                }
            }
            else { $cotation = "NON COTEE";}
            $suggestedOptions .= '<option value="' . htmlspecialchars($source['id'], ENT_QUOTES) . '" data-link="' . htmlspecialchars($source['url'], ENT_QUOTES) . '" ' . $selection . '>' . htmlspecialchars($source['name'], ENT_QUOTES) . ' | '. $cotation.'</option>';
        }
        $suggestedOptions .= '<option value="0" data-link="">---Aucune source correspondante---</option>';
    }
    else {
        $suggestedOptions = '<option value="0" data-link="" selected>---Aucune source correspondante---</option>';
    }

    if (!empty($allSources)) {
        foreach ($allSources as $source) {
            echo $source['cotation'];
            if (isset($source['cotation'])) {
                $cotation = json_decode($source['cotation'], true);
                if (isset($cotation['grade'])) {
                    $cotation = $cotation['grade'] . " " . date('d/m/y', (int)$cotation['date']);
                }
                else {
                    $cotation = "À COTER";
                }
            }
            else { $cotation = "NON COTEE";}
            if (in_array($_SESSION['id_actif'], json_decode($source['followers']))) {
                $myOptions .= '<option value="' . htmlspecialchars($source['id'], ENT_QUOTES) . '" data-link="' . htmlspecialchars($source['url'], ENT_QUOTES) . '">' . htmlspecialchars($source['name'], ENT_QUOTES) . ' | ' . $cotation . '</option>';
            }
            else {
                $allOptions .= '<option value="' . htmlspecialchars($source['id'], ENT_QUOTES) . '" data-link="' . htmlspecialchars($source['url'], ENT_QUOTES) . '">' . htmlspecialchars($source['name'], ENT_QUOTES) . ' | ' . $cotation . '</option>';
            }
        }
    }

    // Publication
    echo '<option value="">-- Choisir une source --</option>
        <optgroup label="Sources suggérées">
            ' . $suggestedOptions . '
        </optgroup>
        <optgroup label="Mes sources">
            ' . $myOptions . '
        </optgroup>' .
        '<optgroup label="Autres sources">'.
        $allOptions .
        '</optgroup>';
}

if (isset($_POST['key']) && $_POST['key'] === 'generate_source_select_for_fav') {
    // Décoder les IDs des correspondances
    $ids = json_decode($_POST['ids'], true);
    $ids = is_array($ids) ? $ids : [];
    $bestMatch = (isset($_POST['best_match']) && is_array($_POST['best_match'])) ? $_POST['best_match'] : [];

    if ($bestMatch !== null && !in_array($bestMatch, $ids)) {
        $ids[] = $bestMatch;
    }

    // Escaper les IDs pour une requête SQL sécurisée
    $inPlaceholder = implode(',', array_fill(0, count($ids), '?'));

    // Récupération des informations des sources suggérées (Sources suggérées)
    $stmt = $bdd->prepare("SELECT id, name, url, cotation, (CAST(SUBSTRING_INDEX(eval, ';', 1) AS DECIMAL(10,2)) / CAST(SUBSTRING_INDEX(eval, ';', -1) AS DECIMAL(10,2))) * 5 AS rating 
    FROM sources WHERE id IN $inPlaceholder ORDER BY name ASC");
    $stmt->execute($ids);
    $suggestedSources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération de toutes les autres sources (Toutes les sources)
    $stmt = $bdd->prepare("SELECT id, name, url, cotation FROM sources ORDER BY name ASC");
    $stmt->execute();
    $allSources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Construction des options HTML
    $suggestedOptions = '';
    $allOptions = '';
    $selectedUrl = '';

    if (!empty($suggestedSources)) {
        foreach ($suggestedSources as $source) {
            $selection = ($source['id'] == $bestMatch) ? 'selected' : '';
            if ($selection) {
                $selectedUrl = $source['url'];
            }
            $suggestedOptions .= '<option value="' . htmlspecialchars($source['id'], ENT_QUOTES) . '" data-link="' . htmlspecialchars($source['url'], ENT_QUOTES) . '" ' . $selection . '>' . htmlspecialchars($source['name'], ENT_QUOTES) . '</option>';
        }
    }
    else {
        $suggestedOptions = '<option value="0" data-link="" selected>---Aucune source correspondante---</option>';
    }

    if (!empty($allSources)) {
        foreach ($allSources as $source) {
            if (in_array($_SESSION['id_actif'], json_decode($source['followers']))) {
                $myOptions .= '<option value="' . htmlspecialchars($source['id'], ENT_QUOTES) . '" data-link="' . htmlspecialchars($source['url'], ENT_QUOTES) . '">' . htmlspecialchars($source['name'], ENT_QUOTES) . ' | ' . $cotation . '</option>';
            }
            else {
                $allOptions .= '<option value="' . htmlspecialchars($source['id'], ENT_QUOTES) . '" data-link="' . htmlspecialchars($source['url'], ENT_QUOTES) . '">' . htmlspecialchars($source['name'], ENT_QUOTES) . ' | ' . $cotation . '</option>';
            }
        }
    }

    // Publication
    echo '<option value="">-- Choisir une source --</option>
            <optgroup label="Sources suggérées">
                ' . $suggestedOptions . '
            </optgroup>
            <optgroup label="Mes sources">
                ' . $myOptions . '
            </optgroup>' .
            '<optgroup label="Autres sources">'.
            $allOptions .
            '</optgroup>';
}

if (isset($_POST['key']) && $_POST['key'] === 'find_best_source') {
    header('Content-Type: application/json');

    $link_url = $_POST['link_url'] ?? '';
    $source_link = $_POST['source_link'] ?? '';

    if (empty($link_url) && empty($source_link)) {
        echo null;
    }

    // Récupérer toutes les sources
    $query = $bdd->query("SELECT id, url FROM sources");
    $sources = $query->fetchAll(PDO::FETCH_ASSOC);

    // Déterminer la meilleure source
    $bestMatch = null;
    $maxLength = 0;
    $idsWithSubstring = []; // Liste des IDs où $inputLink est un sous-mot

    foreach ($sources as $source) {
        $url = $source['url'];
        $inputLink = empty($source_link) ? $link_url : $source_link;

        // Calculer la longueur du sous-mot commun
        $commonLength = longestCommonSubstring($inputLink, $url);

        // Vérifier si une correspondance meilleure est trouvée
        if ($commonLength > $maxLength) {
            $maxLength = $commonLength;
            $bestMatch = $source['id'];
        }

        // Vérifier si $inputLink est un sous-mot de $url
        if (strpos($url, $inputLink) !== false) {
            $idsWithSubstring[] = $source['id'];
        }

    }
    // Éviter les doublons dans la liste des IDs où $inputLink est un sous-mot
    $idsWithSubstring = array_unique($idsWithSubstring);

    if ($maxLength <= 5) {
        $bestMatch = null;
        $idsWithSubstring = [];
    }
    // Résultat final
    $response = [
        'bestMatch' => $bestMatch,
        'bestMatchScore' => $maxLength,
        'substringMatches' => $idsWithSubstring
    ];

    echo json_encode($response);
    exit;
}

// Fonction utilitaire pour calculer le sous-mot le plus long commun
function longestCommonSubstring($str1, $str2) {
    $str1 = str_replace(['http://', 'https://'], '', $str1);
    $str2 = str_replace(['http://', 'https://'], '', $str2);
    $unwanted_expr= [".com", ".com/", "www.", ".ru", ".ru/", ".fr/", ".fr"];

    $len1 = strlen($str1);
    $len2 = strlen($str2);
    $maxLength = 0;

    $table = array_fill(0, $len1 + 1, array_fill(0, $len2 + 1, 0));

    for ($i = 1; $i <= $len1; $i++) {
        for ($j = 1; $j <= $len2; $j++) {
            if ($str1[$i - 1] === $str2[$j - 1] && !in_array($str1[$i - 1], $unwanted_expr)) {
                $table[$i][$j] = $table[$i - 1][$j - 1] + 1;
                $maxLength = max($maxLength, $table[$i][$j]);
            }
        }
    }

    return $maxLength;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key']) && $_POST['key'] === 'sort_sources') {

    function normalizeRankings($bdd) {
        // Lire les sources par leur position actuelle (triées par ranking ASC)
        $stmt = $bdd->prepare('SELECT id FROM sources WHERE ranking IS NOT NULL ORDER BY ranking ASC');
        $stmt->execute();
        $sources = $stmt->fetchAll();

        // Réassigner des valeurs consécutives à chaque ligne
        $newRanking = 1;
        $updateStmt = $bdd->prepare('UPDATE sources SET ranking = :ranking WHERE id = :id');

        foreach ($sources as $source) {
            $updateStmt->execute([
                'ranking' => $newRanking,
                'id' => $source['id'],
            ]);
            $newRanking++;
        }
    }
    function getMonthlyOpenings($sourceId, $bdd) {
        // Requête SQL pour compter les ouvertures par mois pour une source donnée
        $stmt = $bdd->prepare("
                                SELECT 
                                    DATE_FORMAT(date, '%Y-%m') as month, 
                                    COUNT(*) as openings 
                                FROM link_openings 
                                WHERE source = :source
                                GROUP BY month
                                ORDER BY month ASC
                            ");
        $stmt->execute(['source' => $sourceId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'month' => $result['month'], // Mois
                'openings' => $result['openings'], // Nombre d'ouvertures
            ];
        }
        return $data;
    }
    function getMonthlyAdditions($sourceId, $bdd) {
        // Étape 1 : Compter les ajouts par mois depuis la table "links"
        $stmt = $bdd->prepare("
        SELECT 
            DATE_FORMAT(date, '%Y-%m') as month, 
            COUNT(*) as additions 
        FROM links 
        WHERE source = :source
        GROUP BY month
        ORDER BY month ASC
        ");
        $stmt->execute(['source' => $sourceId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Étape 2 : Récupérer les dates JSON de la colonne "derivatives" dans la table "sources"
        $stmt = $bdd->prepare("SELECT derivatives FROM sources WHERE id = :source");
        $stmt->execute(['source' => $sourceId]);
        $derivatives = $stmt->fetchColumn(); // Récupérer la colonne "derivatives" (au format JSON)

        // Convertir le JSON en tableau PHP (sécurité contre les nulls)
        $decodedDerivatives = !empty($derivatives) ? json_decode($derivatives, true) : [];

        // Étape 3 : Parcourir les dates de "derivatives" et extraire le mois au bon format
        $derivativesCounts = [];
        foreach ($decodedDerivatives as $timestamp) {
            $month = date('Y-m', $timestamp); // Convertir le timestamp en format "YYYY-MM"
            if (!isset($derivativesCounts[$month])) {
                $derivativesCounts[$month] = 0;
            }
            $derivativesCounts[$month]++;
        }

        // Étape 4 : Fusionner les données des deux sources (dates de "links" et de "derivatives")
        $data = [];
        foreach ($results as $result) {
            $month = $result['month'];
            $additions = $result['additions'];

            // Ajouter les ajouts venant de "derivatives" au mois correspondant
            if (isset($derivativesCounts[$month])) {
                $additions += $derivativesCounts[$month];
                unset($derivativesCounts[$month]); // Supprimer ce mois des dérivés pour éviter les doublons
            }

            $data[] = [
                'month' => $month,
                'additions' => $additions,
            ];
        }

        // Étape 5 : Ajouter les mois restants venant de "derivatives" uniquement
        foreach ($derivativesCounts as $month => $count) {
            $data[] = [
                'month' => $month,
                'additions' => $count,
            ];
        }

        // Étape 6 : Trier les données par mois
        usort($data, function ($a, $b) {
            return strcmp($a['month'], $b['month']);
        });

        return $data;
    }

    $criteria = $_POST['criteria'] ?? 'occurrences';
    $orderBy = '';

    // Normalisation des `ranking`
    if ($criteria === 'relevance') {
        normalizeRankings($bdd);
    }
    switch ($criteria) {
        case 'occurrences':
            $query = "SELECT *, (CAST(SUBSTRING_INDEX(eval, ';', 1) AS DECIMAL(10,2)) / CAST(SUBSTRING_INDEX(eval, ';', -1) AS DECIMAL(10,2))) * 5 AS rating FROM sources WHERE JSON_CONTAINS(followers, CAST(:user_id AS JSON)) AND sources.name like ". "\"%" . $_POST['input'] . "%\" ORDER BY openings DESC";
            break;
        case 'average-note':
            $query = "SELECT *, (CAST(SUBSTRING_INDEX(eval, ';', 1) AS DECIMAL(10,2)) / CAST(SUBSTRING_INDEX(eval, ';', -1) AS DECIMAL(10,2))) * 5 AS rating FROM sources WHERE JSON_CONTAINS(followers, CAST(:user_id AS JSON)) AND sources.name like ". "\"%" . $_POST['input'] . "%\" ORDER BY rating DESC";
            break;
        case 'relevance':
            $query = "SELECT *, (CAST(SUBSTRING_INDEX(eval, ';', 1) AS DECIMAL(10,2)) / CAST(SUBSTRING_INDEX(eval, ';', -1) AS DECIMAL(10,2))) * 5 AS rating FROM sources WHERE JSON_CONTAINS(followers, CAST(:user_id AS JSON)) AND sources.name like ". "\"%" . $_POST['input'] . "%\" ORDER BY ranking ASC"; // Trier par ordre de pertinence
            break;
        default:
            $query = "SELECT *,  (CAST(SUBSTRING_INDEX(eval, ';', 1) AS DECIMAL(10,2)) / CAST(SUBSTRING_INDEX(eval, ';', -1) AS DECIMAL(10,2))) * 5 AS rating FROM sources WHERE JSON_CONTAINS(followers, CAST(:user_id AS JSON)) AND sources.name like ". "\"%" . $_POST['input'] . "%\" ORDER BY openings DESC";
    }
    $result = $bdd->prepare($query);
    $user_id = isset($_SESSION['id_actif']) ? $_SESSION['id_actif'] : 0;
    $result->execute(['user_id' => $user_id]);
    $sources = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sources as $source) {
        if (!in_array($user_id, json_decode($source['followers']))) {
            continue;
        }
        $typeName = $bdd->query("SELECT parent, name FROM sourcetypes WHERE id = ".$source['type'])->fetch();
        $typeName = ($typeName['parent'] == 0) ? $typeName['name'] :  $typeName['parent']. '/' . $typeName['name'];
        $status = $bdd->query("SELECT color, name FROM sourcestatus WHERE id = ".$source['status'])->fetch();
        $statusName = $status['name'];
        $rank = intval($source['ranking']);
        // Extraction du score et calcul de la note (entre 0 et 5)
        list($score, $coefficient) = explode(';', $source['eval']);
        // Nettoyage des données
        $score = (float)($score);
        $coefficient = (float)($coefficient); // Supprimer les espaces potentiels et forcer en entier

        // Calcul de la note
        $rating = isset($source['rating']) ? $source['rating'] : 0;?>
        <div class="source-item-container" style="align-items: center;">
            <?php echo '<div style="display: flex;align-items: center;">';
            // Génération HTML pour chaque ligne source
            echo '<button class="new-button sub-box-button collapsible-source to_defilter" data-id="'.$source['id'].'" style="max-height: unset;" onclick="    this.classList.toggle(\'collapsible-box-active\'); deploy_collapsible_content_source(this.getAttribute(\'data-id\'))"></button>';
            echo '<div class="source-item" data-tags="'.implode(',', json_decode($source['tags'])).'" data-type="'.$source['type'].'" data-status="'.$source['status'].'">';
            echo '<span class="status-pill" style="background-color: ' . htmlspecialchars($status['color'], ENT_QUOTES) . ';" title="' . htmlspecialchars($status['name'], ENT_QUOTES) . '"></span>';
            echo '<a style="margin: 0; font-weight: bold; font-style : unset;color: black;" target="_blank" href="'.$source['url'].'" title="'.$source['url'].'">'.$source["name"].'</a>';
            echo $typeName;


            // Affichage des étoiles pour la note
                echo '<div class="rating" title="Note : '. number_format($rating, 2) .' / Nb d\'évaluations : '. ($coefficient / 5) .'">';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $rating) {
                        echo '<span class="star full">★</span>'; // Étoile pleine
                    } elseif ($i - 0.5 <= $rating) {
                        echo '<span class="star half">☆</span>'; // Étoile à moitié
                    } else {
                        echo '<span class="star empty">☆</span>'; // Étoile vide
                    }
                }
                echo '</div>';
            echo '</div>';?>
            <?php if ($criteria == 'relevance') {?>

                <button
                        title =
                        class="move-up-button"
                        onclick="console.log('moveUp'); moveUp(<?= $source['id'] ?>, <?= $rank ?>);"
                    <?php if ($rank === 1) echo 'disabled'; ?>>
                    ↑
                </button>
                <button
                        class="move-down-button"
                        onclick="moveDown(<?= $source['id'] ?>, <?= $rank ?>);"
                    <?php if ($rank === count($sources)) echo 'disabled'; ?>>
                    ↓
                </button>
            <?php } ?>
            <button id="edit-source-button" class="edit-icon"
                    onclick="Edit_Source(
                    <?= htmlspecialchars($source['id']) ?>,
                        0,
                    <?= htmlspecialchars(json_encode($source['name'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                    <?= '\''.htmlspecialchars($source['url']).'\'' ?>,
                    <?= htmlspecialchars(json_encode($source['doc'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($source['tags'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                    <?= htmlspecialchars($user_id) ?>,
                        )">
                <img src="Includes/Images/Bouton Modifier.png">
            </button>
            <button id="delete-source-button" class="edit-icon"
                    onclick="Edit_Source(
                    <?= htmlspecialchars($source['id']) ?>,
                        1,
                    <?= htmlspecialchars(json_encode($source['name'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                    <?= '\''.htmlspecialchars($source['url']).'\'' ?>,
                    <?= htmlspecialchars(json_encode($source['doc'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($source['tags'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                    <?= htmlspecialchars($user_id) ?>,
                        )">
                <img src="Includes/Images/Bouton%20Supprimer.png">
            </button>
        </div>
        </div>

        <div class="collapsible-content-source" data-id="<?= $source['id'] ?>">
            <?php $req = $bdd->prepare('SELECT * FROM links WHERE source = :id AND owner = :user_id ORDER BY date DESC');
            $req->execute(['id' => $source['id'], 'user_id' => $user_id]);
            $links = $req->fetchAll(PDO::FETCH_ASSOC);
            $derivatives = count($links);
            ?>
            <button id="alert-tool-button" class="edit-icon" style="padding: 7px; float: right;  border-left: red 1.5px solid;  border-bottom: red 1.5px solid;" onclick="openAlertModal(<?= $source['id'] ?>,<?= $user_id ?>)"><img src="Includes/Images/Bouton%20Alerte.png" style="margin-right: 7px">Signaler la source</button>
            <div style="margin-bottom : 15px">
                <span style="padding-right : 5px; margin-bottom : 15px"><b>URL de la source :</b>
                    <a id="source-url" href="<?= htmlspecialchars($source['url'], ENT_QUOTES) ?>"><?= htmlspecialchars($source['url'], ENT_QUOTES) ?></a>
                </span>
            </div>
            <div style="margin-bottom : 15px">
                <span style="margin-bottom: 15px; padding-right : 5px;"><b>Description :</b>
                    <?= htmlspecialchars($source['doc'], ENT_QUOTES) ?>
                </span>
            </div>
            <?php if ($derivatives > 0) {?>
                <div id="derivatives-header" style="display: flex; align-items: center; justify-content : space-between; margin-bottom: 0; margin-top: 10px;">
                    <label for="source-relatives" style="width: 170px; font-style: italic; vertical-align : top; font-weight: bold"> Mes liens dérivés (<?= $derivatives?>) : </label>
                    <?php $MonthlyOpenings = getMonthlyOpenings($source['id'], $bdd);
                    $MonthlyAdditions = getMonthlyAdditions($source['id'], $bdd);
                    echo '<button class="new-button-smaller" onclick=\'GenerateOpeningsGraph(" '.$source['name'].' ",'. json_encode($MonthlyOpenings).','.json_encode($MonthlyAdditions).',"'. $source['date'] .'")\'><img src="Includes/Images/Icone Graphe.png" class="small-button">  Visualiser l\'activité</button>';
                    ?>
                </div>
                <ul id="source-relatives" class="source-relatives-list">
                <?php
                foreach ($links as $link) {
                    echo '<li><a href="'.$link['url'].'" target="_blank" title="URL : '. $link['url'] . ' / Dernière ouverture : '.$link['lastopening'].'">'.$link['name'].'</a></li>';
                }
                ?>
                </ul>
            <?php } ?>
            <div style="display: flex; gap: 10px; align-items: center;">
                <label for="source-tags" style="width: 170px; font-weight: bold">Tags de la source :</label>
                <div class="tags-container" id="source-tags" style="width: 20vw; display: flex; flex-wrap: wrap; overflow: hidden;">
                    <?php
                    $set_tags = json_decode($source['tags']);
                    foreach ($set_tags as $tag) {
                        $req = $bdd->prepare('SELECT name FROM sourcestags WHERE id = :tag_id');
                        $req->execute(['tag_id' => $tag]);
                        $tag_name = $req->fetch();
                        $tag_name = $tag_name['name'];
                        echo '<button type="button" onclick="" id="set-source-tag-'.strval($tag).'" class="tag-div">#'.$tag_name.'</button>';
                    }
                    ?>
                </div>
            </div>

            <div class="source-cotation">
            <?php
            if ($source['cotation']) {
                $displayedButton = 0;
                $cotation_data = json_decode($source['cotation'], true);
                if ($cotation_data['eval_status'] == "A évaluer") {
                    $demand_date = $cotation_data['demand_date'];
                    $demand_date = date("d/m/y H:i", strtotime($demand_date));
                    $demand_issuer = $cotation_data['demand_issuer'];
                    echo '<div style="display : flex; justify-content : space-between; align-items : center; margin-bottom : 10px; margin-top : 10px; gap : 10px; ">';
                    echo '<span style="color: grey;text-transform: uppercase;font-size: small;">Dernière demande de cotation effectuée le '.$demand_date.' par '.$demand_issuer.'</span>';
                    $req = $bdd->prepare('UPDATE osix.sources t SET t.status = 2 WHERE t.id = '.$source['id']);
                    $req->execute();
                    echo '<button class="new-button-smaller" onclick="document.querySelector(\'#ask-for-cotation-'.$source['id'].'\').style.display = \'block\'"><img src="Includes/PHP/Images/Icone%20Cotation.png">Demander la cotation</button></div>';
                    echo '</div>';
                    $displayedButton = 1;
                }
                else {?>
                    <div style="display : flex; justify-content : space-between; align-items : center; margin-bottom : 10px; margin-top : 10px; gap : 10px; ">
                        <h6 style="margin-right: 15px; color: grey;text-transform: uppercase;font-size: small;">Aucune demande de cotation en cours.</h6>
                        <?php echo '<button class="new-button-smaller" onclick="document.querySelector(\'#ask-for-cotation-'.$source['id'].'\').style.display = \'block\'"><img src="Includes/PHP/Images/Icone%20Cotation.png">Demander la cotation</button>';?>
                    </div>
                <?php }
                if (!isset($cotation_data['grade'])) {?>
                    <div style="display: flex">
                    <h6 style="margin-right: 15px; color = #ffdf7e">SOURCE NON COTEE.</h6>
                    </div>
                <?php }
                else {
                    $grade = $cotation_data['grade'];
                    $comment = $cotation_data['comment'];
                    $date = date("d/m/y", strtotime($cotation_data['date']));
                    $features = json_decode($cotation_data['features']); ?>
                    <div class="source-cotation-header">
                        <h4>Cotation de la source :
                        <?php echo color_cotation($grade);
                        $useframe = (isset($cotation['useframe'])) ? " pour ".$cotation['useframe']. "." : "";
                        echo '<span>('.$date.')'.$useframe.'</span>
                        <button class="bordered" id="showCribleButton" onclick="document.getElementById(\'crible-eval'.$source['id'].'\').style.display=\'block\'; document.getElementById(\'showCribleButton\').style.display=\'none\'; document.getElementById(\'hideCribleButton\').style.display=\'inline\'"> Afficher le crible</button>
                        <button id="hideCribleButton" onclick="document.getElementById(\'crible-eval'.$source['id'].'\').style.display=\'none\'; document.getElementById(\'showCribleButton\').style.display=\'inline\'; document.getElementById(\'hideCribleButton\').style.display=\'none\'" class="bordered" style="display : none;"> Masquer le crible </button>
                        </h4>' ?>
                    </div>
                    <div id="crible-eval<?= $source['id'] ?>" style="gap: 20px; display : none; background-color: #d8e2e6; border-radius : 10px; padding : 15px; font-family: 'monospace'">
                        <div  style="font-family: monospace;">
                            <h6 style="margin-top : 15px; font-size: small; text-transform: uppercase; margin-bottom : 5px">Source</h6>
                            <?php
                            // Charger les questions pour la catégorie "Source"
                            $query = 'SELECT * FROM evalquestions WHERE category = "source"';
                            $stmt = $bdd->prepare($query);
                            $stmt->execute();
                            $questions = $stmt->fetchAll();
                            $i = 0;
                            foreach ($questions as $question) {
                                $checked = ($features != null && isset($features[$i]) && $features[$i] == 1) ? "checked" : "";
                                echo '<label class="source-eval-item">';
                                echo '<input disabled="disabled" type="checkbox" readonly class="source-eval-item" value=1 style="margin: 0 !important;width:  unset !important;" '.$checked.'>';
                                echo '<b>  ' . htmlspecialchars($question['title']) . '</b> : ' . htmlspecialchars($question['question']);
                                echo '</label><br>';
                                $i += 1;
                            }
                            ?>
                        </div>
                        <div style="font-family: monospace;">
                            <h6 style="margin-top : 15px; font-size: small; font-family: monospace; text-transform: uppercase; margin-bottom : 5px">Information</h6>
                            <?php
                            // Charger les questions pour la catégorie "Information"
                            $query = 'SELECT * FROM evalquestions WHERE category = "information"';
                            $stmt = $bdd->prepare($query);
                            $stmt->execute();
                            $questions = $stmt->fetchAll();
                            foreach ($questions as $question) {
                                $checked = ($features !== null && isset($cotation_features[$i]) && $cotation_features[$question['id']] == 1) ? "checked" : "";
                                echo '<label class="source-eval-item">';
                                echo '<input disabled="disabled" type="checkbox" class="source-eval-item" value= 1 style="margin: 0 !important;width:  unset !important;" '.$checked.'>';
                                echo ' <b>  ' . htmlspecialchars($question['title']) . '</b> : ' . htmlspecialchars($question['question']);
                                echo '</label><br>';
                                $i += 1;
                            }
                            ?>
                        </div>
                        <div style="font-family: monospace;">
                            <?php
                                // Charger les questions pour la catégorie "Information"
                                $query = 'SELECT * FROM evalquestions WHERE category = "caracterisation_information"';
                                $stmt = $bdd->prepare($query);
                                $stmt->execute();
                                $questions = $stmt->fetchAll();

                                $features = [];
                                $req = $bdd->prepare('SELECT COUNT(*) AS nbr FROM links WHERE source = :source_id');
                                $req->execute(['source_id' => $source['id']]);
                                $quotient = $req->fetch()['nbr'];
                                for ($i = 1; $i < 5; $i++) {
                                    $req = "SELECT COUNT(*) AS nbr FROM links WHERE feature".$i." = 1 AND source = :source_id";
                                    $stmt = $bdd->prepare($req);
                                    $stmt->execute(['source_id' => $source['id']]);
                                    $features[$i] = $stmt->fetch()['nbr'];
                                }

                                $i = 1;
                            ?>
                            <h6 style="margin-top : 15px; font-size: small; font-family: monospace; text-transform: uppercase; margin-bottom : 5px">Statistiques (sur <?= $quotient?> liens).</h6>
                            <?php foreach ($questions as $question) {
                                echo '<span>'.$question['question'].' : <b>'.round((float)$features[$i] / (float)$quotient * 100).'%</b>. </span><br>';
                                $i++;
                            }
                            ?>
                        </div>                            </div>
                <?php }
             } else {?>
                <div style="display : flex; justify-content : space-between; align-items : center; margin-bottom : 10px; margin-top : 10px; gap : 10px; ">
                    <h6 style="margin-right: 15px; color: grey;text-transform: uppercase;font-size: small;">Source non cotée et Aucune demande de cotation en cours.</h6>
                    <?php echo '<button class="new-button-smaller" onclick="document.querySelector(\'#ask-for-cotation-'.$source['id'].'\').style.display = \'block\'"><img src="Includes/PHP/Images/Icone%20Cotation.png">Demander la cotation</button>';?>
                </div>
             <?php }?>
                <div id="ask-for-cotation-<?= $source['id']?>" class="ask-for-cotation">
                    <form>
                        <label for="demand-message" style="width: 150px; vertical-align : top; margin-bottom: 15px">Message : </label>
                        <textarea id="demand-message" placeholder="Cette source est-elle consulté régulièrement ? Depuis quand ?  Avez-vous pu confirmer ou réfuter ses informations ou analyses ?                                         Dispose-t-elle d'accès pertinents ?                                                                   Pourquoi une demande de cotation maintenant ?"></textarea> <br>
                        <div>
                            <label for="use-area" style=" width: 150px;text-align: center;margin-right: 20px;">Cadre d'emploi de la source :</label>
                            <textarea id="use-area" style="height: 42px" placeholder="Dans quel cadre et sur quels sujets utilisez-vous cette source ?"></textarea>
                            <label for="demanding-group" style=" width: 150px;text-align: center;margin-right: 20px;">Demande pour le groupe :</label>
                            <?php
                            // Vérifier si $_SESSION['groups'] est défini
                            if (isset($_SESSION['groups']) && !empty($_SESSION['groups'])) {
                                // Récupérer la liste des groupes sous forme de tableau
                                $groupIds = json_decode($_SESSION['groups'], true);

                                // Filtrer la liste pour s'assurer qu'il s'agit bien de nombres
                                $groupIds = array_filter($groupIds, function ($id) {
                                    return is_numeric($id);
                                });

                                if (!empty($groupIds)) {
                                    // Générer une liste de placeholders pour la requête IN
                                    $placeholders = implode(',', array_fill(0, count($groupIds), '?'));

                                    // Préparer la requête pour récupérer les noms des groupes
                                    $query = "SELECT id, name FROM usergroups WHERE id IN ($placeholders)";
                                    $stmt = $bdd->prepare($query);
                                    $stmt->execute($groupIds);

                                    // Récupérer les résultats
                                    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                } else {
                                    $groups = [];
                                }
                            } else {
                                // Si aucun groupe n'est défini, retourner une liste vide
                                $groups = [];
                            }
                            $first= 1;?>
                            <select id="demanding-group" name="demanding-group">
                                <?php if (!empty($groups)): ?>
                                    <?php foreach ($groups as $group):
                                        if ($group != "|Analystes" && $first) {
                                            $selected = "selected=\"\"";
                                            $first = 0;
                                        } else {$selected="";} ?>
                                        <option value="<?= htmlspecialchars($group['name']) ?>" <?= $selected?>>
                                            <?= htmlspecialchars($group['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="|Analystes">|Analystes</option>
                                <?php endif; ?>
                            </select>
                        </div><br>
                        <label for="grade-suggestion">Proposition de bigramme source/information (optionnel) :</label>
                        <input id="grade-suggestion" style="width : 40px" placeholder="B3" onblur="if (this.value != '') { verif_cotation(this); }"> <br>
                    </form>
                    <div id="send-buttons" style="justify-content: right">
                        <button class="edit-icon send-button" style="" onclick="parentDiv = document.getElementById('ask-for-cotation-<?= $source['id']?>'); console.log(parentDiv); DemandCotation(parentDiv, <?= $source['id']?>, <?= $user_id?>)"><img style="vertical-align: middle; margin-right: 7px" src="Includes/PHP/Images/Icone%20Envoi.png">Envoyer la demande</button>
                        <button class="edit-icon cancel-send-button" style="" onclick="document.getElementById('ask-for-cotation-<?= $source['id']?>').style.display='none';"><img style="vertical-align: middle; margin-right : 7px" src="Includes/PHP/Images/Icone Croix.png">Annuler</button>
                    </div>
                </div>
            </div>
        </div>
    <?php }?>
    </div>
<?php }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key']) && $_POST['key'] === 'update_ranking') {
    $rankingData = json_decode(file_get_contents('php://input'), true);

    if (!isset($rankingData['ranking']) || !is_array($rankingData['ranking'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $bdd->beginTransaction();
    try {
        $updateStmt = $bdd->prepare('UPDATE sources SET ranking = :position WHERE id = :id');
        foreach ($rankingData['ranking'] as $item) {
            $updateStmt->execute([
                'position' => intval($item['position']),
                'id' => intval($item['id']),
            ]);
        }
        $bdd->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $bdd->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key']) && $_POST['key'] === 'add_fav_link') {
    // Récupérer les données de la requête
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $link_name = isset($_POST['link_name']) ? htmlspecialchars(trim($_POST['link_name'])) : '';
    $link_url = isset($_POST['link_url']) ? trim($_POST['link_url']) : '';
    $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;

    // Vérifier si un fichier est envoyé correctement pour l'icône
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
        $icon_tmp = $_FILES['icon']['tmp_name'];
        $icon_name = basename($_FILES['icon']['name']);
        $icon_ext = pathinfo($icon_name, PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        // Vérification de l'extension
        if (!in_array(strtolower($icon_ext), $allowed_ext)) {
            die("Format d'icône non valide. Seuls les formats JPG et PNG sont acceptés.");
        }

        // Déplacer l'image téléchargée vers le répertoire des icônes
        $icon_path = "Fav_Icons/" . uniqid() . "." . $icon_ext;
        $full_icon_path = "C:/Apache24/htdocs/". $icon_path;
        try {
            move_uploaded_file($_FILES['icon']['tmp_name'], $full_icon_path);
        } catch (Exception $e) {
            die("Erreur lors de l'enregistrement de l'icône.");
        }

    } else {
        die("Aucune icône valide sélectionnée.");
    }

    // Vérifier si l'utilisateur existe
    $stmt = $bdd->prepare("SELECT links FROM favoritelinks WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Utilisateur introuvable.");
    }

    // Décoder le champ JSON des liens
    $links = json_decode($row['links'], true);
    if (!$links) {
        $links = []; // Si le champ est vide ou corrompu, on initialise un tableau vide
    }

    // Trouver un nouvel index pour le lien
    $max_id = 0;
    foreach ($links as $id => $link) {
        if (intval($id) > $max_id) {
            $max_id = intval($id);
        }
    }
    $new_index = $max_id + 1;

    // Créer un nouveau favori
    $new_link = [
        'nom' => $link_name,
        'url' => $link_url,
        'image' => $icon_path,
        'source' => $source_id
    ];

    // Ajouter le lien au tableau existant
    $links[$new_index] = $new_link;

    // Encoder le tableau mis à jour en JSON
    $updated_links = json_encode($links);

    // Mettre à jour la base de données
    $update_stmt = $bdd->prepare("UPDATE favoritelinks SET links = :links WHERE user_id = :user_id");
    $update_stmt->execute([
        'links' => $updated_links,
        'user_id' => $user_id
    ]);

    echo "OK";
}
// Vérifier si un ID source a été envoyé en POST
if (isset($_POST['key']) and $_POST['key'] == 'update_openings') {
    $source_id = intval($_POST['source_id']); // S'assurer que c'est un entier

    // Requête SQL pour incrémenter la colonne 'openings'
    $query = 'UPDATE sources SET openings = openings + 1 WHERE id = :id';

    $stmt = $bdd->prepare($query);
    $stmt->execute(['id' => $source_id]);
}




// Vérifier si le formulaire a été soumis
if (isset($_POST['key']) and $_POST['key'] == 'add_source_list') {
    $sql = "INSERT INTO sourcelists (name, owner, father, links) VALUES (?, ?, ?, ?)";
    $stmt = $bdd->prepare($sql);
    $stmt->execute([$_POST['list_name'], $_POST['user_id'], $_POST['father'], '[]']);
}


if (isset($_POST['edited_source'])) {
    if ($_POST['edited_source'] == 0) {
        $userId = $_POST['user_id'];
        $sourceName = $_POST['source_name'];
        $sourceUrl = $_POST['source_url'];
        $sourceDoc = $_POST['source_doc'];
        $sourceTags = $_POST['tags_list'];
        $sourceType = $_POST['source_type'];
        $sourceStatus = $_POST['source_status'];
        $sourceEval = $_POST['source_eval'];
        $sourceCategory = $_POST['source_cat'];

        // Ajout de l'outil à la table sources avec activation seulement pour le créateur
        $sql = "INSERT INTO sources (name, url, date, doc, type, status, eval, tags, followers, openings, ranking) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" ;
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$sourceName, $sourceUrl, date('Y-m-d H:i:s'), $sourceDoc, $sourceType, $sourceStatus, "0;0", $sourceTags, '['.$userId.']', 0, 0]);

        $query = 'SELECT id FROM sources ORDER BY id DESC LIMIT 1';
        $stmt = $bdd->prepare($query);
        $stmt->execute();
        $new_id = $stmt->fetchAll()[0]['id'];

        $cat_query = 'SELECT sources FROM sourcelists WHERE id = :id';
        $cat_stmt = $bdd->prepare($cat_query);
        $cat_stmt->execute(['id' => $sourceCategory]);
        $cat_links = $cat_stmt->fetchAll()[0]['liens'];

        $arr = json_decode($cat_links, true);
        array_push($arr, $new_id);
        $json_arr = json_encode($arr);

        $sql = 'UPDATE sourcelists SET sources = :links WHERE id = :id';
        $stmt = $bdd->prepare($sql);
        $stmt->execute(['links' => $json_arr, 'id' => $sourceCategory]);

        echo 'OK';
    }
    else {
        $userId = $_POST['user_id'];
        $sourceName = $_POST['source_name'];
        $sourceUrl = $_POST['source_url'];
        $sourceDoc = $_POST['source_doc'];
        $sourceTags = $_POST['tags_list'];
        $sourceCategory = $_POST['source_cat'];
        $sourceId = $_POST['edited_source'];
        $sourceType = $_POST['source_type'];
        $sourceStatus = $_POST['source_status'];
        $sourceEval = $_POST['source_eval'];


        $req = "UPDATE sources SET name = ?, url = ?, tags = ?, doc = ?, owner = ?, type = ?, status = ?, eval = ? WHERE id = ?";
        $stmt = $bdd->prepare($req);
        $stmt->execute([$sourceName, $sourceUrl, $sourceTags, $sourceDoc, $userId, $sourceId]);


        // On récupère la catégorie actuelle de l'outil
        $queryCurrentCategory = "SELECT id, sources FROM sourcelists WHERE sources LIKE :sourceId";
        $stmtCurrent = $bdd->prepare($queryCurrentCategory);
        $stmtCurrent->execute([':sourceId' => '%"'.$sourceId.'"%']); // Conversion du lien en JSON
        $currentCategory = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

        if ($currentCategory && $currentCategory != $sourceCategory) {

            // Extraire les liens de la catégorie actuelle
            $currentLinks = json_decode($currentCategory['source'], true);

            // Retirer l'outil de la catégorie actuelle
            if (($key = array_search($sourceId, $currentLinks)) !== false) {
                unset($currentLinks[$key]);
                $updatedLinks = json_encode(array_values($currentLinks)); // Réindexation nécessaire

                $stmtUpdateCurrent = $bdd->prepare("UPDATE sourcelists SET sources = :updatedLinks WHERE id = :id");
                $stmtUpdateCurrent->execute([':updatedLinks' => $updatedLinks, ':id' => $currentCategory['id']]);
            }
        }

        // Ajouter l'outil à la nouvelle catégorie
        $queryNewCategory = "SELECT sources FROM sourcelists WHERE id = :newCategoryId";
        $stmtNewCategory = $bdd->prepare($queryNewCategory);
        $stmtNewCategory->execute([':newCategoryId' => $sourceCategory]);
        $newCategory = $stmtNewCategory->fetch(PDO::FETCH_ASSOC);

        if ($newCategory) {
            $newLinks = json_decode($newCategory['sources'], true);
            if (!in_array($sourceId, $newLinks)) {
                $newLinks[] = $sourceId; // Ajouter l'outil
                $updatedNewLinks = json_encode($newLinks);

                $stmtUpdateNew = $bdd->prepare("UPDATE sourcelists SET sources = :updatedLinks WHERE id = :id");
                $stmtUpdateNew->execute([':updatedLinks' => $updatedNewLinks, ':id' => $sourceCategory]);
            }
        }
        echo 'OK';
    }
}
if (isset($_POST['edited_link'])) {
    if ($_POST['edited_link'] == 0) {
            // Récupérer les données depuis POST
        $linkName = $_POST['link_name'] ?? '';
        $linkUrl = $_POST['link_url'] ?? '';
        $linkDoc = $_POST['link_doc'] ?? '';
        $sourceId = (int)($_POST['source_id'] ?? 0);
        $listId = (int)($_POST['list_id'] ?? 0);
        $linkType = (int)($_POST['link_type'] ?? 0);
        $userId = (int)($_POST['user_id'] ?? 0);
        $editMode = (int)($_POST['edit_mode'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0); // La note sur 5
        $checkboxAnswers = json_decode($_POST['carac'], true); // Tableau des réponses (ex : [1, 0, 1, 0])

        // Validation des données
        if (count($checkboxAnswers) !== 4) {
            echo json_encode(['success' => false, 'message' => 'Données invalides pour les cases à cocher.']);
            exit;
        }

        // Exemple : Enregistrer ces données dans une table (à personnaliser)
        $query = "INSERT INTO links (name, url, doc, source, list, type, owner, rating, feature1, feature2, feature3, feature4)
                  VALUES (:name, :url, :description, :source_id, :list_id, :type, :user_id, :rating, :q1, :q2, :q3, :q4)";
        $stmt = $bdd->prepare($query);
        $success = $stmt->execute([
            'name' => $linkName,
            'url' => $linkUrl,
            'description' => $linkDoc,
            'source_id' => $sourceId,
            'list_id' => $listId,
            'type' => $linkType,
            'user_id' => $userId,
            'rating' => $rating,
            'q1' => $checkboxAnswers[0],
            'q2' => $checkboxAnswers[1],
            'q3' => $checkboxAnswers[2],
            'q4' => $checkboxAnswers[3],
        ]);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue lors de l\'enregistrement.']);
        }
    } else {
        // Récupérer les données depuis POST
        $linkName = $_POST['link_name'] ?? '';
        $linkUrl = $_POST['link_url'] ?? '';
        $linkDoc = $_POST['link_doc'] ?? '';
        $sourceId = (int)($_POST['source_id'] ?? 0);
        $listId = (int)($_POST['list_id'] ?? 0);
        $linkType = (int)($_POST['link_type'] ?? 0);
        $userId = (int)($_POST['user_id'] ?? 0);
        $editMode = (int)($_POST['edit_mode'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0); // La note sur 5
        $checkboxAnswers = json_decode($_POST['carac'], true); // Tableau des réponses (ex : [1, 0, 1, 0])

        // Validation des données
        if (count($checkboxAnswers) !== 4) {
            echo json_encode(['success' => false, 'message' => 'Données invalides pour les cases à cocher.']);
            exit;
        }

        // Exemple : Enregistrer ces données dans une table (à personnaliser)
        $query = "UPDATE links
                SET name = :name,
                    url = :url,
                    doc = :description,
                    source = :source_id,
                    list = :list_id,
                    type = :type,
                    owner = :user_id,
                    rating = :rating,
                    feature1 = :q1,
                    feature2 = :q2,
                    feature3 = :q3,
                    feature4 = :q4
                WHERE id = :id";
        $stmt = $bdd->prepare($query);
        $success = $stmt->execute([
            'name' => $linkName,
            'url' => $linkUrl,
            'description' => $linkDoc,
            'source_id' => $sourceId,
            'list_id' => $listId,
            'type' => $linkType,
            'user_id' => $userId,
            'rating' => $rating,
            'q1' => $checkboxAnswers[0],
            'q2' => $checkboxAnswers[1],
            'q3' => $checkboxAnswers[2],
            'q4' => $checkboxAnswers[3],
            'id' => $_POST['edited_link'],
        ]);

        if ($success) {
            echo json_encode(['success' => true]);

        } else {
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue lors de l\'enregistrement.']);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key']) && $_POST['key'] === 'delete_fav_link') {
    // Récupérer l'ID du favori et l'ID de l'utilisateur
    $fav_id = isset($_POST['fav_id']) ? intval($_POST['fav_id']) : null;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;

    // Vérifie si les deux identifiants sont valides
    if (is_null($fav_id)) {
        http_response_code(400); // Mauvaise requête
        echo json_encode(['status' => 'error', 'message' => 'Identifiants invalides ou utilisateur non connecté.']);
        exit;
    }

    // Récupérer le JSON des favoris de l'utilisateur
    $query = "SELECT links FROM favoritelinks WHERE user_id = :user_id";
    $stmt = $bdd->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $favoritelinks = json_decode($result['links'], true);

        // Vérifier si le lien favori existe dans les données JSON
        if (array_key_exists($fav_id, $favoritelinks)) {
            // Supprimer le favori
            unset($favoritelinks[$fav_id]);

            // Mettre à jour la base de données avec les favoris modifiés
            $updatedLinks = json_encode($favoritelinks, JSON_UNESCAPED_UNICODE);
            $updateQuery = "UPDATE favoritelinks SET links = :updatedLinks WHERE user_id = :user_id";
            $updateStmt = $bdd->prepare($updateQuery);
            $updateStmt->bindParam(':updatedLinks', $updatedLinks, PDO::PARAM_STR);
            $updateStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $updateStmt->execute();

            // Réponse réussie
            echo json_encode(['status' => 'success', 'message' => 'Favori supprimé avec succès.']);
        } else {
            // Favori non trouvé
            http_response_code(404); // Ressource introuvable
            echo json_encode(['status' => 'error', 'message' => 'Favori introuvable.']);
        }
    }
    else {
        echo 'ELSE';
    }
}

?>
