<?php
session_start();
try {
    // On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}

if (isset($_POST['key'])) {
    if ($_POST['key'] == 'suggest_source_tags'){

        // Cette clé de requête correspond aux requêtes pour la génération des suggestiobns de hashtags
        $input = $_POST['input'];
        $source_name = $_POST['source_name'];
        $source_doc = $_POST['source_doc'];
        $source_category = $_POST['source_category'];
        $user_id = $_SESSION['id_actif'];


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
        $query = 'SELECT * FROM sourcestags ORDER BY user'.$user_id.' DESC';
        $stmt = $bdd->prepare($query);
        $stmt->execute();
        $tags = $stmt->fetchAll();


        if ($input == "") {
            // Attribution d'un score à chaque tag pour l'affichage par ordre de pertinence lorsque l'entrée est vide
            // Les tags déjà affectés sont affectés de la valeur -1 et ne seront pas affichés
            // Le tableau score est de la forme {$tag['id'] => $score_du_tag}
            $keys = array($_POST['source_name'], $_POST['source_doc'], $_POST['source_category']);
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
                $res = 'Empty scores ??';
            }
            else {
                $max = max($scores);
                while ($max >= 0) {
                    foreach ($tags as $tag) {
                        if (isset($scores[$tag['id']])){
                            if ($scores[$tag['id']] == $max) {
                                if ($tag['user'.$user_id] == 2) {
                                    $color = 'background-color : rgba(255, 217, 56, 0.5)';
                                }
                                elseif ($tag['user'.$user_id] == 1) {
                                    $color = 'background-color : rgba(124, 228, 249, 0.87)';
                                } else {$color = "";}
                                $res .= '<button type="button"  data-fav="'.$tag['"user'.$_SESSION['id_actif'].'"'].'" class="tag-div" onclick="Add_source_tags(' . $tag['id'] . ', \'' . $tag['name'] . '\', '. $user_id .')" style="padding:5px; '.$color.'">#' . $tag['name'] . '</button>';
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
                        if ($tag['user'.$user_id] == 2) {
                            $color = 'background-color : rgba(255, 217, 56, 0.5)';
                        }
                        elseif ($tag['user'.$user_id] == 1) {
                            $color = 'background-color : rgba(124, 228, 249, 0.87)';
                        } else {$color = "";}
                        $res .= '<button class="tag-div" data-fav="'.$tag['"user'.$_SESSION['id_actif'].'"'].'" onclick="Add_source_tags(' . $tag['id'] . ', \'' . $tag['name'] . '\', '. $user_id .')" style="padding:5px; '.$color.'">#' . $tag['name'] . '</button>';
                    }
                }
            }

        }
        echo $res === "" ? "<span style = 'padding : 5px'>Aucune suggestion :(</span>" : $res;
    }
    else if ($_POST['key'] == 'new_source_tag_suggestion') {
        $req = 'INSERT INTO news_admin (asking_user, demandtype, content ) VALUES (?, "new_source_tag_suggestion", ?)';
        $stmt = $bdd->prepare($req);
        $stmt->execute([$_POST['user_id'], $_POST['tag_name']]);
    }
    else if ($_POST['key'] == 'accepted_new_source_tag') {
        $sql = 'SELECT id FROM usergroups';
        $stmt = $bdd->prepare($sql);
        $stmt->execute();
        $groups = array_column($stmt->fetchAll(), 'id');
        $stmt->closeCursor();
        $req = 'INSERT INTO sourcestags (name, tagtype, groups) VALUES (?, ?, ?)';
        $stmt = $bdd->prepare($req);
        $stmt->execute([$_POST['tag_name'], $_POST['tag_type'], json_encode($groups)]);

    }
    else if ($_POST['key'] == 'withdraw_source_tag') {
        // ce cas n'a pas lieu d'être, la base de donnée est modifiée à l'envoi du formulaire
    }
    else if ($_POST['key'] == 'search_source_tag') {
        $flag = false;
        $req = 'SELECT name FROM sourcestags';
        $stmt = $bdd->prepare($req);
        $stmt->execute();
        $res = $stmt->fetchAll();
        $tags = array_column($res, 'name');
        foreach ($tags as $tag) {
            if (strpos($tag, $_POST['input']) !== false) {
                $sql = 'SELECT id FROM sourcestags WHERE name = :name';
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