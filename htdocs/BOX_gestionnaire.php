<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> BOX Gestionnaire</title>
    <link rel="icon" href="Includes\Images\Icone Onglet.png"/>
    <link rel="stylesheet" href="STYLE\IndexStyle.css"/>
    <link rel="stylesheet" href="STYLE\GestionaireStyle.css"/>
    <link rel="stylesheet" href="STYLE\HeaderStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexEPIXStyle.css"/>
    <link rel="stylesheet" href="STYLE/IndexPopupStyle.css">
    <link rel="stylesheet" href="STYLE\FooterStyle.css"/>
    <link rel="stylesheet" href="STYLE\AdministrationStyle.css"/>

</head>
<body>
<?php require_once 'Includes/PHP/header.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.tab-button');
        const contents = document.querySelectorAll('.tab-content');

        buttons.forEach(button => {
            button.addEventListener('click', function () {
                // Remove active class from all buttons and contents
                buttons.forEach(b => b.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active-content'));

                // Add active class to clicked button and associated content
                this.classList.add('active');
                document.getElementById(this.dataset.tab).classList.add('active-content');
            });
        });
    });
</script>
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
} else {
    header('Location: profils.php');
}
?>

<?php
$categories = $bdd->query('SELECT * FROM toolscategories ORDER BY name ASC');
$categories = $categories->fetchAll(PDO::FETCH_ASSOC);

