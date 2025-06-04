<?php
try {
    // On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}

if (isset($_SESSION['id_actif'])) {
    $user_id = $_SESSION['id_actif'];
    $user_admin = $_SESSION['is_admin'];
}
else {
    $user_id = 0;
    $user_admin = 0;
}

?>
<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSIX</title>
    <link rel="icon" href="Includes\Images\Icone Onglet.png" />
    <link rel="stylesheet" href="STYLE\IndexStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexPopupStyle.css"/>
    <link rel="stylesheet" href="STYLE\HeaderStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexEPIXStyle.css"/>
    <link rel="stylesheet" href="STYLE\FooterStyle.css"/>
    <link rel="stylesheet" href="STYLE\TutorialStyle.css"/>
</head>


<body>

<?php require_once 'Includes/PHP/header.php';
$user_id = 0;
$user_admin = 0;
if (isset($_SESSION['id_actif'])) {
    $user_id = $_SESSION['id_actif'];
    $user_admin = ($_SESSION['is_admin'] == 1) ? 1 : 0;
}?>

<?php

// Vérifier si la variable GET 'first-log' est définie et qu'elle est vraie
if (isset($_GET['first-log']) && $_GET['first-log'] === 'true') {
    echo '<script type="text/javascript">window.addEventListener("load", function() { startTutorial(); });</script>';
}
// Vérifiez si 'first-log' est défini et vaut true
if (isset($_SESSION['first-log']) && $_SESSION['first-log'] === true) {
    echo '<script type="text/javascript">window.addEventListener("load", function() { startTutorial(); });</script>';

    // Optionnel : désactiver 'first-log' après affichage pour éviter que le tutoriel ne démarre de nouveau au prochain chargement
    unset($_SESSION['first-log']);
}

$alert_message = $bdd->query("SELECT * FROM constantes WHERE name='Alert_Content'")->fetch();

if ($alert_message && $alert_message['valeur'] != '') {
    echo '<div id="alert-message" style="background-color: rgba(255,131,131,0.43)">' .$alert_message['valeur'].'</div>';
}
if ($user_id == 0) {
    echo '<div id="demo-message">Attention, vous n\'êtes pas connecté ! <br> Connectez-vous <a href="profils.php" style="font-family: monospace">ici</a> pour accéder à votre espace personnel ou lancez le <button style="text-decoration: underline 1px red solid; color: red; padding: 0; font-size: 20px; font-family: monospace;" onclick="startTutorial()">tutoriel</button>!</div>';
}

?>

<div class= "content">
    <div class="BOX MAIN">
        <div class="main-div-header"><span class="main-div-title">BOX</span><span class="main-div-subtitle"> - Boîte à Outil</span></div>
        <hr class="main-div-hr" id="BOX-hr">
        <h3 style="text-transform: uppercase">Outils Favoris</h3>
        <div class="gallery" id="favorite-tools-gallery">
            <?php $query = 'SELECT tools FROM favoritetools WHERE user_id = :user_id';
            $stmt = $bdd->prepare($query);
            $stmt->execute(['user_id' => $user_id]);
            $favtools_json = $stmt->fetch();
            $favtools = json_decode($favtools_json['tools'], true);
            $req = $bdd->query("SELECT id FROM tools");
            $allTools = $req->fetchAll();
            $allTools = array_column($allTools, 'id');
            foreach ($favtools as $favtool_id => $favtool_img) {
                if (!in_array($favtool_id, $allTools)) {
                    //Prévention des erreurs : si l'un des outils définis comme favori a été supprimé ou n'existe pas, le supprimer de la liste des favoris pour l'utilisateur concerné
                    $req = "SELECT tools FROM favoritetools WHERE user_id = :user_id";
                    $stmt = $bdd->prepare($req);
                    $stmt->execute(['user_id' => $user_id]);
                    $tools_list = $stmt->fetch()['tools'];
                    $tools_arr = json_decode($tools_list, false);
                    unset($tools_arr[$favtool_id]);
                    $json_arr = json_encode($tools_arr);
                    $req = "UPDATE favoritetools SET tools = :json_arr WHERE user_id = :user_id";
                    $stmt = $bdd->prepare($req);
                    $stmt->execute(['json_arr' => $json_arr, 'user_id' => $user_id]);
                }
                else {
                    $query = 'SELECT * FROM tools WHERE id = :id';
                    $stmt = $bdd->prepare($query);
                    $stmt->execute(['id' => $favtool_id]);
                    $tool = $stmt->fetch();
                    echo '<div style="max-height: 2vw" class="tool-fav-div" id="FavTool' . $favtool_id . '"><a class="fav-anchor" target="_blank" href="' . $tool['url'] . '" target="_blank" title="' . $tool['name'] . '">
                <img class="fav-icon" src="'.$favtool_img.'" alt="' . $tool['name'] . '"></a>
                <a onclick="deleteFavTool(' . $favtool_id . ')" class="delete-anchor"><img class="delete-icon to_defilter" src="Includes\Images\Icone moins.png" ></a></div>';
                }
            }
            ?>
            <button  onclick="document.getElementById('popup-fav-tool').style.display = 'flex'" style="padding:0px"> <img class="new-button-img to_defilter" src="Includes\Images\Icone Plus.png"></button>
        </div>
        <br style="margin: 1em 0">
        <div class="collapsible-matrix" id="tools-matrix">
            <h3 style="text-transform: uppercase; margin-top : 0">Tous les outils</h3>
            <?php /**
             * Generates a nested list of tool categories and associated tools. The function is recursive
             * and is used to display hierarchical categories and their tools dynamically in an HTML structure.
             *
             * @param int $n The ID of the parent category. If 0, it represents the root level.
             * @param PDO $bdd The database connection object used for querying the database.
             * @param $is_admin
             * @param $user_id
             * @return void
             */
            function gen_category_list_tool($n, $bdd, $user_id_in, $is_admin_in){
                $query = 'SELECT * FROM toolscategories WHERE parent = :parent';
                $stmt = $bdd->prepare($query);
                $stmt->execute(['parent' => $n]);
                $categories = $stmt->fetchAll();
                foreach ($categories as $category) {?>
                    <?php
                    if ($n == 0) {  // Distinction des catégories principales et des sous-catégories pour des besoins de style
                        echo '<button class="new-button collapsible to_defilter"></button>' . '  ' . $category['name'];
                    } else {
                        echo '<button class="new-button collapsible sub-button to_defilter"></button>'.'  '.$category['name']; // sub-button est un style propre aux sous-catégories
                    } ?>
                    <!-- Les fonctions javascript appelés par ces différents boutons sont à retrouver dans le fichier ToolEdits.js -->
                    <div class="collapsible-content">
                        <?php echo '<ul id="tool-ul-of-'.$category['id'].'">' ?>
                        <?php  gen_category_list_tool($category['id'], $bdd, $user_id_in, $is_admin_in );
                        $sources = json_decode($category['liens'], true);?>
                        <?php foreach ($sources as $source) {
                            $tool = $bdd->query("SELECT * FROM tools WHERE id =".$source)->fetch();
                            if (isset($tool['user'.$user_id_in]) && $tool['user'.$user_id_in] == 1) {
                                echo '<li id="tool'.$tool['id'].'">';
                                echo '<a target="_blank" href="' . htmlspecialchars($tool['url'],ENT_QUOTES) . '" title="' . htmlspecialchars($tool['doc']) . '">' . htmlspecialchars($tool['name']) . '</a>';?>

                                <button id="edit-tool-button" class="edit-icon"
                                        onclick="Edit_Tool(
                                        <?= htmlspecialchars($tool['id'],ENT_QUOTES) ?>,
                                                0,
                                        <?= htmlspecialchars($category['id'],ENT_QUOTES) ?>,
                                        <?= htmlspecialchars(json_encode($tool['name'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                                        <?= '\''.htmlspecialchars($tool['url']).'\'' ?>,
                                        <?= htmlspecialchars(json_encode($tool['doc'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                                        <?= htmlspecialchars(json_encode($tool['tags'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                                        <?= htmlspecialchars($user_id_in,ENT_QUOTES) ?>,
                                        <?= htmlspecialchars($is_admin_in,ENT_QUOTES) ?>
                                                )">
                                    <img src="Includes/Images/Bouton Modifier.png">
                                </button>
                                <button id="delete-tool-button" class="edit-icon"
                                        onclick="Edit_Tool(
                                        <?= htmlspecialchars($tool['id'],ENT_QUOTES) ?>,
                                                1,
                                        <?= htmlspecialchars($category['id'],ENT_QUOTES) ?>,
                                        <?= htmlspecialchars(json_encode($tool['name'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                                        <?= '\''.htmlspecialchars($tool['url'],ENT_QUOTES).'\'' ?>,
                                        <?= htmlspecialchars(json_encode($tool['doc'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                                        <?= htmlspecialchars(json_encode($tool['tags'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                                        <?= htmlspecialchars($user_id_in,ENT_QUOTES) ?>,
                                        <?= htmlspecialchars($is_admin_in,ENT_QUOTES) ?>
                                                )">
                                    <img src="Includes/Images/Bouton%20Supprimer.png">
                                </button>
                                <?php echo '<button id="alert-tool-button" class="edit-icon" onclick="Alert_Tool('.$tool['id'].','.$user_id_in.')" title="Signaler cet outil">
                                    <img src="Includes/Images/Bouton%20Alerte.png">
                                </button>';
                                ?>
                                <!--                                </li>-->
                            <?php }
                        }
                        ?>
                        </ul>
                        <ul>
                            <?php echo '<button class="new-button small-button add-tool-button" onclick="Add_Tool('.$category["id"].', '.$user_id_in.', '.$is_admin_in.')"><img src="Includes\Images\Icone Plus.png" class="small-button to_defilter">Nouvel outil</button>' ?>
                            <?php if ($n != 0){
                                echo '<hr style="width: 100%;">';
                            }?>
                        </ul>
                    </div>
                    <?php if ($n == 0){
                        echo '<hr>';
                    }?>
                <?php } ?>
            <?php }?>
            <?php gen_category_list_tool(0, $bdd, $user_id, $user_admin); ?>
            <?php echo '<button class="new-button to_defilter add-tool-button" onclick="Add_Tool(0, '.$user_id.', '.$user_admin.')"><img src="Includes\Images\Icone Plus.png" class=" to_defilter">Nouvel outil</button>' ?>
            <?php echo '<button class="new-button to_defilter add-tool-button" onclick="Add_Category('.$user_id.', '.$user_admin.')" style="margin-left : 15px"><img src="Includes\Images\Icone Boite.png" class=" to_defilter">Nouvelle catégorie</button>' ?>
        </div>
    </div>

    <hr>

    <div class="GLORIX MAIN">
        <div class="main-div-header"><span class="main-div-title">GLORIX</span><span class="main-div-subtitle"> - Gestionnaires Listes Optimisés des Ressources Internet</span></div>
        <hr id="GLORIX-hr" class="main-div-hr">
        <div id="generic-sources">
            <span class="collapsible-title">Liens favoris</span><button class="collapsible-box collapsible-box-active to_defilter" style="border: none; padding: 0 10px"></button>
            <span></span>
            <div class="collapsible-content-box" style="max-height: fit-content">
                <div class="gallery" id="generic-sources-gallery">
                <?php

                // Étape 1 : Récupérer les données JSON de la colonne "sources" de favoritelinks
                $query = 'SELECT links FROM favoritelinks WHERE user_id = :user_id';
                $stmt = $bdd->prepare($query);
                $stmt->execute(['user_id' => $user_id]);
                $favlinks_json = $stmt->fetch();

                // Étape 2 : Décoder le JSON
                $favlinks = json_decode($favlinks_json['links'], true); // Conversion en tableau associatif

                // Étape 3 : Vérifier si des liens existent
                if (!empty($favlinks)) {
                    foreach ($favlinks as $favlink_id => $link) {
                        // Information pour chaque lien
                        $link_url = htmlspecialchars($link['url'], ENT_QUOTES); // URL sécurisée
                        $link_image = htmlspecialchars($link['image'], ENT_QUOTES); // Image sécurisée
                        $link_name = htmlspecialchars($link['nom'], ENT_QUOTES);
                        $link_source = isset($link['source']) ? $link['source'] : 0;

                        $updateClick = ($link_source != 0) ? 'onclick="updateOpenings(' . $link_source . ')"' : '';

                        // Affichage dynamique sous forme d'un bouton avec image et lien
                        echo '<div style="max-height: 2vw" class="link-fav-div" data-id='.$favlink_id.' id="FavLink' . $favlink_id . '">';
                        echo '<a class="fav-anchor" href="' . $link_url . '" target="_blank" title="' . $link_name . '">';
                        echo '<img class="fav-icon" src="' . $link_image . '" alt="' . $link_name . '">';
                        echo '</a>';

                        // Bouton pour supprimer le favori
                        $user_id = isset($_SESSION['id_actif']) ? $_SESSION['id_actif'] : 0;
                        echo '<a onclick="deleteFavLink(' . $favlink_id . ', '.$user_id.')" class="delete-anchor">';
                        echo '<img class="delete-icon to_defilter" src="Includes/Images/Icone moins.png">';
                        echo '</a>';
                        echo '</div>';
                    }
                }
                ?>
                <button  onclick="document.getElementById('popup-fav-link').style.display = 'flex'" style="padding:0px" title="Ajouter une lien favori"> <img class="new-button-img to_defilter" src="Includes\Images\Icone Plus.png"></button>
            </div>
            </div>
        </div>
        <br style="margin: 1em 0">
        <div class="collapsible-matrix" id="sources-matrix">
            <div>
                <span class="collapsible-title">Mes sources</span><button id="collapsibleForSources" class="collapsible-box to_defilter" style="border: none; padding: 0 10px"></button>
                <span></span>
                <div class="collapsible-content-box">
                    <h5 style="margin : 0.7em 0">Filtres :</h5>
                    <div class="filters">
                        <div style="display: flex; margin-bottom: 0.5em">
                            <input class="unbordered-modern" type="text" id="filter-source-input" placeholder="Chercher une source" onkeyup="sortSources(this.value)">
                        </div>
                        <select id="type-filter" class="unbordered-modern" onchange="applyFilters()">
                            <option value="all">Choisir un type</option>
                            <?php
                            // Récupération des types dans sourcetypes
                            $types = $bdd->query("SELECT id, name FROM sourcetypes")->fetchAll();
                            foreach ($types as $type) {
                                echo '<option value="'.$type["id"].'">'.$type["name"].'</option>';
                            }
                            ?>
                        </select>
                        <select id="status-filter" class="unbordered-modern" onchange="applyFilters()">
                            <option value="all">Choisir un état</option>
                            <?php
                            // Récupération des états dans sourcestatus
                            $statuses = $bdd->query("SELECT id, name FROM sourcestatus")->fetchAll();
                            foreach ($statuses as $status) {
                                echo '<option value="'.$status["id"].'">'.$status["name"].'</option>';
                            }
                            ?>
                        </select>
                        <select id="sort-options" class="unbordered-modern" onchange="sortSources(document.getElementById('filter-source-input').value)" style="margin-top : 10px">
                            <option value="relevance" selected>Pertinence</option>
                            <option value="occurrences">Occurrences décroissantes</option>
                            <option value="average-note">Note moyenne décroissante</option>
                        </select>
                    </div>
                    <ul id="sources-list" style="margin-bottom : 15px">
                    </ul>
                    <?php echo '<button class="new-button to_defilter add-source-button" onclick="source_added_tags = []; Add_Source('.$user_id.','.$user_admin.')"><img src="Includes\Images\Icone Plus.png" class="to_defilter">Nouvelle source</button>' ?>
                </div>
            </div>
        </div>
        <br style="margin-bottom: 1em">
        <div class="collapsible-matrix" id="links-matrix">
            <div>
                <span class="collapsible-title">Mes listes</span><button class="collapsible-box collapsible-box-active to_defilter" style="border: none; padding: 0 10px"></button>
                <span></span>
                <div class="collapsible-content-box" style="max-height: fit-content">
                    <?php function gen_lists_of_links($n, $bdd, $user_id_in, $is_admin_in){
                        $query = 'SELECT * FROM sourcelists WHERE father = :father AND id <> 0 AND owner = :owner AND status = 1 ORDER BY lastuse DESC';
                        $stmt = $bdd->prepare($query);
                        $stmt->execute(['father' => $n, 'owner' => $user_id_in]);
                        $lists = $stmt->fetchAll();
                        foreach ($lists as $list) {?>
                            <?php
                            if ($n == -1) {
                                echo '<button class="new-button collapsible-box to_defilter"></button>' .
                                    '  ' . htmlspecialchars($list['name'], ENT_QUOTES) .
                                    ' <div class="glorix-button-div"> <button class="icon-button" title="Ouvrir tous les liens de la liste" onclick="openAllSources(' . htmlspecialchars($list['id']) . ')">' .
                                    '   <img src="Includes/Images/Icone Eye.png" alt="Ouvrir tous les liens de la liste" />' .
                                    '</button>'.
                                    ' <button class="edit-icon" title="Masquer cette liste" onclick="hideList(' . htmlspecialchars($list['id'], ENT_QUOTES) . ')">' .
                                    '   <img class="grayscaled" src="Includes/Images/Bouton%20Supprimer.png" alt="Masquer" />' .
                                    '</button></div>';
                            } else {
                                echo '<button class="new-button collapsible-box sub-button to_defilter" style="margin-bottom: 20px"></button>' .
                                    '  ' . htmlspecialchars($list['name'], ENT_QUOTES) .
                                    ' <div class="glorix-button-div"> <button class="icon-button" title="Ouvrir tous les liens de la liste" onclick="openAllSources(' . htmlspecialchars($list['id']) . ')">' .
                                    '   <img src="Includes/Images/Icone Eye.png" alt="Ouvrir tous les liens de la liste" />' .
                                    '</button>'.
                                    ' <button class="edit-icon" title="Masquer cette liste" onclick="hideList(' . htmlspecialchars($list['id'], ENT_QUOTES) . ')">' .
                                    '   <img class="grayscaled" src="Includes/Images/Bouton%20Supprimer.png" alt="Masquer" />' .
                                    '</button></div>';
                            } ?>
                            <div class="collapsible-content">
                                <?php echo '<ul id="ul-of-list-'.$list['id'].'">' ?>
                                <?php $req = "SELECT * FROM links WHERE list = ".$list['id']." AND visible = 1 ORDER BY lastopening DESC";
                                $links = $bdd->query($req)->fetchAll();
                                ?>
                                <?php foreach ($links as $linkcontent) {
//                                    echo var_dump($links);
                                    $linkid= $linkcontent['id'];
                                    if (isset($linkcontent['owner'])) {
                                        echo '<li id="source'.$linkid.'">';
                                        $linkDoc = isset($linkcontent['doc']) ? htmlspecialchars($linkcontent['doc'],ENT_QUOTES) : null;
                                        echo '<a target="_blank" href="' . htmlspecialchars($linkcontent['url']) . '" title="' . $linkDoc . '">' . htmlspecialchars($linkcontent['name']) . '</a>'; ?>
                                        <button id="edit-link-button" class="edit-icon" >
                                            <img src="Includes/Images/Bouton Modifier.png">
                                        </button>
                                        <button id="delete-source-button" class="edit-icon" onclick="hideLink(<?= $linkid ?>)">
                                            <img src="Includes/Images/Bouton%20Supprimer.png">
                                        </button>
                                        </li>
                                    <?php }
                                }
                                ?>
                                <?php   echo'<br style = "height : 20px">';
                                gen_lists_of_links($list['id'], $bdd, $user_id_in, $is_admin_in); ?>
                                </ul>
                                <ul>
                                    <?php echo '<button class="new-button small-button  to_defilter add-source-button" onclick="Add_Link('.$list['id'] .', '.$user_id_in.','.$is_admin_in.')"><img src="Includes\Images\Icone Lien.png" class="to_defilter">Ajouter un Lien</button>' ?>
                                    <?php if ($n != -1){
                                        echo '<hr style="width: 100%;">';
                                    }?>
                                </ul>
                            </div>
                            <?php if ($n == -1){
                                echo '<hr>';
                            }?>
                        <?php } ?>
                    <?php }?>
                    <?php gen_lists_of_links(-1, $bdd, $user_id, $user_admin); ?>
                </div>

            </div>

        </div>
    <br style="margin-bottom: 1em">
    <?php echo '<button class="new-button to_defilter add-source-button" onclick="Add_Link(-1, '.$user_id.','.$user_admin.')"><img src="Includes\Images\Icone Lien.png" class="to_defilter">Ajouter un Lien</button>' ?>
    <?php echo '<button class="new-button to_defilter add-tool-button" onclick="Add_List( -1, '.$user_id.', '.$user_admin.')" style="margin-left : 15px"><img src="Includes\Images\Icone Boite.png" class=" to_defilter">Nouvelle liste</button>' ?>
</div>

<hr>

<div class="EPIX MAIN">

    <div class="main-div-header"><span class="main-div-title">EPIX</span><span class="main-div-subtitle"> - Embasage Progressif des Informations</span></div>
    <hr id="EPIX-hr" class="main-div-hr">
    <?php
    if (isset($_SESSION['id_actif'])) {

        echo '
        <div class="EPIX-projects" style="display: flex; margin: 0 1vw">
            <div>
                <div>
                    <div style="display: flex; justify-content: space-around; margin-bottom: 10px; align-items: center;">
                        <h3>Mes Projets</h3>
                        <button class="new-button"> <a href="EPIX_gestionnaire.php">Nouveau projet</a></button>
                    </div>';

        $query = "SELECT * FROM projects WHERE owner = :user order by last_use DESC LIMIT 3";
        $stmt = $bdd->prepare($query);
        $stmt->execute(['user' => $_SESSION['id_actif']]);
        $projects = $stmt->fetchAll();

        foreach ($projects as $project) {
            echo '
                    <div style="display: flex; margin-bottom: 10px; align-items: center;">
                        <a class="EPIX-project" style="width: 100%" onclick= "redirection(' . $project['id'] .' )" title="' . $project["comments"] . '">
                        <img class="to_defilter" style="width:40%" src="' . $project["image"] . '">
                        <div class="EPIX-project-text" style="width:60%">
                            <div class="EPIX-project-title">' . $project["name"] . '</div>
                            <div class="EPIX-project-date">' . $project["last_use"] . '</div>
                        </div>
                        </a>
                    </div>
                    ';}
        echo '</div>';
        echo '<button class="new-button" type="button"> <a href="EPIX_gestionnaire.php">Voir plus</a></button>';

        echo '
                <div>
                    <h3>Projets partagés avec moi</h3>';

        $query = 'SELECT sharelist, id, comments, image, name, last_use FROM projects WHERE (owner <> :user AND owner <> -1) AND (sharelist IS NOT NULL AND sharelist <> \'{}\') ORDER BY last_use DESC LIMIT 3';
        $stmt = $bdd->prepare($query);
        $stmt -> execute(array(':user' => $_SESSION['id_actif']));
        $datas = $stmt->fetchAll();
        foreach ($datas as $data) {
            $list = json_decode($data['sharelist'], true);
            if (array_key_exists($_SESSION['id_actif'], $list) && $list[$_SESSION['id_actif']] === true)
            {
                echo '<a class="EPIX-project" style="width: 100%" onclick= "redirection(' . $data['id'] .' )" title="' . $data["comments"] . '">
                    <img class="to_defilter" style="width:40%" src="' . $data["image"] . '">
                    <div class="EPIX-project-text" style="width:60%">
                        <div class="EPIX-project-title">' . $data["name"] . '</div>
                        <div class="EPIX-project-date">Dernière modification : ' . $data["last_use"] . '</div>
                    </div>
                    </a>
                    ';            }
        }
        echo '</div>';

        echo '</div>';
    }
    else
    {
        echo '
            <div>
                    <h3>Démonstrations et tests</h3>';

        $query = "SELECT * FROM projects WHERE (id = 50)";
        $stmt = $bdd->prepare($query);
        $stmt -> execute([]);
        $datas = $stmt->fetchAll();
        foreach ($datas as $data)
        {
            echo '<a class="EPIX-project" style="width: 100%" onclick= "newVersionProject(' . $data['id'] .' )" title="' . $data["comments"] . '">
                    <img class="to_defilter" style="width:40%" src="' . $data["image"] . '">
                    <div class="EPIX-project-text" style="width:60%">
                        <div class="EPIX-project-title">' . $data["name"] . '</div>
                        <div class="EPIX-project-date">' . $data["last_use"] . '</div>
                    </div>
                    </a>
                   ';
        } echo '</div>';
    }
    ?>
</div>
</div>
</div>
<div id="success-message" class="success-message">L'opération a été réalisée avec succés</div>
<div id="failure-message" class="success-message" style="background-color: crimson">L'opération a échoué"</div>
<script>
    // Trier les sources à l'ouverture de la page
    document.addEventListener("DOMContentLoaded", function () {
        sortSources();
    })
</script>
<script src="Javascript.js"></script>
<script src="ToolEdits.js"></script>
<div class="popup" id="popup-tool">
    <?php require_once 'Includes/PHP/add_tool_popup.php';?>
</div>
<script src="SourceEdits.js"></script>
<div class="popup" id="popup-source">
    <!--    Généré initialement-->
    <?php require_once 'Includes/PHP/add_source_popup.php';?>
</div>
<?php require_once 'Includes/PHP/add_fav_popup.php';?>
<div class="tutorial-overlay"></div>
<div class="tutorial-popup"></div>
<div class="tutorial-menu">
    <button class="tutorial-toggle-btn" onclick="toggleTutorialMenu()">☰ Sommaire</button>

    <!-- Liseret visuel -->
    <div class="tutorial-liseret"></div>
    <div class="tutorial-menu-content">
        <!-- Le contenu est généré dynamiquement par Javascript -->
    </div>
</div>    <script src="TutorialScript.js"></script>


<!-- Modale Bootstrap -->
<div id="alertModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Signaler un outil</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Expliquez brièvement la raison de votre signalement (optionnel) :</p>
                <textarea id="alertMessage" class="form-control" style="width : 100%" rows="4" placeholder="Votre message ici..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="Alert_Source()">Envoyer le signalement</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'Includes/PHP/footer.php';?>

</body>
</html>