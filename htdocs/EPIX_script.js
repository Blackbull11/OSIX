    let currentType = null; // Stock le type actuel (node ou edge)

 <!--GESTION DES CATEGORIES-->

    // Fonction pour afficher la modale add category
    function addCategory(type) {
    currentType = type; // Stocker le type ("node" ou "edge")
    document.getElementById("addCategoryModal").style.display = "block";

    // Afficher ou masquer la case "Relation orientée" en fonction du type
    if (type === "edge") {
    document.getElementById("isOrientedDiv").style.display = "block";
} else {
    document.getElementById("isOrientedDiv").style.display = "none";
}
    loadCategoriesClasses("selectCategoryClass");
}

    // Fonction pour fermer la modale add category
    function closeModal() {
    document.getElementById("addCategoryModal").style.display = "none";

    // Réinitialiser les valeurs
    document.getElementById("categoryName").value = "";
    document.getElementById("isOriented").checked = false;
}

    // Fonction pour soumettre la nouvelle catégorie
    function submitCategory(owner, common=false) {
    const categoryName = document.getElementById("categoryName").value.trim();
    const isOriented = document.getElementById("isOriented").checked ? 1 : 0;
    let select = null;

    // Vérifier si le nom de la catégorie a été saisi
    if (!categoryName) {
    alert("Veuillez entrer un nom de catégorie.");
    return;
}

    if (common === true)
    {
        select = document.getElementById(
            currentType === "node" ? "commonNodeCategorySelect" : "commonEdgeCategorySelect");
    }
    else {
        select = document.getElementById(
            currentType === "node" ? "nodeCategorySelect" : "edgeCategorySelect"
        );
    }

    // Construire les données pour la requête
    const categoryData = {
    action: "addCategory",
    type: currentType,
    category: {
    name: categoryName,
    owner: owner,
    class : document.getElementById("selectCategoryClass").value,
},
};
    // Inclure le champ "isOriented" uniquement pour les edges
    if (currentType === "edge") {
    categoryData.category.isOriented = isOriented;
    if (isOriented === 0) {
        categoryData.category.default_style = '{"type": "dashed", "thickness": 2, "arrowColor": "#000000", "segmentColor": "#000000"}';
    }
    else
    {
        categoryData.category.default_style = '{"type": "arrowed", "thickness": 2, "arrowColor": "#000000", "segmentColor": "#000000"}';
    }
    }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "Includes/PHP/EPIX_update_categories.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        // Gérer la réponse du serveur
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    // Ajouter l'option à la liste
                    const option = document.createElement('option');
                    option.value = this.responseText.trim(); // Réponse backend
                    option.textContent = categoryName;
                    select.appendChild(option);

                    // Fermer la modale
                    closeModal();
                } else {
                    console.error(`Erreur lors de la requête : statut ${xhr.status}`);
                }
            }
        };


    // Envoyer la requête
    xhr.send(JSON.stringify(categoryData));
}

    //Fonction pour afficher la modale editCategory
    function editCategory(type, common=false) {
    currentType = type; // Stocker le type ("node" ou "edge")
    let select = null;

    // Obtenir l'élément <select> correspondant
        if (common === true)
        {
            select = document.getElementById(
                currentType === "node" ? "commonNodeCategorySelect" : "commonEdgeCategorySelect");
        }
        else {
            select = document.getElementById(
                currentType === "node" ? "nodeCategorySelect" : "edgeCategorySelect"
            );
        }

        // Obtenir l'option sélectionnée (la catégorie active)
    const selectedOption = select.options[select.selectedIndex];
    if (!selectedOption || !selectedOption.value) {
    alert("Aucune catégorie sélectionnée à modifier.");
    return;
}

    if (selectedOption.value === '1')
    {
        alert("Vous ne pouvez pas modifier la catégorie Autre.");
        return;
    }

    // Lecture des informations associées à la catégorie
    const categoryName = selectedOption.textContent; // Nom de la catégorie

    // Pré-remplir les champs de la modale
    document.getElementById("editCategoryName").value = categoryName;

    if (type === "edge") {
    document.getElementById("editIsOrientedDiv").style.display = "block";
    document.getElementById("editIsOriented").checked = 1;
} else {
    document.getElementById("editIsOrientedDiv").style.display = "none";
}

    // Afficher la modale
    document.getElementById("editCategoryModal").style.display = "block";

    loadCategoriesClasses("selectNewCategoryClass");
}

    // Fonction pour fermer la modale edit category
    function closeEditModal() {
    document.getElementById("editCategoryModal").style.display = "none";

    // Réinitialiser les valeurs
    document.getElementById("editCategoryName").value = "";
    document.getElementById("editIsOriented").checked = false;
}

    // Fonction pour soumettre la modification de catégorie
    function submitEditCategory(common=false) {
    const newCategoryName = document.getElementById("editCategoryName").value.trim();

    if (!newCategoryName) {
    alert("Le nom de la catégorie ne peut pas être vide.");
    return;
}

        let select = null;

        // Obtenir l'élément <select> correspondant
        if (common === true)
        {
            select = document.getElementById(
                currentType === "node" ? "commonNodeCategorySelect" : "commonEdgeCategorySelect");
            selectedElementID = "node" ? "nodeCategorySelect" : "edgeCategorySelect";
        }
        else {
            select = document.getElementById(
                currentType === "node" ? "nodeCategorySelect" : "edgeCategorySelect"
            );
            selectedElementID = "node" ? "nodeCategorySelect" : "edgeCategorySelect";
        }

    // Envoi AJAX pour modifier la catégorie
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/EPIX_update_categories.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
    if (xhr.status === 200) {
    console.log('valeur retenue : ', select.value);
    console.log('select = ', select);
    const option = select.querySelector(`option[value="${select.value}"]`);
    if (option) {
    option.textContent = newCategoryName;
}

    // Fermer la modale
    closeEditModal();
} else {
    console.error(`Erreur lors de la requête : statut ${xhr.status}`);
}
}
};

    //ATTENTION MODIFIER CATEGORIE NOUVELLEMENT CREEE
        // ATENTION SUPPRESSION node pas ok mais edge ok ?

    // Préparer les données pour le backend
    const data = {
    action: "editCategory",
    type: currentType,
    category: {
    id: select.value,
    //id: document.getElementById(selectedElementID).value,
    newName: newCategoryName,
    class : document.getElementById("selectNewCategoryClass").value,
},
};

    // Inclure isOriented uniquement si le type est "edge"
    if (currentType === "edge") {
    data.category.isOriented = document.getElementById("editIsOriented").checked ? 1 : 0;
}

    // Envoyer les données
    xhr.send(JSON.stringify(data));
}

    //Fonction pour supprimer une catégorie
    function deleteCategory(type, common=false) {
        console.log("deleteCategory");
    let select = null;
    // Obtenir l'élément <select> correspondant
    if (common === true)
    {
        select = document.getElementById(
            type === "node" ? "commonNodeCategorySelect" : "commonEdgeCategorySelect");
    }
    else {
        select = document.getElementById(
            type === "node" ? "nodeCategorySelect" : "edgeCategorySelect"
        );
        console.log("attention voici l'option sélectionnée  : ", select);

    }
        console.log(select);

    // Vérifier si une option est sélectionnée
    if (select.selectedIndex === -1) {
    alert("Veuillez sélectionner une catégorie à supprimer.");
    return;
}

    const selectedOption = select.options[select.selectedIndex];
    const categoryId = selectedOption.value;
    const categoryName = selectedOption.textContent;

    // Confirmation de suppression
    if (!categoryId)
{
    alert("Veuillez choisir une catégorie à supprimer.");
    return;
}
    if (categoryId === '1')
    {
        alert("Vous ne pouvez pas supprimer la catégorie Autre.");
        return;
    }

    const confirmDeletion = confirm(`Êtes-vous sûr de vouloir supprimer "${categoryName}" ?`);
    if (!confirmDeletion) return;

    // Envoi AJAX pour supprimer la catégorie
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Includes/PHP/EPIX_update_categories.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
    if (xhr.status === 200) {

    // Supprimer l'option sélectionnée dans la liste
    select.removeChild(selectedOption);
} else {
    console.error(`Erreur lors de la requête : statut ${xhr.status}`);
}
}
};

    // Envoyer les données nécessaires
    const data = {
    action: "deleteCategory",
    type: type,
    category: {
    id: categoryId,
},};
    xhr.send(JSON.stringify(data));
}



    // Fonction pour afficher la modale add class
    function addClass(type) {
        currentType = type; // Stocker le type ("node" ou "edge")
        document.getElementById("addClassModal").style.display = "block";
    }
    // Fonction pour fermer la modale add class
    function closeClassModal() {
        document.getElementById("addClassModal").style.display = "none";

        // Réinitialiser les valeurs
        document.getElementById("className").value = "";
    }
    // Fonction pour soumettre la modification de classe
    function submitClass() {
        const className = document.getElementById("className").value.trim();
        let select = null;
        if (currentType === "edge")
        {
            select = "classEdgeCategorySelect"
        }
        else
        {
            select = "classNodeCategorySelect"
        }

        // Vérifier si le nom de la catégorie a été saisi
        if (!className) {
            alert("Veuillez entrer un nom de classe.");
            return;
        }

        // Construire les données pour la requête
        const classData = {
            action: "addClass",
            type: currentType,
            name: className,
        };

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "Includes/PHP/EPIX_update_categories.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        // Gérer la réponse du serveur
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    // Ajouter l'option à la liste en rechargeant les classes
                    loadCategoriesClasses(select)

                    // Fermer la modale
                    closeClassModal();
                } else {
                    console.error(`Erreur lors de la requête : statut ${xhr.status}`);
                }
            }
        };


        // Envoyer la requête
        xhr.send(JSON.stringify(classData));
    }
    // Fonction pour afficher la modale edit class

    function editClass(type) {
        currentType = type; // Stocker le type ("node" ou "edge")
        const select = document.getElementById(
            currentType === "node" ? "classNodeCategorySelect" : "classEdgeCategorySelect");

        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption.value === '1')
        {
            alert("Vous ne pouvez pas modifier la classe Autres.");
            return;
        }

        document.getElementById("editClassModal").style.display = "block";

        // Lecture des informations associées à la catégorie
        const className = selectedOption.textContent; // Nom de la catégorie

        // Pré-remplir les champs de la modale
        document.getElementById("editClassName").value = className;
    }
    // Fonction pour fermer la modale edit class
    function closeEditClassModal() {
        document.getElementById("editClassModal").style.display = "none";

        // Réinitialiser les valeurs
        document.getElementById("className").value = "";
    }
    // Fonction pour soumettre la modification du nom de la classe
    function submitEditClass() {

        console.log("ok edit class");

        const newClassName = document.getElementById("editClassName").value.trim();


        let select = null;
        if (currentType === "edge")
        {
            select = "classEdgeCategorySelect"
        }
        else
        {
            select = "classNodeCategorySelect"
        }

        // Vérifier si le nom de la catégorie a été saisi
        if (!newClassName) {
            alert("Veuillez entrer un nom de classe.");
            return;
        }

        // Construire les données pour la requête
        const classData = {
            action: "editClass",
            type: currentType,
            name: newClassName,
            id: document.getElementById(select).value,
        };
        console.log(" id à modifier : ", document.getElementById(select).value);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "Includes/PHP/EPIX_update_categories.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        // Gérer la réponse du serveur
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    // Ajouter l'option à la liste en rechargeant les classes
                    loadCategoriesClasses(select);
                    console.log(this.responseText);

                    // Fermer la modale
                    closeEditClassModal();
                } else {
                    console.error(`Erreur lors de la requête : statut ${xhr.status}`);
                }
            }
        };


        // Envoyer la requête
        xhr.send(JSON.stringify(classData));
    }

    //Fonction pour supprimer une classe
    function deleteClass(type) {
        console.log("deleteClass");
        let select = document.getElementById(
            type === "node" ? "classNodeCategorySelect" : "classEdgeCategorySelect");
        console.log(select);

        // Vérifier si une option est sélectionnée
        if (select.selectedIndex === -1) {
            alert("Veuillez sélectionner une classe à supprimer.");
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        const classId = selectedOption.value;
        const categoryName = selectedOption.textContent;

        // Confirmation de suppression
        if (!classId) {
            alert("Veuillez choisir une classe à supprimer.");
            return;
        };
        if (classId === '1')
        {
            alert("Vous ne pouvez pas supprimer la classe Autres.");
            return;
        }

        const confirmDeletion = confirm(`Êtes-vous sûr de vouloir supprimer "${categoryName}" ?`);
        if (!confirmDeletion) return;

        // Envoi AJAX pour supprimer la catégorie
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "Includes/PHP/EPIX_update_categories.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {

                    // Supprimer l'option sélectionnée dans la liste
                    select.removeChild(selectedOption);
                    console.log(this.responseText);
                } else {
                    console.error(`Erreur lors de la requête : statut ${xhr.status}`);
                }
            }
        };
        // Envoyer les données nécessaires
        const data = {
            action: "deleteClass",
            type: type,
            category: {
                id: classId,
            },};
        xhr.send(JSON.stringify(data));
    }

    function loadCategories() {
        // Effectuer une requête AJAX pour récupérer les catégories
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/EPIX_load_categories.php');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) { // Lorsque la requête est terminée
                if (xhr.status === 200) { // Vérifier que le statut est OK (200)
                    try {
                        const data = JSON.parse(xhr.responseText); // Parse la réponse en JSON

                        // Vider les éléments <select> avant d'ajouter les options
                        const edgeSelect = document.getElementById('edgeCategorySelect');
                        const nodeSelect = document.getElementById('nodeCategorySelect');

                        edgeSelect.innerHTML = '';
                        nodeSelect.innerHTML = '';

                        // Ajouter une option par défaut
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = 'Sélectionnez une catégorie';
                        edgeSelect.appendChild(defaultOption.cloneNode(true));
                        nodeSelect.appendChild(defaultOption.cloneNode(true));

                        // Ajouter les catégories dans les deux sélecteurs
                        data.nodesCategories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id; // Associer l'ID de la catégorie à la valeur
                            option.textContent = category.name; // Nom de la catégorie affiché
                            nodeSelect.appendChild(option.cloneNode(true));
                        });

                        data.relationsCategories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            edgeSelect.appendChild(option.cloneNode(true));
                        });
                    } catch (e) {
                        console.error('Erreur lors de l’analyse de la réponse JSON :', e);
                    }
                } else {
                    console.error('Erreur HTTP :', xhr.status);
                }
            }
        };

        data = new FormData();
        data.append('action', 'loadCategories');

        // Envoyer la requête
        xhr.send(data);
    }

    function loadCommonCategories() {
        // Effectuer une requête AJAX pour récupérer les catégories communes
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/EPIX_load_categories.php', true);

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) { // Lorsque la requête est terminée
                if (xhr.status === 200) { // Vérifier que le statut HTTP est OK (200)
                    try {
                        // Analyser la réponse JSON reçue
                        const data = JSON.parse(xhr.responseText);

                        // Cibler les éléments <select>
                        const edgeSelect = document.getElementById('commonEdgeCategorySelect');
                        const nodeSelect = document.getElementById('commonNodeCategorySelect');

                        // Vider le contenu actuel des <select>
                        edgeSelect.innerHTML = '';
                        nodeSelect.innerHTML = '';

                        // Ajouter une option par défaut (si nécessaire)
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = 'Sélectionnez une catégorie';
                        edgeSelect.appendChild(defaultOption.cloneNode(true));
                        nodeSelect.appendChild(defaultOption.cloneNode(true));

                        // Ajouter les catégories communes pour les nœuds
                        data.commonNodesCategories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id; // Valeur de l'ID de la catégorie
                            option.textContent = category.name; // Nom affiché
                            option.setAttribute('data-name', category.name); // Attribut data-name
                            nodeSelect.appendChild(option);
                        });

                        // Ajouter les catégories communes pour les relations
                        data.commonRelationsCategories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            option.setAttribute('data-name', category.name); // Attribut data-name
                            if (category.isOriented !== undefined) {
                                option.setAttribute('data-oriented', category.isOriented); // Orientation
                            }
                            edgeSelect.appendChild(option);
                        });

                    } catch (e) {
                        console.error('Erreur lors de l’analyse de la réponse JSON :', e);
                    }
                } else {
                    console.error('Erreur HTTP :', xhr.status); // Gérer les erreurs HTTP
                }
            }
        };

        // Préparer les données pour la requête
        const data = new FormData();
        data.append('action', 'loadCommonCategories');

        // Envoyer la requête
        xhr.send(data);
        }


    //Fonction pour charger les classes de catégories
    function loadCategoriesClasses(element) {
        const data = new FormData();

        if (currentType === "edge")
        {
            data.append("action", "loadRelationCategoriesClasses");
        }
        else if (currentType === "node")
        {
            data.append("action", "loadNodeCategoriesClasses");
        }

        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    document.getElementById(element).innerHTML = xhr.responseText;
                }
            }
        }
        xhr.open('POST', 'Includes/PHP/EPIX_load_categories.php');
        xhr.send(data);
    };