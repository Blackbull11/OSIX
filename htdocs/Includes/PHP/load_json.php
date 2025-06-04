<?php
session_start();

header('Content-Type: application/json;charset=utf-8');

try {
    // Connexion à la base de données
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur de connexion, on retourne un message JSON d'erreur
    echo json_encode(['error' => 'Erreur : ' . $e->getMessage()]);
    exit;
}


if (isset($_POST['action']) && ($_POST['action'] === 'load_graph')) {
// Initialisation des résultats sous forme d'objets JSON
    $result = [
        'nodes' => [],
        'edges' => []
    ];

// Récupération des "nodes" (sommets)
    $query = 'SELECT * FROM projectcontent WHERE project_id = :id'; //' AND version = :v';
    $stmt = $bdd->prepare($query);
    $stmt->execute([
        ':id' => $_SESSION['project_id']]);
    $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($nodes as $node) {
        $position = json_decode($node['position'], true); // true pour obtenir un tableau associatif

        $result['nodes'][$node['id']] = [
            'name' => $node['name'],
            'x' => $position['x'],
            'y' => $position['y'],
            'comments' => $node['comments'],
            'id' => $node['id'],
            'category' => $node['category'],
            'icon' => $node['icon'],
            'image' => $node['image'],
            'source' => $node['source'],
            'geoCoord' => $node['geoCoord']
        ];
    }

// Récupération des "edges" (relations)
    $query = 'SELECT * FROM projectrelation WHERE project_id = :id'; //AND version = :v';
    $stmt = $bdd->prepare($query);
    $stmt->execute([
        ':id' => $_SESSION['project_id']]);

    $relations = $stmt->fetchAll(PDO::FETCH_ASSOC);



    foreach ($relations as $relation) {
        $style = json_decode($relation['style'], true); // true pour obtenir un tableau associatif

        $result['edges'][$relation['id']] = [
            'sourceNode' => $relation['sourceNode'],
            'targetNode' => $relation['targetNode'],
            'category' => $relation['category'],
            'comments' => $relation['comments'],
            'source' => $relation['source'],
            'thickness' => $style['thickness'],        //couleur trait, couleur des pointes, épaisseur.
            'segmentColor' => $style['segmentColor'],
            'type' => $style['type'],
            'arrowColor' => $style['arrowColor'],
            'id' => $relation['id'],
        ];
    }

// Retour des résultats au format JSON
    echo json_encode($result, JSON_PRETTY_PRINT);
}


// Pour charger les catégories
elseif (isset($_POST['action']) && ($_POST['action'] === 'load_categories')) {

// Récupérer l'ID du projet
    $projectId = $_SESSION['project_id'];
// Récupérer l'ID du propriétaire du projet
    $query = 'SELECT owner FROM projects WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute([
        ':id' => $projectId
    ]);
    $projectOwner = $stmt->fetch(PDO::FETCH_ASSOC);

// Étape 1 : Récupérer les données JSON depuis la base de données
    $query = 'SELECT nodecategoriesstatus FROM projects WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $projectId]);
    $jsonData = $stmt->fetchColumn();

    if (!$jsonData) {
        die('Aucune donnée trouvée pour nodecategoriesstatus.');
    }

    $query2 = 'SELECT edgecategoriesstatus FROM projects WHERE id = :id';
    $stmt2 = $bdd->prepare($query2);
    $stmt2->execute([':id' => $projectId]);
    $jsonData2 = $stmt2->fetchColumn();

    if (!$jsonData2) {
        die('Aucune donnée trouvée pour edgecategoriesstatus.');
    }

// Décoder les données JSON depuis les colonnes
    $categoriesStatus = json_decode($jsonData, true);
    $categoriesStatus2 = json_decode($jsonData2, true);

// Vérifier si les JSON sont valides
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($categoriesStatus)) {
        die('Le JSON décodé pour nodecategoriesstatus est invalide ou vide.');
    }
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($categoriesStatus2)) {
        die('Le JSON décodé pour edgecategoriesstatus est invalide ou vide.');
    }

// Préparer le tableau de réponse
    $result = [
        'nodeCategories' => [], // Contient les catégories de noeuds
        'edgeCategories' => [],
        'nodeClasses' => [],
        'edgeClasses' => []
    ];

// Étape 2 : Récupérer les détails des catégories depuis la table `nodescategories`
    if (!empty($categoriesStatus)) {
        $query = "SELECT id, name, owner, class FROM nodescategories WHERE owner IN (0, -1, :owner)";
        $stmt = $bdd->prepare($query);

        // Exécuter avec les IDs récupérés dans $categoriesStatus
        $stmt->execute([':owner' => $projectOwner['owner']]);
        $nodesCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Construire le résultat
        foreach ($nodesCategories as $category) {
            $categoryId = $category['id'];
            $result['nodeCategories'][$categoryId] = [
                'name' => $category['name'],
                'actif' => isset($categoriesStatus[$categoryId]) ? $categoriesStatus[$categoryId] : 0,
                'owner' => $category['owner'],
                'class' => $category['class']
            ];
        }
    }

    if (!empty($categoriesStatus2)) {
        $query = "SELECT id, name, owner, isOriented, default_style, class FROM relationscategories WHERE owner IN (0, :owner)";
        $stmt = $bdd->prepare($query);
        $stmt->execute([':owner' => $projectOwner['owner']]);
        $relationsCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($relationsCategories as $category) {
            $categoryId = $category['id'];
            $style = json_decode($category['default_style'], true); // true pour obtenir un tableau associatif

            $result['edgeCategories'][$categoryId] = [
                'name' => $category['name'],
                'isOriented' => $category['isOriented'],
                'actif' => isset($categoriesStatus2[$categoryId]) ? $categoriesStatus2[$categoryId] : 0,
                'owner' => $category['owner'],
                'type' => $style['type'],
                'arrowColor' => $style['arrowColor'],
                'segmentColor' => $style['segmentColor'],
                'thickness' => $style['thickness'],
                'class' => $category['class'],
            ];
        }


        $stmtNodeClasses = $bdd->prepare('SELECT id, name FROM nodecategoriesclasses');
        $stmtNodeClasses->execute([]);
        $nodesClasses = $stmtNodeClasses->fetchAll(PDO::FETCH_ASSOC);
        foreach ($nodesClasses as $class) {
            $result['nodeClasses'][$class['id']] = $class['name'];
        }

        $stmtRelationsClasses = $bdd->prepare('SELECT id, name FROM relationcategoriesclasses');
        $stmtRelationsClasses->execute([]);
        $relationsCategoriesClasses = $stmtRelationsClasses->fetchAll(PDO::FETCH_ASSOC);
        foreach ($relationsCategoriesClasses as $class) {
            $result['edgeClasses'][$class['id']] = $class['name'];
        }

    }

// Étape 3 : Ajout d'autres données si nécessaire
// Vous pouvez ajouter les données pour edgeCategories ici si nécessaire

// Retourner le résultat sous forme JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

?>
