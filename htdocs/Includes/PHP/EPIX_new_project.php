
<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'createProject') {

    try {
        // On se connecte à MySQL
        $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
    } catch (Exception $e) {// En cas d'erreur, on affiche un message et on arrête tout
        die('Erreur : ' . $e->getMessage());    } ;


    $title = $_POST['title'];
    $comment = $_POST['comment'];
    $icon = isset($_FILES['icon']) ? $_FILES['icon'] : null; // Icône reçue (si présente)

    //Récupérer les catégories et les initialiser.
    $query = 'SELECT id FROM nodescategories WHERE owner IN (0, :owner, -1)';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':owner' => $_SESSION['id_actif']]);
    //construction du JSON qui stockera les catégories retenues pour le projet (initialement, toutes)
    $nodecategoriesstatus = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nodecategoriesstatus[$row['id']] = 1;
    }

    $query = 'SELECT id FROM relationscategories WHERE owner IN (0, :owner, -1)';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':owner' => $_SESSION['id_actif']]);
    $edgecategoriesstatus = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $edgecategoriesstatus[$row['id']] = 1;
    }

    $query = 'INSERT INTO projects (name, date, last_use, comments, owner, sharelist, nodecategoriesstatus, edgecategoriesstatus)
                VALUES (:title, :date, :last_use, :comment, :owner, :sharelist, :nodecategoriesstatus, :edgecategoriesstatus)';
    $stmt = $bdd->prepare($query);
    $stmt = $stmt-> execute([':title' => $title,
        ':date' => date('Y-m-d H:i:s'),
        ':last_use' => date('Y-m-d H:i:s'),
        ':comment' => $comment,
        ':owner' => $_SESSION['id_actif'],
        ':sharelist' => json_encode([]),
        ':nodecategoriesstatus' => json_encode($nodecategoriesstatus),
        ':edgecategoriesstatus' => json_encode($edgecategoriesstatus),
        ]);

    $id = $bdd->lastInsertId();
    echo $id;

    if ($icon)
    {
        // Variables
        $logoFile = $_FILES['icon']; // Pour accéder aux détails pour l'upload

        // Définir les dossiers cibles
        $targetDir_one = "C:/Apache24/htdocs"; // Chemin pour la sauvegarde
        $targetDir_two = "/EPIX_Images/"; // Chemin pour l'accès en JS ou HTTP

        // Construire le nom du fichier cible avec un nom unique
        $fileName = $id . '_logo_' . basename($logoFile['name']);
        $targetFilePath = $targetDir_one . $targetDir_two . $fileName; // Chemin complet pour la sauvegarde
        $targetFilePath_bdd = $targetDir_two . $fileName; // Chemin pour la base de données (lisible en front-end)

        // Extensions autorisées pour le logo
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileMimeType = mime_content_type($logoFile['tmp_name']);

        // Vérification des extensions et types MIME
        if (($fileExtension == 'webp' && $fileMimeType == 'image/webp') ||
            ($fileExtension == 'jpg' && $fileMimeType == 'image/jpeg') ||
            ($fileExtension == 'jpeg' && $fileMimeType == 'image/jpeg') ||
            ($fileExtension == 'png' && $fileMimeType == 'image/png')
        ) {
            // Vérifier si le fichier a bien été téléchargé
            if (is_uploaded_file($logoFile['tmp_name'])) {
                // Déplacer le fichier uploadé depuis son emplacement temporaire vers le dossier cible
                if (move_uploaded_file($logoFile['tmp_name'], $targetFilePath)) {
                    // Mise à jour dans la base de données
                    try {
                        $query = 'UPDATE projects SET image = :logo WHERE id = :id';
                        $stmt = $bdd->prepare($query);
                        $stmt->execute([':logo' => $targetFilePath_bdd, ':id' => $id]);

                    } catch (PDOException $e) {
                        echo "Erreur lors de la mise à jour de la base de données : " . $e->getMessage();
                    }
                } else {
                    echo "Une erreur est survenue lors du déplacement du logo.<br>";
                }
            } else {
                echo "Le fichier logo n'a pas été téléchargé correctement.<br>";
            }
        } else {
            echo "Seules les extensions suivantes sont autorisées pour les logos : " . implode(", ", $allowedExtensions);
        }
    }

    exit;
}
?>