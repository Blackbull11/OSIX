<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPIX - Mon Projet </title>
    <link rel="stylesheet" href="STYLE\EPIXStyle.css">
    <link rel="stylesheet" href="STYLE\HeaderStyle.css"/>
</head>
<body>

<?php

session_start();
try {
    // On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}

if (isset($_SESSION["project_id"])) {
    $query = 'SELECT * FROM projects WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute(['id' => $_SESSION["project_id"]]);
    $_SESSION['this_project'] = $stmt->fetch();
    include 'Includes/PHP/EPIX_content.php';
}

else {echo '<p>Vous devez vous connecter ou passer par un bouton classique.</p>';}

?>

<script src="Javascript.js"></script>

<script>

    function editLogo() {
        const previewLogo = document.getElementById('previewLogo');
        const logoInput = document.getElementById('logoInput');
        const logoDialog = document.getElementById('logoDialog');

        // Ouvrir la boîte de dialogue pour choisir un logo
        logoInput.value = ""; // Réinitialiser l'input de fichier
        previewLogo.src = "#"; // Réinitialiser la prévisualisation
        previewLogo.style.display = "none"; // Cacher l'image de prévisualisation
        logoDialog.style.display = "flex"; // Afficher la boîte de dialogue

        // Ajouter un événement pour prévisualiser l'image
        logoInput.addEventListener('change', function () {
            const file = logoInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewLogo.src = e.target.result; // Charger l'image choisie
                    previewLogo.style.display = "block"; // Afficher la prévisualisation
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function setLogo($id) {
        const logoInput = document.getElementById('logoInput');
        const logoDialog = document.getElementById('logoDialog');

        const file = logoInput.files[0];

        // Validation : Vérifier si un fichier est sélectionné
        if (!file) {
            alert("Veuillez choisir un fichier.");
            return;
        }

        // Validation : Size (Max 2 MB) et Format (JPEG, PNG uniquement)
        const validFormats = ["image/jpeg", "image/png", "image/webp"];
        if (!validFormats.includes(file.type)) {
            alert("Veuillez utiliser un fichier au format JPG, PNG ou WEBP.");
            return;
        }

        if (file.size > 2 * 1024 * 1024) { // 2 MB
            alert("La taille du fichier dépasse 2 Mo.");
            return;
        }
        // Préparer les données pour l'envoi
        const data = new FormData();
        data.append("project_id", $id);
        data.append("projectNewLogo", file);

        // Envoyer la requête au serveur avec AJAX
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Mettre à jour le logo sur la page si l'upload est réussi

                    // Fermer la boîte de dialogue
                    logoDialog.style.display = "none";
                    //mettre le logo à jour
                    const editLogo = document.getElementById('edit-logo');
                    const editButton = document.getElementById('logo-button');

                    editLogo.src = URL.createObjectURL(file);// Prévisualiser localement
                    editLogo.onload = () => {
                        URL.revokeObjectURL(previewLogo.src);
                    };
                    editButton.style.height = "60%";
                } else {
                    alert("Erreur lors de la mise à jour du logo. Veuillez réessayer.");
                }
            }
        };

        xhr.send(data); // Envoi de la requête


    }


    function editTitle() {
        const currentTitle = document.getElementById('projectTitle').textContent;
        const titleInput = document.getElementById('projectTitleInput');

        titleInput.value = currentTitle;
        document.getElementById('titleDialog').style.display = 'flex';
    }


    function setTitle($id) {
        const titleInput = document.getElementById('projectTitleInput');
        const title = titleInput.value.trim();

        if (!title || title.trim() === "") {
            alert('Veuillez entrer un titre valide.');
            return;        }
        else {    document.getElementById('projectTitle').textContent = title;}

        document.getElementById('titleDialog').style.display = 'none';

        var xhr = new XMLHttpRequest();
        data = new FormData();
        data.append("project_id",$id);
        data.append("project_new_name",title);

        xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
        xhr.send(data);
    }

    function saveComments($id) {
        const commentInput = document.getElementById('comments-box');
        var xhr = new XMLHttpRequest();
        data = new FormData();
        data.append("project_id",$id);
        data.append("project_new_comments", commentInput.value);
        xhr.onreadystatechange = function() { if (isRequestSuccessful(this)) {
            console.log('ok requête envoyée');
        } };
        xhr.open("POST", "Includes/PHP/EPIX_update_project.php");
        xhr.send(data);
    }

    function showDialog() {
        const saveDialog = document.getElementById('saveDialog');
        console.log(saveDialog);
        const newProjectTitleInput = document.getElementById('newProjectTitleInput');
        const currentTitle = document.getElementById('projectTitle').textContent;

        newProjectTitleInput.value = currentTitle;
        saveDialog.style.display = 'flex';
        console.log("dialog");
    }

    function isRequestSuccessful($xhr) {
        return $xhr.readyState === 4 && $xhr.status === 200;}

    function closeLogoDialog() {
        document.getElementById('logoDialog').style.display = 'none';
    }

    function closeSaveDialog() {
        document.getElementById('saveDialog').style.display = 'none';
    }

</script>



</body>

</html>
