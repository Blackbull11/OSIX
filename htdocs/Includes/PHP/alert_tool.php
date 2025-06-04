<?php
session_start();

// Connexion à la base de données
try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

// Vérifiez que l'utilisateur est connecté
if (!isset($_SESSION['id_actif'])) {
    echo "Vous devez être connecté pour signaler un outil.";
    exit;
}

// Vérifiez que la méthode est POST et que les données nécessaires sont présentes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tool_id'], $_POST['user_id'])) {
    $tool_id = $_POST['tool_id'];
    $user_id = $_POST['user_id'];

    // Validation des données (par exemple, s'assurer qu'il s'agit d'entiers)
    if (!is_numeric($tool_id) || !is_numeric($user_id)) {
        echo "Données invalides.";
        exit;
    }
    $req = "SELECT * FROM tools WHERE id = :id";
    $stmt = $bdd->prepare($req);
    $stmt->execute(['id' => $tool_id]);
    $tool = $stmt->fetch();
    $req = "SELECT * FROM users WHERE id = :id";
    $stmt = $bdd->prepare($req);
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    // Insérez un signalement dans la table `alerts` ou créez une table appropriée pour gérer les signalements
    $alert_message = "L'utilisateur ". $user['username'] ."  a signalé l'outil " . $tool['name'] .".";

    try {
        // Requête pour enregistrer le signalement
        $insert = $bdd->prepare('
            INSERT INTO news (issuer, receiver, type, message) 
            VALUES (:user_id, :receiver, :type, :message)
        ');
        $insert->execute([
            'user_id' => $user_id,
            'receiver' => '{"users": [], "groups": [1], "viewed": {}}',
            'type' => 'ADMIN',
            'message' => $alert_message,
        ]);

        echo "L'outil a été signalé avec succès. Un administrateur examinera votre signalement sous peu.";
    } catch (Exception $e) {
        echo "Une erreur s'est produite lors de l'enregistrement du signalement : " . $e->getMessage();
        exit;
    }
} else {
    echo "Requête invalide.";
    exit;
}