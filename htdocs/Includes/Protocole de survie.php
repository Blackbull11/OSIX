<!-- EN CAS DE BUG, VERIFIER :

Vérifier Session-start ;
Les points-virgule ;
Les chemins d'accès dont la casse ;
Le bon parenthésage des fonctions js en particulier.


Notes pour les images :
il faut enregistrer avec c://www ....
il faut aller la chercher avec /EPIX_Images...
Il ne faut pas avoir d'espace ni de ' é à ... dans le titre



TO DO :

STYLES
    pouvoir copier un style d'arête
    catégorisation automatique des styles (défaut)
    Bouton "style d'arête par défaut" pour revenir en arrière ?
    sélection edge => choix du style edge pour modifier APRES choix catégorie (car orienté ou non)
CATEGORISATION STYLE ET DESIGN




STOCKAGE DES IMAGES

        OK supprimer sommet et edge
        OK sélection automatique de noeuds pour edges,
        OK orientation edges,
        OK coordonnées mis à jour
        OK commentaire à afficher en survol
        OK supprimer image/icône
        OK bouton + qui se balade avec redéfinir sommet à enlever
        OK logo projet
        OK faire les images...
        OK lien à suivre en cliquant bas gauche par ex.
        OK image à afficher en cliquant bas droit. ou double click sur image dans onglet.
        OK Désactiver les loupes et globes.
        OK nouveau projet initialiser
        OK SELECT commentaires affichage
        OK Liste à cocher/décocher pour "mes catégories".
        OK Mode Masque
        OK possibilité de rotation des items du canvas (dans parameters.)
        OK Gérer si relation de A à B unique obligatoirement ?
        OK taille du canvas à fixer (sinon déroutant pour rotation et autre). Dans l'idée, pas plus que ce qui est visible.
        OK activer 3d en vitesse lente ou très lente... -> permet d'interagir en direct ?
        OK ajouter un dépliant/pliant de commentaire
        OK Edge choix orientation / ou pas. (conséquence des catégories...) (à la création)
        OK +++ arrangement automatique
        OK choix du type pour edge.
        OK TRACER COURBES
        OK edge select doit s'adapter à la courbure... /!\ ( La solution retenue est une approximation des courbes par des droites tangentes en leur milieu).
        OK affichage mouseleave attention aux bugs de rechargement du canvas
        OK enlever commentaire si hidden
        OK partage à fignoler : afficher l'option de partage / départage recquiert d'aller chercher dans la bdd, pour chaque projet...
        OK bug bouton ajouter décliqué.
        OK mode admin pouvoir ajouter des classes de catégories // des catégories publiques.
        OK désactiver mutuellement faire "tourner" et "masque"
        OK bug à corriger : initialisation nulle de node et edgecategoriesstatus, il faut plutôt {}.
        OK Il faut si on restaure des sommets, adapter les relations correspondantes.
        OK Rotation 3D bloquée si lancée.
        OK ovale et losange symbologique
        OK Supprimer un projet
        OK Boutons fonctionnels à partir de osix



EXPORT DE DONNEES finir
grossir ovale et texte // ajuster globes et loupes
Input coordonnées
Sélectionner une classe




Bonus :
    ouverture sur une fenêtre cadrée sur le graphe. Ou bouton pour recentrer le canvas.
NO    mode "inspiration" avec des vx ;
NO    arêtes animées avec flèches et pointillés qui défilent.
    afficher en "surbrillance" les sommets sélectionnés pour une relation.
    listes déroulantes de catégories et de nodes selon la dernière utilisation (en haut) et le dernier ajout (en bas...)
    possibilité de changer le rond rouge par défaut.
    possibilité de mettre plusieurs images


Pour Andrea :
        Mode plein écran.
        Gérer redimensionnement et débordements.
        Mise à jour de la date de dernière modification des projets.
        Style du bouton partager dans EPIX gestionnaire
        pouvoir afficher en dessous du canvas les infos les + significatives (quelques images).
    input coordonnées. // des cases pour lieu, date, ...
    stockage des images

    boutons fonctionnels projets epix à partir de osix
    propotionnalité taille des cadres avec rotation 3D.



