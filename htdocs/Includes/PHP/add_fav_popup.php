<!-------------- Pour BOX ------------------->



<div class="popup" id="popup-fav-tool">
    <div class="popup-content">
        <span class="close-button" onclick="document.getElementById('popup-fav-tool').style.display = 'none'">×</span>        <h2>Ajouter en favoris :</h2>
        <div style="display: flex; justify-content: space-between">
                <form method="" action="http://osix.rin" class="popup-form">
                    <input type="hidden" name="form_type" value="add_fav_tool">
                    <label for="tool" style="float: inherit">Sélectionner l'outil :</label>
                    <select name="tool" id="tool-select" style="width: 23vw; margin-left: 2vw;" onblur="showImage()">
                        <option value=0>Sélectionner un outil</option>
                        <?php
                        $query = 'SELECT * FROM tools WHERE user'.$user_id.' = 1 OR user0 = 1';
                        $stmt = $bdd->prepare($query);
                        $stmt->execute();
                        $tools = $stmt->fetchAll();

                        $req = 'SELECT tools FROM favoritetools WHERE user_id = :user_id';
                        $stmt = $bdd->prepare($req);
                        $stmt->execute(['user_id' => $user_id]);
                        $favorites_assoc = json_decode($stmt->fetch()['tools'], true);
                        $favorites = array();
                        foreach ($favorites_assoc as $tool => $tool_img) {
                            array_push($favorites, $tool);
                        }

                        foreach ($tools as $tool) {
                            if(! in_array($tool['id'], $favorites)) {
                                echo '<option value="' . $tool['id'] . '">' .$tool['name']. '</option>';
                            }
                        };?>
                    </select><br>
                    <script>
                        function showImage() {
                            tool_id = document.getElementById("tool-select").value;
                            if (tool_id == 0) {
                                document.getElementById("status").innerHTML = '';
                                return
                            }
                            xhr = new XMLHttpRequest();
                            xhr.onreadystatechange = function() {
                                if (this.readyState == 4 && this.status == 200) {
                                    console.log('YEs')
                                    document.getElementById("status").innerHTML = '<label for="default-icon-tool">Icône par défaut :</label><img id="default-icon-tool" src="' + this.responseText + '" style="height : 4em">';
                                }
                                else if (this.readyState == 4) {
                                    console.log('NO');
                                    console.log(this.readyState);
                                    console.log(this.status);
                                    document.getElementById("status").innerHTML = '<label for="default-icon-tool">Icône par défaut :</label><img id="default-icon-tool" src="Fav_Icons\Default_Tool.png" style="height : 4em">';
                                }
                            }
                            xhr.open('POST', 'Includes/PHP/add_fav_bdd.php', true)
                            data = new FormData();
                            data.append('tool_id', tool_id);
                            data.append('key', 'require_tool_image');
                            xhr.send(data);
                        }
                    </script>
                    <label for="file" style="width: 20vw">Veuillez sélectionner une icône dans vos fichiers : </label>
                    <input style = "width : 15vw" type="file" name="fav_icon" id="file">
                    <div id="status"></div>
                    <div style="display: flex; justify-content: right">
                        <?php echo '<button onclick="Fav_Tool_Added()" type="button" name="tools">Ajouter</button>' ?>
                    </div>
                </form>
            <!-- Prévisualisation -->
            <div id="preview-container2" style="flex: 1; display: none; margin-left: 50px;">
                <img id="preview-image2" src="#" alt="Prévisualisation" style="max-width: 150px; max-height: 150px; border : 0; padding: 5px;">
            </div>
        </div>
    </div>
</div>

