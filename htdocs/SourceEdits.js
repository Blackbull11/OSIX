// Code JS pour la modification des listes de sources

// Fermer le popup de source en cliquant en dehors
window.addEventListener("click", (event) => {
    if (event.target === document.getElementById("popup-source")) {
        if (window.confirm('Attention, vous allez perdre toutes vos modifications en cours !')) {
            document.getElementById("popup-source").style.display = "none";
            const to_defilter_elts = document.getElementsByClassName("to_defilter");
            Array.from(to_defilter_elts).forEach((element) => {
                element.style.filter = "revert-layer";
            });
        }
    }
});
// Fermer le popup de sources génériques en cliquant en dehors
window.addEventListener("click", (event) => {
    if (event.target === document.getElementById("popup-fav-link")) {
        document.getElementById("popup-fav-link").style.display = "none";
        const to_defilter_elts = document.getElementsByClassName("to_defilter");
        Array.from(to_defilter_elts).forEach((element) => {
            element.style.filter = "revert-layer";
        });
    }
});

function Add_Source(user_id, user_admin) {
    xhreq = new XMLHttpRequest();
    xhreq.onreadystatechange = function () {
        if (xhreq.readyState == 4 && xhreq.status == 200) {
            document.getElementById("popup-source").innerHTML = this.responseText;
            document.getElementById("popup-source").style.display = "flex";
        }
    }
    xhreq.open('POST', 'Includes/PHP/add_source_popup.php', true);
    data = new FormData();
    data.append('user_id', user_id);
    data.append('user_admin', user_admin);
    data.append('key', 'add_source');
    xhreq.send(data);
}

function Add_Link(list_id, user_id, user_admin) {
    xhreq = new XMLHttpRequest();
    xhreq.onreadystatechange = function () {
        if (xhreq.readyState == 4 && xhreq.status == 200) {
            document.getElementById("popup-source").innerHTML = this.responseText;
            document.querySelector('#ListSelect [value="' + list_id + '"]').selected = true;
            document.getElementById("popup-source").style.display = "flex";
        }
    }
    xhreq.open('POST', 'Includes/PHP/add_source_popup.php', true);
    data = new FormData();
    data.append('key', 'add_link');
    data.append('user_id', user_id);
    data.append('user_admin', user_admin);
    xhreq.send(data);
}

function Add_List(user_id) {
    xhreq = new XMLHttpRequest();
    xhreq.onreadystatechange = function () {
        if (xhreq.readyState == 4 && xhreq.status == 200) {
            document.getElementById("popup-source").innerHTML = this.responseText;
            document.getElementById("popup-source").style.display = "flex";
        }
    }
    xhreq.open('POST', 'Includes/PHP/add_source_popup.php', true);
    data = new FormData();
    data.append('add_list', true)
    xhreq.send(data);
}

function Source_List_Added(list_name, user_id, parent_id, tags) {
    if (!list_name || list_name == "") {
        return;
    }
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            window.location.href = "index.php";
            console.log("index.php")
        }
    }
    xhr.open('POST', 'Includes/PHP/glorix_bdd.php', true);
    data = new FormData();
    data.append('key', 'add_source_list')
    data.append('father', parent_id);
    data.append('list_name', list_name)
    data.append('tags_list', JSON.stringify(tags));
    data.append('user_id', user_id);
    xhr.send(data);
}

function Source_Added(add_source_form, userId) {
    // Récupérer les données du formulaire
    const formData = new FormData(add_source_form);

    // Vérifications des champs obligatoires
    if (!formData.get("source_name").trim()) {
        alert("Veuillez fournir un nom de source valide.");
        add_source_form.querySelector("#source-name").classList.add("red");
        return;
    }
    if (!formData.get("source_url").trim()) {
        alert("Veuillez fournir un URL valide.");
        add_source_form.querySelector("#source-url").classList.add("red");
        return;
    }
    if (!formData.get("source_doc").trim()) {
        alert("Veuillez fournir une description valide.");
        add_source_form.querySelector("#source-doc").classList.add("red");
        return;
    }
    if (!formData.get("source_type")) {
        alert("Veuillez sélectionner un type pour la source.");
        add_source_form.querySelector("#source-type").classList.add("red");
        return;
    }
    if (!formData.get("source_status")) {
        alert("Veuillez sélectionner un statut pour la source.");
        add_source_form.querySelector("#source-status").style.border = "1px solid red";
        return;
    }

    if (!userId || userId === "0") {
        alert("Veuillez vous connecter pour effectuer cette action.");
        return;
    }

    // Masquer la popup
    document.getElementById("popup-source").style.display = "none";

    // Préparation des données pour l'envoi via AJAX
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/glorix_bdd.php", true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200 && xhr.responseText === "OK") {
                Success_Message("La source a été ajoutée avec succès.");
                // Réinitialiser le formulaire après succès
                add_source_form.reset();
                document.getElementById("tags_list").innerHTML = '<span id="missing_tags_source" style="color: grey;text-transform: uppercase;font-size: small;">Aucun tag actif</span>';
            } else {
                Failure_Message("Une erreur s'est produite lors de l'ajout de la source.");
                console.error("Erreur :", xhr.responseText);
            }
        }
    };

    // Ajout de la clé 'add_source' pour distinguer le traitement sur le serveur
    formData.append("source_added_tags", source_added_tags);
    formData.append("key", "add_source");
    console.log("Sending Source_Added request...");
    console.log(formData);
    // Envoi de la requête
    xhr.send(formData);
}

