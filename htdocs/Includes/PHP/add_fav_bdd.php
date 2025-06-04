<?php
try {
    // On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}
// Vérifier si un fichier a été envoyé via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['key'])) {
    if ($_POST['key'] == 'require_tool_image') {
        $req = "SELECT * FROM tools WHERE id = :id";
        $stmt = $bdd->prepare($req);
        $stmt->execute(['id' => $_POST['tool_id']]);
        $result = $stmt->fetch();
        if ($result['image']) {
            echo $result['image'];
        }
        else {echo "Fav_Icons/Default_Tool.png";}
    }
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type'])){
    if ($_POST['form_type'] == 'add_fav_tool') {
        // Variables
        if($_FILES['icon']){
            $icon = $_FILES['icon'];
            $targetDir = "C:/Apache24/htdocs/Fav_Icons/"; // Dossier où enregistrer le fichier
            $targetDirShort = "Fav_Icons/";
            $targetFile = $targetDir . basename($icon['name']);
            $targetFileShort = $targetDirShort. basename($icon['name']);
            move_uploaded_file($_FILES['icon']['tmp_name'], $targetFile);

            $req = "UPDATE tools SET Image = :icon WHERE id = :id";
            $stmt = $bdd->prepare($req);
            $stmt->execute(['icon' => $targetFileShort, 'id' => $_POST['tool_id']]);
        }
        else{
            $req = "SELECT image FROM tools WHERE id = :tool_id";
            $stmt = $bdd->prepare($req);
            $stmt->execute(['tool_id' => $_POST['tool_id']]);
            $targetFileShort = $stmt->fetch()['image'];
        }


        $req = "SELECT tools FROM favoritetools WHERE user_id = :user_id";
        $stmt = $bdd->prepare($req);
        $stmt->execute(['user_id' => $_POST['user_id']]);
        $tools_list = $stmt->fetchAll()[0]['tools'];
        $tools_arr = json_decode($tools_list, true);

        $tools_arr[(int)$_POST['tool_id']] = $targetFileShort;
        $json_arr = json_encode($tools_arr);

        $req = "UPDATE favoritetools SET tools = :json_arr WHERE user_id = :user_id";
        $stmt = $bdd->prepare($req);
        $stmt->execute(['json_arr' => $json_arr, 'user_id' => $_POST['user_id']]);


    }
    else if ($_POST['form_type'] == 'delete_fav_tool') {
        $req = "SELECt tools FROM favoritetools WHERE user_id = :user_id";
        $stmt = $bdd->prepare($req);
        $stmt->execute(['user_id' => $_POST['user_id']]);
        $tools_list = $stmt->fetch()['tools'];

        $tools_arr = json_decode($tools_list, true);
        unset($tools_arr[$_POST['tool_id']]);
        $json_arr = json_encode($tools_arr);


        $req = "UPDATE favoritetools SET tools = :json_arr WHERE user_id = :user_id";
        $stmt = $bdd->prepare($req);
        $stmt->execute(['json_arr' => $json_arr, 'user_id' => $_POST['user_id']]);
    }

}
?>