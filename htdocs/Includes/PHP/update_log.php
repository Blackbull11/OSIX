<?php
// Démarrer la session
session_start();

try {
    // Connexion à la base de données MySQL
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367;charset=utf8', 'usersauv', 'S@uvBaseosix25*');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    // En cas d'erreur, répondre avec un message JSON
    echo json_encode(['status' => 'error', 'message' => 'Erreur de connexion à la base de données']);
    exit;
}

// Vérifier si une action a été envoyée via POST
if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Aucune action spécifiée']);
    exit;
}

// Filtrage/Préparation des actions possibles
$action = $_POST['action'];

// Préparer la réponse par défaut
$response = ['status' => 'error', 'message' => 'Action non reconnue'];

// Exécution des actions en fonction de la valeur de "action"
switch ($action) {
    case 'update_box':
        // Exécuter une requête pour enregistrer ou mettre à jour une action "box"
        try {
            $userId = $_SESSION['id_actif'] ?? null; // ID utilisateur depuis la session (optionnel)
            $query = "UPDATE log SET box = 1 WHERE user_id = :user_id ORDER BY date DESC LIMIT 1";
            $stmt = $bdd->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
            ]);

            $response = ['status' => 'success', 'message' => 'Action "Box" enregistrée avec succès'];
        } catch (Exception $e) {
            $response = ['status' => 'error', 'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()];
        }
        break;

    case 'update_glorix':
        // Traite une action de type "glorix"
        try {
            $userId = $_SESSION['id_actif'] ?? null; // ID utilisateur depuis la session (optionnel)
            $query = "UPDATE log SET glorix = 1 WHERE user_id = :user_id ORDER BY date DESC LIMIT 1";
            $stmt = $bdd->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
            ]);

            $response = ['status' => 'success', 'message' => 'Action "Glorix" enregistrée avec succès'];
        } catch (Exception $e) {
            $response = ['status' => 'error', 'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()];
        }
        break;

    case 'update_epix':
        // Gère l'action de type "epix"
        try {
            $userId = $_SESSION['id_actif'] ?? null; // ID utilisateur depuis la session (optionnel)
            $query = "UPDATE log SET epix = 1 WHERE user_id = :user_id ORDER BY date DESC LIMIT 1";
            $stmt = $bdd->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
            ]);

            $response = ['status' => 'success', 'message' => 'Action "Epix" enregistrée avec succès'];
        } catch (Exception $e) {
            $response = ['status' => 'error', 'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()];
        }
        break;

    default:
        // Si l'action n'est pas reconnue
        $response = ['status' => 'error', 'message' => 'Action inconnue'];
        break;
}

// Retourner la réponse au format JSON pour AJAX
header('Content-Type: application/json');
echo json_encode($response);