// Fonction utilitaire pour réinitialiser les champs du formulaire
function resetForm() {
    const popupSourceDiv = document.getElementById("popup-source");
    const inputs = popupSourceDiv.querySelectorAll("input, textarea");
    inputs.forEach((input) => {
        input.value = ""; // Réinitialiser les champs à une chaîne vide
    });
}

function Link_Added2(link_name, link_url, link_doc, category_id, type, source, features, attached_doc, tags, user_id, edited_link) {
    if (user_id === 0) {
        alert("Veuillez vous connecter pour effectuer cette action.");
        document.getElementById("popup-source").style.display = "none";
        return;
    }
    if (category_id === -1) {
        document.getElementById("ListSelect").style.border = "1px solid red";
        return;
    }

    document.getElementById("popup-source").style.display = "none";

    // Validation et encodage des données
    let safeLinkName = encodeURIComponent(link_name);
    let safeLinkUrl = encodeURIComponent(link_url);
    let safeLinkDoc = encodeURIComponent(link_doc);
    let safeAttachedDoc = encodeURIComponent(attached_doc);

    var links_sublist = document.getElementById('ul-of-list-' + category_id);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'Includes/PHP/glorix_bdd.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200 && xhr.response === "OK") {
            if (edited_link === 0) {
                // Ajout d'un lien
                Success_Message("Le lien a été ajouté avec succès.");
                links_sublist.innerHTML += `
                    <li>
                        <a href="${safeLinkUrl}" title="${safeLinkDoc}">${safeLinkName}</a>
                        <button id="edit-link-button" class="edit-icon" onclick="Bad_Edit()">
                            <img src="Includes/Images/Bouton Modifier.png" />
                        </button>
                        <button id="delete-link-button" class="edit-icon" onclick="Bad_Edit()">
                            <img src="Includes/Images/Bouton Supprimer.png" />
                        </button>
                    </li>
                `;
            } else {
                // Modification d'un lien existant
                Success_Message("Le lien a été modifié avec succès.");
                document.getElementById("link" + String(edited_link)).innerHTML = `
                    <a href="${link_url}" title="${link_doc}">${link_name}</a>
                    <button id="edit-link-button" class="edit-icon" onclick="Edit_Link(0, ${user_id}, ${edited_link}, ${link_name}')">
                        <img src="Includes/Images/Bouton Modifier.png">
                    </button>
                    <button id="delete-link-button" class="edit-icon" onclick="Edit_Link(1, ${user_id}, ${edited_link}, '${link_name}', '${link_url}', '${link_doc}', ${category_id}, ${eval}, ${features})">
                        <img src="Includes/Images/Bouton Supprimer.png">
                    </button>
                `;
            }
        } else if (xhr.readyState == 4) {
            Failure_Message("La requête a échoué :(");
        }
    };

    // Préparer et envoyer les données
    let data = new FormData();
    data.append('key', 'add_link');
    data.append('user_id', user_id);
    data.append('edited_link', edited_link);
    data.append('link_name', link_name);
    data.append('link_url', link_url);
    data.append('link_doc', link_doc);
    data.append('category_id', category_id);
    data.append('link_type', type);
    data.append('link_source', source);
    data.append('link_features', JSON.stringify(features));
    data.append('attached_doc', attached_doc);
    data.append('tags_list', JSON.stringify(tags));
    console.log("Sending Link_Added request...");
    xhr.send(data);

    // Réinitialiser le formulaire
    var popupSourceDiv = document.getElementById("popup-source");
    var inputs = popupSourceDiv.querySelectorAll("input, textarea");
    Array.from(inputs).forEach((input) => {
        input.value = "";
    });
}


