<!DOCTYPE html>
<div lang="fr" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN OSIX</title>
    <link rel="icon" href="Includes\PHP\Icone Onglet.png" />
    <link rel="stylesheet" href="STYLE\IndexStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexPopupStyle.css"/>
    <link rel="stylesheet" href="STYLE\HeaderStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexEPIXStyle.css"/>
    <link rel="stylesheet" href="STYLE\AdministrationStyle.css"/>
    <link rel="stylesheet" href="STYLE\EPIXstyle.css"/>
    <link rel="stylesheet" href="STYLE\FooterStyle.css">
    <link rel="stylesheet" href="STYLE\GestionaireStyle.css"/>

</head>

<?php
try {
    // Connexion à la base de données
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

require_once 'Includes\PHP\header.php';

echo "<br>";
if ($_SESSION['is_admin']) {?>
<span style="padding : 20px 2vw; font-family: Georgia; font-weight: bolder; color: #ff5f5f; background-color: #efefef; font-size: x-large">ADMINISTRATION</span><br><br>

    <!-- Menu des onglets -->
<div class="tab-container">
    <div class="tab" data-tab="BOX">BOX</div>
    <div class="tab" data-tab="GLORIX">GLORIX</div>
    <div class="tab" data-tab="EPIX">EPIX</div>
    <div class="tab" data-tab="GENERAL">GENERAL</div>

</div>

<!-- Sous-menus pour les onglets -->
<div class="submenu-container">
    <div class="submenu" id="submenu-GENERAL">
        <div class="submenu-tab" data-subtab="GENERAL-profils">Profils</div>
        <div class="submenu-tab" data-subtab="GENERAL-bdd">Base de données</div>
        <div class="submenu-tab" data-subtab="GENERAL-alert">Annonces</div>
    </div>
    <div class="submenu" id="submenu-BOX">
        <div class="submenu-tab" data-subtab="BOX-default">Arborescence par défaut</div>
        <div class="submenu-tab" data-subtab="BOX-default-fav">Favoris par défaut</div>
        <div class="submenu-tab" data-subtab="BOX-tags" id="toolTagButton">Tags</div>
<!--        <div class="submenu-tab" data-subtab="BOX-bdd">Base de données</div>-->
    </div>
    <div class="submenu" id="submenu-GLORIX">
        <div class="submenu-tab" data-subtab="GLORIX-cotation">Cotation</div>
        <div class="submenu-tab" data-subtab="GLORIX-default-fav">Profil par défaut</div>
        <div class="submenu-tab" data-subtab="GLORIX-tags" id="tagButton">Tags</div>
        <div class="submenu-tab" data-subtab="GLORIX-questions">Questions</div>
        <div class="submenu-tab" data-subtab="GLORIX-types" id="glorixTypeButton">Types</div>
    </div>
    <div class="submenu" id="submenu-EPIX">
        <div class="submenu-tab" data-subtab="EPIX-actualités">Actualités</div>
        <div class="submenu-tab" data-subtab="EPIX-tags" onclick="loadEPIXCategories()">Catégories</div>
    </div>

</div>


<div id="GENERAL-profils" class="subtab-content">
        <h2>Gestion des utilisateurs</h2>
        <form id="users-form">
            <table class="adminTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Mot de passe</th>
                    <?php
                    // Chargement des groupes depuis la base de données
                    $query = "SELECT id, name FROM usergroups";
                    $stmt = $bdd->prepare($query);
                    $stmt->execute();
                    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($groups as $group) {
                        echo "<th>" . htmlspecialchars($group['name']) . "</th>";
                    }
                    ?>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                // Obtenez les utilisateurs
                $query = "SELECT id AS user_id, username AS user_name, password AS user_password, groupes AS user_groups FROM users";
                $stmt = $bdd->prepare($query);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($users as $user):
                    $userGroups = json_decode($user['user_groups'], true) ?: [];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><input type="text" name="username[<?= $user['user_id'] ?>]" value="<?= htmlspecialchars($user['user_name']) ?>"></td>
                        <td style="display: flex; justify-content: space-between"><input style="width : 94% !important" type="password" name="password[<?= $user['user_id'] ?>]" value="<?= htmlspecialchars($user['user_password']) ?>">
                            <button type="button"
                                    aria-label="Voir ou cacher le mot de passe"
                                    onclick="togglePasswordVisibility(<?= $user['user_id'] ?>)"
                                    style="width : 5% !important; padding : 0 5px 0 0">
                                <img src="Includes/Images/Icone%20Eye.png" style="width : 90%" alt="Voir">
                            </button></td>
                        <?php foreach ($groups as $group) : ?>
                            <td style="text-align: center;">
                                <input type="checkbox"
                                       name="groups[<?= $user['user_id'] ?>][]"
                                       value="<?= htmlspecialchars($group['id']) ?>"
                                    <?= in_array($group['id'], $userGroups) ? 'checked' : '' ?>>
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <button type="button" onclick="submitUserForm(<?= $user['user_id'] ?>)">Soumettre</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </form>
</div>
<div id="GENERAL-bdd" class="subtab-content">
    <h3>Base de données</h3>
    <p>Informations sur la gestion de la base de données pour EPIX.</p>
    <button style="background-color: #ff9898" onclick ="window.location.href = 'Includes/PHP/Generate_SQL_Dump.php'" ;>Récupérer la base de données</button>
</div>
<div id="GENERAL-alert" class="subtab-content">
    <h2>Message d'annonce généralisée</h2>
    <label for="alert-content">Insérer le contenu du message</label>
    <?php $req = $bdd->prepare("SELECT valeur FROM constantes WHERE name = 'Alert_Content'");
    $req->execute();
    $AlertMessage = $req->fetch()['valeur'];
    ?>
    <textarea id="alert-content" style="padding : 7px; border : none; border-radius: 15px; " placeholder="Attention, maintenance demain à parti de 15h."><?= $AlertMessage ?></textarea>
    <button onclick="updateAlertMessage()"> Publier l'annonce</button>
    <button onclick="emptyAlertMessage()">Vider le message</button>
</div>
<script>
    function updateAlertMessage() {
        const alertContent = document.getElementById("alert-content").value;

        if (!alertContent.trim()) {
            alert("Le message d'alerte ne peut pas être vide !");
            return;
        }

        // Préparation d'une requête AJAX pour envoyer le contenu au serveur
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "Includes/PHP/UpdateAlertMessage.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    alert("Le message d'alerte a été mis à jour avec succès !");
                } else {
                    alert("Erreur lors de la mise à jour du message d'alerte.");
                }
            }
        };

        xhr.send("action=update&content=" + encodeURIComponent(alertContent));
    }

    function emptyAlertMessage() {
        console.log("emptyAlertMessage");
        if (!confirm("Êtes-vous sûr de vouloir vider le message d'alerte ?")) {
            return;
        }

        // Préparation d'une requête AJAX pour vider la constante
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "Includes/PHP/UpdateAlertMessage.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    document.getElementById("alert-content").value = ""; // Efface la zone de texte
                    alert("Le message d'alerte a été vidé avec succès !");
                    document.getElementById("alert-content").innerHTML = ""
                } else {
                    alert("Erreur lors de la suppression du message d'alerte.");
                }
            }
        };

        xhr.send("action=empty");
    }
