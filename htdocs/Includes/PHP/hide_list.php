<?php
// Connexion à la base de données
try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['list_id'])) {
    $list_id = (int)$_POST['list_id'];
    $restore = isset($_POST['restore']) && $_POST['restore'] === 'true';

    if ($restore) {
        // Restauration : mettre à jour le statut à 1
        $query = 'UPDATE sourcelists SET status = 1 WHERE id = :list_id';
    } else {
        // Masquer : mettre à jour le statut à 0
        $query = 'UPDATE sourcelists SET status = 0 WHERE id = :list_id';
    }

    $stmt = $bdd->prepare($query);
    if ($stmt->execute(['list_id' => $list_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Impossible de mettre à jour la liste.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
?>