function Link_Added(linkName, linkUrl, linkDoc, sourceId, listId, linkType, userId, editMode) {

    // 1. Récupérer la valeur de la note sur 5 (étoiles sélectionnées)
    const rating = document.querySelector('#rating-input').textContent.match(/\d+/)[0] || 0;

    // Réinitialiser les bordures des champs
    const inputsToReset = ['link-name', 'link-url', 'link-doc', 'source-select', 'ListSelect', 'link-type'];
    inputsToReset.forEach((id) => {
        document.getElementById(id).style.border = ''; // Supprimer la bordure rouge si existante
    });
    document.querySelector('.rating').style.border = ''; // Réinitialiser la bordure pour les étoiles

    // Vérifications des champs obligatoires
    let isValid = true;

    // Champs obligatoires : vérifier les inputs
    if (linkName.trim() === '') {
        document.getElementById('link-name').style.border = '2px solid red';
        isValid = false;
    }
    if (linkUrl.trim() === '') {
        document.getElementById('link-url').style.border = '2px solid red';
        isValid = false;
    }
    if (linkDoc.trim() === '') {
        document.getElementById('link-doc').style.border = '2px solid red';
        isValid = false;
    }
    if (sourceId.trim() === '') {
        document.getElementById('source-select').style.border = '2px solid red';
        isValid = false;
    }
    if (listId.trim() === '' || listId === '-1') {
        document.getElementById('ListSelect').style.border = '2px solid red';
        isValid = false;
    }
    if (linkType.trim() === '') {
        document.getElementById('link-type').style.border = '2px solid red';
        isValid = false;
    }

    if (rating < 1 || rating > 5) {
        document.querySelector('#rating-input').style.border = '2px solid red';
        isValid = false;
    }

    // Si un champ est invalide, ne pas envoyer la requête
    if (!isValid) {
        alert('Veuillez remplir tous les champs correctement avant de soumettre.');
        return;
    }

    // 2. Récupérer les réponses des cases à cocher
    const checkboxAnswers = [];
    const checkboxes = document.querySelectorAll('#link-eval-questions input[type="checkbox"]');
    checkboxes.forEach((checkbox) => {
        checkboxAnswers.push(checkbox.checked ? 1 : 0); // 1 si coché, sinon 0
    });

    // Vérifiez que le tableau contient 4 éléments (au besoin, sinon erreur)
    if (checkboxAnswers.length !== 4) {
        alert('Une erreur est survenue : nombre attendu de questions incorrect.');
        return;
    }

    // 3. Préparer les données pour l'envoi
    const params = new URLSearchParams();
    params.append('link_name', linkName);
    params.append('link_url', linkUrl);
    params.append('link_doc', linkDoc);
    params.append('source_id', sourceId);
    params.append('list_id', listId);
    params.append('link_type', linkType);
    params.append('user_id', userId);
    params.append('edited_link', editMode ? document.querySelector('#edited-link-id').value : '0');
    params.append('rating', rating); // Ajouter la note sur 5
    params.append('carac', JSON.stringify(checkboxAnswers)); // Ajouter les réponses des cases sous forme JSON

    // 4. Envoi de la requête AJAX
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert("Votre ajout/modification a bien été pris en compte !");
                    window.location.reload();
                    // Action optionnelle : rafraîchir la page ou mettre à jour l'interface
                } else {
                    alert('Erreur : ' + response.message);
                }
            } else {
                alert('Une erreur réseau est survenue.');
            }
        }
    };
    console.log(params);
    xhr.open('POST', 'Includes/PHP/glorix_bdd.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(params.toString());
}

function Edit_Source(flag, user_id, id, name) {
    if (flag == 1) {
        if (user_id == 0) {
            window.alert("Veuillez vous connecter pour effectuer cette action.");
            document.getElementById("popup-source").style.display = "none";
            return;
        }
        if (window.confirm("Confirmez-vous la suppression de la source " + name + " des sources que vous suivez ? (La source sera toujours existante et disponible dans la page de recherche de sources.)")) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.queryselector('source-item' + id).style.display = 'none';
                    Success_Message("La source a été supprimée de votre liste avec succés")
                }
            }
            xhr.open('POST', 'Includes/PHP/glorix_bdd.php', true);
            data = new FormData();
            data.append('key', 'delete_source');
            data.append('user_id', user_id);
            data.append('source_id', id);
            xhr.send(data);
        }
    } else {
        let popupSourceDiv2 = document.getElementById("popup-source");

        //Interdiction à la modification pour user0 (i.e. si non connecté)
        if (user_id === 0) {
            // Gestion des champs (inputs, textarea, select)
            let inputs = popupSourceDiv2.querySelectorAll("input, textarea, select");
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
            let buttons = popupSourceDiv2.querySelectorAll(".button");
            buttons.forEach((button) => {
                if (button.id !== "scroll-button-right" && button.id !== "scroll-button-left" && button.classList.contains("tag-div") === false) {
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
                document.getElementById("popup-source").innerHTML = this.responseText;
                document.getElementById("popup-source").style.display = "flex";
            }
        }
        xhreq.open('POST', 'Includes/PHP/add_source_popup.php', true);
        data = new FormData();
        data.append('edited_source', id);
        data.append('user_id', user_id);
        data.append('user_admin', is_admin);
        data.append('key', 'add_source');
        xhreq.send(data);

    }
}

function Edit_Link(flag, user_id, id, link_name, link_url, link_doc, category_id, eval, features) {
    if (flag === 1) {
        // Suppression du lien
        if (user_id === 0) {
            alert("Veuillez vous connecter pour effectuer cette action.");
            document.getElementById("popup-source").style.display = "none";
            return;
        }
        if (confirm("Confirmez-vous la suppression du lien " + link_name + " ?")) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'Includes/PHP/glorix_bdd.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('link' + id).style.display = 'none';
                    Success_Message("Le lien a été supprimé avec succès.");
                }
            };
            let data = new FormData();
            data.append('key', 'delete_link');
            data.append('user_id', user_id);
            data.append('link_id', id);
            xhr.send(data);
        }
    } else {
        // Modification du lien
        if (user_id === 0) {
            let popupSourceDiv2 = document.getElementById("popup-source");

            let inputs = popupSourceDiv2.querySelectorAll("input, textarea, select");
            inputs.forEach(input => {
                let previousValue = input.value;

                input.addEventListener("focus", () => {
                    previousValue = input.value;
                });

                input.addEventListener("input", () => {
                    alert("Veuillez vous connecter pour réaliser cette action.");
                    input.value = previousValue;
                    input.blur();
                });
            });

            let buttons = popupSourceDiv2.querySelectorAll(".button");
            buttons.forEach(button => {
                button.addEventListener("click", (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    alert("Veuillez vous connecter pour réaliser cette action.");
                });
            });
        }

        var xhreq = new XMLHttpRequest();
        xhreq.open('POST', 'Includes/PHP/add_source_popup.php', true);
        xhreq.onreadystatechange = function () {
            if (xhreq.readyState === 4 && xhreq.status === 200) {
                document.getElementById("popup-source").innerHTML = this.responseText;
                document.getElementById("popup-source").style.display = "flex";
            }
        };

        let data = new FormData();
        data.append('key', 'add_link');
        data.append('edited_link', id);
        data.append('link_name', link_name);
        data.append('link_url', link_url);
        data.append('link_doc', link_doc);
        data.append('category_id', category_id);
        data.append('user_id', user_id);
        data.append('eval', eval);
        data.append('features', features);
        xhreq.send(data);
    }
}

