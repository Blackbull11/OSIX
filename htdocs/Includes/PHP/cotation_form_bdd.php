<?php
session_start();

// Connexion à la base de données
try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}


// Vérifiez si une source est spécifiée (par exemple via ?source_id dans l'URL)
if (!isset($_POST['source_id']) || !is_numeric($_POST['source_id'])) {
    echo "ID de la source manquant ou invalide.";
    exit;
}

// Récupérez les informations de la source
$source_id = $_POST['source_id'];
$req = $bdd->prepare('SELECT *, (CAST(SUBSTRING_INDEX(eval, ";", 1) AS DECIMAL(10,2)) / CAST(SUBSTRING_INDEX(eval, ";", -1) AS DECIMAL(10,2))) * 5 AS rating FROM sources WHERE id = :source_id');
$req->execute(['source_id' => $source_id]);
$source = $req->fetch();

function getMonthlyOpenings($sourceId, $bdd) {
    // Requête SQL pour compter les ouvertures par mois pour une source donnée
    $stmt = $bdd->prepare("
                                SELECT 
                                    DATE_FORMAT(date, '%Y-%m') as month, 
                                    COUNT(*) as openings 
                                FROM link_openings 
                                WHERE source = :source
                                GROUP BY month
                                ORDER BY month ASC
                            ");
    $stmt->execute(['source' => $sourceId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($results as $result) {
        $data[] = [
            'month' => $result['month'], // Mois
            'openings' => $result['openings'], // Nombre d'ouvertures
        ];
    }
    return $data;
}
function getMonthlyAdditions($sourceId, $bdd) {
    // Étape 1 : Compter les ajouts par mois depuis la table "links"
    $stmt = $bdd->prepare("
        SELECT 
            DATE_FORMAT(date, '%Y-%m') as month, 
            COUNT(*) as additions 
        FROM links 
        WHERE source = :source
        GROUP BY month
        ORDER BY month ASC
        ");
    $stmt->execute(['source' => $sourceId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Étape 2 : Récupérer les dates JSON de la colonne "derivatives" dans la table "sources"
    $stmt = $bdd->prepare("SELECT derivatives FROM sources WHERE id = :source");
    $stmt->execute(['source' => $sourceId]);
    $derivatives = $stmt->fetchColumn(); // Récupérer la colonne "derivatives" (au format JSON)

    // Convertir le JSON en tableau PHP (sécurité contre les nulls)
    $decodedDerivatives = !empty($derivatives) ? json_decode($derivatives, true) : [];

    // Étape 3 : Parcourir les dates de "derivatives" et extraire le mois au bon format
    $derivativesCounts = [];
    foreach ($decodedDerivatives as $timestamp) {
        $month = date('Y-m', $timestamp); // Convertir le timestamp en format "YYYY-MM"
        if (!isset($derivativesCounts[$month])) {
            $derivativesCounts[$month] = 0;
        }
        $derivativesCounts[$month]++;
    }

    // Étape 4 : Fusionner les données des deux sources (dates de "links" et de "derivatives")
    $data = [];
    foreach ($results as $result) {
        $month = $result['month'];
        $additions = $result['additions'];

        // Ajouter les ajouts venant de "derivatives" au mois correspondant
        if (isset($derivativesCounts[$month])) {
            $additions += $derivativesCounts[$month];
            unset($derivativesCounts[$month]); // Supprimer ce mois des dérivés pour éviter les doublons
        }

        $data[] = [
            'month' => $month,
            'additions' => $additions,
        ];
    }

    // Étape 5 : Ajouter les mois restants venant de "derivatives" uniquement
    foreach ($derivativesCounts as $month => $count) {
        $data[] = [
            'month' => $month,
            'additions' => $count,
        ];
    }

    // Étape 6 : Trier les données par mois
    usort($data, function ($a, $b) {
        return strcmp($a['month'], $b['month']);
    });

    return $data;
}
function color_cotation($cotation) {
    // Vérification du format de la cotation : une lettre entre A et F suivie d'un chiffre entre 1 et 6
    if (!preg_match('/^[a-fA-F][1-6]$/', $cotation)) {
        // Si le format n'est pas valide, on retourne la valeur telle quelle
        return $cotation;
    }

    // Mise en majuscule de la lettre
    $lettre = strtoupper($cotation[0]);
    $chiffre = $cotation[1];

    // Définition de la couleur selon le critère
    if ($lettre === 'F' || $chiffre === '6') {
        $couleur = 'blue';
    } elseif ($lettre === 'E' || $chiffre === '5') {
        $couleur = 'red';
    } elseif ($lettre === 'A' || $chiffre === '1') {
        $couleur = 'green';
    } else {
        // Calcul d'une échelle intermédiaire entre le rouge (E ou 5) et le vert (A ou 1)
        $ecartLettre = ord('E') - ord($lettre); // e.g. E->0, D->1, C->2...
        $ecartChiffre = $chiffre - 5;          // e.g. Chiffre 5->0, 4->-1...

        // Conversion en proportion pour calculer un dégradé
        $rouge = max(0, 255 - (64 * $ecartLettre)); // Moins la valeur s'éloigne de E, plus le rouge diminue
        $vert = max(0, 255 + (64 * $ecartChiffre)); // Plus la valeur se rapproche de A ou 1, plus le vert augmente

        $couleur = sprintf('rgb(%d, %d, 0)', $rouge, $vert);
    }

    // Retourne la cotation formatée avec la couleur
    return "<span style='color: $couleur;'>$lettre$chiffre</span>";
}

$MonthlyOpenings = getMonthlyOpenings($source['id'], $bdd);
$MonthlyAdditions = getMonthlyAdditions($source['id'], $bdd);

if (!empty($source['cotation'])) {
     echo '<div "cotation-data">';
    $cotation_data =json_decode($source['cotation'], true);
    if ($cotation_data['eval_status'] == "A évaluer"){
        $grade_sugg = empty($cotation_data['grade_suggestion']) ? "Néant" : $cotation_data['grade_suggestion'];
        echo '<div id="last-cotation" style="margin-bottom: 2em;">';
        echo '<h3>Informations sur la demande de cotation </h3>';
        echo '<span>Date de la demande : '.$cotation_data['demand_date'].'</span><br>';
        echo '<span>Commentaire : '.$cotation_data['demand_message'].'</span><br>';
        echo '<span>Cadre d\'emploi : '.$cotation_data['demand_useframe'].'</span><br>';
        echo '<span>Suggestion de note : '.$grade_sugg.'</span><br>';
        echo '</div><hr>';
    }
    if (isset($cotation_data['grade'])) {
        echo '<div id="last-cotation" style="margin-bottom: 2em;">';
        echo '<h3>Dernière cotation de la source : </h3>';
        $grade = $cotation_data['grade'];
        $comment = $cotation_data['comment'];
        $useframe = $cotation_data['useframe'];
        $date = date("d/m/y", strtotime($cotation_data['date']));
        $features = json_decode($cotation_data['features']); ?>
        <div class="source-cotation-header">
            <h4>Cotation de la source :
                <?php echo color_cotation($grade);
                $useframe = (isset($cotation['useframe'])) ? " pour ".$cotation['useframe']. "." : "";
                echo '<span>('.$date.')'.$useframe.'</span>
                                        <button class="bordered" id="showCribleButton" onclick="document.getElementById(\'crible-eval'.$source['id'].'\').style.display=\'block\'; document.getElementById(\'showCribleButton\').style.display=\'none\'; document.getElementById(\'hideCribleButton\').style.display=\'inline\'"> Afficher le crible</button>
                                        <button id="hideCribleButton" onclick="document.getElementById(\'crible-eval'.$source['id'].'\').style.display=\'none\'; document.getElementById(\'showCribleButton\').style.display=\'inline\'; document.getElementById(\'hideCribleButton\').style.display=\'none\'" class="bordered" style="display : none;"> Masquer le crible </button>
                                        </h4>' ?>
            <span><strong>Commentaire : </strong> <?= $comment ?></span><br>
            <span><strong>Cadre d'emploi : </strong> <?= $useframe ?></span> <br>
        </div>
        <div id="crible-eval<?= $source['id'] ?>" style="gap: 20px; display : none; background-color: #d8e2e6; border-radius : 10px; padding : 15px; font-family: 'monospace'">
            <div  style="font-family: monospace;">
                <h6 style="margin-top : 15px; font-size: small; text-transform: uppercase; margin-bottom : 5px">Source</h6>
                <?php
                // Charger les questions pour la catégorie "Source"
                $query = 'SELECT * FROM evalquestions WHERE category = "source"';
                $stmt = $bdd->prepare($query);
                $stmt->execute();
                $questions = $stmt->fetchAll();
                $i = 0;
                foreach ($questions as $question) {
                    $checked = ($features != null && isset($features[$i]) && $features[$i] == 1) ? "checked" : "";
                    echo '<label class="source-eval-item">';
                    echo '<input disabled="disabled" type="checkbox" readonly class="source-eval-item" value=1 style="margin: 0 !important;width:  unset !important;" '.$checked.'>';
                    echo '<b>  ' . htmlspecialchars($question['title']) . '</b> : ' . htmlspecialchars($question['question']);
                    echo '</label><br>';
                    $i += 1;
                }
                ?>
            </div>
            <div style="font-family: monospace;">
                <h6 style="margin-top : 15px; font-size: small; font-family: monospace; text-transform: uppercase; margin-bottom : 5px">Information</h6>
                <?php
                // Charger les questions pour la catégorie "Information"
                $query = 'SELECT * FROM evalquestions WHERE category = "information"';
                $stmt = $bdd->prepare($query);
                $stmt->execute();
                $questions = $stmt->fetchAll();
                foreach ($questions as $question) {
                    $checked = ($features !== null && isset($cotation_features[$i]) && $cotation_features[$question['id']] == 1) ? "checked" : "";
                    echo '<label class="source-eval-item">';
                    echo '<input disabled="disabled" type="checkbox" class="source-eval-item" value= 1 style="margin: 0 !important;width:  unset !important;" '.$checked.'>';
                    echo ' <b>  ' . htmlspecialchars($question['title']) . '</b> : ' . htmlspecialchars($question['question']);
                    echo '</label><br>';
                    $i += 1;
                }
                ?>
            </div>
            <div style="font-family: monospace;">

                <?php
                // Charger les questions pour la catégorie "Information"
                $query = 'SELECT * FROM evalquestions WHERE category = "caracterisation_information"';
                $stmt = $bdd->prepare($query);
                $stmt->execute();
                $questions = $stmt->fetchAll();

                $features = [];
                $req = $bdd->prepare('SELECT COUNT(*) AS nbr FROM links WHERE source = :source_id');
                $req->execute(['source_id' => $source['id']]);
                $quotient = $req->fetch()['nbr'];
                for ($i = 1; $i < 5; $i++) {
                    $req = "SELECT COUNT(*) AS nbr FROM links WHERE feature".$i." = 1 AND source = :source_id";
                    $stmt = $bdd->prepare($req);
                    $stmt->execute(['source_id' => $source['id']]);
                    $features[$i] = $stmt->fetch()['nbr'];
                }

                $i = 1;
                ?>
                <h6 style="margin-top : 15px; font-size: small; font-family: monospace; text-transform: uppercase; margin-bottom : 5px">Statistiques (sur <?= $quotient?> liens).</h6>
                <?php foreach ($questions as $question) {
                    echo '<span>'.$question['question'].' : <b>'.((float)$features[$i] / (float)$quotient * 100).'%</b>. </span><br>';
                }
                ?>
            </div>
        </div>
        <?php echo '</div><hr>';
    }
    echo '</div>';
}
    ?>

<div id="source-cotation-information" style="margin-bottom: 2em;">
    <?php // Extraction du score et calcul de la note (entre 0 et 5)
    list($score, $coefficient) = explode(';', $source['eval']);
    // Nettoyage des données
    $coefficient = (float)($coefficient); // Supprimer les espaces potentiels et forcer en entier
    $req = $bdd->prepare('SELECT * FROM links WHERE source = :source_id');
    $req->execute(['source_id' => $source['id']]);
    $links = $req->fetchAll();
    $number_of_links = count($links);
    ?>
    <h2>Informations sur la source</h2>
    <p><strong>Nom de la source :</strong> <?= htmlspecialchars($source['name']); ?></p>
    <p><strong>Description :</strong> <?= htmlspecialchars($source['doc']); ?></p>
    <p><strong>Liens dérivés (<?= $number_of_links?>):</strong></p>
    <ul style="list-style-type: square; padding-left: 2em;">
    <?php
            foreach ($links as $link) {
                echo '<li><a href="'.$link['url'].'" target="_blank">'.$link['url'].'</a></li>';
            }
            if ($number_of_links == 0) {
                echo '<li>Aucun lien dérivé disponible</li>';
            }
        ?>
    </ul>
    <?php
        $req = "SELECT SUM(rating), count(rating) FROM links WHERE source = :source_id";
        $stmt = $bdd->prepare($req);

    ?>
    <p><strong>Evaluation :</strong> <?= round(htmlspecialchars($source['rating']), 2); ?>/5 (pour <?= (int)$coefficient/5 ?> évaluations)</p>
    <?php echo '<button class="new-button-smaller" onclick=\'GenerateOpeningsGraph(" '.$source['name'].' ",'. json_encode($MonthlyOpenings).','.json_encode($MonthlyAdditions).',"'. $source['date'] .'")\'><img src="Includes/Images/Icone Graphe.png" class="small-button">  Visualiser l\'activité</button>'; ?>
</div>



<div id="source-cotation-form">
    <h2>Nouvelle cotation</h2>
    <form action="Includes/PHP/submit_evaluation.php" method="POST">
        <input type="hidden" name="source_id" value="<?= htmlspecialchars($source_id); ?>">

        <!-- Section crible -->
        <div id="crible-eval<?= $source['id'] ?>" style="gap: 20px; background-color: #d8e2e6; border-radius : 10px; padding : 15px; font-family: 'monospace'">
            <!-- Questions de la catégorie "Source" -->
            <!-- Catégorie Source -->
            <div>
                <h6>Crible Source</h6>
                <?php
                $query = 'SELECT * FROM evalquestions WHERE category = "source"';
                $stmt = $bdd->prepare($query);
                $stmt->execute();
                $questions = $stmt->fetchAll();
                $i = 0; // Compteur de checkbox
                foreach ($questions as $question) {
                    echo '<label>';
                    echo '<input type="checkbox" name="features[' . $i . ']" value="1">'; // Index explicite pour chaque position
                    echo htmlspecialchars($question['title']) . ': ' . htmlspecialchars($question['question']);
                    echo '</label><br>';
                    $i++;
                }
                ?>
            </div>

            <!-- Catégorie Information -->
            <div>
                <h6>Crible Information</h6>
                <?php
                $query = 'SELECT * FROM evalquestions WHERE category = "information"';
                $stmt = $bdd->prepare($query);
                $stmt->execute();
                $questions = $stmt->fetchAll();
                foreach ($questions as $question) {
                    echo '<label>';
                    echo '<input type="checkbox" name="features[' . $i . ']" value="1">'; // Continuation de l'index
                    echo htmlspecialchars($question['title']) . ': ' . htmlspecialchars($question['question']);
                    echo '</label><br>';
                    $i++;
                }
                ?>
            </div>
        </div>

        <!-- Note globale -->
        <label for="grade">Bigramme :</label>
        <input type="text" id="grade" name="grade"><br>
        <!-- Note globale -->
        <label for="note">Cadre d'emploi :</label>
        <input type="text" id="useframe" name="useframe"><br>
        <!-- Commentaire -->
        <label for="commentaire">Commentaire :</label><br>
        <textarea id="commentaire" name="commentaire" rows="5" cols="200" placeholder="Ajoutez un commentaire sur la cotation en réponse à la demande formulée..." required></textarea><br>

        <button type="submit">Soumettre l'évaluation</button>
    </form>
</div>
