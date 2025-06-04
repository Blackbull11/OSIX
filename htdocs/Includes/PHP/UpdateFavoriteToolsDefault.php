<?php
try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['tool_id']) || !isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit;
}

$toolId = (int)$data['tool_id'];
$userId = (int)$data['user_id'];
$toolImage = $data['tool_image'];
$query = 'SELECT tools FROM favoritetools WHERE user_id = :user_id';
$stmt = $bdd->prepare($query);
$stmt->execute(['user_id' => $userId]);
$favToolsJson = $stmt->fetchColumn();
$favTools = $favToolsJson ? json_decode($favToolsJson, true) : [];

if (isset($favTools[$toolId])) {
    // Supprimer l'outil des favoris
    unset($favTools[$toolId]);
} else {
    // Ajouter l'outil aux favoris
    $favTools[$toolId] = $toolImage; // Remplacez par l'image associée.
}

$newFavToolsJson = json_encode($favTools);

$query = 'UPDATE favoritetools SET tools = :tools WHERE user_id = :user_id';
$stmt = $bdd->prepare($query);
if ($stmt->execute(['tools' => $newFavToolsJson, 'user_id' => $userId])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Échec lors de la mise à jour.']);
}
?>