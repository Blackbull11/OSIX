<?php
session_start();

try {
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
}
catch (Exception $e) {
    exit('Erreur : ' . $e->getMessage());
}

function is_receiver_PHP($receiver) {
    // Décoder le JSON contenu dans le champ receiver
    $receiverData = json_decode($receiver, true);

    // Si le JSON est invalide ou vide, on considère que l'utilisateur n'est pas concerné
    if (!$receiverData) {
        return false;
    }

    // Récupérer les groupes de l'utilisateur à partir de $_SESSION
    $userGroups = isset($_SESSION['groups']) ? json_decode($_SESSION['groups']) : [];
    $user_id = isset($_SESSION['id_actif']) ? $_SESSION['id_actif'] : 0;

    // Vérifier si l'utilisateur est directement mentionné dans "users"
    if (isset($receiverData['users']) && in_array($user_id, $receiverData['users'])) {
        return true;
    }

    // Vérifier si un des groupes de l'utilisateur est mentionné dans "groups"
    if (isset($receiverData['groups'])) {
        foreach ($userGroups as $group) {
            if (in_array($group, $receiverData['groups'])) {
                return true;
            }
        }
    }

    // Si aucune condition n'est remplie, l'utilisateur n'est pas concerné
    return false;
}

function newsLines($mailData, $sqlRequest)
{
    echo '<div class="recentFeed">';
    foreach ($mailData as $item) {
        if (is_receiver_PHP($item['receiver'])) {
            $sqlRequest->execute([':id' => $item['issuer']]);
            $userName = $sqlRequest->fetch();
            $data = json_decode($item['receiver'], true);
            $viewed = (isset($data['viewed']) && in_array($_SESSION['id_actif'], $data['viewed'])) ? 1 : 0;
            $adminMail = ($item['type'] === 'ADMIN') ? true : false;
            ?>
            <div class="news-item">
                <button id="showMailButton_<?= $item['id']?>"
                        class="collapsible-news" style="display: flex; width: 100%; align-items: center;
                            <?= (!$viewed) ? "font-weight: bold; background-color: cornflowerblue;" : "" ?>" >
                    <span style="width: 40%; overflow: hidden; white-space: nowrap"> <?php
                        // Découper le message à la première occurrence de <br>
                        $messageTitle = explode('<br>', $item['message'])[0];
                        echo htmlspecialchars($messageTitle); print_r($userName)
                        ?> </span>
                    <span style="margin-left: 10%; position:relative; width: 15%"> Expéditeur : <?= (is_array($userName) && array_key_exists(0, $userName)) ? $userName[0] : 'Erreur senderId' ?> </span>

                    <?= $adminMail ? ('<span style="margin-left: 5%; position:relative;">'.
                        ($item['treated'] === 1 ? 'Traitée <input type="checkbox" onclick="adminMailStatus(this, '. $item['id'] . ' )" checked/> '
                            : 'En cours <input type="checkbox" onclick="adminMailStatus(this, '. $item['id'] . ' )"/>')
                        .' </span>') : ''?>

                    <span style="margin-left: auto"> <?= date("d/m/Y H:i", strtotime($item['date'])) ?> </span>
                </button>
                <div class="collapsible-news-content" style="display: flex; justify-content: space-between">
                    <p> <?= $item['message'] ?> </p>
                    <p> <button style="background-color: rgba(161,177,238,0.7);" onclick="<?= isset($userName['username']) ? 'selectedUsers.add(parseInt('.$item['issuer'].'));' : ""?><?=  isset($userName['username']) ? ' userSelectedRecipients.add({ mail: \''. $userName['username']. '\', identifiant: '. $item['issuer'] . '});' : "";?> updateRecipientsDisplay(); writeModal(); " }"> Répondre </button></p>
                </div>
            </div>
            <?php
        }
    }
    echo "</div>";
}


