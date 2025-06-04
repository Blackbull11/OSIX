<?php
session_start();

try {
    // On se connecte à MySQL
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {// En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());    } ;


if ($_POST["type_requete"] == "log_off")
{   unset($_SESSION['id_actif']);
    $_SESSION = array();             }

elseif ($_POST["type_requete"] == "log_in")
{   $query = 'SELECT * FROM users WHERE username = :login';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':login' => $_POST['identifiant']]);
    $candidat = $stmt->fetch();



    // Le login est-il rempli ?
    if (empty($_POST['identifiant'])) {
        $message = 'Veuillez indiquer votre login !';
        echo 'profils.php';}

    // Le login est-il correct ?
    elseif (empty($candidat)) {
        $message = 'Votre login est inconnu !';         echo 'profils.php';}
    // Le mot de passe est-il rempli ?
    elseif (empty($_POST['mot_de_passe'])) {
        $message = 'Veuillez indiquer votre mot de passe !';            echo 'profils.php';}

    // Le mot de passe est-il correct ? (les conditions font que user est vide ou unique).
    elseif ($_POST['mot_de_passe'] !== $candidat["password"]) {
        $message = 'Votre mot de passe est faux !';         echo 'profils.php';}

    else {// L'identification a réussi
        $message = "La x24 vous souhaite la bienvenue à vous camarade !";
        $_SESSION['user_actif'] = $candidat["username"];
        $_SESSION['id_actif'] = $candidat["id"];
        $_SESSION['is_admin'] = $candidat["is_admin"];
        $_SESSION['groups'] = $candidat['groupes'];

        // Code pour suivi des connections avec la table log.
        // Ajout dans la table log uniquement si aucune entrée n'a été faite dans l'heure précédente
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('UTC')); // Régler le fuseau horaire si nécessaire
        $oneHourAgo = $now->modify('-1 hour')->format('Y-m-d H:i:s');
        $currentUserId = $_SESSION['id_actif'];

        // Vérifiez s'il existe une entrée récente pour cet utilisateur
        $query = "SELECT COUNT(*) FROM log WHERE user_id = :user_id AND date > :one_hour_ago";
        $stmt = $bdd->prepare($query);
        $stmt->execute([':user_id' => $currentUserId, ':one_hour_ago' => $oneHourAgo]);

        $logExists = $stmt->fetchColumn();

        if (!$logExists) { // Ajouter une nouvelle entrée si aucune ligne récente n'existe
            $insertQuery = "INSERT INTO log (user_id) VALUES (:user_id)";
            $stmt = $bdd->prepare($insertQuery);
            $stmt->execute([':user_id' => $currentUserId]);
        }

        // Mise à jour des tags favoris de groupe
        $req = "SELECT id, taggroups, user".$currentUserId." as fav from sourcestags";
        $stmt = $bdd->prepare($req);
        $stmt->execute();
        $groups = $stmt->fetchAll();
        foreach ($groups as $group) {
            $taggroups = json_decode($group['taggroups']);
            $req = "SELECT groupes FROM users WHERE id = :user_id";
            $stmt = $bdd->prepare($req);
            $stmt->execute([':user_id' => $currentUserId]);
            $usergroups = json_decode($stmt->fetch()['groupes']);
            if (count(array_intersect($taggroups, $usergroups)) >0 && $group['fav'] <2) {
                $req = ("UPDATE sourcestags SET user".$currentUserId." = 1 WHERE id= :id");
                $stmt = $bdd->prepare($req);
                $stmt->execute([':id' => $group['id']]);
            }
        }

        echo 'index.php';
    }


    $_SESSION['login_status'] = $message;
    $_SESSION['new_login_status'] = '';
}