function Bad_Edit() {
    window.alert('Veuillez recommencer l\'opération après le rechargement de la page.');
    window.location.reload();
}

function updateOpenings(sourceId) {
    // Vérifions si un ID valide a été envoyé
    if (!sourceId) {
        console.error("Aucun identifiant source fourni.");
        return;
    }

    // Initialisation de l'objet XMLHttpRequest pour une requête AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/glorix_bdd.php", true);
    data = new FormData();
    data.append('key', 'update_openings');
    data.append('source_id', sourceId);
    // Envoi des données au script PHP avec l'ID source
    xhr.send();

    // Vérification de la réponse côté client
    xhr.onload = function () {
        if (xhr.status === 200) {
            console.log("Le compteur d'ouvertures a été mis à jour pour la source avec ID: " + sourceId);
        } else {
            console.error("Erreur lors de la mise à jour du compteur d'ouvertures : " + xhr.status);
        }
    };
}

// Fonction pour afficher le champ texte du lien de la source
function showSourceInput() {
    document.getElementById("source-link").style.display = "inline-block";
}

// Fonction pour masquer le champ texte du lien de la source
function hideSourceInput() {
    document.getElementById("source-link").style.display = "none";
}

// Met à jour le champ texte avec le lien de la source sélectionnée
function updateSourceLink() {
    console.log("updateSourceLink");
    const selectedOption = document.getElementById("source-select").selectedOptions[0];
    const sourceLink = selectedOption ? selectedOption.getAttribute("data-link") : "";
    document.getElementById("source-link").value = sourceLink;
}

// Met à jour dynamiquement la meilleure source
function updateBestSource(sourceLinkInput) {
    const linkUrl = document.getElementById("link-url").value;
    console.log(updateBestSource);

    if (linkUrl.trim() === "") return; // Si le lien est vide, on stoppe

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/glorix_bdd.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
        if (xhr.status === 200 && xhr.responseText) {
            try {
                // Parse la réponse JSON
                const response = JSON.parse(xhr.responseText);
                console.log(response);

                // Extraire les IDs pour "substringMatches"
                const ids = response.substringMatches;
                const bestMatch = response.bestMatch;

                // Créer une requête pour récupérer le code HTML du select
                const xhrPhp = new XMLHttpRequest();
                xhrPhp.open("POST", "Includes/PHP/glorix_bdd.php", true); // Adresse à un script PHP dédié

                xhrPhp.onload = function () {
                    if (xhrPhp.status === 200) {
                        console.log(sourceLinkInput);
                        console.log(sourceLinkInput == "");
                        if (sourceLinkInput == "") {
                            updateSourceLink();
                        }
                        // Injecter le select généré en PHP dans le DOM
                        document.getElementById("source-select").innerHTML = xhrPhp.responseText;
                    } else {
                        console.error("Erreur lors de la génération du select : ", xhrPhp.responseText);
                    }
                };
                let data = new FormData();
                data.append('key', 'generate_source_select');
                data.append('ids', (JSON.stringify(ids)));
                data.append('best_match', bestMatch);
                xhrPhp.send(data);
            } catch (e) {
                console.error("Erreur de traitement de réponse JSON : ", e);
            }
        } else {
            console.error("Erreur : ", xhr.responseText);
        }
    };

    const data = `key=find_best_source&link_url=${encodeURIComponent(linkUrl)}&source_link=${encodeURIComponent(sourceLinkInput)}`;
    console.log(data);
    xhr.send(data);
}

