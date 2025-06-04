<?php
try {
    // On se connecte à la bdd
    $bdd = new PDO('mysql:host=localhost;dbname=osix;port=3367', 'usersauv', 'S@uvBaseosix25*');
} catch (Exception $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur : ' . $e->getMessage());
}
// On récupère les tableaux, ces variables sont des tables de la bdd (tableau de tableau en php)
$users = $bdd->query("SELECT * FROM users")->fetchAll();
$tools = $bdd->query("SELECT * FROM tools")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACtualités OSIX</title>
    <link rel="icon" href="Includes\Images\Icone Onglet.png" />
    <link rel="stylesheet" href="STYLE\IndexStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexPopupStyle.css"/>
    <link rel="stylesheet" href="STYLE\HeaderStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexEPIXStyle.css"/>
    <link rel="stylesheet" href="STYLE\FooterStyle.css"/>
    <link rel="stylesheet" href="STYLE\NewsStyle.css"/>
	<link rel="stylesheet" href="STYLE\TutorialStyle.css"/>

</head>


<body>

<?php require_once 'Includes/PHP/header.php';
$user_id = 0;
$user_admin = 0;
if (isset($_SESSION['id_actif'])) {
    $user_id = $_SESSION['id_actif'];
    $user_admin = ($_SESSION['is_admin'] == 1) ? 1 : 0;
}

function count_user_messages_typed($bdd, $type) {

    // Exemple de récupération des données de la base (news et utilisateur connecté)
    $req = "SELECT * FROM news WHERE type = :type";
    $stmt = $bdd->prepare($req);
    $stmt->execute([':type' => $type]);
    $news = $stmt->fetchAll();
    $user_id = isset($_SESSION['id_actif']) ? $_SESSION['id_actif'] : 0;

    // Nombre de messages destinés à l'utilisateur
    return count_user_messages($news, $user_id);

}
function count_new_messages($bdd) {
    // Exemple de récupération des données de la base (news et utilisateur connecté)
    $news = $bdd->query("SELECT * FROM news")->fetchAll();
    $user_id = isset($_SESSION['id_actif']) ? $_SESSION['id_actif'] : 0;
    // Nombre de messages destinés à l'utilisateur
    return count_user_messages($news, $user_id);
}
?>

