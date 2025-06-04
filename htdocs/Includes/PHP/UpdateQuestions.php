<?php
try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367;charset=utf8', 'usersauv', 'S@uvBaseosix25*');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST['questions'] as $id => $question) {
            $stmt = $bdd->prepare('UPDATE evalquestions SET question = :question WHERE id = :id');
            $stmt->execute(['question' => $question, 'id' => $id]);
        }

        echo json_encode(['success' => true, 'message' => 'Modifications enregistrées.']);
    } else {
        throw new Exception('Requête invalide.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>