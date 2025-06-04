<?php
session_start();
try {
    // On se connecte à MySQL
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {// En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());    } ;



$req = $bdd->query('SELECT * FROM sourcestags ORDER BY type');
$tags = $req->fetchAll();
$tagtype = "";
echo '<div>';
foreach ($tags as $tag) {
    if ($tag['type'] != $tagtype) {
        echo '</div>';
        echo '<h4>' . htmlspecialchars($tag['type'], ENT_QUOTES) . '</h4>';
        $tagtype = $tag['type'];
        echo '<div style="display: flex; flex-wrap: wrap; justify-content: left; padding: 10px;">';
    }
    $onchange = "console.log('onchange')";
    echo '<div class="tag-div" id="sourceTagCheckbox'.$tag['id'].'">';
    $checked = $tag['user'.$_SESSION['id_actif']] == 1 ? 'checked' : '';
    if ($tag['user'.$_SESSION['id_actif']] == 2) {
        echo '<button class="tag-button" onclick="console.log(\'Niveau0\'); AddFavTag('.$tag['id'].', 0)"><img src="Includes\Images\Icone%20moins.png" height="15px">' . htmlspecialchars($tag['name'], ENT_QUOTES) . '</button>';
    } else {
        echo '<button class="tag-button" onclick="console.log(\'Niveau0\'); AddFavTag(' . $tag['id'] . ', 1)"><img src="Includes\Images\Icone%20Plus.png" height="15px">' . htmlspecialchars($tag['name'], ENT_QUOTES) . '</button>';
    }
    echo '</div>';
}
?>