<script>
    // Fermer la popup en cliquant sur la croix, pas de nécessité de distinction
    function closeFavPopup() {
        console.log("test")
        document.getElementById("popup-fav-tool").style.display = "none";
        const to_defilter_elts = document.getElementsByClassName("to_defilter");
        Array.from(to_defilter_elts).forEach((element) => {
            element.style.filter = "revert-layer";
        });
    };

    function Fav_Tool_Added(){
        if (<?php echo $user_id ?> == 0) {
            window.alert('Veuillez vous connecter pour réaliser cette action !');
            document.getElementById("popup-fav-tool").style.display = "none";
            return
        }
        else if (document.getElementById("tool-select").value == 0){
            document.getElementById("tool-select").style.border = "1px solid red";
            return
        }
        const to_defilter_elts = document.getElementsByClassName("to_defilter");
        Array.from(to_defilter_elts).forEach((element) => {
            element.style.filter = "revert-layer";
        });
        var tool_id = document.getElementById("tool-select");
        var iconInput = document.getElementById("file");
        var icon = iconInput.files[0];
        if (!file) {
            document.getElementById("status").innerHTML = "Aucune image sélectionnée.";
            return;
        }
        var data = new FormData();
        xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                window.location.reload();
            }
        }
        data.append('icon', icon);
        data.append('tool_id', tool_id.value);
        data.append('user_id', <?php echo $user_id ?>);
        data.append('form_type', 'add_fav_tool');
        xhr.open('POST', 'Includes/PHP/add_fav_bdd.php', true);
        xhr.send(data);
    }


    function deleteFavTool(tool_id){
        if (<?php echo $user_id ?> == 0) {
            if (window.confirm("Voulez-vous vraiment supprimer cet outil de vos favoris ?")) {
                window.alert('Veuillez vous connecter pour réaliser cette action !');
                return;
            }
        }
        if (window.confirm("Voulez-vous vraiment supprimer cet outil de vos favoris ?")) {
            console.log(<?php echo $user_id ?>)
            var data = new FormData();
            data.append('tool_id', tool_id);
            data.append('user_id', <?php echo $user_id ?>);
            data.append('form_type', 'delete_fav_tool');

            fetch('Includes/PHP/add_fav_bdd.php', {
                method: 'POST',
                body: data
            })
                .then(response => {
                    document.getElementById("FavTool" + tool_id).remove();
                })
        }
    }

</script>

<!-------------- Pour GLORIX ------------------->


<div class="popup" id="popup-fav-link">
    <div class="popup-content">
        <span class="close-button" onclick="closeFavPopup()">&times;</span>
        <h2>Ajouter une lien favori :</h2>
        <div style="display: flex; justify-content: space-between">



        <form method="" action="http://osix.rin" class="popup-form">
            <input type="hidden" name="form_type" value="add_fav_link" style="width: unset">
            <!-- Nom -->
            <label for="fav-name">Nom du favori :</label>
            <input type="text" name="link_name" style="width:20vw" id="link-name" placeholder="Entrez le nom du favori" required>
            <br>
            <!-- Lien -->
            <label for="link-url">URL :</label>
            <input type="url" name="link_url" style="width:20vw"  id="link-url" placeholder="Entrez l'URL" required onblur="updateBestSourceForFav(this.value)">


            <!-- Icône -->
            <div style="flex:1;">
                <label for="fav-icon">Icône (.png ou .jpg) :</label>
                <input type="file" name="fav_icon" id="fav-icon" accept="image/png, image/jpeg" style="width: unset" required>
            </div>

            
<!--             Icône -->
<!--            <label for="fav-link-icon" style="width : 20vw">Icône du favori (formats png et jpeg uniquement) :</label>-->
<!--            <input type="file" name="fav_link_icon" id="fav-link-icon" accept="image/png, image/jpeg" required>-->
<!--            <br>-->

            <!-- Source -->
            <label for="source" style="float: inherit">Relier à une source :</label>
            <select name="source" class="source-select" id="source-select-for-fav" style="width: 20vw; margin-left: 3vw;">
                <option value=0>Insérer le lien</option>

            </select><br>


        </form>

        <!-- Prévisualisation -->
        <div id="preview-container" style="flex: 1; display: none; margin-left: 50px;">
            <img id="preview-image" src="#" alt="Prévisualisation" style="max-width: 150px; max-height: 150px; border : 0; padding: 5px;">
        </div>

        </div>
        <div id="fav-link-status"></div>
        <div style="display: flex; justify-content: right">
            <?php echo '<button onclick="Fav_Source_Added()" type="button" name="sources">Ajouter</button>' ?>
        </div>
    </div>
</div>

