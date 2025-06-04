<?php
try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

function get_all_links($listId, $bdd) {
    // Récupérer les sous-listes de la liste donnée
    $query = 'SELECT * FROM sourcelists WHERE father = :father';
    $stmt = $bdd->prepare($query);
    $stmt->execute(['father' => $listId]);
    $subLists = $stmt->fetchAll();

    $links = [];
    foreach ($subLists as $subList) {
        $links = array_merge($links, json_decode($subList['links'], true));
        $links = array_merge($links, get_all_links($subList['id'], $bdd)); // Récursion pour les sous-listes
    }

    // Récupérer les liens directement associées à la liste courante
    $query = 'SELECT * FROM sourcelists WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute(['id' => $listId]);
    $currentList = $stmt->fetch();

    $currentLinks = json_decode($currentList['links'], true) ?? [];
    $links = array_merge($links, $currentLinks);

    return $links;
}

$listId = isset($_GET['list_id']) ? intval($_GET['list_id']) : 0;
$links = get_all_links($listId, $bdd);

// Création d'un tableau avec les informations des liens
$linkDetails = [];
foreach ($links as $linkId) {
    $query = 'SELECT * FROM links WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute(['id' => $linkId]);
    $link = $stmt->fetch();

    if ($link) {
        $linkDetails[] = [
            'id' => $link['id'],
            'name' => htmlspecialchars($link['name']),
            'url' => htmlspecialchars($link['url']),
        ];
    }
}

header('Content-Type: application/json');
echo json_encode(['links' => $linkDetails]);