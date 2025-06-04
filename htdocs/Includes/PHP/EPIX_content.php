<div class="content">

    <div id="page-title" class="page-title" style="height: 15vh">

        <div id="logo-container" style="display: flex; align-items: center; justify-content: center; height: 100%;"> <!-- Taille du conteneur adaptable -->
            <a id="OSIX" href="index.php" >
                <img src="Includes/Images/Logo Noir Complet Cropped.png" alt="Logo OSIX" class="logo"  >
            </a>
            <a id="EPIX" href="EPIX_gestionnaire.php">
                <img src="Includes\Images\Logo Epix Blanc.png" alt="Logo EPIX" class="logo">
            </a>
            <button id="logo-button" class="edit-button" onclick="editLogo()"
                    title="Modifier le logo du projet" style="border: none; display: flex; align-items: center; justify-content: center;
            <?php if ($_SESSION['this_project']['image'] !== "") {echo 'height:60%"';} else {echo 'height:40%"';} ?>>
                    <img src="<?php if ($_SESSION['this_project']['image'] !== "")
                    {echo $_SESSION['this_project']['image'];}
            else {echo 'Includes/Images/No_Image.jpg';} ?>" id="edit-logo" style="object-fit: contain;">
            </button>
        </div>

        <div class="page-title-text-and-date">
            <div id = "page-title-text" class="page-title-text" style="display: flex; align-items: center; justify-content: center;">
                <div style="display: flex"> <span id="projectTitle"><?php echo $_SESSION['this_project']['name'];?></span>
                    <?php
                    if (isset($_SESSION['user_actif']) && $_SESSION['user_actif'] !== "")
                    {
                        echo '
                    <button class="edit-button" onclick="editTitle()">
                        <img src="Includes\Images\Bouton Modifier.png" title="Modifier le titre">
                    </button>';
                    }
                    ?>
                </div>

            <div style= "display: flex; justify-content: center" id="last_use_time">
                <?php echo 'Dernière sauvegarde : '. $_SESSION['this_project']["last_use"];?>
            </div>
            </div>
        </div>

        <div id="comments">
            <textarea id="comments-box" name="Comments" <?php if ($_SESSION['this_project']["comments"] == "Still Nothing")
            {echo 'placeholder="Laissez un commentaire à ce projet"';}?>><?php if ($_SESSION['this_project']["comments"] !== "Still Nothing")
                {echo $_SESSION['this_project']['comments'];}?></textarea>
            <button class="new-button" onclick="saveComments(<?php echo $_SESSION['this_project']['id']?>)" style="padding: 0.5em">Enregistrer</button>
        </div>


        <div  id="titleDialog" class="dialog-overlay">
            <div class="dialog">
                <h2>Modifier le titre du projet</h2>
                <input autofocus type="text" id="projectTitleInput" value="<?php echo $_SESSION['this_project']['name'];?>"/>
                <button onclick="setTitle(<?php echo $_SESSION['this_project']['id'] ;?>)">Valider</button>
            </div>
        </div>

        <div id="logoDialog" class="dialog-overlay" style="display: none;">
            <div class="dialog">
                <h2>Modifier le logo</h2>
                <div id="upload-container" style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <label for="logoInput" style="margin-bottom: 10px;">Choisir un logo</label>
                    <div style="text-align: center;">
                        <input type="file" id="logoInput" />
                    </div>
                    <img id="previewLogo" alt="Prévisualisation"
                         style="display: none; max-width: 300px; margin-top: 10px;" src="#">
                </div>
                <div style="margin-top: 15px; text-align: center;">
                    <button onclick="closeLogoDialog()" style="background-color: #d9534f; margin-top: 20px; cursor: pointer">Annuler</button>
                    <button onclick="setLogo(<?php echo $_SESSION['this_project']['id']; ?>)" style="margin-top: 20px;">Valider</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">

        <div class="parameters-bar" style="display: flex; height: min-content; justify-content: center; align-items: center; flex-direction: column;">
            <button onclick="showDialog()">
                <img id="save-button" src="Includes/Images/Save.png" title="Enregistrer sous"
                     style="width: 80%; margin-top: 15%" alt="Enregistrer sous" />
            </button>

            <div  id="saveDialog" class="dialog-overlay">
                <div class="dialog">
                    <h2>Enregistrer sous...</h2>
                    <input autofocus type="text" id="newProjectTitleInput" value="<?php echo $_SESSION['this_project']['name'];?>"/>
                    <button onclick="closeSaveDialog()" style="background-color: #d9534f; margin-top: 20px; cursor: pointer">Annuler</button>
                    <button onclick="newVersionProject(<?php echo $_SESSION['this_project']['id'] ;?>)">Valider</button>
                </div>
            </div>

            <button id="export-to-word" class="export-btn">Exporter en Word</button>

            <button id="export-to-excel" class="export-btn">Exporter en Excel</button>

            <button id="export-to-pdf" class="export-btn">Exporter en PDF</button>

            <!-- Nouveau bouton pour masquer ou afficher les items -->
            <button id="hideItems" title="Cliquez pour cacher/révéler des items" style="background-color: grey" onclick="toggleItemsVisibility()" class="export-btn">Masque désactivé</button>

            <button id="rotateButton" class="export-btn" style="background-color: grey">Faire tourner</button>

            <div style="display: flex">
            <button id="tridButton" class="export-btn" style="background-color: grey">3D</button>
            <button id="tridGo" class="export-btn" style="background-color: grey">&#x23F8;</button>
            </div>

            <button id="planarButton" class="export-btn" title="Réarrange les sommets et arrêtes pour plus de lisibilité" onclick="arrangeGraph(100000, 0.05, nodes)">Arranger</button>

            <button id="printCanvas" class="export-btn" style="background-color: darkgoldenrod">Imprimer le graphique</button>
        </div>

        <div class="canvas-container" id="canvas-container" style="height: min-content;">

            <canvas id="myCanvas"></canvas> <!--On dessine là dessus le graphe.-->

            <div id="tooltip"></div> <!--Le plus qui se promène avec le curseur en ajoutant un sommet-->

            <!--La boîte de dialogue qui affiche une image, après un click sur une loupe.-->
            <div id="imageViewContainer" class="dialog-overlay" style="background-color : rgba(255,255,255,0.75);">
                <span id="closeButton">✖</span>
                <img id="imageView" src="" alt="Aperçu de l'image"
                     style="max-width: 90%;
                     max-height: 90%;
                     margin: auto;
                     display: block;"/>
            </div>
        </div>

        <!--Onglets-->
        <div class="controls">

            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; /*width: 100% */" >
                <div class="onglet" id="ajouter" style="padding: 5px; justify-content: center; display: flex">
                    <button onclick="selectTab('ajouter');toggleCategory('nodeContent', nodePlus)">S</button>
                    <span style="margin: 0 7px">Ajouter</span>
                    <button onclick="selectTab('ajouter');toggleCategory('edgeContent', edgePlus)">R</button>
                </div>
                <button class="onglet" id="modifier" onclick="selectTab('modifier')">Modifier</button>
                <button class="onglet" id="gestion" onclick="selectTab('gestion')">Gestion</button>
            </div>

            <div id="interactivePanel" class="interactivePanel">

                <div id="nodePanel">
                    <h2>
                        <span id="nodeTitle">Nouveau Sommet</span>
                        <button id="nodePlus" onclick="toggleCategory('nodeContent', this)"
                                style="width: 20px; height: 20px; padding: 0;"
                        >+</button></h2>

                    <div id="nodeContent" style="display: none">
                    <div style="display: flex; align-items: center">
                        <p id="nodeNameDescription">Nom&nbsp;:&nbsp;</p>
                        <input type="text" style="width: 100%" id="nodeName">
                    </div>
                    <br>
                        <textarea id="nodeComment" placeholder="Commentaire sur le sommet (facultatif)"
                                  style="font-size: small; height: auto" rows="1"></textarea>
                    <!-- On fait d'abord un système de catégories sans ajout. -->
                    <select id="nodeCategory" class="category-select"></select>

                    <label for="file" style="width: 20vw" id="nodeIconDescription">Importer une icône : </label>
                    <input type="file" id="nodeIconInput">
                    <!-- Bouton pour supprimer l'icône -->
                    <button id="removeIconButton" style="display: none; margin-left: 10px;">Supprimer l'icône</button>


                    <div id="iconPreviewContainer" style="border: 1px solid #ccc; padding: 10px; display: flex; align-items: center; justify-content: space-around; margin: 10px 0">

                        <div id="actualIconPreviewContainer" style="display: none; flex-direction: column; align-items: center;">
                            <span style="font-weight: bold"> Icône actuelle : </span>
                            <img id="actualIconPreview" alt="Image actuelle"
                                 style="max-width: 150px; max-height: 150px">
                        </div>


                        <div id="newIconPreviewContainer" style="display: none; flex-direction: column; align-items: center;">
                            <span style="font-weight: bold">Nouvelle icône : </span>
                            <img id="newIconPreview" alt="Image actuelle"
                                 style="max-width: 150px; max-height: 150px">
                        </div>
                    </div>

                    <label for="file" style="width: 20vw" id="nodeImageDescription">Importer une image : </label>
                    <input type="file" id="nodeImageInput">
                    <!-- Bouton pour supprimer l'icône -->
                    <button id="removeImageButton" style="display: none; margin-left: 10px;">Supprimer l'image</button>

                    <div id="imagePreviewContainer" style="border: 1px solid #ccc; padding: 10px; display: flex; align-items: center; justify-content: space-around;">

                        <div id="actualImagePreviewContainer" style="display: none; flex-direction: column; align-items: center;">
                            <span style="font-weight: bold"> Image attachée : </span>
                            <img id="actualImagePreview" alt="Image actuelle"
                                 style="max-width: 150px; max-height: 150px">
                        </div>


                        <div id="newImagePreviewContainer" style="display: none; flex-direction: column; align-items: center;">
                            <span style="font-weight: bold">Nouvelle image : </span>
                            <img id="newImagePreview" alt="Image actuelle"
                                 style="max-width: 150px; max-height: 150px">
                        </div>
                    </div>
                    <br>
                    <div style="display: flex; align-items: center">
                        <p id="nodeLinkDescription">Affecter un lien&nbsp;:&nbsp;</p>
                        <input type="text" id="nodeLink" placeholder="Pas de lien..." style="width: 70%">
                    </div>
                        <div id="nodeDetectedSource" style="border: 2px solid green; border-radius: 5px; padding: 10px; margin: 5px; display: flex">
                            <select id="nodeSource-select" title="A des fins statistiques, vous pouvez sélectionner une source."></select>
                        </div>

                    <div style="display: flex; align-items: center">
                        <p id="nodeGeoCoordDescription">Coordonnées&nbsp;:&nbsp;</p>
                        <input type="text" title="Insérer des coordonnées géographiques" style="width: 100%" id="nodeGeoCoord">
                    </div>

                    <button id="updateNodeButton">Ajouter Sommet</button>

                    <div id="cursor-icon" style="display: none;">
                        <img src="Includes/Images/Icone plus.png" alt="Icone" style="width: 32px; height: 32px;" />
                    </div>
                    <hr>
                </div>
                </div>

                <div class="form-group" id="edgePanel">
                    <h2>
                        <span id="edgeTitle">Nouvelle Relation</span>
                        <button id="edgePlus" onclick="toggleCategory('edgeContent', this)"
                                style="width: 20px; height: 20px; padding: 0;"
                        >+</button></h2>
                    <div id="edgeContent" style="display: none">
                    <div style="width: 100%; display: flex; flex-wrap: wrap; justify-content: space-between">
                        <div style="flex: 1; width: 100%; display: flex; flex-wrap: wrap; justify-content: center; align-items: baseline">
                            <p style="margin-bottom: 0" id="sourceNodeDescription">Sommet 1 :</p>
                            <select id="sourceNode"></select>
                        </div>
                        <div style="flex: 1; width: 100%; display: flex; flex-wrap: wrap; justify-content: center; align-items: baseline">
                            <p style="margin-bottom: 0" id="targetNodeDescription">Sommet 2 :</p>
                            <select id="targetNode"></select>
                        </div>
                    </div>

                        <textarea id="edgeComment" placeholder="Commentaire sur la relation (facultatif)"
                                  style="font-size: small; height: auto;" rows="1"></textarea>

                        <select id="edgeCategory" class="category-select"></select>
                    <div id="edgeStyle" style="display: none">
                        <div id="arrowPreview" style="display: flex; flex-wrap: wrap; justify-content: space-around; border: 1px solid black; padding: 5px; border-radius: 5px; margin: 10px 0; align-items: center; flex: 1">
                            <p id="styleEdgeStatus">Style d'arête par défaut&nbsp;:&nbsp;</p>
                            <canvas id="arrowCanvas" style="display: block"></canvas>
                            <button id= "modifyEdgeStyle" class="modify-button" onclick="modifyEdgeStyle()">Modifier</button>
                        </div>

                        <div id="choosingStyle" style="display: none; border: 1px solid black; padding: 5px; border-radius: 5px; margin-bottom: 10px">
                            <!-- Choix du style de l'arête -->
                            <button id="copyStyleButton" style="justify-content: center; height: 3em;" onclick="copyStyle()">Copier un style</button>
                            <div id="choosingEdgeType" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between">
                                <label for="edgeStyleType">Type de trait :</label>
                                <select id="edgeStyleType" style="width: 50%">
                                </select>
                            </div>

                            <!-- Couleur des segments -->
                            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between">
                                <label for="segmentColor">Couleur des pointillés :</label>
                                <input type="color" id="edgeStyleSegmentColor" value="#000000">
                            </div>
                            <!-- Couleur des flèches -->
                            <div  style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between">
                                <label for="arrowColor">Couleur des flèches :</label>
                                <input type="color" id="edgeStyleArrowColor" value="#000000">
                            </div>
                            <!-- Épaisseur de l'arête -->
                            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between">
                                <label for="thickness">Épaisseur de la ligne :</label>
                                <input type="number" id="edgeStyleThickness" value="2" min="1" max="10">
                            </div>

                        </div>
                    </div>
                    <div style="display: flex; align-items: center">
                        <p id="edgeLinkDescription">Affecter un lien&nbsp;:&nbsp;</p>
                        <input type="text" id="edgeLink" placeholder="Pas de lien..." style="width: 70%">
                    </div>

                    <div id="edgeDetectedSource" style="border: 2px solid green; border-radius: 5px; padding: 10px; margin: 5px; display: flex">
                        <select id="edgeSource-select" title="A des fins statistiques, vous pouvez sélectionner une source."></select>
                    </div>

                    <button id="updateEdgeButton" onclick="addEdge(<?php echo $_SESSION['this_project']['id'];?>)">Ajouter Relation</button>
                    <hr>
                </div>
                </div>
                <button id="deleteItem" style="display: none" onclick="deleteItem()">Supprimer l'objet</button>

            <div id="managementPanel" style="display: flex; flex-direction: column;">
                <h2>Onglet de Gestion</h2>

                <!-- Section des Catégories -->
                <section>
                    <div>
                        <h3 title="Cochez les catégories d'entité que vous utilisez pour ce projet.">
                            Catégories des sommets
                            <button onclick="toggleCategory('nodeCategoriesManagement', this)"
                                    style="width: 20px; height: 20px; padding: 0;"
                            >+</button>
                        </h3>
                        <ul id="nodeCategoriesManagement" style="display: none; width: 50%; justify-content: center"></ul>
                    </div>

                    <div>
                        <h3 title="Cochez les catégories de relation que vous utilisez pour ce projet.">
                            Catégories des relations
                            <button onclick="toggleCategory('edgeCategoriesManagement', this)"
                                    style="width: 20px; height: 20px; padding: 0;"
                            >+</button>
                        </h3>
                        <ul id="edgeCategoriesManagement" style="display: none; width: 50%"></ul>
                    </div>
                </section>

                <!-- Section des options d'affichage -->
                <h3>Options d'affichage
                    <button onclick="toggleCategory('affichageSection', this)"
                            style="width: 20px; height: 20px; padding: 0;">+</button>
                </h3>
                <div id="affichageSection" style="display: none;">
                <section>
                    <label for="commentsVisibility">Affichage des commentaires :</label>
                    <select id="commentsVisibility">
                        <option value="always">Toujours</option>
                        <option value="ifNotEmpty" selected>Seulement si non vide</option>
                        <option value="never">Jamais</option>
                    </select>
                </section>

                <!-- Section désactivation drawImage -->
                <section>
                    <label for="disableLink">
                        <input type="checkbox" id="disableLinkCheckbox" checked>
                        Affichage des liens hypertextes
                    </label>
                    <label for="disableImage">
                        <input type="checkbox" id="disableImageCheckbox" checked>
                        Affichage en grand des images
                    </label>
                </section>
                </div>

                <label for="deletedItemSelect">Restaurer un élément : <button onclick="toggleCategory('restorePanel', this)"
                    style="width: 20px; height: 20px; padding: 0;">+</button></label>
                <div id="restorePanel" style="display: none">
                    <select id="deletedItemSelect">
                        <!-- Les options seront insérées dynamiquement -->
                    </select>
                    <button id="restoreItemButton" style="margin-bottom: 5px">Restaurer l'élément</button>
                </div>

                <button id="applyChangesButton">Appliquer les modifications</button>

            </div>
                <button id="resetTab" style="margin-top: 0.5em">Annuler les modifications</button>


            </div>

</div>


<!--Attention l'ordre des scripts compte-->

    <!--INTERACTION AVEC LA BDD-->
