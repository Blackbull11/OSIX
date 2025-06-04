<?php
try {
    // Connexion à la base de données
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        // Vérifier et appliquer les actions demandées
        if ($action === 'update') {
            $content = $_POST['content'] ?? '';
            if (empty($content)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Contenu vide non autorisé.']);
                exit;
            }

            // Mettre à jour le message d'alerte
            $query = "UPDATE constantes SET valeur = :content WHERE name = 'Alert_Content'";
            $stmt = $bdd->prepare($query);
            $stmt->bindParam(':content', $content);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Message d\'alerte mis à jour.']);
        } elseif ($action === 'empty') {
            // Vider le contenu de la constante
            $query = "UPDATE constantes SET valeur = '' WHERE name = 'Alert_Content'";
            $bdd->exec($query);

            echo json_encode(['success' => true, 'message' => 'Message d\'alerte vidé.']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action non spécifiée ou invalide.']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur interne : ' . $e->getMessage()]);
}