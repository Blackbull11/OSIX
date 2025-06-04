<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crédits de la Plateforme</title>
    <link rel="icon" href="Includes\Images\Icone Onglet.png" />
    <link rel="stylesheet" href="STYLE\IndexStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexPopupStyle.css"/>
    <link rel="stylesheet" href="STYLE\HeaderStyle.css"/>
    <link rel="stylesheet" href="STYLE\IndexEPIXStyle.css"/>
    <link rel="stylesheet" href="STYLE\FooterStyle.css"/>
    <link rel="stylesheet" href="STYLE\TutorialStyle.css"/>
</head>
<body>
    <?php require_once 'Includes\PHP\header.php'; ?>

    <main style="padding : 5%; font-family: 'Courier New';">
        <a href="Docs.php" style="text-decoration: underline; float : right; margin-right: 10vw; font-style: italic; color: black">📄 Voir la documentation</a>

        <h1 style="font-weight: bold; font-family: 'Courier New'">Crédits de la Plateforme</h1>

        <section></section>

        <section>
            <h2 style="font-weight: bold">Développeurs</h2>
            <strong>Cette plateforme a été développée de zéro par les stagiaires polytechniciens X24 du centre et publiée en Avril 2025.</strong>
            <br><i>Cette fois-ci, les X n'ont pas fait qu'un tableau Excel mais bien </i>
        </section>

        <section>
            <h2 style="font-weight: bold">Partenaires</h2>
            <h4>Cette plateforme a vu le jour grâce au soutien des partenaires suivants :</h4>
            <ul style="list-style-type: square; padding-left :25px">
                <li class="squared-before" style="margin-bottom : 15px"><strong>Responsable Innovation</strong> - Conduite du projet</li>
                <li class="squared-before" style="margin-bottom : 15px"><strong>Chef de bureau</strong> - Orientation des besoins et des outils</li>
                <li class="squared-before" style="margin-bottom : 15px"><strong>Cellule ROC</strong> - Orientation du développement et entretien de la plateforme</li>
                <li class="squared-before" style="margin-bottom : 15px"><strong>Bureau Système Informatique</strong> - Fourniture de ressources, mise en place réseau et contrôles de sécurité</li>
            </ul>
        </section>

        <section>
            <h2>Technologies utilisées</h2>
            <ul style="list-style-type: square; padding-left : 25px">
                <li class="squared-before" style="margin-bottom : 15px"><strong>PHP</strong> et <strong>MySQL</strong> - Code côté serveur et gestion de la base de données</li>
                <li class="squared-before" style="margin-bottom : 15px"><strong>HTML5</strong>, <strong>CSS3</strong>, <strong>JavaScript</strong> - Interface utilisateur</li>
                <li class="squared-before" style="margin-bottom : 15px"><strong>PHP Storm</strong> - IDE de développement</li>
                <li class="squared-before" style="margin-bottom : 15px"><strong>WampServer</strong> - Emulation de serveur web pour le développement</li>
                <li class="squared-before" style="margin-bottom : 15px"><strong>PowerShell</strong> - Déploiemennt sur l'intranet</li>
                <li class="squared-before" style="margin-bottom : 15px"><strong>OPENSSL</strong> - Certification du site en HTTPS et de l'AddOn Mozilla sur l'intranet</li>
                <li class="squared-before" style="margin-bottom : 15px"><i>Toutes les commandes utilisées sont natives aux langages employés et aucun appel à des bibliothèques ou services extérieures n'a été fait (NodeJS n'est pas employé par exemple)</i></li>
            </ul>
        </section>

        <section>
            <h2>Avertissement</h2>
                Cet outil n'est encore qu'un prototype et ne sera pas suivi au cours de son développement par ses développeurs initiaux.
                Nous invitons tout développeur voulant se plonger dans ce code à s'assurer de posséder une sauvegarde de cette version avant de faire des modifications et à se procurer le README.md contenant des spécificités techniques.
                OSIX possède encore un grand potentiel d'amélioration, merci de partciiper à son développement et à sa consolidification. L'apport de l'intégration de l'IA dans GLORIX et EPIX est inestimable (reconnaissance de liens pour caractérisation et classification automatique, pistes d'exploration de graphes suggérées par IA, rcupération de données sur un epage web pour embasement automatisé dans EPIX par IA... ).
        </section>
    </main>

    <?php require_once 'Includes\PHP\footer.php'; ?>
</body>
</html>