<script>
    // Fermer la popup en cliquant sur la croix, pas de nécessité de distinction
    function closeFavPopup() {
        console.log("test")
        document.getElementById("popup-fav-link").style.display = "none";
        const to_defilter_elts = document.getElementsByClassName("to_defilter");
        Array.from(to_defilter_elts).forEach((element) => {
            element.style.filter = "revert-layer";
        });
    };

    function Fav_Source_Added(){
        const to_defilter_elts = document.getElementsByClassName("to_defilter");
        Array.from(to_defilter_elts).forEach((element) => {
            element.style.filter = "revert-layer";
        });

        var link_name = document.getElementById("link-name");
        var link_url = document.getElementById("link-url");
        var iconInput = document.getElementById("fav-icon");
        var source_id = document.getElementById("source-select-for-fav");
        link_name.classList.remove('red');
        link_url.classList.remove('red');
        iconInput.classList.remove('red');
        var unvalid = false;
        if (!link_name.value) {
            link_name.classList.add('red');
            unvalid = true;
        }
        if (!link_url.value) {
            link_url.classList.add('red');
            unvalid = true;
        }
        if (!iconInput.files[0]) {
            iconInput.classList.add('red');
            unvalid = true;
        }
        if (unvalid) {
            document.getElementById("fav-link-status").innerHTML = "<span style='color : #ff0000'> Veuillez remplir tous les champs. </span>";
            return;
        }

        var icon = iconInput.files[0];

        var data = new FormData();
        xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200 && this.responseText == "OK") {
                window.location.reload();
            }
        }
        data.append('icon', icon);
        data.append('link_name', link_name.value);
        data.append('link_url', link_url.value);
        data.append('source_id', source_id.value);
        data.append('user_id', <?php echo $user_id ?>);
        data.append('key', 'add_fav_link');
        xhr.open('POST', 'Includes/PHP/glorix_bdd.php', true);
        xhr.send(data);
    }


    function deleteGenSource(source_id){
        if (window.confirm("Voulez-vous vraiment supprimer cette source de vos favoris ?")) {
            console.log(<?php echo $user_id ?>)
            var data = new FormData();
            data.append('source_id', source_id);
            data.append('user_id', <?php echo $user_id ?>);
            data.append('form_type', 'delete_fav_source');

            fetch('Includes/PHP/add_fav_bdd.php', {
                method: 'POST',
                body: data
            })
                .then(response => {
                    document.getElementById("FavSource" + source_id).remove();
                })
        }
    }

    // Gestion de la prévisualisation d'image
    document.getElementById("fav-icon").addEventListener("change", function (event) {
        const file = event.target.files[0]; // Obtenir le fichier sélectionné
        const previewContainer = document.getElementById("preview-container");
        const previewImage = document.getElementById("preview-image");

        if (file) {
            // Vérifiez si le fichier est une image
            const validImageTypes = ["image/jpeg", "image/png"];
            if (!validImageTypes.includes(file.type)) {
                alert("Veuillez sélectionner une image valide (JPEG ou PNG uniquement).");
                event.target.value = ""; // Réinitialiser le champ si type invalide
                previewContainer.style.display = "none";
                return;
            }

            // Lire et afficher l'image
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result; // Définir l'URL de l'image
                previewContainer.style.display = "block"; // Afficher la section de prévisualisation
            };
            reader.onerror = function () {
                alert("Impossible de lire le fichier. Veuillez réessayer.");
                previewContainer.style.display = "none";
            };
            reader.readAsDataURL(file); // Lire le fichier
        } else {
            // Si aucune image n'est sélectionnée, cacher la prévisualisation
            previewContainer.style.display = "none";
        }
    });
    // Gestion de la prévisualisation d'image
    document.getElementById("file").addEventListener("change", function (event) {
        const file = event.target.files[0]; // Obtenir le fichier sélectionné
        const previewContainer2 = document.getElementById("preview-container2");
        const previewImage = document.getElementById("preview-image2");

        if (file) {
            // Vérifiez si le fichier est une image
            const validImageTypes = ["image/jpeg", "image/png"];
            if (!validImageTypes.includes(file.type)) {
                alert("Veuillez sélectionner une image valide (JPEG ou PNG uniquement).");
                event.target.value = ""; // Réinitialiser le champ si type invalide
                previewContainer2.style.display = "none";
                return;
            }

            // Lire et afficher l'image
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result; // Définir l'URL de l'image
                previewContainer2.style.display = "block"; // Afficher la section de prévisualisation
            };
            reader.onerror = function () {
                alert("Impossible de lire le fichier. Veuillez réessayer.");
                previewContainer2.style.display = "none";
            };
            reader.readAsDataURL(file); // Lire le fichier
        } else {
            // Si aucune image n'est sélectionnée, cacher la prévisualisation
            previewContainer2.style.display = "none";
        }
    });


</script>