function sentLines($mailData, $sqlRequest1, $sqlRequest2)
{
    echo '<div class="recentFeed">';

    foreach ($mailData as $item) {
            $destinataires = array();
            $data = json_decode($item['receiver'], true); // true pour obtenir un tableau associatif
            $targetedUsers = $data['users'] ?? []; // Utiliser un opérateur de coalescence pour éviter une erreur
            $data = json_decode($item['receiver'], true); // true pour obtenir un tableau associatif
            $targetedGroups = $data['groups'] ?? []; // Utiliser un opérateur de coalescence pour éviter une erreur

            $list = ''; // Initialisation de la variable
            foreach ($targetedUsers as $t) {
                $sqlRequest1->execute([':id' => $t]);
                $row = $sqlRequest1->fetch(PDO::FETCH_ASSOC);
                if ($row) {  // Vérifier que la requête a retourné un résultat
                    $list .= $row['username'] . ', ';
                }
            }

            foreach ($targetedGroups as $t) {
                $sqlRequest2->execute([':id' => $t]);
                $row = $sqlRequest2->fetch(PDO::FETCH_ASSOC);
                if ($row) {  // Vérifier que la requête a retourné un résultat
                    $list .= $row['name'] . ', ';
                }
            }

            $list = rtrim($list, ', '); // Supprimer la dernière virgule et l'espace
            ?>
            <div class="news-item">
                <button id="showMailButton_<?= $item['id']?>"
                        class="collapsible-news" style="display: flex; width: 100%; align-items: center;" >
                    <span style="width: 40%; overflow: hidden; white-space: nowrap"> <?php
                        // Découper le message à la première occurrence de <br>
                        $messageTitle = explode('<br>', $item['message'])[0];
                        echo htmlspecialchars($messageTitle);
                        ?> </span>
                    <span style="margin-left: 10%; position:relative; width: 15%"> Destinataire(s) : <?= $list ?> </span>

                    <span style="margin-left: auto"> <?= date("d/m/Y H:i", strtotime($item['date'])) ?> </span>
                </button>
                <div class="collapsible-news-content" style="display: flex; justify-content: space-between">
                    <p> <?= $item['message'] ?> </p>
                </div>
            </div>
            <?php
        }
    echo "</div>";
}
if (isset($_SESSION['id_actif'])
    && isset($_POST['action'])
    && $_POST['action'] === 'loadMails'
    && isset($_POST['keyWord'])
    && $_POST['keyWord'] === 'recentMessaging'
)
    {
        $query = 'SELECT * FROM news WHERE ((date >= NOW() - INTERVAL 30 DAY) AND (type != :cste) ) ORDER BY date DESC';
        $stmt = $bdd->prepare($query);
        $stmt->execute([
                ':cste' => 'ADMIN'
        ]);
        $news = $stmt->fetchAll();

        $query = 'SELECT username FROM users WHERE id = :id';
        $stmt = $bdd->prepare($query);
        echo '<h3>Messages récents</h3>';

        newsLines($news, $stmt);
    }


elseif (isset($_SESSION['id_actif'])
    && isset($_POST['action'])
    && $_POST['action'] === 'loadMails'
    && isset($_POST['keyWord'])
    && $_POST['keyWord'] === 'boxMessaging'
)
{
    $query = 'SELECT * FROM news WHERE (type = :box) ORDER BY date DESC';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':box' => 'BOX']);
    $news = $stmt->fetchAll();

    $query = 'SELECT username FROM users WHERE id = :id';
    $stmt = $bdd->prepare($query);
    echo '<h3>Actualités de BOX</h3>';

    newsLines($news, $stmt);
}

elseif (isset($_SESSION['id_actif'])
    && isset($_POST['action'])
    && $_POST['action'] === 'loadMails'
    && isset($_POST['keyWord'])
    && $_POST['keyWord'] === 'glorixMessaging'
)
{
    $query = 'SELECT * FROM news WHERE (type = :glorix) ORDER BY date DESC';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':glorix' => 'GLORIX']);
    $news = $stmt->fetchAll();

    $query = 'SELECT username FROM users WHERE id = :id';
    $stmt = $bdd->prepare($query);
    echo '<h3>Actualités de GLORIX</h3>';
    newsLines($news, $stmt);
}

