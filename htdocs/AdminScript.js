xhreq = new XMLHttpRequest();
xhreq.onreadystatechange = function () {
    if (xhreq.readyState == 4 && xhreq.status == 200) {
        document.getElementById("results-tbody").innerHTML =
            this.responseText;
    }
}
xhreq.open("POST", "Includes/PHP/BOX_ADMIN_bdd.php", true);
xhreq.send();

// Attach event listeners to filters
document.getElementById("checkbox-mine").addEventListener("change",
    toggleConflictCheckboxes);
document.getElementById("checkbox-hidden").addEventListener("change",
    toggleConflictCheckboxes);
document.getElementById("start-date").addEventListener("change",
    updateResults);
document.getElementById("end-date").addEventListener("change",
    updateResults);
document.getElementById("search-bar").addEventListener("input",
    updateResults);
document.getElementById("sort_selection").addEventListener("change",
    updateResults);

document.querySelectorAll("#category-checkboxes input").forEach(checkbox=> {
    checkbox.addEventListener("change", function () {
        toggleSubCategories(this.value); // Utilise la valeur de la checkbox cliquée
    });
});
document.querySelectorAll("#checkboxes input").forEach(checkbox => {
    checkbox.addEventListener("change", updateResults);
});


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
    xhr.open("POST", "Includes/PHP/BOX_ADMIN_bdd.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("results-tbody").innerHTML =
                xhr.responseText;
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
        recommended:
        document.getElementById("checkbox-recomended").checked,
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
        const targetContent =
            document.querySelector(`#${this.dataset.tab}`);
        targetContent.classList.add("active-content");
    });
});

function BOX_Gest_Failure_Message($string) {
    console.log('Failure_Message(' + $string +') a été appelé.')
    const message = document.getElementById('BOX-Gest-failure-message');
    if (message) {
        message.style.opacity = '1';
        message.innerHTML = $string;
        message.style.display = 'flex';

        setTimeout(() => {
            setTimeout(() => message.style.display = 'none', 500); //Masquer complètement après la transition
        }, 3000);
    }
}

function Update_Tool_Recommended(checkbox, toolId) {
    // Préparer les données à envoyer
    const isChecked = checkbox.checked ? 1 : 0;

    // Effectuer une requête AJAX pour mettre à jour l'état recommandé de l'outil
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/BOX_ADMIN_bdd.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

    xhr.onload = function () {
        if (xhr.status === 200) {
            // Vérifier la réponse success/failure
            console.log('Mise à jour réussie pour tool ID:', toolId,
                'Recommandé:', isChecked);
            console.log(xhr.responseText);
        } else {
            // Échec de la mise à jour
            console.error("Erreur lors de la mise à jour de l'outil recommandé :", xhr.responseText);
            // Revenir à l'état précédent en cas d'échec
            checkbox.checked = !isChecked;
        }
    };

    xhr.onerror = function () {
        console.error("Erreur réseau ou problème de communication.");
        // Revenir à l'état précédent
        checkbox.checked = !isChecked;
    };
    data = new FormData();
    data.append('toolId', toolId);
    data.append("recommendation", isChecked)
    // Envoi des données sous forme JSON
    xhr.send(data);
}

function submitUserForm(userId) {
    const form = document.getElementById('users-form');
    const formData = new FormData();

    // Récupérez les inputs de l'utilisateur spécifique
    const usernameInput =
        form.querySelector(`input[name="username[${userId}]"]`);
    const passwordInput =
        form.querySelector(`input[name="password[${userId}]"]`);
    const groupInputs =
        form.querySelectorAll(`input[name="groups[${userId}][]"]:checked`);

    // Ajoutez les données dans formData
    formData.append("user_id", userId);
    formData.append("username", usernameInput.value);
    formData.append("password", passwordInput.value);

    const groups = [];
    groupInputs.forEach(input => groups.push(input.value));
    formData.append("groups", JSON.stringify(groups));

    // Effectuez la requête AJAX
    fetch("Includes/PHP/update_user.php", {
        method: "POST",
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Modifications enregistrées !");
            } else {
                alert("Erreur : " + data.error);
            }
        })
        .catch(error => {
            console.error("Une erreur s'est produite :", error);
        });
}