$toolsByCategory = [];
$query = $bdd->query('
    SELECT tools.*, toolscategories.name AS category_name
    FROM tools
    INNER JOIN toolscategories ON tools.category_id = toolscategories.id
    ORDER BY toolscategories.name ASC
');
while ($tool = $query->fetch(PDO::FETCH_ASSOC)) {
    $toolsByCategory[$tool['category_name']][] = $tool;
}

function getFullCategoryHierarchy($parentId, $bdd, &$hierarchy = []) {
    // Chercher les sous-catégories de la catégorie actuelle
    $query = $bdd->prepare('SELECT id FROM toolscategories WHERE parent = :parentId');
    $query->execute(['parentId' => $parentId]);
    $subcategories = $query->fetchAll(PDO::FETCH_COLUMN);

    // Initialiser la clé de la catégorie actuelle avec les sous-catégories directes
    if (!isset($hierarchy[$parentId])) {
        $hierarchy[$parentId] = [];
    }

    // Ajouter les sous-catégories directes dans la clé du parent
    foreach ($subcategories as $subcatId) {
        $hierarchy[$parentId][] = $subcatId; // Ajouter le fils direct

        // Appel récursif pour trouver les descendants des sous-catégories
        getFullCategoryHierarchy($subcatId, $bdd, $hierarchy);

        // Ajouter les descendants indirects
        if (isset($hierarchy[$subcatId])) {
            $hierarchy[$parentId] = array_merge($hierarchy[$parentId], $hierarchy[$subcatId]);
        }
    }

    return $hierarchy;
}
// Récupérer la hierarchie des catégories pour pouvoir effectuer un tri par catégorie grâce à AJAX par la suite
$FullCategoryHierarchy = getFullCategoryHierarchy(0, $bdd);


// Récupérer les hashtags depuis la table `toolstags`
$query = $bdd->query("SELECT id, name FROM toolstags");
$hashtags = $query->fetchAll(PDO::FETCH_ASSOC); // Récupère tous les hashtags sous forme de tableau associatif

if (!isset($_SESSION['id_actif'])) {
    header('Location: profils.php');
} else { ?>

    <div class="tab-container" style="display: unset !important">
        <div class="tab-buttons">
            <button class="tab-button active" data-tab="search">Rechercher</button>
            <button class="tab-button" data-tab="navigate">Naviguer</button>
            <button class="tab-button" data-tab="add">Ajouter</button>
            <button class="tab-button" data-tab="all-tags">Tags</button>
        </div>
        <div class="tab-contents">
            <div id="search" class="tab-content active-content">
                <h3>Rechercher des outils</h3>
                <div class="search-filters">
                    <div class="search-filters-left">
                        <div id="checkboxes">
                            <div class="checkbox-container">
                                <input type="checkbox" class="checkbox-recomended" value="recomended" id="checkbox-recomended"/>
                                <label for="checkbox-recomended"><button class="edit-icon" style="margin-left: 0"><img src="Includes/Images/Icone%20Star.png"></button> Outils recommandés</label>
                                <input type="checkbox" class="checkbox-mine" value="mine" id="checkbox-mine"/>
                                <label for="checkbox-mine"><button class="edit-icon" style="margin-left: 0"> <img src="Includes/Images/Icone%20checked.png"></button> Seulement mes outils</label>
                                <input type="checkbox" class="checkbox-hidden" id="checkbox-hidden"/>
                                <label for="checkbox-hidden"><button class="edit-icon" style="margin-left: 0"> <img src="Includes/Images/Icone%20Plus.png"></button> Seulement les outils cachés</label>
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center; margin-top: 2em">
                            <label for="tag-input-bar-box" style="text-align: left; padding: 5px; color: rgb(91,98,170)">Chercher un tag :</label>
                            <input class="unbordered-modern" type="text" id="tag-input-bar-box" style="display: flex; max-width: 10vw;" placeholder="Chercher un tag">
                        </div>
                        <div style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center; margin-top: 2em">
                            <label for="hashtag-buttons-container" style="text-align: left; padding: 5px; color: rgb(91,98,170)">Filtrer par tags :</label>
                            <div id="hashtag-buttons-container" style="display: flex; max-width: 35vw; flex-wrap: wrap;"></div>
                        </div>
                        <div style="display: flex; gap: 10px; margin-top: 10px; margin-bottom: 10px; align-items: center;">
                            <label for="selected-tags-list" style="text-align: left; padding: 5px; color: rgb(91,98,170)">Tags sélectionnés :</label>
                            <div id="selected-tags-list">
                                <!-- Les tags sélectionnés apparaîtront ici -->
                            </div>
                            <button class="clear-button hidden" id="clear-selected-tags" title="Supprimer tous les tags">✖</button>
                        </div>

                        <div style='margin-top: 20px; margin-bottom: 20px;'>
                            <label for="start-date"><span style="font-weight : bold">Date de début :</span></label>
                            <input class="unbordered-modern" type="date" id="start-date"/>
                            <button class="clear-button hidden" id="clear-start-date">
                                ✖
                            </button>
                            <label for="end-date"><span style="font-weight : bold">Date de fin :</span></label>
                            <input class="unbordered-modern" type="date" id="end-date"/>
                            <button class="clear-button hidden" id="clear-end-date">
                                ✖
                            </button>
                        </div>
                        <div style='margin-top: 20px; margin-bottom: 20px;'>
                            <label for="sort_selection"><span style="font-weight : bold">Trier par :</span></label>
                            <select class="unbordered-modern" id="sort_selection">
                                <option value="category">Catégories</option>
                                <option value="date-asc">Date croissante</option>
                                <option value="date-desc">Date décroissante</option>
                                <option value="name-asc">Nom croissant</option>
                                <option value="name-desc">Nom décroissant</option>
                            </select>
                        </div>
                        <div style="margin-top: 40px">
                            <input type="text" id="search-bar" style="color: white" placeholder="Rechercher dans la table"/>
                            <img src="Includes/Images/Search Icon.png" style="width: 25px; height: 25px; position: relative; left: -40px; top: 7px;">
                            <button class="clear-button hidden" id="clear-search-bar">✖</button>
                        </div>
                    </div>
                    <div class="search-filters-right">
                        <label><span style="font-weight : bold">Filtres de catégorie :</span></label>
                        <div id="category-checkboxes" style="margin-bottom: 1em">
                            <?php function gen_checkboxes_for_categories($parent, $bdd, $temp)
                            {
                                $req = 'SELECT * FROM toolscategories WHERE parent = :parent';
                                $stmt = $bdd->prepare($req);
                                $stmt->execute(['parent' => $parent]);
                                $categories = $stmt->fetchAll();
                                foreach ($categories as $category) {
                                    echo '<span style="margin-left: ' . strval($temp * 2) . 'em"></span>';
                                    echo '<input type="checkbox" value="' . $category['id'] . '" id="tool-cat-' . $category['id'] . '">';
                                    echo '<label for="tool-cat-' . $category['id'] . '">' . $category['name'] . '</label><br>';
                                    gen_checkboxes_for_categories($category['id'], $bdd, $temp + 1);
                                }
                            }

                            gen_checkboxes_for_categories(0, $bdd, 0);
                            ?>
                        </div>
                        <button id="export-excel-button" style="background-color: rgba(51,110,202,0.7);">Ouvrir dans Excel</button>
                    </div>
                </div>

                <div class="search-results">
                    <h3>Résultats</h3>
                    <table id="results-table" border="1">
                        <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th style="text-align : center">Image</th>
                            <th style="text-align: center;">Nom</th>
                            <th>Description</th>
                            <th>Url</th>
                            <th>Tags</th>
                            <th>Date d'insertion</th>
                        </tr>
                        </thead>
                        <tbody id="results-tbody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="navigate" class="tab-content">
                <h3 style="font-weight: bold; text-transform: uppercase">MOn arborescence : </h3>
                <div class="checkbox-container">
                    <input type="checkbox" class="checkbox-mine" value="mine" id="checkbox-navigate-mine"/>
                    <label for="checkbox-navigate-mine">
                        <button class="edit-icon" style="margin-left: 0">
                            <img src="Includes/Images/Icone%20checked.png">
                        </button> Seulement mes outils
                    </label>

                    <input type="checkbox" class="checkbox-recommended" value="recommended" id="checkbox-navigate-recommended"/>
                    <label for="checkbox-navigate-recommended">
                        <button class="edit-icon" style="margin-left: 0">
                            <img src="Includes/Images/Icone%20Star.png">
                        </button> Outils recommandés
                    </label>

                    <input type="checkbox" class="checkbox-new" value="new" id="checkbox-navigate-new"/>
                    <label for="checkbox-navigate-new">
                        <button class="edit-icon" style="margin-left: 0">
                            <img src="Includes/Images/Icone%20Plus.png">
                        </button> Nouveaux outils
                    </label>
                </div>
                <div class="collapsible-matrix" id="tools-matrix" style="padding-left: 25px; padding-top: 35px;">
                    <h3><b>Résultat : </b></h3>
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
                            <?php // On crée dabord les
                            if ($n == 0) {  // Distinction des catégories principales et des sous-catégories pour des besoins de style
                                echo '<button class="new-button collapsible collapsible-active to_defilter"></button>' . '  ' . strtoupper($category['name']);
                            } else {
                                echo '<button class="new-button collapsible collapsible-active sub-button to_defilter"></button>'.'  '.$category['name']; // sub-button est un style propre aux sous-catégories
                            } ?>
                            <!-- Les fonctions javascript appelés par ces différents boutons sont à retrouver dans le fichier ToolEdits.js -->
                            <div class="collapsible-content" style="background-color: #dbecfdab;">
                                <?php echo '<ul id="tool-ul-of-'.$category['id'].'">' ?>
                                <?php  gen_category_list_tool($category['id'], $bdd, $user_id_in, $is_admin_in );
                                $sources = json_decode($category['liens'], true);?>
                                <?php foreach ($sources as $source) {
                                    $tool = $bdd->query("SELECT * FROM tools WHERE id =".$source)->fetch();
                                    if (isset($tool['user'.$user_id_in]) && $tool['user'.$user_id_in] == 1) {
                                        echo '<li id="tool'.$tool['id'].'">';
                                        echo '<a target="_blank" href="' . htmlspecialchars($tool['url'],ENT_QUOTES) . '" title="' . htmlspecialchars($tool['doc']) . '">' . htmlspecialchars($tool['name']) . '</a>'; ?>
                                        <?php if($_SESSION['is_admin']) { ?><button id="edit-tool-button" class="edit-icon"
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
                                            <img src="Includes/Images/Bouton Modifier.png"> Modifier l'outil
                                        </button> <?php } ?>

                                        <button id="delete-tool-button" class="edit-icon"
                                                onclick="Edit_Tool_Gest(
                                                    -1,
                                                <?= htmlspecialchars($user_id_in,ENT_QUOTES) ?>,
                                                <?= htmlspecialchars(json_encode($tool['name'], JSON_HEX_TAG | JSON_HEX_QUOT),ENT_QUOTES) ?>,
                                                <?= htmlspecialchars($tool['id'],ENT_QUOTES) ?>, 1)">
                                            <img src="Includes/Images/Bouton%20Supprimer.png"> Supprimer de mon arborescence
                                        </button>
                                        <button id="add-tool-button" class="edit-icon" onclick="document.getElementById('gest-popup-<?php echo $tool['id']?>').style.display = 'inline-block'" onblur="document.getElementById('gest-popup-<?php echo $tool['id']?>').style.display = 'none'">
                                            <img src="Includes/Images/Loupe.png"> Voir les détails
                                        </button>
                                        <div id="gest-popup-<?php echo $tool['id']?>" class="gest-popup"> <label for="tool-name">Nom de l'outil : </label>
                                            <span id="tool-name"><?php echo $tool['name']?></span><br><br>
                                            <label for="tool-url">Lien url : </label>
                                            <span id="tool-url"><?php echo $tool['url']?></span><br><br>
                                            <div style="display: flex">
                                                <label for="tool-doc" style="margin-right: 5px">Description : </label>
                                                <span><?php echo $tool['doc']?></span>
                                            </div><br>
                                            <label for="tool-category">Catégorie : </label>
                                            <span id="tool-category"><?php echo $category['name']?></span>
                                            <br><br>
                                            <div id="tool-tags" style="display: flex">
                                                <label for="tool-tags" style="margin-right : 10px">Tags : </label>
                                                <div id="view_tags_list_content">
                                                    <?php $req = $bdd->prepare('SELECT tags FROM tools WHERE id = :tool_id');
                                                    $req->execute(['tool_id' => $tool['id']]);
                                                    $set_tags = $req->fetch();
                                                    $set_tags = json_decode($set_tags['tags']);
                                                    foreach ($set_tags as $tag) {
                                                        $req = $bdd->prepare('SELECT name FROM toolstags WHERE id = :tag_id');
                                                        $req->execute(['tag_id' => $tag]);
                                                        $tag_name = $req->fetch();
                                                        $tag_name = $tag_name['name'];
                                                        echo '<button type="button" id="view-active-tool-tag-'.strval($tag).'" class="view-active-tag">#'.$tag_name.'</button>';
                                                    };?>
                                                </div>
                                            </div><br><br>
                                            <?php if ($tool['image'] and $tool['image'] != 'Fav_Icons/Default_Tool.png' ) {
                                                echo '<div style="display : flex; align-content: flex-start"><span>Image : </span>
                                                    <span id="tool-image"><img src="'.$tool["image"].'" style="width : 60px"></span></div>';}
                                            ?>
                                        </div>
                                    <?php }
                                    elseif (isset($tool['user'.$user_id_in]))  {
                                        $tool = $bdd->query("SELECT * FROM tools WHERE id =".$source)->fetch();
                                        echo '<li id="tool'.$tool["id"].'" class="unactive-tool">';
                                        echo '<a target="_blank" href="' . htmlspecialchars($tool['url'],ENT_QUOTES) . '" title="' . htmlspecialchars($tool['doc']) . '">' . htmlspecialchars($tool['name']) . '</a>'; ?>
                                        <button id="add-tool-button" class="edit-icon" onclick="Edit_Tool_Gest(1, <?php echo $user_id_in. ', \'' . $tool['name'] .'\', '.$tool['id'].', 1'?>)">
                                            <img src="Includes/Images/Icone%20Plus.png"> Ajouter à mon arborescence
                                        </button>
                                        <button id="add-tool-button" class="edit-icon" onclick="document.getElementById('gest-popup-<?php echo $tool['id']?>').style.display = 'inline-block'" onblur="document.getElementById('gest-popup-<?php echo $tool['id']?>').style.display = 'none'">
                                            <img src="Includes/Images/Loupe.png"> Voir les détails
                                        </button>
                                        <div id="gest-popup-<?php echo $tool['id']?>" class="gest-popup"> <label for="tool-name">Nom de l'outil : </label>
                                            <span id="tool-name"><?php echo $tool['name']?></span><br><br>
                                            <label for="tool-url">Lien url : </label>
                                            <span id="tool-url"><?php echo $tool['url']?></span><br><br>
                                            <div style="display: flex">
                                                <label for="tool-doc" style="margin-right: 5px">Description : </label>
                                                <span><?php echo $tool['doc']?></span>
                                            </div><br>
                                            <label for="tool-category">Catégorie : </label>
                                            <span id="tool-category"><?php echo $category['name']?></span>
                                            <br><br>
                                            <div id="tool-tags" style="display: flex">
                                                <label for="tool-tags">Tags : </label>
                                                <div id="view_tags_list_content">
                                                    <?php $req = $bdd->prepare('SELECT tags FROM tools WHERE id = :tool_id');
                                                    $req->execute(['tool_id' => $tool['id']]);
                                                    $set_tags = $req->fetch();
                                                    $set_tags = json_decode($set_tags['tags']);
                                                    foreach ($set_tags as $tag) {
                                                        $req = $bdd->prepare('SELECT name FROM toolstags WHERE id = :tag_id');
                                                        $req->execute(['tag_id' => $tag]);
                                                        $tag_name = $req->fetch();
                                                        $tag_name = $tag_name['name'];
                                                        echo '<button type="button" id="view-active-tool-tag-'.strval($tag).'" class="view-active-tag">#'.$tag_name.'</button>';
                                                    };?>
                                                </div>
                                            </div><br>
                                            <?php if ($tool['image'] and $tool['image'] != 'Fav_Icons/Default_Tool.png' ) {
                                                echo '<label for="tool-image">Image : </label>
                                                    <span id="tool-image"><img src="'.$tool["image"].'" style="width : 60px"></span>';}
                                            ?>
                                        </div>
                                    <?php }
                                    else {
                                        // Supprimer l'identifiant de la table si aucun outil n'y corresponds
                                        $newSources = $sources;
                                        unset($newSources[$source]);
                                        // Mettre à jour la liste des sources pour la catégorie, en excluant les orphelins
                                        $updateQuery = $bdd->prepare('UPDATE toolscategories SET liens = :liens WHERE id = :id');
                                        $updateQuery->execute([
                                            'liens' => json_encode($newSources), // Liste nettoyée
                                            'id' => $category['id']
                                        ]);
                                    }
                                }
                                ?>
                                </ul>
                                <ul>
                                    <?php echo '<button class="new-button small-button add-tool-button" onclick="Add_Tool('.$category["id"].', '.$user_id_in.', '.$is_admin_in.')"><img src="Includes\Images\Icone Plus.png" class="small-button to_defilter">Nouvel outil</button>' ?>
                                    <?php if ($n != 0){
                                        echo '<hr style="width: 100%; margin:1%; padding:0">';
                                    }?>
                                </ul>
                            </div>
                            <?php if ($n == 0){
                                echo '<hr style="margin : 1%; padding: 0">';
                            }?>
                        <?php } ?>
                    <?php }?>
                    <?php gen_category_list_tool(0, $bdd, $user_id, $user_admin); ?>
                    <?php echo '<button class="new-button to_defilter add-tool-button" onclick="Add_Tool(0, '.$user_id.', '.$user_admin.')"><img src="Includes\Images\Icone Plus.png" class=" to_defilter">Nouvel outil</button>' ?>
                </div>

            </div>
            <div id="add" class="tab-content">
                <h2>Ajouter plusieurs outils avec des tags :</h2>

                <!-- Tableau pour les outils -->
                <table id="tools-table" class="adminTable" style="width: 100%; background-color: #efefef; border-collapse: collapse;">
                    <thead>
                    <tr>
                        <th>Nom de l'outil</th>
                        <th>Lien URL</th>
                        <th>Description</th>
                        <th>Catégorie</th>
                        <th>Tags</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Ligne initiale (exemple) -->
                    <tr class="tool-row">
                        <td><input type="text" name="tool_name[]" class="tool-name" required placeholder="Nom de l'outil"></td>
                        <td><input type="url" name="tool_url[]" class="tool-url" required placeholder="http://"></td>
                        <td><textarea name="tool_doc[]" class="tool-doc" style="background-color: transparent; border: none" required placeholder="Description"></textarea></td>
                        <td>
                            <select name="tool_category[]" class="tool-category" required>
                                <option value="">Choisir une catégorie</option>
                                <?php
                                $query = 'SELECT * FROM toolscategories';
                                $stmt = $bdd->prepare($query);
                                $stmt->execute();
                                $toolscategories = $stmt->fetchAll();
                                foreach ($toolscategories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <!-- Colonne des Tags -->
                        <td>
                            <input type="text" class="tool-tags-input" placeholder="Chercher ou ajouter un tag" onkeyup="suggestTags(this, this.nextElementSibling)">
                            <div class="suggested-tags" style="display: flex; flex-wrap: wrap; margin-top: 5px;">
                                <!-- Suggestions de tags dynamiques -->
                            </div>
                            <div class="active-tags" style="margin-top: 10px; display: flex; flex-wrap: wrap;">
                                <!-- Liste des tags actifs -->
                            </div>
                        </td>
                        <td><button type="button" class="remove-row-btn">Supprimer</button></td>
                    </tr>
                    </tbody>
                </table>

                <!-- Boutons d'action -->
                <button type="button" id="add-row-btn">Ajouter une ligne</button>
                <button type="button" id="submit-tools-btn">Soumettre</button>
            </div>
            <div id="all-tags" class="tab-content" style="width: 100%">
                <div style="padding: 2%">
                    <h2>Tags : </h2>
                    <?php $req = $bdd->query('SELECT * FROM toolstags ORDER BY type');
                    $tags = $req->fetchAll();
                    $tagtype = "";
                    echo '<div>';
                    foreach ($tags as $tag) {
                        if ($tag['type'] != $tagtype) {
                            echo '</div>';
                            echo '<h4>' . htmlspecialchars($tag['type'], ENT_QUOTES) . '</h4>';
                            $tagtype = $tag['type'];
                            echo '<div style="display: flex; flex-wrap: wrap; justify-content: left; padding: 10px;">';
                        }
                        echo '<div class="tag-div">';
                        echo '<span>' . htmlspecialchars($tag['name'], ENT_QUOTES) . '</span>';
                        echo '</div>';
                    }
                    ?>
                </div>

            </div>
        </div>
    </div>

<?php } ?>

<?php require_once 'Includes/PHP/footer.php'; ?>
<script>

    // Traitement des collapsibles pour l'onglet naviguer
    var coll = document.getElementsByClassName("collapsible");
    var i;

    for (i = 0; i < coll.length; i++) {
        coll[i].addEventListener("click", function() {
            this.classList.toggle("collapsible-active");
            var content = this.nextElementSibling;
            if (content.style.maxHeight){
                content.style.maxHeight = null;
            } else {
                for (i = 0; i < coll.length; i++) {
                    coll[i].style.maxHeight = 'unset';
                }
                content.style.maxHeight = 'fit-content';
            }
        });
    }

    coll_content = document.querySelectorAll(".collapsible-content");
    var i;
    for (i = 0; i < coll_content.length; i++) {
            coll_content[i].style.maxHeight = 'fit-content';
    }

</script>
<script>

    // Attendre que le DOM soit complètement chargé
    // document.addEventListener('DOMContentLoaded', function () {

    xhreq = new XMLHttpRequest();
    xhreq.onreadystatechange = function () {
        if (xhreq.readyState == 4 && xhreq.status == 200) {
            document.getElementById("results-tbody").innerHTML = this.responseText;
        }
    }
    xhreq.open("POST", "Includes/PHP/BOX_Gest_bdd.php", true);
    xhreq.send();

    // Attach event listeners to filters
    document.getElementById("checkbox-mine").addEventListener("change", toggleConflictCheckboxes);
    document.getElementById("checkbox-hidden").addEventListener("change", toggleConflictCheckboxes);
    document.getElementById("start-date").addEventListener("change", updateResults);
    document.getElementById("end-date").addEventListener("change", updateResults);
    document.getElementById("search-bar").addEventListener("input", updateResults);
    document.getElementById("sort_selection").addEventListener("change", updateResults);


    document.querySelectorAll("#category-checkboxes input").forEach(checkbox => {
        checkbox.addEventListener("change", function () {
            toggleSubCategories(this.value); // Utilise la valeur de la checkbox cliquée
        });
    });
    document.querySelectorAll("#checkboxes input").forEach(checkbox => {
        checkbox.addEventListener("change", updateResults);
    });

    function toggleSubCategories($category_id) {
        console.log($category_id);
        const fullHierarchy = JSON.parse(<?php echo '\''.json_encode($FullCategoryHierarchy).'\''; ?>);
        const subCategories = fullHierarchy[$category_id];
        subCategories.forEach(subCategory => {
            const checkbox = document.getElementById("tool-cat-" + subCategory);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
        console.log('test');
        updateResults();
    }
    function toggleConflictCheckboxes() {
        const mineCheckbox = document.getElementById("checkbox-mine");
        const hiddenCheckbox = document.getElementById("checkbox-hidden");

        if (this === mineCheckbox && mineCheckbox.checked) {
            hiddenCheckbox.checked = false;
        } else if (this === hiddenCheckbox && hiddenCheckbox.checked) {
            mineCheckbox.checked = false;
        }
    }

    function updateResults() {
        const sort_selection = document.getElementById("sort_selection").value;
        const startDate = document.getElementById("start-date").value;
        const endDate = document.getElementById("end-date").value;
        const searchText = document.getElementById("search-bar").value;

        // Obtenir les catégories sélectionnées
        const selectedCategories = Array.from(
            document.querySelectorAll("#category-checkboxes input:checked")
        ).map(cb => cb.value);

        // Obtenir les hashtags sélectionnés
        const selectedTags = Array.from(
            document.querySelectorAll("#selected-tags-list .selected-tag")
        ).map(tag => tag.id.replace("selected-tag-", ""));

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "Includes/PHP/BOX_Gest_bdd.php", true);
        xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById("results-tbody").innerHTML = xhr.responseText;
            }
        };

        xhr.send(JSON.stringify({
            startDate,
            endDate,
            searchText,
            sort_selection,
            categories: selectedCategories,
            mine: document.getElementById("checkbox-mine").checked,
            hidden: document.getElementById("checkbox-hidden").checked,
            recommended: document.getElementById("checkbox-recomended").checked,
            selected_tags: selectedTags // Inclure les hashtags sélectionnés
        }));
    }


    //Code JavaScript pour la navigation en onglets
    const tabs = document.querySelectorAll(".tab-button");
    const tabContents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            // Supprimer les classes "active" des autres onglets
            tabs.forEach(t => t.classList.remove("active"));
            tabContents.forEach(c => c.classList.remove("active-content"));

            // Ajouter la classe "active" à l'onglet sélectionné
            this.classList.add("active");
            const targetContent = document.querySelector(`#${this.dataset.tab}`);
            targetContent.classList.add("active-content");
        });
    });

    function Edit_Tool_Gest($flag, $user_id, $name, $tool_id, $nav=0) {
        if ($flag == 1) {
            $continue = window.confirm('Voulez-vous ajouter l\'outil ' + $name + ' à votre arborescence ?');
        }
        else if ($flag == -1) {
            $continue = window.confirm('Voulez-vous supprimer l\'outil ' + $name + ' de votre arborescence ?');
        }
        if ($continue == true) {
            xhr = new XMLHttpRequest();
            xhr.open('POST', 'Includes/PHP/tool_tags.php', true);
            data = new FormData();
            data.append('key', 'get_added_tags');
            data.append('tool_id', $tool_id);
            xhr.send(data);
            xhr.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    added_tags = JSON.parse(this.responseText);
                }
            }
            if (!added_tags) {
                let added_tags = [];
            }
            xhreq = new XMLHttpRequest();
            xhreq.open('POST', 'Includes/PHP/BOX_Gest_bdd.php', true);
            xhreq.onreadystatechange = function () {
                if (xhreq.readyState == 4 && xhreq.status == 200) {
                    updateResults();
                    console.log('La page a été rafraichie')
                    if ($nav) {location.reload();}
                }
                else if (xhreq.readyState == 4 && xhreq.status != 200) {
                    BOX_Gest_Failure_Message("La modification n'a pas été appliquée")
                }
            }
            data = new FormData();
            data.append('editing', $flag);
            data.append('user_id', $user_id);
            data.append('tool_id', $tool_id);
            xhreq.send(data);
        }
    }

    function BOX_Gest_Failure_Message($string) {
        console.log('Failure_Message(' + $string +') a été appelé.')
        const message = document.getElementById('BOX-Gest-failure-message');
        if (message) {
            message.style.opacity = '1';
            message.innerHTML = $string;
            message.style.display = 'flex';

            setTimeout(() => {
                setTimeout(() => message.style.display = 'none', 500); // Masquer complètement après la transition
            }, 3000);
        }
    }

    document.getElementById("export-excel-button").addEventListener("click", function () {
        const table = document.getElementById("results-table"); // ID de la table HTML
        const rows = table.querySelectorAll("tr");
        let excelContent = "<table>";

        // Ajouter les lignes de la table HTML au format Excel
        rows.forEach(row => {
            excelContent += "<tr>";
            const cells = row.querySelectorAll("th, td"); // Ajouter les cellules d'en-têtes et de corps
            cells.forEach(cell => {
                excelContent += `<td>${cell.innerHTML}</td>`; // Inclure HTML brut pour Excel
            });
            excelContent += "</tr>";
        });

        excelContent += "</table>";

        // Générer un fichier Blob compatible Excel
        const blob = new Blob(
            [
                `
            <html xmlns:o="urn:schemas-microsoft-com:office:office"
                  xmlns:x="urn:schemas-microsoft-com:office:excel"
                  xmlns="http://www.w3.org/TR/REC-html40">
            <head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
            <x:Name>Table</x:Name>
            <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>
            </x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>
            <body>${excelContent}</body></html>
            `
            ],
            { type: "application/vnd.ms-excel;charset=utf-8;" }
        );

        // Créer un lien pour forcer le téléchargement ou ouverture dans Excel
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "export-table.xls"; // Nom du fichier Excel
        link.style.display = "none";

        document.body.appendChild(link); // Ajouter le lien temporairement au DOM
        link.click(); // Simuler un clic pour télécharger ou ouvrir Excel
        document.body.removeChild(link); // Supprimer le lien
    });

    document.addEventListener('DOMContentLoaded', function () {
        const hashtags = <?= json_encode($hashtags, JSON_HEX_TAG | JSON_HEX_QUOT); ?>;
        console.log(hashtags);
        const selectedTagsList = document.getElementById("selected-tags-list");
        const hashtagButtonsContainer = document.getElementById("hashtag-buttons-container");
        const searchBar = document.getElementById("search-bar");

        // Gérer le clic sur les hashtags
        function toggleHashtag(tagId, tagName) {
            const existingTag = document.querySelector(`#selected-tag-${tagId}`);
            const existingTagInSuggestions = document.querySelector(`#available-tag-${tagId}`);

            if (existingTag) {
                // Si le tag est déjà sélectionné, le retirer
                existingTag.remove();

                // Ajouter le tag dans la liste suggérée à sa place initiale
                if (!existingTagInSuggestions) {
                    // Créer le bouton pour le tag dans les suggestions
                    const tagButton = document.createElement("button");
                    tagButton.id = `available-tag-${tagId}`;
                    tagButton.className = "tag-div";
                    tagButton.textContent = `#${tagName}`;
                    tagButton.onclick = () => toggleHashtag(tagId, tagName);

                    // Identifier la position initiale du tag
                    const indexInHashtags = hashtags.findIndex(h => h.id == tagId); // Trouve l'index du tag
                    const currentButtons = Array.from(document.querySelectorAll("#hashtag-buttons-container .tag-div"));

                    // Ajouter dans le DOM à la bonne position
                    if (indexInHashtags >= currentButtons.length) {
                        // Si la position dans "hashtags" est après la fin, l'ajouter à la fin
                        hashtagButtonsContainer.appendChild(tagButton);
                    } else {
                        // Sinon, insérer à la bonne position
                        hashtagButtonsContainer.insertBefore(tagButton, currentButtons[indexInHashtags]);
                    }
                }

            } else {
                // Si le tag n'est pas sélectionné, l'ajouter
                const newTagButton = document.createElement("button");
                newTagButton.id = `selected-tag-${tagId}`;
                newTagButton.className = "selected-tag";
                newTagButton.textContent = `#${tagName}`;
                newTagButton.onclick = () => toggleHashtag(tagId, tagName);
                selectedTagsList.appendChild(newTagButton);

                // Supprimer le tag de la liste des suggestions
                const buttonToRemove = document.querySelector(`#available-tag-${tagId}`);
                if (buttonToRemove) buttonToRemove.remove();
            }

            // Rafraîchir les résultats
            updateResults();
        }        // Générer les boutons des hashtags au chargement

        hashtags.forEach(({id, name}) => {
            const button = document.createElement("button");
            button.id = `available-tag-${id}`;
            button.className = "tag-div";
            button.textContent = `#${name}`;
            button.onclick = () => toggleHashtag(id, name);
            hashtagButtonsContainer.appendChild(button);
        });

        // Fonction pour rafraîchir les événements après mise à jour dynamique
        function rebindEvents() {
            hashtags.forEach(({id, name}) => {
                const button = document.getElementById("available-tag-" + id);
                if (button) {
                    button.id = `available-tag-${id}`;
                    button.className = "tag-div";
                    button.textContent = `#${name}`;
                    button.onclick = () => toggleHashtag(id, name);
                    hashtagButtonsContainer.appendChild(button);
                }
            });
        }

        // Fonction pour mettre à jour les suggestions en fonction de l'input
        function fetchHashtags(inputValue) {
            // Préparer la requête AJAX
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "Includes/PHP/tool_tags.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            // Définir les données
            const toolName = ""; // Récupérez ces valeurs dynamiquement si nécessaire
            const toolDoc = "";
            const toolCategory = "";
            const userId = 1; // Ajoutez l'ID utilisateur si disponible
            const memory = JSON.stringify(Array.from(selectedTagsList.querySelectorAll(".selected-tag")).map(tag => tag.dataset.id));

            const data = `key=suggest_tool_tags_gest&input='${encodeURIComponent(inputValue)}'&tool_name=${encodeURIComponent(toolName)}&tool_doc=${encodeURIComponent(toolDoc)}&tool_category=${encodeURIComponent(toolCategory)}&user_id=${encodeURIComponent(userId)}&memory=${encodeURIComponent(memory)}`;

            // Envoyer la requête
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Insérer les hashtags reçus dans le conteneur
                    hashtagButtonsContainer.innerHTML = xhr.responseText;
                    rebindEvents();
                } else {
                    console.error("Erreur lors du chargement des hashtags :", xhr.status);
                }
            };

            xhr.send(data);
        }

        // Ajoutez un écouteur pour détecter les changements dans la barre de recherche
        searchBar.addEventListener("input", function () {
            const inputValue = searchBar.value.trim();
            fetchHashtags(inputValue);
        });

        // Références aux champs et boutons
        const clearSearchBar = document.getElementById("clear-search-bar");

        const clearSelectedTags = document.getElementById("clear-selected-tags");

        const startDate = document.getElementById("start-date");
        const clearStartDate = document.getElementById("clear-start-date");

        const endDate = document.getElementById("end-date");
        const clearEndDate = document.getElementById("clear-end-date");

        // Fonction pour afficher/masquer un bouton en fonction de la valeur d'un champ
        function toggleClearButton(input, button) {
            if (input.value || (input.children && input.children.length > 0)) {
                button.classList.remove("hidden");
            } else {
                button.classList.add("hidden");
            }
        }

        // Barre de recherche : Vérification en temps réel
        searchBar.addEventListener("input", function () {
            toggleClearButton(searchBar, clearSearchBar);
        });

        // Tags sélectionnés : Vérification à chaque changement
        function toggleTagsClearButton() {
            if (selectedTagsList.children.length > 0) {
                clearSelectedTags.classList.remove("hidden");
            } else {
                clearSelectedTags.classList.add("hidden");
            }
        }

        // Attache à la fonction principale lors de mises à jour
        clearSelectedTags.addEventListener("click", function () {
            // Supprimer tous les tags
            const selectedTags = selectedTagsList.querySelectorAll(".selected-tag");
            selectedTags.forEach(tag => tag.remove());
            toggleTagsClearButton(); // Vérification après suppression
            updateResults(); // Mettre à jour les résultats
        });

        // Dates : Vérification en temps réel
        function attachDateInputListeners(dateInput, clearButton) {
            dateInput.addEventListener("input", function () {
                toggleClearButton(dateInput, clearButton);
            });
        }

        attachDateInputListeners(startDate, clearStartDate);
        attachDateInputListeners(endDate, clearEndDate);

        // Ajouter les actions aux boutons de suppression
        clearSearchBar.addEventListener("click", function () {
            searchBar.value = ""; // Réinitialiser
            toggleClearButton(searchBar, clearSearchBar); // Masquer le bouton
            updateResults(); // Mettre à jour les résultats
        });

        clearStartDate.addEventListener("click", function () {
            startDate.value = ""; // Réinitialiser
            toggleClearButton(startDate, clearStartDate); // Masquer le bouton
            updateResults(); // Mettre à jour les résultats
        });

        clearEndDate.addEventListener("click", function () {
            endDate.value = ""; // Réinitialiser
            toggleClearButton(endDate, clearEndDate); // Masquer le bouton
            updateResults(); // Mettre à jour les résultats
        });

        // Vérifiez les tags sélectionnés au démarrage
        toggleTagsClearButton();


        const tabs = document.querySelectorAll(".tab-button");
        const tabContents = document.querySelectorAll(".tab-content");

        // 1. Vérifiez si un onglet actif est stocké dans localStorage
        const activeTab = localStorage.getItem("activeTab");

        if (activeTab) {
            // Si une valeur est trouvée, activez l'onglet correspondant
            tabs.forEach(tab => tab.classList.remove("active"));
            tabContents.forEach(content => content.classList.remove("active-content"));

            const targetTabButton = document.querySelector(`.tab-button[data-tab="${activeTab}"]`);
            const targetTabContent = document.getElementById(activeTab);

            if (targetTabButton && targetTabContent) {
                targetTabButton.classList.add("active");
                targetTabContent.classList.add("active-content");
            }
        } else {
            // Si aucune valeur n'est trouvée, activez le premier onglet par défaut
            tabs[0].classList.add("active");
            tabContents[0].classList.add("active-content");
        }

        // 2. Ajoutez des listeners pour mettre à jour localStorage lors du clic sur un onglet
        tabs.forEach(tab => {
            tab.addEventListener("click", function () {
                const tabId = this.dataset.tab;

                // Enregistrez le nouvel onglet actif dans localStorage
                localStorage.setItem("activeTab", tabId);

                // Gérez l'affichage des onglets
                tabs.forEach(t => t.classList.remove("active"));
                tabContents.forEach(content => content.classList.remove("active-content"));

                this.classList.add("active");
                document.getElementById(tabId).classList.add("active-content");
            });
        });
    });