<script>
        function addNode(x, y) {
            data = new FormData();

            var icon = nodeIconInput.files[0];
            if (icon) {data.append("icon", icon);}

            var image = nodeImageInput.files[0];
            if (image) {data.append("nodeImage", image);}

            data.append("project_id", <?php echo $_SESSION["project_id"];?>);
            data.append('action', 'addNode');
            data.append("nodeName", nodeName.value.trim());
            data.append("nodeCategory", nodeCategory.value);
            data.append("nodeComment", nodeComment.value.trim());
            data.append("position", '{"x": ' + Math.round(x) + ', "y": ' + Math.round(y) + "}");
            data.append("nodeLink", nodeLink.value);
            data.append("nodeDetectedSource", (nodeLink.value !== '') ? document.getElementById('nodeSource-select').value : 0);

            data.append("nodeGeoCoord", nodeGeoCoord.value);

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (isRequestSuccessful(this))  {
                    new_option = document.createElement('option');
                    new_option.id = "sourceNodeOption_" + this.responseText;
                    new_option.value = this.responseText;
                    new_option.textContent = nodeName.value.trim();

                    new_option2 = document.createElement('option');
                    new_option2.id = "targetNodeOption_" + this.responseText;
                    new_option2.value = this.responseText;
                    new_option2.textContent = nodeName.value.trim();
                    document.getElementById('sourceNode').appendChild(new_option);
                    document.getElementById('targetNode').appendChild(new_option2);


                    load_graph_json();
                    selectedItem = nodes[this.responseText];
                    selectTab("modifier");
                }
            }

            xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
            xhr.send(data);
        }

        function restoreNode(node) {
            data = new FormData();
            const oldId = node.id;
            data.append("action", "restoreNode");
            data.append("project_id", <?php echo $_SESSION["project_id"];?>);
            data.append("nodeName", node.name);
            data.append("nodeCategory", node.category);
            data.append("nodeComment", node.comments);
            data.append("position", '{"x": ' + node.x + ', "y": ' + node.y + "}");
            data.append("nodeLink", (node.source !== undefined) ? node.source : "");
            data.append("nodeIcon", node.icon);
            data.append("nodeImage", node.image.trim());
            data.append("nodeID", node.id);
            data.append("nodeGeoCoord", node.geoCoord);

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (isRequestSuccessful(this))  {
                    const id = this.responseText;
                    new_option = document.createElement('option');
                    new_option.id = `sourceNodeOption_${id}`;
                    new_option.value = id;
                    new_option.textContent = node.name;

                    new_option2 = document.createElement('option');
                    new_option2.id = `targetNodeOption_${id}` + id;
                    new_option2.value = id;
                    new_option.textContent = node.name;
                    document.getElementById('sourceNode').appendChild(new_option);
                    document.getElementById('targetNode').appendChild(new_option2);

                    selectedItem = nodes[id];
                    selectTab("modifier");

                    for (let key in edgeBin)
                    {
                        if (edgeBin[key].sourceNode === node.id) {
                            edgeBin[key].sourceNode = id;
                        }
                        if (edgeBin[key].targetNode === node.id) {
                            edgeBin[key].targetNode = id;
                        }
                    }
                    alert(`Sommet restaurée : ${node.name} avec id = ${id}`);
                    delete nodeBin[oldId];
                    load_graph_json();
                }
            }

            xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
            xhr.send(data);
        }


        function restoreEdge(edge) {
            data = new FormData();
            const oldId = edge.id;
            data.append("action", "restoreEdge");
            data.append("project_id", <?php echo $_SESSION["project_id"];?>);
            data.append("edgeCategory", edge.category);
            data.append("edgeComment", edge.comments);
            data.append("edgeSource", edge.source);
            data.append("targetNode", edge.targetNode);
            data.append("sourceNode", edge.sourceNode);
            data.append("edgeStyle", '{"segmentColor": "' + edge.segmentColor +
                '", "arrowColor": "' + edge.arrowColor +'", "thickness":'  + edge.thickness + ', "type":"'+ edge.type + '"}');

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (isRequestSuccessful(this))  {
                    alert(`Relation restaurée : ${edge.sourceNodeName} ${edgeCategories[edge.category].name} ${edge.targetNodeName}`);
                    delete edgeBin[oldId];
                    load_graph_json();
                    selectedItem = edges[this.responseText];
                    selectTab("modifier")

                }
            }

            xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
            xhr.send(data);
        }


        function addEdge() {
            if (sourceNode.value == "" || targetNode.value == ""|| sourceNode.value == targetNode.value || edgeCategory.value == "") {
                alert('Veuillez sélectionner une catégorie et deux sommets distincts.');
                return;        }

            data = new FormData();
            data.append('action', 'addEdge');
            data.append("project_id", <?php echo $_SESSION["project_id"];?>);
            data.append("sourceNode", sourceNode.value);
            data.append("targetNode", targetNode.value);
            data.append("edgeComment", edgeComment.value);
            data.append("edgeCategory", edgeCategory.value);
            data.append("edgeStyle", '{"segmentColor": "' + storedEdgeStyleSegmentColor +
                '", "arrowColor": "' + storedEdgeStyleArrowColor +'", "thickness":'  + storedEdgeStyleThickness + ', "type":"'+ storedEdgeStyleType+ '"}');
            data.append("edgeLink", edgeLink.value);
            data.append("edgeDetectedSource", (edgeLink.value !== '') ? document.getElementById('edgeSource-select').value : 0);

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() { if (isRequestSuccessful(this)) {
                load_graph_json();
                selectedItem = edges[this.responseText];
				if (searchingStyle) {
					copyStyle();
				}
            }
			}
            xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
            xhr.send(data);}

</script>

    <!--DECLARATION DES VARIABLES-->
<script>
    let projectTitle = document.getElementById("projectTitle");
    let projectComments = document.getElementById("comments-box");


    let nodes = {};
    let edges = {};

    let tridNodes = {};

    // "id" => 0 si invisible, 1 si invisible car dépendant d'un node invisible.
    let hiddenNodes = {};
    let hiddenEdges = {};
    let searchingHiddenItem = false;

    //Le noeud ou l'arête courante.
    let selectedItem = null;

    //Charger les catégories de noeuds
    let nodeCategories = {};
    let nodeCategoriesStatus = {};
    //Charger les catégories de relation
    let edgeCategories = {};
    let edgeCategoriesStatus = {};
    //Charger les classes de catégories
    let nodeClasses = {};
    let edgeClasses = {};

    // Récupération du canvas et de son contexte
    const canvas = document.getElementById("myCanvas");
    const ctx = canvas.getContext("2d");

    // Variables globales représentant les états des options
    let disabledLink = false; // Pour gérer "Affichage des liens hypertextes"
    let disabledImage = false; // Pour gérer "Affichage en grand des images"

    //Récupération de la boîte d'affichage des imagese.
    const imageViewContainer = document.getElementById("imageViewContainer");
    const imageView = document.getElementById("imageView");

    //Récupération des onglets
    const interactivePanel = document.getElementById("interactivePanel");
    const nodePanel = document.getElementById("nodePanel");
    const edgePanel = document.getElementById("edgePanel");
    const managementPanel = document.getElementById("managementPanel");

    const edgeContent = document.getElementById("edgeContent");
    const nodeContent = document.getElementById("nodeContent");
    const edgePlus = document.getElementById("edgePlus");
    const nodePlus = document.getElementById("nodePlus");

    const nodeTitle = document.getElementById("nodeTitle");
    const nodeName = document.getElementById("nodeName");
    const nodeNameDescription = document.getElementById("nodeNameDescription");
    const nodeComment = document.getElementById("nodeComment");
    const nodeCategory = document.getElementById("nodeCategory");
    const nodeLinkDescription = document.getElementById("nodeLinkDescription");
    const nodeLink = document.getElementById("nodeLink");
    const nodeGeoCoordDescription = document.getElementById("nodeGeoCoordDescription");
    const nodeGeoCoord = document.getElementById("nodeGeoCoord");

    const nodeIcon = document.getElementById("nodeIcon");
    const nodeIconDescription = document.getElementById("nodeIconDescription");
    const nodeIconInput = document.getElementById("nodeIconInput");
    const actualIconPreview = document.getElementById("actualIconPreview");
    const actualIconPreviewContainer = document.getElementById("actualIconPreviewContainer");
    const removeIconButton = document.getElementById("removeIconButton");
    const newIconPreview = document.getElementById("newIconPreview");
    const newIconPreviewContainer = document.getElementById("newIconPreviewContainer");

    const nodeImage = document.getElementById("nodeImage");
    const nodeImageDescription = document.getElementById("nodeImageDescription");
    const nodeImageInput = document.getElementById("nodeImageInput");
    const actualImagePreview = document.getElementById("actualImagePreview");
    const actualImagePreviewContainer = document.getElementById("actualImagePreviewContainer");
    const removeImageButton = document.getElementById("removeImageButton");
    const newImagePreview = document.getElementById("newImagePreview");
    const newImagePreviewContainer = document.getElementById("newImagePreviewContainer");



    const edgeTitle = document.getElementById("edgeTitle");
    const edgeCategory = document.getElementById("edgeCategory");
    const edgeStyle = document.getElementById("edgeStyle");
    const edgeStyleType = document.getElementById("edgeStyleType");
    const edgeStyleArrowColor = document.getElementById("edgeStyleArrowColor");
    const edgeStyleSegmentColor = document.getElementById("edgeStyleSegmentColor");
    const edgeStyleThickness = document.getElementById("edgeStyleThickness");
    const sourceNode = document.getElementById('sourceNode');
    const sourceNodeDescription = document.getElementById("sourceNodeDescription");
    const targetNode = document.getElementById('targetNode');
    const targetNodeDescription = document.getElementById("targetNodeDescription");
    const choosingStyle = document.getElementById("choosingStyle");
    const edgeComment = document.getElementById("edgeComment");
    const modifyEdgeStyleButton = document.getElementById("modifyEdgeStyle");
    const edgeLinkDescription = document.getElementById("edgeLinkDescription");
    const edgeLink = document.getElementById("edgeLink");

    //Pour supprimer l'item sélectionné
    const deleteItemButton = document.getElementById("deleteItem");
    //Pour réinitialiser les saisies.
    const resetTabButton = document.getElementById("resetTab");

    //Variables de l'onglet de Gestion
    const nodeCategoriesManagement = document.getElementById("nodeCategoriesManagement");
    const edgeCategoriesManagement = document.getElementById("edgeCategoriesManagement");
    const commentsVisibility = document.getElementById("commentsVisibility");
    const disableLinkCheckbox = document.getElementById("disableLinkCheckbox");
    const disableImageCheckbox = document.getElementById("disableImageCheckbox");
    const disableLink = document.getElementById("disableLink");
    const disableImage = document.getElementById("disableImage");
    const commentsVisibilityDescription = document.getElementById("commentsVisibilityDescription");
    const disableLinkDescription = document.getElementById("disableLinkDescription");
    const disableImageDescription = document.getElementById("disableImageDescription");

    // Canvas pour l'aperçu graphique de la flèche., et son contexte 2D.
    const arrowCanvas = document.getElementById("arrowCanvas");
    const arrowCtx = arrowCanvas.getContext("2d");

    //Le globe internet pour renvoyer sur un lien.
    const globe = new Image();
    globe.src = "Includes/Images/Globe_Internet.png";
    const loupe = new Image();
    loupe.src = "Includes/Images/Loupe.png";

    // La balise "Ajouter Sommet" et la balise contenant l'icône (+) qui se déplace
    //lorsqu'on veut ajouter un sommet.
    const updateNodeButton = document.getElementById("updateNodeButton");
    const cursorIcon = document.getElementById("cursor-icon");
    const updateEdgeButton = document.getElementById("updateEdgeButton");
    const tooltip = document.getElementById("tooltip");

    // La barre de paramètres // parameter bar
    const hideItems = document.getElementById("hideItems");
    const rotateButton = document.getElementById("rotateButton");
    const tridButton = document.getElementById("tridButton");
    const tridGo = document.getElementById("tridGo");
    const printButton = document.getElementById("printCanvas");

        //Stockage de variables cachées.
    let storedEdgeStyleArrowColor = "#000000";
    let storedEdgeStyleThickness = 2;
    let storedEdgeStyleSegmentColor = "#000000";
    let storedEdgeStyleType = "continuous";


    //Gestion des sélections
    let nexNode = null;
    let searchingSourceNode = false;
    let searchingTargetNode = false;
    let searchingStyle = false;

    // Variables pour le zoom et le déplacement
    let scale = 1;
    let offsetX = 0;
    let offsetY = 0;
    let rotationAngle = 0;
    let theta = 0; // Angle de rotation autour de Y
    let phi = 0; // Angle de rotation autour de Z

    // État pour le déplacement des nœuds
    let isAddingNode = false; // Indique si le mode "Ajouter Sommet" est actif
    let isDragging = false;
    let draggedNode = null;
    let isPanning = false;
    let isRotating = false;
    let isTrid = false;
    let isDynamic = false;  // Variable globale pour activer/désactiver l'animation
    let animationOffset = 0; // Offset pour animer les pointillés et les flèches    let startX = 0;
    let startY = 0;

</script>

    <!--FONCTION DE DESSIN-->
<script>
function drawArrowEdge(contexte, startX, startY, endX, endY, segmentLength,
               arrowSize, arrowColor, segmentColor, thickness,
               addShadow = false, isHidden = false, curve = 0, type = 'dashed')
{
// Vérification des coordonnées
if (!Number.isFinite(startX) || !Number.isFinite(startY) ||
!Number.isFinite(endX) || !Number.isFinite(endY)) {
console.warn("Coordonnées invalides pour drawArrowEdge:", { startX, startY, endX, endY });
return;
}

if (type === 'continuous') {
segmentLength += arrowSize;
arrowSize = 0;
}

// Gestion des ombres
if (addShadow) {
contexte.shadowColor = 'rgba(0, 0, 0, 1)';
contexte.shadowBlur = 6;
contexte.shadowOffsetX = 3;
contexte.shadowOffsetY = 3;
} else {
contexte.shadowColor = 'transparent';
contexte.shadowBlur = 0;
}

// Gestion du flou si caché
contexte.filter = isHidden ? 'blur(1px)' : 'none';

const dx = endX - startX;
const dy = endY - startY;
const distance = Math.sqrt(dx * dx + dy * dy);
const angle = Math.atan2(dy, dx);

let currentX = startX;
let currentY = startY;
const step = segmentLength + arrowSize;

contexte.lineWidth = thickness;
contexte.strokeStyle = segmentColor;



if (curve === 0) {
// Dessin des segments droits
for (let i = 0; i < distance - 2 * step / 3; i += step) {
    const nextX = currentX + segmentLength * Math.cos(angle);
    const nextY = currentY + segmentLength * Math.sin(angle);

    // Animation des flèches (elles avancent)
    if (isDynamic) {
        const animX0 = currentX - (animationOffset % step) * Math.cos(angle);
        const animY0 = currentY - (animationOffset % step) * Math.sin(angle);
        const animX = nextX - (animationOffset % step) * Math.cos(angle);
        const animY = nextY - (animationOffset % step) * Math.sin(angle);
        contexte.beginPath();
        contexte.moveTo(animX0, animY0);
        contexte.lineTo(animX, animY);
        contexte.stroke();
        if (type === "arrowed" || type === "lightArrowed") {
        drawArrow(contexte, animX + arrowSize * Math.cos(angle), animY + arrowSize * Math.sin(angle), angle, arrowSize, arrowColor, type);
    }
  }
    else {
      contexte.beginPath();
      contexte.moveTo(currentX, currentY);
      contexte.lineTo(nextX, nextY);
      contexte.stroke();
      if (type === "arrowed" || type === "lightArrowed") {
        drawArrow(contexte, nextX + arrowSize * Math.cos(angle), nextY + arrowSize * Math.sin(angle), angle, arrowSize, arrowColor, type);
    }
  }

        currentX = nextX + arrowSize * Math.cos(angle);
        currentY = nextY + arrowSize * Math.sin(angle);

}
} else {
// Dessin des segments courbés
const midX = (startX + endX) / 2;
const midY = (startY + endY) / 2;
const controlX = midX - curve * (startY - endY) / distance;
const controlY = midY + curve * (startX - endX) / distance;

for (let i = 0; i < distance - 2 * step / 3; i += step) {
    const tStart = i / distance;
    const tEnd = (i + segmentLength) / distance;

    const startCurveX = (1 - tStart) ** 2 * startX + 2 * (1 - tStart) * tStart * controlX + tStart ** 2 * endX;
    const startCurveY = (1 - tStart) ** 2 * startY + 2 * (1 - tStart) * tStart * controlY + tStart ** 2 * endY;
    const endCurveX = (1 - tEnd) ** 2 * startX + 2 * (1 - tEnd) * tEnd * controlX + tEnd ** 2 * endX;
    const endCurveY = (1 - tEnd) ** 2 * startY + 2 * (1 - tEnd) * tEnd * controlY + tEnd ** 2 * endY;

    // Animation des flèches (elles avancent)
    if (isDynamic) {
        const animX0 = startCurveX - (animationOffset % step) * Math.cos(angle);
        const animY0 = startCurveY - (animationOffset % step) * Math.sin(angle);
        const animX = endCurveX - (animationOffset % step) * Math.cos(angle);
        const animY = endCurveY - (animationOffset % step) * Math.sin(angle);

            contexte.beginPath();
            contexte.moveTo(animX0, animY0);
            contexte.lineTo(animX, animY);
            contexte.stroke();
        if    (type === "arrowed" || type === "lightArrowed") {
        drawArrow(contexte, animX + arrowSize * Math.cos(angle), animY + arrowSize * Math.sin(angle), angle, arrowSize, arrowColor, type);
    }
  }
    else
    {
      contexte.beginPath();
      contexte.moveTo(startCurveX, startCurveY);
      contexte.lineTo(endCurveX, endCurveY);
      contexte.stroke();
      if (type === "arrowed" || type === "lightArrowed") {
        const segmentAngle = Math.atan2(endCurveY - startCurveY, endCurveX - startCurveX);
        drawArrow(contexte, endCurveX + Math.cos(segmentAngle) * arrowSize, endCurveY + Math.sin(segmentAngle) * arrowSize , segmentAngle, arrowSize, arrowColor, type);
    }
  }

    currentX = endCurveX;
    currentY = endCurveY;

}
}

// Réinitialisation des styles
contexte.filter = "none";
contexte.setLineDash([]);
contexte.shadowColor = "transparent";
}

let frameRate = 30; // 30 FPS
let interval = 500 / frameRate;

