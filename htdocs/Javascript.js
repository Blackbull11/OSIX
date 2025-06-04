// Code JS pour les menus déroulants (collapsible)

var coll = document.getElementsByClassName("collapsible");
var i;

let list_tags = [];
let source_added_tags = [];

for (i = 0; i < coll.length; i++) {
    coll[i].addEventListener("click", function () {
        this.classList.toggle("collapsible-active");
        var content = this.nextElementSibling;
        if (content.style.maxHeight) {
            content.style.maxHeight = null;
        } else {
            for (i = 0; i < coll.length; i++) {
                coll[i].style.maxHeight = 'unset';
            }
            content.style.maxHeight = 'fit-content';
        }
    });
}

var coll_box = document.getElementsByClassName("collapsible-box");
var j;

for (j = 0; j < coll_box.length; j++) {
    coll_box[j].addEventListener("click", function () {
        console.log('test');
        this.classList.toggle("collapsible-box-active");
        var content = this.nextElementSibling.nextElementSibling;
        if (this.id = "collapsibleForSources") {
            console.log("collapsibleForSources")
            sortSources("");
        }
        if (content.style.maxHeight) {
            content.style.maxHeight = null;
        } else {
            for (j = 0; j < coll_box.length; j++) {
                coll_box[j].style.maxHeight = 'unset';
            }
            content.style.maxHeight = 'fit-content';
        }
    });
}


// Code pour l'affichage des messages de succés et d'echec
function Success_Message($string) {
    console.log('Success_Message(' + $string + ') a été appelé.')
    const message = document.getElementById('success-message');
    if (message) {
        message.style.opacity = '1';
        message.innerHTML = $string;
        message.style.display = 'flex';

        setTimeout(() => {
            setTimeout(() => message.style.display = 'none', 500); // Masquer complètement après la transition
        }, 3000);
    }
}

function Failure_Message($string) {
    console.log('Failure_Message(' + $string + ') a été appelé.')
    const message = document.getElementById('failure-message');
    if (message) {
        message.style.opacity = '1';
        message.innerHTML = $string;
        message.style.display = 'flex';

        setTimeout(() => {
            setTimeout(() => message.style.display = 'none', 500); // Masquer complètement après la transition
        }, 3000);
    }
}

//OUverture de toutes les sources d'une liste
function openAllSources(listId) {
    console.log('ouverture de toutes les sources d\'une liste');
    // Récupérer les sources via l'API PHP
    fetch(`Includes/PHP/get_list_links.php?list_id=${listId}`)
        .then((response) => response.json())
        .then((data) => {
            console.log(data);
            // Vérification si des sources sont présentes
            if (data.links && data.links.length > 0) {
                // Créer et afficher une popup HTML avec les liens
                const link_popup = document.createElement('div');
                link_popup.classList.add('custom-link-popup');

                // Créer un conteneur pour la popup
                link_popup.innerHTML = `
                    <div class="link-popup-content">
                        <button class="link-popup-close">&times;</button>
                        <h2>Liens disponibles</h2>
                        <div class="link-popup-links">
                            ${data.links.map(source =>
                    `<a href="${source.url}" target="_blank">> ${source.name}</a><br>`).join('')}
                        </div>
                    </div>
                `;

                // Ajouter la popup au corps de la page
                document.body.appendChild(link_popup);

                // Ajouter un gestionnaire pour fermer la popup
                link_popup.querySelector('.link-popup-close').addEventListener('click', () => {
                    link_popup.remove();
                });
            } else {
                alert("Aucune source disponible pour cette liste.");
            }
        })
        .catch((error) => {
            console.error("Erreur lors de la récupération des sources :", error);
            alert("Une erreur s'est produite. Impossible d'ouvrir les sources.");
        });
}
// Fonction pour la gestion des profils :

