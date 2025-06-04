<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> GLORIX Gestionnaire</title>
    <link rel="icon" href="Includes\Images\Icone Onglet.png"/>
    <link rel="stylesheet" href="STYLE\IndexStyle.css"/>
    <link rel="stylesheet" href="STYLE\GestionaireStyle.css"/>
    <link rel="stylesheet" href="STYLE\HeaderStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexEPIXStyle.css"/>
    <link rel="stylesheet" href="STYLE/IndexPopupStyle.css">
    <link rel="stylesheet" href="STYLE\FooterStyle.css"/>
    <link rel="stylesheet" href="STYLE\AdministrationStyle.css"/>
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
</head>
<body>
<?php require_once 'Includes/PHP/header.php'; ?>

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

if (!isset($_SESSION['id_actif'])) {
    header('Location: profils.php');
} else {

    $req = ('SELECT *, user'.$_SESSION['id_actif'].' as fav FROM osix.sourcestags ORDER BY fav DESC');
    $res = $bdd->query($req);
    $hashtags = $res->fetchAll();
    ?>

    <div class="tab-container" style="display: unset !important;">
        <div class="tab-buttons">
            <button class="tab-button" data-tab="search">Sources</button>
            <button class="tab-button" data-tab="lists">Listes</button>
            <button class="tab-button" data-tab="all-tags">Tags</button>
        </div>
        <div class="tab-contents">
            <div id="lists" class="tab-content">
                <div>
                    <span class="collapsible-title">Toutes mes listes </span><button class="collapsible-box collapsible-box-active to_defilter" style="border: none; padding: 0 10px"></button>
                    <span></span>
                    <div class="collapsible-content-box" style="max-height: fit-content">
                        <?php function gen_lists_of_links_active($n, $bdd, $user_id_in, $is_admin_in){
                            $query = 'SELECT * FROM sourcelists WHERE father = :father AND id <> 0 AND owner = :owner';
                            $stmt = $bdd->prepare($query);
                            $stmt->execute(['father' => $n, 'owner' => $user_id_in]);
                            $lists = $stmt->fetchAll();
                            foreach ($lists as $list) {?>
                                <?php
                                $visible = !$list['status'] ? 'style="background-color: #ffc1c175"' : '';
                                if ($n == -1) {
                                    $show_or_notshow = $list['status'] ? ' <button class="edit-icon" title="Masquer cette liste" onclick="hideList(' . htmlspecialchars($list['id'], ENT_QUOTES) . ')">' .
                                        '   <img class="grayscaled" src="Includes/Images/Bouton%20Supprimer.png" alt="Masquer" /> Masquer la liste' .
                                        '</button></div>' : ' <button class="edit-icon" title="Restaurer cette liste" onclick="restoreList(' . htmlspecialchars($list['id'], ENT_QUOTES) . ')">' .
                                        '   <img class="grayscaled" src="Includes/Images/Icone%20Plus.png" alt=" Restaurer" /> Restaurer la liste' .
                                        '</button></div>';
                                    echo '<button class="new-button collapsible-box to_defilter"></button>' .
                                        '  ' . htmlspecialchars($list['name'], ENT_QUOTES) .
                                        '<div class=""> <button class="icon-button" title="Ouvrir tous les liens de la liste" onclick="openAllSources(' . htmlspecialchars($list['id']) . ')">' .
                                        '   <img src="Includes/Images/Icone Eye.png" alt="Ouvrir tous les liens de la liste" /> Ouvrir toute la liste' .
                                        '</button>'.
                                        ' <button class="edit-icon" title="Masquer cette liste" onclick="ExtractList(' . htmlspecialchars($list['id'], ENT_QUOTES) . ')">' .
                                        '   📄 Extraire la liste' .
                                        '</button>'.
                                        $show_or_notshow;
                                } else {
                                    $show_or_notshow = $list['status'] ? ' <button class="edit-icon" title="Masquer cette liste" onclick="hideList(' . htmlspecialchars($list['id'], ENT_QUOTES) . ')">' .
                                        '   <img class="grayscaled" src="Includes/Images/Bouton%20Supprimer.png" alt="Masquer" />Masquer la liste' .
                                        '</button></div>' : ' <button class="edit-icon" title="Restaurer cette liste" onclick="restoreList(' . htmlspecialchars($list['id'], ENT_QUOTES) . ')">' .
                                        '   <img class="grayscaled" src="Includes/Images/Icone%20Plus.png" alt="Masquer" />Restaurer la liste' .
                                        '</button></div>';
                                    echo '<button class="new-button collapsible-box sub-button to_defilter"></button>' .
                                        '  ' . htmlspecialchars($list['name'], ENT_QUOTES) .
                                        ' <div class=""> <button class="icon-button" title="Ouvrir tous les liens de la liste" onclick="openAllSources(' . htmlspecialchars($list['id']) . ')">' .
                                        '   <img src="Includes/Images/Icone Eye.png" alt="Ouvrir tous les liens de la liste" />' .
                                        '</button>'.
                                        $show_or_notshow;
                                } ?>
                                <div class="collapsible-content" <?= $visible ?>>
                                    <?php echo '<ul id="ul-of-list-'.$list['id'].'">' ?>
                                    <?php  gen_lists_of_links_active($list['id'], $bdd, $user_id_in, $is_admin_in);
                                    $links = array_column($bdd -> query("SELECT * FROM links WHERE list =".$list['id'])->fetchAll(), 'id');
                                    ?>
                                    <?php foreach ($links as $linkid) {
//                                    echo var_dump($links);
                                        $linkcontent = $bdd->query("SELECT * FROM links WHERE id =".$linkid)->fetch();
                                        if (isset($linkcontent['owner'])) {
                                            echo '<li id="source'.$linkid.'">';
                                            $linkDoc = isset($linkcontent['doc']) ? htmlspecialchars($linkcontent['doc'],ENT_QUOTES) : null;
                                            echo '<a target="_blank" href="' . htmlspecialchars($linkcontent['url']) . '" title="' . $linkDoc . '">' . htmlspecialchars($linkcontent['name']) . '</a>'; ?>
                                            <button id="edit-link-button" class="edit-icon"
                                                    onclick="">
                                                <img src="Includes/Images/Bouton Modifier.png">
                                            </button>
                                            <?php if ($linkcontent['visible']) {
                                                echo '<button id="delete-source-button" class="edit-icon"
                                                    onclick="hideLink('.$linkid.')";}  ">
                                                <img src="Includes/Images/Bouton%20Supprimer.png">
                                            </button>';}
                                            else {
                                                echo '<button id="delete-source-button" class="edit-icon"
                                                    onclick="restoreLink('.$linkid.')";}  ">
                                                <img src="Includes/Images/Icone%20Plus.png">
                                            </button>';}
                                        }
                                    }
                                    ?>
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
                        <?php gen_lists_of_links_active(-1, $bdd, $user_id, $user_admin); ?>
                    </div>
                </div>
                <div>
                    <span class="collapsible-title">Listes de mes groupes : </span><button class="collapsible-box collapsible-box-active to_defilter" style="border: none; padding: 0 10px"></button>
                    <span></span>
                    <div class="collapsible-content-box" style="max-height: fit-content">
                        <?php function gen_lists_of_links_gest_groups($n, $bdd, $user_id_in, $is_admin_in){
                            $query = 'SELECT * FROM sourcelists WHERE father = :father AND id <> 0 AND owner = :owner AND status = 0 ORDER BY lastuse DESC';
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
                                        ' <button class="edit-icon" title="Activer cette liste" onclick="restoreList(' . htmlspecialchars($list['id'], ENT_QUOTES) . ')">' .
                                        '   <img class="grayscaled" src="Includes/Images/Icone%20Plus.png" alt="Activer" />' .
                                        '</button></div>';
                                } else {
                                    echo '<button class="new-button collapsible-box sub-button to_defilter"></button>' .
                                        '  ' . htmlspecialchars($list['name'], ENT_QUOTES) .
                                        ' <div class="glorix-button-div"> <button class="icon-button" title="Ouvrir tous les liens de la liste" onclick="openAllSources(' . htmlspecialchars($list['id']) . ')">' .
                                        '   <img src="Includes/Images/Icone Eye.png" alt="Ouvrir tous les liens de la liste" />' .
                                        '</button>'.
                                        ' <button class="edit-icon" title="Masquer cette liste" onclick="hideList(' . htmlspecialchars($list['id'], ENT_QUOTES) . ')">' .
                                        '   <img class="grayscaled" src="Includes/Images/Icone%20Plus.png" alt="Activer" />' .
                                        '</button></div>';
                                } ?>
                                <div class="collapsible-content">
                                    <?php echo '<ul id="ul-of-list-'.$list['id'].'">' ?>
                                    <?php  gen_lists_of_links_gest_groups($list['id'], $bdd, $user_id_in, $is_admin_in);
                                    $links = json_decode($list['links'], true);?>
                                    <?php foreach ($links as $linkid) {
//                                    echo var_dump($links);
                                        $linkcontent = $bdd->query("SELECT * FROM links WHERE id =".$linkid)->fetch();
                                        if (isset($linkcontent['owner'])) {
                                            echo '<li id="source'.$linkid.'">';
                                            $linkDoc = isset($linkcontent['doc']) ? htmlspecialchars($linkcontent['doc'],ENT_QUOTES) : null;
                                            echo '<a target="_blank" href="' . htmlspecialchars($linkcontent['url']) . '" title="' . $linkDoc . '">' . htmlspecialchars($linkcontent['name']) . '</a>'; ?>
                                            <button id="edit-link-button" class="edit-icon"
                                                    onclick="">
                                                <img src="Includes/Images/Bouton Modifier.png">
                                            </button>
                                            <button id="delete-source-button" class="edit-icon"
                                                    onclick="hideLink(<?= $linkid?>)">
                                                <img src="Includes/Images/Bouton%20Supprimer.png">
                                            </button>
                                        <?php }
                                    }
                                    ?>
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
                        <?php gen_lists_of_links_gest_groups(-1, $bdd, $user_id, $user_admin); ?>
                    </div>

                </div>

            </div>


            <div id="search" class="tab-content active-content">
                <h3>Rechercher des sources</h3>
                <div class="search-filters">
                    <div class="search-filters-left">
                        <div id="checkboxes">
                            <div class="checkbox-container">
                                <input type="checkbox" class="checkbox-mine" value="mine" id="checkbox-mine"/>
                                <label for="checkbox-mine">
                                    <button class="edit-icon" style="margin-left: 0">
                                        <img src="Includes/Images/Icone Checked.png" alt="Checked Icon">
                                    </button>
                                    Mes sources uniquement
                                </label>
                                <input type="checkbox" class="checkbox-hidden" value="hidden" id="checkbox-hidden"/>
                                <label for="checkbox-hidden">
                                    <button class="edit-icon" style="margin-left: 0">
                                        <img src="Includes/Images/Icone Plus.png" alt="Plus Icon">
                                    </button>
                                    Nouvelles sources uniquement
                                </label>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center; margin-top: 2em">
                            <label for="tag-input-bar-box" style="text-align: left; padding: 5px; color: rgb(91,98,170)" >Chercher un tag :</label>
                            <input type="text" id="tag-input-bar-box" style="display: flex; max-width: 10vw;" placeholder="Chercher un tag" class="unbordered-modern">
                        </div>
                        <div style="display: flex; gap: 10px; margin-bottom: 15px; align-items: center;">
                            <label for="hashtag-buttons-container" style="color: #5B62AA;">Filtrer par hashtags</label>
                            <div id="hashtag-buttons-container" style="display: flex; align-items: center; max-width: 35vw; flex-wrap: wrap;"></div>
                        </div>

                        <div style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                            <label for="selected-tags-list" style="color: #5B62AA;">Tags sélectionnés :</label>
                            <div id="selected-tags-list"><!-- Les tags sélectionnés apparaîtront ici --></div>
                            <button class="clear-button hidden" id="clear-selected-tags" title="Supprimer tous les tags">✖</button>
                        </div>

                        <div style="margin: 20px 0;">
                            <label for="start-date"><strong>Date de début :</strong></label>
                            <input type="date" id="start-date" class="unbordered-modern"/>
                            <button class="clear-button hidden" id="clear-start-date">✖</button>
                            <label for="end-date"><strong>Date de fin :</strong></label>
                            <input type="date" id="end-date" class="unbordered-modern"/>
                            <button class="clear-button hidden" id="clear-end-date">✖</button>
                        </div>

                        <div style="margin: 20px 0;">
                            <label for="sort_selection"><strong>Trier par :</strong></label>
                            <select id="sort_selection" class="unbordered-modern">
                                <option value="category">Catégories</option>
                                <option value="date-asc">Dernier usage croissante</option>
                                <option value="date-desc">Dernier usage décroissante</option>
                                <option value="name-asc">Nom (A-Z)</option>
                                <option value="name-desc">Nom (Z-A)</option>
                                <option value="date-asc">Date croissante</option>
                                <option value="date-desc">Date décroissante</option>
                            </select>
                        </div>

                        <div style="margin-top: 40px;">
                            <input type="text" id="search-bar" style="color: white;" placeholder="Rechercher dans la table">
                            <img src="Includes/Images/Search Icon.png" alt="Search Icon" style="width: 25px; height: 25px; position: relative; left: -40px; top: 7px;">
                            <button class="clear-button hidden" id="clear-search-bar">✖</button>
                        </div>
                    </div>

                    <div class="search-filters-right">
                        <button id="export-excel-button" style="background-color: rgba(51,110,202,0.7);">Ouvrir dans Excel</button>
                    </div>
                </div>

                <div class="search-results">
                    <h3>Résultats</h3>
                    <table id="results-table">
                        <div id="loading-overlay" style="display: none; position: absolute; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 10; justify-content: center; align-items: center;">
                            <div class="loading-spinner" style="width: 50px; height: 50px; border: 5px solid #ccc; border-top: 5px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        </div>
                        <thead>
                        <tr>
                            <th style="text-align: center;">Image</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Tags</th>
                            <th>URL</th>
                            <th>Cotation</th>
                            <th>Date d'insertion</th>
                            <th>Dernier usage</th>
                        </tr>
                        </thead>
                        <tbody id="results-tbody">
                        <!-- Les résultats de recherche seront insérés ici via JS -->
                        </tbody>
                    </table>
                </div>
            </div>


            <div id="all-tags" class="tab-content" style="width: 100%">
                <div style="padding: 2%">
                    <h2>Mes tags favoris : </h2>
                    <br><br>
                    <p style="font-style : italic; width: 40vw; font-size: small; text-align : justify">
                        Les tags favoris apparaîtront en premier et en jaune lorsque vous ajouterez ou chercherez des sources. En deuxième et en pleu ciel appraîtront les tags favoris pour l'un des groupes auquel vous appartenez. Enfin viendront l'ensemble des autres tags. Choisissez vos tags favoris en cliquant sur une case à cocher !
                    </p>
                    <br><br>
                    <div id="loading-overlay2" style="display: none; position: absolute; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 10; justify-content: center; align-items: center;">
                        <div class="loading-spinner" style="width: 50px; height: 50px; border: 5px solid #ccc; border-top: 5px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    </div>
                    <div id="all-tags-container">

                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            console.log('Tags triés?');
                            var xhr = new XMLHttpRequest();
                            xhr.open('GET', 'Includes/PHP/get_all_tags.php', true);
                            xhr.onload = function () {
                                document.getElementById('all-tags-container').innerHTML = xhr.responseText;
                                console.log('Tags Triés !')
                            }
                            xhr.send();
                        })
                    </script>

                </div>

            </div>

    </div>

<?php } ?>

