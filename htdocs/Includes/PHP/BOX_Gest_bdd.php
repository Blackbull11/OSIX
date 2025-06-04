<?php

// Démarrer la session et se connecter à la base de données
session_start();


try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

function gen_category_name($category_id, $bdd){
    $req = "SELECT * FROM toolscategories WHERE id = $category_id";
    $stmt = $bdd->prepare($req);
    $stmt->execute();
    $category = $stmt->fetch();
    if ($category['parent'] != 0) {
        return gen_category_name($category['parent'], $bdd). ' --> '.$category['name'];
    }
    return $category['name'];
}

function gen_category_sort_request($sort_selection, $parent, $bdd)
{
    if ($sort_selection == 'category') {
        $res = '';
        $query = 'SELECT * FROM toolscategories WHERE parent = :parent';
        $stmt = $bdd->prepare($query);
        $stmt->execute(['parent' => $parent]);
        $categories = $stmt->fetchAll();
        foreach ($categories as $category) {
            $res .= ' UNION (SELECT * FROM tools WHERE category_id = ' . $category['id'] . ')' . gen_category_sort_request($sort_selection, $category['id'], $bdd);
        }
        return $res;
    }
    if ($sort_selection == 'name-asc') {
        return 'UNION (SELECT * FROM tools)ORDER BY name';
    }
    if ($sort_selection == 'name-desc') {
        return 'UNION (SELECT * FROM tools) ORDER BY name DESC';
    }
    if ($sort_selection == 'date-asc') {
        return 'UNION (SELECT * FROM tools) ORDER BY date';
    }
    if ($sort_selection == 'date-desc') {
        return 'UNION (SELECT * FROM tools) ORDER BY date DESC';
    }
    return 'UNION (SELECT * FROM tools)';
}

function generate_tags_list($tags, $bdd) {
    $res = '';
    if (empty(json_decode($tags))) {
        return '<span id="missing_tags_source" style="color: grey;text-transform: uppercase;font-size: small;">Aucun tag</span>';
    };
    $tags_list = json_decode($tags, true);
    foreach ($tags_list as $tag) {
        $req = $bdd->prepare('SELECT name FROM toolstags WHERE id = :tag_id');
        $req->execute(['tag_id' => $tag]);
        $tag_name = $req->fetch();
        $tag_name = $tag_name['name'];
        if ($res != '') { $res.= ', ';}
        $res .= '<button type="button" id="gest-active-tool-tag'.strval($tag).'" class="gest-active-tag" onclick="">#'.$tag_name.'</button>';
    }
    return $res;
}

// Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['editing'])) {
        $flag = $_POST['editing'] == 1 ? 1 : 0;
        $query = "UPDATE osix.tools t SET t.user".$_POST['user_id']." = :flag WHERE t.id = :tool_id;";
        $stmt = $bdd->prepare($query);
        $stmt->execute(['flag' => $flag, 'tool_id' => $_POST['tool_id']]);
        $tool = $stmt->fetch();
        echo 'OK';
    }
    elseif (isset($_POST['view'])) {
        $req = $bdd->prepare('SELECT * FROM (SELECT * FROM tools WHERE id = :tool_id) AS A JOIN (SELECT id, name AS category_name FROM toolscategories) AS B ON B.id = A.category_id'); ;
        $req->execute(['tool_id' => $_POST['view']]);
        $tool = $req->fetch();
        echo' <label for="tool-name">Nom de l\'outil : </label>
            <span id="tool-name" >' . $tool['name'] . '</span>
            <label for="tool-url">Lien url : </label>
            <span id="tool-url" >' . $tool['url'] . '</span>
            <div style="display: flex">
                <label for="tool-doc">Description : </label>
                <span id="tool-doc"> '. $tool['doc'] .'</span><br>
            </div>
            <label for="tool-category">Catégorie : </label>
            <span id="tool-category">'. $tool['category_name'] .'</span>
            <br>
            <div id="tool-tags" style="display: flex">
                <label for="tool-tags">Tags : </label>
                <div id="view_tags_list_content">';
                $req = $bdd->prepare('SELECT tags FROM tools WHERE id = :tool_id');
                $req->execute(['tool_id' => $_POST['view']]);
                $set_tags = $req->fetch();
                $set_tags = json_decode($set_tags['tags']);
                foreach ($set_tags as $tag) {
                    $req = $bdd->prepare('SELECT name FROM toolstags WHERE id = :tag_id');
                    $req->execute(['tag_id' => $tag]);
                    $tag_name = $req->fetch();
                    $tag_name = $tag_name['name'];
                    echo '<button type="button" id="view-active-tool-tag-'.strval($tag).'" class="view-active-tag">#'.$tag_name.'</button>';
            };
        echo '</div> </div>';
        if ($tool['user0'] == 1) {
            echo '<div> <img src="Images\Icone Star.png"> Outil recommandé ! </div>';
        }
        if ($tool['user'.$_SESSION['id_actif']] == 1) {
            echo '<div> <button onclick="Edit_Tool_Gest(-1, '.$_SESSION['id_actif'].', \''.$tool['name'].'\','.$tool['id'].')"><img src="Images\Bouton Supprimer.png"> Supprimer de mon arborescence</button> </div>';
        }
        else {
            echo '<div> <button class="edit-icon" onclick="Edit_Tool_Gest(1, '.$_SESSION['id_actif'].', \''.$tool['name'].'\','.$tool['id'].')"><img src="Images\Icone checked.png"> Ajouter à mon arborescence</button></div>';
        }
    }
    else {
        // Lire les données JSON envoyées par la requête AJAX
        $input = json_decode(file_get_contents('php://input'), true);

        // Récupérer les filtres
        $searchText = isset($input['searchText']) ? $input['searchText'] : '';
        $categories = isset($input['categories']) ? $input['categories'] : [];
        $startDate = isset($input['startDate']) ? $input['startDate'] : '';
        $endDate = isset($input['endDate']) ? $input['endDate'] : '';
        $filterMine = isset($input['mine']) ? $input['mine'] : null;
        $filterHidden = isset($input['hidden']) ? $input['hidden'] : null;
        $filterRecommended = isset($input['recommended']) ? $input['recommended'] : null;
        $selected_tags = isset($input['selected_tags']) ? $input['selected_tags'] : [];
        $sort_selection = isset($input['sort_selection']) ? $input['sort_selection'] : 'category';

        $sorted_table = '((SELECT * FROM tools WHERE category_id = -1)' . gen_category_sort_request($sort_selection, 0, $bdd) . ') as sorted_tools';
        // Préparer une liste d'outils filtrés par catégorie
        $filteredToolIds = [];
        if (!empty($categories)) {
            // Obtenir les outils liés à chaque catégorie filtrée
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $categoryQuery = "SELECT liens FROM toolscategories WHERE id IN ($placeholders)";
            $categoryStmt = $bdd->prepare($categoryQuery);
            $categoryStmt->execute($categories);
            $categoryResults = $categoryStmt->fetchAll();
            // Extraire les outils de la colonne "liens" (JSON)
            foreach ($categoryResults as $row) {
                $toolsInCategory = json_decode($row['liens'], true);
                if (is_array($toolsInCategory)) {
                    $filteredToolIds = array_merge($filteredToolIds, $toolsInCategory);
                }
            }
            $filteredToolIds = array_unique($filteredToolIds);
        }
        else {
            $req = "SELECT id FROM tools";
            $stmt = $bdd->prepare($req);
            $stmt->execute();
            $tools = $stmt->fetchAll();
            $filteredToolIds = array_column($tools, 'id');
        }
        // Supprimer les doublons

        if (!empty($selected_tags)) {
            $req = "SELECT tags FROM tools WHERE id = :tool_id";
            $stmt = $bdd->prepare($req);
            foreach ($filteredToolIds as $index => $toolId) {
                $stmt->execute(['tool_id' => $toolId]);
                if (!count(array_intersect(json_decode($stmt->fetch()['tags'], true), $selected_tags)) == count($selected_tags)) {
                    unset($filteredToolIds[$index]);
                }
            }
        }

        //Filtrer selon les hashtags
//        echo count($filteredToolIds);
//        $definite_filtered_Ids = [];
//        $req = "SELECT * FROM tools WHERE id = :tool_id";
//        foreach ($filteredToolIds as $toolId) {
//            $stmt = $bdd->prepare($req);
//            $stmt->execute(['tool_id' => $toolId]);
//            $tool = $stmt->fetch();
//            $flag = 1;
//            foreach ($selected_tags as $tag) {
//                if (!in_array(intval($tag), json_decode($tool['tags'], true))) {
//                    echo (intval($tag));
//                    $flag = 0;
//                }
//            }
//            if ($flag == 1) {
//                $definite_filtered_Ids[] = $toolId;
//            }
//        }
//        $filteredToolIds = $definite_filtered_Ids;

        // Construire la requête pour la table "tools"
        $query = "SELECT *, date_format(date,'%W %d %M %Y') FROM $sorted_table WHERE (1=1)";
        $params = [];

        // Ajouter le filtre texte (recherche)
        if (!empty($searchText)) {
            $query .= " AND (sorted_tools.name LIKE :searchText OR sorted_tools.doc LIKE :searchText OR sorted_tools.url LIKE :searchText)";
            $params['searchText'] = '%' . $searchText . '%';
        }

        // Ajouter le filtre basé sur les IDs d'outils (si filtré par catégorie)
        if (!empty($filteredToolIds)) {
            $placeholders = [];
            foreach ($filteredToolIds as $index => $toolId) {
                $placeholder = ':toolId' . $index;
                $placeholders[] = $placeholder;
                $params[$placeholder] = $toolId;
            }
            if (!empty($placeholders)) {
                $query .= " AND sorted_tools.id IN (" . implode(',', $placeholders) . ")";
            }
        }

        // Ajouter les filtres de dates
        if (!empty($startDate)) {
            $query .= " AND sorted_tools.date >= :startDate";
            $params['startDate'] = $startDate;
        }
        if (!empty($endDate)) {
            $query .= " AND sorted_tools.date <= :endDate";
            $params['endDate'] = $endDate;
        }
        // Ajouter les filtres supplémentaires (checkboxes)
        if ($filterMine == 1) {
            $query .= " AND sorted_tools.user" . $_SESSION['id_actif'] . " = 1";
        }
        if ($filterHidden == 1) {
            $query .= " AND sorted_tools.user" . $_SESSION['id_actif'] . " = 0";
        }
        if ($filterRecommended == 1) {
            $query .= " AND sorted_tools.user0 = 1";
        }
        $stmt = $bdd->query('SET lc_time_names = "fr_FR";');
        $stmt = $bdd->prepare($query);
        $stmt->execute($params);

        // Récupérer les résultats
        $results = $stmt->fetchAll();

        // Vérifier s'il y a des résultats
        if ($results) {
            foreach ($results as $tool) {
                if ($tool['image'] == null) {
                    $tool['image'] = 'Fav_Icons/Default_Tool.png';
                }
                echo '<tr>';
                echo '<td>' . gen_category_name($tool['category_id'], $bdd) . '</td>';
                echo '<td class="image_column"><img src="' . $tool['image'] . '" alt="'.$tool['name'].'" width="40px" height="40px"></td>';
                $toolName = $tool['name'];
                if ($tool['user' . $_SESSION['id_actif']] == 0) {
                    $toolName = '<button id="edit-tool-button" class="edit-icon" style="padding-right : 15px" onclick="Edit_Tool_Gest(1, ' . $_SESSION["id_actif"] . ', \''.$tool['name'].'\', ' . $tool["id"] . ')" title="Ajouter l\'outil à mon arborescence"> <img src="Includes/Images/Icone Plus.png"> </button>'.$toolName;
                }
                else {
                    $toolName = '<button id="edit-tool-button" class="edit-icon" style="padding-right : 15px" onclick="Edit_Tool_Gest(-1, ' . $_SESSION["id_actif"] . ', \''.$tool['name'].'\', ' . $tool["id"] . ')" title="Supprimer l\'outil de mon arborescence"> <img src="Includes/Images/Icone checked.png"> </button>'.$toolName;
                }
                if ($tool['user0'] == 1) {
                    $toolName = '<button id="edit-tool-button" class="edit-icon" onclick=""> <img src="Includes/Images/Icone Star.png"> </button>'.$toolName;
                }
                else{
                    $toolName = '<span style="padding-right : 32px"></span>'.$toolName;
                }

                echo '<td style="font-weight: bold">' . $toolName . '</td>';
                echo '<td style="max-width: 15vw">' . $tool['doc'] . '</td>';
                echo '<td class="url-column"><a href="' . $tool['url'] . '" target="_blank">' . $tool['url'] . '</a></td>';
                echo '<td>' . generate_tags_list($tool['tags'], $bdd) . '</td>';
                echo '<td style="text-transform: capitalize">' . $tool["date_format(date,'%W %d %M %Y')"] . '</td>';
                echo '</tr>';
            }
        } else {
            echo "<tr><td colspan='4'>Aucun résultat trouvé.</td></tr>";
        }
    }

} else {
    echo "Requête invalide.";
}
?>

