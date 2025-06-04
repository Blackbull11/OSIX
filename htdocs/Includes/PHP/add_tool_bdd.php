<?php
session_start();

try {
    // On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}
$tools = $bdd->query("SELECT * FROM tools")->fetchAll();
$toolscategories = $bdd->query("SELECT * FROM toolscategories")->fetchAll();

// Vérifier si le formulaire a été soumis
if (isset($_POST['key']) and $_POST['key'] == 'add_tool_category') {
    if ($_POST['is_admin'] == 1) {
        $sql = "INSERT INTO toolscategories (name, parent, liens) VALUES (?, ?, '[]')";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$_POST['cat_name'], $_POST['tool_parent']]);
    }
    else {
        $content = [
            'cat_name' => $_POST['cat_name'],
            'parent_id' => $_POST['tool_parent'],
            'liens' => [],
            'tags' => json_decode($_POST['tags'], false)
        ];
        $sql = "INSERT INTO newsadmin (asking_user, demandtype, content) VALUES (?, 'New_Tool_Category', ?)";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$_POST['user_id'], json_encode($content) ]);
    }
    echo 'OK';
}
elseif (isset($_POST['edited_tool'])) {
    if ($_POST['edited_tool'] == 0) {
        $userId = $_POST['user_id'];
        $toolName = $_POST['tool_name'];
        $toolUrl = $_POST['tool_url'];
        $toolDoc = $_POST['tool_doc'];
        $toolTags = $_POST['tags_list'];
        $toolCategory = $_POST['category_id'];
        $sharing = $_POST['sharing'];

        // Ajout de l'outil à la table tools avec activation seulement pour le créateur
        $for_user0 = ($sharing == 1) ? 1 : 0;

        $sql = "INSERT INTO tools (name, url, date, owner, doc, tags, user0, user".$userId.", category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$toolName, $toolUrl, date('Y-m-d H:i:s'), $userId, $toolDoc, $toolTags, $for_user0, 1, $toolCategory]);

        $query = 'SELECT id FROM tools ORDER BY id DESC LIMIT 1';
        $stmt = $bdd->prepare($query);
        $stmt->execute();
        $new_id = $stmt->fetchAll()[0]['id'];

        if ($sharing == 1) {
            for ($i = 1; $i <= $userId; $i++) {
                $sql = "UPDATE tools SET user".$i." = 1 WHERE id = :id";
                $stmt = $bdd->prepare($sql);
                $stmt->execute(['id' => $new_id]);
            };
            $message_content = $_POST['tool_name'] . "fait son apparition dans votre aborescnce box.<br> Découvrez cet outil dès maintenant sur <a href='/index.php'>votre profil</a> !";
            $req = "INSERT INTO news (issuer, receiver, message, date) VALUES (:issuer, :receiver, :message) ";
            $stmt = $bdd->prepare($req);
            $stmt->execute(['issuer' => $_SESSION['user_id'], 'receiver' => '{"users": [], "groups": [3], "viewed": []}', "message" => $message_content]);
        };

        $cat_query = 'SELECT liens FROM toolscategories WHERE id = :id';
        $cat_stmt = $bdd->prepare($cat_query);
        $cat_stmt->execute(['id' => $toolCategory]);
        $cat_links = $cat_stmt->fetchAll()[0]['liens'];

        $arr = json_decode($cat_links, true);
        array_push($arr, $new_id);
        $json_arr = json_encode($arr);

        $sql = 'UPDATE toolscategories SET liens = :links WHERE id = :id';
        $stmt = $bdd->prepare($sql);
        $stmt->execute(['links' => $json_arr, 'id' => $toolCategory]);

        echo 'OK';
    }
    else {
        $userId = $_POST['user_id'];
        $toolName = $_POST['tool_name'];
        $toolUrl = $_POST['tool_url'];
        $toolDoc = $_POST['tool_doc'];
        $toolTags = $_POST['tags_list'];
        $toolCategory = $_POST['category_id'];
        $sharing = $_POST['sharing'];
        $toolId = $_POST['edited_tool'];

        $req = "UPDATE tools SET name = ?, url = ?, tags = ?, doc = ?, user0 = ?, category_id = ? WHERE id = ?";
        $stmt = $bdd->prepare($req);
        $stmt->execute([$toolName, $toolUrl, $toolTags, $toolDoc, $sharing, $toolCategory, $toolId]);


        // On récupère la catégorie actuelle de l'outil
        $queryCurrentCategory = "SELECT id, liens FROM toolscategories WHERE liens LIKE :toolId";
        $stmtCurrent = $bdd->prepare($queryCurrentCategory);
        $stmtCurrent->execute([':toolId' => '%"'.$toolId.'"%']); // Conversion du lien en JSON
        $currentCategory = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

        if ($currentCategory && $currentCategory != $toolCategory) {

            // Extracter les liens de la catégorie actuelle
            $currentLinks = json_decode($currentCategory['liens'], true);

            // Retirer l'outil de la catégorie actuelle
            if (($key = array_search($toolId, $currentLinks)) !== false) {
                unset($currentLinks[$key]);
                $updatedLinks = json_encode(array_values($currentLinks)); // Réindexation nécessaire

                $stmtUpdateCurrent = $bdd->prepare("UPDATE toolscategories SET liens = :updatedLinks WHERE id = :id");
                $stmtUpdateCurrent->execute([':updatedLinks' => $updatedLinks, ':id' => $currentCategory['id']]);
            }
        }

        // Ajouter l'outil à la nouvelle catégorie
        $queryNewCategory = "SELECT liens FROM toolscategories WHERE id = :newCategoryId";
        $stmtNewCategory = $bdd->prepare($queryNewCategory);
        $stmtNewCategory->execute([':newCategoryId' => $toolCategory]);
        $newCategory = $stmtNewCategory->fetch(PDO::FETCH_ASSOC);

        if ($newCategory) {
            $newLinks = json_decode($newCategory['liens'], true);
            if (!in_array($toolId, $newLinks)) {
                $newLinks[] = $toolId; // Ajouter l'outil
                $updatedNewLinks = json_encode($newLinks);

                $stmtUpdateNew = $bdd->prepare("UPDATE toolscategories SET liens = :updatedLinks WHERE id = :id");
                $stmtUpdateNew->execute([':updatedLinks' => $updatedNewLinks, ':id' => $toolCategory]);
            }
        }
        echo 'OK';
    }
};

?>
