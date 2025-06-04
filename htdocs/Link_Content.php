<?php

// Connexion à la base de données
try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

// Fonction pour télécharger un fichier et l'enregistrer localement
function downloadFile($url, $folder = 'Link_Content') {
    // Vérifier si le dossier existe, si non, le créer
    if (!file_exists($folder)) {
        mkdir($folder, 0755, true);
    }

    // Récupérer le contenu du fichier en ligne
    $fileContent = file_get_contents($url);

    // Vérifier si le téléchargement a échoué
    if ($fileContent === false) {
        return false; // Télécharger échoué
    }

    // Extraire l'extension du fichier
    $pathInfo = pathinfo($url);
    $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';

    // Vérifier les formats autorisés
    $allowedExtensions = ['html', 'png', 'jpg', 'jpeg'];
    if (!in_array($extension, $allowedExtensions)) {
        return false; // Format du fichier non autorisé
    }

    // Générer un nom de fichier unique
    $filename = uniqid('file_', true) . '.' . $extension;

    // Chemin complet du fichier local
    $filePath = $folder . '/' . $filename;

    // Sauvegarder le contenu dans le fichier local
    file_put_contents($filePath, $fileContent);

    // Retourner le chemin complet du fichier
    return $filePath;
}

// Exemple d'URL à sauvegarder (remplacez ceci par vos liens dynamiques)
$url = "https://example.com/file.html";

// Appeler la fonction pour télécharger le fichier
$filePath = downloadFile($url);

if ($filePath !== false) {
    echo "Fichier téléchargé et sauvegardé en : $filePath";
    $insertQuery = "INSERT INTO links (url, document) VALUES (:url, :document)";
    $stmt = $bdd->prepare($insertQuery);
    $stmt->execute([
        'url' => $url,
        'document' => $filePath
    ]);

    echo "Lien enregistré dans la base de données.";
} else {
    echo "Échec du téléchargement du fichier ou type de fichier non autorisé.";
}
?>