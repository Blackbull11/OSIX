// Code JS pour la modification des listes d'outil
// Test modification
// Fermer le popup d'outils en cliquant en dehors
window.addEventListener("click", (event) => {
    if (event.target === document.getElementById("popup-tool")) {
        if (window.confirm('Attention, vous allez perdre toutes vos modifications en cours !')) {
            document.getElementById("popup-tool").style.display = "none";
            const to_defilter_elts = document.getElementsByClassName("to_defilter");
            Array.from(to_defilter_elts).forEach((element) => {
                element.style.filter = "revert-layer";
            });
        }
    }
});

// Fermer le popup d'outils favoris en cliquant en dehors
window.addEventListener("click", (event) => {
    if (event.target === document.getElementById("popup-fav-tool")) {
        document.getElementById("popup-fav-tool").style.display = "none";
        const to_defilter_elts = document.getElementsByClassName("to_defilter");
        Array.from(to_defilter_elts).forEach((element) => {
            element.style.filter = "revert-layer";
        });
    }
});

function Add_Tool(category_id, user_id, user_admin){
    xhreq = new XMLHttpRequest();
    xhreq.onreadystatechange = function () {
        if (xhreq.readyState == 4 && xhreq.status == 200) {
            let added_tags = [];
            document.getElementById("popup-tool").innerHTML = this.responseText;
            document.querySelector('#categorySelect [value="'+ category_id +'"]').selected = true;
            document.getElementById("popup-tool").style.display = "flex";
        }
    }
    xhreq.open('POST', 'Includes/PHP/add_tool_popup.php', true);
    data = new FormData();
    data.append('user_id', user_id);
    data.append('user_admin', user_admin)
    xhreq.send(data);
}

function Add_Category(user_id, user_admin){
    xhreq = new XMLHttpRequest();
    xhreq.onreadystatechange = function () {
        if (xhreq.readyState == 4 && xhreq.status == 200) {
            document.getElementById("popup-tool").innerHTML = this.responseText;
            document.getElementById("popup-tool").style.display = "flex";
        }
    }
    xhreq.open('POST', 'Includes/PHP/add_tool_popup.php', true);
    data = new FormData();
    data.append('add_category', true)
    data.append('user_id', user_id);
    data.append('user_admin', user_admin)
    xhreq.send(data);
}