</script>



    <!-- Contenus liés à la sous-catégorie -->
<div id="BOX-default" class="subtab-content">
    <h3>Arborescence par défaut</h3>
    <div id="search" class="">
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
                    <label for="hashtag-buttons-container" style="text-align: left; padding: 5px; color: rgb(91,98,170)">Filtrer par hashtags</label>
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
                    <input type="date" id="start-date"/>
                    <button class="clear-button hidden" id="clear-start-date">
                        ✖
                    </button>
                    <label for="end-date"><span style="font-weight : bold">Date de fin :</span></label>
                    <input type="date" id="end-date"/>
                    <button class="clear-button hidden" id="clear-end-date">
                        ✖
                    </button>
                </div>
                <div style='margin-top: 20px; margin-bottom: 20px;'>
                    <label for="sort_selection"><span style="font-weight : bold">Trier par :</span></label>
                    <select id="sort_selection">
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
                    <th style="">Par défaut</th>
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

</div>

<div id="BOX-default-fav" class="subtab-content">
        <h3>Favoris par défaut</h3>
    <?php
    // Charger les outils favoris pour le profil par défaut (user_id = 0)
    $query = 'SELECT tools FROM favoritetools WHERE user_id = 0';
    $stmt = $bdd->prepare($query);
    $stmt->execute();
    $defaultToolsJson = $stmt->fetch();
    $defaultTools = json_decode($defaultToolsJson['tools'], true) ?? [];

    // Récupérer tous les outils existants
    $req = $bdd->query("SELECT id, image, name, url FROM tools ORDER BY image DESC");
    $allTools = $req->fetchAll(PDO::FETCH_ASSOC);

    foreach ($allTools as $tool) {
        $toolId = $tool['id'];
        $toolImg = !empty($tool['image']) ? $tool['image'] : 'Fav_Icons\Default_Source.png';
        $toolName = htmlspecialchars($tool['name'], ENT_QUOTES);
        $toolUrl = htmlspecialchars($tool['url'], ENT_QUOTES);
        $isFavorite = array_key_exists($toolId, $defaultTools);
        echo '<div class="tool-fav-div" id="DefaultProfileTool' . $toolId . '" style="display: flex; justify-content: left; gap : 1em; margin-left : 1em; align-items: center; margin-bottom: 10px">';
        echo '<img class="tool-fav-img" src="'.$toolImg.'" alt="Image de l\'outil" height="30px">';
        echo '<a class="fav-anchor" href="' . $toolUrl . '" target="_blank" title="' . $toolName . '">' . $toolName . '</a>';
        // Ajouter ou supprimer des favoris
        echo $isFavorite ? '<button onclick="toggleDefaultFavTool(' . $toolId . ', \''.$toolImg.'\')" class="toggle-fav-btn" style="background-color: #82c5a9">Retirer des favoris</button>' : '<button onclick="toggleDefaultFavTool(' . $toolId . ', \''.$toolImg.'\')" class="toggle-fav-btn" style="background-color: #d58787">Ajouter aux favoris</button>';

        echo '</div>';
    }
    ?>
</div>


<div id="BOX-tags" class="subtab-content">
    <h3>Tags BOX - ADMIN</h3>
    <div id="toolTags-table-container">
        <div class="search-tags">
            <table id="toolTags-table" class="adminTable">
                <thead>
                <tr>
                    <th style="text-align : center">NOM</th>
                    <th style="text-align : center">TYPE</th>
                    <th style="text-align: center;">GROUPES</th>
                </tr>
                </thead>
                <tbody id="toolTags-tbody">
                </tbody>
            </table>
        </div>
    </div>
    <div style="display: flex; justify-content: space-around">
        <div id="add-toolTags-container" class="" style="padding: 10px; margin: 5px; width: 20%;">
            <div id="add-toolTags" class="" style="padding: 20px; margin: 5px; background-color: rgba(255,128,128,0.21); border-radius: 20px;">
                <h2 style="margin-top : 0">Nouveau tag</h2>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Champ pour le nom du tag -->
                    <div style="display: flex; justify-content: space-between; flex-wrap: wrap; align-items: center;">
                        <input type="text" name="tags" id="newToolTagInput" placeholder="Ajouter un tag" style="width: 200px; margin-top: 20px; margin-bottom: 10px; border-radius: 10px; padding: 5px; border: none" />
                        <input type="text" name="tags" id="newToolTagInputType" placeholder="Type de Tag" style="width: 200px; margin-top: 0; margin-bottom: 10px; border-radius: 10px; padding: 5px; border: none" /><br>
                    </div>
                    <button id="addToolTagButton" style="background-color: white; border-radius: 10px; width : 150px">Valider</button>
                </div>
            </div>
        </div>
        <div id="delete-toolTags-container" class="" style="padding: 10px; margin: 5px; width: 20%;">
            <div id="delete-toolTags" style="padding: 20px; margin: 10px; background-color: rgba(255,128,128,0.21); border-radius: 20px;">
                <h2>Supprimer un tag</h2>
                <form id="deleteToolTagForm">
                    <label for="deleteToolTagSelect">Sélectionnez un tag :</label>
                    <select id="deleteToolTagSelect" name="toolTagId" style="width: 100%; padding: 5px; margin: 10px 0;">
                        <!-- Les options seront chargées dynamiquement via AJAX -->
                    </select>
                    <button type="submit" style="background-color: white; border-radius: 10px; padding: 10px;">Supprimer</button>
                </form>
            </div>
        </div>
        <div id="give-toolTags-container" class="" style="padding: 10px; margin: 5px; width: 20%;">
            <div id="give-toolTags" class="" style="padding: 20px; margin: 5px; background-color: rgba(255,128,128,0.21); border-radius: 20px;">
                <h3>Valider la sélection</h3>
                <button id="confirmToolTagButton" style="background-color: white; border-radius: 10px">Confirmer</button>
            </div>
        </div>
    </div>
