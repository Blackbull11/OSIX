<?php
session_start();

try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
}
catch (PDOException $e) {
    exit('Erreur : ' . $e->getMessage());
}

if (isset($_POST['action']) && $_POST['action'] === 'loadTagTable') {
    // Fetch all tags
    $stmt = $bdd->prepare('SELECT * FROM sourcestags ORDER BY type');
    $stmt->execute();
    $tags = $stmt->fetchAll();

// Fetch all groups
    $stmt = $bdd->prepare('SELECT id, name FROM usergroups');
    $stmt->execute();
    $groups = $stmt->fetchAll();

// Create an associative array for quick lookup of group names by ID
    $groupNames = [];
    foreach ($groups as $group) {
        $groupNames[$group['id']] = $group['name'];
    }

// Display the tags and their associated groups
    foreach ($tags as $tag) {
        // Create an array of group names for the current tag
        $groupNameList = [];
        $array = json_decode($tag['taggroups'], true);
        foreach ($array as $tagGroup) {
            $groupNameList[] = $groupNames[$tagGroup];
        }
        $tagtype = !is_null($tag['type']) ? $tag['type'] : '';
        ?>

        <tr>
        <td style="border: 1px solid #dddddd; padding: 0;">
            <input
                type="text"
                value="<?= htmlspecialchars($tag['name']) ?>"
                class="modify-question"
                id="tagNameInput_<?=$tag['id']?>"
            />
        </td>
        <td style="border: 1px solid #dddddd; padding: 0;">
            <input
                type="text"
                value="<?= htmlspecialchars($tagtype) ?>"
                class="modify-question"
                id="tagTypeInput_<?=$tag['id']?>"
            />
        </td>
        <td style="text-align: center">
        <?php echo '<div style="display: flex; justify-content: space-between; align-items: center;">';

        foreach ($groupNames as $id => $name) {
            $boxId = htmlspecialchars('tagCheckbox_'.$tag['id'].'_'.$id);
            if (in_array($name, $groupNameList)) {
                echo '<input type="checkbox" name="' . $id .' " id="'. $boxId .'" checked><label for="' . $id . '">' . $name . '</label>';
            }
            else
            {
                echo '<input type="checkbox" name="' . $id .'" id="'. $boxId .'" ><label for="' . $id . '">' . $name . '</label>';
            }
        }
        echo '</div>';

        echo '</td>
            </tr>';
    }
}

elseif (isset($_POST['action']) && $_POST['action'] === 'addTag' && isset($_POST['tagName'])) {
    $query = 'INSERT INTO sourcestags (name, type, taggroups) VALUES (:name, :type, :groups)';
    $stmt = $bdd->prepare($query);
    $stmt->execute([
        ':name' => $_POST['tagName'],
        ':type' => $_POST['tagType'],
        ':groups' => '[]'
    ]);
    echo 'Tag ajouté avec succès';
}
elseif (isset($_POST['action']) && $_POST['action'] === 'deleteTag' && isset($_POST['tagId'])) {
    $tagId = (int)$_POST['tagId'];

    // Supprimer le tag de la table sourcestags
    $query = "DELETE FROM sourcestags WHERE id = :id";
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $tagId]);

    // Récupérer toutes les entrées de la table sources
    $query = "SELECT id, tags FROM sources";
    $stmt = $bdd->prepare($query);
    $stmt->execute();
    $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Parcourir les résultats et retirer le tag supprimé de la colonne `tags`
    foreach ($sources as $source) {
        $tags = json_decode($source['tags'], true);
        if (($key = array_search($tagId, $tags)) !== false) {
            unset($tags[$key]); // Retirer le tag
            // Remettre à jour la base de données
            $queryUpdate = "UPDATE sources SET tags = :tags WHERE id = :id";
            $stmtUpdate = $bdd->prepare($queryUpdate);
            $stmtUpdate->execute([
                ':tags' => json_encode(array_values($tags)), // Réindexation
                ':id' => $source['id'],
            ]);
        }
    }

    echo "Le tag a été supprimé avec succès.";
}

// Charger les tags pour le formulaire de suppression
elseif (isset($_POST['action']) && $_POST['action'] === 'loadTagsSelect') {
    $stmt = $bdd->prepare("SELECT id, name FROM sourcestags ORDER BY name");
    $stmt->execute();
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tags as $tag) {
        echo '<option value="' . htmlspecialchars($tag['id']) . '">' . htmlspecialchars($tag['name']) . '</option>';
    }
}

