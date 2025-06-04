<?php
try {
    // On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}

if (isset($_POST['key'])) {
    if ($_POST['key'] == 'suggest_tool_tags'){

        // Cette clé de requête correspond aux requêtes pour la génération des suggestions de hashtags
        $input = $_POST['input'];
        $tool_name = $_POST['tool_name'];
        $tool_doc = $_POST['tool_doc'];
        $tool_category = $_POST['tool_category'];
        $user_id = $_POST['user_id'];
        $memory = $_POST['memory'];

        // Convertir $memory en tableau si nécessaire
        if (!is_array($memory)) {
            $memory = json_decode($memory, true);
        }
        if (!is_array($memory)) {
            $memory = [];
        }

        //Obtenir la liste de tous les tags : array de la forme { 'id' => {INT}; 'name' => {STR}  }
        $res ='';
        $query = 'SELECT * FROM toolstags';
        $stmt = $bdd->prepare($query);
        $stmt->execute();
        $tags = $stmt->fetchAll();


        if ($input == "") {
            // Attribution d'un score à chaque tag pour l'affichage par ordre de pertinence lorsque l'entrée est vide
            // Les tags déjà affectés sont affectés de la valeur -1 et ne seront pas affichés
            // Le tableau score est de la forme {$tag['id'] => $score_du_tag}
            $req = "SELECT * FROM toolscategories WHERE id = '%".$tool_category."%'";
            $stmt = $bdd->prepare($req);
            $stmt->execute();
            $tags_id = $stmt->fetchAll();
            $cat_tag_names = "";
            if ($tags_id) {
                foreach ($tags_id as $tag_id) {
                    $req = "SELECT * FROM toolstags WHERE id = '%".$tag_id."%'";
                    $stmt = $bdd->prepare($req);
                    $stmt->execute();
                    $tag_name = $stmt->fetch()['name'];
                    $cat_tag_names = $cat_tag_names.' '.$tag_id;
                }
            }

            $keys = array($_POST['tool_name'],  $_POST['tool_category'], $_POST['tool_doc'], $cat_tag_names);
            $scores = array();
            foreach ($tags as $tag) {
                if (!in_array($tag['id'], $memory)) {
                    $temp_score = 0;
                    for ($i = 0; $i < 4; $i++) {
                        if (strpos($keys[$i], $tag['name']) !== false) {
                            $temp_score += (4-$i/2);
                        }
                    }
                    $scores[$tag['id']] = $temp_score;
                }
                else {
                    $scores[$tag['id']] = -1;
                }
            };
            if (empty($scores)) {
                $res = 'Anormal result';
            }
            else {
                $max = max($scores);
                while ($max >= 0) {
                    foreach ($tags as $tag) {
                        if (isset($scores[$tag['id']])){
                            if ($scores[$tag['id']] == $max) {
                                $res .= '<button type="button" class="tag-div" onclick="Add_tool_tags(' . $tag['id'] . ', \'' . $tag['name'] . '\', '. $user_id .')" style="padding:5px">#' . $tag['name'] . '</button>';
                                unset($scores[$tag['id']]);
                            }
                        }
                    }
                    if (!empty($scores)) {
                        $max = max($scores);
                    } else {
                        $max = -1;
                    };
                }
            }
        }
        else {
            $input = strtolower($input);
            foreach ($tags as $tag) {
                if (!in_array($tag['id'], $memory)) {
                    if (strpos(strtolower($tag['name']), $input) !== false) {
                        $res .= '<button type="button" class="tag-div" onclick="Add_tool_tags(' . $tag['id'] . ', \'' . $tag['name'] . '\', '. $user_id .')" style="padding:5px">#' . $tag['name'] . '</button>';
                    }
                }
            }

        }
        echo $res === "" ? "Aucune suggestion :(" : $res;
    }
    if ($_POST['key'] == 'get_added_tags') {
        if ((int)$_POST['tool_id'] != 0) {
            $req = 'SELECT tags FROM tools WHERE id = :id';
            $stmt = $bdd->prepare($req);
            $stmt->execute(['id' => $_POST['tool_id']]);
            $res = $stmt->fetch();
            echo ($res['tags']);
        }
        else {
            echo '[]';
        }

    }
    elseif ($_POST['key'] == 'suggest_tool_tags_gest'){
        // Cette clé de requête correspond aux requêtes pour la génération des suggestiobns de hashtags
        $input = $_POST['input'];
        $tool_name = $_POST['tool_name'];
        $tool_doc = $_POST['tool_doc'];
        $tool_category = $_POST['tool_category'];
        $user_id = $_POST['user_id'];
        $memory = $_POST['memory'];

        // Convertir $memory en tableau si nécessaire
        if (!is_array($memory)) {
            $memory = json_decode($memory, true);
        }
        if (!is_array($memory)) {
            $memory = [];
        }

        //Obtenir la liste de tous les tags : array de la forme { 'id' => {INT}; 'name' => {STR}  }
        $res ='';
        $query = 'SELECT * FROM toolstags';
        $stmt = $bdd->prepare($query);
        $stmt->execute();
        $tags = $stmt->fetchAll();


        if ($input == "") {
            // Attribution d'un score à chaque tag pour l'affichage par ordre de pertinence lorsque l'entrée est vide
            // Les tags déjà affectés sont affectés de la valeur -1 et ne seront pas affichés
            // Le tableau score est de la forme {$tag['id'] => $score_du_tag}
            $keys = array($_POST['tool_name'], $_POST['tool_doc'], $_POST['tool_category']);
            $scores = array();
            foreach ($tags as $tag) {
                if (!in_array($tag['id'], $memory)) {
                    $temp_score = 0;
                    for ($i = 0; $i < 3; $i++) {
                        if (strpos($keys[$i], $tag['name']) !== false) {
                            $temp_score += (3-$i);
                        }
                    }
                    $scores[$tag['id']] = $temp_score;
                }
                else {
                    $scores[$tag['id']] = -1;
                }
            };
            if (empty($scores)) {
                $res = 'WTF ??';
            }
            else {
                $max = max($scores);
                while ($max >= 0) {
                    foreach ($tags as $tag) {
                        if (isset($scores[$tag['id']])){
                            if ($scores[$tag['id']] == $max) {
                                $res .= '<button id="available-tag-'.$tag['id'].'" class="tag-div" >#' . $tag['name'] . '</button>';
                                unset($scores[$tag['id']]);
                            }
                        }
                    }
                    if (!empty($scores)) {
                        $max = max($scores);
                    } else {
                        $max = -1;
                    };
                }
            }
        }
        else {
            $input = strtolower($input);
            foreach ($tags as $tag) {
                if (!in_array($tag['id'], $memory)) {
                    if (strpos(strtolower($tag['name']), $input) !== false) {
                        $res .= '<button id="available-tag-'.$tag['id'].'" class="tag-div" >#' . $tag['name'] . '</button>';
                    }
                }
            }

        }
        echo $res === "" ? "Aucune suggestion :(" : $res;
    }

//    else if ($_POST['key'] == 'new_tool_tag') {
//        $req = 'INSERT INTO toolstags (name) VALUES (:name)';
//        $stmt = $bdd->prepare($req);
//        $stmt->execute(['name' => $_POST['name']]);
//        $sql = 'SELECT id FROM toolstags WHERE name = :name';
//        $stmt = $bdd->prepare($sql);
//        $stmt->execute(['name' => $_POST['name']]);
//        $new_id = $stmt->fetchAll()[0]['id'];
//        echo $new_id;
//    }
    else if ($_POST['key'] == 'search_tool_tag') {
        $flag = false;
        $req = 'SELECT name FROM toolstags';
        $stmt = $bdd->prepare($req);
        $stmt->execute();
        $res = $stmt->fetchAll();
        $tags = array_column($res, 'name');
        foreach ($tags as $tag) {
            if (strpos($tag, $_POST['input']) !== false) {
                $sql = 'SELECT id FROM toolstags WHERE name = :name';
                $stmt = $bdd->prepare($sql);
                $stmt->execute(['name' => $tag]);
                $id = $stmt->fetchAll()[0]['id'];
                $flag = true;
                echo $id;
            }
        }
        if (!$flag) {
            echo 0;
        }


    }
}