</div>

<div id="BOX-bdd2" class="subtab-content">
    <h3>Base de données - Tables BOX</h3>
    <p>Visualisez, modifiez ou ajoutez des données à vos tables ci-dessous.</p>

    <!-- Boutons pour sélectionner les tables -->
    <div class="subsubmenu">
        <button class="table-selection" data-table="tools">Outils</button>
        <button class="table-selection" data-table="toolscategories">Categories</button>
        <button class="table-selection" data-table="toolstags">Tags</button>
        <button class="table-selection" data-table="favoritetools">Favoris</button>
    </div>

    <!-- Divisions pour chaque table -->
    <div id="table-tools" class="table-container"></div>
    <div id="table-toolscategories" class="table-container hidden"></div>
    <div id="table-toolstags" class="table-container hidden"></div>
    <div id="table-favoritetools" class="table-container hidden"></div>
</div>

<div id="GLORIX-cotation" class="subtab-content">
    <h1>Coter une source</h1>
    <div>
        <label for "source-select-cotation">Sélectionner une source à coter</label>
        <select onchange="EditCotationForm(this.value)" name="source" id="source-select-cotation" style="width: 100%; padding: 5px; margin: 10px 0;">
            <option value="">--Sélectonner une source--</option>
            <?php $req = "SELECT * FROM sources";
            $stmt = $bdd->prepare($req);
            $stmt->execute();
            $sources = $stmt->fetchAll();
            foreach ($sources as $source) {
                echo '<option value="' . $source['id'] . '">' . $source['name'] . '</option>';
            }?>
        </select>
    </div>
    <div id="source-cotation-content">
    </div>
</div>

<div id="GLORIX-tags" class="subtab-content">
    <h3>Tags GLORIX - ADMIN</h3>
    <div id="tags-table-container">
        <div class="search-tags">
            <table id="tags-table" class="adminTable">
                <thead>
                <tr>
                    <th style="text-align : center">NOM</th>
                    <th style="text-align : center">TYPE</th>
                    <th style="text-align: center;">GROUPES</th>
                </tr>
                </thead>
                <tbody id="tags-tbody">
                </tbody>
            </table>
        </div>
    </div>
    <div style="display: flex; justify-content: space-around">
        <div id="add-tags-container" class="" style="padding: 10px; margin: 5px; width: 20%;">
            <div id="add-tags" class="" style="padding: 20px; margin: 5px; background-color: rgba(255,128,128,0.21); border-radius: 20px;">
                <h2 style="margin-top : 0">Nouveau tag</h2>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Champ pour le nom du tag -->
                    <div style="display: flex; justify-content: space-between; flex-wrap: wrap; align-items: center;">
                        <input type="text" name="tags" id="newTagInput" placeholder="Ajouter un tag" style="width: 200px; margin-top: 20px; margin-bottom: 10px; border-radius: 10px; padding: 5px; border: none" />
                        <input type="text" name="tags" id="newTagInputType" placeholder="Type de Tag" style="width: 200px; margin-top: 0; margin-bottom: 10px; border-radius: 10px; padding: 5px; border: none" /><br>
                    </div>
                    <button id="addTagButton" style="background-color: white; border-radius: 10px; width : 150px">Valider</button>
                </div>
            </div>
        </div>
        <div id="delete-tags-container" class="" style="padding: 10px; margin: 5px; width: 20%;">
            <div id="delete-tags" style="padding: 20px; margin: 10px; background-color: rgba(255,128,128,0.21); border-radius: 20px;">
                <h2>Supprimer un tag</h2>
                <form id="deleteTagForm">
                    <label for="deleteTagSelect">Sélectionnez un tag :</label>
                    <select id="deleteTagSelect" name="tagId" style="width: 100%; padding: 5px; margin: 10px 0;">
                        <!-- Les options seront chargées dynamiquement via AJAX -->
                    </select>
                    <button type="submit" style="background-color: white; border-radius: 10px; padding: 10px;">Supprimer</button>
                </form>
            </div>
        </div>
        <div id="give-tags-container" class="" style="padding: 10px; margin: 5px; width: 20%;">
            <div id="give-tags" class="" style="padding: 20px; margin: 5px; background-color: rgba(255,128,128,0.21); border-radius: 20px;">
                <h3>Valider la sélection</h3>
                <button id="confirmTagButton" style="background-color: white; border-radius: 10px">Confirmer</button>
            </div>
        </div>
    </div>
