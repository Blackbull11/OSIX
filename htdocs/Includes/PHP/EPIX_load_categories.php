<?php
session_start();

// Vérifier le type d'action et exécuter l'opération correspondante
try {
    // Connexion à la base de données (remplacer les informations par vos propres données d'accès)
    $pdo = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_POST['action'] === 'loadCategories') {
        // Récupérer les catégories des deux tables (nodescategories et relationscategories)

        $stmtNodes = $pdo->prepare('SELECT id, name, class FROM nodescategories WHERE owner = :own');
        $stmtNodes->execute([':own' => $_SESSION['id_actif']]);
        $nodesCategories = $stmtNodes->fetchAll(PDO::FETCH_ASSOC);

        $stmtRelations = $pdo->prepare('SELECT id, name, class FROM relationscategories WHERE owner = :own');
        $stmtRelations->execute([':own' => $_SESSION['id_actif']]);
        $relationsCategories = $stmtRelations->fetchAll(PDO::FETCH_ASSOC);

        // Retourner les résultats sous forme de JSON
        echo json_encode([
            'nodesCategories' => $nodesCategories,
            'relationsCategories' => $relationsCategories
        ]);

    }

    elseif ($_POST['action'] === 'loadCommonCategories')
    {
        $stmtNodes = $pdo->prepare('SELECT id, name, class FROM nodescategories WHERE owner = :own');
        $stmtNodes->execute([':own' => 0]);
        $nodesCategories = $stmtNodes->fetchAll(PDO::FETCH_ASSOC);

        $stmtRelations = $pdo->prepare('SELECT id, name, class FROM relationscategories WHERE owner = :own');
        $stmtRelations->execute([':own' => 0]);
        $relationsCategories = $stmtRelations->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'commonNodesCategories' => $nodesCategories,
            'commonRelationsCategories' => $relationsCategories,
        ]);
    }

    elseif ($_POST['action'] === 'loadNodeCategoriesClasses')
    {
        $query = 'SELECT id, name FROM nodeCategoriesClasses';
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $nodeCategoriesClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($nodeCategoriesClasses as $nodeCategoryClass) {
            echo '<option value="' . $nodeCategoryClass['id'] . '">' . $nodeCategoryClass['name'] . '</option>';
        }
    }

    elseif ($_POST['action'] === 'loadRelationCategoriesClasses')
    {
        $query = 'SELECT id, name FROM relationCategoriesClasses';
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $relationCategoriesClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($relationCategoriesClasses as $relationCategoryClass) {
            echo '<option value="' . $relationCategoryClass['id'] . '">' . $relationCategoryClass['name'] . '</option>';
        }
    }

}

catch (Exception $e) {
    // Gestion des erreurs (par exemple : base de données inaccessible)
    echo json_encode([
        'success' => false,
        'message' => 'Erreur côté serveur : ' . $e->getMessage()
    ]);
}

?>