function animateEdges() {
    if (isDynamic) {
        animationOffset -= 2;  // Vitesse de l’animation
        if (animationOffset > 20) animationOffset = 0; // Réinitialisation du cycle
        drawGraph();
    }
}


document.addEventListener("keydown", (event) => {
  if (event.ctrlKey && event.key === 'x') {
        isDynamic = !isDynamic;
        if (isDynamic)
        {
          setInterval(() => {
              requestAnimationFrame(animateEdges);
          }, interval);
        }
    }
});

/**
* Dessine une flèche à la fin d'une arête.
*/
function drawArrow(contexte, tipX, tipY, angle, arrowSize, arrowColor, type) {
const leftX = tipX - arrowSize * Math.cos(angle - Math.PI / 6);
const leftY = tipY - arrowSize * Math.sin(angle - Math.PI / 6);
const rightX = tipX - arrowSize * Math.cos(angle + Math.PI / 6);
const rightY = tipY - arrowSize * Math.sin(angle + Math.PI / 6);

contexte.fillStyle = arrowColor;
contexte.beginPath();
contexte.moveTo(leftX, leftY);
contexte.lineTo(tipX, tipY);
contexte.lineTo(rightX, rightY);

if (type === 'lightArrowed') {
const midX = (rightX + 2 * tipX + leftX) / 4;
const midY = (rightY + 2 * tipY + leftY) / 4;
contexte.lineTo(midX, midY);
}

contexte.closePath();
contexte.fill();
}



        // NOTE IMPORTANTE : la fonction ne doit pas être apelée sur un canvas caché car inclus dans un block de display = none
        // En effet, le resize lui donnerait alors une taille nulle... et on ne le verrait pas par la suite

        function initArrowPreview(arrowColor, segmentColor, thickness, t) {
            resizeArrowCanvas("6vw", "6vh");
            // Effacer tout ce qui est sur le canvas pour démarrer proprement
            arrowCtx.clearRect(0, 0, arrowCanvas.width, arrowCanvas.height);
// Coordonner de départ et de fin pour dessiner une flèche au milieu du canvas
            // const startX = 5; // Point de départ (axe X)
            const startY = arrowCanvas.height / 2; // Milieu vertical
            //const endX = arrowCanvas.width - 15; // Proche du bord droit
            const endY = arrowCanvas.height / 2; // Milieu vertical
            const segmentLength = 21;
            const arrowSize = Math.round(5 + 1.5 * thickness);
            const step = segmentLength + arrowSize;

            const n = Math.floor(arrowCanvas.width/step)
            const startX = (arrowCanvas.width - n * step)/2;
            const endX = arrowCanvas.width - startX;

            // Appeler la fonction pour dessiner une flèche
            drawArrowEdge(arrowCtx,startX, startY, endX, endY, 21, arrowSize, arrowColor, segmentColor, thickness, false, false, 0, t);
        }

        // Fonction pour gérer la rotation et redessiner le canvas
        function rotateNodes(a, sendingRequest) {
            let newCoordinates = {};

            const x = (canvas.width /2 - offsetX) / scale;
            const y = (canvas.height/2 - offsetY) / scale;
            // Incrémenter l'angle de rotation

            // Appliquer la rotation pour chaque nœud de l'objet
            for (const key in nodes) {
                if (nodes.hasOwnProperty(key)) {
                    const node = nodes[key];
                    newCoordinates[key] = rotatePoint(node.x, node.y, x, y, a);
                    nodes[key].x = newCoordinates[key].x; // Mettre à jour les coordonnées du nœud
                    nodes[key].y = newCoordinates[key].y;
                }
            }
            if (sendingRequest)
            {
                let data = new FormData();
                data.append("project_id", <?php echo $_SESSION["project_id"];?>);
                data.append("newNodeCoordinates", JSON.stringify(newCoordinates));
                data.append('action', 'rotateNodes');
                sendUpdatedCoordinates(data);
                // Redessiner les éléments avec la nouvelle rotation
            }
            preloadImages(drawGraph);
        }

        // Fonction pour effectuer la rotation d'un nœud
        function rotatePoint(x, y, cx, cy, a) {
            const cos = Math.cos(a);
            const sin = Math.sin(a);

            // Calculer les nouvelles coordonnées selon les formules de rotation
            const newX = cx + (x - cx) * cos - (y - cy) * sin;
            const newY = cy + (x - cx) * sin + (y - cy) * cos;

            return { x: Math.round(newX), y: Math.round(newY) };
        }

        // Fonction pour envoyer les nœuds mis à jour via XHR
        function sendUpdatedCoordinates(data) {
            // Créer une instance de l'objet XMLHttpRequest
            const xhr = new XMLHttpRequest();

            // Configuration de la méthode POST et de l'URL de l'API
            xhr.open("POST", "Includes/PHP/EPIX_update_project.php", true);

            xhr.onerror = function () {
                console.error("Erreur réseau ou problème avec la requête.");
            };

            // Convertir l'objet nodes en une chaîne JSON et l'envoyer
            xhr.send(data);
        }

        function curveEdges() {
            const curveStep = 20; // Pas pour les valeurs différentes de courbure
            const edgeGroups = {}; // Regrouper les arêtes par source et cible

            // Regrouper les arêtes ayant la même sourceNode et targetNode
            for (let id in edges) {
                const edge = edges[id];

                // Identifier un groupe par source et cible (inclure les deux directions si nécessaire)
                const groupKey = `${edge.sourceNode}-${edge.targetNode}`;
                const reverseGroupKey = `${edge.targetNode}-${edge.sourceNode}`;

                // Si la clé existe déjà (ou dans l'ordre inverse, si les arêtes sont bidirectionnelles)
                if (edgeGroups[groupKey]) {
                    edgeGroups[groupKey].push(edge);
                } else if (edgeGroups[reverseGroupKey]) {
                    edgeGroups[reverseGroupKey].push(edge);
                } else {
                    edgeGroups[groupKey] = [edge]; // Créer un nouveau groupe
                }
            }

            // Attribuer des courbures aux arêtes dans chaque groupe
            for (let groupKey in edgeGroups) {
                const edgeGroup = edgeGroups[groupKey];
                const nbEdges = edgeGroup.length;
                const edgeRef = edgeGroup[0];

                if (nbEdges === 1) {
                    // Aucune courbure si une seule arête
                    edgeRef.curve = 0;
                } else {
                    // Répartir les courbures pour les arêtes entre les mêmes nœuds
                    for (let i = 0; i < nbEdges; i++) {
                        // Alterner les courbures (positives et négatives pour différenciation visuelle)
                        edgeGroup[i].curve = curveStep * (i - (nbEdges - 1) / 2) * (edgeGroup[i].sourceNode === edgeRef.sourceNode ? 1 : -1);
                    }
                }
            }
        }

        // Pour une copie qui n'altérera pas les coordonnées réelles des noeuds pendant le traitement 3D.
        function realCoordinates() {
            // Copier les coordonnées x et y de chaque nœud
            for (const [key, node] of Object.entries(nodes)) {
                tridNodes[key] = {
                    x: node.x,
                    y: node.y,
                    z: 2 * (Math.abs(node.x)+Math.abs(node.y)) * (Math.random() - 0.5)
                };
            }
        }

        // Fonction pour calculer la distance entre deux nœuds
        function getDistance(node1, node2) {
            if (!node1.z || !node2.z) {
                const dx = node1.x - node2.x;
                const dy = node1.y - node2.y;
                return Math.sqrt(dx * dx + dy * dy);
            } else {
                const dx = node1.x - node2.x;
                const dy = node1.y - node2.y;
                const dz = node1.z - node2.z;
                return Math.sqrt(dx * dx + dy * dy + dz * dz);
            }
        }

        function arrangeGraph(f, r) {
            // Paramètres pour l'algorithme (vous pouvez ajuster ces valeurs)
            const repulsionForce = f; // Force de répulsion entre nœuds (plus cette valeur est grande, plus ils se repoussent)
            const springLength = 150; // Longueur idéale des arêtes
            const springForce = r; // Force de rappel pour les arêtes (simule un ressort)
            const maxIterations = 1000; // Nombre maximum d'itérations pour repositionner les nœuds

            // Fonction principale de calcul des forces
            for (let iteration = 0; iteration < maxIterations; iteration++) {
                // Étape 1 : Répulsion entre tous les nœuds
                for (const nodeA of Object.values(nodes))
                {
                    for (const nodeB of Object.values(nodes))
                    {
                        if (nodeA !== nodeB) {
                            const dx = nodeA.x - nodeB.x;
                            const dy = nodeA.y - nodeB.y;
                            const distance = Math.max(getDistance(nodeA, nodeB), 0.01); // Éviter les divisions par 0
                            const force = repulsionForce / (distance * distance) * Math.exp(-distance/500); // Force inversement proportionnelle au carré de la distance

                            // Appliquer la force de répulsion
                            nodeA.x += (dx / distance) * force;
                            nodeA.y += (dy / distance) * force;

                        }
                    }
                }
                // Étape 2 : Attraction selon les arêtes (ressorts)
                for (const edge of Object.values(edges)) {
                    const sourceNode = nodes[edge.sourceNode]; // Nœud source
                    const targetNode = nodes[edge.targetNode]; // Nœud cible

                    const dx = targetNode.x - sourceNode.x;
                    const dy = targetNode.y - sourceNode.y;
                    const distance = Math.max(getDistance(sourceNode, targetNode), 0.01); // Éviter les divisions par 0
                    const displacement = springForce * (distance - springLength); // La force est proportionnelle à l'étirement du ressort

                    // Appliquer la force d'attraction sur les nœuds
                    sourceNode.x += (dx / distance) * displacement;
                    sourceNode.y += (dy / distance) * displacement;

                    targetNode.x -= (dx / distance) * displacement;
                    targetNode.y -= (dy / distance) * displacement;
                }
            }
            // Retourner les nœuds réarrangés
            preloadImages(drawGraph);
            // Attendre un court moment avant d'afficher le prompt
            setTimeout(() => {
            // Demander confirmation pour enregistrer les nouvelles positions des nœuds
            const saveChanges = window.confirm("Acceptez-vous la proposition ?");
            if (saveChanges)
            {
                newCoordinates = {};
                for (const [key, node] of Object.entries(nodes))
                {
                    newCoordinates[key] =
                        {
                        x: node.x,
                        y: node.y
                        };
                }
                let data = new FormData();
                data.append("project_id", <?php echo $_SESSION["project_id"];?>);
                data.append("newNodeCoordinates", JSON.stringify(newCoordinates));
                data.append('action', 'rotateNodes');
                sendUpdatedCoordinates(data);
                // Redessiner les éléments avec la nouvelle rotation
            }
            else
            {load_graph_json()}

            }, 100);
            }


        function arrangeTridGraph(f, r) {
            realCoordinates();

            // Paramètres pour l'algorithme (vous pouvez ajuster ces valeurs)
            const repulsionForce = f; // Force de répulsion entre nœuds
            const springLength = 150; // Longueur idéale des arêtes
            const springForce = r; // Force de rappel des arêtes (simule un ressort)
            const maxIterations = 1000; // Nombre maximum d'itérations pour repositionner les nœuds

            // Calculer les connexions par nœud pour le point 2
            const nodeAdjacencyCount = {}; // Compte les arêtes par nœud
            for (const edge of Object.values(edges)) {
                if (!nodeAdjacencyCount[edge.sourceNode]) {
                    nodeAdjacencyCount[edge.sourceNode] = 0;
                }
                if (!nodeAdjacencyCount[edge.targetNode]) {
                    nodeAdjacencyCount[edge.targetNode] = 0;
                }
                nodeAdjacencyCount[edge.sourceNode]++;
                nodeAdjacencyCount[edge.targetNode]++;
            }

            // Fonction principale de calcul des forces
            for (let iteration = 0; iteration < maxIterations; iteration++) {
                // Étape 1 : Répulsion entre tous les nœuds
                for (const nodeA of Object.values(tridNodes)) {
                    for (const nodeB of Object.values(tridNodes)) {
                        if (nodeA !== nodeB) {
                            const dx = nodeA.x - nodeB.x;
                            const dy = nodeA.y - nodeB.y;
                            const dz = nodeA.z - nodeB.z;
                            const distance = Math.max(getDistance(nodeA, nodeB), 0.01); // Éviter les divisions par 0
                            const force = repulsionForce / (distance * distance); // Force inversement proportionnelle au carré de la distance

                            // Appliquer la force de répulsion
                            nodeA.x += (dx / distance) * force;
                            nodeA.y += (dy / distance) * force;
                            nodeA.z += (dz / distance) * force;
                        }
                    }
                }

                // Étape 2 : Attraction selon les arêtes (ressorts)
                for (const edge of Object.values(edges)) {
                    const sourceNode = tridNodes[edge.sourceNode]; // Nœud source
                    const targetNode = tridNodes[edge.targetNode]; // Nœud cible

                    const dx = targetNode.x - sourceNode.x;
                    const dy = targetNode.y - sourceNode.y;
                    const dz = targetNode.z - sourceNode.z;
                    const distance = Math.max(getDistance(sourceNode, targetNode), 0.01); // Éviter les divisions par 0

                    // Calcul de la force de rappel (ressort) ajustée pour les nœuds peu connectés
                    const adjustedSpringForce = (nodeAdjacencyCount[edge.sourceNode] === 1 || nodeAdjacencyCount[edge.targetNode] === 1)
                        ? springForce * 2.0 // Si un des nœuds a une seule connexion, augmentons la force
                        : springForce;

                    const displacement = adjustedSpringForce * (distance - springLength); // La force est proportionnelle à l'écart avec la longueur idéale

                    // Appliquer la force d'attraction sur les nœuds
                    sourceNode.x += (dx / distance) * displacement;
                    sourceNode.y += (dy / distance) * displacement;
                    sourceNode.z += (dz / distance) * displacement;

                    targetNode.x -= (dx / distance) * displacement;
                    targetNode.y -= (dy / distance) * displacement;
                    targetNode.z -= (dz / distance) * displacement;
                }
            }

            // Retourner les nœuds réarrangés
            preloadImages(drawGraph);
        }

        function tridGraph(theta, phi, sendingRequest = false, perspectiveDistance= 500) {
            let newCoordinates = {};

            // Vérification : éviter une division par zéro
            if (Object.keys(tridNodes).length === 0) return;

            // Calcul du centre du graphe
            let sumX = 0, sumY = 0, sumZ = 0;
            for (let key in tridNodes) {
                const n = tridNodes[key];
                sumX += n.x;
                sumY += n.y;
                sumZ += n.z ?? 0;
            }

            const centerX = sumX / Object.keys(tridNodes).length;
            const centerY = sumY / Object.keys(tridNodes).length;
            const centerZ = sumZ / Object.keys(tridNodes).length;

            // Pré-calcul des valeurs trigonométriques
            const cosTheta = Math.cos(theta * Math.PI / 180);
            const sinTheta = Math.sin(theta * Math.PI / 180);
            const cosPhi = Math.cos(phi * Math.PI / 180);
            const sinPhi = Math.sin(phi * Math.PI / 180);

            // Transformation et projection de chaque nœud
            for (let key in tridNodes) {
                const n = tridNodes[key];
                const x = n.x - centerX;
                const y = n.y - centerY;
                const z = (n.z ?? 0) - centerZ;

                // Rotation autour de Y (Theta)
                let rotatedX = x * cosTheta + z * sinTheta;
                let rotatedZ = -x * sinTheta + z * cosTheta;
                let rotatedY = y; // Y ne change pas avec la rotation autour de Y

                // Rotation autour de Z (Phi)
                nodes[key].finalX = rotatedX * cosPhi - rotatedY * sinPhi;
                nodes[key].finalY = rotatedX * sinPhi + rotatedY * cosPhi;
                nodes[key].finalZ = rotatedZ; // Z reste inchangé lors de la rotation autour de Z

                //Projection en 2D avec perspective
                const depthFactor = 1 / (1 + nodes[key].finalZ / perspectiveDistance);
                nodes[key].x = nodes[key].finalX * depthFactor;
                nodes[key].y = nodes[key].finalY * depthFactor;


                newCoordinates[key] = { x: nodes[key].x, y: nodes[key].y };
            }

            // Redessiner le graphe après transformation
            preloadImages(drawGraph);
        }


        function rotateGraph(duration = 5000) {
            arrangeTridGraph(1000, 0.05);
            const rotationSteps = 120; // Nombre de pas
            const interval = duration / rotationSteps; // Temps entre chaque pas (ms)
            let currentStep = 0; // Compteur de rotation actuelle
            const maxTheta = 360; // Rotation complète autour de Y

            // Définir une perspectiveDistance fixe basée sur l'étendue des profondeurs
            const minZ = Math.min(...Object.values(nodes).map(n => n.z));
            const maxZ = Math.max(...Object.values(nodes).map(n => n.z));
            const minY = Math.min(...Object.values(nodes).map(n => n.y));
            const maxY = Math.max(...Object.values(nodes).map(n => n.y));
            const minX = Math.min(...Object.values(nodes).map(n => n.x));
            const maxX = Math.max(...Object.values(nodes).map(n => n.x));
            const perspectiveDistance = Math.max((maxZ - minZ), (maxY- minY), (maxX-minX)) * 1.5 || 500; // Valeur par défaut si max-min = 0

            for (const key in nodes) {
                const node = nodes[key];

                // Projection en 2D avec perspective
                const depthFactor = 1 / (1 + node.finalZ / perspectiveDistance);

                node.screenX = canvas.width / 2 + node.finalX * depthFactor;
                node.screenY = canvas.height / 2 + node.finalY * depthFactor;
                node.size = Math.max(2, 5 * depthFactor); // Ajustement de la taille selon la profondeur
            }


            function stepRotation() {
                if (currentStep > rotationSteps) {
                    // Fin de la rotation
                    load_graph_json()
                    isTrid = false;
                    tridGo.innerHTML = "&#x23EF";
                    return;
                }

                // Calcul des angles de rotation pour cette étape
                const theta = (currentStep / rotationSteps) * maxTheta; // Rotation sur Y

                // Effectuer la rotation
                tridGraph(theta, 0, 500, perspectiveDistance);
                if (tridGo.style.backgroundColor !== "grey") {
                    currentStep++;
                }
                setTimeout(stepRotation, interval); // Prochaine étape après l'intervalle
            }

            // Démarrer la rotation
            stepRotation();
        }

        function getDrawingOrder(nodes) {
    // Créer un tableau avec les IDs des nœuds et leur profondeur (Z final)
    let nodeOrder = Object.keys(nodes).map(key => ({
        key: key,
        depth: nodes[key].finalZ // Utiliser finalZ pour trier par profondeur
    }));

    // Trier du plus éloigné (grand Z) au plus proche (petit Z)
    nodeOrder.sort((a, b) => b.depth - a.depth);

    // Retourne la liste ordonnée des clés des nœuds
    return nodeOrder.map(node => node.key);
}