</div>

    <div id="GLORIX-default-fav" class="subtab-content">
        <h3>Liens favoris pour le profil par défaut</h3>
        <div class="collapsible-content-box" style="max-height: fit-content; width : 50vw; padding : 2em">
            <div class="gallery" id="generic-sources-gallery">
                <?php

                // Étape 1 : Récupérer les données JSON de la colonne "sources" de favoritelinks
                $query = 'SELECT links FROM favoritelinks WHERE user_id = 0';
                $stmt = $bdd->prepare($query);
                $stmt->execute([]);
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

                        // Affichage dynamique sous forme d'un bouton avec image et lien
                        echo '<div style="max-height: 2vw" class="link-fav-div" data-id='.$favlink_id.' id="FavLink' . $favlink_id . '">';
                        echo '<a class="fav-anchor" href="' . $link_url . '" target="_blank" title="' . $link_name . '">';
                        echo '<img class="fav-icon" src="' . $link_image . '" alt="' . $link_name . '">';
                        echo '</a>';

                        // Bouton pour supprimer le favori
                        echo '<a onclick="deleteFavLinkAdmin(' . $favlink_id .')" class="delete-anchor">';
                        echo '<img class="delete-icon to_defilter" src="Includes/Images/Icone moins.png">';
                        echo '</a>';
                        echo '</div>';
                    }
                }
                ?>
                <button  onclick="document.getElementById('popup-fav-link-admin').style.display = 'flex'" style="padding:0px" title="Ajouter une lien favori"> <img class="new-button-img to_defilter" src="Includes\Images\Icone Plus.png"></button>
            </div>
        </div>
        <span style="height : 2em"></span>
        <h3>Sources par défaut</h3>
        <div>
            <?php $sources = $bdd->query('SELECT * FROM sources');
            $sources = $sources->fetchAll();
            $list = "";
            foreach ($sources as $source) {
                $FollowButton = in_array(0, json_decode($source['followers'])) ? '<button class="follow-button" onclick="Unfollow_Source_Admin(' . $source['id'] . ')"><img src="Includes\Images\Icone%20moins.png" height="15px"></button>' : '<button class="follow-button" onclick="Follow_Source_Admin(' . $source['id'] . ')"><img src="Includes\Images\Icone Plus.png" height="15px"></button>' ;
                $source_cotation_grade = $source['cotation'] != "" && isset(json_decode($source['cotation'])->grade) ? " | " . json_decode($source['cotation'])->grade : "";
                $list .= '<li>'. htmlspecialchars($source['name']) . $source_cotation_grade . $FollowButton .'</li>';
            }?>
            <ul>
                <?= $list ?>
            </ul>
        </div>

    </div>


    <div id="GLORIX-types" class="subtab-content">
        <h3>Types GLORIX - ADMIN</h3>
        <p style="font-style: italic;">NB : Pour un type principal (une ligne dont la CATEGORIE est "Ressource générique"), mettre le chiffre 0 pour PARENT.</p>
        <div id="types-table-container">
            <div class="search-tags">
                <table id="types-table" class="adminTable">
                    <thead>
                    <tr>
                        <th style="text-align: center;">PARENT</th>
                        <th style="text-align : center">NOM</th>
                        <th style="text-align : center">CATEGORIE</th>
                    </tr>
                    </thead>
                    <tbody id="types-tbody">
                    </tbody>
                </table>
            </div>
        </div>
        <div style="display: flex; justify-content: space-around">
            <div id="add-types-container" class="" style="padding: 10px; margin: 5px; width: 20%;">
                <div id="add-types" class="" style="padding: 20px; margin: 5px; background-color: rgba(255,128,128,0.21); border-radius: 20px;">
                    <h2 style="margin-top : 0">Nouveau type</h2>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <!-- Champ pour le nom du tag -->
                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; align-items: center;">
                            <input type="text" id="typeParentInput" placeholder="Parent du type" style="width: 200px; margin-top: 20px; margin-bottom: 10px; border-radius: 10px; padding: 5px; border: none" />
                            <input type="text" id="typeNameInput" placeholder="Nom du Tag" style="width: 200px; margin-top: 0; margin-bottom: 10px; border-radius: 10px; padding: 5px; border: none" /><br>
                            <select type="text" id="typeSelect" style="width: 200px; margin-top: 0; margin-bottom: 10px; border-radius: 10px; padding: 5px; border: none">
                                <option value=''>Choisir un type</option>
                                <option value='0'>Sources et liens</option>
                                <option value='1'>Liens uniquements</option>
                                <option value='2'>Ressource générique</option>
                            </select>
                        </div>
                        <button id="addTypeButton" style="background-color: white; border-radius: 10px; width : 150px">Valider</button>
                    </div>
                </div>
            </div>
            <div id="delete-types-container" class="" style="padding: 10px; margin: 5px; width: 20%;">
                <div id="delete-types" style="padding: 20px; margin: 10px; background-color: rgba(255,128,128,0.21); border-radius: 20px;">
                    <h2>Supprimer un type</h2>
                    <form id="deleteTypeForm">
                        <label for="deleteTypeSelect">Type à supprimer :</label>
                        <select id="deleteTypeSelect" style="width: 100%; padding: 5px; margin: 10px 0;">
                            <!-- Les options seront chargées dynamiquement via AJAX -->
                        </select>
                        <label for="newTypeSelect" title="Les outils affectés au type supprimé seront ré-affectés vers ce nouveau type.">Type de remplacement :</label>
                        <select id="newTypeSelect" title="Les outils affectés au type supprimé seront ré-affectés vers ce nouveau type." style="width: 100%; padding: 5px; margin: 10px 0;">
                            <!-- Les options seront chargées dynamiquement via AJAX -->
                        </select>
                        <button type="submit" style="background-color: white; border-radius: 10px; padding: 10px;">Supprimer</button>
                    </form>
                </div>
            </div>
            <div id="give-types-container" class="" style="padding: 10px; margin: 5px; width: 20%;">
                <div id="give-types" class="" style="padding: 20px; margin: 5px; background-color: rgba(255,128,128,0.21); border-radius: 20px;">
                    <h3>Valider la sélection</h3>
                    <button id="confirmTypeButton" style="background-color: white; border-radius: 10px">Confirmer</button>
                </div>
            </div>
        </div>
    </div>

<div id="GLORIX-bdd" class="subtab-content">
    <h3>Base de données</h3>
    <p>Détails sur la base de données GLORIX.</p>
