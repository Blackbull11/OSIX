<?php
session_start();

try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
}
catch (Exception $e) {
    exit('Erreur : ' . $e->getMessage());
}

// Vérification des données POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $action_follow = isset($_POST['follow']);
    $action_unfollow = isset($_POST['unfollow']);

    if ($source_id <= 0 || ($user_id <= 0 && !$_POST['admin'])) {
        echo json_encode(["status" => "error", "message" => "Identifiants invalides."]);
        exit;
    }

    try {
        // Récupérer la liste actuelle des followers
        $query = "SELECT followers FROM sources WHERE id = :source_id";
        $stmt = $bdd->prepare($query);
        $stmt->execute([':source_id' => $source_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            echo json_encode(["status" => "error", "message" => "Source introuvable."]);
            exit;
        }

        // Chargement et initialisation de la liste des followers
        $followers = json_decode($result['followers'], true);
        if (!is_array($followers)) {
            $followers = []; // Initialiser comme un tableau vide si JSON invalide ou null
        }

        // Gérer l'ajout ou le retrait de l'user_id
        if ($action_follow && !in_array($user_id, $followers)) {
            $followers[] = $user_id; // Ajouter user_id
        } elseif ($action_unfollow && in_array($user_id, $followers)) {
            $followers = array_filter($followers, function ($follower) use ($user_id) {
                return $follower !== $user_id; // Enlever user_id
            });
        }

        // Mise à jour des followers dans la base de données (encoder en JSON)
        $update_query = "UPDATE sources SET followers = :followers WHERE id = :source_id";
        $update_stmt = $bdd->prepare($update_query);
        $update_stmt->execute([
            ':followers' => json_encode(array_values($followers)), // Réindexer les clés du tableau
            ':source_id' => $source_id,
        ]);

        echo json_encode(["status" => "success", "message" => "Mise à jour effectuée avec succès."]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Requête invalide."]);
}


?>