Eventuellement :
    aperçu de l'icône dans le "nouveau projet". (pas compliqué, voir ce qui se fait déjà dans l'insertion d'images)
    pouvoir mettre plusieurs images dans un seul noeud. Eventuellement aussi dans les relations. Cela demande de revoir la structure du stockage, et du traitement ajax.


updateBestSource ?
updateRelatives quand envoie formulaire pour nouveau lien à cette source
bestMatch renvoie la sélection

updateopenings quand click lien

 epix script révision...

table


adapter positionnement globe et loupe aux ovales et losanges.

catégories admin pouvoir sélectionner automatiquement la classe déjà présente si on modifie.

partage supprimer accès si supprimé par propriétaire

annuler sauvegarde projet...

ajouter des sources

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Losange avec texte</title>
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      background-color: #f0f0f0;
    }
    canvas {
      border: 1px solid black;
    }
  </style>
</head>
<body>
  <canvas id="monCanvas" width="500" height="500"></canvas>
  <script>
    const canvas = document.getElementById('monCanvas');
    const ctx = canvas.getContext('2d');

    // Définir les coordonnées du losange
    const x = canvas.width / 2;
    const y = canvas.height / 2;
    const taille = 100; // Taille du losange

    // Texte à insérer dans le losange
    const texte = "Bienvenue dans le losange!";
    const maxWidth = 2 * taille * 0.8; // Largeur maximale du texte (80% de la largeur du losange)

    // Fonction pour couper le texte en plusieurs lignes
    function wrapText(text, maxWidth) {
      const words = text.split(' ');
      let lines = [];
      let currentLine = '';

      for (let i = 0; i < words.length; i++) {
        let testLine = currentLine + (currentLine ? ' ' : '') + words[i];
        let testWidth = ctx.measureText(testLine).width;

        if (testWidth > maxWidth) {
          lines.push(currentLine);
          currentLine = words[i];
        } else {
          currentLine = testLine;
        }
      }

      lines.push(currentLine); // Ajouter la dernière ligne
      return lines;
    }

    // Découper le texte
    const lines = wrapText(texte, maxWidth);

    // Dessiner le losange
    ctx.beginPath();
    ctx.moveTo(x, y - taille); // Point du haut
    ctx.lineTo(x + taille, y); // Point de droite
    ctx.lineTo(x, y + taille); // Point du bas
    ctx.lineTo(x - taille, y); // Point de gauche
    ctx.closePath();
    ctx.fillStyle = 'red';
    ctx.fill();

    // Dessiner le texte
    ctx.fillStyle = 'white'; // Couleur du texte
    ctx.font = '20px Arial'; // Taille de la police
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    const lineHeight = 25; // Hauteur de ligne
    const totalTextHeight = lines.length * lineHeight;

    // Positionner le texte de manière centrée dans le losange
    let textY = y - totalTextHeight / 2;

    // Dessiner chaque ligne de texte
    lines.forEach(line => {
      ctx.fillText(line, x, textY);
      textY += lineHeight;
    });

  </script>
</body>
</html>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ovale avec texte</title>
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      background-color: #f0f0f0;
    }
    canvas {
      border: 1px solid black;
    }
  </style>