</div>
<div id="GLORIX-questions" class="subtab-content">
    <h1>Questions</h1>

    <h3>> Crible de cotation :</h3>
    <div id="crible-cotation" style="display: flex">
        <div style="margin-bottom: 3em; width : 40%">
            <h5>Source</h5>
            <form id="questionsFormSource">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                    <tr style="background-color: #586b7c; color: #ffffff; font-size: large;">
                        <th style="border: 1px solid #dddddd; padding: 8px; text-align: center">Titre</th>
                        <th style="border: 1px solid #dddddd; padding: 8px;; text-align: center">Question</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $req = "SELECT id, question, title FROM evalquestions WHERE category='information'";
                    $stmt = $bdd->prepare($req);
                    $stmt->execute();
                    $questions = $stmt->fetchAll();

                    foreach ($questions as $question):
                        ?>
                        <tr>
                            <td style="border: 1px solid #dddddd; padding: 0;">
                                <input
                                        type="text"
                                        name="questions[<?= htmlspecialchars($question['id']) ?>]"
                                        value="<?= htmlspecialchars($question['title']) ?>"
                                        class="modify-question"
                                />                    </td>
                            <td style="border: 1px solid #dddddd; padding: 0;">
                                <input
                                        type="text"
                                        name="questions[<?= htmlspecialchars($question['id']) ?>]"
                                        value="<?= htmlspecialchars($question['question']) ?>"
                                        class="modify-question"
                                />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" onclick="submitQuestionsSource()" style="margin-top: 20px;">Soumettre</button>
            </form>
        </div>
        <hr style="margin: 1vw 5vw">
        <div style="width : 40%">
            <h5>Source</h5>
            <form id="questionsFormInfo" >
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                    <tr style="background-color: #586b7c; color: #ffffff; font-size: large;">
                        <th style="border: 1px solid #dddddd; padding: 8px; text-align: center">Titre</th>
                        <th style="border: 1px solid #dddddd; padding: 8px;; text-align: center">Question</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $req = "SELECT id, question, title FROM evalquestions WHERE category='information'";
                    $stmt = $bdd->prepare($req);
                    $stmt->execute();
                    $questions = $stmt->fetchAll();

                    foreach ($questions as $question):
                        ?>
                        <tr>
                            <td style="border: 1px solid #dddddd; padding: 0;">
                                <input
                                        type="text"
                                        name="questions[<?= htmlspecialchars($question['id']) ?>]"
                                        value="<?= htmlspecialchars($question['title']) ?>"
                                        class="modify-question"
                                />                    </td>
                            <td style="border: 1px solid #dddddd; padding: 0;">
                                <input
                                        type="text"
                                        name="questions[<?= htmlspecialchars($question['id']) ?>]"
                                        value="<?= htmlspecialchars($question['question']) ?>"
                                        class="modify-question"
                                />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" onclick="submitQuestionsInfo()" style="margin-top: 20px;">Soumettre</button>
            </form>
        </div>
    </div>



    <hr>
    <h3 style="margin-top : 30px; margin-bottom: 20px">> Caractérisation de l'information :</h3>
    <?php
    $req = "SELECT id, question FROM evalquestions WHERE category='caracterisation_information'";
    $stmt = $bdd->prepare($req);
    $stmt->execute();
    $questions = $stmt->fetchAll();
    ?>
    <div id="link-eval-questions" style="margin-left: 20px">

        <form id="questionsFormCaracterisation">
            <table style="width: 50%; border-collapse: collapse; text-align: left;">
                <thead>
                <tr style="background-color: #586b7c; color: #ffffff; font-size: large;">
                    <th style="border: 1px solid #dddddd; padding: 8px; text-align: center;">Question</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($questions as $question) { ?>
                    <tr>
                        <td style="border: 1px solid #dddddd; padding: 0;">
                            <input
                                    class="modify-question"
                                    type="text"
                                    name="questions[<?= htmlspecialchars($question['id']) ?>]"
                                    value="<?= htmlspecialchars($question['question']) ?>"
                                    style="width: 100%; box-sizing: border-box;"
                            />
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <button
                    type="button"
                    onclick="submitQuestionsCaracterisation()"
                    style="margin-top: 20px;"
            >Soumettre</button>
        </form>
    </div>
    <script>
        function submitQuestionsSource() {
            const form = document.getElementById("questionsFormSource");
            const formData = new FormData(form);

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "Includes/PHP/UpdateQuestions.php", true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert("Les modifications ont été enregistrées avec succès !");
                } else if (xhr.readyState === 4) {
                    alert("Une erreur s'est produite lors de la soumission.");
                }
            };

            xhr.send(formData);
        }
        function submitQuestionsInfo() {
            const form = document.getElementById("questionsFormInfo");
            const formData = new FormData(form);

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "Includes/PHP/UpdateQuestions.php", true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert("Les modifications ont été enregistrées avec succès !");
                } else if (xhr.readyState === 4) {
                    alert("Une erreur s'est produite lors de la soumission.");
                }
            };

            xhr.send(formData);
        }
        function submitQuestionsCaracterisation() {
            const form = document.getElementById("questionsFormCaracterisation");
            const formData = new FormData(form);

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "Includes/PHP/UpdateQuestions.php", true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert("Les modifications ont été enregistrées avec succès !");
                } else if (xhr.readyState === 4) {
                    alert("Une erreur s'est produite lors de la soumission.");
                }
            };

            xhr.send(formData);
        }
    </script>
</div>

<div id="PROFILS-gestion" class="subtab-content">
    <h2>Gestion des utilisateurs</h2>
    <form id="users-form">
        <table class="adminTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Mot de passe</th>
                <?php
                // Chargement des groupes depuis la base de données
                $query = "SELECT id, name FROM usergroups";
                $stmt = $bdd->prepare($query);
                $stmt->execute();
                $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($groups as $group) {
                    echo "<th>" . htmlspecialchars($group['name']) . "</th>";
                }
                ?>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            // Obtenez les utilisateurs
            $query = "SELECT id AS user_id, username AS user_name, password AS user_password, groupes AS user_groups FROM users";
            $stmt = $bdd->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as $user):
                $userGroups = json_decode($user['user_groups'], true) ?: [];
                ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                    <td><input type="text" name="username[<?= $user['user_id'] ?>]" value="<?= htmlspecialchars($user['user_name']) ?>"></td>
                    <td style="display: flex; justify-content: space-between"><input style="width : 94% !important" type="password" name="password[<?= $user['user_id'] ?>]" value="<?= htmlspecialchars($user['user_password']) ?>">
                        <button type="button"
                                aria-label="Voir ou cacher le mot de passe"
                                onclick="togglePasswordVisibility(<?= $user['user_id'] ?>)"
                                style="width : 5% !important; padding : 0 5px 0 0">
                            <img src="Includes/Images/Icone%20Eye.png" style="width : 90%" alt="Voir">
                        </button></td>
                    <?php foreach ($groups as $group) : ?>
                        <td style="text-align: center;">
                            <input type="checkbox"
                                   name="groups[<?= $user['user_id'] ?>][]"
                                   value="<?= htmlspecialchars($group['id']) ?>"
                                <?= in_array($group['id'], $userGroups) ? 'checked' : '' ?>>
                        </td>
                    <?php endforeach; ?>
                    <td>
                        <button type="button" onclick="submitUserForm(<?= $user['user_id'] ?>)">Soumettre</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>