// Met à jour dynamiquement la meilleure source
function updateBestSourceForFav(sourceLinkInput) {
    const linkUrl = document.getElementById("link-url").value;
    console.log(updateBestSource);

    if (linkUrl.trim() === "") return; // Si le lien est vide, on stoppe

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/glorix_bdd.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
        if (xhr.status === 200 && xhr.responseText) {
            try {
                // Parse la réponse JSON
                const response = JSON.parse(xhr.responseText);
                console.log(response);

                // Extraire les IDs pour "substringMatches"
                const ids = response.substringMatches;
                const bestMatch = response.bestMatch;

                // Créer une requête pour récupérer le code HTML du select
                const xhrPhp = new XMLHttpRequest();
                xhrPhp.open("POST", "Includes/PHP/glorix_bdd.php", true); // Adresse à un script PHP dédié

                xhrPhp.onload = function () {
                    if (xhrPhp.status === 200) {
                        // Injecter le select généré en PHP dans le DOM
                        document.getElementById("source-select-for-fav").innerHTML = xhrPhp.responseText;
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

    const data = `key=find_best_source&link_url=${encodeURIComponent(linkUrl)}&source_link=${encodeURIComponent('')}`;
    console.log(data);
    xhr.send(data);
}


function source_list_scroll_og(n) {
    var container = document.getElementById('suggested_tags_source_content');
    if (n === '+1') {
        container.style.flexWrap = 'wrap'; // Permet d'aller à la ligne si nécessaire
        document.getElementById('srce-scroll-button-down').style.display = 'none';
        document.getElementById('srce-scroll-button-up').style.display = 'unset';
    } else if (n === '-1') {
        container.style.flexWrap = 'unset'; // Permet d'aller à la ligne si nécessaire
        document.getElementById('srce-scroll-button-down').style.display = 'unset';
        document.getElementById('srce-scroll-button-up').style.display = 'none';
    } else {
        container.scrollLeft += n;
    }
}

function suggest_source_tags_og(input, user_id) {
    console.log(input);
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var suggested_tags_source_content_bis = document.getElementById('suggested_tags_source_content');
            suggested_tags_source_content_bis.innerHTML = this.responseText;
        }
    }
    xhr.open('POST', 'Includes/PHP/source_tags.php', true);
    data = new FormData();
    data.append('input', input);
    data.append('source_name', '');
    data.append('source_doc', '');
    data.append('source_category', '');
    data.append('key', 'suggest_source_tags');
    data.append('user_id', user_id);
    data.append('memory', JSON.stringify({}));
    xhr.send(data);
}

function Add_source_tags_og(tag_id, string, user_id, source_added_tags) {
    if (user_id == 0) {
        window.alert('Veuillez vous connecter pour ajouter un tag')
        return
    }
    if (string === '') {
        return;
    }
    if (tag_id == 0) {
        xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/source_tags.php', true);
        data = new FormData();
        data.append('source_tags_name', string);
        data.append('key', 'search_source_tag');
        xhr.send(data);
        xhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {  // Je vérifie si le nom de tag entré n'est pas déjà existant, auquel cas, je ne fais qu'appliquer la deuxième partie de la fonction pour un tag déja existant
                if (parseInt(this.responseText) > 0) {
                    tag_id = parseInt(this.responseText);
                } else {
                    xhr = new XMLHttpRequest();
                    xhr.open('POST', 'Includes/PHP/source_tags.php', true);
                    data = new FormData();
                    data.append('name', string);
                    data.append('key', 'new_source_tag_suggestion');
                    xhr.send(data);
                    xhr.onreadystatechange = function () {
                        if (this.readyState == 4 && this.status == 200) {
                            tag_id = parseInt(this.responseText);
                        }
                    }
                }
            } else {
                Failure_Message('Une erreur est survenue lors de la recherche du tag :(');
                document.getElementById('source-tags').style.border = '1px solid rgb(255, 0, 0)';
                return;
            }
        }
    }
    console.log("TEST");
    console.log("source_added_tags" + source_added_tags);
    if (!source_added_tags) {
        source_added_tags = [];
    }
    if (source_added_tags.includes(tag_id)) {
        document.getElementById('source-tags').style.border = '1px solid rgb(255, 0, 0)';
        document.getElementById('source-tags').ariaPlaceholder = 'Ce tag est déjà appliqué';
        document.getElementById('source-tags').classList.add('red');
        return
    } else {
        console.log(this.responseText);
        document.getElementById('missing_tags_source').style.display = 'none';
        document.getElementById("source_tags_list_content").innerHTML += '<button type="button" id="active-source-tag-' + tag_id + '" class="active-tag" onclick="Withdraw_source_tags(' + tag_id + ',' + user_id + ')" style="padding : 2px 0; color : rgb(91,98,170)">#' + string + '</button>';
        document.getElementById('source-tags').classList.remove('red')
    }
}

function Withdraw_source_tags($id, $user_id, $source_id) {
    if ($user_id == 0) {
        window.alert('Veuillez vous connecter pour retirer un tag');
        return;
    }
    source_added_tags.splice(source_added_tags.indexOf($id), 1);
    document.getElementById("active-source-tag-" + $id).remove();
    if (source_added_tags.length === 0) {
        document.getElementById("missing_tags_source").style.display = "unset";
    }
}


function GenerateOpeningsGraph(sourceName, openingsData, linksData, creationDate) { // Données pour les ouvertures et récupération des ajouts de liens
    function generateMonthsRange(start, end) {
        const startDate = new Date(start + "-01");
        const endDate = new Date(end + "-01");
        const months = [];

        // Ajouter chaque mois à partir de la date de début jusqu'à la date de fin
        while (startDate <= endDate) {
            const year = startDate.getFullYear();
            const month = String(startDate.getMonth() + 1).padStart(2,
                '0'); // Mois formaté
            months.push(`${year}-${month}`);
            startDate.setMonth(startDate.getMonth() + 1); // Passer au mois suivant
        }
        // Ajouter explicitement le mois en cours si non couvert
        const currentDate = new Date(); // Aujourd'hui
        const currentMonth =
            `${currentDate.getFullYear()}-${String(currentDate.getMonth() +
                1).padStart(2, '0')}`;
        if (!months.includes(currentMonth)) {
            months.push(currentMonth);
        }


        return months;
    }

    const currentDate = new Date();
    const currentMonth =
        `${currentDate.getFullYear()}-${String(currentDate.getMonth() +
            1).padStart(2, '0')}`;
    const uniqueMonths = generateMonthsRange(creationDate, currentMonth);
    console.log(uniqueMonths);

    // Obtenir les données des ouvertures
    const openingsByMonth = openingsData.reduce((acc, { month, openings
    }) => {
        acc[month] = (acc[month] || 0) + openings;
        return acc;
    }, {});

    // Grouper les ajouts de lien par mois
    const linksByMonth = linksData.reduce((acc, { month, additions }) => {
        acc[month] = (acc[month] || 0) + additions; // Compte
        return acc;
    }, {});
    console.log(linksByMonth);

    // Étape 4 : Mapper les valeurs sur "uniqueMonths"
    const openingsValues = uniqueMonths.map(month =>
        openingsByMonth[month] || 0);
    const linksValues = uniqueMonths.map(month => linksByMonth[month]
        || 0);

    // Supprimez les anciens popups s'ils existent
    const existingPopupContainer =
        document.getElementById('popup-graph-container');
    if (existingPopupContainer) {
        document.body.removeChild(existingPopupContainer);
    }

    // Créer un conteneur pour le graphique plein écran
    const popup_container = document.createElement('div');
    popup_container.id = 'popup-graph-container'; // Ajoutez un ID pour simplifier l'identification
    popup_container.style.position = 'fixed';
    popup_container.style.top = '0';
    popup_container.style.left = '0';
    popup_container.style.width = '100%';
    popup_container.style.height = '100%';
    popup_container.style.zIndex = '10000';
    popup_container.style.padding = '20px';
    popup_container.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';

    const popup = document.createElement('div');
    popup.style.position = 'fixed';
    popup.style.top = '0';
    popup.style.left = '0';
    popup.style.width = '70%';
    popup.style.height = '70%';
    popup.style.top = '15%';
    popup.style.left = '15%';
    popup.style.backgroundColor = 'white';
    popup.style.borderRadius = '10px';

    // Bouton de fermeture
    const closeButton = document.createElement('button');
    closeButton.textContent = 'x';
    closeButton.style.position = 'absolute';
    closeButton.style.top = '10px';
    closeButton.style.right = '10px';
    closeButton.style.border = 'none';
    closeButton.style.padding = '10px';
    closeButton.style.background = 'none';
    closeButton.style.color = 'red';
    closeButton.style.fontWeight = 'bold';
    closeButton.style.cursor = 'pointer';

    closeButton.addEventListener('click', () => {
        const popupToRemove =
            document.getElementById('popup-graph-container');
        if (popupToRemove) {
            document.body.removeChild(popupToRemove);
        }
    });

    // Canvas pour le graphique
    const canvas = document.createElement('canvas');
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight - 50;
    canvas.style.borderRadius = '10px';
    canvas.style.backgroundColor = '#FFFFFF';

    popup.appendChild(closeButton);
    popup.appendChild(canvas);
    popup_container.appendChild(popup);
    document.body.appendChild(popup_container);

    createGraph(canvas, uniqueMonths, openingsValues, linksValues,
        sourceName)

}

function createGraph(canvas, months, openings, links, sourceName) {
    const ctx = canvas.getContext('2d');
    canvas.width = canvas.clientWidth;
    canvas.height = canvas.clientHeight;

    const padding = 60;
    const width = canvas.width - 2 * padding;
    const height = canvas.height - 2 * padding;
    const maxY = Math.max(...openings, ...links) * 1.1;

    function getX(i) { return padding + (i / (months.length - 1)) *
        width; }
    function getY(value) { return canvas.height - padding - (value /
        maxY) * height; }

    // Fond
    ctx.fillStyle = "#F8F9FA";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Affichage du titre
    ctx.fillStyle = "#000";
    ctx.font = "20px Arial";
    ctx.textAlign = "center";
    ctx.fillText(sourceName, canvas.width / 2, 30);

    // Dessiner la légende (en haut à droite)
    const legendX = canvas.width - 200; // Position horizontale
    const legendY = 20; // Position verticale
    const legendSpacing = 25; // Espacement entre les lignes

    // Style de la légende
    ctx.font = "14px Arial";
    ctx.textAlign = "left";

    // Courbe bleue (Ouvertures)
    ctx.fillStyle = "blue";
    ctx.fillRect(legendX, legendY, 15, 15); // Petit carré coloré
    ctx.fillStyle = "#000";
    ctx.fillText("Ouvertures de liens", legendX + 20, legendY + 12);

    // Courbe orange (Ajouts de liens)
    ctx.fillStyle = "orange";
    ctx.fillRect(legendX, legendY + legendSpacing, 15, 15);
    ctx.fillStyle = "#000";
    ctx.fillText("Ajouts de liens", legendX + 20, legendY +
        legendSpacing + 12);

    // Axes
    ctx.strokeStyle = "#333";
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, canvas.height - padding);
    ctx.lineTo(canvas.width - padding, canvas.height - padding);
    ctx.stroke();

    // Labels Axe X (avec gestion de chevauchement)
    ctx.fillStyle = "#000";
    ctx.font = "14px Arial";
    let labelStep = Math.ceil(months.length / 10); // Affiche un label tous les "labelStep" mois

    let angle = 0;
    if (months.length > 10) {
        angle = -45 * (Math.PI / 180); // Rotation des labels si nécessaire
        ctx.textAlign = "right";
        ctx.textBaseline = "middle";
    }

    months.forEach((month, i) => {
        if (i % labelStep === 0) {
            const x = getX(i);
            const y = canvas.height - padding + 20;
            ctx.save();
            ctx.translate(x, y);
            if (angle !== 0) ctx.rotate(angle);
            ctx.fillText(month, 0, 0);
            ctx.restore();
        }
    });

    // Labels Axe Y
    for (let i = 0; i <= 5; i++) {
        const yValue = (maxY / 5) * i;
        const y = getY(yValue);
        ctx.fillText(Math.round(yValue), padding - 40, y + 5);
        ctx.beginPath();
        ctx.moveTo(padding - 5, y);
        ctx.lineTo(padding, y);
        ctx.stroke();
    }

    // Fonction pour tracer une courbe
    function drawCurve(data, color) {
        ctx.beginPath();
        ctx.strokeStyle = color;
        ctx.lineWidth = 2;

        ctx.moveTo(getX(0), getY(data[0]));
        for (let i = 1; i < data.length; i++) {
            const prevX = getX(i - 1);
            const prevY = getY(data[i - 1]);
            const currX = getX(i);
            const currY = getY(data[i]);
            const controlX = (prevX + currX) / 2;

            ctx.quadraticCurveTo(controlX, prevY, currX, currY);
        }

        ctx.stroke();

        // Points
        ctx.fillStyle = color;
        data.forEach((value, i) => {
            ctx.beginPath();
            ctx.arc(getX(i), getY(value), 4, 0, Math.PI * 2);
            ctx.fill();
        });
    }

    // Tracer les courbes
    drawCurve(openings, "blue");
    drawCurve(links, "orange");
}