</script>

    <!--FONCTIONS D'AFFICHAGE-->
<script>
    // Redimensionner le canvas selon le conteneur
    function resizeCanvas() {
        //Pourcentage occupé du conteneur parent
        canvas.style.width = "100%";
        canvas.style.height = "100%";

        //Dimensions réelles occupées dans le DOM. (en pixels)
        //Permet au canvas de correspondre à la taille du conteneur en pixel.
        const width = canvas.clientWidth;  // Largeur du conteneur
        const height = canvas.clientHeight; // Hauteur du conteneur

        //Redéfini la taille css, en pixels cette fois.
        canvas.style.width = width + "px";
        canvas.style.height = height + "px";

        // Dimensions internes du canvas : nombre de "cases" en largeur et en hauteur
        // Permet d'avoir un canvas sans flou ou déformation
        canvas.width = width;
        canvas.height = height;
    }

    function resizeArrowCanvas(w, h) {
        arrowCanvas.style.width = w;
        arrowCanvas.style.height = h;

        const width = arrowCanvas.clientWidth;  // Largeur du conteneur
        const height = arrowCanvas.clientHeight; // Hauteur du conteneur

        arrowCanvas.style.width = width + "px";
        arrowCanvas.style.height = height + "px";

        // Taille physique du canvas (prise en compte du ratio de pixel)
        arrowCanvas.width = width;
        arrowCanvas.height = height;
    }


    //Charger le graphe depuis le serveur

    //on va calculer une fois seulement les items à afficher ou pas, avec les listes edgeHidden et nodeHidden
    function load_graph_json() {
        data = new FormData();
        data.append("action", "load_graph");
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    // Convertir la réponse texte en JSON
                    const response = JSON.parse(this.responseText);
                    nodes = response.nodes; // Charger les données des nœuds depuis la réponse JSON
                    edges = response.edges; // Charger les données des relations (arêtes) depuis la réponse JSON
                    curveEdges();
                    preloadImages(drawGraph);
                    refreshDeletedItems();
                } catch (error) {
                    console.error("Erreur lors de l'analyse du JSON :", error);
                    console.log(this.responseText);
                }
            } else if (xhr.readyState === 4) {
                console.error("Erreur AJAX : ", xhr.status, xhr.statusText);
            }
        };


        xhr.open("POST", "Includes/PHP/load_json.php", false); // Remplacez ceci par l'URL correcte
        xhr.send(data);
    }

    //Charger les images nécessaires (les images ont un emplacement différent de la bdd).
    function preloadImages(callback) {
        let imagesToLoad = 0;

        for (let nodeId in nodes) {
            if (nodes[nodeId].icon && nodes[nodeId].icon !== '' && !nodes[nodeId].imgIcon) {
                imagesToLoad++;
                let img1 = new Image();
                img1.src = nodes[nodeId].icon;
                img1.onload = () => {
                    // Enregistrer l'image redimensionnée dans nodes[id]
                    nodes[nodeId].imgIcon = img1;
                    imagesToLoad--;
                    if (imagesToLoad === 0) {
                        callback(); // Quand toutes les images sont chargées, on dessine

                    }
                };

                img1.onerror = () => {
                    console.error(`Erreur : Impossible de charger l'icône pour ${nodes[nodeId].name}`);
                    nodes[nodeId].imgIcon = null;
                    imagesToLoad--;
                    if (imagesToLoad === 0) {
                        callback(); // Passe à l'étape suivante même si certaines images échouent

                    }
                };
            }

            if (nodes[nodeId].image && nodes[nodeId].image !== '' && !nodes[nodeId].imgImage) {
                imagesToLoad++;
                let img2 = new Image();
                img2.src = nodes[nodeId].image;

                img2.onload = () => {
                    // Enregistrer l'image redimensionnée dans nodes[id]
                    nodes[nodeId].imgImage = img2;
                    imagesToLoad--;
                    if (imagesToLoad === 0)
                    {
                        callback();

                    }

                };

                img2.onerror = () => {
                    console.error(`Erreur : Impossible de charger l'image pour ${nodes[nodeId].name}`);
                    nodes[nodeId].imgImage = null;
                    imagesToLoad--;
                    if (imagesToLoad === 0)
                    {
                        callback();
                    }
                };
            }
        }

        if (imagesToLoad === 0) {
            callback(); // Si aucune image à charger, dessine directement
        }
    }

    function modifyHiddenItems()
    {

        // Parcourir chaque ID dans hiddenNodes et définir leur propriété "hidden" à true
        for (let id in nodes)
        {
            if (hiddenNodes.hasOwnProperty(id))
            {
                nodes[id].hidden = hiddenNodes[id];
            }
        }
        for (let id in edges)
        {
            if (hiddenEdges.hasOwnProperty(id))
            {
                edges[id].hidden = hiddenEdges[id];
            }
            if ((hiddenNodes.hasOwnProperty(edges[id].sourceNode) && hiddenNodes[edges[id].sourceNode] === true)
                || (hiddenNodes.hasOwnProperty(edges[id].targetNode) && hiddenNodes[edges[id].targetNode] === true))
            {
                edges[id].hidden = true;
            }
            else if (!hiddenEdges.hasOwnProperty(id)
                    || (hiddenEdges.hasOwnProperty(id) && hiddenEdges[id] === false))
            {
                edges[id].hidden = false;
            }
        }
    }

    // Fonction pour dessiner le graphe
    function drawGraph() {

        //effacer
        ctx.save();
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        //appliquer les translations et le zoom
        ctx.translate(offsetX, offsetY);
        ctx.scale(scale, scale);

        // Dessiner les arêtes
        ctx.strokeStyle = '#0000ff';
        isRevealingHiddenItems = (hideItems.textContent == "Démasquer Items");

        Object.values(edges).forEach(edge => {
            if (edge.hidden && !isRevealingHiddenItems) return; // Ignore les arêtes cachées si elles ne doivent pas être révélées

            const startNode = nodes[edge.sourceNode]; // Nœud de départ
            const endNode = nodes[edge.targetNode];   // Nœud de destination

            // Vérification que les nœuds existent et ont des coordonnées valides
            if (!startNode || !endNode || isNaN(startNode.x) || isNaN(startNode.y) || isNaN(endNode.x) || isNaN(endNode.y)) {
                console.warn(`Coordonnées invalides pour l'arête entre "$\{startNode?.name || 'inconnu'\}" et "$\{endNode?.name || 'inconnu'\}".`);
                return; // Ignore cette arête
            }

            // Coordonnées des centres des nœuds avec déstructuration
            const { x: startX, y: startY } = startNode;
            const { x: endX, y: endY } = endNode;

            // Paramètres de l'affichage de l'arête
            const arrowSize = 5 + edge.thickness; // Taille de la flèche
            const segmentLength = 20; // Longueur d'un segment
            const isSelected = selectedItem === edge; // Vérification si l’arête est sélectionnée
            const isFaded = edge.hidden && isRevealingHiddenItems; // Gestion du flou sur arête cachée révélée

            // Dessin de l’arête avec une flèche
            drawArrowEdge(ctx, startX, startY, endX, endY,
                segmentLength, arrowSize, edge.arrowColor, edge.segmentColor,
                edge.thickness, isSelected, isFaded, edge.curve, edge.type);
        });


        ctx.shadowColor = 'transparent';

        // Position de la caméra
        const cameraX = offsetX + canvas.width / 2;
        const cameraY = offsetY + canvas.height / 2;
        const cameraZ = 500;  // Distance de la caméra
        // Obtenir l'ordre de dessin des nœuds (du plus éloigné au plus proche)
        const drawingOrder = getDrawingOrder(nodes);

        // Parcourir les nœuds dans l'ordre correct
for (let id of drawingOrder) {
    let node = nodes[id];

    if (!node.hidden || isRevealingHiddenItems) {
        let isSelected = (node === selectedItem);
        let isIconAvailable = node.icon && node.imgIcon;
        // Si la catégorie n'existe plus, on lui attribue automatiquement la catégorie autres...
        let category = (nodeCategories[node.category] !== undefined) ? nodeCategories[node.category] : parseInt(1);

        // Initialisation du facteur d’échelle
        let scaleFactor = 1;
        if (isTrid && node.finalX && node.finalY && node.finalZ) {
            const dx = node.finalX - cameraX;
            const dy = node.finalY - cameraY;
            const dz = node.finalZ + cameraZ;
            const distance = Math.sqrt(dx * dx + dy * dy + dz * dz);

            scaleFactor = 500 / (distance + 100); // Plage de mise à l'échelle sécurisée
        }

        // Définition des styles
        ctx.lineWidth = isSelected ? 4 * scaleFactor : 1 * scaleFactor;
        ctx.strokeStyle = isSelected ? "#ff0000" : "#000000";
        ctx.imageSmoothingEnabled = true;

        // Définition des tailles
        const fontSize = 14 * scaleFactor;
        const imageSize = 32 * scaleFactor;
        const circleSize = 20 * scaleFactor;
        const textPadding = 8 * scaleFactor;
        const textWidth = ctx.measureText(truncateText(node.name, 150, fontSize)).width;

        // Dimensions du cadre
        const elementSize = isIconAvailable ? imageSize : circleSize;
        const boxWidth = elementSize + textWidth + textPadding * 2;
        const boxHeight = Math.max(elementSize, fontSize) + 2 * scaleFactor;
        const boxX = node.x - boxWidth / 2;
        const boxY = node.y - boxHeight / 2;

        // Stockage des positions
        Object.assign(node, { boxX, boxY, boxWidth, boxHeight });

        // Effet de flou si l'élément est masqué
        ctx.filter = (isRevealingHiddenItems && node.hidden) ? 'blur(1px)' : 'none';

        // Dessin du fond (boîte ou forme spéciale)
        if (category && category.owner !== -1) {
            drawStyledBox(ctx, node, boxX, boxY, boxWidth, boxHeight, scaleFactor, isSelected);
        } else {
            switch (category?.name) {
                case "Symbologie - action à mener":
                    drawDiamond(ctx, node, textWidth, textPadding, scaleFactor);
                    break;
                case "Symbologie - point de départ":
                    drawOval(ctx, node, textWidth, textPadding, scaleFactor);
                    break;
                case "Symbologie - décision":
                    drawHexagone(ctx, node, textWidth, textPadding, scaleFactor);
                    break;
            }
        }

        // Dessin de l’image ou du cercle
        if (category?.owner !== -1) {
            if (isIconAvailable) {
                ctx.save();
                ctx.beginPath();
                ctx.roundRect(boxX + 1, node.y - imageSize / 2, imageSize, imageSize, 8 * scaleFactor);
                ctx.clip();
                ctx.drawImage(node.imgIcon, boxX + 1, node.y - imageSize / 2, imageSize, imageSize);
                ctx.restore();
            } else {
                drawCircle(ctx, boxX, node.y, circleSize);
            }

            // Dessin du texte avec meilleure gestion de la largeur
            ctx.fillStyle = "#000000";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.font = `${fontSize}px Arial`;
            const truncatedText = truncateText(node.name, Math.min(boxWidth - textPadding * 2, 150), fontSize);
            ctx.fillText(truncatedText, node.x + elementSize / 2, node.y);
        }
        // Dessin des icônes supplémentaires
		if (!isTrid) {
        drawExtraIcons(ctx, node, boxX, boxY, boxWidth, boxHeight, scaleFactor);
		}
	}
}
ctx.restore();
    }

    // 📌 Nouvelle fonction pour dessiner le cadre des nœuds standards
    function drawStyledBox(ctx, node, boxX, boxY, boxWidth, boxHeight, scaleFactor, isSelected) {
        ctx.beginPath();
        ctx.roundRect(boxX, boxY, boxWidth, boxHeight, 8 * scaleFactor);
        ctx.fillStyle = isSelected ? "#ffdddd" : "#ffffff";
        ctx.shadowColor = "rgba(0, 0, 0, 0.2)";
        ctx.shadowBlur = isSelected ? 15 * scaleFactor : 5 * scaleFactor;
        ctx.fill();
        ctx.stroke();
    }

    // Fonctions utilitaires
    function drawDiamond(ctx, node, textWidth, textPadding, sfactor) {
        const x = node.x, y = node.y;
        const taille = Math.min(Math.max(textWidth / 2, 20), 45) * 1.2 * sfactor;
        // Ajouter une ombre pour un effet de profondeur
        ctx.shadowColor = "rgba(0, 0, 0, 0.4)";
        ctx.shadowOffsetX = 5;
        ctx.shadowOffsetY = 5;
        ctx.shadowBlur = 10;
        ctx.beginPath();
        ctx.moveTo(x, y - taille);
        ctx.lineTo(x + taille, y);
        ctx.lineTo(x, y + taille);
        ctx.lineTo(x - taille, y);
        ctx.closePath();
        // Appliquer un dégradé de couleur pour une meilleure esthétique
        const gradient = ctx.createLinearGradient(x - taille, y - taille, x + taille, y + taille);
        gradient.addColorStop(0, '#ff1744');
        gradient.addColorStop(1, '#d50000');
        ctx.fillStyle = gradient;
        ctx.fill();
        ctx.stroke();

        const maxWidth = taille * 1.8; // Largeur du losange
        const maxHeight = taille * 1.5; // Hauteur du losange
        const { lines, fontSize } = autoSizeText(node.name, maxWidth, maxHeight);

        ctx.font = `${fontSize}px Arial`;
        ctx.fillStyle = 'black';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        let textY = y + fontSize/4 - ((lines.length - 0.5) * fontSize) / 2;
        lines.forEach(line => {
            ctx.fillText(line, x, textY);
            textY += fontSize;
        });
    }

    function drawOval(ctx, node, textWidth, textPadding, sfactor) {
        const x = node.x, y = node.y;
        const ovaleWidth = Math.min(textWidth * 1.3, 150) * 1.2 * sfactor;
        const ovaleHeight = Math.min(40, 80) * sfactor ;
        // Ajouter une ombre pour un effet de profondeur
        ctx.shadowColor = "rgba(0, 0, 0, 0.4)";
        ctx.shadowOffsetX = 5;
        ctx.shadowOffsetY = 5;
        ctx.shadowBlur = 10;
        ctx.beginPath();
        ctx.ellipse(x, y, ovaleWidth / 2, ovaleHeight / 2, 0, 0, 2 * Math.PI);
        ctx.closePath();
        // Appliquer un dégradé de couleur pour une meilleure esthétique
        const gradient = ctx.createLinearGradient(x - ovaleWidth / 2, y - ovaleHeight / 2, x + ovaleWidth / 2, y + ovaleHeight / 2);
        gradient.addColorStop(0, '#2979ff');
        gradient.addColorStop(1, '#0d47a1');
        ctx.fillStyle = gradient;
        ctx.fill();
        ctx.stroke();

        const maxWidth = ovaleWidth * 0.8; // Largeur du texte max (80% du symbole)
        const maxHeight = ovaleHeight * 0.6; // Hauteur max
        const { lines, fontSize } = autoSizeText(node.name, maxWidth, maxHeight);

        ctx.font = `bold ${fontSize}px Arial`;
        ctx.fillStyle = 'white';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        let textY = y + fontSize/4 - ((lines.length - 0.5) * fontSize) / 2;
        lines.forEach(line => {
            ctx.fillText(line, x, textY);
            textY += fontSize;
        });

    }

    function drawHexagone(ctx, node, textWidth, textPadding, sfactor) {
    const x = node.x, y = node.y;
    const taille = Math.min(Math.max(textWidth / 2, 25), 50) * 1.1 * sfactor ; // Taille adaptative

    // Ajouter une ombre pour un effet de profondeur
    ctx.shadowColor = "rgba(0, 0, 0, 0.4)";
    ctx.shadowOffsetX = 5;
    ctx.shadowOffsetY = 5;
    ctx.shadowBlur = 10;

    // Définition des points de l'hexagone
    ctx.beginPath();
    for (let i = 0; i < 6; i++) {
        const angle = (Math.PI / 3) * i;
        const px = x + taille * Math.cos(angle);
        const py = y + taille * Math.sin(angle);
        ctx.lineTo(px, py);
    }
    ctx.closePath();

    // Appliquer un dégradé de couleur orangé
    const gradient = ctx.createLinearGradient(x - taille, y - taille, x + taille, y + taille);
    gradient.addColorStop(0, '#ff9800'); // Orange clair
    gradient.addColorStop(1, '#d84315'); // Orange foncé
    ctx.fillStyle = gradient;
    ctx.fill();
    ctx.stroke();

    // Ajustement du texte à l'intérieur de l'hexagone
    const maxWidth = taille * 1.8; // Largeur max du texte
    const maxHeight = taille * 1.5; // Hauteur max du texte
    const { lines, fontSize } = autoSizeText(node.name, maxWidth, maxHeight);

    ctx.font = `${fontSize}px Arial`;
    ctx.fillStyle = 'black';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    // Centrage du texte dans l'hexagone
    let textY = y + fontSize / 4 - ((lines.length - 0.5) * fontSize) / 2;
    lines.forEach(line => {
        ctx.fillText(line, x, textY);
        textY += fontSize;
    });
}


    function drawCircle(ctx, x, y, radius) {
        ctx.beginPath();
        ctx.arc(x + radius / 2 + 1, y, radius / 2, 0, Math.PI * 2);
        ctx.fillStyle = "red";
        ctx.fill();
    }

    function drawExtraIcons(ctx, node, boxX, boxY, boxWidth, boxHeight, scale) {
        if (!disabledLink && node.source !== "") {
            if (scale > 0.67) {
                ctx.drawImage(globe, boxX - 8, boxY - 8, 16, 16);
            } else {
                ctx.beginPath();
                ctx.arc(boxX, boxY, 6, 0, Math.PI * 2);
                ctx.fillStyle = "#000000";
                ctx.fill();
            }
        }

        if (!disabledImage && node.image !== "") {
            if (scale > 0.67) {
                ctx.drawImage(loupe, boxX - 8, boxY + boxHeight - 8, 16, 16);
            } else {
                ctx.beginPath();
                ctx.arc(boxX, boxY + boxHeight, 6, 0, Math.PI * 2);
                ctx.fillStyle = "green";
                ctx.fill();
            }
        }
    }


    function wrapText(text, maxWidth) {
        const words = text.split(' ');
        let lines = [];
        let currentLine = '';

        words.forEach(word => {
            let testLine = currentLine ? currentLine + ' ' + word : word;
            let testWidth = ctx.measureText(testLine).width;

            if (testWidth > maxWidth && currentLine !== '') {
                lines.push(currentLine); // On ajoute la ligne et on recommence
                currentLine = word;
            } else {
                currentLine = testLine;
            }
        });

        if (currentLine) {
            lines.push(currentLine); // Ajoute la dernière ligne
        }
        return { lines, maxTextWidth: Math.max(...lines.map(line => ctx.measureText(line).width), 0) };
    }

    function autoSizeText(text, maxWidth, maxHeight, minFontSize = 10, maxFontSize = 20) {
        let fontSize = maxFontSize;
        let lines;

        do {
            ctx.font = `${fontSize}px Arial`;
            lines = wrapText(text, maxWidth).lines; // Découpe le texte en lignes
            fontSize -= 2; // Réduit la taille si ça ne rentre pas
        } while (lines.length * fontSize > maxHeight && fontSize > minFontSize);

        return { lines, fontSize };
    }

    function truncateText(text, maxWidth, fontSize) {
        ctx.font = `${fontSize}px Arial`; // Assurer la bonne police
        let width = ctx.measureText(text).width;

        if (width <= maxWidth) return text; // Si ça rentre, pas de souci

        let truncated = text;
        while (width > maxWidth && truncated.length > 1) {
            truncated = truncated.slice(0, -1); // On enlève un caractère à chaque itération
            width = ctx.measureText(truncated + "…").width;
        }
        return truncated + "…"; // Ajoute "…" à la fin pour montrer qu'il est tronqué
    }



    //Charger le graphe depuis le serveur
    function load_categories_json() {
        data = new FormData();
        data.append("action", "load_categories");
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    // Convertir la réponse texte en JSON
                    const response = JSON.parse(this.responseText);
                    nodeCategories = response.nodeCategories; // Charger les données des nœuds depuis la réponse JSON
                    edgeCategories = response.edgeCategories; // Charger les données des relations (arêtes) depuis la réponse JSON
                    nodeClasses = response.nodeClasses;
                    edgeClasses = response.edgeClasses;
                    preloadImages(drawGraph);

                    // Debug optionnel : Afficher les données des nodes et edges pour vérification

                } catch (error) {
                  console.log(this.responseText);
                    console.error("Erreur lors de l'analyse du JSON :", error);
                }
            } else if (xhr.readyState === 4) {
                console.error("Erreur AJAX : ", xhr.status, xhr.statusText);
            }
        };


        xhr.open("POST", "Includes/PHP/load_json.php", false); // Remplacez ceci par l'URL correcte
        xhr.send(data);
    }