</head>
<body>
  <canvas id="monCanvas" width="500" height="500"></canvas>
  <script>
    const canvas = document.getElementById('monCanvas');
    const ctx = canvas.getContext('2d');

    // Texte à insérer dans l'ovale
    const texte = "Bienvenue dans l'ovale ergonomique!";

    // Fonction pour couper le texte en plusieurs lignes et déterminer la taille de l'ovale
    function wrapText(text, maxWidth) {
      const words = text.split(' ');
      let lines = [];
      let currentLine = '';
      let maxTextWidth = 0;

      for (let i = 0; i < words.length; i++) {
        let testLine = currentLine + (currentLine ? ' ' : '') + words[i];
        let testWidth = ctx.measureText(testLine).width;

        if (testWidth > maxWidth) {
          lines.push(currentLine);
          currentLine = words[i];
        } else {
          currentLine = testLine;
        }

        // Mémoriser la largeur maximale du texte
        maxTextWidth = Math.max(maxTextWidth, testWidth);
      }

      lines.push(currentLine); // Ajouter la dernière ligne

      // Retourner les lignes et la largeur du texte
      return { lines, maxTextWidth };
    }

    // Paramètres
    const maxWidth = canvas.width * 0.8; // Largeur maximale pour le texte (80% du canevas)

    // Découper le texte
    const { lines, maxTextWidth } = wrapText(texte, maxWidth);

    // Calculer la taille optimale de l'ovale
    const maxHeight = lines.length * 25; // Hauteur totale du texte (hauteur de ligne * nombre de lignes)
    const ovaleWidth = Math.max(maxTextWidth * 1.2, canvas.width * 0.6); // Largeur de l'ovale (120% de la largeur du texte ou 60% du canevas)
    const ovaleHeight = Math.max(maxHeight * 1.2, canvas.height * 0.3); // Hauteur de l'ovale (120% de la hauteur du texte ou 30% du canevas)

    // Définir les coordonnées du centre de l'ovale
    const x = canvas.width / 2;
    const y = canvas.height / 2;

    // Ajouter une ombre pour un effet de profondeur
    ctx.shadowColor = "rgba(0, 0, 0, 0.4)";
    ctx.shadowOffsetX = 5;
    ctx.shadowOffsetY = 5;
    ctx.shadowBlur = 10;

    // Dessiner l'ovale avec bords arrondis
    ctx.beginPath();
    ctx.ellipse(x, y, ovaleWidth / 2, ovaleHeight / 2, 0, 0, 2 * Math.PI); // Dessiner l'ovale
    ctx.closePath();

    // Appliquer un dégradé de couleur pour une meilleure esthétique
    const gradient = ctx.createLinearGradient(x - ovaleWidth / 2, y - ovaleHeight / 2, x + ovaleWidth / 2, y + ovaleHeight / 2);
    gradient.addColorStop(0, '#5cbbff');
    gradient.addColorStop(1, '#1e7bbf');
    ctx.fillStyle = gradient;
    ctx.fill();

    // Réinitialiser l'ombre pour le texte
    ctx.shadowColor = "transparent";

    // Dessiner le texte
    ctx.fillStyle = 'white'; // Couleur du texte (blanc pour contraster avec l'ovale)
    ctx.font = '20px Arial'; // Taille de la police
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    const lineHeight = 25; // Hauteur de ligne
    const totalTextHeight = lines.length * lineHeight;

    // Positionner le texte de manière centrée dans l'ovale
    let textY = y - totalTextHeight / 2;

    // Dessiner chaque ligne de texte
    lines.forEach(line => {
      ctx.fillText(line, x, textY);
      textY += lineHeight;
    });

  </script>
</body>
</html>


sandbox
Catégories

ACTIONS TERRORISTES
explosion E.E.
exaction
tir indirect
attaque position fixe
embuscade
attaque d'ampleur
véhicule suicide
Affrontement inter-groupe



mistral ai

<?php
// Supposons que vous avez une table de jonction `tag_groups` qui lie les tags aux groupes
// La structure de la table `tag_groups` pourrait être : (tag_id, group_id)

// Fetch all tags
$stmt = $bdd->prepare('SELECT * FROM sourcestags');
$stmt->execute();
$tags = $stmt->fetchAll();

// Fetch all groups
$stmt = $bdd->prepare('SELECT id, name FROM groups');
$stmt->execute();
$groups = $stmt->fetchAll();

// Create an associative array for quick lookup of group names by ID
$groupNames = [];
foreach ($groups as $group) {
    $groupNames[$group['id']] = $group['name'];
}