const stars = document.querySelectorAll(".rating .star-big");
const ratingValue = document.querySelector(".rating .rating-value");

let currentRating = 0; // Variable pour stocker la note actuelle

// Fonction pour mettre à jour les étoiles
const updateStars = (rating) => {
    const stars = document.querySelectorAll(".rating .star-big");
    stars.forEach(star => {
        star.classList.remove("full");
        star.innerHTML = "☆";
        if (parseInt(star.getAttribute("data-value")) <= rating) {
            star.classList.add("full");
            star.innerHTML = "★";
        }
    });
};

// Ajouter le gestionnaire d'événement de clic sur chaque étoile
function inputStars(star, ratingValue) {
    console.log(star.getAttribute("data-value"));
    const selectedValue = parseInt(star.getAttribute("data-value")); // Récupérer la valeur de l'étoile cliquée
    currentRating = selectedValue; // Mettre à jour la note actuelle
    updateStars(currentRating); // Mettre à jour les étoiles visuellement
    ratingValue.textContent = `(${currentRating})`; // Mettre à jour l'affichage de la note
}

function DemandCotation(parentDiv, source_id, user_id) {
    if (user_id == 0) {
        window.alert("Veuillez vous connecter pour demander une cotation");
        return;
    }
    const message = parentDiv.querySelector('#demand-message').value;
    const use_area = parentDiv.querySelector('#use-area').value;
    const suggestion = parentDiv.querySelector('#grade-suggestion').value.trim();
    const group = parentDiv.querySelector('#demanding-group').value.trim();
    var xhr = new XMLHttpRequest();
    data = new FormData();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200 && xhr.responseText == 'OK') {
            Success_Message('Demande de cotation envoyée avec succès');
            parentDiv.innerHTML = "<h4>Demande de cotation effectuée.</h4>";
        } else if (xhr.readyState === 4) {
            Failure_Message('Echec de l\'envoi de la demande de notation');
        }
    }
    xhr.open('POST', 'Includes/PHP/glorix_bdd.php', true);
    data.append('key', 'demand_cotation');
    data.append('source_id', source_id);
    data.append('user_id', user_id);
    data.append('message', message);
    data.append('use_area', use_area);
    data.append('suggestion', suggestion);
    data.append('group', group)
    xhr.send(data);
}

