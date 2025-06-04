
<?php
session_start();
// Activer le support JSON dans les en-têtes HTTP
header('Content-Type: application/json');

// Lire les données envoyées avec la requête (POST JSON)
$request = json_decode(file_get_contents('php://input'), true);

// Vérifier si les données sont présentes
if (!$request || !isset($request['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Requête invalide ou action manquante.'
    ]);
    exit;
}

// Récupérer l'action que doit effectuer le serveur
$action = $request['action'];
$type = isset($request['type']) ? $request['type'] : null;
$category = isset($request['category']) ? $request['category'] : null;

// Vérifier le type d'action et exécuter l'opération correspondante
try {
    // Connexion à la base de données (remplacer les informations par vos propres données d'accès)
    $pdo = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($action === 'addCategory' && $category) {

        if ($type === 'node') {
            // Ajouter une catégorie
            $stmt = $pdo->prepare('INSERT INTO nodescategories (name, owner, class) VALUES (:name, :own, :class)');
            $stmt->execute([
                ':name' => $category['name'],
                ':own' => $category['owner'],
                ':class' => $category['class']
            ]);

            $id = $pdo->lastInsertId();
            echo $id;
        } else if ($type === 'edge') {
            // Ajouter une catégorie
            $stmt = $pdo->prepare('INSERT INTO relationscategories (name, owner, isOriented, class, default_style) VALUES (:name, :own, :isOriented, :class, :default_style)');
            $stmt->execute([
                ':name' => $category['name'],
                ':own' => $category['owner'],
                ':isOriented' => $category['isOriented'],
                ':default_style' => $category['default_style'],
                ':class' => $category['class']
            ]);

            $id = $pdo->lastInsertId();
            echo $id;
        }

    }

    elseif ($action === 'editCategory' && $type && $category) {
        // Modifier une catégorie
        if ($type === 'node') {
            $stmt = $pdo->prepare('UPDATE nodescategories SET name = :newName, class = :newClass WHERE id = :id');
        } else if ($type === 'edge') {
            $stmt = $pdo->prepare('UPDATE relationscategories SET name = :newName, class = :newClass WHERE id = :id');
        }
        $stmt->execute([
            ':id' => $category['id'],
            ':newName' => $category['newName'],
            ':newClass' => $category['class']
        ]);


    }
    elseif ($action === 'deleteCategory' && $type && $category) {
        // Supprimer une catégorie
        if ($type === 'node') {
            $stmt = $pdo->prepare('DELETE FROM nodescategories WHERE id = :id');
            $stmt->execute([
                ':id' => $category['id']
            ]);
            try {
                // Préparer la requête pour mettre à jour les catégories inexistantes
                $updateStmt = $pdo->prepare('UPDATE projectcontent SET category = 1 WHERE (category = :deletedId)');
                $updateStmt->execute([
                    ':deletedId' => $category['id']
                ]);
            } catch (Exception $e) {
                echo 'Erreur lors de la mise à jour des catégories : ' . $e->getMessage();
            }
        }

        else if ($type === 'edge')
        {
            $stmt = $pdo->prepare('DELETE FROM relationscategories WHERE id = :id');
            $stmt->execute([
                ':id' => $category['id']
            ]);
            try
            {
                // Préparer la requête pour mettre à jour les catégories inexistantes
                $updateStmt = $pdo->prepare('UPDATE projectrelation SET category = 1 WHERE (category = :deletedId)');
                $updateStmt->execute([
                   ':deletedId' => $category['id']
                ]);
            }
            catch (Exception $e)
            {
                echo 'Erreur lors de la mise à jour des catégories : ' . $e->getMessage();
            }
    }
    }

    elseif ($action === "addClass" && $type && isset($request['name'])) {
        if ($type === 'node') {
            //ajouter une classe dans nodecategoriesclasses
            $stmt = $pdo -> prepare('INSERT INTO nodecategoriesclasses (name) VALUES (:name)');
            $stmt->execute([
                ':name' => $request['name']
            ]);
            $id = $pdo->lastInsertId();
        }

        elseif ($type === 'edge') {
            $stmt = $pdo -> prepare('INSERT INTO relationcategoriesclasses (name) VALUES (:name)');
            $stmt->execute([
                ':name' => $request['name']
            ]);
            $id = $pdo->lastInsertId();
        }
    }
    //La condition && $request['id'] !== 1 correspond à "sauf AUTRES".
    elseif ($action === 'editClass' && $type && isset($request['name']) && isset($request['id']) && $request['id'] !== '1') {
        if ($type === 'node')
        {
            $stmt = $pdo -> prepare('UPDATE nodecategoriesclasses SET name = :name WHERE id = :id');
            $stmt->execute([
                ':name' => $request['name'],
                ':id' => $request['id']
            ]);
        }
        elseif ($type === 'edge')
        {
            $stmt = $pdo -> prepare('UPDATE relationcategoriesclasses SET name = :name WHERE id = :id');
            $stmt->execute([
                ':name' => $request['name'],
                ':id' => $request['id']
            ]);
        };
        echo $request['id'] . ' edited';

    }

    //La condition && $category['id'] !== 1 correspond à "sauf AUTRES".
    elseif ($action === 'deleteClass' && $type && $category && $category['id'] !== '1') {
        // Supprimer une catégorie
        if ($type === 'node') {
            $stmt = $pdo->prepare('DELETE FROM nodecategoriesclasses WHERE id = :id');
            $stmt->execute([
                ':id' => $category['id']
            ]);

            $stmt = $pdo -> prepare('UPDATE nodescategories SET class = :value WHERE (class = :deletedId)');
            $stmt->execute([
                ':value' => 1,
                ':deletedId' => $category['id']
            ]);
        } else if ($type === 'edge') {
            $stmt = $pdo->prepare('DELETE FROM relationcategoriesclasses WHERE id = :id');
            $stmt->execute([
                ':id' => $category['id']
            ]);

            $stmt = $pdo -> prepare('UPDATE relationscategories SET class = 1 WHERE (class = :deletedId)');
            $stmt->execute([
                ':deletedId' => $category['id']
            ]);
        }

        echo $category['id'] . ' deleted qdf XCHLMKJML';
    }

    if ($action === 'loadCategories') {
        // Récupérer les catégories des deux tables (nodescategories et relationscategories)

        $stmtNodes = $pdo->prepare('SELECT id, name FROM nodescategories WHERE owner = :own');
        $stmtNodes->execute([':own' => $_SESSION['id_actif']]);
        $nodesCategories = $stmtNodes->fetchAll(PDO::FETCH_ASSOC);

        $stmtRelations = $pdo->prepare('SELECT id, name FROM relationscategories WHERE owner = :own');
        $stmtRelations->execute([':own' => $_SESSION['id_actif']]);
        $relationsCategories = $stmtRelations->fetchAll(PDO::FETCH_ASSOC);

        // Retourner les résultats sous forme de JSON
        echo json_encode([
            'nodesCategories' => $nodesCategories,
            'relationsCategories' => $relationsCategories,
        ]);
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