// Fonction pour changer le mot de passe
function changePassword() {
    const newPassword = prompt("Entrez le nouveau mot de passe :");
    if (newPassword) {
        fetch("Includes/PHP/profils_backend.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "change_password",
                new_password: newPassword,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    alert("Erreur : " + data.error);
                } else {
                    alert(data.message);
                }
            })
            .catch((error) => console.error("Erreur :", error));
    }
}







// Drag and drop et classement des sources
// Fonction pour trier les sources
function sortSources(input) {
    // Obtenir le critère de tri sélectionné par l'utilisateur
    const sortCriteria = document.getElementById("sort-options").value;

    // Afficher un message si aucun critère de tri n'est sélectionné
    if (!sortCriteria) {
        console.error("Aucun critère de tri sélectionné.");
        return;
    }


    // Création d'une requête XMLHttpRequest pour communiquer avec le backend
    const xhr = new XMLHttpRequest();

    // Gestion de la réponse retournée par le backend
    xhr.onreadystatechange = function () {
        if (this.readyState === 4) { // La réponse est prête
            if (this.status === 200) { // Requête réussie
                // Mise à jour du DOM avec les résultats triés
                const sourcesList = document.getElementById("sources-list");
                sourcesList.innerHTML = this.responseText;
                console.log('tri des sources');

            } else {
                // Affichage d'une erreur en cas d'échec
                console.error("Erreur lors de la requête de tri des sources :", this.statusText);
                Failure_Message("La requête de tri a échoué.");
            }
        }
    };

    // Configurer la requête pour un POST vers l'endpoint PHP responsable du tri
    xhr.open("POST", "Includes/PHP/glorix_bdd.php", true);

    // Création et envoi des données POST
    const formData = new FormData();
    formData.append("key", "sort_sources"); // Indicateur d'action pour le backend
    formData.append("criteria", sortCriteria); // Critère de tri sélectionné
    formData.append("input", input); // Tags appliqués
    xhr.send(formData); // Envoie de la requête avec les données
}


// Initialiser le drag and drop après chargement de la page


function applyFilters() {
    // Récupérer les valeurs des filtres
    const typeFilter = document.getElementById("type-filter").value;
    const statusFilter = document.getElementById("status-filter").value;

    // Obtenir la liste des sources
    const sourcesList = document.getElementById("sources-list");
    const sources = sourcesList.querySelectorAll(".source-item-container");

    // Parcourir les sources et appliquer les filtres
    sources.forEach((source) => {
        sourceItem = source.querySelector(".source-item");
        const sourceType = sourceItem.getAttribute("data-type");
        const sourceStatus = sourceItem.getAttribute("data-status");

        // Vérifier si l'élément correspond aux filtres
        const typeMatch = (typeFilter === "all" || sourceType === typeFilter);
        const statusMatch = (statusFilter === "all" || sourceStatus === statusFilter);

        if (typeMatch && statusMatch) {
            source.style.display = "block"; // Afficher l'élément
        } else {
            source.style.display = "none"; // Masquer l'élément
        }
    });
}

function moveUp(id, rank) {
    console.log('moveUp');
    if (rank > 1) {
        updateRanking(id, rank, rank - 1); // Inverse avec l'élément au-dessus
    }
}

function moveDown(id, rank) {
    console.log('moveDown');
    updateRanking(id, rank, rank + 1); // Inverse avec l'élément en dessous
}

function updateRanking(id, oldRank, newRank) {
    // Créez une instance d'XMLHttpRequest
    const xhr = new XMLHttpRequest();

    // Configurer la requête POST vers le bon endpoint
    xhr.open('POST', 'Includes/PHP/glorix_bdd.php', true);

    // Définir l'en-tête pour envoyer des données JSON
    xhr.setRequestHeader('Content-Type', 'application/json');

    // Définir une fonction de retour pour traiter la réponse
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) { // La requête est terminée
            if (xhr.status === 200) { // Succès HTTP
                if (xhr.responseText == 'OK') {
                    console.log('update_ranking');
                }
            } else {
                Failure_Message("Le déplacement a échoué :(")
            }
        }
    };

    // Préparer et envoyer les données JSON
    const data = new FormData();
    data.append('key', 'update_ranking');
    data.append('id', id);
    data.append('old_rank', oldRank);
    data.append('new_rank', newRank);
    xhr.send(data);
}