elseif (isset($_SESSION['id_actif'])
    && isset($_POST['action'])
    && $_POST['action'] === 'loadMails'
    && isset($_POST['keyWord'])
    && $_POST['keyWord'] === 'epixMessaging'
)
{
    $query = 'SELECT * FROM news WHERE (type = :epix) ORDER BY date DESC';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':epix' => 'EPIX']);
    $news = $stmt->fetchAll();

    $query = 'SELECT username FROM users WHERE id = :id';
    $stmt = $bdd->prepare($query);
    echo '<h3>Actualités de EPIX</h3>';
    newsLines($news, $stmt);
}

elseif (isset($_SESSION['id_actif'])
    && isset($_POST['action'])
    && $_POST['action'] === 'loadMails'
    && isset($_POST['keyWord'])
    && ($_POST['keyWord'] === 'ownMessagingReceived')
)
{
    $query = 'SELECT * FROM news WHERE (type = :private) ORDER BY date DESC';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':private' => 'PRIVATE']);
    $news = $stmt->fetchAll();

    $query = 'SELECT username FROM users WHERE id = :id';
    $stmt = $bdd->prepare($query);
    ?>
    <div style="display: flex">
                    <h3 id="ownMessagingLabel">Messagerie personnelle - messages reçus</h3>
                    <div style="display: flex; margin-left: auto; ">
                        <button class="export-btn" onclick="writeModal()">Nouveau message</button>
                        <button class="export-btn" onclick="loadMails('ownMessaging')" id="ownMessagingChangeButton">Messages envoyés</button>
                    </div>
                </div>
                <hr>
    <?php newsLines($news, $stmt);
}

elseif (isset($_SESSION['id_actif'])
    && isset($_POST['action'])
    && $_POST['action'] === 'loadMails'
    && isset($_POST['keyWord'])
    && ($_POST['keyWord'] === 'ownMessagingSent')
)
{
    $query = 'SELECT * FROM news WHERE (issuer = :userId) ORDER BY date DESC';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':userId' => $_SESSION['id_actif']]);
    $news = $stmt->fetchAll();

    ?>
    <div style="display: flex">
        <h3 id="ownMessagingLabel">Messagerie personnelle - messages envoyés</h3>
        <div style="display: flex; margin-left: auto; ">
            <button class="export-btn" onclick="writeModal()">Nouveau message</button>
            <button class="export-btn" onclick="loadMails('ownMessaging')" id="ownMessagingChangeButton">Messages reçus</button>
        </div>
    </div>
    <hr>
    <?php
    $query = 'SELECT username FROM users WHERE (id = :id)';
    $sqlRequest1 = $bdd -> prepare($query);

    $query = 'SELECT name FROM usergroups WHERE (id = :id)';
    $sqlRequest2 = $bdd -> prepare($query);
    sentLines($news, $sqlRequest1, $sqlRequest2);
}


elseif (isset($_SESSION['id_actif'])
    && isset($_POST['action'])
    && $_POST['action'] === 'loadMails'
    && isset($_POST['keyWord'])
    && ($_POST['keyWord'] === 'adminMessaging')
    && $_SESSION['is_admin']
)
{
    $query = 'SELECT * FROM news WHERE (type = :cste) ORDER BY date DESC';
    $stmt = $bdd->prepare($query);
    $stmt->execute([
            ':cste' => 'ADMIN'
    ]);
    $news = $stmt->fetchAll();

    $query = 'SELECT username FROM users WHERE id = :id';
    $stmt = $bdd->prepare($query);
    echo '<h3>Messages récents</h3>';

    newsLines($news, $stmt);
}



elseif (isset($_SESSION['id_actif'])
    && isset($_POST['action'])
    && $_POST['action'] === 'viewedMail'
    && isset($_POST['mailId']))
{
    $query = 'SELECT receiver FROM news WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $_POST['mailId']]);
    $receiver = $stmt->fetch();
    $rec = json_decode($receiver['receiver'], true);

    $rec['viewed'][] = $_SESSION['id_actif'];

    $query = 'UPDATE news SET receiver = :rec WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $_POST['mailId'], ':rec' => json_encode($rec)]);
    echo 'ok viewed';
}