// Display the tags and their associated groups
foreach ($tags as $tag) {
    // Fetch the groups associated with the current tag
    $stmt = $bdd->prepare('SELECT group_id FROM tag_groups WHERE tag_id = :tag_id');
    $stmt->execute(['tag_id' => $tag['id']]);
    $tagGroups = $stmt->fetchAll();

    // Create an array of group names for the current tag
    $groupNameList = [];
    foreach ($tagGroups as $tagGroup) {
        $groupNameList[] = isset($groupNames[$tagGroup['group_id']]) ? $groupNames[$tagGroup['group_id']] : 'Unknown Group';
    }

    // Display the tag name and the list of group names
    echo '<tr>
                <td style="text-align: center">' . htmlspecialchars($tag['name']) . '</td>
                <td style="text-align: center">' . htmlspecialchars('[' . implode(', ', $groupNameList) . ']') . '</td>
            </tr>';
}
?>


                        <table id="tags-table"><thead><tr><th style="text-align : center">NOM</th><th style="text-align: center;">GROUPES</th></tr></thead><tbody id="tags-tbody"></tbody>
-->


<?php
// Supposons que vous avez une table de jonction `tag_groups` qui lie les tags aux groupes
// La structure de la table `tag_groups` pourrait être : (tag_id, group_id)

// Fetch all tags
$stmt = $bdd->prepare('SELECT * FROM sourcestags');
$stmt->execute();
$tags = $stmt->fetchAll();

// Fetch all groups
$stmt = $bdd->prepare('SELECT id, name FROM groups');
$stmt->execute();
$groups = $stmt->fetchAll();

// Create an associative array for quick lookup of group names by ID
$groupNames = [];
foreach ($groups as $group) {
    $groupNames[$group['id']] = $group['name'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau avec Défilement Vertical</title>
    <style>
        .table-container {
            max-height: 300px; /* Définissez la hauteur maximale souhaitée */
            overflow-y: auto; /* Activez le défilement vertical */
            border: 1px solid #ccc; /* Ajoutez une bordure pour une meilleure visibilité */
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="table-container">
    <table>
        <thead>
        <tr>
            <th>Nom du Tag</th>
            <th>Groupes Associés</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Display the tags and their associated groups
        foreach ($tags as $tag) {
            // Fetch the groups associated with the current tag
            $stmt = $bdd->prepare('SELECT group_id FROM tag_groups WHERE tag_id = :tag_id');
            $stmt->execute(['tag_id' => $tag['id']]);
            $tagGroups = $stmt->fetchAll();

            // Create an array of group names for the current tag
            $groupNameList = [];
            foreach ($tagGroups as $tagGroup) {
                $groupNameList[] = isset($groupNames[$tagGroup['group_id']]) ? $groupNames[$tagGroup['group_id']] : 'Unknown Group';
            }

            // Display the tag name and the list of group names
            echo '<tr>
                            <td>' . htmlspecialchars($tag['name']) . '</td>
                            <td>' . htmlspecialchars('[' . implode(', ', $groupNameList) . ']') . '</td>
                        </tr>';
        }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>




qlsdjf


<?php
$nameSubList = ['a', 'b'];
$groupList = ['a', 'b', 'c'];

// Filtrer les éléments de $groupList qui ne sont pas dans $nameSubList
$itemsToCheck = array_diff($groupList, $nameSubList);
?>

<td>
    <?php foreach ($itemsToCheck as $item): ?>
        <input type="checkbox" name="items[]" value="<?php echo htmlspecialchars($item); ?>">
        <label><?php echo htmlspecialchars($item); ?></label><br>
    <?php endforeach; ?>
</td>



qsdf


<?php
$nameSubList = ['a', 'b'];
$groupList = ['a', 'b', 'c'];
?>

<td>
    <?php foreach ($groupList as $item): ?>
        <input type="checkbox" name="items[]" value="<?php echo htmlspecialchars($item); ?>" <?php if (in_array($item, $nameSubList)) echo 'checked'; ?>>
        <label><?php echo htmlspecialchars($item); ?></label><br>
    <?php endforeach; ?>
</td>



<td>
    <input type="checkbox" name="items[]" value="a" checked>
    <label>a</label><br>
    <input type="checkbox" name="items[]" value="b" checked>
    <label>b</label><br>
    <input type="checkbox" name="items[]" value="c">
    <label>c</label><br>
</td>


qflmj


formData.forEach((value, key) => {
const checkbox = document.querySelector(`input[name="${key}"][value="${value}"]`);
checkboxStates[checkbox.id] = checkbox.checked;
});