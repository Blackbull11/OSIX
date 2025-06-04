<?php
// Démarrer une session
session_start();

try {
    // Connexion à la base de données
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

// Vérifiez si le type d'action est spécifié
if (!isset($_POST['action'])) {
    echo json_encode(["error" => "Aucune action spécifiée."]);
    exit;
}

// Récupérer le type d'action
$action = $_POST['action'];

// Vérifier si un utilisateur est connecté pour les actions nécessitant une session valide
if (!isset($_SESSION['id_actif'])) {
    echo json_encode(["error" => "Vous devez être connecté pour effectuer cette action."]);
    exit;
}

// Traiter chaque action en fonction de la clé d'identification
switch ($action) {
    case "change_password":
        // Modifier le mot de passe
        if (!isset($_POST['new_password'])) {
            echo json_encode(["error" => "Mot de passe non fourni."]);
            exit;
        }

        $newPassword = $_POST['new_password'];
        $stmt = $bdd->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $_SESSION['id_actif'],
        ]);
        echo json_encode(["message" => "Mot de passe modifié avec succès."]);
        break;

    case "load_groups":
        // Charger les groupes de l'utilisateur
        $stmt = $bdd->prepare("SELECT groups FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['id_actif']]);
        $groups = json_decode($stmt->fetchColumn());

        $groupDetails = [];
        foreach ($groups as $groupId) {
            $stmt = $bdd->prepare("SELECT id, name FROM usergroups WHERE id = :id");
            $stmt->execute(['id' => $groupId]);
            $groupDetails[] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        echo json_encode(["groups" => $groupDetails]);
        break;

    case "manage_group":
        // Ajouter ou retirer un groupe
        if (!isset($_POST['group_id']) || !isset($_POST['action_type'])) {
            echo json_encode(["error" => "Données invalides pour gérer le groupe."]);
            exit;
        }

        $groupId = (int)$_POST['group_id'];
        $actionType = $_POST['action_type']; // "add" ou "remove"

        $stmt = $bdd->prepare("SELECT groupes FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['id_actif']]);
        $groups = json_decode($stmt->fetchColumn());

        if ($actionType === "add" && !in_array($groupId, $groups)) {
            $groups[] = $groupId;
        } elseif ($actionType === "remove") {
            $groups = array_diff($groups, [$groupId]);
        }

        $stmt = $bdd->prepare("UPDATE users SET groupes = :groups WHERE id = :id");
        $stmt->execute([
            'groups' => json_encode($groups),
            'id' => $_SESSION['id_actif'],
        ]);
        echo json_encode(["message" => "Groupes mis à jour."]);
        break;

    case "request_group":
        // Demander la création d'un groupe
        if (!isset($_POST['group_name'])) {
            echo json_encode(["error" => "Nom du groupe non fourni."]);
            exit;
        }

        $groupName = $_POST['group_name'];
        $stmt = $bdd->prepare("INSERT INTO news_admin (type, content) VALUES ('group_request', :content)");
        $stmt->execute([
            'content' => json_encode(["user" => $_SESSION['id_actif'], "name" => $groupName]),
        ]);
        echo json_encode(["message" => "Demande de groupe envoyée avec succès."]);
        break;

    case "export_settings":
        // Exporter les paramètres de l'utilisateur
        $stmt = $bdd->prepare("SELECT groups, tools, sourcelists FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['id_actif']]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="account_settings.json"');
        echo json_encode($settings);
        exit; // Important pour éviter tout contenu supplémentaire dans l'exportation.

    case "import_settings":
        // Importer les paramètres de l'utilisateur
        if (!isset($_FILES['settings_file'])) {
            echo json_encode(["error" => "Fichier manquant pour l'import."]);
            exit;
        }

        $fileContent = file_get_contents($_FILES['settings_file']['tmp_name']);
        $importedData = json_decode($fileContent, true);

        if (!$importedData) {
            echo json_encode(["error" => "Erreur dans le fichier importé."]);
            exit;
        }

        // Vérification et application des paramètres à importer
        if (isset($importedData['groups'])) {
            $stmt = $bdd->prepare("UPDATE users SET groups = :groups WHERE id = :id");
            $stmt->execute([
                'groups' => json_encode($importedData['groups']),
                'id' => $_SESSION['id_actif'],
            ]);
        }

        if (isset($importedData['tools'])) {
            $stmt = $bdd->prepare("UPDATE users SET tools = :tools WHERE id = :id");
            $stmt->execute([
                'tools' => json_encode($importedData['tools']),
                'id' => $_SESSION['id_actif'],
            ]);
        }

        if (isset($importedData['sourcelists'])) {
            $stmt = $bdd->prepare("UPDATE users SET sourcelists = :sourcelists WHERE id = :id");
            $stmt->execute([
                'sourcelists' => json_encode($importedData['sourcelists']),
                'id' => $_SESSION['id_actif'],
            ]);
        }

        echo json_encode(["message" => "Paramètres importés avec succès."]);
        break;

    default:
        echo json_encode(["error" => "Action inconnue."]);
        break;
}