<?php
session_start();

try {
    // On se connecte à MySQL
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Activer les exceptions PDO
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

} catch (Exception $e) {// En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());    } ;


// Insérer dans la table
try {
    $query = 'SELECT image FROM projects WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $_POST['project_id']]);
    $image = $stmt->fetchColumn();

    $query = 'SELECT comments FROM projects WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $_POST['project_id']]);
    $comments = $stmt->fetchColumn();

    $query = 'SELECT nodecategoriesstatus FROM projects WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $_POST['project_id']]);
    $nodestatus = $stmt->fetchColumn();

    $query = 'SELECT edgecategoriesstatus FROM projects WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $_POST['project_id']]);
    $edgestatus = $stmt->fetchColumn();

    // Préparer la requête d'insertion
    $query = "INSERT INTO projects (name, date, owner, image, comments, sharelist, nodecategoriesstatus, edgecategoriesstatus) VALUES (:name, :date, :owner, :image, :com, :sharelist, :nodestatus, :edgestatus)";
    $stmt = $bdd->prepare($query);
    $stmt->execute([':name' => $_POST['projectNewName'], ':date' => date('Y-m-d H:i:s'),
        ':owner' => (isset($_SESSION['id_actif']) && $_SESSION['id_actif'] !== '') ? $_SESSION['id_actif'] : -2, ':image' => $image, ':com' => $comments,
        ':sharelist' => '[]', ':nodestatus' => $nodestatus, ':edgestatus'=> $edgestatus]);

    // Récupération de l'ID inséré via LAST_INSERT_ID()
    $newProjectId = $bdd->lastInsertId();

    // Affichage ou usage de l'ID
    $_SESSION['project_id'] = $newProjectId;


} catch (Exception $e) {
    echo "Erreur lors de l'insertion : " . $e->getMessage();
}


//copie des sommets...
$query = 'SELECT * FROM projectcontent WHERE project_id = :id'; //' AND version = :v';
$stmt = $bdd->prepare($query);
$stmt->execute(['id' => $_POST["project_id"]]); //, 'v' => $_SESSION['version']]);
$this_project_entities = $stmt->fetchAll();
//$conversionArray permet de stocker le passage d'un noeud à un autre en mémorisant le couple d'ID.
//(Utile pour garder les mêmes relations.)
$conversionArray = [];

$query = 'INSERT INTO projectcontent (project_id, category, name, icon, comments, source, position, image)
            VALUES (:id, :cat, :name, :icon, :comments, :source, :position, :img)';
$stmt = $bdd->prepare($query);

foreach ($this_project_entities as $row) {
    // Insérer les données dans la table projectcontent
    $stmt->execute([
        ':id' => $newProjectId,
        /*':ver' => $row['version'] + 1, */
        ':cat' => $row['category'],
        ':name' => $row['name'],
        ':icon' => $row['icon'],
        ':comments' => $row['comments'],
        ':source' => $row['source'],
        ':position' => $row['position'],
        ':img' => $row['image'],
    ]);

    // Récupérer l'ID auto-généré par l'insertion
    $lastId = $bdd->lastInsertId();

    $conversionArray[$row['id']] = $lastId; // Associe l'ancien ID au nouveau dans $conversionArray
}

if (empty($conversionArray)) {
    echo "Erreur : conversionArray est vide après l'insertion dans projectcontent.";
    exit; // Arrêter l'exécution si la mise en correspondance échoue
}

/* Étape 1 : Récupérer les relations du projet existant */
$query = 'SELECT * FROM projectrelation WHERE project_id = :id';
$stmt = $bdd->prepare($query);

// Exécute la requête avec l'ID du projet en session
$stmt->execute(['id' => $_POST["project_id"]]);
$this_project_relations = $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupère chaque ligne en tableau associatif

// Vérification : Si aucune relation n'est trouvée
if (empty($this_project_relations)) {
    echo "Aucune relation trouvée pour le projet ID : " . $_SESSION["project_id"];
    exit;
}

// Débogage : Affiche le type et les données trouvées
echo "Type de \$this_project_relations : " . gettype($this_project_relations) . PHP_EOL;
var_dump($this_project_relations);

/* Étape 2 : Vérifie que le tableau de conversion est valide */
if (empty($conversionArray)) {
    echo "Erreur : ConversionArray est vide ou n'a pas été initialisé correctement.";
    var_dump($conversionArray);
    exit;
}
/* Étape 3 : Préparer l'insertion des relations copiées */
$query = 'INSERT INTO projectrelation (project_id, category, style, comments, source, sourceNode, targetNode)
          VALUES (:id, :cat, :sty, :com, :source, :sourceNode, :targetNode)';
$stmt = $bdd->prepare($query);

/* Étape 4 : Boucle à travers chaque relation extraite */
foreach ($this_project_relations as $row) {

    // Vérification : Les clés "sourceNode" et "targetNode" existent dans le tableau de conversion
    if (!isset($conversionArray[$row['sourceNode']]) || ! isset($conversionArray[$row['targetNode']])) {
        echo "Erreur : sourceNode ou targetNode introuvable dans conversionArray pour la relation : ";
        var_dump($row);
        continue; // Ignorer cette relation et passer à la suivante
    }

    try {
        // Débogage : Affiche le contenu de la relation avant insertion
        echo "Type de \$row : " . gettype($row) . PHP_EOL;
        var_dump($row);

        // Insère la relation copiée avec les nouvelles valeurs
        $stmt->execute([
            ':id' => $newProjectId, // ID du nouveau projet
            ':cat' => $row['category'], // Catégorie copiée
            ':sty' => $row['style'],    // Style copié
            ':com' => $row['comments'], // Commentaires copiés
            ':source' => $row['source'], // Source originale de la relation
            ':sourceNode' => $conversionArray[$row['sourceNode']], // ID converti de sourceNode
            ':targetNode' => $conversionArray[$row['targetNode']]  // ID converti de targetNode
        ]);

    } catch (PDOException $e) {
        // Gestion des erreurs PDO
        echo "Erreur PDO lors de l'insertion : " . $e->getMessage() . PHP_EOL;
        var_dump($row); // Données problématiques affichées pour le débogage
        continue; // Passe à la relation suivante
    }
}


?>