function deploy_collapsible_content_source(data_id) {
    console.log('détails');
    var content = document.querySelectorAll(`.collapsible-content-source`);
    for (i = 0; i < content.length; i++) {
        if (content[i].getAttribute('data-id') == data_id) {
            if (content[i].style.display == "none" || content[i].style.display == "" ){
                content[i].style.display = "block";
            } else {
                content[i].style.display = 'none';
            }
        }
    }

}



function downloadDatabaseDump() {
// Rediriger vers le script PHP pour générer et télécharger le fichier SQL
    window.location.href = "Includes/PHP/Generate_SQL_Dump.php";
}



// Fonction pour charger une nouvelle version d'un projet (ou un nouveau projet ?) dans la bdd
function newVersionProject($n){
    const newProjectTitleInput = document.getElementById('newProjectTitleInput');
    const newTitle = newProjectTitleInput ? newProjectTitleInput.value : 'Votre nouveau projet';
    console.log(newTitle, " est le nouveau titre.");
    data = new FormData();
    data.append('project_id', $n);
    data.append('projectNewName', newTitle);

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() { if (isRequestSuccessful(this))
    {   window.location.href = "EPIX_interface.php";
        console.log('response new' , this.responseText);
    }}

    xhr.open('POST', 'Includes/PHP/new_version_project.php');
    xhr.send(data);
}

function redirection(idProject) {
    data = new FormData();
    data.append('project_id', idProject);

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (isRequestSuccessful(xhr)) {
            window.location.href = xhr.responseText;
            console.log(xhr.responseText);
            console.log('redirection ok');
        }
    }

    xhr.open('POST', 'Includes/PHP/projectRedirection.php', true);
    xhr.send(data);
}

// Code pour la mise à jour des connexions aux différents modules

const boxDiv = document.querySelector(".BOX");
var boxVisited = false;
if (boxDiv && !boxVisited) {
    boxDiv.addEventListener("click", function () {
        if (boxVisited === false) {
            boxVisited = true;
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "Includes/PHP/update_log.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log("Log mis à jour avec succès.");
                }
            };

            xhr.send("action=update_box");
        }
    });
}

const glorixDiv = document.querySelector(".GLORIX");
var glorixVisited = false;
if (glorixDiv && glorixVisited === false) {
    glorixDiv.addEventListener("click", function () {
        if (glorixVisited === false) {
            glorixVisited = true;
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "Includes/PHP/update_log.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log("Log mis à jour avec succès.");
                }
            };

            xhr.send("action=update_glorix");
        }
    });
}
const epixDiv = document.querySelector(".EPIX");
var epixVisited = false;
if (epixDiv && epixVisited === false) {
    epixDiv.addEventListener("click", function () {
        if (epixVisited === false) {
            epixVisited = true;
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "Includes/PHP/update_log.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log("Log mis à jour avec succès.");
                }
            };

            xhr.send("action=update_epix");
        }

    });
}

function open_EPIX(user_id) {
    if (user_id != 0) {
        window.open("http://localhost/EPIX_gestionnaire.php", "_blank");
    }
    else {
        alert('veuillez vous connecter ou explorer la SANDBOX');
    }
}

function newMessage(corps, groups, users, type)
{
    if (groups.length!=0 || users.length !=0)
    {
        let data = new FormData();
        data.append('action', 'submitMail');
        data.append('mailContent', corps);
        data.append('groupSelectedRecipients', JSON.stringify(groups));
        data.append('userSelectedRecipients', JSON.stringify(users));
        data.append('type', type);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/mails.php');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status != 200) {
                    console.log("Votre message n'a pas pu être envoyé");
                }
            }
        }
        xhr.send(data);
    }
}
