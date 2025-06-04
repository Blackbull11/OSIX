<?php

session_start();

try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    echo '<label for="sharePeople">Partager le projet avec...</label>
                <select id="sharePeople">
                <option value="">Sans objet</option>
                ';

    $query = "SELECT id, username from users WHERE id != :id";
    $stmt = $bdd->prepare($query);
    $stmt -> execute(array(':id' => $_SESSION['id_actif']));
    $options = $stmt->fetchAll();

    $query = "SELECT sharelist from projects where id = :id";
    $stmt = $bdd->prepare($query);
    $stmt -> execute(array(':id' => $_POST['idProject']));
    $shareList = $stmt->fetch();


    $list = json_decode($shareList['sharelist'], true);

    foreach ($options as $option) {

        if (!(key_exists($option['id'], $list) && $list[$option['id']] === true)) {
            echo '<option value="' . $option['id'] . '">' . $option['username'] . '</option>';
        }
    }

    echo '</select>
          <label for="noSharePeople">Ne plus partager le projet avec...</label>
          <select id="noSharePeople">
          <option value="">Sans objet</option>
';

    foreach ($options as $option) {
        if ( key_exists($option['id'], $list) && $list[$option['id']] === true) {
            echo '<option value="' . $option['id'] . '">' . $option['username'] . '</option>';
        }
    }

    echo '</select>';



}
catch (Exception $e) {
    // Gestion des erreurs (par exemple : base de données inaccessible)
    echo json_encode([
        'success' => false,
        'message' => 'Erreur côté serveur : ' . $e->getMessage()
    ]);
}