<?php require_once 'Includes/PHP/footer.php'; ?>

<!-- Script tableau tags -->
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
    xhreq.open("POST", "Includes/PHP/GLORIX_Gest_bdd.php", true);
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
        console.log(searchText);

        // Obtenir les hashtags sélectionnés
        const selectedTags = Array.from(
            document.querySelectorAll("#selected-tags-list .selected-tag")
        ).map(tag => tag.id.replace("selected-tag-", ""));

        // Afficher le voile et l'anneau de chargement
        const loadingOverlay = document.getElementById("loading-overlay");
        loadingOverlay.style.display = "flex";

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "Includes/PHP/GLORIX_Gest_bdd.php", true);
        xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        xhr.onerror = function () {
            console.error("Erreur réseau ou URL inaccessible.");
        };
        xhr.onload = function () {
            console.log("Requête");
            if (xhr.status === 200) {
                document.getElementById("results-tbody").innerHTML = xhr.responseText;
            }
            loadingOverlay.style.display = "none";
        };
        console.log('Niv2');
        xhr.send(JSON.stringify({
            startDate,
            endDate,
            searchText,
            sort_selection,
            mine: document.getElementById("checkbox-mine").checked,
            hidden: document.getElementById("checkbox-hidden").checked,
            selected_tags: selectedTags // Inclure les hashtags sélectionnés
        }));
        console.log('Niv3');
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

    function Edit_Tool_Gest($flag, $user_id, $name, $tool_id) {
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
            data.append('tool_id', $id);
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
                // Vérifier si une image est présente et l'exclure
                if (!cell.querySelector("img")) {
                    excelContent += `<td>${cell.innerText}</td>`;
                }
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

        // Créer un lien pour téléchargement ou ouverture dans Excel
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
        function toggleHashtag(tagId, tagName, fav) {
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
                    tagButton.onclick = () => toggleHashtag(tagId, tagName, fav);

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
                newTagButton.onclick = () => toggleHashtag(tagId, tagName, fav);
                selectedTagsList.appendChild(newTagButton);

                // Supprimer le tag de la liste des suggestions
                const buttonToRemove = document.querySelector(`#available-tag-${tagId}`);
                if (buttonToRemove) buttonToRemove.remove();
            }

            // Rafraîchir les résultats
            updateResults();
        }        // Générer les boutons des hashtags au chargement

        hashtags.forEach(({ id, name, fav }) => {
            const button = document.createElement("button");
            button.id = `available-tag-${id}`;
            button.className = "tag-div";
            button.textContent = `#${name}`;
            button.dataset.fav = fav; // Ajouter l'attribut data-fav pour les filtres
            button.onclick = () => toggleHashtag(id, name, fav);
            switch (fav) {
                case 2:
                    button.style.backgroundColor = "rgba(255,217, 56,0.50)";
                    break;
                case 1:
                    button.style.backgroundColor = "rgba(124, 228, 249, 0.87)";
                    break;
                default :
                    button.style.display= "none";
                    break;
            }
            hashtagButtonsContainer.appendChild(button);
        });

        // Création du bouton +
        const showBtn = document.createElement("button");
        const hideBtn = document.createElement("button");


        showBtn.textContent = "+";
        showBtn.title = "Afficher les boutons avec data-fav=0";
        showBtn.id = "showTagsButton";
        showBtn.className = "extentToolDivButton";
        showBtn.onclick = () => {
            // Afficher les boutons ayant data-fav=0
            const buttons = document.querySelectorAll('.tag-div[data-fav="0"]');
            buttons.forEach(b => b.style.display = "block");
            showBtn.style.display = "none";
            hideBtn.style.display = "block";
        };
        hashtagButtonsContainer.appendChild(showBtn);


        hideBtn.textContent = "-";
        hideBtn.id = "hideTagsButton";
        hideBtn.title = "Masquer les boutons avec data-fav=0";
        hideBtn.className = "extentToolDivButton";
        hideBtn.style.display = "none";
        hideBtn.onclick = () => {
            // Masquer les boutons ayant data-fav=0
            const visibleButtons = document.querySelectorAll('#hashtag-buttons-container .tag-div[data-fav="0"]');
            visibleButtons.forEach(button => {
                const buttons = document.querySelectorAll('.tag-div[data-fav="0"]');
                buttons.forEach(b => b.style.display = "none");
                showBtn.style.display = "block";
                hideBtn.style.display = "none";
            });
        };
        hashtagButtonsContainer.appendChild(hideBtn);




        // Fonction pour rafraîchir les événements après mise à jour dynamique
        function rebindEvents() {
            hashtags.forEach(({ id, name, fav }) => {
                const button = document.getElementById("available-tag-" + id);
                if (button) {
                    button.id = `available-tag-${id}`;
                    button.className = "tag-div";
                    button.textContent = `#${name}`;
                    button.onclick = () => toggleHashtag(id, name, fav);
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

            const data = `key=suggest_tool_tags_gest&input=${encodeURIComponent(inputValue)}&tool_name=${encodeURIComponent(toolName)}&tool_doc=${encodeURIComponent(toolDoc)}&tool_category=${encodeURIComponent(toolCategory)}&user_id=${encodeURIComponent(userId)}&memory=${encodeURIComponent(memory)}`;

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
        const tagInputBar = document.getElementById("tag-input-bar-box")
        tagInputBar.addEventListener("input", function () {
            const inputValue = tagInputBar.value.trim();
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
    });
</script>


<script>
    function AddFavTag(tag_id,flag) {
        console.log('Niveau 1');
        console.log(flag);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/update_source_tags.php', true);
        console.log('Niveau 1.2');
        // Afficher le voile et l'anneau de chargement
        const loadingOverlay2 = document.getElementById("loading-overlay2");
        loadingOverlay2.style.display = "flex";
        console.log('Niveau 1.3');
        xhr.onload = function () {
            if (this.status === 200) {
                console.log('Niveau 2');
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'Includes/PHP/get_all_tags.php', true);
                xhr.onload = function () {
                    console.log('Niveau 3');
                    document.getElementById('all-tags-container').innerHTML = xhr.responseText;
                    console.log('Tags Triés !')
                    loadingOverlay2.style.display = "none";
                }
                xhr.send();
            } else {
                console.log('Erreur : ' + xhr.status);
            }
        }
        var data = new FormData();
        data.append('tag_id', tag_id);
        data.append('flag', flag)
        xhr.send(data);
    }

</script>
<script>
    function ExtractList(listId) {
        // Redirige vers le script PHP pour créer et télécharger le fichier CSV
        window.location.href = 'Includes/PHP/ExtractList.php?extract_list_id=' + listId;
    }
</script>

<script src="SourceEdits.js"></script>
<script src="Javascript.js"></script>
<div id="BOX-Gest-failure-message" class="gest-success-message" style="background-color: crimson">L'opération a échoué"</div>


</body>
</html>