function Tool_Added($tool_name, $tool_url, $tool_doc, $category_id, $sharing, $tags, $user_id, $user_is_admin, $edited_tool) {
    if ($user_id == 0) {
        window.alert("Veuillez vous connecter pour effectuer cette action.");
        document.getElementById("popup-tool").style.display = "none";
        return;
    }
    console.log(document.getElementById('categorySelect').value)
    if ($category_id == 0) {
        document.getElementById("categorySelect").style.border = "1px solid red";
        return;
    }
    if ($tool_doc == '') {
        document.getElementById("tool-doc").style.border = "1px solid red";
        return;
    }
    console.log('Valide ? ' + validateInputs())
    if (!validateInputs()) {
        alert("Certains champs contiennent des caractères non autorisés ! Veuillez corriger votre saisie.");
        return; // Bloquer la fonction si la validation échoue
    }
    document.getElementById("popup-tool").style.display = "none";
    let safeToolUrl = encodeURIComponent($tool_url);
    let safeToolDoc = encodeURIComponent($tool_doc);
    let safeToolName = encodeURIComponent($tool_name);
    // vider le formulaire
    var tools_sublist = document.getElementById('tool-ul-of-' + $category_id);

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200 && xhr.response == "OK") {
            console.log(xhr.response);
            console.log('OK');
            if($edited_tool == 0) {
                Success_Message("L'outil a été ajouté avec succés !");
                tools_sublist.innerHTML += `
                   <li>
                       <a target="_blank" href="${safeToolUrl}" title="${safeToolDoc}">${safeToolName}</a>
                       <button id="edit-tool-button" class="edit-icon" onclick="Bad_Edit()">
                           <img src="Includes/Images/Bouton Modifier.png" />
                       </button>
                       <button id="delete-tool-button" class="edit-icon" onclick="Bad_Edit()">
                           <img src="Includes/Images/Bouton Supprimer.png" />
                       </button>
                   </li>
               `;
            }
            else {
                Success_Message("L'outil a été modifié avec succés");
                document.getElementById("tool"+ String($edited_tool)).innerHTML = `
                    <a href=` + $tool_url + ` title=` + $tool_doc + `>`+ $tool_name+`</a>
                        <button id="edit-tool-button" class="edit-icon" onclick="Edit_Tool(` + $edited_tool + `, 0, ` + $category_id + `, '` + String($tool_name) + `', '` + $tool_url + `', '` + $tool_doc + `', \'` + JSON.stringify($tags) + `\', ` + $user_id + `, ` + $user_is_admin + `)">
                            <img src="Includes/Images/Bouton Modifier.png">
                        </button>
                        <button id="delete-tool-button" class="edit-icon" onclick="Edit_Tool(` + $edited_tool + `, 1, ` + $category_id + `, '` + String($tool_name) + `', '` + $tool_url + `', '` + $tool_doc + `', \'` + JSON.stringify($tags) + `\', ` + $user_id + `, ` + $user_is_admin + `)">
                            <img src="Includes/Images/Bouton%20Supprimer.png">
                        </button>
            `;
            }
        }
        else if (xhr.readyState == 4){
            Failure_Message('La requête a échoué :(')
        }
    }
    xhr.open('POST', 'Includes/PHP/add_tool_bdd.php', true);
    data = new FormData();
    data.append('user_id', $user_id);
    data.append('edited_tool', $edited_tool);
    data.append('tool_name', $tool_name);
    data.append('tool_url', $tool_url);
    data.append('tool_doc', $tool_doc);
    data.append('category_id', $category_id);
    data.append('sharing', $sharing);
    data.append('tags_list', JSON.stringify($tags));
    console.log('Requête prête a être envoyée');
    xhr.send(data)

    // Récupérer la div avec l'ID "popup-tool"
    var popupToolDiv = document.getElementById("popup-tool");
    // Récupérer tous les champs input à l'intérieur de la div
    var inputs = popupToolDiv.querySelectorAll("input");
    // Parcourir chaque input et vider son contenu
    Array.from(inputs).forEach(function (input) {
        input.value = ""; // Met la valeur du champ à une chaîne vide
    });
}

function Tool_Cat_Added(is_admin, cat_name, parent_id, user_id){
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            window.location.href = "index.php";
            console.log("index.php")
        }
    }
    xhr.open('POST', 'Includes/PHP/add_tool_bdd.php', true);
    data = new FormData();
    data.append('key', 'add_tool_category')
    data.append('is_admin', is_admin)
    data.append('tool_parent', parent_id);
    data.append('cat_name', cat_name)
    if (!is_admin){
        data.append('user_id', user_id);
    }
    xhr.send(data);
}

