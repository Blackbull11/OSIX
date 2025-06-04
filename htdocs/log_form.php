<?php

if (!isset($_SESSION['user_actif'])) {

echo '
<div style="display: flex; justify-content: space-between;">
 <div style="width: 40vw;margin-left: 5vw; border: 2px solid black">        <!-- formulaire d identification -->
        <fieldset>
            <legend>Vous connecter à un profil existant</legend>
            <p>
                <label>Login :</label>
                <input class="log-input" type="text" id="identifiant" style="margin-left: 77px"/>
            </p>
            <p>
                <label>Mot de passe :</label>
                <input class="log-input" type="password" id="mot_de_passe" style="margin-left: 20px"/>
            </p>
            <div style="display: flex; justify-content: left; gap : 10vw ; margin-top: 20px">
                <p style="margin-top : 15px">
                    Mot de passe oublié ? Les admins peuvent <br> le récupérer, demandez-leur !
                </p>
                <button type="submit" onclick="log_in_request()" class="log-button new-button">Identification</button>
            </div>
        </fieldset>
    </form> ';

if (isset($_SESSION['login_status'])) {echo '<p style="margin-left: 2vw">'.$_SESSION['login_status'].'</p>';}
else {echo '<p style="margin-left: 2vw">Veuillez vous identifier.</p>';};
echo '</div>';



echo '
 <div style="width: 40vw;margin-right: 5vw; border: 2px solid black">        <!-- formulaire de création d\'un profil -->
        <fieldset>
            <legend style="text-transform: uppercase; margin : 5px">Créer un nouveau profil</legend>
            <p>
                <label>Login :</label>
                <input class="log-input" type="text" id="nouvel_identifiant" style="margin-left : 20px"/>
            </p>
            <p style="text-transform: uppercase; font-weight: bold; font-size: 12px; color : red; width: 440px">
                Attention, ce mot de passe est visible par les administrateurs de la plateforme. Utilisez un mot de passe dédié.
            </p>
            <p>
                <label>Mot de passe :</label>
                <input class="log-input" type="password" id="nouveau_mot_de_passe"  style="margin-left : 137px"/>
            </p>
            <p>
                <label>Confirmation du mot de passe :</label>
                <input class="log-input" type="password" id="confirmation"  style="margin-left: 20px"/>
            </p>
            <div style="display: flex; justify-content: right; padding-right: 2vw">
            <button type="submit" onclick="new_account_request()" class="new-button log-button">Créer le profil</>
        </fieldset>';
    if (isset($_SESSION['new_login_status'])) {echo '<p style="margin-left: 2vw">'.$_SESSION['new_login_status'].'</p>';};
echo '</div> </div>';

}

elseif ($_SESSION['is_admin']==0) {
echo '
<div style="width: 40vw;margin-right: 5vw; margin-left: 5vw; border: 2px solid black">        <!-- formulaire de création d\'un profil -->
    <fieldset>
        <legend>Je veux devenir administrateur.</legend>
        <p>
            <label>Clef administrateur :</label>
            <input class="log-input" type="password" id="clef_admin"/>
            <button type="submit" onclick="admin_request()" style="border: 2px solid black">Soumettre</>
        </p>
    </fieldset>';
if (isset($_SESSION['is_admin_status'])) {echo '<p style="margin-left: 2vw">'.$_SESSION['is_admin_status'].'</p>';};
echo '</div>';
};
?>

<script>
    function new_account_request() {

        var ni = document.getElementById('nouvel_identifiant') ;
        var nm = document.getElementById('nouveau_mot_de_passe') ;
        var c = document.getElementById('confirmation') ;

        var nouvel_identifiant = ni.value ;
        var nouveau_mot_de_passe = nm.value ;
        var confirmation = c.value ;

        xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() { if (isRequestSuccessful(this)) {
            console.log(this.responseText) ;
            window.location.href = this.responseText  ;
        } ; }

        xhr.open('POST', 'Includes/PHP/log.php');
        data = new FormData();
        data.append("type_requete", "creation_compte");
        data.append("nouvel_identifiant", nouvel_identifiant);
        data.append("nouveau_mot_de_passe", nouveau_mot_de_passe);
        data.append("confirmation", confirmation);
        xhr.send(data);                          };

    function log_in_request() {

        var login = document.getElementById('identifiant') ;
        var mdp = document.getElementById('mot_de_passe') ;

        var identifiant = login.value ;
        var mot_de_passe = mdp.value ;

        xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() { if (isRequestSuccessful(this)) {
            window.open(this.responseText, '_self')  ;   } ; }

        xhr.open('POST', 'Includes/PHP/log.php');
        data = new FormData();
        data.append("type_requete", "log_in");
        data.append("identifiant", identifiant);
        data.append("mot_de_passe", mot_de_passe);
        xhr.send(data);                          };

    function admin_request() {

        var c = document.getElementById('clef_admin') ;
        var clef = c.value ;

        xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() { if (isRequestSuccessful(this)) {
            window.open(this.responseText, '_self')  ;   } ; }
        xhr.open('POST', 'Includes/PHP/log.php');
        data = new FormData();
        data.append("type_requete", "admin_login");
        data.append("clef", clef);
        xhr.send(data);                          };

</script>