function verif_cotation(element) {
    const text = element.value;
    if (text && text.length == 2) {
        let letters = ["A", "B", "C", "D", "E"];
        let numbers = ["1", "2", "3", "4", "5"];
        if (!letters.find(text[0]) || !numbers.find(text[1])) {
            this.value = "";
            this.classList.add('red');
        }
    } else {
        this.classList.remove('red');
    }
}


function deleteFavLink(fav_id, user_id) {
    if (user_id == 0) {
        window.alert('Veuillez vous connecter pour réaliser cette action !');
        return;
    }
    if (window.confirm("Voulez-vous vraiment supprimer ce lien de vos favoris ?")) {
        console.log(user_id)
        var data = new FormData();
        data.append('fav_id', fav_id);
        data.append('user_id', user_id);
        data.append('key', 'delete_fav_link');

        fetch('Includes/PHP/glorix_bdd.php', {
            method: 'POST',
            body: data
        })
            .then(response => {
                document.getElementById("FavLink" + fav_id).remove();
            })
    }
}
function deleteFavLinkAdmin(fav_id) {
    if (window.confirm("Voulez-vous vraiment supprimer ce lien de vos favoris ?")) {
        var data = new FormData();
        data.append('fav_id', fav_id);
        data.append('user_id', 0);
        data.append('key', 'delete_fav_link');

        fetch('Includes/PHP/glorix_bdd.php', {
            method: 'POST',
            body: data
        })
            .then(response => {
                document.getElementById("FavLink" + fav_id).remove();
            })
    }
}