elseif (isset($_POST['action'])
    && ($_POST['action'] === 'adminGroups')
    && isset($_POST['tagGroupsJson'])
    && isset($_POST['tagTypeJson'])
    && isset($_POST['tagNamesJson'])
)
{
    $row = json_decode($_POST['tagGroupsJson'], true);
    $query = "UPDATE sourcestags SET taggroups = :groups WHERE id = :id";
    $stmt = $bdd->prepare($query);
    foreach ($row as $tag => $tagGroup) {
        $stmt->execute([
            ':id' => $tag,
            ':groups' => json_encode($tagGroup)
        ]);
    }

    $names = json_decode($_POST['tagNamesJson'], true);
    $query = "UPDATE sourcestags SET name = :name WHERE id = :id";
    $stmt = $bdd->prepare($query);
    foreach ($names as $id => $name) {
        $stmt->execute([
            ':id' => $id,
            ':name' => $name
        ]);
    }

    $types = json_decode($_POST['tagTypeJson'], true);
    $query = "UPDATE sourcestags SET type = :tp WHERE id = :id";
    $stmt = $bdd->prepare($query);
    foreach ($types as $id => $tp) {
        $stmt->execute([
            ':id' => $id,
            ':tp' => $tp
        ]);
    }
    echo 'ok';
}

elseif (isset($_POST['action']) && $_POST['action'] === 'loadToolTagTable') {
    // Fetch all tags
    $stmt = $bdd->prepare('SELECT * FROM toolstags ORDER BY type');
    $stmt->execute();
    $tags = $stmt->fetchAll();

// Fetch all groups
    $stmt = $bdd->prepare('SELECT id, name FROM usergroups');
    $stmt->execute();
    $groups = $stmt->fetchAll();

// Create an associative array for quick lookup of group names by ID
    $groupNames = [];
    foreach ($groups as $group) {
        $groupNames[$group['id']] = $group['name'];
    }

// Display the tags and their associated groups
    foreach ($tags as $tag) {
        // Create an array of group names for the current tag
        $groupNameList = [];
        $array = json_decode($tag['taggroups'], true);
        foreach ($array as $tagGroup) {
            $groupNameList[] = $groupNames[$tagGroup];
        }
        $tagtype = !is_null($tag['type']) ? $tag['type'] : '';
        ?>

        <tr>
        <td style="border: 1px solid #dddddd; padding: 0;">
            <input
                type="text"
                value="<?= htmlspecialchars($tag['name']) ?>"
                class="modify-question"
                id="toolTagNameInput_<?=$tag['id']?>"
            />
        </td>
        <td style="border: 1px solid #dddddd; padding: 0;">
            <input
                type="text"
                value="<?= htmlspecialchars($tagtype) ?>"
                class="modify-question"
                id="toolTagTypeInput_<?=$tag['id']?>"
            />
        </td>
    </tr>
<?php
    }
}

elseif (isset($_POST['action']) && $_POST['action'] === 'addToolTag' && isset($_POST['tagName'])) {
    $query = 'INSERT INTO toolstags (name, type, taggroups) VALUES (:name, :type, :groups)';
    $stmt = $bdd->prepare($query);
    $stmt->execute([
        ':name' => $_POST['tagName'],
        ':type' => $_POST['tagType'],
        ':groups' => '[]'
    ]);
    echo 'Tag ajouté avec succès';
}

elseif (isset($_POST['action']) && $_POST['action'] === 'deleteToolTag' && isset($_POST['tagId'])) {
    $tagId = (int)$_POST['tagId'];

    // Supprimer le tag de la table sourcestags
    $query = "DELETE FROM toolstags WHERE id = :id";
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $tagId]);

    // Récupérer toutes les entrées de la table sources
    $query = "SELECT id, tags FROM tools";
    $stmt = $bdd->prepare($query);
    $stmt->execute();
    $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Parcourir les résultats et retirer le tag supprimé de la colonne `tags`
    foreach ($sources as $source) {
        $tags = json_decode($source['tags'], true);
        if (($key = array_search($tagId, $tags)) !== false) {
            unset($tags[$key]); // Retirer le tag
            // Remettre à jour la base de données
            $queryUpdate = "UPDATE tools SET tags = :tags WHERE id = :id";
            $stmtUpdate = $bdd->prepare($queryUpdate);
            $stmtUpdate->execute([
                ':tags' => json_encode(array_values($tags)), // Réindexation
                ':id' => $source['id'],
            ]);
        }
    }

    echo "Le tag a été supprimé avec succès.";
}

// Charger les tags pour le formulaire de suppression
elseif (isset($_POST['action']) && $_POST['action'] === 'loadToolTagsSelect') {
    $stmt = $bdd->prepare("SELECT id, name FROM toolstags ORDER BY name");
    $stmt->execute();
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tags as $tag) {
        echo '<option value="' . htmlspecialchars($tag['id']) . '">' . htmlspecialchars($tag['name']) . '</option>';
    }
}

