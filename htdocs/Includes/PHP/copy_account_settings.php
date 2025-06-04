<?php
session_start();

// Connexion à la base de données
try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

// Vérifiez que l'utilisateur est connecté
if (!isset($_SESSION['id_actif'])) {
    echo "Vous devez être connecté pour effectuer cette action.";
    exit;
}

// Récupération des données envoyées par la requête
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['demand'])) {
    $requested_username = $_POST['username'] ?? '';

    // Vérifie si l'utilisateur demandé existe
    $req = $bdd->prepare('SELECT id FROM users WHERE username = :username');
    $req->execute(['username' => $requested_username]);
    $target_user = $req->fetch();

    if (!$target_user) {
        echo "L'utilisateur demandé n'existe pas.";
        exit;
    }

    // Préparer le message pour la table news_admin
    $requesting_user_id = $_SESSION['id_actif']; // L'utilisateur qui effectue la demande
    $message = sprintf(
        "L'utilisateur avec l'ID %d a demandé à copier les paramètres de l'utilisateur '%s'.",
        $requesting_user_id,
        htmlspecialchars($requested_username)
    );

    // Ajouter une entrée à la table news_admin
    $insert = $bdd->prepare('INSERT INTO news_admin (message) VALUES (:message)');
    $insert->execute(['message' => $message]);

    // Générer un retour avec un bouton pour effectuer la copie
    echo "Demande envoyée. Cliquez sur le bouton ci-dessous pour confirmer.";
    echo '<form method="POST" action="Includes/PHP/copy_account_settings.php">';
    echo '<input type="hidden" name="confirm" value="1">';
    echo '<input type="hidden" name="target_user_id" value="' . htmlspecialchars($target_user['id']) . '">';
    echo '<button type="submit">Confirmer la copie</button>';
    echo '</form>';
} elseif (isset($_POST['confirm']) && $_POST['confirm'] == '1') {
    // Validation de l'action par le bouton
    $requesting_user_id = $_SESSION['id_actif'];
    $target_user_id = $_POST['target_user_id'] ?? null;

    if (!$target_user_id) {
        echo "Une erreur est survenue. Impossible de réaliser cette opération.";
        $req = "INSERT into news (issuer, receiver, message, type) VALUES (:issuer, :receiver, :message, :type)";
        $message = "La copie des paramètres d'un autre utilisateur dans votre profil a échoué :( Assurez-vous que ce compte est bien valide.";
        $insert = $bdd->prepare($req);
        $insert->execute([
            'issuer' => $_SESSION['id_actif'],
            'receiver' => $requesting_user_id,
            'message' => $message,
            'type' => 'GENERAL',
        ]);
        exit;
    }

    // Logique de copie des paramètres d'un utilisateur :
    // Exemple : Copier les groupes et d'autres paramètres
    $req = $bdd->prepare('SELECT * FROM users WHERE id = :id');
    $req->execute(['id' => $target_user_id]);
    $target_user_data = $req->fetch();
    $target_user_name = $target_user_data['username'];

    if (!$target_user_data) {
        echo "Impossible de récupérer les données de l'utilisateur cible.";
        $req = "INSERT into news (issuer, receiver, message, type) VALUES (:issuer, :receiver, :message, :type)";
        $message = "La copie des paramètres d'un autre utilisateur dans votre profil a échoué :( Assurez-vous que ce compte est bien valide.";
        $insert = $bdd->prepare($req);
        $insert->execute([
            'issuer' => $_SESSION['id_actif'],
            'receiver' => $requesting_user_id,
            'message' => $message,
            'type' => 'GENERAL',
        ]);
        exit;
    }

    // Mise à jour des groupes
    $update = $bdd->prepare('UPDATE users SET groupes = :groupes WHERE id = :id');
    $update->execute([
        'groupes' => $target_user_data['groupes'],
        'id' => $requesting_user_id,
    ]);
    // Mise à jour des outils
    $update = $bdd->prepare('UPDATE users SET groupes = :groupes WHERE id = :id');
    $update->execute([
        'groupes' => $target_user_data['groupes'],
        'id' => $requesting_user_id,
    ]);

    $req = "INSERT into news (issuer, receiver, message, type) VALUES (:issuer, :receiver, :message, :type)";
    $message = "La copie des paramètres de $target_user_name dans votre profil a été validée par un administrateur ! Lancez-vous avec votre nouvelle configuration !";
    $insert = $bdd->prepare($req);
    $insert->execute([
        'issuer' => $_SESSION['id_actif'],
        'receiver' => $requesting_user_id,
        'message' => $message,
        'type' => 'GENERAL',
    ]);
    echo "Les paramètres de l'utilisateur ont été copiés avec succès.";
} else {
    echo "Requête inconnue.";
}