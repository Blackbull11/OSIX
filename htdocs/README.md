

# A l'attention des développeurs souhaitant travailler sur le projet OSIX.

OSIX a été développé entre Janvier et Avril 2025 par deux stagiaires polytechniciens passionnés et ambitieux.
Il est leur premier projet numérique d'une telle ampleur et leur première prise avec le développement Web (PHP, HTML, CSS, JS).
Cette inexpérience se retrouve dans la structure désordonnée du code, dans des changements réguliers de syntaxe et de logique pour réaliser des tâches très similaires 
et des doublons innombrables. La courte durée de notre de stage ne nous a pas permis de lui refaire une beauté, et nous nous excusons pour les difficultés que cela pourra vous créer. 

OSIX est une plateforme polyvalente qui propose de nombreuses fonctionnalités mais qui pourrait encore être énormément enrichie. Nous vous le laissons entre vos mains, désormais il vous appartient de vous assurer que son fonctionnement et son emploi ne se verront qu'améliorés.

Ci-dessous vous trouverez quelques indications pour vous aider dans cette tâche ardu mais pas insurmontable de décortiquer ce prototype.

## Caractéristiques Générales

Environnement : 

* **Hébergement** : Serveur Apache (v2.4.62, nommé Apache24) installé sur un Windows Serveur IIS tournant sur Windows 12 (égelement utilisé en tant que WSUS pour le moment) (C://Windows/Apache2) (Port : 8067)
* **Emploi** : Navigateur Mozilla sur poste du SIRIN dédiés à la recherche numérique (sous système d'exploitation Windows)
* **Sauvegardes** : service Windows assurant la sauvegarde automatique bijournalière de la BDD sur un serveur interne dédié aux sauvegardes (cf. BSIC du centre). Sauvegardes des documents télécharges ?
* **Code Source** : Base en PHP (v8.3)  compilée par le serveur Apache complétée par du CSS (styles) et du Javascript (commandes dynamiques)
* **Base de données** : Hébergée sur le même serveur Apache sous MySQL (v9.1, port : 3306).
* **DNS** : Association par le serveur DNS du SIRIN entre l'adresse osix.rin:8067 et le chemin d'accès du fichier htdocs dans le serveur Apache24 + configuration du IIS pour avoir le port 8067 par défaut.

Perspectives : 

* **GLORIX+** : Vous trouverez dans le dossier GLORIX+ l'ébauche d'un projet d'AddOn pour embaser efficacement les liens depuis le web. L'idée est simplement de reproduire le formulaire d'ajout de lien dans l'addon en récupérant les variables de session lorsqu'une session est active. Cet AddOn doit être déployés sur les machines et pour cela, nous avons réfléchis à mettre en place une GPO après avoir certifié l'AddOn par d'un Certficat Racine auto-signé. Cela peut être mis en place facilement à l'aide d'openssl mais nécessite l'accord des responsables Cyber du Centre
* **EPIX+** : Une autre idée d'Addon consiste à permettre d'alimenter les graphes EPIX de manière semi-automatiques. Toujours à l'aide d'un Addon, l'idée est de pouvoir récupérer les éléments en les surlignant avec la souris grâce à un popup qui apparait pour définir la catégorie du noeud et sa couleur éventuellement. Nous pourrions penser également à implémenter un modèle permettant de suggérer une caractérisation ; automatiquement voire même de proposer un graphe généré en étudiant la page : le modèle reconnait que Bob est maire de Helsinki et qu'il appartient au groupe du Parti Ouvrierpar exemple.
* **EPIX Intelligent** : afin d'appuyer les analystes dans leurs démarches de recherche, nous pourrions implémenter un modèle de langage qui suggère 3 ou 4 pistes de réflexions à partir d'un noeud lorsqu'on le sélectionne en tenant compte du contexte du graphe et du contenu du noeud.
* **GLORIX x EPIX** : Afin d'avoir une vision d'ensemble sur les sources, nous pourrions générer un projet EPIX à partir de la liste des sources auxquelles je suis abonné en les reliant par tags en communs ou cadres d'emplois par exemple.
* **GLORIX Intelligent** : Afin de faciliter les débuts de recherche, nous pourrions mettre en place un modèle de remplissage facilité des listes à partir des sources et des liens qui correspondent à la description de la liste, en choisissant des tags par exemple. Il s'agit d'avantage d'un outil de confort qu'une réelle fonctionnalité nouvelle.