elseif (isset($_POST['action'])
    && $_POST['action'] === 'adminToolGroups'
    && isset($_POST['toolTagNames'])
    && isset($_POST['toolTagTypes'])
)
{
    $names = json_decode($_POST['toolTagNames'], true);
    $query = "UPDATE toolstags SET name = :name WHERE id = :id";
    $stmt = $bdd->prepare($query);
    foreach ($names as $id => $name) {
        $stmt->execute([
            ':id' => $id,
            ':name' => $name
        ]);
    }

    $types = json_decode($_POST['toolTagTypes'], true);
    $query = "UPDATE toolstags SET type = :tp WHERE id = :id";
    $stmt = $bdd->prepare($query);
    foreach ($types as $id => $tp) {
        $stmt->execute([
            ':id' => $id,
            ':tp' => $tp
        ]);
    }
    echo "ok";
}


elseif (isset($_POST['action']) && $_POST['action'] === 'loadTypeTable') {
// Fetch all tags
$stmt = $bdd->prepare('SELECT * FROM sourcetypes ORDER BY parent');
$stmt->execute();
$types = $stmt->fetchAll();

// Display the tags and their associated groups
foreach ($types as $type) {

    $typeValue = !is_null($type['endpoint']) ? $type['endpoint'] : '';
?>

<tr>
    <td style="border: 1px solid #dddddd; padding: 0;">
        <input
                type="text"
                value="<?= htmlspecialchars($type['parent']) ?>"
                class="modify-question"
                id="typeParentInput_<?=$type['id']?>"
        />
    </td>

    <td style="border: 1px solid #dddddd; padding: 0;">
        <input
                type="text"
                value="<?= htmlspecialchars($type['name']) ?>"
                class="modify-question"
                id="typeNameInput_<?=$type['id']?>"
        />
    </td>

    <td style="border: 1px solid #dddddd; padding: 0;">
        <select id="typeSelect_<?=$type['id']?>" style=" width: 100%; height: 40px;">
            <option value="0" <?=(($typeValue === 0) ? 'selected' :  '');?>>Sources et liens</option>
            <option value="1" <?=(($typeValue === 1) ? 'selected' : '');?>>Liens uniquements</option>
            <option value="2" <?=(($typeValue === 2) ? 'selected' : '');?>>Ressource générique</option>
        </select>
    </td>

</tr>
<?php
        }
        }


// Charger les tags pour le formulaire de suppression
elseif (isset($_POST['action']) && $_POST['action'] === 'loadTypeSelect') {
    $stmt = $bdd->prepare("SELECT id, name, parent FROM sourcetypes ORDER BY name");
    $stmt->execute();
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tags as $tag) {
        echo '<option value="' . htmlspecialchars($tag['id']). '">' . htmlspecialchars($tag['name']) . ' dans '. htmlspecialchars($tag['parent']) . '</option>';
    }
}

elseif  (isset($_POST['action']) && $_POST['action'] === 'deleteType'
      && isset($_POST['typeId'])
      && isset($_POST['newTypeId'])
    ) {
    $typeId = (int)$_POST['typeId'];
    $newTypeId = (int)$_POST['newTypeId'];

    // Supprimer le tag de la table sourcestags
    $query = "DELETE FROM sourcetypes WHERE id = :id";
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $typeId]);

    //Affecter les outils du type supprimé au nouveau type.
    $query = "UPDATE sources SET type = :newType WHERE type = :exType";
    $stmt = $bdd->prepare($query);
    $stmt->execute([
      ':exType' => $typeId,
      ':newType' => $newTypeId
    ]);

    echo "Le type a été supprimé avec succès.";
}

elseif (isset($_POST['action'])
    && ($_POST['action'] === 'adminTypes')
    && isset($_POST['typeEndpoints'])
    && isset($_POST['typeNames'])
    && isset($_POST['typeParents'])
)
{
      $names = json_decode($_POST['typeNames'], true);
      $endpoints = json_decode($_POST['typeEndpoints'], true);
      $parents = json_decode($_POST['typeParents'], true);
      $query = "UPDATE sourcetypes SET name = :name, endpoint = :endpoint, parent = :parent WHERE id = :id";
      $stmt = $bdd->prepare($query);
      foreach ($names as $id => $name) {
        if (isset($endpoints[$id]) && isset($parents[$id]))
          $stmt->execute([
              ':id' => $id,
              ':name' => $name,
              ':parent' => $parents[$id],
              ':endpoint' => $endpoints[$id]
          ]);
      }
      echo "ok";
}


elseif (isset($_POST['action']) && $_POST['action'] === 'addType'
        && isset($_POST['typeParent'])
        && isset($_POST['typeSelected'])
        && isset($_POST['typeName'])) {
    $query = 'INSERT INTO sourcetypes (name, parent, endpoint) VALUES (:name, :parent, :endpoint)';
    $stmt = $bdd->prepare($query);
    $stmt->execute([
        ':name' => $_POST['typeName'],
        ':parent' => $_POST['typeParent'],
        ':endpoint' => $_POST['typeSelected']
    ]);
  }

?>