</script>

    <!--GESTIONNAIRE DES EVENEMENTS CANVAS-->
<script>
        // Déplacement des sommets (gestion souris) // panning = faire glisser l'image à l'écran
    canvas.addEventListener("mousedown", (event) => {
        tooltip.style.display = "none"; // Cacher le tooltip si aucun nœud n'est survolé

        if (!isAddingNode && !searchingSourceNode && !searchingTargetNode) {

            const mousePos = getMousePosition(event);
            mouseX = mousePos.x;
            mouseY = mousePos.y;
            draggedNode = getNodeUnderCursor(mouseX, mouseY);
            draggedEdge = getEdgeUnderCursor(mouseX, mouseY);

            if (searchingHiddenItem && (draggedNode||draggedEdge))
            {
                if (hideItems.textContent === "Masquer Items") {
                    if (draggedNode) {
                        hiddenNodes[draggedNode.id] = true;
                    } else if (draggedEdge) {
                        hiddenEdges[draggedEdge.id] = true;
                    }
                }
                else if (hideItems.textContent === 'Démasquer Items')
                {
                    if (draggedNode) {
                        hiddenNodes[draggedNode.id] = false;
                    } else if (draggedEdge) {
                        hiddenEdges[draggedEdge.id] = false;
                    }
                }
                modifyHiddenItems();
            }
            else
            {
            clickNode = getGlobeInTopLeftCorner(mouseX, mouseY);

            if (clickNode)
            {
                selectedItem = clickNode;
                selectTab("modifier");

                // Vérification si une URL est disponible dans le node
                // Créer une balise dynamique avec les sécurités
                const link = document.createElement("a");
                link.href = clickNode.source; // URL du node
                //updateOpeningsEPIX(clickNode.source);
                link.target = "_blank"; // Ouvrir dans un nouvel onglet
                link.rel = "noopener noreferrer"; // Appliquer les sécurités

                // Simuler un clic pour ouvrir le lien
                link.click();
            }
            else
            {
                clickNode = getImageInBottomLeftCorner(mouseX, mouseY);

                const canvasRect = canvas.getBoundingClientRect();


                if (clickNode) {
                    selectedItem = clickNode;
                    selectTab("modifier");
                    imageView.src = clickNode.image;


                    // Insérer l'image et afficher
                    imageView.src = clickNode.image; // Charger l'image
                    imageViewContainer.style.display = "flex"; // Affiche l'overlay

                }
                else
                {
                    if (draggedNode) {
                        isDragging = true;
                        startX = mouseX;
                        startY = mouseY;
                        selectedItem = draggedNode;
                        selectTab("modifier");
                    } else {
                        if (draggedEdge) {
                            if (searchingStyle)
                            {
                                storedEdgeStyleThickness = draggedEdge.thickness;
                                storedEdgeStyleSegmentColor = draggedEdge.segmentColor;
                                storedEdgeStyleArrowColor = draggedEdge.arrowColor;

                                edgeStyleArrowColor.value = draggedEdge.arrowColor;
                                edgeStyleThickness.value = draggedEdge.thickness;
                                edgeStyleSegmentColor.value = draggedEdge.segmentColor;

                                if (storedEdgeStyleType === "continuous" || storedEdgeStyleType === "dashed") {
                                    if (draggedEdge.type === "dashed" || draggedEdge.type === "continuous") {
                                        storedEdgeStyleType = draggedEdge.type;
                                        edgeStyleType.value = draggedEdge.type;
                                    }
                                }
                                else
                                if (storedEdgeStyleType === "arrowed" || storedEdgeStyleType === "lightArrowed") {
                                    if (draggedEdge.type === "arrowed" || draggedEdge.type === "lightArrowed") {
                                        storedEdgeStyleType = draggedEdge.type;
                                        edgeStyleType.value = draggedEdge.type;
                                    }
                                }
                                initArrowPreview(storedEdgeStyleArrowColor, storedEdgeStyleSegmentColor, storedEdgeStyleThickness, storedEdgeStyleType);
                            }
                            else
                            {
                                selectedItem = draggedEdge;
                                selectTab("modifier");
                            }
                        }
                        else
                        {
                            if (isRotating)
                            {
                                // Coordonnées du curseur
                                const x_cur = event.clientX - canvasRect.left;
                                const y_cur = event.clientY - canvasRect.top;

                                // Coordonnées du centre du canvas
                                const x_center = canvas.width / 2;
                                const y_center = canvas.height / 2;

                                // Différences
                                const dx = x_cur - x_center;
                                const dy = y_cur - y_center;

                                // Calcule de l'angle (en radians)
                                rotationAngle = Math.atan2(dy, dx);
                            }

                            else
                            {
                                if (!searchingStyle)
                                {
                                    isPanning = true;
                                    startX = event.clientX;
                                    startY = event.clientY;
                                    selectedItem = null;
                                }

                            }
                        }
                    }
                }
            }
        }
            preloadImages(drawGraph);
        }});

    canvas.addEventListener("mousemove", (event) => {
        const mousePos = getMousePosition(event);
        const hoveredNode = getNodeUnderCursor(mousePos.x, mousePos.y);
        const hoveredEdge = getEdgeUnderCursor(mousePos.x, mousePos.y);
        const rect = canvas.getBoundingClientRect();

        if (isDragging && draggedNode) {
            const mousePos = getMousePosition(event);
            draggedNode.x += mousePos.x - startX;
            draggedNode.y += mousePos.y - startY;
            startX = mousePos.x;
            startY = mousePos.y;
            preloadImages(drawGraph);
        }
        else if (isPanning) {
            offsetX += event.clientX - startX;
            offsetY += event.clientY - startY;
            startX = event.clientX;
            startY = event.clientY;
            preloadImages(drawGraph);
            }

        else if (hoveredNode && commentsVisibility.value !== "never" && !isRotating && !hoveredNode.hidden)
        {
            // Afficher le tooltip avec le commentaire
            if (hoveredNode.comments !== "") {
                tooltip.textContent = hoveredNode.comments; // Si pas de commentaire, afficher un message par défaut
                tooltip.style.display = "block";
                tooltip.style.left = `${event.clientX - rect.left + 40}px`; // Décalage à droite de la position de la souris
                tooltip.style.top = `${event.clientY - rect.top + 40}px`; // Décalage en bas de la position de la souris
            }
            else if (commentsVisibility.value === "always") {
                tooltip.textContent = "Pas de commentaires"; // Si pas de commentaire, afficher un message par défaut
                tooltip.style.display = "block";
                tooltip.style.left = `${event.clientX - rect.left + 40}px`; // Décalage à droite de la position de la souris
                tooltip.style.top = `${event.clientY - rect.top + 40}px`; // Décalage en bas de la position de la souris            }
            }
            else
                {
                    tooltip.style.display = "none";
                }
        }
        else if (hoveredEdge && commentsVisibility.value !== "never" && !isRotating && !hoveredEdge.hidden)
        {
            // Afficher le tooltip avec le commentaire
            if (hoveredEdge.comments !== "") {
                tooltip.textContent = hoveredEdge.comments; // Si pas de commentaire, afficher un message par défaut
                tooltip.style.display = "block";
                tooltip.style.left = `${event.clientX - rect.left + 40}px`; // Décalage à droite de la position de la souris
                tooltip.style.top = `${event.clientY - rect.top + 40}px`; // Décalage en bas de la position de la souris
            }
            else if (commentsVisibility.value === "always") {
                tooltip.textContent = "Pas de commentaires"; // Si pas de commentaire, afficher un message par défaut
                tooltip.style.display = "block";
                tooltip.style.left = `${event.clientX - rect.left + 40}px`; // Décalage à droite de la position de la souris
                tooltip.style.top = `${event.clientY - rect.top + 40}px`; // Décalage en bas de la position de la souris            }
            }
            else
            {
                tooltip.style.display = "none";
            }
        }
        else if (isRotating && rotationAngle !== 0)
        {
            // Coordonnées du curseur
            const x_cur = event.clientX - rect.left;
            const y_cur = event.clientY - rect.top;

            // Coordonnées du centre du canvas
            const x_center = canvas.width / 2;
            const y_center = canvas.height / 2;

            // Différences
            const dx = x_cur - x_center;
            const dy = y_cur - y_center;

            // Calcule de l'angle (en radians)
            finalAngle = Math.atan2(dy, dx);
            rotateNodes(finalAngle-rotationAngle, false);
            rotationAngle = finalAngle;

            tooltip.style.display = "none";
        }

        else {
            tooltip.style.display = "none"
        }

        //Calculer les coordonnées réelles en tenant compte du zoom et du décalage
        const x = event.clientX - rect.left ;
        const y = event.clientY - rect.top;
    });

    canvas.addEventListener("mouseup", (event) => {
        mousePos = getMousePosition(event);
        draggedNode = null;
        isPanning = false;
        startX = 0;
        startY = 0;
        const node = getNodeUnderCursor(mousePos.x, mousePos.y);
        const rect = canvas.getBoundingClientRect();


        if (isDragging) {
        data = new FormData();
        data.append("project_id", <?php echo $_SESSION["project_id"];?>);
        data.append("nodeID", node.id);
        data.append("newNodePosition", '{"x": ' + Math.round(node.x) + ', "y": ' + Math.round(node.y) + '}');

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
        xhr.onreadystatechange = function () {
            if (isRequestSuccessful(this)) {
                preloadImages(drawGraph);
            }
        }

        xhr.send(data);

        isDragging = false;
        }

        else if (isAddingNode) {

            //calcule les coordonnées dans le référentiel des nodes et des edges
            const x = (event.clientX - rect.left - offsetX) / scale;
            const y = (event.clientY - rect.top - offsetY) / scale;
            addNode(x, y); // Appelle la fonction pour ajouter un sommet
            // Désactiver le mode après l'ajout
            isAddingNode = false;
            cursorIcon.style.display = 'none'; // efface l'icône
        }

        else if (searchingSourceNode && node) {
                searchingSourceNode = false;
                sourceNode.style.border = "";
                sourceNode.value = node.id;
        }

        else if (searchingTargetNode && node) {
                searchingTargetNode = false;
                targetNode.style.border = "";
                targetNode.value = node.id;
        }
        else if (isRotating) {
            // Coordonnées du curseur
            const x_cur = event.clientX - rect.left;
            const y_cur = event.clientY - rect.top;

            // Coordonnées du centre du canvas
            const x_center = canvas.width / 2;
            const y_center = canvas.height / 2;

            // Différences
            const dx = x_cur - x_center;
            const dy = y_cur - y_center;

            // Calcule de l'angle (en radians)
            finalAngle = Math.atan2(dy, dx);
            rotateNodes(finalAngle-rotationAngle, true);
            rotationAngle = 0;
        }
    }
    )

    canvas.addEventListener("mouseleave", (event) => {
        const rect = canvas.getBoundingClientRect();
        isDragging = false; // Arrêter si la souris quitte le canvas
        draggedNode = null;
        isPanning = false;
        tooltip.style.display = "none"; // Cacher le tooltip
        if (isRotating && rotationAngle !== 0) {
            // Coordonnées du curseur
            const x_cur = event.clientX - rect.left;
            const y_cur = event.clientY - rect.top;

            // Coordonnées du centre du canvas
            const x_center = canvas.width / 2;
            const y_center = canvas.height / 2;

            // Différences
            const dx = x_cur - x_center;
            const dy = y_cur - y_center;

            // Calcule de l'angle (en radians)
            finalAngle = Math.atan2(dy, dx);
            rotateNodes(finalAngle-rotationAngle, true);
            rotationAngle = 0;
        }
    });

    // Gestion du zoom
    canvas.addEventListener("wheel", (event) => {
        event.preventDefault();
        const zoomIntensity = 0.1;
        const mouseX = event.offsetX;
        const mouseY = event.offsetY;
        const zoom = event.deltaY > 0 ? 1 - zoomIntensity : 1 + zoomIntensity;

        offsetX = mouseX - (mouseX - offsetX) * zoom;
        offsetY = mouseY - (mouseY - offsetY) * zoom;
        scale *= zoom;

        preloadImages(drawGraph);
    });


    // Obtenir la position de la souris en tenant compte du zoom et des décalages
    function getMousePosition(event) {
        const rect = canvas.getBoundingClientRect();
        const x = (event.clientX - rect.left - offsetX) / scale;
        const y = (event.clientY - rect.top - offsetY) / scale;
        return { x, y };
    }

    function getNodeUnderCursor(x, y) {
        // Parcourir les valeurs de l'objet nodes
        var node_under_cursor = null;
        for (var key in nodes) {
            const node = nodes[key]; // Récupérer le node correspondant

            // Vérifier si le curseur (x, y) est à l'intérieur du rectangle
            if (
                x >= node.boxX && x <= node.boxX + node.boxWidth && // Vérifie les limites horizontales
                y >= node.boxY && y <= node.boxY + node.boxHeight  // Vérifie les limites verticales
            ) {
                node_under_cursor = node;
            }
        }
        if (node_under_cursor) {
            return node_under_cursor;} // Retourner l'objet node si le curseur est au-dessus
        else {
            return null;} // Aucun node sous le curseur
    }

    function getEdgeUnderCursor(x, y) {
            let edge_under_cursor = null;

            // Parcourir les arêtes (edges)
            for (let key in edges) {
                const edge = edges[key];
                const sourceNode = nodes[edge.sourceNode]; // Récupère le nœud source
                const targetNode = nodes[edge.targetNode]; // Récupère le nœud cible

                // Coordonnées des nœuds
                const startX = sourceNode.x;
                const startY = sourceNode.y;
                const endX = targetNode.x;
                const endY = targetNode.y;

                // Calcul des distances pour vérifier si le curseur est proche de l'arête
                const distance = pointToLineDistance(x, y, startX, startY, endX, endY, edge.curve);

                if (distance <= 5) { // Par exemple, une distance de 5 pixels est utilisée comme seuil
                    edge_under_cursor = edge;
                    break;
                }
            }
            return edge_under_cursor;
        }

    // Fonction pour calculer la distance entre un point et une ligne
    function pointToLineDistance(x, y, x1, y1, x2, y2, c=0) {
        const d = Math.sqrt(Math.pow(x1-x2,2) + Math.pow(y1-y2,2));
        const controlX = - c * (y1 - y2) / (d*2); // Décalage du point médiant en X
        const controlY =  c * (x1 - x2) / (d*2); // Décalage du point médiant en Y

        const x1_bis = x1 + controlX;
        const x2_bis = x2 + controlX;
        const y1_bis = y1 + controlY;
        const y2_bis = y2 + controlY;

        const A = x - x1_bis ;
        const B = y - y1_bis ;
        const C = x2 - x1;
        const D = y2 - y1;

        const dot = A * C + B * D;
        const lenSq = C * C + D * D;
        const param = lenSq !== 0 ? dot / lenSq : -1;

        let xx, yy;

        if (param < 0) {
            xx = x1_bis;
            yy = y1_bis;
        } else if (param > 1) {
            xx = x2_bis;
            yy = y2_bis;
        } else {
            xx = x1_bis + param * C;
            yy = y1_bis + param * D;
        }

        const dx = x - xx;
        const dy = y - yy;
        const distance = Math.sqrt(dx * dx + dy * dy);

        return distance;
    }


        function getGlobeInTopLeftCorner(x, y) {
            for (var key in nodes) {
                const node = nodes[key]; // Récupérer le node

                // Définit les dimensions du coin supérieur gauche
                const cornerWidth = 8;  // 10 % de la largeur
                const cornerHeight = 8;

                // Vérifier si le clic est dans la zone supérieure gauche
                if (
                    x >= node.boxX -cornerWidth && x <= node.boxX + cornerWidth && // Dans la largeur du coin
                    y >= node.boxY -cornerHeight && y <= node.boxY + cornerHeight &&   // Dans la hauteur du coin
                    node.source !== ""
                ) {
                    return node; // Retourne le node si le clic est dans le coin
                }
            }

            return null; // Aucun node trouvé dans le coin supérieur gauche
        }

        function getImageInBottomLeftCorner(x, y) {
            for (var key in nodes) {
                const node = nodes[key]; // Récupérer le node

                // Définit les dimensions du coin supérieur gauche
                const cornerWidth = 8;
                const cornerHeight = 8;

                // Vérifier si le clic est dans la zone supérieure gauche
                if (
                    x >= node.boxX -cornerWidth && x <= node.boxX + cornerWidth && // Dans la largeur du coin
                    y >= node.boxY -cornerHeight + node.boxHeight && y <= node.boxY + cornerHeight + node.boxHeight &&   // Dans la hauteur du coin
                    node.image !== ""
                ) {
                    return node; // Retourne le node si le clic est dans le coin
                }
            }

            return null; // Aucun node trouvé dans le coin supérieur gauche
        }

    window.addEventListener("resize", () => {
        resizeCanvas();
        if (edgeStyle.style.display !== "none") {
            initArrowPreview(storedEdgeStyleArrowColor, storedEdgeStyleSegmentColor, storedEdgeStyleThickness, storedEdgeStyleType);
        }

        preloadImages(drawGraph);
    });

        // Fonction pour fermer le conteneur
        function closeImageDialog() {
            const imageContainer = document.getElementById("imageViewContainer");

            // Masquer le conteneur
            imageContainer.style.display = "none";

            // Nettoyage de l'image
            const dialogImage = document.getElementById("imageView");
            dialogImage.src = ""; // Réinitialise la source de l'image
        }

        // Ajouter le gestionnaire d'événement au bouton
        document.getElementById("closeButton").addEventListener("click", closeImageDialog);

        </script>


        <!--GESTIONNAIRE DE PARAMETER BAR-->
        <script>

        // Fonction pour basculer l'état de "searchingHiddenItem"
        function toggleItemsVisibility()
        {
            if (hideItems.textContent === "Masque désactivé")
            {
                hideItems.style.backgroundColor = "purple";
                hideItems.textContent = "Masquer Items";
                searchingHiddenItem = true;
                isRotating = false;
                rotateButton.style.backgroundColor = "grey";
            }

            else if (hideItems.textContent === "Masquer Items") {
                hideItems.style.backgroundColor = "green";
                hideItems.textContent = "Démasquer Items";
                searchingHiddenItem = true;
            }
            else {
                hideItems.style.backgroundColor = "grey";
                hideItems.textContent = "Masque désactivé";
                searchingHiddenItem = false;
            }
            modifyHiddenItems();
            preloadImages(drawGraph);
        }

        rotateButton.addEventListener("click", () => {
            if (isRotating) {
            rotateButton.style.backgroundColor = "grey";
            isRotating = false;
            }
            else {
            rotateButton.style.backgroundColor = "green";
            isRotating = true;
            hideItems.style.backgroundColor = "grey";
            hideItems.textContent = "Masque désactivé";
            searchingHiddenItem = false;
            }
        });

            tridButton.addEventListener("click", () => {
            if (!isTrid) {
                tridGo.innerHTML = "&#x23EF;";

                if (tridButton.style.backgroundColor === "grey") {
                    tridButton.style.backgroundColor = "darkkhaki";
                    tridGo.style.backgroundColor = "#3498db";
                } else if (tridButton.style.backgroundColor === "darkkhaki") {
                    tridButton.style.backgroundColor = "darkolivegreen";
                } else if (tridButton.style.backgroundColor === "darkolivegreen") {
                    tridButton.style.backgroundColor = "darkgreen";
                } else if (tridButton.style.backgroundColor === "darkgreen") {
                    tridButton.style.backgroundColor = "grey";
                    tridGo.style.backgroundColor = "grey";
                    tridGo.innerHTML = "&#x23F8;";
                }
            }


        })

        tridGo.addEventListener("click", () => {
            if (tridButton.style.backgroundColor !== "grey" && !isTrid)
            {
                isTrid = true

                if (tridButton.style.backgroundColor === "darkkhaki")
                {
                    rotateGraph(10000);
                }
                else if (tridButton.style.backgroundColor === "darkolivegreen")
                {
                    rotateGraph(8000);
                }
                else if (tridButton.style.backgroundColor === "darkgreen")
                {
                    rotateGraph(5000);
                }
                tridGo.innerHTML = "&#x23F8;";
            }

            else
            {
                if (isTrid) {
                    if (tridGo.style.backgroundColor === 'grey')
                    {
                        tridGo.innerHTML = '&#x23F8;';
                        tridGo.style.backgroundColor = '#3498db';
                    }
                    else {
                        tridGo.innerHTML = '&#x23EF;';
                        tridGo.style.backgroundColor = 'grey';
                    }
                }

            else
                {
                    tridGo.innerHTML = "&#x23F8;"
                }
            }


        })

        printButton.addEventListener("click", () => {
            // Convertir le contenu du canvas en image
            const dataUrl = canvas.toDataURL("image/png");

            // Créer un lien de téléchargement automatique
            const link = document.createElement("a");
            link.href = dataUrl;
            link.download = "capture.png";
            link.click();

            // Ouvrir une nouvelle fenêtre
            const newWindow = window.open("", "_blank");

            // Construire le contenu de la nouvelle fenêtre
            const html = `
        <html>
            <head>
                <title>Impression du Canvas</title>
            </head>
            <body>
                <img src="${dataUrl}" style="width:100%;">
            </body>
        </html>
    `;

            // Insérer le contenu HTML dans la nouvelle fenêtre
            newWindow.document.open();
            newWindow.document.write(html);

            // Ajouter le script JavaScript via DOM
            const script = newWindow.document.createElement("script");
            script.textContent = `
        window.onload = function() {
            window.print();
            window.close();
        };
    `;
            newWindow.document.body.appendChild(script);

            newWindow.document.close();
        });

