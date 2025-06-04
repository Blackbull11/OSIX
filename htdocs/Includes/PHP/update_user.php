<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connexion à la base de données
        $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les données postées
        $userId = intval($_POST['user_id']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Les groupes cochés
        $groups = isset($_POST['groups']) ? array_map('intval', json_decode($_POST['groups'])) : [];

        // Mise à jour des informations utilisateurs
        $updateQuery = "
            UPDATE users 
            SET username = :username, 
                password = :password, 
                groupes = :groupes 
            WHERE id = :user_id
        ";
        $stmt = $bdd->prepare($updateQuery);
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':groupes' => json_encode($groups),
            ':user_id' => $userId,
        ]);

        echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour avec succès.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>