<div id="EPIX-actualités" class="subtab-content">
    <h3>Actualités</h3>
    <p>Toutes les actualités concernant EPIX.</p>
    <?php
    // Dossier des fichiers
    $directory = __DIR__ . '/EPIX_Images';

    // Récupérer la liste des fichiers
    if (is_dir($directory)) {
        $files = scandir($directory); // Liste tous les fichiers et dossiers
        $filesData = []; // Stockera les infos des fichiers

        // Parcourir tous les fichiers
        foreach ($files as $file) {
            $filePath = $directory . '/' . $file;

            // Filtrer uniquement les fichiers (ignorer . et ..)
            if (is_file($filePath)) {
                $filesData[] = [
                    'name' => $file,
                    'size' => filesize($filePath), // Obtenir le poids du fichier
                    'last_modified' => filemtime($filePath) // Dernière modification
                ];
            }
        }

        // Trier par la date de modification (plus ancien au plus récent)
        usort($filesData, function($a, $b) {
            return $a['last_modified'] <=> $b['last_modified'];
        });

        // Calculer le poids total des fichiers
        $totalSize = array_sum(array_column($filesData, 'size'));

        // Afficher les résultats
        echo "<h3>Poids total du dossier : " . round($totalSize / 1024, 2) . " KB</h3>"; // Converti en KB
        echo "<h3>10 fichiers les plus anciens :</h3>";
        echo "<ul>";
        foreach ($filesData as $file) {
            echo "<li>" . $file['name'] . " - " . round($file['size'] / 1024, 2) . " KB - Dernière modification : " . date('Y-m-d H:i:s', $file['last_modified']) . "
            <button class='delete-button' data-filename='" . $file['name'] . "'>Supprimer</button>
        </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Le dossier EPIX_Images n'existe pas.</p>";
    }
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const deleteButtons = document.querySelectorAll(".delete-button");

            deleteButtons.forEach(button => {
                button.addEventListener("click", () => {
                    const filename = button.getAttribute("data-filename");

                    if (confirm(`Êtes-vous sûr(e) de vouloir supprimer le fichier : ${filename} ?`)) {
                        fetch("delete_file.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({ filename })
                        })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    alert(result.message);
                                    button.parentElement.remove(); // Supprime l'élément du DOM
                                } else {
                                    alert(result.message);
                                }
                            })
                            .catch(error => {
                                console.error("Erreur : ", error);
                                alert("Une erreur est survenue.");
                            });
                    }
                });
            });
        });
    </script><script>
        document.addEventListener("DOMContentLoaded", () => {
            const deleteButtons = document.querySelectorAll(".delete-button");

            deleteButtons.forEach(button => {
                button.addEventListener("click", () => {
                    const filename = button.getAttribute("data-filename");

                    if (confirm(`Êtes-vous sûr(e) de vouloir supprimer le fichier : ${filename} ?`)) {
                        fetch("delete_file.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({ filename })
                        })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    alert(result.message);
                                    button.parentElement.remove(); // Supprime l'élément du DOM
                                } else {
                                    alert(result.message);
                                }
                            })
                            .catch(error => {
                                console.error("Erreur : ", error);
                                alert("Une erreur est survenue.");
                            });
                    }
                });
            });
        });
    </script>
</div>
<div id="EPIX-tags" class="subtab-content">
    <h3>Catégories</h3>

    <div style="display: flex; justify-content: space-around; margin-bottom: 3vh;">
        <div id="managementNodeCategory" class="interactivePanel" style="height: max-content">
            <h3 title="Les catégories d'entité communes à tous les utilisateurs.">Gestion des catégories publiques d'entité</h3>
            <!-- Bouton ajouter -->
            <button style="margin: 10px 0;" onclick="addCategory('node')">Ajouter une catégorie</button>
            <!-- Bouton modifier -->
            <button style="margin: 10px 0;" onclick="editCategory('node', true)">Modifier une catégorie</button>
            <!-- Bouton supprimer -->
            <button style="margin: 10px 0;" onclick="deleteCategory('node', true)">Supprimer une catégorie</button>

            <!-- Liste des catégories (dropdown) -->
            <select id="commonNodeCategorySelect" style="width: 100%; margin-top: 10px;">
                <option value="" disabled selected>Choisir une catégorie</option>
            </select>
        </div>

        <div id="managementEdgeCategory" class="interactivePanel" style="height: max-content">
            <h3 title="Les catégories de relation communes à tous les utilisateurs.">Gestion des catégories publiques de relation</h3>
            <!-- Bouton ajouter -->
            <button style="margin: 10px 0;" onclick="addCategory('edge')">Ajouter une catégorie</button>
            <!-- Bouton modifier -->
            <button style="margin: 10px 0;" onclick="editCategory('edge', true)">Modifier une catégorie</button>
            <!-- Bouton supprimer -->
            <button style="margin: 10px 0;" onclick="deleteCategory('edge', true)">Supprimer une catégorie</button>

            <!-- Liste des catégories (dropdown) -->
            <select id="commonEdgeCategorySelect" style="width: 100%; margin-top:  10px;">
                <option value="" disabled selected>Choisir une catégorie</option>
                <!-- Options ajoutées dynamiquement -->
            </select>
        </div>
    </div>

    <div style="display: flex; justify-content: space-around; margin-bottom: 3vh;">
        <div id="managementNodeCategoriesClasses" class="interactivePanel" style="height: max-content">
            <h3 title="Les classes publiques de catégories regroupent plusieurs catégories dans une classe plus large.">Classes publiques d'entité</h3>
            <!-- Bouton ajouter -->
            <button style="margin: 10px 0;" onclick="addClass('node')">Ajouter une classe d'entité</button>
            <!--Bouton modifier -->
            <button style="margin: 10px 0;" onclick="editClass('node')">Modifier une classe d'entité</button>
            <!--Bouton supprimer -->
            <button style="margin: 10px 0;" onclick="deleteClass('node')">Supprimer une classe d'entité</button>

            <!-- Liste des catégories (dropdown) -->
            <select id="classNodeCategorySelect" style="width: 100%; margin-top: 10px;">
                <option value="" disabled selected>Choisir une catégorie</option>
            </select>
        </div>
        <div id="managementCommonEdgeCategory" class="interactivePanel" style="height: max-content">
            <h3 title="Les classes publiques de catégories regroupent plusieurs catégories dans une classe plus large.">Classes publiques de relations</h3>
            <!-- Bouton ajouter -->
            <button style="margin: 10px 0;" onclick="addClass('edge')">Ajouter une classe de relation</button>
            <!--Bouton modifier -->
            <button style="margin: 10px 0;" onclick="editClass('edge')">Modifier une classe de relation</button>
            <!--Bouton supprimer -->
            <button style="margin: 10px 0;" onclick="deleteClass('edge')">Supprimer une classe de relation</button>

            <!-- Liste des catégories (dropdown) -->
            <select id="classEdgeCategorySelect" style="width: 100%; margin-top: 10px;">
                <option value="" disabled selected>Choisir une catégorie</option>
            </select>
        </div>
    </div>

