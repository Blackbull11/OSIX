<?php
session_start();


//Mesures de protectin cybersécurité (CSP : Content Security Policy)
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; frame-ancestors 'none'; form-action 'self'; base-uri 'self';");

    // Bloquer tous les accès provenant d'autres domaines
    header("Access-Control-Allow-Origin: http://osix.rin");
    header("Access-Control-Allow-Methods: GET,POST");
    header("X-Frame-Options: DENY"); // Empêche votre site d'être chargé dans un iframe
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: no-referrer-when-downgrade");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=(), browsing-topics=()"); // Désactive les permissions inutiles

$bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');

?>
<header>
    <div class ="left-header">

        <?php if (strstr($_SERVER['PHP_SELF'], 'EPIX')){
            echo '<a id="OSIX" href="index.php" >
                    <img src="Includes/Images/Logo Noir Complet Cropped.png" alt="Logo OSIX" class="logo"  >
                </a>
                <img id="Separateur" src="Includes\Images\Trait noir vertical.png" class="header-hr" >
                <a id="EPIX" href="EPIX_interface.php" >
                    <img src="Includes\Images\Logo Epix Blanc.png" alt="Logo EPIX" class="logo" style="width: 8vw"  >
                </a>';
            if (isset($_SESSION['is_admin']) and $_SESSION['is_admin']) {
                echo'<span style="background-color: rgba(87,255,126,0.5); font-weight: bold; font-family: \'Arial Black\'; font-size: 2vw; padding : 1vw; color : white; position: relative; bottom: 1.4vw"> ADMIN </span>';
            }
        }
        elseif (strstr($_SERVER['PHP_SELF'], 'BOX')){
            echo '<a id="OSIX" href="index.php" >
                    <img src="Includes/Images/Logo Noir Complet Cropped.png" alt="Logo OSIX" class="logo"  >
                </a>
                <img id="Separateur" src="Includes\Images\Trait noir vertical.png" class="header-hr" >
                <a id="BOX" href="http://osix.rin/BOX.php" >
                    <img src="Includes\Images\Logo BOX.png" alt="Logo BOX" class="logo" style="width: unset" >
                </a>';
            if (isset($_SESSION['is_admin']) and $_SESSION['is_admin']) {
                echo'<span style="background-color: rgba(87,255,126,0.5); font-weight: bold; font-family: \'Arial Black\'; font-size: 2vw; padding : 1vw; color : white; position: relative; bottom: 1.4vw"> ADMIN </span>';
            }
        }
        elseif (strstr($_SERVER['PHP_SELF'], 'GLORIX')){
            echo '<a id="OSIX" href="index.php" >
                <img src="Includes/Images/Logo Noir Complet Cropped.png" alt="Logo OSIX" class="logo"  >
            </a>
            <img id="Separateur" src="Includes\Images\Trait noir vertical.png" class="header-hr">
            <a id="GLORIX" href="GLORIX_gestionnaire.php" >
                <img src="Includes\Images\Logo GLORIX OK.png" alt="Logo GLORIX" class="logo" style="width: unset" >
            </a>';
            if (isset($_SESSION['is_admin']) and $_SESSION['is_admin']) {
                echo'<span style="background-color: rgba(87,255,126,0.5); font-weight: bold; font-family: \'Arial Black\'; font-size: 2vw; padding : 1vw; color : white; position: relative; bottom: 1.4vw"> ADMIN </span>';
            }
        }
        elseif (strstr($_SERVER['PHP_SELF'], 'administration')){
            echo '<a id="OSIX" href="index.php" >
                <img src="Includes/Images/Logo Noir Complet Cropped.png" alt="Logo OSIX" class="logo" style="width: 20vw; height: 7.08vw">
            </a>';
            if (isset($_SESSION['is_admin']) and $_SESSION['is_admin']) {
                echo'<span style="background-color: rgba(255, 67, 67, 0.5); font-weight: bold; font-family: \'Arial Black\'; font-size: 2vw; padding : 1vw; color : white; position: relative; bottom: 2.7vw"> ADMIN </span>';
            }
        }
        else{
            echo '<a id="OSIX" href="index.php" >
                <img src="Includes/Images/Logo Noir Complet Cropped.png" alt="Logo OSIX" class="logo" style="width: 20vw; height: 7.08vw">
            </a>';
            if (isset($_SESSION['is_admin']) and $_SESSION['is_admin']) {
                echo'<span onclick="window.open(\'/administration.php\')" style="cursor : pointer; background-color: rgba(87,255,126,0.5); font-weight: bold; font-family: \'Arial Black\'; font-size: 2vw; padding : 1vw; color : white; position: relative; bottom: 2.7vw"> ADMIN </span>';
            }};


        ?>
    <?php
        function is_receiver($receiver) {
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
        function count_user_messages($news, $user_id) {
            $count = 0;

            // Si aucun utilisateur connecté, retourne 1 par défaut
            if ($user_id == 0) {
                return 1;
            }

            try {
                $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
            }
            catch (Exception $e){
                exit('Erreur pas d accès à la bdd : '.$e->getMessage());
            }

            // Parcourir les messages et vérifier s'ils concernent l'utilisateur connecté
            foreach ($news as $item) {
                if ($item['receiver'] === null) {
                    // Supprimer l'élément de la base de données
                    $statement = $bdd->prepare("DELETE FROM news WHERE id = :id");
                    $statement->bindValue(':id', $item['id'], PDO::PARAM_INT);
                    $statement->execute();
                }
                elseif (is_receiver($item['receiver']))
                {
                    $r = json_decode($item['receiver'], true);
                    if (!in_array($user_id, $r['viewed'])) {
                        $count++;
                        }
                }
            }

            return $count;
        }
        // Exemple de récupération des données de la base (news et utilisateur connecté)
        $news = $bdd->query("SELECT * FROM news")->fetchAll();
        $user_id = isset($_SESSION['id_actif']) ? $_SESSION['id_actif'] : 0;

        // Nombre de messages destinés à l'utilisateur
        $message_count = count_user_messages($news, $user_id);
    ?>
    </div>
    <div class ="right-header">
        <a id="news-button" href="http://osix.rin/News_page.php"><span>Consulter / Envoyer un message</span>Actualités</a>
            <?php if ($message_count > 0): ?>
                <span class="notification-badge"><?= $message_count ?></span>
            <?php endif; ?>
        <a id="box-button" href="http://osix.rin/BOX_gestionnaire.php"><span>Modifier ma boîte à outil</span>BOX</a>
        <a id="glorix-button" href="http://osix.rin/GLORIX_gestionnaire.php"><span>Gérer mes listes</span>GLORIX</a>
        <a id="epix-button" href="http://osix.rin/EPIX_gestionnaire.php"><span>Gérer mes projets</span>EPIX</a>
            <?php
                if (isset($_SESSION["user_actif"])) {
                    echo '<div class="dropdown">';
                     echo '<div class="dropdown-father"><button id="log-button" href="profils.php" onclick="profils_page()" style = "text-transform : uppercase;">
                            <img src="Includes/Images/Icone Logged In.png" style="width : 1.5vw; margin-right: 0.1vw"> <span style = "vertical-align: 10%"> '  .$_SESSION["user_actif"]. '</button></div>';
                     echo '<div class="dropdown-child">
                                <a href="profils.php">Mon Compte</a>';
                    if ($_SESSION["is_admin"]==1) {
                          echo '<a href="administration.php">Administration</a>';}
                    echo '<button id="log_off_button" onclick="log_off_request()">Déconnexion</button>';
                    echo '</div> </div>';
                } else { echo '<button id="log-button" href="profils.php" onclick="profils_page()" style="text-transform: none; display: inline-block; margin-left: 3vw"> Me connecter </button>';}
            ?>
        </div>
    </div>
</header>

<script>

function isRequestSuccessful($xhr) {
        return $xhr.readyState === 4 && $xhr.status === 200;}

function log_off_request()     {
    var xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function() { if (isRequestSuccessful(this)) {
    window.open('index.php', '_self')  ;   } ; }

    xhr.open('POST', '/Includes/PHP/log.php');
    data = new FormData();
    data.append('type_requete', 'log_off');
    xhr.send(data);                                       }


function profils_page() {
    window.open('profils.php', '_self')        }
</script>

<!--     xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); -->
