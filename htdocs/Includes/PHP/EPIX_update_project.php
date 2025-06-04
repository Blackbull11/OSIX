<?php
session_start();

/*Les catégories sont > ou < 0 pour relation ou node.
attention, ne pas retourner n'importe quoi car on retourne l'ID (pour edge et node en tout cas).
*/

try {
    // On se connecte à MySQL
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {// En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());    } ;


if (isset($_POST['project_id'])) {

    if (isset($_POST['project_new_name'])) {
        $query = 'UPDATE projects SET name = :title WHERE id = :id ';
        $stmt = $bdd->prepare($query);
        $stmt->execute([':title' => $_POST['project_new_name'], ':id' => $_POST['project_id']]);
    }

    elseif (isset($_POST['project_new_comments'])) {
        $query = 'UPDATE projects SET comments = :com WHERE id = :id ';
        $stmt = $bdd->prepare($query);
        $stmt->execute([':com' => $_POST['project_new_comments'], ':id' => $_POST['project_id']]);
    }

    elseif (isset($_FILES['projectNewLogo'])) {
        // Variables
        $logoFile = $_FILES['projectNewLogo']; // Pour accéder aux détails pour l'upload
        $id = $_POST['project_id']; // Assurez-vous que l'ID du projet est disponible

        // Définir les dossiers cibles
        $targetDir_one = "C:/Apache24/htdocs"; // Chemin pour la sauvegarde
        $targetDir_two = "/EPIX_Images/"; // Chemin pour l'accès en JS ou HTTP

        // Construire le nom du fichier cible avec un nom unique
        $fileName = $id . '_logo_' . basename($logoFile['name']);
        $targetFilePath = $targetDir_one . $targetDir_two . $fileName; // Chemin complet pour la sauvegarde
        $targetFilePath_bdd = $targetDir_two . $fileName; // Chemin pour la base de données (lisible en front-end)

        // Extensions autorisées pour le logo
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff', 'svg', 'heif', 'heic', 'ico', 'raw', 'jp2', 'avif', 'xcf'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileMimeType = mime_content_type($logoFile['tmp_name']);

        // Vérification des extensions et types MIME
        if (($fileExtension == 'webp' && $fileMimeType == 'image/webp') ||
            ($fileExtension == 'jpg' && $fileMimeType == 'image/jpeg') ||
            ($fileExtension == 'jpeg' && $fileMimeType == 'image/jpeg') ||
            ($fileExtension == 'png' && $fileMimeType == 'image/png') ||
            ($fileExtension == 'gif' && $fileMimeType == 'image/gif') ||
            ($fileExtension == 'bmp' && $fileMimeType == 'image/bmp') ||
            ($fileExtension == 'tiff' && $fileMimeType == 'image/tiff') ||
            ($fileExtension == 'svg' && $fileMimeType == 'image/svg+xml') ||
            ($fileExtension == 'heif' && $fileMimeType == 'image/heif') ||
            ($fileExtension == 'heic' && $fileMimeType == 'image/heic') ||
            ($fileExtension == 'ico' && $fileMimeType == 'image/x-icon') ||
            ($fileExtension == 'raw' && $fileMimeType == 'image/raw') ||
            ($fileExtension == 'jp2' && $fileMimeType == 'image/jp2') ||
            ($fileExtension == 'avif' && $fileMimeType == 'image/avif') ||
            ($fileExtension == 'xcf' && $fileMimeType == 'image/x-xcf')
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

                        echo $targetFilePath_bdd;
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

    elseif (isset($_POST['action']) && ($_POST['action'] == 'addNode')
        && isset($_POST['nodeName'])
        && isset($_POST['nodeCategory'])
        && isset($_POST['nodeComment'])
        && isset($_POST['nodeLink'])
        && isset($_POST['nodeGeoCoord'])
        && isset($_POST['nodeDetectedSource'])
    )
    {
        $query = 'INSERT INTO projectcontent (project_id, category, name, comments, position, source, image, icon, geoCoord)
            VALUES (:id, :cat, :name, :com, :pos, :src, :img, :icon, :geoCoord)';
        $stmt = $bdd->prepare($query);
        $stmt->execute([
            ':id' => $_POST['project_id'], ':cat' => $_POST['nodeCategory'],
            ':name' => $_POST['nodeName'], ':com' => $_POST['nodeComment'],
            ':pos' => $_POST['position'],
            ':src' => $_POST['nodeLink'],
            ':img' => '',
            ':icon' => '',
            ':geoCoord' => $_POST['nodeGeoCoord']
        ]);


        $query = 'SELECT MAX(id) FROM projectcontent WHERE project_id = :id';
        $stmt = $bdd->prepare($query);
        $stmt->execute([':id' => $_SESSION["project_id"]]);
        $id = $stmt->fetch()['MAX(id)'];

        echo $id;

        if (isset($_FILES['icon'])) {
            // Variables
            $icon = $_FILES['icon'];
            // Définir le dossier cible
            //$targetDir = "http://localhost/EPIX_Images/"; // Dossier où enregistrer le fichier
            // pour des contraintes de wamp64, on doit définir deux chemins d'accès.
            $targetDir_one = "C:/Apache24/htdocs"; //pour le sauvegarder
            $targetDir_two = "/EPIX_Images/"; // Dossier où aller le chercher en js


            // Récupérer les informations sur le fichier téléchargé
            // Le nom code l'id de l'entité (attention, ne s'adapte pas (encore ?) à la version, inutile car on copiera le chemin d'accès
            $fileName = $id.'_'.$_POST['project_id'].'_icon_'.basename($icon['name']) ; //. "name_item ". $_POST['nodeName']; // Nom original du fichier
            $targetFilePath = $targetDir_one. $targetDir_two . $fileName; // Chemin complet du fichier de destination
            $targetFilePath_bdd = $targetDir_two . $fileName; //chemin d'accès lisible par js et wamp

            // Vérifier l'extension du fichier pour accepter uniquement des images
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff', 'svg', 'heif', 'heic', 'ico', 'raw', 'jp2', 'avif', 'xcf'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Récupérer l'extension
            $fileMimeType = mime_content_type($_FILES['icon']['tmp_name']);

            if (($fileExtension == 'webp' && $fileMimeType == 'image/webp') ||
                ($fileExtension == 'jpg' && $fileMimeType == 'image/jpeg') ||
                ($fileExtension == 'jpeg' && $fileMimeType == 'image/jpeg') ||
                ($fileExtension == 'png' && $fileMimeType == 'image/png') ||
                ($fileExtension == 'gif' && $fileMimeType == 'image/gif') ||
                ($fileExtension == 'bmp' && $fileMimeType == 'image/bmp') ||
                ($fileExtension == 'tiff' && $fileMimeType == 'image/tiff') ||
                ($fileExtension == 'svg' && $fileMimeType == 'image/svg+xml') ||
                ($fileExtension == 'heif' && $fileMimeType == 'image/heif') ||
                ($fileExtension == 'heic' && $fileMimeType == 'image/heic') ||
                ($fileExtension == 'ico' && $fileMimeType == 'image/x-icon') ||
                ($fileExtension == 'raw' && $fileMimeType == 'image/raw') ||
                ($fileExtension == 'jp2' && $fileMimeType == 'image/jp2') ||
                ($fileExtension == 'avif' && $fileMimeType == 'image/avif') ||
                ($fileExtension == 'xcf' && $fileMimeType == 'image/x-xcf')
            )
            {
                // Vérifier si le fichier a été correctement téléchargé
                if (is_uploaded_file($icon['tmp_name'])) {
                    // Déplacer le fichier vers le dossier cible
                    if (move_uploaded_file($icon['tmp_name'], $targetFilePath)) {
                        //echo "L'image a été enregistrée avec succès dans le dossier EPIX_Images !";
                        $query = 'UPDATE projectcontent SET icon = :icon WHERE id = :id';
                        $stmt = $bdd->prepare($query);
                        $stmt->execute([':icon' => $targetFilePath_bdd, ':id' => $id]);

                    } else {
                        echo "Une erreur est survenue lors du déplacement de l'image.<br>";
                    }
                } else {
                    echo "Le fichier n'a pas été téléchargé correctement.<br>";
                }
            } else {
                echo "Seules les extensions suivantes sont autorisées : " . implode(", ", $allowedExtensions);
            }

        }

        if (isset($_FILES['nodeImage'])) {
            // Variables
            $image = $_FILES['nodeImage'];
            // Définir le dossier cible
//            $targetDir = "http://localhost/EPIX_Images/"; // Dossier où enregistrer le fichier
            // pour des contraintes de wamp64, on doit définir deux chemins d'accès.
            $targetDir_one = "C:/Apache24/htdocs"; //pour le sauvegarder
            $targetDir_two = "/EPIX_Images/"; // Dossier où aller le chercher en js


            // Récupérer les informations sur le fichier téléchargé
            // Le nom code l'id de l'entité (attention, ne s'adapte pas (encore ?) à la version, inutile car on copiera le chemin d'accès
            $fileName = $id.'_'.$_POST['project_id'].'_image_'.basename($image['name']) ; //. "name_item ". $_POST['nodeName']; // Nom original du fichier
            $targetFilePath = $targetDir_one. $targetDir_two . $fileName; // Chemin complet du fichier de destination
            $targetFilePath_bdd = $targetDir_two . $fileName; //chemin d'accès lisible par js et wamp

            // Vérifier l'extension du fichier pour accepter uniquement des images
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff', 'svg', 'heif', 'heic', 'ico', 'raw', 'jp2', 'avif', 'xcf'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Récupérer l'extension
            $fileMimeType = mime_content_type($image['tmp_name']);

            if (($fileExtension == 'webp' && $fileMimeType == 'image/webp') ||
                ($fileExtension == 'jpg' && $fileMimeType == 'image/jpeg') ||
                ($fileExtension == 'jpeg' && $fileMimeType == 'image/jpeg') ||
                ($fileExtension == 'png' && $fileMimeType == 'image/png') ||
                ($fileExtension == 'gif' && $fileMimeType == 'image/gif') ||
                ($fileExtension == 'bmp' && $fileMimeType == 'image/bmp') ||
                ($fileExtension == 'tiff' && $fileMimeType == 'image/tiff') ||
                ($fileExtension == 'svg' && $fileMimeType == 'image/svg+xml') ||
                ($fileExtension == 'heif' && $fileMimeType == 'image/heif') ||
                ($fileExtension == 'heic' && $fileMimeType == 'image/heic') ||
                ($fileExtension == 'ico' && $fileMimeType == 'image/x-icon') ||
                ($fileExtension == 'raw' && $fileMimeType == 'image/raw') ||
                ($fileExtension == 'jp2' && $fileMimeType == 'image/jp2') ||
                ($fileExtension == 'avif' && $fileMimeType == 'image/avif') ||
                ($fileExtension == 'xcf' && $fileMimeType == 'image/x-xcf')
            )
            {
                // Vérifier si le fichier a été correctement téléchargé
                if (is_uploaded_file($image['tmp_name'])) {
                    // Déplacer le fichier vers le dossier cible
                    if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
                        //echo "L'image a été enregistrée avec succès dans le dossier EPIX_Images !";
                        $query = 'UPDATE projectcontent SET image = :image WHERE id = :id';
                        $stmt = $bdd->prepare($query);
                        $stmt->execute([':image' => $targetFilePath_bdd, ':id' => $id]);

                    } else {
                        echo "Une erreur est survenue lors du déplacement de l'image.<br>";
                    }
                } else {
                    echo "Le fichier n'a pas été téléchargé correctement.<br>";
                }
            } else {
                echo "Seules les extensions suivantes sont autorisées : " . implode(", ", $allowedExtensions);
            }
        }

        if ($_POST['nodeDetectedSource'] != 0) {
            $query = 'SELECT derivatives FROM sources WHERE id = :id';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':id' => $_POST['nodeDetectedSource']]);
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo 'source : ' .$_POST['nodeDetectedSource'] ;
            print_r($row);

            $derivatives = json_decode($row[0]['derivatives']);
            $derivatives[] = date('Y-m-d H:i:s') ;

            $query = 'UPDATE sources SET derivatives = :derivatives WHERE id = :id';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':derivatives' => json_encode($derivatives), ':id' => $_POST['nodeDetectedSource']]);
        }


    }

    elseif (isset($_POST['newNodeName'])
        && isset($_POST['newNodeCategory'])
        && isset($_POST['newNodeComments'])
        && isset($_POST['nodeID'])
        && isset($_POST['newNodeLink'])
        && isset($_POST['newNodeDetectedSource'])
    && isset($_POST['newNodeGeoCoord']) )
    {
        $query = 'UPDATE projectcontent SET name = :name, category = :cat, comments = :com, source = :src, geoCoord = :geoCoord WHERE id = :id';
        $stmt = $bdd->prepare($query);
        $stmt->execute([
            ':name' => $_POST['newNodeName'],
            ':cat' => $_POST['newNodeCategory'],
            ':com' => $_POST['newNodeComments'],
            ':id' => $_POST['nodeID'],
            ':src' => $_POST['newNodeLink'],
            ':geoCoord' => $_POST['newNodeGeoCoord']
            ]);
        $id = $_POST['nodeID'];
        echo $id;

        if (isset($_FILES['newNodeIcon'])) {

            // Variables
            $icon = $_FILES['newNodeIcon'];
            // Définir le dossier cible
//            $targetDir = "http://localhost/EPIX_Images/"; // Dossier où enregistrer le fichier
            // pour des contraintes de wamp64, on doit définir deux chemins d'accès.
            $targetDir_one = "C:/Apache24/htdocs"; //pour le sauvegarder
            $targetDir_two = "/EPIX_Images/"; // Dossier où aller le chercher en js


            // Récupérer les informations sur le fichier téléchargé
            // Le nom code l'id de l'entité (attention, ne s'adapte pas (encore ?) à la version, inutile car on copiera le chemin d'accès
            $fileName = $id.'_'.$_POST['project_id'].'_icon_'.basename($icon['name']) ; //. "name_item ". $_POST['nodeName']; // Nom original du fichier
            $targetFilePath = $targetDir_one. $targetDir_two . $fileName; // Chemin complet du fichier de destination
            $targetFilePath_bdd = $targetDir_two . $fileName; //chemin d'accès lisible par js et wamp

            // Vérifier l'extension du fichier pour accepter uniquement des images
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff', 'svg', 'heif', 'heic', 'ico', 'raw', 'jp2', 'avif', 'xcf'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Récupérer l'extension
            $fileMimeType = mime_content_type($icon['tmp_name']);


            if (($fileExtension == 'webp' && $fileMimeType == 'image/webp') ||
                ($fileExtension == 'jpg' && $fileMimeType == 'image/jpeg') ||
                ($fileExtension == 'jpeg' && $fileMimeType == 'image/jpeg') ||
                ($fileExtension == 'png' && $fileMimeType == 'image/png') ||
                ($fileExtension == 'gif' && $fileMimeType == 'image/gif') ||
                ($fileExtension == 'bmp' && $fileMimeType == 'image/bmp') ||
                ($fileExtension == 'tiff' && $fileMimeType == 'image/tiff') ||
                ($fileExtension == 'svg' && $fileMimeType == 'image/svg+xml') ||
                ($fileExtension == 'heif' && $fileMimeType == 'image/heif') ||
                ($fileExtension == 'heic' && $fileMimeType == 'image/heic') ||
                ($fileExtension == 'ico' && $fileMimeType == 'image/x-icon') ||
                ($fileExtension == 'raw' && $fileMimeType == 'image/raw') ||
                ($fileExtension == 'jp2' && $fileMimeType == 'image/jp2') ||
                ($fileExtension == 'avif' && $fileMimeType == 'image/avif') ||
                ($fileExtension == 'xcf' && $fileMimeType == 'image/x-xcf')
            ) {
                // Vérifier si le fichier a été correctement téléchargé
                if (is_uploaded_file($icon['tmp_name'])) {
                    // Déplacer le fichier vers le dossier cible
                    if (move_uploaded_file($icon['tmp_name'], $targetFilePath)) {
//                        echo "L'image a été enregistrée avec succès dans le dossier EPIX_Images !";
                        $query = 'UPDATE projectcontent SET icon = :icon WHERE id = :id';
                        $stmt = $bdd->prepare($query);
                        $stmt->execute([':icon' => $targetFilePath_bdd, ':id' => $id]);
                    } else {
//                        echo "Une erreur est survenue lors du déplacement de l'image.<br>";
                    }
                } else {
//                    echo "Le fichier n'a pas été téléchargé correctement.<br>";
                }
            } else {
//                echo "Seules les extensions suivantes sont autorisées : " . implode(", ", $allowedExtensions);
            }

        }
        else if (isset($_POST["deleteIcon"]) && $_POST["deleteIcon"] === '1') {
            $query = 'UPDATE projectcontent SET icon = :icon WHERE id = :id';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':icon' => "", ':id' => $id]);
        }


        if (isset($_FILES['newNodeImage'])) { // Adapté pour utiliser $_FILES['newNodeImage']
            // Variables
            $newNodeImage = $_FILES['newNodeImage'];

            // Définir le dossier cible
            $targetDir_one = "C:/Apache24/htdocs"; // pour le sauvegarder
            $targetDir_two = "/EPIX_Images/"; // Dossier où l'utiliser en JavaScript

            // Générer un nom unique pour le fichier
            $fileName = $id . '_' . $_POST['project_id'] . '_image_'. basename($newNodeImage['name']);
            $targetFilePath = $targetDir_one . $targetDir_two . $fileName; // Chemin complet pour l'enregistrement
            $targetFilePath_bdd = $targetDir_two . $fileName; // Chemin enregistré dans la base de données

            // Vérifier l'extension du fichier pour accepter uniquement des images
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff', 'svg', 'heif', 'heic', 'ico', 'raw', 'jp2', 'avif', 'xcf'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Récupérer l'extension
            $fileMimeType = mime_content_type($newNodeImage['tmp_name']);

            // Validation des extensions
            if (($fileExtension == 'webp' && $fileMimeType == 'image/webp') ||
                ($fileExtension == 'jpg' && $fileMimeType == 'image/jpeg') ||
                ($fileExtension == 'jpeg' && $fileMimeType == 'image/jpeg') ||
                ($fileExtension == 'png' && $fileMimeType == 'image/png') ||
                ($fileExtension == 'gif' && $fileMimeType == 'image/gif') ||
                ($fileExtension == 'bmp' && $fileMimeType == 'image/bmp') ||
                ($fileExtension == 'tiff' && $fileMimeType == 'image/tiff') ||
                ($fileExtension == 'svg' && $fileMimeType == 'image/svg+xml') ||
                ($fileExtension == 'heif' && $fileMimeType == 'image/heif') ||
                ($fileExtension == 'heic' && $fileMimeType == 'image/heic') ||
                ($fileExtension == 'ico' && $fileMimeType == 'image/x-icon') ||
                ($fileExtension == 'raw' && $fileMimeType == 'image/raw') ||
                ($fileExtension == 'jp2' && $fileMimeType == 'image/jp2') ||
                ($fileExtension == 'avif' && $fileMimeType == 'image/avif') ||
                ($fileExtension == 'xcf' && $fileMimeType == 'image/x-xcf')
            ) {
                // Vérifie si le fichier a bien été téléchargé
                if (is_uploaded_file($newNodeImage['tmp_name'])) {
                    // Déplace le fichier vers le dossier cible
                    if (move_uploaded_file($newNodeImage['tmp_name'], $targetFilePath)) {
                        // Mettre à jour le chemin du fichier dans la base de données
                        $query = 'UPDATE projectcontent SET image = :image WHERE id = :id';
                        $stmt = $bdd->prepare($query);
                        $stmt->execute([':image' => $targetFilePath_bdd, ':id' => $id]);
                    } else {
                        // En cas d'échec du déplacement du fichier
                        //echo "Une erreur est survenue lors du déplacement de l'image.<br>";
                    }
                } else {
                    // En cas de problème avec le téléchargement
                    //echo "Le fichier n'a pas été téléchargé correctement.<br>";
                }
            } else {
                // En cas d'extension non autorisée
                //echo "Seules les extensions suivantes sont autorisées : " . implode(", ", $allowedExtensions);
            }
        }
        else if (isset($_POST["deleteImage"]) && $_POST["deleteImage"] === '1') {
            $query = 'UPDATE projectcontent SET image = :image WHERE id = :id';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':image' => "", ':id' => $id]);
        }

        // Pour mettre à jour l'historique des sources consultées
        if (isset($_POST['newNodeDetectedSource']) && ($_POST['newNodeDetectedSource'] != 0) ) {

            $query = 'SELECT derivatives FROM sources WHERE id = :id';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':id' => $_POST['newNodeDetectedSource']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC)['derivatives'];

            $derivatives = json_decode($row);
            $derivatives[] = date('Y-m-d H:i:s');

            $query = 'UPDATE sources SET derivatives = :derivatives WHERE id = :id';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':derivatives' => json_encode($derivatives), ':id' => $_POST['newNodeDetectedSource']]);
        }

    }

    elseif (isset($_POST['nodeID']) && isset($_POST['newNodePosition'])) {
        $query = 'UPDATE projectcontent SET position = :pos WHERE id = :id';
        $stmt = $bdd->prepare($query);
        $stmt->execute([':pos' => $_POST['newNodePosition'], ':id' => $_POST['nodeID']]);
    }

    elseif (isset($_POST['action']) && $_POST['action'] === 'restoreNode'
        && isset($_POST['nodeID'])
        && isset($_POST['nodeIcon'])
        && isset($_POST['nodeImage'])
        && isset($_POST['nodeLink'])
        && isset($_POST['position'])
        && isset($_POST['nodeCategory'])
        && isset($_POST['nodeName'])
        && isset($_POST['nodeComment'])
        && isset($_POST['nodeGeoCoord'])
    ) {

       // Insérer manuellement l'ID avec toutes les autres données
            $query = 'INSERT INTO projectcontent
          (project_id, icon, image, category, name, comments, position, source, geoCoord)
          VALUES
          (:project_id, :icon, :image, :cat, :name, :com, :pos, :src, :geoCoord)';
            $stmt = $bdd->prepare($query);

            try {
                $stmt->execute([
                    ':project_id' => $_POST['project_id'],
                    ':icon' => $_POST['nodeIcon'],
                    ':image' => $_POST['nodeImage'],
                    ':cat' => $_POST['nodeCategory'],
                    ':name' => $_POST['nodeName'],
                    ':com' => $_POST['nodeComment'],
                    ':pos' => $_POST['position'],
                    ':src' => $_POST['nodeLink'],
                    ':geoCoord' => $_POST['nodeGeoCoord']
                ]);

                $id = $bdd->lastInsertId();

                $query = 'UPDATE projectrelation SET sourceNode = :srcNode WHERE (sourceNode = :oldSrcNode)';
                $stmt = $bdd->prepare($query);
                $stmt->execute([
                    ':srcNode' => $id,
                    ':oldSrcNode' => $_POST['nodeID']
                ]);

                $query = 'UPDATE projectrelation SET targetNode = :trgtNode WHERE (targetNode = :oldTargetNode)';
                $stmt = $bdd->prepare($query);
                $stmt->execute([
                    ':trgtNode' => $id,
                    ':oldTargetNode' => $_POST['nodeID']
                ]);

                echo $id;
            } catch (PDOException $e) {
                exit('Erreur lors de l’insertion : ' . $e->getMessage());
            }
    }

    elseif (isset($_POST['action']) && $_POST['action'] === 'restoreEdge') {
        $query = "INSERT INTO projectrelation (project_id, category, comments, source, sourceNode, targetNode, style)
        VALUES (:project_id, :category, :comments, :source, :sourceNode, :targetNode, :style)";
        $stmt = $bdd->prepare($query);

        try {
            $stmt->execute([
                ':project_id' => $_POST['project_id'],
                ':category' => $_POST['edgeCategory'],
                ':comments' => $_POST['edgeComment'],
                ':source' => $_POST['edgeSource'],
                ':sourceNode' => $_POST['sourceNode'],
                ':targetNode' => $_POST['targetNode'],
                ':style' => $_POST['edgeStyle']
            ]);
            $id = $bdd->lastInsertId();
            echo $id;
        }
        catch (PDOException $e) {
            exit('Erreur lors de l\'insertion : ' . $e->getMessage());
        }
    }

    elseif (isset($_POST['action']) && $_POST['action'] === 'rotateNodes' && isset($_POST['newNodeCoordinates']))
    {

            // Forcer json_decode à renvoyer un tableau associatif (true en 2e paramètre)
            $nodeData = json_decode($_POST["newNodeCoordinates"], true);

            // Vérifier que les données JSON sont valides
            if ($nodeData && is_array($nodeData)) {
                // Préparer la requête SQL
                $query = 'UPDATE projectcontent SET position = :pos WHERE id = :id';
                $stmt = $bdd->prepare($query);

                // Parcourir les données des nœuds
                foreach ($nodeData as $id => $position) {
                    if (is_array($position)) {
                        // Convertir $position en texte JSON
                        $positionAsText = json_encode($position);
                    } else {
                        $positionAsText = (string)$position; // Si nécessaire, on convertit en texte
                    }

                    // Exécuter la requête avec les paramètres
                    $stmt->execute([
                        ':pos' => $positionAsText,
                        ':id' => $id
                    ]);

                    // Débogage pour vérifier la sortie
                    echo "id : " . $id . " position : " . $positionAsText . "<br>";
                }
            }
            else {
                echo "Erreur : données JSON non valides ou mal formatées.";
            }
    }
    elseif ( isset($_POST['action']) && ($_POST['action'] === 'addEdge')
        && isset($_POST['edgeComment'])
        && isset($_POST['sourceNode'])
        && isset($_POST['targetNode'])
        && isset($_POST['edgeCategory'])
        && isset($_POST['edgeStyle'])
        && isset($_POST['edgeDetectedSource'])

    ) {
        $query = 'INSERT INTO projectrelation (project_id, category, comments, style, sourceNode, targetNode, source)
                    VALUES (:id, :cat, :com, :sty, :sourceNode, :targetNode, :src)';
        $stmt = $bdd->prepare($query);
        $stmt->execute([':id' => $_POST['project_id'], ":cat" => $_POST['edgeCategory'],
            'com' => $_POST['edgeComment'], ':sty' => $_POST['edgeStyle'], ':sourceNode' => $_POST['sourceNode'],
            ':targetNode' => $_POST['targetNode'], ':src' => $_POST['edgeLink']]);
        $id = $bdd->lastInsertId();
        echo $id;

        if (isset($_POST['edgeDetectedSource']) && $_POST['edgeDetectedSource'] != 0)
        {
            $query = 'SELECT derivatives FROM sources WHERE id = :id';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':id' => $_POST['edgeDetectedSource']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            print_r($row);
            echo "string id = ". $_POST['edgeDetectedSource'];

            $derivatives = json_decode($row['derivatives']);
            $derivatives[] = date('Y-m-d H:i:s');

            $query = 'UPDATE sources SET derivatives = :derivatives WHERE id = :id';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':derivatives' => json_encode($derivatives), ':id' => $_POST['edgeDetectedSource']]);
        }
    }

    elseif (isset($_POST['edgeID'])
        && isset($_POST['newEdgeComments'])
        && isset($_POST['newEdgeCategory'])
        && isset($_POST['newSourceNode'])
        && isset($_POST['newTargetNode'])
        && isset($_POST['newEdgeStyle'])
        && isset($_POST['newEdgeDetectedSource'])
    ) {
        $query = 'UPDATE projectrelation SET comments = :com, category = :cat, sourceNode = :sourceNode, targetNode = :targetNode,
                           style = :sty, source = :src WHERE id = :id';
        $stmt = $bdd->prepare($query);
        $stmt -> execute([':id'=>$_POST['edgeID'], ":cat"=> $_POST['newEdgeCategory'], 'com'=> $_POST['newEdgeComments'],
            ':sourceNode'=>$_POST['newSourceNode'], ':targetNode'=>$_POST['newTargetNode'],
            ':sty' => $_POST['newEdgeStyle'], ':src' => $_POST['newEdgeLink']]);


        if ($_POST['newEdgeDetectedSource'] !== 0) {
            $query = 'SELECT derivatives FROM sources WHERE id = :id';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':id' => $_POST['newEdgeDetectedSource']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC)['derivatives'];

            $derivatives = json_decode($row);
            $derivatives[] = date('Y-m-d H:i:s');

            $query = 'UPDATE sources SET derivatives = :derivatives WHERE id = :id';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':derivatives' => json_encode($derivatives), ':id' => $_POST['newEdgeDetectedSource']]);
        }
    }

    elseif (isset($_POST['deletedNodeID'])) {
        $query = 'DELETE FROM projectcontent WHERE id = :id';
        $stmt = $bdd->prepare($query);
        $stmt -> execute([':id'=>$_POST['deletedNodeID']]);

        $query = 'DELETE FROM projectrelation WHERE (sourceNode = :id OR targetNode = :id)';
        $stmt = $bdd->prepare($query);
        $stmt -> execute([':id'=>$_POST['deletedNodeID']]);
    }

    elseif (isset($_POST['deletedEdgeID'])) {
        $query = 'DELETE FROM projectrelation WHERE id = :id';
        $stmt = $bdd->prepare($query);
        $stmt -> execute([':id'=>$_POST['deletedEdgeID']]);
    }

    elseif (isset($_POST['action']) && ($_POST['action']) === 'modifyCategoriesStatus')
    {
        echo "modifyCategoriesStatus";

        $nodeCategoriesStatus = $_POST['nodeCategoriesStatus'];
        $edgeCategoriesStatus = $_POST['edgeCategoriesStatus'];

        $query = 'UPDATE projects
          SET nodecategoriesstatus = :nodestatus,
              edgecategoriesstatus = :edgestatus
          WHERE id = :id';
        $stmt = $bdd->prepare($query);
        $stmt->execute([
            ':nodestatus' => $_POST['nodeCategoriesStatus'],
            ':edgestatus' => $_POST['edgeCategoriesStatus'],
            ':id' => $_POST['project_id']
        ]);
    }

    elseif (isset($_POST['action']) && ($_POST['action']) === 'shareProject')
    {
        $query = 'SELECT sharelist FROM projects WHERE id = :id';
        $stmt = $bdd->prepare($query);
        $stmt -> execute([':id'=>$_POST['project_id']]);
        $row = $stmt->fetch();
        $shareList = json_decode($row['sharelist'], true);

        //Pour donner un accès
        if (isset($_POST['who']) && !empty($_POST['who'])) {
            $shareList[$_POST["who"]] = true;
        }
        //Pour retirer un accès
        if (isset($_POST['noWho']) && !empty($_POST['noWho'])) {
            $shareList[$_POST["noWho"]] = false;
        }

        $query = "UPDATE projects SET sharelist = :returnJson WHERE id = :id";
        $stmt = $bdd->prepare($query);
        $stmt->execute([
            ':returnJson' => json_encode($shareList),
            ':id' => $_POST['project_id']
        ]);
    }

    elseif (isset($_POST['action']) && ($_POST['action']) === 'deleteProject')
    {
        try {
            $query = 'UPDATE projects SET owner = -1 WHERE (id = :id)';
            $stmt = $bdd->prepare($query);
            $stmt->execute([':id' => $_POST['project_id']]);
        }
        catch (PDOException $e) {
            exit('Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}

?>
