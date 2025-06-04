<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filename'])) {
    $file = __DIR__ . '/EPIX_Images/' . basename($_POST['filename']);

    if (is_file($file)) {
        if (unlink($file)) {
            echo json_encode(['success' => true, 'message' => 'Fichier supprimé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Impossible de supprimer le fichier.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Fichier inexistant.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
?>