elseif (isset($_SESSION['id_actif'])
        && isset($_POST['action'])
        && $_POST['action'] === 'loadUsers'
) {
    $query = 'SELECT id, username FROM users WHERE (id != :id)';
    $stmt = $bdd->prepare($query);
    $stmt->execute([':id' => $_SESSION['id_actif']]);
    $users = $stmt->fetchAll();


    echo '<option value="-1">Sélectionner un destinataire</option>';
    foreach ($users as $user) {
        echo '<option value="' . $user['id'] . '" >' . $user["username"] . '</option>';
    }
}

elseif (isset($_SESSION['id_actif'])
    && isset($_POST['action'])
    && $_POST['action'] === 'loadGroups'
) {
    $query = 'SELECT id, name FROM usergroups';
    $stmt = $bdd->prepare($query);
    $stmt->execute();
    $userGroups = $stmt->fetchAll();
    echo '<option value="-1">Sélectionner un groupe</option>';

    foreach ($userGroups as $userGroup) {
        echo '<option value="' . $userGroup['id'] . '" > | ' . $userGroup['name'] . '</option>';
    }
}

elseif (isset($_SESSION['id_actif'])
&& isset($_POST['action'])
&& $_POST['action'] === 'submitMail'
&& isset($_POST['mailContent'])
&& isset($_POST['groupSelectedRecipients'])
&& isset($_POST['userSelectedRecipients'])
 )
{
    echo 'ok mail';
    $receiver = array();
    $receiver['users'] = json_decode($_POST['userSelectedRecipients']);
    $receiver['groups'] = json_decode($_POST['groupSelectedRecipients']);
    $receiver['viewed'] = array();

    $query = 'INSERT INTO news (issuer, receiver, message, date, type) VALUES (:issuer, :receiver, :message, :date, :type)';
    $stmt = $bdd->prepare($query);
    $stmt->execute([
        ':issuer' => $_SESSION['id_actif'],
        ':receiver' => json_encode($receiver),
        ':message' => $_POST['mailContent'],
        ':date' => date('Y-m-d H:i:s'),
        ':type' => isset($_POST['type']) ? $_POST['type'] : 'PRIVATE'
    ]);

}

elseif (isset($_SESSION['id_actif'])
    && isset($_SESSION['is_admin'])
    && $_SESSION['is_admin'] === 1
&& isset($_POST['action'])
&& $_POST['action'] === 'adminMailTreated'
&& isset($_POST['mailId'])
&& isset($_POST['newStatus']) )
{
    try {
    $query = 'UPDATE news SET treated = :status WHERE id = :id';
    $stmt = $bdd->prepare($query);
    $stmt->execute([
       ':status' => $_POST['newStatus'],
        ':id' => $_POST['mailId']
    ]);
    echo 'ok';
}
catch (PDOException $e) {
    echo $e->getMessage();}

}

elseif ((isset($_SESSION['id_actif']) && $_SESSION['id_actif'] === 0) || !isset($_SESSION['id_actif']) && isset($_POST['action']) && $_POST['action'] === 'loadMails')
{

    ?>
        <h3>Messages récents</h3>
        <!-- Fil d'actualité -->
        <div class="recentFeed">
            <div class="news-item">
                <button class="collapsible-news">
                    Bienvenus sur OSIX ! <a href="profils.php" style="color : black; font-style: italic">Créez votre compte</a> et suivez le tutoriel pour rentrer dans la révolution OSIX !
                </button>

                <div class="collapsible-news-content" style="display : none; justify-content: flex-start">
                    <div class="message-data">
                        <span>Envoyé le : 26/03/2025</span><br>
                        <span>Par : Vos preux serviteurs de la X24 !</span><br>
                        <span>À : Tous les analystes innovateurs du centre</span>
                    </div>
                    <hr>
                    <div class="message-content" style="display: flex; justify-content: center; align-items: center; width: 500px; text-align: center">
                        <a href="profils.php" style="color : black; font-style: italic">Je crée mon compte sans plus attendre</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
}
?>
