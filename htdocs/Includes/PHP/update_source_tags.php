<?php
session_start();
try {
    // Connexion à la base de données
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');

    // Récupération des données envoyées via AJAX
    $flag = isset($_POST['flag']) ? (int) $_POST['flag'] : 0;
    $tag_id = isset($_POST['tag_id']) ? (int) $_POST['tag_id'] : 0;
    $user_id = isset($_SESSION['id_actif']) ? (int) $_SESSION['id_actif'] : 0;

    $req = "UPDATE sourcestags SET user1 = 0 WHERE id = :tag_id";
    $stmt = $bdd->prepare($req);
    $stmt->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
    $stmt->execute();
    if ($flag == 1) {
        // Ajouter le tag à l'utilisateur
        echo 'Cas 1';
        $sql = "UPDATE sourcestags SET user" . $user_id ." = 2 WHERE id = :tag_id ";
    } else {
        echo 'Cas 2';
        // Supprimer le tag de l'utilisateur
        $req = "SELECT taggroups FROM sourcestags WHERE id = :tag_id";
        $stmt = $bdd->prepare($req);
        $stmt->execute(['tag_id' => $tag_id]);
        $taggroups = $stmt->fetch()['taggroups'];
        $taggroups = ($taggroups) ? json_decode($taggroups) : ["FUCK"];
        $usergroups = json_decode($bdd->query("SELECT groupes FROM users WHERE id = $user_id")->fetchColumn());
        $flag_bis = count(array_intersect($usergroups, $taggroups)) > 0 ? 1 : 0;

        $sql = "UPDATE sourcestags SET user" . $user_id . " = $flag_bis WHERE id = :tag_id";
    }
    $stmt = $bdd->prepare($sql);
    $stmt->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "Succès";
    } else {
        echo "Échec : Impossible de mettre à jour la base de données.";
    }
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
?>