<div id="main-content">
    <div id="news-titles">
            <!-- Menu de navigation principal -->
            <div class="tab-container" style="margin: 0 1%;">
                <!-- Onglets communs pour tous les utilisateurs -->
                <?php if ($user_admin) {
                    echo '<button class="tab-button" onclick="openTab(this, \'adminMessaging\')" id="adminMessagingButton">Messages Admin</button>';
                    if (count_user_messages_typed($bdd, "ADMIN") > 0) {
                        echo '<span class="notification-badge">'.count_user_messages_typed($bdd, "ADMIN").'</span>';
                    }
                }?>
                <button class="tab-button" onclick="openTab(this, 'recentMessaging')" id="recentMessagingButton">Fil d'actualité</button>
                <?php if (count_new_messages($bdd) > 0): ?>
                    <span class="notification-badge"><?= count_new_messages($bdd) ?></span>
                <?php endif; ?>
                <?php if ($user_id){?>
                    <button class="tab-button" onclick="openTab(this, 'boxMessaging')">BOX
                    </button>
                    <?php if (count_user_messages_typed($bdd,"BOX") > 0): ?>
                        <span class="notification-badge"><?= count_user_messages_typed($bdd,"BOX") ?></span>
                    <?php endif; ?>
                    <button class="tab-button" onclick="openTab(this, 'glorixMessaging')">GLORIX</button>
                    <?php if (count_user_messages_typed($bdd,"GLORIX") > 0): ?>
                        <span class="notification-badge"><?= count_user_messages_typed($bdd,"GLORIX") ?></span>
                    <?php endif; ?>
                    <button class="tab-button" onclick="openTab(this, 'epixMessaging')">EPIX</button>
                    <?php if (count_user_messages_typed($bdd,"EPIX") > 0): ?>
                        <span class="notification-badge"><?= count_user_messages_typed($bdd,"EPIX") ?></span>
                    <?php endif; ?>
                    <button class="tab-button" onclick="openTab(this, 'ownMessaging')">Messages directs</button>
                    <?php if (count_user_messages_typed($bdd,"PRIVATE") > 0): ?>
                        <span class="notification-badge"><?= count_user_messages_typed($bdd,"PRIVATE") ?></span>
                    <?php endif; ?>
                <?php } ?>
            </div>

            <!-- Contenu des onglets -->
            <div id="adminMessaging" class="messaging tab-content hidden"></div>

            <div id="recentMessaging" class="messaging tab-content hidden" >
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
                                    <span>Par : Vos preux serviteurs de la X24!</span><br>
                                    <span>À : Tous les analystes innovateurs du centre</span>
                                </div>
                                <hr>
                                <div class="message-content" style="display: flex; justify-content: center; align-items: center; width: 500px; text-align: center">
                                    <a href="profils.php" style="color : black; font-style: italic">Je crée mon compte sans plus attendre</a>
                                </div>
                            </div>
                        </div>
                </div>
            </div>

            <div id="boxMessaging" class="messaging tab-content hidden"></div>

            <div id="glorixMessaging" class="messaging tab-content hidden"></div>

            <div id="epixMessaging" class="messaging tab-content hidden"></div>

            <div id="ownMessaging" class="messaging tab-content hidden">
                <h3>Voir tous les messages me concernant.</h3>
                <div class="tab-container">
                    <button class="tab-button" onclick="openTab(this, 'message')">Messages reçus</button>
                    <button class="tab-button" onclick="openTab(this, 'message')">Messages envoyés</button>
                </div>
                <div id="epix" class="tab-content hidden">
                    <h3>Messages reçus</h3>

                </div>
                <div id="epix" class="tab-content hidden">
                    <h3>Messages envoyés</h3>

                </div>
            </div>

        <!-- Modal -->
        <div id="writeModal" class="mailModal">
            <div class="mailModal-content">
                <span class="close" id="closeModalButton" onclick="closeMailModal()">&times;</span>

                <h2>Envoyer un message</h2>

                <!-- Formulaire pour envoyer un message -->
                    <!-- Sélecteur pour le destinataire -->
                <div id="recipientSelect" class="recipientSelect" style="display: flex">
                    <label for="userRecipientSelect">Ajouter un destinataire</label>
                    <select id="userRecipientSelect" name="userRecipientSelect">
                        <option id="-1">Sélectionner un destinataire</option>
                    </select>
                    <br><br>
                    <label for="groupRecipientSelect">Ajouter un groupe</label>
                    <select id="groupRecipientSelect" name="groupRecipientSelect">
                        <option id="-1">Sélectionner un groupe</option>
                    </select>
                </div>

                <div id="userSelectedRecipients"></div>
                <div id="groupSelectedRecipients"></div>

                <!-- Zone de texte pour le message -->
                    <label for="message">Corps du message</label>
                    <textarea id="message" name="message" rows="4" cols="50"></textarea>
                    <br><br>

                    <!-- Bouton d'envoi -->
                    <button id="submitMailButton" onclick="submitMailModal()" class="export-btn">Envoyer</button>
                    <button id="submitMailButton" onclick="closeMailModal()" style="background-color: darkred" class="export-btn">Annuler</button>
            </div>
        </div>

        </div>
    </div>
</div>