</script>


<!--GESTION DES ONGLETS-->

<script>
    let onglet_actif = document.getElementById("ajouter");

    document.getElementById('ajouter').addEventListener('click', () => {
        selectTab('ajouter');
    });

    function selectTab(id) {
        // Si un onglet actif existe, on le réinitialise
        if (onglet_actif) {
            onglet_actif.style.backgroundColor = "darkgrey";
        }

        //Si la demande est correcte
        if (!(id === "modifier" && selectedItem === null)) {
            // Mettre à jour le bouton d'onglet actif
            onglet_actif = document.getElementById(id);
            onglet_actif.style.backgroundColor = "dimgrey";
            tabContent(selectedItem, id);
        }
        else {
            alert("Vous devez sélectionner un item pour accéder à l'onglet Modifier")
        }
    }

    function tabContent(x, tab) {
        if (x && x.hasOwnProperty('name') && tab == "modifier") {

            edgePanel.style.display = "none";
            nodePanel.style.display = "block";
            managementPanel.style.display = "none";

            if (nodeContent.style.display === "none") {
              toggleCategory("nodeContent", nodePlus);
            };

            nodeTitle.textContent = "Modifier Sommet";
            nodeNameDescription.innerHTML = "Nouveau nom&nbsp;:&nbsp;";
            nodeName.value = x.name;
            nodeComment.value = x.comments;
            nodeLink.value = x.source;
            nodeLinkDescription.innerHTML = "Nouveau lien&nbsp;:&nbsp;";
            document.getElementById('nodeDetectedSource').style.display = 'none';
            nodeGeoCoordDescription.innerHTML = "Nouvelles coordonnées&nbsp;:&nbsp;";
            nodeGeoCoord.value = x.geoCoord;

            let content = "";
            for (let classId in nodeClasses) {
              content += `<optgroup label="${nodeClasses[classId]}" style="font-weight:bold;"></optgroup>`;
                for (let id in nodeCategories) {
                    if ((nodeCategories[id].actif === 1) && (nodeCategories[id].class == classId)) {
                      let selected = (id == x.category) ? "selected" : "";
                      content += `<option value="${id}" ${selected}>${nodeCategories[id].name}</option>`;
                    }
                }
            }

            nodeCategory.innerHTML = content;

            if (x.icon) {
                actualIconPreviewContainer.style.display = "flex";
                actualIconPreview.src = x.icon;
                nodeIconDescription.textContent = "Changer d'icône";
                removeIconButton.style.display = "block";
                nodeIconInput.value = "";
            } else {
                actualIconPreviewContainer.style.display = "none";
                nodeIconDescription.textContent = "Importer une icône";
                nodeIconInput.value = "";
                removeIconButton.style.display = "none";
            }
            newIconPreviewContainer.style.display = "none";

            if (x.image) {
                actualImagePreviewContainer.style.display = "flex";
                actualImagePreview.src = x.image;
                nodeImageDescription.textContent = "Changer d'image";
                removeImageButton.style.display = "block";
                nodeImageInput.value = "";
            } else {
                actualImagePreviewContainer.style.display = "none";
                nodeImageDescription.textContent = "Importer une image";
                nodeImageInput.value = "";
                removeImageButton.style.display = "none";
            }
            newImagePreviewContainer.style.display = "none";

            updateNodeButton.onclick = modifyNode;
            updateNodeButton.textContent = "Redéfinir Sommet";

            deleteItemButton.style.display = "block";
        }

        else if (x && x.hasOwnProperty('sourceNode') && (tab == "modifier")) {
            nodePanel.style.display = "none";
            edgePanel.style.display = "block";
            managementPanel.style.display = "none";
            edgeStyle.style.display = "block";
            edgeContent.style.display = "block";
            edgePlus.textContent = "-";

            edgeTitle.textContent = "Modifier Relation";
            sourceNodeDescription.innerHTML = "Nouveau sommet source&nbsp;:&nbsp;";
            targetNodeDescription.innerHTML = "Nouveau sommet cible&nbsp;:&nbsp;";
            edgeLinkDescription.innerHTML = "Nouveau lien&nbsp;:&nbsp;";
            edgeLink.value = x.source;
            document.getElementById('edgeDetectedSource').style.display = 'none';

            content = "";
            for (let key in nodes) {
                if (x.sourceNode == key) {
                    content += `<option id="sourceNodeOption_${key}" value="${nodes[key].id}" selected>${nodes[key].name}</option>`;
                }
                else {
                    content += `<option id="sourceNodeOption_${key}" value="${nodes[key].id}">${nodes[key].name}</option>`;
                }
            }
            sourceNode.innerHTML = content;
            content = "";
            for (let key in nodes) {
                if (x.targetNode == key) {
                    content += `<option id="targetNodeOption_${key}" value="${nodes[key].id}" selected>${nodes[key].name}</option>`;
                }
                else {
                    content += `<option id="targetNodeOption_${key}" value="${nodes[key].id}">${nodes[key].name}</option>`;
                }
            }
            targetNode.innerHTML = content;
            content = "";
            for (let classId in edgeClasses) {
              content += `<optgroup label="${edgeClasses[classId]}" style="font-weight:bold;"></optgroup>`;
                for (let id in edgeCategories) {
                    if ((edgeCategories[id].actif === 1) && (edgeCategories[id].class == classId)) {
                      let selected = (id == x.category) ? "selected" : "";
                      content += `<option value="${id}" ${selected}>${edgeCategories[id].name}</option>`;                    }
                }
            }
            edgeCategory.innerHTML = content;

            edgeStyleArrowColor.value = x.arrowColor;
            edgeStyleSegmentColor.value = x.segmentColor;
            edgeStyleThickness.value = x.thickness;

            storedEdgeStyleArrowColor = x.arrowColor;
            storedEdgeStyleSegmentColor = x.segmentColor;
            storedEdgeStyleThickness = x.thickness;
            storedEdgeStyleType = x.type;
            initArrowPreview(storedEdgeStyleArrowColor, storedEdgeStyleSegmentColor, storedEdgeStyleThickness, storedEdgeStyleType);

            modifyEdgeStyleButton.textContent = "Modifier";
            choosingStyle.style.display = "none";

            deleteItemButton.style.display = "block";
            updateEdgeButton.onclick = modifyEdge;
            updateEdgeButton.textContent = "Redéfinir Relation";

        }

        else if (tab == "ajouter") {
            edgePanel.style.display = "block";
            nodePanel.style.display = "block";
            managementPanel.style.display = "none";

            nodeTitle.textContent = "Nouveau Sommet";
            nodeNameDescription.innerHTML = "Nom&nbsp;:&nbsp;";
            nodeName.value = '';
            nodeComment.value = '';
            nodeLink.value = '';
            edgeLink.value = '';
            nodeLinkDescription.innerHTML = "Affecter un lien&nbsp;:&nbsp;";
            edgeLinkDescription.innerHTML = "Affecter un lien&nbsp;:&nbsp;";
            nodeGeoCoordDescription.innerHTML = "Coordonnées&nbsp;:&nbsp;";
            nodeGeoCoord.value = '';
            document.getElementById('nodeDetectedSource').style.display = 'none';
            document.getElementById('edgeDetectedSource').style.display = 'none';

            actualIconPreviewContainer.style.display = "none";
            newIconPreviewContainer.style.display = "none";
            removeIconButton.style.display = "none";
            nodeIconDescription.textContent = "Importer une icône";

            actualImagePreviewContainer.style.display = "none";
            newImagePreviewContainer.style.display = "none";
            removeImageButton.style.display = "none";
            nodeImageDescription.textContent = "Importer une image";

            content = '<option value="" selected>Catégorie</option>';
            for (let classId in nodeClasses) {
              content += `<optgroup label="${nodeClasses[classId]}" style="font-weight:bold;"></optgroup>`;
                for (let id in nodeCategories) {
                    if ((nodeCategories[id].actif === 1) && (nodeCategories[id].class == classId)) {
                        content += `<option value="${id}">${nodeCategories[id].name}</option>`;
                    }
                }
            }

            nodeCategory.innerHTML = content;

            updateNodeButton.textContent = "Ajouter Sommet";
            updateNodeButton.onclick = '';

            edgeTitle.textContent = "Nouvelle Relation";
            sourceNodeDescription.textContent = "Sommet source : ";
            targetNodeDescription.textContent = "Sommet cible : ";
            edgeComment.value = '';

            content = "<option value=''>Sommet source</option>";
            for (let key in nodes) {
                content += `<option id="sourceNodeOption_${key}" value="${nodes[key].id}">${nodes[key].name}</option>`;
            }
            sourceNode.innerHTML = content;

            content = "<option value=''>Sommet cible</option>";
            for (let key in nodes) {
                content += `<option id="targetNodeOption_${key}" value="${nodes[key].id}">${nodes[key].name}</option>`;
            }
            targetNode.innerHTML = content;

            content = '<option value="" selected>Catégorie</option>';
            for (let classId in edgeClasses) {
              content += `<optgroup label="${edgeClasses[classId]}" style="font-weight:bold;"></optgroup>`;
                for (let id in edgeCategories) {
                    if ((edgeCategories[id].actif === 1) && (edgeCategories[id].class == classId)) {
                      content += `<option value="${id}">${edgeCategories[id].name}</option>`;                    }
                }
            }
            edgeCategory.innerHTML = content;
            edgeCategory.innerHTML = content;
            updateEdgeButton.onclick = addEdge;
            updateEdgeButton.textContent = "Ajouter Relation";

            modifyEdgeStyleButton.textContent = "Modifier";
            choosingStyle.style.display = "none";

            deleteItemButton.style.display = "none";
        }

        else if (tab == "gestion") {
            edgePanel.style.display = "none";
            nodePanel.style.display = "none";
            managementPanel.style.display = "flex"; //important flex et pas block pour l'affichage.
            deleteItemButton.style.display = "none";


            content = "";
            for (let key in nodeCategories) {
                // Déterminer la couleur en fonction de nodeCategories[key].owner
                let backgroundColor = (nodeCategories[key].owner === 0 || nodeCategories[key].owner === -1) ? "background-color: #FFDAB9;" : "background-color: #ADD8E6;";

                if (nodeCategories[key].actif) {
                    content += `<li style="${backgroundColor}"><label><input type="checkbox" value="${key}" checked>${nodeCategories[key].name}</label></li>`;
                } else {
                    content += `<li style="${backgroundColor}"><label><input type="checkbox" value="${key}">${nodeCategories[key].name}</label></li>`;
                }
            }
            nodeCategoriesManagement.innerHTML = content;

            content = "";
            for (let id in edgeCategories) {
                // Déterminer la couleur en fonction de edgeCategories[id].owner
                let backgroundColor = (edgeCategories[id].owner === 0) ? "background-color: #FFDAB9;" : "background-color: #ADD8E6;";

                if (edgeCategories[id].actif) {
                    content += `<li style="${backgroundColor}"><label><input type="checkbox" value="${id}" checked>${edgeCategories[id].name}</label></li>`;
                } else {
                    content += `<li style="${backgroundColor}"><label><input type="checkbox" value="${id}">${edgeCategories[id].name}</label></li>`;
                }
            }
            edgeCategoriesManagement.innerHTML = content;
        }

        if (searchingStyle)
        {
            copyStyle();
        }
    }

    function modifyNode() {

        data = new FormData();

        var icon = nodeIconInput.files[0];
        if (icon) {data.append("newNodeIcon", icon);}
        else if (removeIconButton.style.display === "none") {data.append("deleteIcon", 1);}
        else {data.append("deleteIcon", 0);}

        var image = nodeImageInput.files[0];
        if (image) {data.append("newNodeImage", image);}
        else if (removeImageButton.style.display === "none") {data.append("deleteImage", 1);}
        else {data.append("deleteImage", 0);}


        data.append("project_id", <?php echo $_SESSION["project_id"];?>);
        data.append("newNodeName", nodeName.value.trim());
        data.append("newNodeCategory", nodeCategory.value);
        data.append("newNodeComments", nodeComment.value.trim());
        data.append("newNodeLink", nodeLink.value);
        data.append("nodeID", selectedItem.id);
        data.append("newNodeDetectedSource", (nodeLink.value !== '') ? document.getElementById('nodeSource-select').value : 0);
        data.append("newNodeGeoCoord", nodeGeoCoord.value);

        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (isRequestSuccessful(this))  {
                document.getElementById("sourceNodeOption_"+selectedItem.id).textContent = nodeName.value.trim();
                document.getElementById("targetNodeOption_"+selectedItem.id).textContent = nodeName.value.trim();
                load_graph_json();
                selectTab("ajouter");
                console.log(this.responseText);
            }
        }

        xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
        xhr.send(data);

    }

    function modifyEdge() {

        data = new FormData();

        data.append("project_id", <?php echo $_SESSION["project_id"];?>);
        data.append("newSourceNode", sourceNode.value);
        data.append("newTargetNode", targetNode.value);
        data.append("newEdgeCategory", edgeCategory.value);
        data.append("newEdgeComments", edgeComment.value.trim());
        data.append("newEdgeStyle", '{"arrowColor":"' + storedEdgeStyleArrowColor + '", "segmentColor":"' + storedEdgeStyleSegmentColor + '", "thickness":' + storedEdgeStyleThickness + ', "type":"'+ storedEdgeStyleType + '"}');
        data.append("newEdgeLink", edgeLink.value);
        data.append("edgeID", selectedItem.id);
        data.append("newEdgeDetectedSource", (edgeLink.value !== '') ? document.getElementById('edgeSource-select').value : 0);

        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (isRequestSuccessful(this))  {
                load_graph_json();
                selectTab("ajouter");
            }
        }


        xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
        xhr.send(data);

    }

    function modifyEdgeStyle() {
            if (modifyEdgeStyleButton.textContent === "Modifier") {
                modifyEdgeStyleButton.textContent = "Annuler";
                choosingStyle.style.display = "block";
                let cntnt = '';

                if (edgeCategories[edgeCategory.value].isOriented == 1)
                {
                    cntnt =  `<option id="arrowed" value='arrowed'>Ligne fléchée</option><option id="lightArrowed" value='lightArrowed'>Ligne peu fléchée</option>`;
                }
                else
                {
                    cntnt = `<option id="continuous" value="continuous">Ligne continue</option><option id="dashed" value='dashed'>Ligne pointillée</option>`;
                }
                edgeStyleType.innerHTML = cntnt;

                document.getElementById("styleEdgeStatus").innerHTML = "Nouveau style d'arête&nbsp;:&nbsp;";

            } else {
                modifyEdgeStyleButton.textContent = "Modifier";
                choosingStyle.style.display = "none";
                document.getElementById("styleEdgeStatus").innerHTML = "Style d'arête&nbsp;:&nbsp;";
                if (searchingStyle)
                {
                    copyStyle();
                }
                //Plus tard à adapter en fonction du style de la catégorie sélectionnée
            }
        initArrowPreview(storedEdgeStyleArrowColor, storedEdgeStyleSegmentColor, storedEdgeStyleThickness, storedEdgeStyleType);
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Delete' && event.ctrlKey) {
            deleteItem();
        }
    });


    document.addEventListener('keydown', function(event) {
      if (event.key === 'Enter' && isAddingNode)
      {
        const x = (canvas.width/2 - offsetX) / scale;
        const y = (canvas.height/2 - offsetY) / scale;
        addNode(x, y);
        isAddingNode = false;
        cursorIcon.style.display = 'none'; // efface l'icône
      }
    })

    function deleteItem() {
        // Continuer la suppression si confirmé
        const data = new FormData();
        data.append("project_id", <?php echo $_SESSION["project_id"];?>);

        if (selectedItem.hasOwnProperty('sourceNode')) {
            // Afficher une alerte de confirmation
            const confirmation = confirm("Êtes-vous sûr de vouloir supprimer cette relation ?");

            // Si l'utilisateur annule, ne pas continuer
            if (!confirmation) {
                return;
            }
            data.append("deletedEdgeID", selectedItem.id);
            edgeBin[selectedItem.id] = selectedItem;
            edgeBin[selectedItem.id].sourceNodeName = nodes[selectedItem.sourceNode].name;
            edgeBin[selectedItem.id].targetNodeName = nodes[selectedItem.targetNode].name;
            refreshDeletedItems()
        }
        else
        {
            // Afficher une alerte de confirmation
            const confirmation = confirm("Êtes-vous sûr de vouloir supprimer ce sommet et les relations qui en dépendent ?");

            // Si l'utilisateur annule, ne pas continuer
            if (!confirmation) {
                return;
            }
            data.append("deletedNodeID", selectedItem.id);
            nodeBin[selectedItem.id] = selectedItem;
            nodeBin[selectedItem.id].name = nodes[selectedItem.id].name;
            for (let id in edges)
            {
                if (edges[id].sourceNode === selectedItem.id || edges[id].targetNode === selectedItem.id)
                {
                    edgeBin[id] = edges[id];
                    edgeBin[id].sourceNodeName = nodes[edges[id].sourceNode].name;
                    edgeBin[id].targetNodeName = nodes[edges[id].targetNode].name;
                }
            }

            refreshDeletedItems()
        }

        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (isRequestSuccessful(this)) {
                load_graph_json();
                selectTab("ajouter");
            }
        };

        xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
        xhr.send(data);
    }

    nodeComment.oninput = function () {
        nodeComment.style.height = "min-content";
        nodeComment.rows = Math.min(nodeComment.scrollHeight / 20, 8);
    }
    edgeComment.oninput = function () {
        edgeComment.rows = Math.min(edgeComment.scrollHeight / 20, 8);
    }

    function copyStyle() {
        if (searchingStyle) {
            document.getElementById("copyStyleButton").style.border = "none";
            document.getElementById("copyStyleButton").removeAttribute("title") ;
            searchingStyle = false;
        }
        else {
            document.getElementById("copyStyleButton").style.border = "3px solid red";
            document.getElementById("copyStyleButton").title = "Cliquez sur une relation pour copier le style.";

            searchingStyle = true;
        }
    }