function togglePasswordVisibility(userId) {
    const passwordInput =
        document.getElementsByName(`password[${userId}]`)[0];

    // Alterne le type de l'input entre "password" et "text"
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
    } else {
        passwordInput.type = "password";
    }
}
document.addEventListener('DOMContentLoaded', () => {

    // Sélectionnez toutes les lignes du tableau
    const tableRows = document.querySelectorAll('.adminTable tbody tr');

    // Appliquez un écouteur d'événements à chaque tr
    tableRows.forEach((row) => {
        // Écouteur d'événements pour les inputs/textareas
        row.addEventListener('input', () => {
            row.classList.add('row-modified'); // Ajoute la classe "row-modified" sur modification
        });

        // Pour les autres interactions comme clics sur des boutons
        row.addEventListener('click', (event) => {
            // Ne pas redéclencher si c'est un lien ou un élément interactif spécifique
            if (event.target.tagName !== 'BUTTON' &&
                event.target.tagName !== 'INPUT') return;
            console.log("Modifiée");
            row.classList.add('row-modified'); // Ajoute la classe
            "row-modified"
        });
    });

    // Chargement du tableau pour les tags
    data = new FormData();
    data.append('action', 'loadTagTable')
    let xhr = new XMLHttpRequest();
    xhr.open('POST', 'Includes/PHP/tagTableBdd.php');
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                document.getElementById('tags-tbody').innerHTML =
                    xhr.responseText;
            }
        }
    }
    xhr.send(data);

    // Chargement du tableau pour les TOOLtags
    data = new FormData();
    data.append('action', 'loadToolTagTable')
    let xhr2 = new XMLHttpRequest();
    xhr2.open('POST', 'Includes/PHP/tagTableBdd.php');
    xhr2.onreadystatechange = function() {
        if (xhr2.readyState == 4) {
            if (xhr2.status == 200) {
                document.getElementById('toolTags-tbody').innerHTML = xhr2.responseText;
            }
        }
    }
    xhr2.send(data);

    // Chargement du tableau pour les TOOLtags
    data = new FormData();
    data.append('action', 'loadTypeTable')
    let xhr3 = new XMLHttpRequest();
    xhr3.open('POST', 'Includes/PHP/tagTableBdd.php');
    xhr3.onreadystatechange = function() {
        if (xhr3.readyState == 4) {
            if (xhr3.status == 200) {
                document.getElementById('types-tbody').innerHTML =
                    xhr3.responseText;
            }
        }
    }
    xhr3.send(data);

    document.getElementById('addTagButton').onclick = function() {
        console.log('add tag button clicked');
        var tagName = document.getElementById('newTagInput').value;
        var tagType = document.getElementById('newTagInputType').value;
        data = new FormData();
        data.append('action', 'addTag');
        data.append('tagName', tagName);
        data.append('tagType', tagType);

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/tagTableBdd.php');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    window.location.reload();
                }
            }
        }
        xhr.send(data);
    }

    document.getElementById('addToolTagButton').onclick = function() {
        console.log('add tooltag button clicked');
        var tagName = document.getElementById('newToolTagInput').value;
        var tagType = document.getElementById('newToolTagInputType').value;
        data = new FormData();
        data.append('action', 'addToolTag');
        data.append('tagName', tagName);
        data.append('tagType', tagType);
        console.log('add tag value ', tagName);

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/tagTableBdd.php');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    window.location.reload();
                }
            }
        }
        xhr.send(data);
    }

    document.getElementById('addTypeButton').onclick = function() {
        console.log('add type button clicked');
        var typeParent = document.getElementById('typeParentInput').value;
        var typeName = document.getElementById('typeNameInput').value;
        var typeSelected = document.getElementById('typeSelect').value;
        data = new FormData();
        data.append('action', 'addType');
        data.append('typeName', typeName);
        data.append('typeParent', typeParent);
        data.append('typeSelected', typeSelected);

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/tagTableBdd.php');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    window.location.reload();
                }
            }
        }
        xhr.send(data);
    }

    document.getElementById('confirmTagButton').onclick = function() {
        let data = new FormData();
        data.append('action', 'adminGroups')
        data.append('tagGroupsJson', getCheckboxJson(true));
        data.append('tagNamesJson', getNameJson("tagNameInput_"));
        data.append('tagTypeJson', getNameJson('tagTypeInput_'));
        console.log(data, ' pour source tags');
        xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/tagTableBdd.php');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    alert("Votre modification a été enregistrée");
                }
                else {
                    alert("Votre modification n'a pas été enregistrée.")
                }
            }
        }
        xhr.send(data);
    };

    document.getElementById('confirmTypeButton').onclick = function() {
        let data = new FormData();
        data.append('action', 'adminTypes')
        data.append('typeNames', getNameJson("typeNameInput_"));
        data.append('typeParents', getNameJson('typeParentInput_'));
        data.append('typeEndpoints', getSelectedJson('typeSelect_'));

        console.log(data, ' pour types');
        xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/tagTableBdd.php');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    alert("Votre modification a été enregistrée");
                }
                else {
                    alert("Votre modification n'a pas été enregistrée.")
                }
            }
        }
        xhr.send(data);
    };

    document.getElementById('confirmToolTagButton').onclick =
        function() {
            let data = new FormData();
            data.append('action', 'adminToolGroups')
            data.append('toolTagNames', getNameJson('toolTagNameInput_'));
            data.append('toolTagTypes', getNameJson('toolTagTypeInput_'))
            console.log(data, ' pour tool tags');
            xhr = new XMLHttpRequest();
            xhr.open('POST', 'Includes/PHP/tagTableBdd.php');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        alert("Votre modification a été enregistrée");
                    }
                    else {
                        alert("Votre modification n'a pas été enregistrée.")
                    }
                }
            }
            xhr.send(data);
        };

    // Charger les options de tag dans la liste déroulante
    function loadTagsForDeletion() {
        const selectTag = document.getElementById('deleteTagSelect');

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/tagTableBdd.php');
        xhr.setRequestHeader('Content-Type',
            'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                selectTag.innerHTML = xhr.responseText; // Mettre à jour la liste des options
            }
        };

        xhr.send('action=loadTagsSelect');
    }

    // Charger les options de tag dans la liste déroulante des outils box
    function loadToolTagsForDeletion() {
        const selectTag = document.getElementById('deleteToolTagSelect');

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/tagTableBdd.php');
        xhr.setRequestHeader('Content-Type',
            'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                selectTag.innerHTML = xhr.responseText; // Mettre à jour la liste des options
            }
        };

        xhr.send('action=loadToolTagsSelect');
    }

    // Charger les options de types pour les sources glorix
    function loadTypesForDeletion() {
        const selectType = document.getElementById('deleteTypeSelect');
        const selectNewType = document.getElementById('newTypeSelect');

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/tagTableBdd.php');
        xhr.setRequestHeader('Content-Type',
            'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                selectType.innerHTML = xhr.responseText; // Mettre à jour la liste des options
                selectNewType.innerHTML = xhr.responseText;
            }
        };
        xhr.send('action=loadTypeSelect');
    }

    // Charger les tags à la première exécution
    loadTagsForDeletion();
    loadToolTagsForDeletion();
    loadTypesForDeletion();

    // Gestion de la soumission du formulaire de suppression
    document.getElementById('deleteTagForm').addEventListener('submit',
        function (e) {
            e.preventDefault();
            const tagId = document.getElementById('deleteTagSelect').value;

            if (confirm('Êtes-vous sûr de vouloir supprimer ce tag ?')) {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'Includes/PHP/tagTableBdd.php', true);
                xhr.setRequestHeader('Content-Type',
                    'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert(xhr.responseText);
                        window.location.reload();
                    }
                };
                xhr.send(`action=deleteTag&tagId=${tagId}`);
            }
        });

    // Gestion de la soumission du formulaire de suppression
    document.getElementById('deleteToolTagForm').addEventListener('submit',
        function (e) {
            e.preventDefault();
            const tagId = document.getElementById('deleteToolTagSelect').value;

            if (confirm('Êtes-vous sûr de vouloir supprimer ce tooltag ?')) {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'Includes/PHP/tagTableBdd.php', true);
                xhr.setRequestHeader('Content-Type',
                    'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert(xhr.responseText);
                        window.location.reload();                }
                };
                xhr.send(`action=deleteToolTag&tagId=${tagId}`);
            }
        });

    // Gestion de la soumission du formulaire de suppression
    document.getElementById('deleteTypeForm').addEventListener('submit',
        function (e) {
            e.preventDefault();
            const typeId = document.getElementById('deleteTypeSelect').value;
            const newTypeId = document.getElementById('newTypeSelect').value;

            if (typeId == newTypeId) {
                window.alert("Veuillez choisir un type pour le remplacement différent de celui que vous supprimez.");
                return;
            }
            if (confirm('Êtes-vous sûr de vouloir supprimer ce type ?')) {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'Includes/PHP/tagTableBdd.php', true);
                xhr.setRequestHeader('Content-Type',
                    'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert(xhr.responseText);
                        window.location.reload();
                    }
                };
                xhr.send(`action=deleteType&typeId=${typeId}&newTypeId=${newTypeId}`);
            }
        });

    //Pour afficher en rouge foncé le type à détruire.
    document.getElementById('deleteTypeSelect').addEventListener('change',
        () => {
            const selectElement = document.getElementById('deleteTypeSelect');
            const selectedIndex = selectElement.selectedIndex;

            // Réinitialiser toutes les lignes (enlever la couleur)
            for (let i = 0; i < selectElement.options.length; i++) {
                const row = document.getElementById('typeNameInput_' +
                    selectElement.options[i].value);
                if (row && row.style.backgroundColor != 'aquamarine') {
                    row.style.backgroundColor = ''; // Supprime la couleur
                }
            }

            // Appliquer le fond rouge à la ligne sélectionnée
            const selectedRow = document.getElementById('typeNameInput_' +
                selectElement.options[selectedIndex].value);
            if (selectedRow) {
                selectedRow.style.backgroundColor = 'brown';
            }

            console.log('ok : ', selectedRow);
        });

    //Pour afficher en bleu clair le type éventuel de remplacement.
    document.getElementById('newTypeSelect').addEventListener('change', () => {
        const selectElement = document.getElementById('newTypeSelect');
        const selectedIndex = selectElement.selectedIndex;

        // Réinitialiser toutes les lignes (enlever la couleur)
        for (let i = 0; i < selectElement.options.length; i++) {
            const row = document.getElementById('typeNameInput_' +
                selectElement.options[i].value);
            if (row && row.style.backgroundColor != 'brown') {
                row.style.backgroundColor = ''; // Supprime la couleur
            }
        }

        // Appliquer le fond rouge à la ligne sélectionnée
        const selectedRow = document.getElementById('typeNameInput_' +
            selectElement.options[selectedIndex].value);
        if (selectedRow) {
            selectedRow.style.backgroundColor = 'aquamarine';
        }

        console.log('ok : ', selectedRow);
    });

});