<!-- Modale pour ajouter une catégorie -->
<div id="addCategoryModal" style="display: none; position: fixed; z-index: 1000; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="position: relative; margin: 10% auto; padding: 20px; width: 30%; background-color: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); text-align: left;">
        <h3>Ajouter une catégorie</h3>
        <label for="categoryName">Nom de la catégorie :</label>
        <input type="text" id="categoryName" placeholder="Nom de la catégorie" style="width: 100%; padding: 8px; margin: 10px 0;" />

        <select id="selectCategoryClass">Classe de la catégorie</select>

        <div id="isOrientedDiv" style="display: none; margin: 10px 0;">
            <label for="isOriented">
                <input type="checkbox" id="isOriented"> Relation orientée ?
            </label>
        </div>

        <div style="margin-top: 15px; text-align: right;">
            <button onclick="closeModal()" style="background-color: #d9534f; color: white; border: none; padding: 8px 16px; cursor: pointer;">Annuler</button>
            <button onclick="submitCategory(0, true)" style="background-color: #5cb85c; color: white; border: none; padding: 8px 16px; cursor: pointer;">Ajouter</button>
        </div>
    </div>
</div>

<!-- Modale pour modifier une catégorie -->
<div id="editCategoryModal" style="display: none; position: fixed; z-index: 1000; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="position: relative; margin: 10% auto; padding: 20px; width: 30%; background-color: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); text-align: left;">
        <h3>Modifier une catégorie</h3>
        <label for="editCategoryName">Nom de la catégorie :</label>
        <input type="text" id="editCategoryName" placeholder="Nom de la catégorie" style="width: 100%; padding: 8px; margin: 10px 0;" />

        <select id="selectNewCategoryClass">Classe de la catégorie</select>

        <div id="editIsOrientedDiv" style="display: none; margin: 10px 0;">
            <label for="editIsOriented">
                <input type="checkbox" id="editIsOriented"> Relation orientée ?
            </label>
        </div>

        <div style="margin-top: 15px; text-align: right;">
            <button onclick="closeEditModal()" style="background-color: #d9534f; color: white; border: none; padding: 8px 16px; cursor: pointer;">Annuler</button>
            <button onclick="submitEditCategory(true)" style="background-color: #5cb85c; color: white; border: none; padding: 8px 16px; cursor: pointer;">Enregistrer</button>
        </div>
    </div>
</div>

<!-- Modale pour ajouter une classe -->
<div id="addClassModal" style="display: none; position: fixed; z-index: 1000; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="position: relative; margin: 10% auto; padding: 20px; width: 30%; background-color: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); text-align: left;">
        <h3>Ajouter une classe d'entité</h3>
        <label for="className">Nom de la classe :</label>
        <input type="text" id="className" placeholder="Nom de la catégorie" style="width: 100%; padding: 8px; margin: 10px 0;" />

        <div style="margin-top: 15px; text-align: right;">
            <button onclick="closeClassModal()" style="background-color: #d9534f; color: white; border: none; padding: 8px 16px; cursor: pointer;">Annuler</button>
            <button onclick="submitClass()" style="background-color: #5cb85c; color: white; border: none; padding: 8px 16px; cursor: pointer;">Ajouter</button>
        </div>
    </div>
</div>

    <!-- Modale pour modifier une classe -->
    <div id="editClassModal" style="display: none; position: fixed; z-index: 1000; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="position: relative; margin: 10% auto; padding: 20px; width: 30%; background-color: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); text-align: left;">
            <h3>Modifier une classe d'entité</h3>
            <label for="editClassName">Nouveau nom de la classe :</label>
            <input type="text" id="editClassName" placeholder="Nom de la catégorie" style="width: 100%; padding: 8px; margin: 10px 0;" />

            <div style="margin-top: 15px; text-align: right;">
                <button onclick="closeEditClassModal()" style="background-color: #d9534f; color: white; border: none; padding: 8px 16px; cursor: pointer;">Annuler</button>
                <button onclick="submitEditClass()" style="background-color: #5cb85c; color: white; border: none; padding: 8px 16px; cursor: pointer;">Modifier</button>
            </div>
        </div>
    </div>

</div>
<div id="EPIX-bdd" class="subtab-content">
    <h3>Base de données</h3>
    <p>Informations sur la gestion de la base de données pour EPIX.</p>
    <button style="background-color: #ff9898" onclick ="window.location.href = 'Includes/PHP/Generate_SQL_Dump.php'" ;>Récupérer la base de données </button>
</div>


