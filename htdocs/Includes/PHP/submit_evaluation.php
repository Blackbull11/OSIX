<?php
session_start();
// Connexion à la base de données
try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    ]);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

// Vérifie si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données envoyées via le formulaire
    $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : null; // Assurez-vous que source_id est bien un entier
    $grade = $_POST['grade'] ?? '';
    $comment = $_POST['commentare'] ?? '';
    $useframe = $_POST['useframe'] ?? '';
    $eval_status = 'Evaluée';


    // Génération de valeurs par défaut pour les "features"
    $total_features = 10; // Nombre total de checkbox/positions attendu
    $features = array_fill(0, $total_features, 0); // Tableau rempli de 0 (par défaut)

    // Remplir le tableau "features" en fonction des cases cochées
    if (isset($_POST['features']) && is_array($_POST['features'])) {
        foreach ($_POST['features'] as $key => $value) {
            if (is_numeric($key) && $key >= 0 && $key < $total_features) {
                $features[$key] = 1; // Marquer `1` pour chaque case cochée
            }
        }
    }

    // Génération de la date actuelle au format "Y-m-d H:i:s"
    $date = date('Y-m-d H:i:s');

    // Vérification des paramètres obligatoires
    if (!$source_id || empty($grade) || empty($useframe) || empty($eval_status)) {
        die('Paramètres manquants');
    }

    // Transformation des données en JSON pour le champ "cotation"
    $cotation_data = [
        'date' => $date,
        'grade' => $grade,
        'comment' => $comment,
        'features' => json_encode($features), // Encodage JSON de $features
        'useframe' => $useframe,
        'eval_status' => $eval_status
    ];

    // Conversion du tableau en format JSON
    $cotation_json = json_encode($cotation_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    try {
        // Mise à jour de la colonne "cotation" pour le source_id correspondant
        $stmt = $bdd->prepare('UPDATE sources SET cotation = :cotation WHERE id = :source_id');
        $stmt->bindParam(':cotation', $cotation_json, PDO::PARAM_STR);
        $stmt->bindParam(':source_id', $source_id, PDO::PARAM_INT);
        $stmt->execute();

        echo 'Données mises à jour avec succès !';
    } catch (Exception $e) {
        die('Erreur lors de la mise à jour : ' . $e->getMessage());
    }

    $req = "SELECT followers FROM sources WHERE id = $source_id";
    $stmt = $bdd->prepare($req);
    $stmt->execute();
    $followers = $stmt->fetch()['followers'];
    $source_name = $bdd->query("SELECT name FROM sources WHERE id = $source_id")->fetchColumn();
    $message = 'La source '.$source_name.'que vous suivez a été cotée '.$grade.' ! Rendez-vous sur GLORIX pour voir le détail de cette cotation.';
    $req = "INSERT INTO news (issuer, receiver, message, type) VALUES (".$_SESSION['id_actif'].", {\"users\": ".$followers.", \"groups\": [], \"viewed\": []}', ".$message.", 'GLORIX')";



}
?>