//bool = true si on cherche pour les source tags, sinon pour box tags.
function getCheckboxJson()
{
    let M = {};
    const initWord ="tagCheckbox_";
    document.querySelectorAll(`input[type="checkbox"][id^="${initWord}"]`).forEach(checkbox=> {

        // On a en effet les id de la forme tagCheckbox_tag['id']_group[id]
        let match = checkbox.id.match(/^tagCheckbox_(\d+)_(\d+)$/);
        if (match) {
            let K = parseInt(match[1], 10);
            let L = parseInt(match[2], 10);
            if (!M[K]) {
                M[K] = Array();
            }
            if (checkbox.checked)
            {
                M[K].push(L);
            }
        }

    } )

    return JSON.stringify(M);
}

function getNameJson(initWord) {
    let Names = {};

    let tagInputs =
        document.querySelectorAll(`input[type="text"][id^="${initWord}"]`);
    // Escape special characters in initWord
    let escapedInitWord = initWord.replace(/[.*+?^${}()|[\]\\]/g,
        '\\$&');

    // Construct the regular expression using the RegExp constructor
    let regex = new RegExp(`^(${escapedInitWord})(\\d+)$`);

    tagInputs.forEach(tagInput => {
        let match = tagInput.id.match(regex);
        console.log('parcours ok pour ', tagInput, `avec`,
            escapedInitWord);
        if (match) {
            let tagId = parseInt(match[2], 10);
            console.log('tag id identifié : ', tagId);
            Names[tagId] = tagInput.value;
        }
    })
    return JSON.stringify(Names);
}


function getSelectedJson(initWord) {
    let CategoriesObject = {};

    let items = document.querySelectorAll(`select[id^="${initWord}"]`);
    // Escape special characters in initWord
    let escapedInitWord = initWord.replace(/[.*+?^${}()|[\]\\]/g,
        '\\$&');

    // Construct the regular expression using the RegExp constructor
    let regex = new RegExp(`^(${escapedInitWord})(\\d+)$`);

    items.forEach(v => {
        let match = v.id.match(regex);
        console.log('parcours ok pour ', v, `avec`, escapedInitWord);
        if (match) {
            let tagId = parseInt(match[2], 10);
            console.log('tag id identifié : ', tagId);
            CategoriesObject[tagId] = v.value;
        }
    })
    return JSON.stringify(CategoriesObject);
}