function Edit_Tool($id, $flag, $tool_cat, $tool_name, $tool_url, $tool_doc, $tags, $user_id, $is_admin) {
    if ($flag == 1) {
        if ($user_id === 0) {
            window.alert("Veuillez vous connecter pour effectuer cette action.");
            document.getElementById("popup-tool").style.display = "none";
            return;
        }
        if (window.confirm("Confirmez-vous la suppression de l'outil " + $tool_name + " de votre arborescence ? (L'outil sera toujours existant et disponible dans la page de gestion BOX.)")) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('tool' + $id).style.display = 'none';
                    Success_Message("L'outil a été supprimé de votre arborescence avec succés")
                }
            }
            xhr.open('POST', 'Includes/PHP/erase_tool.php', true);
            data = new FormData();
            data.append('user_id', $user_id);
            data.append('tool_cat', $tool_cat);
            data.append('tool_id', $id);
            xhr.send(data);
        }
    } else {
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
            console.log('flop');
            added_tags = [];
        }
        let popupToolDiv2 = document.getElementById("popup-tool");

        //Interdiction à la modification pour user0 (i.e. si non connecté)
        if ($user_id === 0) {
            // Gestion des champs (inputs, textarea, select)
            let inputs = popupToolDiv2.querySelectorAll("input, textarea, select");
            inputs.forEach(input => {
                let previousValue = input.value;

                input.addEventListener("focus", () => {
                    previousValue = input.value; // Sauvegarder la valeur avant modification
                });

                input.addEventListener("input", () => {
                    window.alert('Veuillez vous connecter pour réaliser cette action.');
                    input.value = previousValue; // Revenir à la dernière valeur
                    input.blur(); // Enlever le focus (facultatif)
                });
            });

            // Gestion des boutons
            let buttons = popupToolDiv2.querySelectorAll(".button");
            buttons.forEach((button) => {
                if (button.id !== "scroll-button-right" && button.id !== "scroll-button-left") {
                    button.addEventListener("click", (event) => {
                        console.log('Témoin')
                        event.preventDefault();
                        event.stopPropagation();
                        window.alert("Veuillez vous connecter pour réaliser cette action.");
                    });
                }
            });
        }

        xhreq = new XMLHttpRequest();
        xhreq.onreadystatechange = function () {
            if (xhreq.readyState == 4 && xhreq.status == 200) {
                document.getElementById("popup-tool").innerHTML = this.responseText;
                document.getElementById("popup-tool").style.display = "flex";
            }
        }
        xhreq.open('POST', 'Includes/PHP/add_tool_popup.php', true);
        data = new FormData();
        data.append('edited_tool', $id);
        data.append('tool_name', $tool_name);
        data.append('tool_url', $tool_url);
        data.append('tool_doc', $tool_doc);
        data.append('tool_cat', $tool_cat);
        data.append('tags_list', JSON.stringify($tags));
        data.append('user_id', $user_id);
        data.append('user_admin', $is_admin);
        xhreq.send(data);

    }
}

function Bad_Edit(){
    window.alert('Veuillez recommencer l\'opération après le rechargement de la page.');
    window.location.reload();
}

function Alert_Tool(tool_id, user_id) {
    if (!window.confirm("Voulez-vous signaler cet outil ? Si oui, n'hésitez pas à envoyer un message aux admins pour expliquer quel est le sujet de votre signalement.")){
        return;
    }
    xhr = new XMLHttpRequest();
    xhr.open('POST', 'Includes/PHP/alert_tool.php', true);
    data = new FormData();
    data.append('tool_id', tool_id);
    data.append('user_id', user_id);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            window.alert(this.responseText);
        }
    }
    xhr.send(data);
}

// Fonction pour ajouter un tag lié aux outils
function addToolTag(tagName) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/manage_tool_tags.php", true);
    const data = new FormData();
    data.append("action", "add");
    data.append("tag_name", tagName);
    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert(xhr.responseText); // Affiche un message de succès ou d'erreur
            refreshToolTags(); // Met à jour la liste des tags
        }
    };
    xhr.send(data);
}

// Fonction pour modifier un tag existant lié aux outils
function editToolTag(tagId, newTagName) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/manage_tool_tags.php", true);
    const data = new FormData();
    data.append("action", "edit");
    data.append("tag_id", tagId);
    data.append("new_tag_name", newTagName);
    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert(xhr.responseText); // Affiche un message de succès ou d'erreur
            refreshToolTags(); // Met à jour la liste des tags
        }
    };
    xhr.send(data);
}

// Fonction pour supprimer un tag lié aux outils
function deleteToolTag(tagId) {
    if (!confirm("Êtes-vous sûr de vouloir supprimer ce tag ?")) return;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/manage_tool_tags.php", true);
    const data = new FormData();
    data.append("action", "delete");
    data.append("tag_id", tagId);
    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert(xhr.responseText); // Affiche un message de succès ou d'erreur
            refreshToolTags(); // Met à jour la liste des tags
        }
    };
    xhr.send(data);
}

// Fonction pour rafraîchir la liste des tags liés aux outils
function refreshToolTags() {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/manage_tool_tags.php", true);
    const data = new FormData();
    data.append("action", "fetch");
    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById("tagsTable").innerHTML = xhr.responseText;
        }
    };
    xhr.send(data);
}