</script>
<!-- GESTIONNAIRE DES EVENEMENTS ONGLETS-->
<script>

        updateNodeButton.addEventListener("mousedown", (event) => {

            if (nodeName.value.trim() == "" || nodeCategory.value == "") {
                alert('Veuillez entrer un nom et une catégorie valide.');
                return;
            }
            else if (edgePanel.style.display !== "none" && nodePanel.style.display !== "none") {
                isAddingNode = true;
                cursorIcon.style.display = 'block'; // Affiche l'icône
                cursorIcon.style.left = (event.clientX - 16) + "px"; // Position horizontale
                cursorIcon.style.top = (event.clientY - 16) + "px";  // Position verticale
            }
        });

        document.addEventListener("mousemove", (event) => {
            if (isAddingNode) { // Vérifie si la condition pour afficher l'icône est remplie
                //cursorIcon.style.display = 'block'; // Affiche l'icône
                cursorIcon.style.left = (event.clientX - 16) + "px"; // Position horizontale
                cursorIcon.style.top = (event.clientY - 16) + "px";  // Position verticale
            }});

        document.addEventListener("mouseup", (event) => {
            if (event.target !== sourceNode && event.target !== targetNode) {
                var abs = event.clientX;
                var ord = event.clientY;
                const rect = canvas.getBoundingClientRect();

                // Vérifie si la souris est en dehors des limites du canvas
                const isOutsideCanvas =
                    abs < rect.left ||
                    abs > rect.right ||
                    ord < rect.top ||
                    ord > rect.bottom;

                if (isOutsideCanvas) {
                    if (searchingSourceNode) {
                        searchingSourceNode = false;
                        sourceNode.style.border = "";
                    } else if (searchingTargetNode) {
                        searchingTargetNode = false;
                        targetNode.style.border = "";
                    }

                    if (isAddingNode) {
                        isAddingNode = false;
                        cursorIcon.style.display = 'none'; // Masque l'icône
                    }
                }
            }
        });

        sourceNode.addEventListener("mousedown", (event) => {
            if (searchingSourceNode) {
                sourceNode.style.border = "";
                searchingSourceNode = false;
            } else if (searchingTargetNode) {
                sourceNode.style.border = "2px solid red";
                targetNode.style.border = "";
                searchingTargetNode = false;
                searchingSourceNode = true;
            } else {
                sourceNode.style.border = "2px solid red";
                searchingSourceNode = true;
            }
        })

        targetNode.addEventListener("mousedown", (event) => {

            if (searchingTargetNode){
                targetNode.style.border = "";
                searchingTargetNode = false;
            }

            else if (searchingSourceNode) {
                targetNode.style.border = "2px solid red";
                sourceNode.style.border = "";
                searchingSourceNode = false;
                searchingTargetNode = true;
            }
            else {
                targetNode.style.border = "2px solid red";
                searchingTargetNode = true;
            }
        })

        // Ajouter un écouteur d'événement au changement de sélection de catégorie d'arête du graphe.
        edgeCategory.addEventListener("change", function () {
            if (edgeCategory.value !== "") {
                // Si une option est sélectionnée, afficher #choosingStyle
                edgeStyle.style.display = "block";
                storedEdgeStyleType = edgeCategories[edgeCategory.value].type;
                storedEdgeStyleThickness = edgeCategories[edgeCategory.value].thickness;
                storedEdgeStyleArrowColor = edgeCategories[edgeCategory.value].arrowColor;
                storedEdgeStyleSegmentColor = edgeCategories[edgeCategory.value].segmentColor;

                edgeStyleThickness.value = edgeCategories[edgeCategory.value].thickness;
                edgeStyleArrowColor.value = edgeCategories[edgeCategory.value].arrowColor;
                edgeStyleSegmentColor.value = edgeCategories[edgeCategory.value].segmentColor;

                initArrowPreview(storedEdgeStyleArrowColor, storedEdgeStyleSegmentColor, storedEdgeStyleThickness, storedEdgeStyleType);
                if (edgeCategories[edgeCategory.value].isOriented == 1)
                {
                    content =  `<option id="arrowed" value='arrowed'>Ligne fléchée</option><option id="lightArrowed" value='lightArrowed'>Ligne peu fléchée</option>`
                }
                else
                {
                    content = `<option id="continuous" value="continuous">Ligne continue</option><option id="dashed" value='dashed'>Ligne pointillée</option>`;
                }
                edgeStyleType.innerHTML = content;

                document.getElementById(storedEdgeStyleType).selected = true;

            }
            else {
                // Sinon, cacher #choosingStyle
                edgeStyle.style.display = "none";
            }
        });

        // Pour mettre à jour l'aperçu de la nouvelle arête.
        edgeStyleType.addEventListener("change", function () {
            storedEdgeStyleType = edgeStyleType.value;
            initArrowPreview(storedEdgeStyleArrowColor, storedEdgeStyleSegmentColor, storedEdgeStyleThickness, storedEdgeStyleType);
        })

        edgeStyleArrowColor.addEventListener("change", function () {
            storedEdgeStyleArrowColor = edgeStyleArrowColor.value;
            initArrowPreview(storedEdgeStyleArrowColor, storedEdgeStyleSegmentColor, storedEdgeStyleThickness, storedEdgeStyleType);
        });

        edgeStyleSegmentColor.addEventListener("change", function () {
            storedEdgeStyleSegmentColor = edgeStyleSegmentColor.value;
            initArrowPreview(storedEdgeStyleArrowColor, storedEdgeStyleSegmentColor, storedEdgeStyleThickness, storedEdgeStyleType);
        })

        edgeStyleThickness.addEventListener("change", function () {
            storedEdgeStyleThickness = parseInt(edgeStyleThickness.value, 10);
            initArrowPreview(storedEdgeStyleArrowColor, storedEdgeStyleSegmentColor, storedEdgeStyleThickness, storedEdgeStyleType);
        });

        // Effacer le fichier sélectionné lorsqu'on clique sur le bouton de suppression
        removeIconButton.addEventListener("click", function () {
            nodeIconInput.value = ""; // Efface le fichier sélectionné
            removeIconButton.style.display = "none";  //masquer le bouton.
            // Rendre l'image actualIconPreview en niveaux de gris
            actualIconPreview.style.filter = "grayscale(100%)"; // Applique un filtre gris sur l'image
            actualIconPreview.style.opacity = "0.5"; // Réduit l'opacité pour un effet plus visuel (optionnel)
            nodeIconInput.value = "";
            newIconPreviewContainer.style.display = "none";
            nodeIconDescription.textContent = "Importer une icône"
        });

        // Effacer le fichier sélectionné lorsqu'on clique sur le bouton de suppression
        removeImageButton.addEventListener("click", function () {
            nodeImageInput.value = ""; // Efface le fichier sélectionné
            removeImageButton.style.display = "none";  //masquer le bouton.
            // Rendre l'image actualIconPreview en niveaux de gris
            actualImagePreview.style.filter = "grayscale(100%)"; // Applique un filtre gris sur l'image
            actualImagePreview.style.opacity = "0.5"; // Réduit l'opacité pour un effet plus visuel (optionnel)
            nodeImageInput.value = "";
            newImagePreviewContainer.style.display = "none";
            nodeImageDescription.textContent = "Importer une icône"
        });

        resetTabButton.addEventListener("click", () => {
            if (nodePanel.style.display !== "none" || edgePanel.style.display !== "none") {
                selectTab("modifier");
                actualIconPreview.style.filter = "none";
                actualIconPreview.style.opacity = "1";
            }
            else {
                if (managementPanel.style.display === "flex") {
                    selectTab("gestion");
                } else {
                    selectTab("ajouter");
                }
            }
        });

        nodeIconInput.addEventListener("change", () => {
            // Rendre l'image actualIconPreview sans niveaux de gris
            actualIconPreview.style.filter = "none";
            actualIconPreview.style.opacity = "1";
            var newIcon = nodeIconInput.files[0];
            if (newIcon) {
                newIconPreview.src = URL.createObjectURL(newIcon);
                newIconPreviewContainer.style.display = "flex";
                nodeIconDescription.textContent = "Changer d'icône";
                removeIconButton.style.display = "block";
            } else {
                nodeIconDescription.textContent = "Importer une icône";
                removeIconButton.style.display = "none";
            }
        })

        nodeImageInput.addEventListener("change", () => {
            // Rendre l'image actualIconPreview sans niveaux de gris
            actualImagePreview.style.filter = "none";
            actualImagePreview.style.opacity = "1";
            var newImage = nodeImageInput.files[0];
            if (newImage) {
                newImagePreview.src = URL.createObjectURL(newImage);
                newImagePreviewContainer.style.display = "flex";
                nodeImageDescription.textContent = "Changer d'image";
                removeImageButton.style.display = "block";
            } else {
                nodeImageDescription.textContent = "Importer une image";
                removeImageButton.style.display = "none";
            }
        })

        //Détection de source

        document.getElementById("nodeLink").addEventListener('blur', function () {
            if (nodeLink.value !== '') {
                const textContent = updateBestSourceEPIX(document.getElementById("nodeLink").value, "nodeSource-select");
                document.getElementById('nodeDetectedSource').style.display = 'flex';
            }
            });

        document.getElementById("edgeLink").addEventListener('blur', function () {
            if (edgeLink.value !== '') {
                const textContent = updateBestSourceEPIX(document.getElementById("edgeLink").value, "edgeSource-select");
                document.getElementById('edgeDetectedSource').style.display = 'flex';
            }
            });

        // Définition de l'objet `edgeBin` contenant des éléments
        let edgeBin = {};
        let nodeBin = {};

        /**
         * Fonction pour rafraîchir la liste des éléments supprimés.
         */
        function refreshDeletedItems() {
            const select = document.getElementById('deletedItemSelect');
            select.innerHTML = ''; // Réinitialisation du contenu
            const option = document.createElement('option');
            option.value = `sansObjet`;
            option.textContent = `Sélectionnez un item à restaurer`;
            select.appendChild(option);

            // Parcourt `edgeBin` et ajoute les éléments supprimés au select
            Object.entries(edgeBin).forEach(([id, edgeObject]) => {
                if (edgeObject.sourceNodeName != undefined &&
                    edgeObject.targetNodeName != undefined &&
                    Object.hasOwn(nodes, edgeObject.sourceNode) &&
                    Object.hasOwn(nodes, edgeObject.targetNode)
                ) {
                    const option = document.createElement('option');
                    option.value = `${id}_edge`;
                    option.textContent = `${edgeObject.sourceNodeName} ${edgeCategories[edgeObject.category].name} ${edgeObject.targetNodeName} (ID: ${id})`;
                    select.appendChild(option);
                }
            });
            Object.entries(nodeBin).forEach(([id, nodeObject]) => {
                const option = document.createElement('option');
                option.value = `${id}_node`;
                option.textContent = `${nodeObject.name} (ID: ${id})`;
                select.appendChild(option);
            })

            // Désactive le bouton si aucun élément supprimé n'est disponible
            document.getElementById('restoreItemButton').disabled = (select.options.length === 0);
        }

        /**
         * Gestion de la restauration d'un élément.
         */
        document.getElementById('restoreItemButton').addEventListener('click', () => {
            select = document.getElementById('deletedItemSelect');
            selectedId = select.value;

            // Utilisation d'une expression régulière pour vérifier le type et l'ID
            const match = selectedId.match(/^(\d+)_(edge|node)$/);
            if (match) {
                const id = match[1];   // Numéro d'ID (par exemple "1")
                const type = match[2]; // "edge" ou "node"

                if (type === "edge") {
                    restoreEdge(edgeBin[id]);
                } else if (type === "node") {
                    restoreNode(nodeBin[id]);
                }
            }
            else {
                alert("Veuillez sélectionner un élément à restaurer !");
            }
        });

        </script>
        <!--EVENEMENTS GESTION-->
