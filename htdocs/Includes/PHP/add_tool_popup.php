<?php
session_start();
try {
// On se connecte à la bdd
$bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
// En cas d'erreur, on affiche un message et on arrête tout
die('Erreur : ' . $e->getMessage());
}
$user_id = isset($_SESSION['id_actif']) ? $_SESSION['id_actif'] : 0;
$user_admin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 0;
?>
<?php if(isset($_POST['add_category'])) {?>
    <div class="popup-content">
        <h2>Nouvelle catégorie :</h2>
        <form method="" action="" class="popup-form">
            <label for="cat-name">Nom de la catégorie : </label>
            <input type="text" id="cat-name" name="tool_name" required><br>
            <label for="parentSelect">Catégorie parent : </label>
            <select name="parent_category" id="parentSelect">
                <option value = 0> Catégorie principale (Aucune catégorie parent) </option>
                <?php //Generation automatique des catégories pour liste déroulante
                function gen_category_hierarchy_sources_2($father, $temp, $bdd){
                    $query = 'SELECT * FROM toolscategories WHERE parent = :parent';
                    $stmt = $bdd->prepare($query);
                    $stmt->execute(['parent' => $father]);
                    $categories = $stmt->fetchAll();
                    foreach ($categories as $category) {
                        if (isset($_POST['edited_tool']) && $category['id'] == $_POST['tool_cat']) {
                            echo '<option value="' . $category['id'] . '" selected>' . $temp . $category['name'] . '</option>';
                        }
                        else {
                            echo '<option value="' . $category['id'] . '" >' . $temp . $category['name'] . '</option>';
                        }
                        gen_category_hierarchy_sources_2($category['id'], $category['name']." -> ", $bdd);
                    };}
                gen_category_hierarchy_sources_2(0, '', $bdd) ?>
            </select><br>
        </form>
        <?php if(isset($_SESSION['is_admin']) and !$_SESSION['is_admin']) {
            echo '<br><span style="max-width: 30vw; margin-bottom: 20px; display: block; text-align: center "><b>NB</b> : votre proposition sera traitée par un administrateur. N\'hésitez pas à lui envoyer également une liste d\'outil à insérer dans cette catégorie</span>';
        }?>
        <div style="display: flex; justify-content: right">
        <button type="button" class = "empty-button" onclick="document.getElementById('popup-tool').style.display = 'none'">Annuler</button>
        <?php if(isset($_SESSION['is_admin']) and $_SESSION['is_admin']) {
            echo '<button class="submit-button" onclick="Tool_Cat_Added(1, document.getElementById(\'cat-name\').value, document.getElementById(\'parentSelect\').value, 0)" type="button" name="tools">Ajouter</button>';
        } elseif (isset($_SESSION['id_actif']))  {
            echo '<button class="submit-button" onclick="Tool_Cat_Added(0, document.getElementById(\'cat-name\').value, document.getElementById(\'parentSelect\').value, '.$_SESSION['id_actif'].')" type="button" name="tools">Proposer l\'ajout</button>';
        }
        ?></div>
    </div>
<?php }
else {?>
    <div class="popup-content">
        <span class="close-button" onclick="Close_tool_popup()">&times;</span>
        <?php if (isset($_POST['edited_tool'])) {echo '<h2>Modifier l\'outil :</h2>';} else {echo '<h2>Ajouter un outil :</h2>';} ?>
        <form method="" action="" class="popup-form">
            <label for="tool-name">Nom de l'outil : </label>
            <input type="text" placeholder="Ex: Yandex" id="tool-name" name="tool_name"  onblur="if (!regex.test(this.value)) {this.classList.add('red');}; suggest_tool_tags(document.getElementById('tool-tags').value, this.value,  document.getElementById('tool-doc').value, document.getElementById('categorySelect').value, added_tags)" required value ="<?php if (isset($_POST['edited_tool'])) {echo $_POST['tool_name'];} else {echo "";}?>" ><br>
            <label for="tool-url">Lien url : </label>
            <input type="url" id="tool-url" name="tool_url" onblur="" required value ="<?php if (isset($_POST['edited_tool'])) {echo $_POST['tool_url'];} else {echo "";}?>">
            <div style="display: flex">
                <label for="tool-description">Description : </label>
                <textarea placeholder="Ex : Moteur de recherche russe. efficace en matière de reverse image." id="tool-doc" style="resize: vertical; " name="tool_doc" required onblur="if (!regex.test(this.value)) {console.log('Validation échouée : caractères spéciaux détectés.'); this.classList.add('red');}; suggest_tool_tags(document.getElementById('tool-tags').value, document.getElementById('tool-name').value,  this.value, document.getElementById('categorySelect').value, added_tags)"><?php if (isset($_POST['edited_tool'])) {echo $_POST['tool_doc'];} else {echo "";}?></textarea><br>
            </div>
            <label for="categorySelect">Catégorie : </label>
            <select name="tool_category" id="categorySelect" onblur="suggest_tool_tags(document.getElementById('tool-tags').value, document.getElementById('tool-name').value,  document.getElementById('tool-doc').value, this.value, added_tags)">
                <option value = 0> 'Veuillez sélectionner une catégorie' </option>
                <?php //Generation automatique des catégories pour liste déroulante
                function gen_category_hierarchy_sources_2($father, $temp, $bdd){
                    $query = 'SELECT * FROM toolscategories WHERE parent = :parent';
                    $stmt = $bdd->prepare($query);
                    $stmt->execute(['parent' => $father]);
                    $categories = $stmt->fetchAll();
                    foreach ($categories as $category) {
                        if (isset($_POST['edited_tool']) && $category['id'] == $_POST['tool_cat']) {
                            echo '<option value="' . $category['id'] . '" selected>' . $temp . $category['name'] . '</option>';
                        }
                        else {
                            echo '<option value="' . $category['id'] . '" >' . $temp . $category['name'] . '</option>';
                        }
                        gen_category_hierarchy_sources_2($category['id'], $category['name']." -> ", $bdd);
                    };}
                gen_category_hierarchy_sources_2(0, '', $bdd) ?>
            </select><br>

            <label for="tool-tags">Tags : </label>
            <div id="new-tag" style="width : 30vw; display: flex; justify-content: space-between; margin-bottom : 1em">
                <script>
                    let added_tags =[];
                </script>
                <input type="text" id="tool-tags" name="tool_tags_input" style="margin : 0" onkeyup="suggest_tool_tags(this.value, document.getElementById('tool-name').value,  document.getElementById('tool-doc').value, document.getElementById('categorySelect').value, added_tags)" placeholder="Chercher ou créer un tag" autocomplete="off">
<!--                <button type="button"  id="new-tag-button" onclick="Add_tool_tags(0, '', --><?php //echo $user_id?><!--//)">Nouveau tag</button><br>-->
            </div>
            <label for="suggested_tags_list" style="text-align: right; padding : 5px; color : rgb(91,98,170)">Suggestions : </label>
            <div id="suggested_tags_list">
                <script>
                    document.addEventListener("click", function(event) {
                        // Récupérer l'élément "suggested_tags_list"
                        var suggestedTagsList = document.getElementById("suggested_tags_list");
                        var suggestedTagsListContent = document.getElementById("suggested_tags_list_content");

                        // Vérifier si le clic est en dehors de "suggested_tags_list"
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
                    function tools_list_scroll(n) {
                        var container = document.getElementById('suggested_tags_tools_content');
                        if (n === '+1') {
                            container.style.flexWrap = 'wrap'; // Permet d'aller à la ligne si nécessaire
                            document.getElementById('scroll-button-down').style.display = 'none';
                            document.getElementById('scroll-button-up').style.display = 'unset';
                        }
                        else if (n === '-1') {
                            container.style.flexWrap = 'unset'; // Permet d'aller à la ligne si nécessaire
                            document.getElementById('scroll-button-down').style.display = 'unset';
                            document.getElementById('scroll-button-up').style.display = 'none';
                        }
                        else {
                            container.scrollLeft += n;
                        }
                    }

                </script>
                <button type="button" onclick="tools_list_scroll('-180')" class="scroll-button-left"></button>
                <div id="suggested_tags_tools_content">
                    <?php
                    $query = 'SELECT * FROM toolstags';
                    $stmt = $bdd->prepare($query);
                    $stmt->execute();
                    $tags = $stmt->fetchAll();
                    foreach ($tags as $tag) {
                        echo '<button type="button" class="tag-div" onclick="Add_tool_tags('.$tag['id'].', \''.$tag['name'].'\', '.$user_id.')" style="padding:5px">#'.$tag['name'].'</button>';
                    };
                    ?>
                </div>
                <button type="button" onclick="tools_list_scroll('180')" class="scroll-button-right"></button>
                <button type="button" onclick="tools_list_scroll('+1')" class="scroll-button-down" id="scroll-button-down" style="right: 30px">+</button>
                <button type="button" onclick="tools_list_scroll('-1')" class="scroll-button-up" id="scroll-button-up" style="right: 30px">-</button>
            </div>
            <script>
                function suggest_tool_tags(input, name, doc, category, memory) {
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            var suggested_tags_tools_content = document.getElementById('suggested_tags_tools_content');
                            suggested_tags_tools_content.innerHTML = this.responseText;
                        }
                    }
                    xhr.open('POST', 'Includes/PHP/tool_tags.php', true);
                    data = new FormData();
                    data.append('input', input);
                    data.append('tool_name', name);
                    data.append('tool_doc', doc);
                    data.append('tool_category', category);
                    data.append('key', 'suggest_tool_tags');
                    data.append('user_id',<?php echo $user_id;?>)
                    data.append('memory', JSON.stringify(memory));
                    xhr.send(data);
                }
            </script>
            <label for="tags-list" style="text-align: right; padding : 2px 5px; color : rgb(91,98,170)">Tags actifs : </label>
            <div id="tags_list" style="display: flex">
                <span></span>
                <div id="tags_list_content">
                    <?php
                    if (!isset($_POST['tags_list']) OR empty(json_decode($_POST['tags_list']))) {echo '<span id="missing_tags" style="color: grey;text-transform: uppercase;font-size: small;">Aucun tag actif</span>';};
                    if (isset($_POST['edited_tool'])) {
                        $tool_id = $_POST['edited_tool'];
                        $req = $bdd->prepare('SELECT tags FROM tools WHERE id = :tool_id');
                        $req->execute(['tool_id' => $tool_id]);
                        $set_tags = $req->fetch();
                        $set_tags = json_decode($set_tags['tags']);
                        foreach ($set_tags as $tag) {
                            $req = $bdd->prepare('SELECT name FROM toolstags WHERE id = :tag_id');
                            $req->execute(['tag_id' => $tag]);
                            $tag_name = $req->fetch();
                            $tag_name = $tag_name['name'];
                            echo '<button type="button" id="active-tool-tag-'.strval($tag).'" class="active-tag" onclick="Withdraw_tool_tags('.$tag.','.$user_id.')" >#'.$tag_name.'</button>';

                        }
                    }?>
                </div>
            </div>

            <?php if (isset($user_admin)){
                if ($user_admin == 1){
                    echo '<label for="sharing" style="width: fit-content">Partager cet outil à tous les utilisateurs ? (Admin)</label> <input type="checkbox" name="sharing" id="sharing" value="1" style="width: 1vw; margin-left: 2vw"><br>';;
                }
                else { echo '<input type="hidden" name="sharing" value="0">';};
            } else {
                echo '<input type="hidden" name="sharing" value="0">';
            }

            ?>

            <script>
            //____________________GESTION_CARACTERES_SPECIAUX______________________________________________________________
                // Déclare la liste de caractères spéciaux autorisés
                const regex = /^[a-zA-ZÀ-ÿ0-9 '.,-/?\\]*$/; ; // Autoriser seulement les lettres (accentuées opu non), chiffres, espace, point et virgule

                // Fonction de validation
                function validateInputs() {
                    const popup = document.getElementById('popup-tool')
                    const inputs = popup.querySelectorAll('input[type="text"], textarea');
                     let isValid = true;

                    inputs.forEach(input => {
                        const value = input.value;
                        if (!regex.test(value)) {
                            input.classList.add('red'); // Ajouter la classe red si les caractères spéciaux non autorisés sont détectés
                            isValid = false;
                        } else {
                            input.classList.remove('red'); // Supprimer la classe red si l'entrée est correcte
                        }
                    });

                    return isValid;
                }

            //____________________ENVOI_FORMULAIRE______________________________________________________________
                var tool_name = document.getElementById("tool-name");
                var tool_url = document.getElementById("tool-url");
                var tool_doc = document.getElementById("tool-doc");
                var category_select = document.getElementById("categorySelect");
                var sharing = document.getElementById("sharing");


                function Add_tool_tags($tag_id, $string, $user_id){
                    if ($user_id == 0) {
                        window.alert('Veuillez vous connecter pour ajouter un tag')
                        return
                    }

                    else {
                        console.log(this.responseText);
                        if (document.getElementById('missing_tags')) {
                            document.getElementById('missing_tags').style.display = 'none';
                        }
                        document.getElementById("tags_list_content").innerHTML += '<button type="button" id="active-tool-tag-'+$tag_id+'" class="active-tag" onclick="Withdraw_tool_tags('+$tag_id+','+$user_id+')" style="padding : 2px 0; color : rgb(91,98,170)">#' + $string +'</button>';
                        added_tags.push($tag_id);
                        suggest_tool_tags(document.getElementById('tool-tags').value, document.getElementById('tool-name').value,  document.getElementById('tool-doc').value, document.getElementById('categorySelect').value, added_tags);
                        document.getElementById('tool-tags').classList.remove('red');
                    }
                }


                function Withdraw_tool_tags($id, $user_id) {
                    if ($user_id == 0) {
                        window.alert('Veuillez vous connecter pour retirer un tag');
                        return;
                    }
                    added_tags.splice(added_tags.indexOf($id), 1);
                    document.getElementById("active-tool-tag-"+$id).remove();
                    if (added_tags.length === 0) {
                        document.getElementById("missing_tags").style.display = "unset";
                    }
                }

            </script>
            <div style="display: flex; justify-content: right">
                <?php
                if (isset($_POST['edited_tool'])) {
                    echo  '<button type="button" class = "empty-button" onclick="'?>
                    Edit_Tool(
                            <?= htmlspecialchars($_POST['edited_tool']) ?>,
                                    1,
                            <?= htmlspecialchars($_POST['tool_cat']) ?>,
                            <?= htmlspecialchars(json_encode($_POST['tool_name'], JSON_HEX_TAG | JSON_HEX_QUOT)) ?>,
                            <?= '\''.htmlspecialchars($_POST['tool_url']).'\'' ?>,
                            <?= htmlspecialchars(json_encode($_POST['tool_doc'], JSON_HEX_TAG | JSON_HEX_QUOT)) ?>,
                            <?= htmlspecialchars(json_encode($_POST['tags_list'], JSON_HEX_TAG | JSON_HEX_QUOT)) ?>,
                            <?= htmlspecialchars($user_id) ?>,
                            <?= htmlspecialchars($user_admin) ?>
                                    )
                    <?php echo '">';
                    echo "Supprimer l'outil</button>";
                    echo '<button class="submit-button" onclick="Tool_Added(tool_name.value, tool_url.value, tool_doc.value, document.getElementById(\'categorySelect\').value, sharing.value, added_tags, '.$user_id.', '.$user_admin.', '.$_POST['edited_tool'].')" type="button" name="tools">Modifier</button>';
                }
                elseif (isset($user_id)) {
                    echo  '<button type="button" class = "empty-button" onclick="console.log(added_tags); Add_Tool(0, '.$user_id.', '.$user_admin.')">Vider le formulaire</button>';
                    echo '<button class="submit-button" onclick="Tool_Added(tool_name.value, tool_url.value, tool_doc.value, document.getElementById(\'categorySelect\').value, sharing.value, added_tags, '.$user_id.', '.$user_admin.', 0)" type="button" name="tools">Ajouter</button>';
                }
                else {
                    echo  '<button type="button" class = "empty-button" onclick="Add_Tool(0, '.$user_id.', '.$user_admin.')">Vider le formulaire</button>';
                    echo '<button class="submit-button" onclick="Tool_Added(tool_name.value, tool_url.value, tool_doc.value, document.getElementById(\'categorySelect\').value, sharing.value, added_tags, '. 0 .', '. 0 .', 0)" type="button" name="tools">Ajouter</button>';
                }
                ?>
            </div>

        </form>
    </div>
<?php }?>
<script>

    // Fermer la popup en cliquant sur la croix, pas de nécessité de distinction
    function Close_tool_popup() {
        if (window.confirm('Attention vous perdrez toutes les modifications effectuées !')) {
            document.getElementById("popup-tool").style.display = "none";
            const to_defilter_elts = document.getElementsByClassName("to_defilter");
            Array.from(to_defilter_elts).forEach((element) => {
                element.style.filter = "revert-layer";
            });
        }
    }

</script>
