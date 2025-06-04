<?php
session_start();
try {
// On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
// En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}
$user_id = (isset($_SESSION['id_actif'])) ? $_SESSION['id_actif'] : 0 ;
$user_admin = (isset($_SESSION['is_admin'])) ? $_SESSION['is_admin'] : 0;

?>

<?php if(isset($_POST['add_list'])) {?>
    <div class="popup-content">
        <h2>Nouvelle liste :</h2>
        <form method="" action="" class="popup-form">
            <label for="list-name">Nom de la liste : </label>
            <input type="text" id="list-name" name="list_name" required><br>
            <label for="parentSelect">Liste parent : </label>
            <select name="parent_category" id="parentSelect">
                <option value = -1> Liste principale (Aucune liste parent) </option>
                <?php //Generation automatique des catégories pour liste déroulante
                function gen_category_hierarchy_sources_($father, $temp, $bdd){
                    $query = 'SELECT * FROM sourcelists WHERE father = :father AND id <> 0';
                    $stmt = $bdd->prepare($query);
                    $stmt->execute(['father' => $father]);
                    $categories = $stmt->fetchAll();
                    foreach ($categories as $category) {
                        echo '<option value="' . $category['id'] . '" >' . $temp . $category['name'] . '</option>';
                        gen_category_hierarchy_sources_($category['id'], $category['name']." -> ", $bdd);
                    };}
                gen_category_hierarchy_sources_(-1, '', $bdd) ?>
            </select><br>
        </form>
        <div style="display: flex; justify-content: right">
            <button type="button" class = "empty-button" onclick="document.getElementById('popup-source').style.display = 'none'">Annuler</button>
            <button class="submit-button" onclick="Source_List_Added(document.getElementById('list-name').value, <?= isset($_SESSION['id_actif']) ? $_SESSION['id_actif'] : 0 ?>, document.getElementById('parentSelect').value, list_tags)" type="button" name="list">Ajouter</button>
        </div>
    </div>
<?php }
elseif (isset($_POST['key']) and $_POST['key'] == 'add_source') {?>

<div class="popup-content">
    <span class="close-button" onclick="Close_source_popup()">&times;</span>
    <h2>Ajouter une source :</h2>
    <form id="add-source-form" method="" action="" class="popup-form">
        <label for="source-name">Nom de la source : </label>
        <input type="text"  placeholder="Ex : DeepState UA - TG" id="source-name" name="source_name" required value ="" onblur="if (!regex.test(this.value)) {this.classList.add('red');}; suggest_source_tags(document.getElementById('source-tags').value, this.value,  document.getElementById('source-doc').value, document.getElementById('ListSelect').value, source_added_tags)"><br>
        <label for="source-url">Lien url : </label>
        <input type="url" id="source-url" placeholder="https://t.me/DeepStateUA" name="source_url" required value ="">
        <div style="display: flex">
            <label for="source-description">Description : </label>
            <textarea placeholder="Ex : Source pro-ukraine sur Telegram traitant du conflit Russie/Ukraine" id="source-doc" style="resize: vertical;" name="source_doc" required onblur="if (!regex.test(this.value)) {this.classList.add('red');}; suggest_source_tags(document.getElementById('source-tags').value, document.getElementById('source-name').value, this.value, document.getElementById('ListSelect').value, source_added_tags)"></textarea><br>
        </div>

        <label for="source-type">Type :</label>
        <select id="source-type" name="source_type" required>
            <option value="">Sélectionnez un type</option>
            <?php
            // Charger les types depuis la table sourcetypes
            function gen_source_type_list($bdd, $parent) {
                $query = 'SELECT * FROM sourcetypes WHERE parent = :parent';
                $stmt = $bdd->prepare($query);
                $stmt->execute(['parent' => $parent]);
                $types = $stmt->fetchAll();
                foreach ($types as $type) {
                    if ($type['endpoint'] == 2) {
                        $name = ($parent == "0") ? strtoupper(htmlspecialchars($type['name'])) : htmlspecialchars($type['name']);
                        echo '<optgroup label="' . $name . '">';
                        gen_source_type_list($bdd, $type['name']);
                        echo '</optgroup>';
                    }
                    elseif ($type['endpoint'] == 0) {
                        echo '<option value="' . $type['id'] . '">' . htmlspecialchars($type['name']) . '</option>';
                    }
                    else {
                        echo '<option disabled value="' . $type['id'] . '">' . htmlspecialchars($type['name']) . '</option>';
                    }
                }
            }
            gen_source_type_list($bdd, "0");
        ?>
        </select> <br>


        <label for="source-status">Statut :</label>
        <div id="source-status" class = "radio-gallery" style="margin-bottom : 2em">
            <?php
            // Charger les statuts depuis la table sourcestatus
            $query = 'SELECT * FROM sourcestatus';
            $stmt = $bdd->prepare($query);
            $stmt->execute();
            $statuses = $stmt->fetchAll();
            foreach ($statuses as $status) {
                echo '<div>';
                echo '<label style="width : max-content">';
                echo '<input type="radio" name="source_status" class="status-radio" value="' . $status['id'] . '" required>';
                echo ' ' . htmlspecialchars($status['name']);
                echo '</label><br>';
                echo '</div>';
            }
            ?>
        </div>






        <label for="new-tag">Tags : </label>
        <div id="new-tag" style="width : 30vw; display: flex; justify-content: space-between; margin-bottom : 1em">
            <input type="text" id="source-tags" name="source_tags_input" style="margin : 0" onkeyup="suggest_source_tags_og(this.value, document.getElementById('source-name').value,  document.getElementById('source-doc').value, document.getElementById('ListSelect').value, source_added_tags)" placeholder="Chercher un tag" autocomplete="off"><br>
<!--            <button type="button"  id="new-tag-button" onclick="Add_source_tags(0, '', --><?php //echo $user_id;?><!--)">Nouveau tag</button><br>-->
        </div>
        <label for="suggested_tags_list_source" style="text-align: right; padding : 5px; color : rgb(91,98,170)">Suggestions : </label>
        <div id="suggested_tags_list_source">
            <button type="button" onclick="source_list_scroll_og('-180')" class="scroll-button-left"></button>
            <div id="suggested_tags_source_content">
                <?php
                $query = 'SELECT * FROM sourcestags order by user'.$_SESSION['id_actif'].' DESC';
                $stmt = $bdd->prepare($query);
                $stmt->execute();
                $tags = $stmt->fetchAll();
                foreach ($tags as $tag) {
                    if ($tag['user'.$user_id] == 2) {
                        $color = 'background-color : rgba(255, 217, 56, 0.5)';
                    }
                    elseif ($tag['user'.$user_id] == 1) {
                        $color = 'background-color : rgba(124, 228, 249, 0.87)';
                    }
                    else {$color = "";}
                    echo '<button type="button" class="tag-div" onclick="Add_source_tags_og('.$tag['id'].', \''.$tag['name'].'\', '.$user_id.'); source_added_tags.push('.$tag['id'].');" style="padding:5px; '.$color.'">#'.$tag['name'].'</button>';
                };
                ?>
            </div>
            <button type="button" onclick="source_list_scroll_og('180')" class="scroll-button-right"></button>
            <button type="button" onclick="source_list_scroll_og('+1')" class="scroll-button-down" id="srce-scroll-button-down" style="right: 30px">+</button>
            <button type="button" onclick="source_list_scroll_og('-1')" class="scroll-button-up" id="srce-scroll-button-up" style="right: 30px">-</button>
        </div>
        <label for="tags-list" style="text-align: right; padding : 2px 5px; color : rgb(91,98,170)">Tags actifs : </label>
        <div id="tags_list" style="display: flex">
            <span></span>
            <div id="source_tags_list_content">
                <span id="missing_tags_source" style="color: grey;text-transform: uppercase;font-size: small;">Aucun tag actif</span>
            </div>
        </div>
        <div id="list-linking" style="display:none">
            <label for="ListSelect">Associer à une liste : </label>
            <select name="source_list" id="ListSelect" style="margin-bottom : 0.5em">
                <option value = -1> Veuillez sélectionner une liste </option>
                <?php //Generation automatique des catégories pour liste déroulante
                function gen_category_hierarchy_sources_($father, $temp, $bdd){
                    $query = 'SELECT * FROM sourcelists WHERE father = :father AND id <> 0 AND owner = '.$_SESSION['user_id'].' ';
                    $stmt = $bdd->prepare($query);
                    $stmt->execute(['father' => $father]);
                    $categories = $stmt->fetchAll();
                    foreach ($categories as $category) {
                        if (isset($_POST['edited_source']) && $category['id'] == $_POST['source_cat']) {
                            echo '<option value="' . $category['id'] . '" selected>' . $temp . $category['name'] . '</option>';
                        }
                        else {
                            echo '<option value="' . $category['id'] . '" >' . $temp . $category['name'] . '</option>';
                        }
                        gen_category_hierarchy_sources_($category['id'], $category['name']." -> ", $bdd);
                    };}
//                gen_category_hierarchy_sources_(-1, '', $bdd) ?>
            </select><br>
            <small style="margin-left : 10vw">(facultatif - le lien de la source sera simplement ajouté à cette liste)<br></small>
        </div>

        <div id="cotation-message" style="display: flex; margin-bottom : 3em">
            <span style="width : 10vw">Cotation :</span>
            <span  style="font-style: italic; color: dimgray; width : 30vw"> Pour demander une cotation de cette source par les opérateurs ROC, ouvrez les détails de la source depuis la page d'accueil dans la liste des sources.</span>
        </div>

        <div style="display: flex; justify-content: right">
            <?php
                $user_id_temp = (isset($_SESSION['id_actif'])) ? $_SESSION['id_actif'] : 0;
                echo  '<button type="button" class = "empty-button" onclick="Add_Source('.$user_id_temp.', '.$user_admin.')">Vider le formulaire</button>';
                echo '<button class="submit-button" onclick="Source_Added(document.getElementById(\'add-source-form\'), '.$user_id_temp.')" type="button" name="sources">Ajouter</button>';
            ?>
        </div>
    </form>
</div>
<script>
    document.addEventListener("click", function(event) {
        // Récupérer l'élément "suggested_tags_list_source"
        var suggestedTagsList = document.getElementById("suggested_tags_list_source");
        var suggestedTagsListContent = document.getElementById("suggested_tags_list_source_content");

        // Vérifier si le clic est en dehors de "suggested_tags_list_source"
        if (suggestedTagsList && suggestedTagsListContent) {
            if (!suggestedTagsList.contains(event.target)) {
                // Remettre la valeur de scroll à 0 (en haut et à gauche)
                suggestedTagsListContent.scrollTo({
                    top: 0,
                    left: 0,
                    behavior: "smooth" // Animation fluide
                });
            }
        }
    });
</script>

<?php }
elseif (isset($_POST['key']) and $_POST['key'] == 'add_link') {?>
    <div class="popup-content">
        <span class="close-button" onclick="Close_source_popup()">&times;</span>
        <h2><?php echo isset($_POST['edited_link']) ? "Modifier un lien :" : "Ajouter un lien :"; ?></h2>
        <form id="add-link-form" method="" action="" class="popup-form" style="display : inline-block">
            <input type="hidden" name="edited-link-id" value="<?= (isset($_POST['edited_link'])) ? $_POST['edited_link'] : 0 ?>">
            <label for="link-name">Nom de la page : </label>
            <input type="text" id="link-name" name="link_name" required
                    value="<?php if (isset($_POST['edited_link'])) {echo $_POST['link_name'];} else {echo "";}?>"
                    onblur="if (!regex.test(this.value)) {this.classList.add('red');};"><br>
            <label for="link-url">Lien url : </label>
            <input type="url" id="link-url" name="link_url" required onblur="updateBestSource('')" value ="<?php if (isset($_POST['edited_link'])) {echo $_POST['link_url'];} else {echo "";}?>">

            <div id="source-select-container" onmouseover="showSourceInput()" onmouseleave="hideSourceInput()">
                <label for="source-select">Source :</label>
                <select id="source-select" class="source-select" name="source_select" onchange="updateSourceLink()">
                    <option value=0 data-link="">Entrez un lien avant de choisir la source</option>
                    <!-- Options ajoutées dynamiquement via JS -->
                </select>
                <input type="text" id="source-link" name="source_link" placeholder="Lien de la source" >

            </div>

            <div style="display: flex">
                <label for="link-description">Description : </label>
                <textarea id="link-doc" style="resize: vertical;" name="link_doc" required onblur="if (!regex.test(this.value)) {this.classList.add('red');}; "><?php if (isset($_POST['edited_link'])) {echo $_POST['link_doc'];} else {echo "";}?></textarea><br>
            </div>
            <label for="ListSelect">Choisir une liste : </label>
            <select name="link_list" id="ListSelect">
                <option value = -1> Veuillez sélectionner une liste </option>
                <?php //Generation automatique des catégories pour liste déroulante
                function gen_category_hierarchy_links_($father, $temp, $bdd){
                    $query = 'SELECT * FROM sourcelists WHERE father = :father AND id <> 0';
                    $stmt = $bdd->prepare($query);
                    $stmt->execute(['father' => $father]);
                    $categories = $stmt->fetchAll();
                    foreach ($categories as $category) {
                        if (isset($_POST['edited_link']) && $category['id'] == $_POST['link_cat']) {
                            echo '<option value="' . $category['id'] . '" selected>' . $temp . $category['name'] . '</option>';
                        }
                        else {
                            echo '<option value="' . $category['id'] . '" >' . $temp . $category['name'] . '</option>';
                        }
                        gen_category_hierarchy_links_($category['id'], $category['name']." -> ", $bdd);
                    };}
                gen_category_hierarchy_links_(-1, '', $bdd) ?>
            </select><br>
            <label for="link-type">Type :</label>
            <select id="link-type" name="link_type" required>
                <option value="">Sélectionnez un type</option>
                <?php
                // Charger les types depuis la table sourcetypes
                function gen_link_type_list($bdd, $parent) {
                    $query = 'SELECT * FROM sourcetypes WHERE parent = :parent';
                    $stmt = $bdd->prepare($query);
                    $stmt->execute(['parent' => $parent]);
                    $types = $stmt->fetchAll();
                    foreach ($types as $type) {
                        if (isset($_POST['edited_link']) && $type['id'] == $_POST['source_type']) {
                            echo '<option value="' . $type['id'] . '" selected>' . htmlspecialchars($type['name']) . '</option>';
                        }
                        elseif ($type['endpoint'] == 2) {
                            echo '<optgroup label="' . htmlspecialchars($type['name']) . '">';
                            gen_link_type_list($bdd, $type['name']);
                            echo '</optgroup>';
                        }
                        elseif ($type['parent'] == 0) {
                            echo '<optgroup label="' . strtoupper(htmlspecialchars($type['name'])) . '">';
                            gen_link_type_list($bdd, $type['name']);
                            echo '</optgroup>';
                        }
                        else {
                            echo '<option value="' . $type['id'] . '">' . htmlspecialchars($type['name']) . '</option>';
                        }
                    }
                }
                gen_link_type_list($bdd, "0");
                ?>
            </select> <br>

            <div id="link-eval" style="margin-top: 20px; margin-bottom : 30px">
                <label style="font-weight: bold; margin-bottom : 10px">Caractérisation de l'information :</label><br><br>
                <?php $req = "SELECT question FROM evalquestions WHERE category='caracterisation_information'";
                $stmt = $bdd->prepare($req);
                $stmt->execute();
                $questions = $stmt->fetchAll();
                $questions = array_column($questions, 'question');?>
                <div id="link-eval-questions" style="margin-left : 20px">
                    <?php foreach ($questions as $question) {
                        echo '<input type="checkbox"  style="width : 10px; margin-right: 15px; margin-bottom: 10px">'.$question.'<br>';
                    }?>
                </div>
                <?php $previous_rating = isset($_POST['eval']) ? $_POST['eval'] : 1; ?>
                <div id="rating-input" class="rating" style="margin-left: 20px; width : fit-content">
                    <?php for ($i = 1; $i < $previous_rating; $i++) {
                         echo '<span class="star-big" data-value="'.$i.'" onclick="inputStars(this, document.querySelector(\'.rating .rating-value\'))">☆</span>';
                    };
                    for ($i=$previous_rating; $i <= 5; $i++) {
                        echo '<span class="star-big" data-value="'.$i.'" onclick="inputStars(this, document.querySelector(\'.rating .rating-value \'))">☆</span>';
                    }?>
                    <span class="rating-value">(0/5)</span>
                </div>
            </div>

            <script>

                var link_name = document.getElementById("link-name");
                var link_url = document.getElementById("link-url");
                var link_doc = document.getElementById("link-doc");
                var category_select = document.getElementById("ListSelect");


                function Add_link_tags($tag_id, $string, $user_id){
                    if ($user_id == 0) {
                        window.alert('Veuillez vous connecter pour ajouter un tag')
                        return
                    }
                    if ($string === '') {
                        return;
                    }
                    if ($tag_id == 0) {
                        xhr = new XMLHttpRequest();
                        xhr.open('POST', 'Includes/PHP/link_tags.php', true);
                        data = new FormData();
                        data.append('source_tags_name', $string);
                        data.append('key', 'search_source_tag');
                        xhr.send(data);
                        xhr.onreadystatechange = function() {
                            if (this.readyState == 4 && this.status == 200) {  // Je vérifie si le nom de tag entré n'est pas déjà existant, auquel cas, je ne fais qu'appliquer la deuxième partie de la fonction pour un tag déja existant
                                if (parseInt(this.responseText) > 0) {
                                    $tag_id = parseInt(this.responseText);
                                }
                                else {
                                    xhr = new XMLHttpRequest();
                                    xhr.open('POST', 'Includes/PHP/source_tags.php', true);
                                    data = new FormData();
                                    data.append('name', $string);
                                    data.append('key', 'new_source_tag_suggestion');
                                    xhr.send(data);
                                    xhr.onreadystatechange = function() {
                                        if (this.readyState == 4 && this.status == 200) {
                                            $tag_id = parseInt(this.responseText);
                                        }
                                    }
                                }
                            }
                            else {
                                Failure_Message('Une erreur est survenue lors de la recherche du tag :(');
                                document.getElementById('link-tags').style.border = '1px solid rgb(255, 0, 0)';
                                return;
                            }
                        }
                    }
                    console.log($tag_id);
                    if (link_added_tags.includes($tag_id)){
                        document.getElementById('link-tags').style.border = '1px solid rgb(255, 0, 0)';
                        document.getElementById('link-tags').ariaPlaceholder = 'Ce tag est déjà appliqué';
                        document.getElementById('link-tags').classList.add('red');
                        return
                    }
                    else {
                        console.log(this.responseText);
                        document.getElementById('missing_tags_link').style.display = 'none';
                        document.getElementById("link_tags_list_content").innerHTML += '<button type="button" id="active-link-tag-'+$tag_id+'" class="active-tag" onclick="Withdraw_link_tags('+$tag_id+','+$user_id+')" style="padding : 2px 0; color : rgb(91,98,170)">#' + $string +'</button>';
                        link_added_tags.push($tag_id);
                        document.getElementById('link-tags').classList.remove('red')
                    }
                }


                function Withdraw_link_tags($id, $user_id) {
                    if ($user_id == 0) {
                        window.alert('Veuillez vous connecter pour retirer un tag');
                        return;
                    }
                    link_added_tags.splice(link_added_tags.indexOf($id), 1);
                    document.getElementById("active-link-tag-"+$id).remove();
                    if (link_added_tags.length === 0) {
                        document.getElementById("missing_tags_link").style.display = "unset";
                    }
                }

            </script>
            <div style="display: flex; justify-content: right">
                <?php
                if (isset($_POST['edited_link'])) {
                    echo  '<button type="button" class = "empty-button" onclick="document.getElementById(\'popup-source\').style.display = \'none\')">';
                    echo "Annuler</button>";
                    echo '<button class="submit-button" onclick="Link_Added(
                            document.getElementById(\'link-name\').value, 
                            document.getElementById(\'link-url\').value, 
                            document.getElementById(\'link-doc\').value, 
                            document.getElementById(\'ListSelect\').value, 
                            document.getElementById(\'link-type\').value, 
                            '.$user_id.', 
                            '. $_POST['edited_link'].'
                        )" type="button" name="sources">Modifier</button>';
                }
                else {
                    $user_id_temp = (isset($_SESSION['id_actif'])) ? $_SESSION['id_actif'] : 0;
                    $user_id_temp = (isset($_SESSION['is_admin'])) ? $_SESSION['is_admin'] : 0;
                    echo  '<button type="button" class = "empty-button" onclick="Add_Source('.$user_id.', '.$user_admin.')">Vider le formulaire</button>';
                    echo '<button class="submit-button" onclick="Link_Added(
                            document.getElementById(\'link-name\').value, 
                            document.getElementById(\'link-url\').value, 
                            document.getElementById(\'link-doc\').value,
                            document.getElementById(\'source-select\').value,
                            document.getElementById(\'ListSelect\').value, 
                            document.getElementById(\'link-type\').value, 
                            '.$user_id.', 
                            0
                        )" type="button" name="sources">Ajouter</button>';                }
                ?>
            </div>
        </form>
    </div>
<?php }?>
<script>
    // Fermer la popup en cliquant sur la croix, pas de nécessité de distinction

    function Close_source_popup() {
        if (window.confirm('Attention vous perdrez toutes les modifications effectuées !')) {
            document.getElementById("popup-source").style.display = "none";
            const to_defilter_elts = document.getElementsByClassName("to_defilter");
            Array.from(to_defilter_elts).forEach((element) => {
                element.style.filter = "revert-layer";
            });
        }
    }

    const checkboxes = document.querySelectorAll('input.source-eval-item');
    const reliabilityInput = document.getElementById('source-reliability');
    function updateReliability() {
        console.log('updateReliability');
        var totalWeight = 0;
        var score = 0;
        for (let i = 0; i < checkboxes.length; i++) {
            totalWeight += parseInt(checkboxes[i].value);
        }
        const checkedBoxes = document.querySelectorAll('input.source-eval-item:checked');
        for (let i = 0; i < checkedBoxes.length; i++) {
            score += parseInt(checkedBoxes[i].value);
        }
        console.log(score);
        console.log(totalWeight);
        const reliability = Math.round(score / totalWeight * 100);
        console.log(reliability);
        document.getElementById('source-reliability').value = reliability;
    }


</script>