<script src="EPIX_script.js"></script>
<script>
    function loadEPIXCategories() {
        loadCommonCategories();
        console.log("ok ");
        currentType = "edge";
        loadCategoriesClasses("classEdgeCategorySelect");
        currentType = "node";
        loadCategoriesClasses("classNodeCategorySelect");
    }
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Sélectionner tous les éléments nécessaires
        const tabs = document.querySelectorAll(".tab");
        const submenus = document.querySelectorAll(".submenu");
        const submenuTabs = document.querySelectorAll(".submenu-tab");
        const subtabContents = document.querySelectorAll(".subtab-content");

        // Fonction pour activer un onglet principal
        function activateTab(tab) {
            const tabName = tab.getAttribute("data-tab");

            // Tout réinitialiser
            tabs.forEach(t => t.classList.remove("active"));
            submenus.forEach(sm => sm.classList.remove("active"));
            submenuTabs.forEach(st => st.classList.remove("active"));
            subtabContents.forEach(sc => sc.classList.remove("active"));

            // Activer l'onglet sélectionné et son sous-menu
            tab.classList.add("active");
            const submenu = document.querySelector(`#submenu-${tabName}`);
            if (submenu) {
                submenu.classList.add("active");

                // Par défaut, activer le premier sous-onglet du sous-menu
                const firstSubtab = submenu.querySelector(".submenu-tab");
                if (firstSubtab) {
                    activateSubtab(firstSubtab);
                }
            }

            // Sauvegarder l'onglet actif dans le localStorage
            localStorage.setItem("activeTab", tabName);
        }

        // Fonction pour activer un sous-onglet
        function activateSubtab(subtab) {
            const subtabId = subtab.getAttribute("data-subtab");

            // Désactiver les onglets du sous-menu
            submenuTabs.forEach(st => st.classList.remove("active"));
            subtabContents.forEach(sc => sc.classList.remove("active"));

            // Activer le sous-onglet sélectionné
            subtab.classList.add("active");
            const subtabContent = document.querySelector(`#${subtabId}`);
            if (subtabContent) {
                subtabContent.classList.add("active");
            }

            // Sauvegarder le sous-onglet actif dans le localStorage
            localStorage.setItem("activeSubtab", subtabId);
        }

        // Ajouter les écouteurs d'événements pour chaque onglet principal
        tabs.forEach(tab => {
            tab.addEventListener("click", function () {
                activateTab(this);
            });
        });

        // Ajouter les écouteurs d'événement pour chaque sous-onglet
        submenuTabs.forEach(subtab => {
            subtab.addEventListener("click", function () {
                activateSubtab(this);
            });
        });

        // Restaurer l'état actif au rechargement de la page
        const savedTab = localStorage.getItem("activeTab");
        const savedSubtab = localStorage.getItem("activeSubtab");

        if (savedTab) {
            // Charger l'onglet actif sauvegardé
            const activeTab = document.querySelector(`.tab[data-tab="${savedTab}"]`);
            if (activeTab) {
                activateTab(activeTab);
            }
        } else {
            // Par défaut, activer l'onglet `PROFILS` s'il n'y a rien de sauvegardé
            const defaultTab = document.querySelector(`.tab[data-tab="PROFILS"]`);
            if (defaultTab) {
                activateTab(defaultTab);
            }
        }

        if (savedSubtab) {
            // Charger le sous-onglet actif sauvegardé
            const activeSubtab = document.querySelector(`.submenu-tab[data-subtab="${savedSubtab}"]`);
            if (activeSubtab) {
                activateSubtab(activeSubtab);
            }
        } else {
            // Par défaut, activer le sous-onglet `PROFILS-gestion` s'il n'y a rien de sauvegardé
            const defaultSubtab = document.querySelector(`.submenu-tab[data-subtab="GENERAL-profils"]`);
            if (defaultSubtab) {
                activateSubtab(defaultSubtab);
            }
        }
    });
</script>
<script>

    function validateForm(form) {
        const inputs = form.querySelectorAll("input");
        for (let input of inputs) {
            if (!input.value.trim()) {
                alert("Veuillez remplir tous les champs.");
                return false;
            }
        }
        return true;
    }

    document.addEventListener("submit", (event) => {
        const form = event.target;

        if (form.classList.contains("table-form")) {
            if (!validateForm(form)) {
                event.preventDefault(); // Bloque la soumission si la validation échoue
            }
        }
    });


</script>
<script>
    // Fonction pour voir un mot de passe
    function showPassword(userId) {
        const passwordSpan = document.getElementById('password-' + userId);
        if (passwordSpan.style.display === 'none') {
            passwordSpan.style.display = 'inline';
        } else {
            passwordSpan.style.display = 'none';
        }
    }

    // Fonction pour ouvrir la modale de changement de mot de passe
    function updatePassword(userId) {
        document.getElementById('modal_user_id').value = userId;
        document.getElementById('passwordModal').style.display = 'block';
    }
</script>
<script>
    function loadEPIXCategories() {
    loadCommonCategories();
        currentType = "edge";
        loadCategoriesClasses("classEdgeCategorySelect");
        currentType = "node";
        loadCategoriesClasses("classNodeCategorySelect");
    }
</script>
<script src="AdminScript.js"></script>
<script src="SourceEdits.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Récupérer l'URL actuelle
        const currentUrl = window.location.href;
        const urlParams = new URLSearchParams(window.location.search);

        // **Étape 1 : Vérifiez si "make_cotation" est présent dans l'URL**
        if (urlParams.has("make_cotation")) {
            // Toutes les divisions ayant la classe subtab-content
            const subtabContents = document.querySelectorAll(".subtab-content");

            // Masquez toutes les sous-divisions
            subtabContents.forEach(content => content.classList.remove("active"));

            // Rendre `GLORIX-cotation` visible
            const cotationTab = document.getElementById("GLORIX-cotation");
            if (cotationTab) {
                cotationTab.classList.add("active");
            }

            // Activer également la classe active pour les sous-menus et l'onglet principal
            const cotationMenuTab = document.querySelector('.submenu-tab[data-subtab="GLORIX-cotation"]');
            if (cotationMenuTab) {
                cotationMenuTab.classList.add("active");
            }

            const glorixTab = document.querySelector('.tab[data-tab="GLORIX"]');
            if (glorixTab) {
                glorixTab.classList.add("active");
            }

            // Sauvegarder l'état actif dans localStorage
            localStorage.setItem("activeTab", "GLORIX");
            localStorage.setItem("activeSubtab", "GLORIX-cotation");
        }

        // **Étape 2 : Vérifiez si "source_id" est présent dans l'URL**
        if (urlParams.has("source_id")) {
            const sourceId = urlParams.get("source_id"); // Récupère la valeur de source_id

            // Vérifiez si la fonction EditCotationForm existe dans le contexte global
            if (typeof EditCotationForm === "function") {
                EditCotationForm(sourceId); // Appelle la fonction avec l'ID de la source
            } else {
                console.warn("La fonction EditCotationForm n'est pas définie !");
            }
        }
    });
</script>
<script>
    function toggleDefaultFavTool(toolId, toolimage) {
        fetch('Includes/PHP/UpdateFavoriteToolsDefault.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tool_id: toolId,
                user_id: 0, // Profil par défaut
                tool_image : toolimage
            }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Recharger la page pour refléter les changements
                } else {
                    alert('Échec de la mise à jour des outils favoris.');
                }
            })
            .catch(error => console.error('Erreur :', error));
    }
</script>

<?php }
else {echo "Vous n'avez pas accès à cette page";};
?>
<?php require_once 'Includes/PHP/add_fav_popup_admin.php';?>

<?php require("Includes/PHP/footer.php"); ?>