elseif ($_POST["type_requete"] == "creation_compte") {
    $query = 'SELECT * FROM users WHERE username = :login';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':login' => $_POST['nouvel_identifiant']]);
    $candidat = $stmt->fetch();


    if (empty($_POST['nouveau_mot_de_passe']) || empty($_POST['confirmation']) || empty($_POST['nouvel_identifiant'])) {
        $message = 'Veuillez remplir les champs !';
        echo 'profils.php';
    } elseif ($_POST['nouveau_mot_de_passe'] !== $_POST['confirmation']) {
        $message = 'Rentrez deux mots de passe identiques !';
        echo 'profils.php';
    } elseif (!empty($candidat)) {
        $message = 'Soyez plus inventif, ce login existe déjà';
        echo 'profils.php';
    } else {
        $query = 'INSERT INTO osix.users (username, password, is_admin, groupes) VALUES (:login, :password, 0, "[3]")';
        $stmt = $bdd->prepare($query);
        $stmt->execute([':login' => $_POST['nouvel_identifiant'], ':password' => $_POST['nouveau_mot_de_passe']]);
        $_SESSION['user_actif'] = $_POST['nouvel_identifiant'];

        $query = 'SELECT * FROM users WHERE username = :login';
        $stmt = $bdd->prepare($query);
        $stmt->execute([':login' => $_POST['nouvel_identifiant']]);
        $candidat = $stmt->fetch();

        $nouvel_id = $candidat["id"];
        $_SESSION['id_actif'] = $nouvel_id;
        $_SESSION['user_actif'] = $candidat["username"];
        $_SESSION['is_admin'] = 0;
        $message = "Bienvenue à " . $_POST['nouvel_identifiant'];

        // Étape 2 : Ajouter les colonnes usern dans `tools` et `usergroups`
        $col_name = 'user' . $nouvel_id;

        // Ajouter la colonne dans la table `tools`
        $bdd->exec("ALTER TABLE tools ADD COLUMN `$col_name` INT DEFAULT 0");
        $bdd->exec("UPDATE tools SET `$col_name` = user0");

        $bdd->exec("ALTER TABLE sourcestags ADD COLUMN `$col_name` INT DEFAULT 0");
        $req = "SELECT id, taggroups from sourcestags";
        $stmt = $bdd->prepare($req);
        $stmt->execute();
        $groups = $stmt->fetchAll();
        foreach ($groups as $group) {
            $taggroups = json_decode($group['taggroups']);
            $req = "SELECT groupes FROM users WHERE id = :user_id";
            $stmt = $bdd->prepare($req);
            $stmt->execute([':user_id' => $nouvel_id]);
            $usergroups = json_decode($stmt->fetch()['groupes']);
            if (count(array_intersect($taggroups, $usergroups)) > 0) {
                $req = ("UPDATE sourcestags SET `$col_name` = 1 WHERE id= :id");
                $stmt = $bdd->prepare($req);
                $stmt->execute([':id' => $group['id']]);
            }
        }

        // Étape 3 : Ajouter une ligne dans `favorite_tools`
        $stmt = $bdd->prepare(
            "INSERT INTO favoritetools (user_id, tools)
             SELECT :user_id, tools FROM favoritetools WHERE user_id = 0"
        );
        $stmt->execute([':user_id' => $nouvel_id]);

        // Étape 4 : Ajouter une ligne dans `favorite_links`
        $stmt = $bdd->prepare(
            "INSERT INTO favoritelinks (user_id, links)
             SELECT :user_id, links FROM favoritelinks WHERE user_id = 0"
        );
        $stmt->execute([':user_id' => $nouvel_id]);

        // Ajouter une ligne dans la table `projects` pour le projet EPIX
        $stmt = $bdd->prepare(
            "INSERT INTO projects (name, comments, image, owner, sharelist, nodecategoriesstatus, edgecategoriesstatus)
                VALUES ('Mon premier projet', 'Le premier mais pas le dernier !', 'Fav_Icons/Sandbox_logo.png', :owner_id, '[]', '{}', '{}')"
        );
        $stmt->execute([':owner_id' => $nouvel_id]);


        // Ajout dans la table log uniquement si aucune entrée n'a été faite dans l'heure précédente
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('UTC')); // Régler le fuseau horaire si nécessaire
        $oneHourAgo = $now->modify('-1 hour')->format('Y-m-d H:i:s');
        $currentUserId = $_SESSION['id_actif'];

        // Vérifiez s'il existe une entrée récente pour cet utilisateur
        $query = "SELECT COUNT(*) FROM log WHERE user_id = :user_id AND date > :one_hour_ago";
        $stmt = $bdd->prepare($query);
        $stmt->execute([':user_id' => $currentUserId, ':one_hour_ago' => $oneHourAgo]);

        $logExists = $stmt->fetchColumn();

        if (!$logExists) { // Ajouter une nouvelle entrée si aucune ligne récente n'existe
            $insertQuery = "INSERT INTO log (user_id) VALUES (:user_id)";
            $stmt = $bdd->prepare($insertQuery);
            $stmt->execute([':user_id' => $currentUserId]);
        }

        // Redirection après succès
        echo 'index.php';


        $_SESSION['new_login_status'] = $message;
        $_SESSION['login_status'] = '';
        $_SESSION['first-log'] = true;
    }
}

elseif ($_POST["type_requete"] == "admin_login")

{       $query = 'SELECT * FROM constantes WHERE name = :x';
        $stmt = $bdd->prepare($query);
        $stmt->execute([':x' => "admin_key"]);
        $candidat = $stmt->fetch();

        if ($candidat["valeur"]== $_POST['clef']) {
        $_SESSION['is_admin'] = 1;
        echo 'administration.php';}

        else {$_SESSION['is_admin_status'] = "Ceci n'est pas le bon mot de passe.";
                echo 'profils.php';} ;
}

?>