</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {

        // Référence aux éléments HTML
        const toolsTable = document.getElementById("tools-table");
        const addRowBtn = document.getElementById("add-row-btn");
        const submitToolsBtn = document.getElementById("submit-tools-btn");

        // Ajouter une nouvelle ligne
        addRowBtn.addEventListener("click", () => {
            const newRow = toolsTable.querySelector(".tool-row").cloneNode(true);

            // Réinitialiser les valeurs des champs clonés
            newRow.querySelectorAll("input, textarea, select").forEach(input => {
                input.value = "";
            });

            toolsTable.querySelector("tbody").appendChild(newRow);
        });

        // Supprimer une ligne
        toolsTable.addEventListener("click", (e) => {
            if (e.target.classList.contains("remove-row-btn")) {
                const row = e.target.closest("tr");
                const totalRows = toolsTable.querySelectorAll(".tool-row").length;

                if (totalRows > 1) {
                    row.remove();
                } else {
                    alert("Vous devez conserver au moins une ligne !");
                }
            }
        });

        // Soumettre les données
        submitToolsBtn.addEventListener("click", () => {
            const rows = toolsTable.querySelectorAll(".tool-row");
            let allValid = true;

            rows.forEach(row => {
                const name = row.querySelector(".tool-name").value.trim();
                const url = row.querySelector(".tool-url").value.trim();
                const doc = row.querySelector(".tool-doc").value.trim();
                const category = row.querySelector(".tool-category").value;
                const tags = row.querySelector(".tool-tags").value.trim();

                // Validation des champs
                if (!name || !url || !doc || !category) {
                    allValid = false;
                    alert("Tous les champs sont obligatoires !");
                    return;
                }

                if (!isValidUrl(url)) {
                    allValid = false;
                    alert("Veuillez fournir un lien URL valide !");
                    return;
                }

                // Appel à Tool_Added pour chaque ligne
                Tool_Added(name, url, doc, category, 0, tags.split(","), <?= $_SESSION['id_actif'] ?? 0 ?>, <?= $_SESSION['is_admin'] ?? 0 ?>, 0);
            });

            if (allValid) {
                alert("Tous les outils ont été ajoutés avec succès !");
            }
        });

        // Fonction pour valider les URL
        function isValidUrl(url) {
            const pattern = new RegExp("^(https?:\\/\\/)?"+ // protocole
                "((([a-z\\d]([a-z\\d-]*[a-z\\d])*).)+[a-z]{2,}|" + // domaine
                "((\\d{1,3}\\.){3}\\d{1,3}))" + // ip (v4)
                "(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*" + // port et chemin
                "(\\?[;&a-z\\d%_.~+=-]*)?" + // chaîne de requête
                "(\\#[-a-z\\d_]*)?$","i"); // fragment
            return !!pattern.test(url);
        }
    });
    document.addEventListener("DOMContentLoaded", function () {

        // Suggestions de tags en temps réel
        function suggestTags(inputElement, suggestionsContainer) {
            const query = inputElement.value.trim();

            // Vider les suggestions si la recherche est vide
            if (!query) {
                suggestionsContainer.innerHTML = "";
                return;
            }

            // Requête AJAX pour récupérer les suggestions de tags
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "Includes/PHP/tool_tags.php", true);
            const data = new FormData();
            data.append("input", query);
            data.append("key", "suggest_tool_tags");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    suggestionsContainer.innerHTML = xhr.responseText;

                    // Ajouter les événements aux boutons des tags suggérés
                    suggestionsContainer.querySelectorAll(".tag-div").forEach(tagBtn => {
                        tagBtn.addEventListener("click", function () {
                            const tagId = this.dataset.tagId;
                            const tagName = this.dataset.tagName;
                            const activeTagsContainer = inputElement.parentElement.querySelector(".active-tags");

                            // Empêcher les doublons
                            if (activeTagsContainer.querySelector(`[data-tag-id="${tagId}"]`)) {
                                alert("Ce tag est déjà ajouté.");
                                return;
                            }

                            // Ajouter le bouton du tag actif
                            const tagButton = document.createElement("button");
                            tagButton.type = "button";
                            tagButton.className = "active-tag";
                            tagButton.dataset.tagId = tagId;
                            tagButton.textContent = `#${tagName}`;
                            tagButton.addEventListener("click", function () {
                                this.remove(); // Supprimer le tag actif
                            });

                            activeTagsContainer.appendChild(tagButton);
                        });
                    });
                }
            };

            xhr.send(data);
        }

        // Ajouter une nouvelle ligne dans le tableau
        const toolsTable = document.getElementById("tools-table");
        const addRowBtn = document.getElementById("add-row-btn");

        addRowBtn.addEventListener("click", () => {
            const newRow = toolsTable.querySelector(".tool-row").cloneNode(true);

            // Réinitialiser les données des inputs
            newRow.querySelectorAll("input, textarea, select").forEach(input => (input.value = ""));
            newRow.querySelectorAll(".suggested-tags, .active-tags").forEach(container => (container.innerHTML = ""));

            toolsTable.querySelector("tbody").appendChild(newRow);
        });

        // Soumettre les données avec les tags
        const submitToolsBtn = document.getElementById("submit-tools-btn");
        submitToolsBtn.addEventListener("click", () => {
            const rows = toolsTable.querySelectorAll(".tool-row");
            let allValid = true;

            rows.forEach(row => {
                const name = row.querySelector(".tool-name").value.trim();
                const url = row.querySelector(".tool-url").value.trim();
                const doc = row.querySelector(".tool-doc").value.trim();
                const category = row.querySelector(".tool-category").value;
                const activeTags = [...row.querySelectorAll(".active-tags .active-tag")].map(tag => tag.dataset.tagId);

                if (!name || !url || !doc || !category) {
                    allValid = false;
                    alert("Tous les champs sont obligatoires.");
                    return;
                }

                // Appel à la méthode Tool_Added
                Tool_Added(
                    name,
                    url,
                    doc,
                    category,
                    0,
                    activeTags,
                    <?= $_SESSION['id_actif'] ?? 0 ?>,
                    <?= $_SESSION['is_admin'] ?? 0 ?>,
                    0
                );
            });

            if (allValid) {
                alert("Tous les outils ont été ajoutés avec succès !");
            }
        });
    });
</script>
<script src="Javascript.js"></script>
<div class="popup" id="popup-tool">
    <?php require_once 'Includes/PHP/add_tool_popup.php';?>
</div>

<script src="ToolEdits.js"></script>
<div id="BOX-Gest-failure-message" class="gest-success-message" style="background-color: crimson">L'opération a échoué"</div>
</body>
</html>
