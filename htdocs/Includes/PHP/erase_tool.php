<?php
try {
    // On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}
// On récupère les tableaux, ces variables sont des tables de la bdd (tableau de tableau en php)
$users = $bdd->query("SELECT * FROM users")->fetchAll();
$tools = $bdd->query("SELECT * FROM tools")->fetchAll();
$toolcategories = $bdd->query("SELECT * FROM toolscategories")->fetchAll();


if (isset($_POST['tool_id'])){
    $tool_id = $_POST['tool_id'];
    $user_id = $_POST['user_id'];
    $tool_cat = $_POST['tool_cat'];


    $req_date = "SELECT * FROM tools WHERE id = :id AND DATE_ADD(date, INTERVAL 3 HOUR) >= NOW()";
    $stmt_date = $bdd->prepare($req_date);
    $stmt_date->execute(['id' => $user_id]);

//    Modification de la base de donnée pour la suppression de l'outil :
//        - Si l'outil a été ajouté il y a moins d'une heure, il est supprimé,
//        - Sinon il est juste caché

    if ($stmt_date->rowCount() > 0) { //On ne rentre jamais dans cette condition ?
        $query = "DELETE FROM tools WHERE id = :id";

        $cat_query = 'SELECT liens FROM toolscategories WHERE id = :id';
        $cat_stmt = $bdd->prepare($cat_query);
        $cat_stmt->execute(['id' => $tool_cat]);
        $cat_links = $cat_stmt->fetchAll()[0]['liens'];

        $arr = json_decode($cat_links, true);
        unset($arr[array_search($tool_id, $arr)]);
        $json_arr = json_encode($arr);

        $sql = 'UPDATE toolscategories SET liens = :links WHERE id = :id';
        $stmt_in = $bdd->prepare($sql);
        $stmt_in->execute(['links' => $json_arr, 'id' => $tool_cat]);
    } else {
        $query = "UPDATE osix.tools t SET t.user$user_id = 0 WHERE t.id = :id";
    }
    $stmt = $bdd->prepare($query);
    $stmt->execute(['id' => (int)$tool_id]);
}
?>

