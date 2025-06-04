<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=127.0.0.1;dbname=osix', 'usersauv', 'S@uvBaseosix25*');


if (isset($_POST['action']) and $_POST['action'] == 'load_table') {
    // Vérifiez si le nom de la table est reçu
    if (!isset($_POST['table_name'])) {
        echo "<p>Erreur : aucune table spécifiée.</p>";
        exit();
    }

    $table = $_POST['table_name'];

    // Liste des tables autorisées
    $validTables = ['tools', 'toolscategories', 'toolstags', 'favoritetools'];
    if (!in_array($table, $validTables)) {
        echo "<p>Erreur : table non valide.</p>";
        exit();
    }

    // Récupérer les données de la table demandée
    $query = $pdo->query("SELECT * FROM {$table}");
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    // Générer un tableau HTML
    if ($rows): ?>
        <form class="table-form" method="post" action="update_table.php" data-table="<?= $table ?>">
            <table>
                <thead>
                <tr>
                    <?php foreach (array_keys($rows[0]) as $column): ?>
                        <th><?= htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                    <th>Supprimer</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($row as $key => $value): ?>
                            <td>
                                <input type="text" name="<?= $key; ?>[]" value="<?= htmlspecialchars($value); ?>">
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <input type="checkbox" name="delete[]" value="<?= $row['id']; ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Ligne pour ajouter une nouvelle entrée -->
                <tr>
                    <?php foreach (array_keys($rows[0]) as $key): ?>
                        <td>
                            <input type="text" name="new_<?= $key; ?>[]" placeholder="Nouveau <?= $key; ?>">
                        </td>
                    <?php endforeach; ?>
                    <td></td>
                </tr>
                </tbody>
            </table>
            <div>
                <button type="submit" name="submit_changes">Soumettre</button>
                <button type="button" onclick="loadTable(<?= $table?>)">Rafraîchir</button>
            </div>
        </form>
    <?php else: ?>
        <p>Cette table est vide. Ajoutez des données pour commencer.</p>
    <?php endif;

}


if (isset($_POST['action']) and $_POST['action'] == 'update_table') {
    // Connexion à la base de données
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=osix', 'usersauv', 'S@uvBaseosix25*');

    // Vérifier la table sélectionnée
    $table = isset($_GET['table']) ? $_GET['table'] : null;
    $validTables = ['tools', 'toolscategories', 'toolstags', 'favoritetools'];
    if (!in_array($table, $validTables)) {
        echo "Table non valide.";
        exit();
    }

    // Suppression des lignes cochées
    if (isset($_POST['delete'])) {
        $deleteIds = $_POST['delete'];

        // Préparation de la requête de suppression
        $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
        $query = $pdo->prepare("DELETE FROM {$table} WHERE id IN ($placeholders)");
        $query->execute($deleteIds);
    }

    // Mise à jour des lignes existantes
    if (isset($_POST['id'])) {
        foreach ($_POST['id'] as $index => $id) {
            $columns = array_keys($_POST);
            $set = "";
            $params = [];

            foreach ($columns as $column) {
                if ($column === 'id' || $column === 'submit_changes' || $column === 'delete' || strpos($column, 'new_') === 0) {
                    continue;
                }
                $set .= "{$column} = ?, ";
                $params[] = $_POST[$column][$index];
            }

            $set = rtrim($set, ", ");
            $params[] = $id;

            $query = $pdo->prepare("UPDATE {$table} SET {$set} WHERE id = ?");
            $query->execute($params);
        }
    }

    // Ajout des nouvelles lignes
    $newColumns = [];
    foreach ($_POST as $key => $values) {
        if (strpos($key, 'new_') === 0) {
            $newColumns[] = substr($key, 4);
        }
    }

    if (!empty($newColumns) && isset($_POST['new_' . $newColumns[0]])) {
        foreach ($_POST['new_' . $newColumns[0]] as $index => $value) {
            if (empty($value)) {
                continue; // Ignorer les lignes vides
            }

            $columns = implode(',', $newColumns);
            $placeholders = implode(',', array_fill(0, count($newColumns), '?'));
            $params = [];

            foreach ($newColumns as $column) {
                $params[] = $_POST['new_' . $column][$index];
            }

            $query = $pdo->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
            $query->execute($params);
        }
    }

    // Redirection vers la page d'administration
    header("Location: administration.php");
    exit();
}
?>