<script>
    function loadMails(keyWord)
    {
        const data = new FormData();
        data.append('action', 'loadMails');
        if (keyWord === 'ownMessaging')
            {
            data.append('keyWord',
                (document.getElementById('ownMessagingChangeButton') && (document.getElementById('ownMessagingChangeButton').textContent === 'Messages envoyés'))
                ? 'ownMessagingSent'
                : 'ownMessagingReceived'
            );
            }
        else {
            data.append('keyWord', keyWord);
            }
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'Includes/PHP/mails.php');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    document.getElementById(keyWord).innerHTML = xhr.responseText;
                    console.log(document.getElementById(keyWord));
                }
            }
        }
        console.log('load Mails xhr : ', data);
        xhr.send(data);
    }

    // Pour charger les messages du fil d'actualité à l'ouverture
    addEventListener("DOMContentLoaded", function () {
        openTab(document.getElementById("recentMessagingButton"), "recentMessaging");
    });

    function updateRecipientsDisplay() {
        const userSelectedRecipientsDiv = document.getElementById("userSelectedRecipients");
        const groupSelectedRecipientsDiv = document.getElementById("groupSelectedRecipients");

        userSelectedRecipientsDiv.innerHTML = '';
        groupSelectedRecipientsDiv.innerHTML = '';

        userSelectedRecipients.forEach((item) => {
            const mail = item.mail;
            const num = item.identifiant
            const span = document.createElement("span");
            span.textContent = mail;
            span.classList.add("recipient-tag");

            const removeBtn = document.createElement("button");
            removeBtn.textContent = "X";
            removeBtn.onclick = function () {
                userSelectedRecipients.delete(item);
                selectedUsers.delete(parseInt(num));
                console.log('ok delete : ', selectedUsers);
                updateRecipientsDisplay();
            }
            span.appendChild(removeBtn);
            userSelectedRecipientsDiv.appendChild(span);
            console.log("liste des id users : ", selectedUsers);
        });

        groupSelectedRecipients.forEach((item) => {
            const mail = item.mail;
            const num = item.identifiant
            const span = document.createElement("span");
            span.textContent = mail;
            span.classList.add("recipient-tag");

            const removeBtn = document.createElement("button");
            removeBtn.textContent = "X";
            removeBtn.onclick = function () {
                groupSelectedRecipients.delete(item);
                selectedGroups.delete(parseInt(num));
                updateRecipientsDisplay();
            }
            span.appendChild(removeBtn);
            console.log(mail, ' dans ', groupSelectedRecipients);
            groupSelectedRecipientsDiv.appendChild(span);
            console.log("liste des id groups : ", selectedGroups);
        });
    }

    // Pour afficher un onglet correspondant à un bouton.
    function openTab(button, tabId)
    {
        const tabs = document.querySelectorAll('.tab-content');
        const buttons = document.querySelectorAll('.tab-button');

        tabs.forEach(tab => tab.classList.remove('show'));
        buttons.forEach(btn => btn.classList.remove('active'));

        button.classList.add('active');
        document.getElementById(tabId).classList.add('show');

        loadMails(tabId);
    }

    //Pour dérouler ou refermer le corps d'un message avec un click.
    document.addEventListener('click', (e) => {
        // Vérifie si l'élément cliqué ou l'un de ses ancêtres possède la classe 'collapsible-news'
        const button = e.target.closest('.collapsible-news');

        if (button) {
            button.classList.toggle('active');
            const content = button.nextElementSibling;

            content.style.display = content.style.display === 'flex' ? 'none' : 'flex';

            if (button.style.backgroundColor === 'cornflowerblue') {
                let match = button.id.match(/^showMailButton_(\d+)$/);
                if (match)
                {
                    xhr = new XMLHttpRequest();
                    data = new FormData();
                    data.append('action', 'viewedMail');
                    data.append('mailId', parseInt(match[1]));
                    xhr.open('POST', 'Includes/PHP/mails.php');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4) {
                            if (xhr.status == 200) {
                                button.style.backgroundColor = 'white';
                                button.style.fontWeight = '';
                                console.log(xhr.responseText);
                            }
                        }
                    }
                    xhr.send(data);
                }
            }
        }
    });

    function writeModal() {
        document.getElementById('writeModal').style.display='block';
        let data1 = new FormData();
        data1.append('action', 'loadUsers');
        var xhr1 = new XMLHttpRequest();
        xhr1.open('POST', 'Includes/PHP/mails.php');
        xhr1.onreadystatechange = function() {
            if (xhr1.readyState == 4) {
                if (xhr1.status == 200) {
                    console.log(this.responseText, " ERJMLFJMFLJ");
                    document.getElementById('userRecipientSelect').innerHTML = xhr1.responseText;
                    updateRecipientsDisplay();
                }
            }
        }
        xhr1.send(data1);

        let data2 = new FormData();
        data2.append('action', 'loadGroups');
        var xhr2 = new XMLHttpRequest();
        xhr2.open('POST', 'Includes/PHP/mails.php');
        xhr2.onreadystatechange = function() {
            if (xhr2.readyState == 4) {
                if (xhr2.status == 200) {
                    console.log(xhr2.responseText);
                    document.getElementById('groupRecipientSelect').innerHTML = xhr2.responseText;
                    updateRecipientsDisplay();
                }
            }
        }
        xhr2.send(data2);
    }

    function closeMailModal() {
        document.getElementById('writeModal').style.display='none'
    }

    function submitMailModal() {
        if (selectedGroups.size !== 0 || selectedUsers.size !==0) {
            console.log('submitMailModal');
            let data = new FormData();
            data.append('action', 'submitMail');
            data.append('mailContent', document.getElementById('message').value);
            data.append('groupSelectedRecipients', JSON.stringify([...selectedGroups]));
            data.append('userSelectedRecipients', JSON.stringify([...selectedUsers]));
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'Includes/PHP/mails.php');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        alert('Votre message a bien été envoyé');
                        closeMailModal();
                    } else {
                        alert("Votre message n'a pas pu être envoyé");
                    }
                }
            }
            xhr.send(data);
        }
        else {
            alert('Veuillez sélectionner un destinataire ou un groupe de destinataires.')
        }

    }

    function adminMailStatus(chckd, id) {
            let data = new FormData();
            data.append('action', 'adminMailTreated');
            data.append('mailId', parseInt(id));
            data.append('newStatus', chckd.checked ? 1 : 0);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'Includes/PHP/mails.php');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (!(xhr.status == 200 && xhr.responseText==='ok')) {
                        alert('Modification non enregistrée.');
                        openTab(document.getElementById('adminMessagingButton'), 'adminMessaging');
                    }
                }
        }
        xhr.send(data);
    }

    let userSelectedRecipients = new Set();
    let groupSelectedRecipients = new Set();
    let selectedGroups = new Set();
    let selectedUsers = new Set();

    const userRecipientSelect = document.getElementById('userRecipientSelect');
    const groupRecipientSelect = document.getElementById('groupRecipientSelect');

    userRecipientSelect.addEventListener("change", function () {
        let emailId = userRecipientSelect.value;
        let email = userRecipientSelect.options[userRecipientSelect.selectedIndex].text;
        if (email && emailId && !userSelectedRecipients.has({mail: email, identifiant: emailId}) && (emailId != -1)) {
            userSelectedRecipients.add({ mail: email, identifiant: emailId});
            selectedUsers.add(parseInt(emailId));
            updateRecipientsDisplay();
        }
    });

    groupRecipientSelect.addEventListener("change", function () {
        let groupId = groupRecipientSelect.value;
        let groupName = groupRecipientSelect.options[groupRecipientSelect.selectedIndex].text;
        if (groupName && groupId && !groupSelectedRecipients.has({mail: groupName, identifiant: groupId}) && (groupId != -1))
        {
            groupSelectedRecipients.add({mail: groupName, identifiant: groupId});
            selectedGroups.add(parseInt(groupId));
            updateRecipientsDisplay();
        }
    });



</script>
<div class="tutorial-overlay"></div>
<div class="tutorial-popup"></div>
<div class="tutorial-menu">
    <button class="tutorial-toggle-btn" onclick="toggleTutorialMenu()">☰ Sommaire</button>

    <!-- Liseret visuel -->
    <div class="tutorial-liseret"></div>
    <div class="tutorial-menu-content">
        <!-- Le contenu est généré dynamiquement par Javascript -->
    </div>
</div>    <script src="TutorialScript.js"></script>
<?php require_once 'Includes/PHP/footer.php';?>

</body>
</html>