<script>

// Ajout d'un gestionnaire d'événement pour le bouton "Appliquer les modifications"
document.getElementById("applyChangesButton").addEventListener("click", () => {
    // Récupérer les états des cases à cocher
    disabledLink = !document.getElementById("disableLinkCheckbox").checked;
    disabledImage = !document.getElementById("disableImageCheckbox").checked;

// Parcourir les éléments de edgeCategoriesManagement puis de nodeCategoriesManagement
    const edgeCategoriesElements = document.querySelectorAll('#edgeCategoriesManagement input[type="checkbox"]');
    const nodeCategoriesElements = document.querySelectorAll('#nodeCategoriesManagement input[type="checkbox"]');

    edgeCategoriesElements.forEach((checkbox) => {
        // Ajouter chaque checkbox comme une paire clé-valeur dans edgecategoriesstatus
        edgeCategoriesStatus[checkbox.value] = checkbox.checked ? 1 : 0;
    });

    nodeCategoriesElements.forEach((checkbox) => {
        // Ajouter chaque checkbox comme une paire clé-valeur dans nodecategoriesstatus
        nodeCategoriesStatus[checkbox.value] = checkbox.checked ? 1 : 0;
    });

// Créer une requête AJAX avec XMLHttpRequest
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/EPIX_update_project.php", true); // Spécifiez le fichier PHP cible

// Définissez une fonction de rappel pour la réponse
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                alert("Les modifications ont été appliquées avec succès !");
                load_categories_json();
            } else {
                console.error("Erreur lors de la modification :", xhr.status, xhr.statusText);
                alert("Une erreur est survenue lors de la modification.");
            }
        }
    };

// Préparation des données à envoyer
    const data = new FormData();
    data.append('action', 'modifyCategoriesStatus');
    data.append('nodeCategoriesStatus', JSON.stringify(nodeCategoriesStatus)); // Objet JSON pour nodecategoriesstatus
    data.append('edgeCategoriesStatus', JSON.stringify(edgeCategoriesStatus)); // Objet JSON pour edgecategoriesstatus
    data.append('project_id', `<?php echo $_SESSION["project_id"]; ?>`); // Inclusion de project_id depuis PHP

// Envoyer les données JSON au serveur
    xhr.send(data);
});

/**
 * Fonction pour enrouler/dérouler la liste liée à l'ID donné
 * @param {string} id - ID de la <ul> à enrouler/dérouler
 */
function toggleCategory(id, btn)
{
    const element = document.getElementById(id);

    if (element.style.display === "none") {
        // Si la liste est cachée, on l'affiche et met à jour le bouton (remet "-")
        setTimeout(() => {element.style.display = "block";}, 100);

        btn.textContent = "-"; // Bouton pour réduire
    } else {
        // Sinon, on cache la liste et on met le bouton à "+"
        element.style.display = "none";
        btn.textContent = "+"; // Bouton pour agrandir
    }

    if (id === "nodeContent")
    {
        document.getElementById("edgeContent").style.display = "none";
        document.getElementById("edgePlus").textContent = "+";
    }
    else if (id === "edgeContent")
    {
        document.getElementById("nodeContent").style.display = "none";
        document.getElementById("nodePlus").textContent = "+";
    }

}

    </script>

        <!--Export des données-->
<script>

// AVEC CONVERSION WEBP
async function convertImageToBase64(url) {
    try {
        if (!url) return null; // Si aucune URL n'est donnée
        const response = await fetch(url);
        if (!response.ok) return null; // Vérifiez que l'image est accessible
        const blob = await response.blob();

        // Vérifiez si le blob est une image .webp
        if (blob.type === "image/webp") {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => {
                    // Charger l'image dans un élément <img>
                    const img = new Image();
                    img.onload = () => {
                        // Convertir l'image webp au format PNG via <canvas>
                        const canvas = document.createElement("canvas");
                        canvas.width = img.width;
                        canvas.height = img.height;
                        const ctx = canvas.getContext("2d");
                        ctx.drawImage(img, 0, 0);
                        // Exporter l'image du canvas au format PNG (par défaut)
                        resolve(canvas.toDataURL("image/png"));
                    };
                    img.onerror = reject; // En cas d'erreur de chargement
                    img.src = reader.result; // Charger l'image
                };
                reader.onerror = reject;
                reader.readAsDataURL(blob); // Lire la source de l'image
            });
        }

        // Si le fichier n'est pas .webp, utiliser la logique classique
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result); // Convertir l'image en Base64
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });
    } catch (error) {
        console.error(`Erreur lors de la conversion de l'image : ${url}`, error);
        return null;
    }
}

// Écouteur d'événement sur le bouton
document.getElementById("export-to-word").addEventListener('click', async () => {
    // Créer le contenu HTML pour le fichier Word
    let content = `
        <h1 style="text-align: center;">Données des Sommets</h1>
        <table border="1" style="border-collapse: collapse; width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Catégorie</th>
                    <th>Nom</th>
                    <th>______Commentaires______</th>
                    <th>Source</th>
                    <th>Icône</th>
                    <th>Image</th>
                    <th>Coordonnées</th>
                </tr>
            </thead>
            <tbody>
    `;

    // Remplir le tableau des nœuds
    for (const node of Object.values(nodes)) {
        const base64Image = await convertImageToBase64(node.image || null); // Convertir l'image en Base64
        const base64Icon = await convertImageToBase64(node.icon || null)
        const cat = nodeCategories.hasOwnProperty(node.category) ? nodeCategories[node.category].name : 'Autre';
        const classe = (nodeCategories.hasOwnProperty(node.category) && nodeClasses.hasOwnProperty(nodeCategories[node.category].class)) ? nodeClasses[nodeCategories[node.category].class] : 'Autres';
        content += `
            <tr>
                <td>${classe}</td>
                <td>${cat}</td>
                <td>${node.name}</td>
                <td>${node.comments}</td>
                <td>${node.source}</td>
                <td>${base64Icon ? `<img src="${base64Icon}" alt="icon" style="height:50px; width:50px;">` : 'Image indisponible'}</td>
                <td>${base64Image ? `<img src="${base64Image}" alt="image" style="height:50px; width:50px;">` : 'Image indisponible'}</td>
                <td>${node.geoCoord}</td>
            </tr>
        `;
    }

    content += `
            </tbody>
        </table>
    `;

    // Ajouter la section pour les données des arêtes
    content += `
        <h1 style="text-align: center;">Données des Arcs</h1>
        <table border="1" style="border-collapse: collapse; width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Sommet Source</th>
                    <th>Catégorie</th>
                    <th>Sommet Cible</th>
                    <th>Commentaires</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
    `;

    // Remplir le tableau des arêtes
    for (const edge of Object.values(edges)) {
        const cat = edgeCategories.hasOwnProperty(edge.category) ? edgeCategories[edge.category].name : 'Autre';
        const classe = (edgeCategories.hasOwnProperty(edge.category) && edgeClasses.hasOwnProperty(edgeCategories[edge.category].class)) ? edgeClasses[edgeCategories[edge.category].class] : 'Autres';

        content += `
            <tr>
                <td>${classe}</td>
                <td>${nodes[edge.sourceNode].name}</td>
                <td>${cat}</td>
                <td>${nodes[edge.targetNode].name}</td>
                <td>${edge.comments}</td>
                <td>${edge.source}</td>
            </tr>
        `;
    }

    content += `
            </tbody>
        </table>
    `;

    // Créer un Blob et générer un lien de téléchargement
    const blob = new Blob(["\ufeff" + content], {
        type: "application/msword",
    });

    const link = document.createElement("a");
    link.download = document.getElementById("projectTitle").textContent+".doc"; // Nom du fichier généré
    link.href = URL.createObjectURL(blob);
    link.click();
});

    document.getElementById('export-to-excel').addEventListener('click', () => {
      let csvContent = "Entités:\n";
csvContent += "Classe;Catégorie;Nom;Commentaires;Source;Coordonnées\n"; // En-tête

Object.values(nodes).forEach(node => {

    const categorie = nodeCategories.hasOwnProperty(node.category) ? nodeCategories[node.category].name : 'Autre';
    const classe = (nodeCategories.hasOwnProperty(node.category) && nodeClasses.hasOwnProperty(nodeCategories[node.category].class)) ? nodeClasses[nodeCategories[node.category].class] : 'Autres';
    const nom = node.name || '';
  const commentaires = (node.comments || '').replace(/[\r\n]+/g, " ") || '';
  const source = (node.source || '').replace(/[\r\n]+/g, " ") || '';
  const coord = (node.geoCoord || '').replace(/[\r\n]+/g, " ") || '';

  csvContent += `"${classe}";"${categorie}";"${nom}";"${commentaires}";"${source}";"${coord}"\n`;
});

csvContent += "\n Relations:\n";
csvContent += "Classe;Sommet source;Catégorie;Sommet cible;Commentaires;Source\n"; // En-tête

// Parcourir les edges
Object.values(edges).forEach(edge => {
    const categorie = edgeCategories.hasOwnProperty(edge.category) ? edgeCategories[edge.category].name : 'Autre';
    const classe = (edgeCategories.hasOwnProperty(edge.category) && edgeClasses.hasOwnProperty(edgeCategories[edge.category].class)) ? edgeClasses[edgeCategories[edge.category].class] : 'Autres';
const sourceNodeName = nodes[edge.sourceNode]?.name || '';  // Vérifie si le nœud source existe
const cibleNodeName = nodes[edge.targetNode]?.name || '';  // Vérifie si le nœud cible existe
const commentaires = (edge.comments || '').replace(/[\r\n]+/g, " ") || '';
const source = (edge.source || '').replace(/[\r\n]+/g, " ") || '';

// Ajout de la ligne formatée
csvContent += `"${classe}";"${sourceNodeName}";"${categorie}";"${cibleNodeName}";"${commentaires}";"${source}"\n`;
});

const BOM = "\uFEFF"; // Ajout du BOM UTF-8
const blob = new Blob([BOM + csvContent], { type: "text/csv;charset=utf-8;" });

        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", document.getElementById("projectTitle").textContent+".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });


    document.getElementById("export-to-pdf").addEventListener("click", async () => {
        // Générer la structure HTML pour l'impression
        let content = `
        <div style="font-family: Arial, sans-serif;">
            <h1 style="text-align: center;">Données des Sommets</h1>
            <table border="1" style="border-collapse: collapse; width: 100%; text-align: left;">
                <thead>
                    <tr>
                        <th>Classe</th>
                        <th>Catégorie</th>
                        <th>Nom</th>
                        <th>Commentaires</th>
                        <th>Source</th>
                        <th>Icône</th>
                        <th>Image</th>
                        <th>Coordonnées</th>
                    </tr>
                </thead>
                <tbody>
    `;

        for (const node of Object.values(nodes)) {
            const base64Icon = await convertImageToBase64(node.icon);
            const base64Image = await convertImageToBase64(node.image);
            const categorie = nodeCategories.hasOwnProperty(node.category) ? nodeCategories[node.category].name : 'Autre';
            const classe = (nodeCategories.hasOwnProperty(node.category) && nodeClasses.hasOwnProperty(nodeCategories[node.category].class)) ? nodeClasses[nodeCategories[node.category].class] : 'Autres';

            content += `
            <tr>
                <td>${classe}</td>
                <td>${categorie}</td>
                <td>${node.name}</td>
                <td>${node.comments}</td>
                <td>${node.source}</td>
                <td>${base64Icon ? `<img src="${base64Icon}" alt="icon" style="height:50px; width:50px;">` : 'Image indisponible'}</td>
                <td>${base64Image ? `<img src="${base64Image}" alt="image" style="height:50px; width:50px;">` : 'Image indisponible'}</td>
                <td>${node.geoCoord}</td>
            </tr>
        `;
        }

        content += `
                </tbody>
            </table>
            <h1 style="text-align: center; margin-top: 50px;">Données des Arêtes</h1>
            <table border="1" style="border-collapse: collapse; width: 100%; text-align: left; margin-top: 20px;">
                <thead>
                    <tr>
                        <th>Classe</th>
                        <th>Sommet Source</th>
                        <th>Catégorie</th>
                        <th>Sommet Cible</th>
                        <th>Commentaires</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
    `;

        for (const edge of Object.values(edges)) {
            const categorie = edgeCategories.hasOwnProperty(edge.category) ? edgeCategories[edge.category].name : 'Autre';
            const classe = (edgeCategories.hasOwnProperty(edge.category) && edgeClasses.hasOwnProperty(edgeCategories[edge.category].class)) ? edgeClasses[edgeCategories[edge.category].class] : 'Autres';

            content += `
            <tr>
                <td>${classe}</td>
                <td>${nodes[edge.sourceNode].name}</td>
                <td>${categorie}</td>
                <td>${nodes[edge.targetNode].name}</td>
                <td>${edge.comments}</td>
                <td>${edge.source}</td>
            </tr>
        `;
        }

        content += `
                </tbody>
            </table>
        </div>
    `;

        // Ouvrir une nouvelle fenêtre pour l'impression PDF
        const printWindow = window.open("", "PRINT", "height=600,width=800");

        // Ajouter le contenu dans la fenêtre
        printWindow.document.write(`
        <html>
        <head>
            <title>Nodes et Edges</title>
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th, td {
                    border: 1px solid black;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                }
                img {
                    display: block;
                    margin: auto;
                    max-height: 50px;
                    max-width: 50px;
                }
                h1 {
                    text-align: center;
                }
            </style>
        </head>
        <body>
            ${content}
        </body>
        </html>
    `);

        // Lancer l'impression et fermer la fenêtre
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    });

</script>
        <!-- FONCTIONS ANNEXES-->

        <script>

            function updateOpeningsEPIX(sourceId) {
                // Vérifions si un ID valide a été envoyé
                if (!sourceId) {
                    console.error("Aucun identifiant source fourni.");
                    return;
                }

                // Initialisation de l'objet XMLHttpRequest pour une requête AJAX
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "Includes/PHP/glorix_bdd.php", true);
                data = new FormData();
                data.append('key', 'update_openingsEPIX');
                data.append('source_id', sourceId);
                // Envoi des données au script PHP avec l'ID source
                xhr.send();
            }



            // Met à jour dynamiquement la meilleure source
            function updateBestSourceEPIX(linkUrl, replacedElement) {
                if (linkUrl.trim() === "") return; // Si le lien est vide, on stoppe

                const xhr = new XMLHttpRequest();
                xhr.open("POST", "Includes/PHP/glorix_bdd.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.responseText) {
                        try {
                            // Parse la réponse JSON
                            const response = JSON.parse(xhr.responseText);

                            // Extraire les IDs pour "substringMatches"
                            const ids = response.substringMatches;
                            const bestMatch = response.bestMatch;

                            // Créer une requête pour récupérer le code HTML du select
                            const xhrPhp = new XMLHttpRequest();
                            xhrPhp.open("POST", "Includes/PHP/glorix_bdd.php", true); // Adresse à un script PHP dédié

                            xhrPhp.onload = function () {
                                if (xhrPhp.status === 200) {
                                    // Injecter le select généré en PHP dans le DOM
                                    document.getElementById(replacedElement).innerHTML = xhrPhp.responseText;
                                } else {
                                    console.error("Erreur lors de la génération du select : ", xhrPhp.responseText);
                                }
                            };
                            let data = new FormData();
                            data.append('key', 'generate_source_select');
                            data.append('ids', encodeURIComponent(JSON.stringify(ids)));
                            data.append('best_match', bestMatch);
                            xhrPhp.send(data);
                        } catch (e) {
                            console.error("Erreur de traitement de réponse JSON : ", e);
                        }
                    } else {
                        console.error("Erreur : ", xhr.responseText);
                    }
                };

                const data = `key=find_best_source&link_url=${encodeURIComponent(linkUrl)}}`;
                xhr.send(data);
            }

            function getMinCoordinates() {
                    let minX = Infinity;
                    let minY = Infinity;
                    let maxX = -Infinity;
                    let maxY = -Infinity;

                    // Calculer les coordonnées minimales et maximales
                    for (let id in nodes) {
                        const node = nodes[id];
                        if (node.x < minX) {
                            minX = node.x;
                        }
                        if (node.y < minY) {
                            minY = node.y;
                        }
                        if (node.x > maxX) {
                            maxX = node.x;
                        }
                        if (node.y > maxY) {
                            maxY = node.y;
                        }
                    }

                    // Calculer la largeur et la hauteur des nœuds
                    const nodesWidth = maxX - minX;
                    const nodesHeight = maxY - minY;

                    if (minX != +Infinity && nodesWidth != 0 && nodesHeight != 0){

                      // Calculer l'échelle pour que tous les nœuds soient visibles dans la zone définie par width et height
                      const scaleX = canvas.width / nodesWidth;
                      const scaleY = canvas.height / nodesHeight;
                      scale = Math.min(scaleX, scaleY);

                      // Calculer l'échelle pour que tous les nœuds soient visibles dans la zone définie par width et height
                      offsetX = canvas.width / 2 - (minX + maxX) / 2 * scale;
                      offsetY = canvas.height / 2 - (minY + maxY) / 2 * scale;
                  }
                }
        </script>


<!--INITIALISATION-->
<script>
        // Initialisation
        resizeCanvas();
        load_graph_json();
        getMinCoordinates();
        load_categories_json();
        refreshDeletedItems();
        realCoordinates();
        selectTab("ajouter");

    </script>
