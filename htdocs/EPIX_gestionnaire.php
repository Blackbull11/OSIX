<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPIX Gestionnaire</title>
    <link rel="icon" href="Includes\Images\Icone Onglet.png" />
    <link rel="stylesheet" href="STYLE\HeaderStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexEPIXStyle.css"/>
    <link rel="stylesheet" href="STYLE\EPIXstyle.css"/>
</head>
<body>

<?php require_once 'Includes/PHP/header.php';

try {
    // On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}
?>

<?php
if (isset($_SESSION['id_actif'])) {
    echo '
    <div class="EPIX-projects" style="display: flex; margin: 0 5vw">
    <div style="width: 50%">
    <div>
    <div style="display: flex; justify-content: space-around; margin-bottom: 10px; align-items: center;">
    <h3>Mes Projets</h3>
    <button class="new-button" onclick="newEpixProject()">Nouveau projet</button>

    <!-- Modal new project -->
<div id="newProjectModal" class="modal-container" style="display: none;">
    <div class="modal">
        <h2>Créer un nouveau projet</h2>

        <form id="newProjectForm">
            <!-- Titre -->
            <div class="form-group">
                <label for="projectTitle">Titre du projet :</label>
                <input type="text" id="projectTitle" name="projectTitle" placeholder="Titre du projet" required>
            </div>

            <!-- Commentaire -->
            <div class="form-group">
                <label for="projectComment">Commentaire :</label>
                <textarea id="projectComment" name="projectComment" rows="4" placeholder="Ajoutez un commentaire (facultatif)"></textarea>
            </div>

            <!-- Icône -->
            <div class="form-group">
                <label for="projectIcon">Icône du projet :</label>
                <input type="file" id="projectIcon" name="projectIcon" accept="image/*">
                <small>Choisissez une image pour représenter votre projet.</small>
            </div>

            <!-- Boutons -->
            <div class="form-actions">
                <button type="button" onclick="closeNewProjectModal()">Annuler</button>
                <button type="submit">Créer</button>
            </div>
        </form>
    </div>
</div>

    <!-- Modal openShare -->
<div id="shareModal" class="modal-container" style="display: none;">
    <div class="modal">
        <h2>Partager le projet</h2>

        <form id="shareProjectForm">
            <!-- Titre -->
            <div id="sharePeopleSelect" class="form-group">
            </div>

            <!-- Boutons -->
            <div class="form-actions">
                <button type="button" onclick="closeShareModal()">Annuler</button>
                <button type="submit">Partager</button>
            </div>
        </form>
    </div>
</div>

    </div>';

    $query = "SELECT * FROM projects WHERE owner = :user order by last_use desc";
    $stmt = $bdd->prepare($query);
    $stmt->execute(['user' => $_SESSION['id_actif']]);
    $projects = $stmt->fetchAll();

    foreach ($projects as $project) {
        echo '
        <div style="display: flex; margin-bottom: 10px; align-items: center;">
            <a class="EPIX-project" style="width: 70%" onclick= "redirection(' . $project['id'] .' )" title="' . $project["comments"] . '">
            <img class="to_defilter" style="width:30%" src="' . $project["image"] . '">
            <div class="EPIX-project-text" style="width:80%">
                <div class="EPIX-project-title">' . $project["name"] . '</div>
                <div class="EPIX-project-date">' . $project["last_use"] . '</div>
            </div>
            </a>

            <button onclick="openShare('. $project["id"] .' )" class="export-btn" style="background-color: darkolivegreen">Partager</button>
            <button onclick="deleteProject('. $project["id"] .' )" class="export-btn" style="background-color: brown">Supprimer</button>
        </div>
            ';}
    echo '</div>';

    echo '
    <div>
        <h3>Projets partagés avec moi</h3>';

        $query = 'SELECT sharelist, id, comments, image, name, last_use FROM projects WHERE (owner <> :user AND owner <> -1) AND (sharelist IS NOT NULL AND sharelist <> \'{}\') ORDER BY last_use DESC LIMIT 3';
        $stmt = $bdd->prepare($query);
        $stmt -> execute(array(':user' => $_SESSION['id_actif']));
        $datas = $stmt->fetchAll();
        foreach ($datas as $data) {
            $list = json_decode($data['sharelist'], true);
            if (array_key_exists($_SESSION['id_actif'], $list) && $list[$_SESSION['id_actif']] === true)
            {
                echo '<a class="EPIX-project" onclick= "redirection(' . $data['id'] .' )" title="' . $data["comments"] . '">
                <img class="to_defilter" style="width:20%" src="' . $data["image"] . '">
                <div class="EPIX-project-text" style="width:80%">
                    <div class="EPIX-project-title">' . $data["name"] . '</div>
                    <div class="EPIX-project-date">' . $data["last_use"] . '</div>
                </div>
                </a>
                ';            }
        }

    echo '</div>
</div>
<div style="display: flex; flex-direction: column; width: 60%">

<div style="display: flex; justify-content: space-around; margin-bottom: 3vh;">
<div id="managementNodeCategory" class="interactivePanel" style="height: max-content">
    <h3>Gestion de vos catégories d\'entité</h3>
    <!-- Bouton ajouter -->
    <button style="margin: 10px 0;" onclick="addCategory(\'node\')">Ajouter une catégorie</button>
    <!-- Bouton modifier -->
    <button style="margin: 10px 0;" onclick="editCategory(\'node\')">Modifier une catégorie</button>
    <!-- Bouton supprimer -->
    <button style="margin: 10px 0;" onclick="deleteCategory(\'node\')">Supprimer une catégorie</button>

    <!-- Liste des catégories (dropdown) -->
<select id="nodeCategorySelect" style="width: 100%; margin-top: 10px;">
    <option value="" disabled selected>Choisir une catégorie</option>
</select>
</div>

<div id="managementEdgeCategory" class="interactivePanel" style="height: max-content">
    <h3>Gestion de vos catégories de relation</h3>
    <!-- Bouton ajouter -->
    <button style="margin: 10px 0;" onclick="addCategory(\'edge\')">Ajouter une catégorie</button>
    <!-- Bouton modifier -->
    <button style="margin: 10px 0;" onclick="editCategory(\'edge\')">Modifier une catégorie</button>
    <!-- Bouton supprimer -->
    <button style="margin: 10px 0;" onclick="deleteCategory(\'edge\')">Supprimer une catégorie</button>

    <!-- Liste des catégories (dropdown) -->
    <select id="edgeCategorySelect" style="width: 100%; margin-top:  10px;">
        <option value="" disabled selected>Choisir une catégorie</option>
        <!-- Options ajoutées dynamiquement -->
    </select>
</div>
</div>

<div style="display: flex; justify-content: space-around; margin-bottom: 3vh;">
<div id="managementCommonNodeCategory" class="interactivePanel" style="height: max-content">
    <h3>Catégories publiques d\'entité</h3>
    <!-- Liste des catégories (dropdown) -->
<select id="commonNodeCategorySelect" style="width: 100%; margin-top: 10px;">
    <option value="" disabled selected>Choisir une catégorie</option>
</select>
</div>
<div id="managementCommonEdgeCategory" class="interactivePanel" style="height: max-content">
    <h3>Catégories publiques de relations</h3>
    <!-- Liste des catégories (dropdown) -->
<select id="commonEdgeCategorySelect" style="width: 100%; margin-top: 10px;">
    <option value="" disabled selected>Choisir une catégorie</option>
</select>
</div>
</div>

<!-- Modale pour ajouter une catégorie -->
<div id="addCategoryModal" style="display: none; position: fixed; z-index: 1000; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="position: relative; margin: 10% auto; padding: 20px; width: 30%; background-color: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); text-align: left;">
        <h3>Ajouter une catégorie</h3>
        <label for="categoryName">Nom de la catégorie :</label>
        <input type="text" id="categoryName" placeholder="Nom de la catégorie" style="width: 100%; padding: 8px; margin: 10px 0;" />

        <select id="selectCategoryClass">Classe de la catégorie</select>

        <div id="isOrientedDiv" style="display: none; margin: 10px 0;">
            <label for="isOriented">
                <input type="checkbox" id="isOriented"> Relation orientée ?
            </label>
        </div>

        <div style="margin-top: 15px; text-align: right;">
            <button onclick="closeModal()" style="background-color: #d9534f; color: white; border: none; padding: 8px 16px; cursor: pointer;">Annuler</button>
            <button onclick="submitCategory('.  $_SESSION['id_actif']  .')" style="background-color: #5cb85c; color: white; border: none; padding: 8px 16px; cursor: pointer;">Ajouter</button>
        </div>
    </div>
</div>

<!-- Modale pour modifier une catégorie -->
<div id="editCategoryModal" style="display: none; position: fixed; z-index: 1000; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="position: relative; margin: 10% auto; padding: 20px; width: 30%; background-color: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); text-align: left;">
        <h3>Modifier une catégorie</h3>
        <label for="editCategoryName">Nom de la catégorie :</label>
        <input type="text" id="editCategoryName" placeholder="Nom de la catégorie" style="width: 100%; padding: 8px; margin: 10px 0;" />

        <select id="selectNewCategoryClass">Classe de la catégorie</select>

        <div id="editIsOrientedDiv" style="display: none; margin: 10px 0;">
            <label for="editIsOriented">
                <input type="checkbox" id="editIsOriented"> Relation orientée ?
            </label>
        </div>

        <div style="margin-top: 15px; text-align: right;">
            <button onclick="closeEditModal()" style="background-color: #d9534f; color: white; border: none; padding: 8px 16px; cursor: pointer;">Annuler</button>
            <button onclick="submitEditCategory()" style="background-color: #5cb85c; color: white; border: none; padding: 8px 16px; cursor: pointer;">Enregistrer</button>
        </div>
    </div>
</div>

';

        echo "
        <script src='Javascript.js'></script>
        <script src='EPIX_script.js'></script>
        <script>
   selectedProject = null;

    function newEpixProject() {
    // Active le modal pour créer un nouveau projet
    const modal = document.getElementById('newProjectModal');
    modal.style.display = 'flex';
}

    function closeNewProjectModal() {
    // Ferme le modal
    const modal = document.getElementById('newProjectModal');
    modal.style.display = 'none';
}
    function closeShareModal() {
    const modal = document.getElementById('shareModal');
    modal.style.display = 'none';
}

    // Gestion de la soumission du formulaire
    document.getElementById('newProjectForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Empêche la soumission normale du formulaire

    // Récupération des données
    const title = document.getElementById('projectTitle').value.trim();
    const comment = document.getElementById('projectComment').value.trim();
    const icon = document.getElementById('projectIcon').files[0]; // Fichier sélectionné

    // Validation basique
    if (!title) {
    alert('Le titre du projet est obligatoire.');
    return;
}

    // Création de l'objet FormData pour envoyer les données au serveur
    const formData = new FormData();
    formData.append('action', 'createProject');
    formData.append('title', title);
    formData.append('comment', comment);
    if (icon) {
    formData.append('icon', icon);
}

    // Requête AJAX pour soumettre les données au serveur
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'Includes/PHP/EPIX_new_project.php', true);

    xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
    if (xhr.status === 200) {
    console.log('Projet créé avec succès :', xhr.responseText);
    alert('Le projet a été créé avec succès.');
    closeNewProjectModal();
    redirection(this.responseText)
} else {
    console.error('Erreur lors de la création du projet :', xhr.status);
    alert('Une erreur est survenue lors de la création du projet.');
}
}
};

    xhr.send(formData); // Envoi des données au serveur
});

    function openShare(idProject) {
    const modal = document.getElementById('shareModal');
    modal.style.display = 'flex';
    selectedProject = idProject;

    data = new FormData();
    data.append('idProject', idProject);

    xhr = new XMLHttpRequest();
    xhr.open('POST', 'Includes/PHP/EPIX_share.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                document.getElementById('sharePeopleSelect').innerHTML = xhr.responseText;
            }
        }
    }

    xhr.send(data);

}

    // Gestion de la soumission du formulaire
    document.getElementById('shareProjectForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Empêche la soumission normale du formulaire

    // Récupération des données
    const who = document.getElementById('sharePeople').value;
    const noWho = document.getElementById('noSharePeople').value;

    // Validation basique
    if (who === '' && noWho === '') {
    alert('Veuillez sélectionner une personne. (L\'ensemble vide n\'en est pas une).');
    return;
}

    // Création de l'objet FormData pour envoyer les données au serveur
    const formData = new FormData();
    formData.append('action', 'shareProject');
    formData.append('who', who);
    formData.append('noWho', noWho);
    formData.append('project_id', selectedProject);

    // Requête AJAX pour soumettre les données au serveur
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'Includes/PHP/EPIX_update_project.php', true);

    xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
    if (xhr.status === 200) {
    closeShareModal();
    alert('Les modifications ont été appliquées avec succès.');
} else {
    console.error('Erreur lors de la création du projet :', xhr.status);
    alert('Une erreur est survenue lors de la création du projet.');
}
}
};

    xhr.send(formData); // Envoi des données au serveur
});

    function deleteProject(id)
    {
        const confirmation = window.confirm(`Êtes-vous sûr de vouloir supprimer ce projet ? Cette action est (presque) irréversible.`);
        // Si l'utilisateur confirme, on procède à la suppression
        if (confirmation) {
            let data = new FormData();
            data.append('project_id', id);
            data.append('action', 'deleteProject');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'Includes/PHP/EPIX_update_project.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        alert('Le projet a bien été supprimé.');
                        location.reload(true);
                        console.log(this.responseText);
                        return;
                    }
                }
            }
            xhr.send(data);
        }
        else
            {return;}

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


    loadCategories();
    loadCommonCategories();
</script>
        ";

}

else {echo '<p>Cette page exposera les projets existants après votre authentification.</p>';};

?>

</body>
</html>
