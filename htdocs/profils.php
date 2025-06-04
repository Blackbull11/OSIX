<!--Charge la base de données-->
<?php
try {
    // On se connecte à MySQL
$bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {// En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil OSIX</title>
    <link rel="icon" href="Includes\Images\Icone Onglet.png" />
    <link rel="stylesheet" href="STYLE\IndexStyle.css">
    <link rel="stylesheet" href="STYLE\HeaderStyle.css">
    <link rel="stylesheet" href="STYLE\ProfilesStyle.css">
</head>

<body>
<?php require_once 'Includes\PHP\header.php';
if (isset($_SESSION['must-login'])) {
    echo '<div id="demo-message" style = "margin-bottom: 5em">Veuillez-vous connecter avant de commencer votre activité !</div>';
    unset($_SESSION['must-login']);
}
require_once 'log_form.php'; ?>


<?php if (isset($_SESSION['id_actif'])) {
    echo '<div class="profile-content">';
    echo '<h1>Mon profil</h1>';
    if (isset($_SESSION['id_actif'])) {
        $req = $bdd->prepare('SELECT * FROM users WHERE id = ?');
        $req->execute([$_SESSION['id_actif']]);
        $user = $req->fetch();
        echo 'Nom d\'utilisateur : '. $user["username"];
        echo '<br>';
        echo 'Date de création du compte : '. $user["creationdate"];
    }
    ?>
        <p><i>Pour modifier votre mot de passe, veuillez vous adresser à un administrateur</i></p>
<br>
<!-- Bouton pour changer le mot de passe -->

<!-- Liste des groupes avec bouton pour ajouter/enlever des groupes -->
<h3>Mes Groupes</h3>
<div id="user-groups" style="display: flex">
    <?php
    $req = $bdd->prepare('SELECT groupes FROM users WHERE id = '.$_SESSION['id_actif']);
    $req->execute();
    $usergroups = json_decode($req->fetch()['groupes'], true);
    $group_req = $bdd->prepare('SELECT id, name FROM usergroups WHERE id = :group_id');
    foreach ($usergroups as $usergroup) {
        $group_req->execute(['group_id' => $usergroup]);
        $usergroup = $group_req->fetch();
        echo '<button class="group-button" onclick="removeGroup('.$usergroup["id"].', \''.$usergroup['name'].'\')"> |' . strtoupper($usergroup["name"]) . '</button>';
    }
   ?>
    <button onclick="document.getElementById('new-group').style.display = 'block'" id="new-group-button" class="new-button" style="margin-left : 15px">
        <img src="Includes\Images\Icone Plus.png" class="to_defilter" style="height: 12px"> Intégrer un groupe
    </button>
</div>
<div id="new-group" style="display: none; margin-top : 2em">
    <label for="new-group-list">Groupes à intégrer : </label>
    <div id="new-group-list" style="display: flex; flex-wrap: wrap">
        <?php
        $req = $bdd->prepare('SELECT id, name FROM usergroups');
        $req->execute();
        $groups = $req->fetchAll();
        foreach ($groups as $group) {
            if (!in_array($group['id'],$usergroups)) {
                echo '<button class="add-group-button" onclick="addGroup('.$group['id'].',\''.$group['name'].'\')">|'.$group['name'].'</button>';
            }
        }


        ?>
    </div>
</div>


<!-- Demander la création d'un groupe -->

</div>

</div>
<?php }?>
<script>

    // Fonction pour ajouter un groupe
    function addGroup(groupId, groupName) {
        console.log('add group');
        if (!window.confirm(`Etes-sous sûr de vouloir rejoindre le groupe ${groupName} ?`)) {
            return;
        }

        var xhr = new XMLHttpRequest();

        xhr.open("POST", "Includes/PHP/profils_backend.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (data.error) {
                            alert("Erreur : " + data.error);
                        } else {
                            alert(data.message);
                            window.location.reload();
                        }
                    } catch (e) {
                        console.error("Erreur de traitement des données : ", e);
                    }
                } else {
                    console.error("Erreur HTTP : ", xhr.statusText);
                }
            }
        };

        const body = `action=manage_group&action_type=add&group_id=${groupId}`;
        xhr.send(body);
    }
</script>
<script>
    // Fonction pour retirer un groupe
    function removeGroup(groupId, groupName) {
        fetch("Includes/PHP/profils_backend.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "manage_group",
                action_type: "remove",
                group_id: groupId,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    alert("Erreur : " + data.error);
                } else {
                    alert(data.message);

                    // Intégrer dynamiquement le message depuis PHP dans la page
                    try {
                        const username = "<?= $_SESSION['id_actif'] ? $bdd->query("SELECT username FROM users WHERE id = ".$_SESSION['id_actif'])->fetch()['username'] : '' ?>";
                        if (username) {
                            let message_content = `${username} a quitté le groupe ${groupName}`;
                            newMessage(message_content, ['1'], [], 'ADMIN');
                            console.log(message_content);
                        }
                    } catch (e) {
                        console.error("Erreur lors de la construction du message : ", e);
                    }

                    window.location.reload(); // Recharge la page après suppression du groupe
                }
            })
            .catch((error) => console.error("Erreur :", error));
    }

</script>
</body>
</html>





<!-- "Mon profil" ne redirige pas mais c'est un bouton. Par la suite, les ancres en dessous
fonctionneront : me connecter / me déconnecter / nouveau profil.
-->