function hideList(listId) {
    if (!confirm('Êtes-vous sûr de vouloir masquer cette liste ?')) {
        return;
    }

    // Initialisation de la requête Ajax
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) { // Requête terminée
            if (xhr.status === 200) { // Succès
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert('Liste masquée avec succès');
                    // Optionnel : rafraîchir ou masquer l'élément DOM correspondant
                    window.location.reload();
                } else {
                    alert('Erreur : ' + response.message);
                }
            } else {
                alert('Une erreur réseau est survenue.');
            }
        }
    };
    xhr.open('POST', 'Includes/PHP/hide_list.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('list_id=' + encodeURIComponent(listId));
}

function restoreList(listId) {

    // Initialisation de la requête Ajax
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) { // Requête terminée
            if (xhr.status === 200) { // Succès
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert('Liste démasquée avec succès');
                    // Optionnel : rafraîchir ou masquer l'élément DOM correspondant
                    document.getElementById('list-' + listId).style.display = 'none';
                } else {
                    alert('Erreur : ' + response.message);
                }
            } else {
                alert('Une erreur réseau est survenue.');
            }
        }
    };
    xhr.open('POST', 'Includes/PHP/hide_list.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('list_id=' + encodeURIComponent(listId) + '&restore=true');
}


function EditCotationForm(source_id) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('source-cotation-content').innerHTML = xhr.responseText;
        }
    }
    xhr.open('POST', 'Includes/PHP/cotation_form_bdd.php', true);
    data = new FormData();
    data.append('source_id', source_id);
    xhr.send(data);
}

function hideLink(linkId) {
    if (!confirm('Êtes-vous sûr de vouloir masquer ce lien ?')) {
        return;
    }

    // Initialisation de la requête Ajax
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) { // Requête terminée
            if (xhr.status === 200) { // Succès
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert('Lien masqué avec succès');
                    // Optionnel : rafraîchir ou masquer l'élément DOM correspondant
                    window.location.reload();
                } else {
                    alert('Erreur : ' + response.message);
                }
            } else {
                alert('Une erreur réseau est survenue.');
            }
        }
    };
    xhr.open('POST', 'Includes/PHP/hide_link.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('link_id=' + encodeURIComponent(linkId));
}

function restoreLink(linkId) {

    // Initialisation de la requête Ajax
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) { // Requête terminée
            if (xhr.status === 200) { // Succès
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert('Lien démasqué avec succès');
                    // Optionnel : rafraîchir ou masquer l'élément DOM correspondant
                    window.location.reload();
                } else {
                    alert('Erreur : ' + response.message);
                }
            } else {
                alert('Une erreur réseau est survenue.');
            }
        }
    };
    xhr.open('POST', 'Includes/PHP/hide_link.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('link_id=' + encodeURIComponent(linkId) + '&restore=true');
}

let currentSourceId = null;
let currentUserId = null;

function openAlertModal(source_id, user_id) {
    currentSourceId = source_id;
    currentUserId = user_id;
    document.getElementById("alertModal").style.display = "block"; // Afficher la modale
}

function closeAlertModal() {
    document.getElementById("alertModal").style.display = "none"; // Fermer la modale
}

function Alert_Source() {
    const user_message = document.getElementById("alertMessage").value;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/alert_source.php", true);

    const data = new FormData();
    data.append("source_id", currentSourceId);
    data.append("user_id", currentUserId);
    data.append("message", user_message);

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            alert(this.responseText);
            closeAlertModal(); // Fermer la modale après envoi
        }
    };

    xhr.send(data);
}

function Follow_Source(source_id, user_id) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/follow_source.php", true);
    xhr.onreadystatechange = function () {
        updateResults();
    }
    var data = new FormData();
    data.append("source_id", source_id);
    data.append("user_id", user_id);
    data.append("follow", 1);
    xhr.send(data);
}

function Unfollow_Source(source_id, user_id) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/follow_source.php", true);
    xhr.onreadystatechange = function () {
        updateResults();
    }
    var data = new FormData();
    data.append("source_id", source_id);
    data.append("user_id", user_id);
    data.append("unfollow", 1);
    xhr.send(data);
}7

function Follow_Source_Admin(source_id) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/follow_source.php", true);
    xhr.onreadystatechange = function () {
        window.location.reload();
    }
    var data = new FormData();
    data.append("source_id", source_id);
    data.append("user_id", 0);
    data.append("follow", 1);
    data.append("admin", 1);
    xhr.send(data);
}

function Unfollow_Source_Admin(source_id) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/follow_source.php", true);
    xhr.onreadystatechange = function () {
        window.location.reload();
    }
    var data = new FormData();
    data.append("source_id", source_id);
    data.append("user_id", 0);
    data.append("unfollow", 1);
    data.append("admin", 1);
    xhr.send(data);
}

