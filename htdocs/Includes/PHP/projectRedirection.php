
<?php

session_start();


if (isset($_POST['project_id']) && isset($_SESSION['user_actif'])) {
    try {
        $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');

        $query = "UPDATE projects SET last_use = :time WHERE id = :id";
        $stmt = $bdd->prepare($query);
        $stmt = $stmt->execute(array(
            ":id" => $_POST["project_id"],
            ":time" => date("Y-m-d H:i:s")
        ));


    }
    catch (PDOException $e) {
        exit('Erreur : ' . $e->getMessage());
    }
}


if (isset($_SESSION['user_actif']) && $_SESSION['user_actif'] != "" && isset($_POST['project_id'])) {
    echo "EPIX_interface.php";
    $_SESSION['project_id'] = $_POST['project_id'];
}
else
{
    echo "EPIX_gestionnaire.php";
}
?>
