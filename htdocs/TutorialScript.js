let currentStep = 0; // Étape actuelle

const steps = [
    {
        title: 'Introduction',
        steps: [
            {
                title: "Bienvenue sur OSIX !",
                message: "Prêts à rentrer dans la révolution de la recherche numérique ? <br><br> Lancez-vous !",
                style :"position: fixed;transform: translate(-50%, -50%);top: 50%;left: 50%;display: block;padding: 2em;color: white;width: 480px;height: 335px;background-image: url(\"Includes/Images/Chouette.jpg\");background-size: 110%;background-position: right bottom;background-repeat: no-repeat;background-color: black;border: none;"
            },
            {
                title: "BOX, le meilleur des couteaux suisses.",
                selector: ".BOX",
                message: "Bienvenue dans le module BOX ! <br><br> BOX vous permet d'ouvrir, enregistrer et rechercher les outils d'aide à la recherche en source ouverte disponibles sur le web. <br> Outils d'analyse d'image, de traduction, d'extraction de métadonnées... sont maintenant à portée de click pour faciliter vos recherches.",
            },
            {
                title: "Outils favoris",
                selector: "#favorite-tools-gallery",
                message: "Personnalisez vos favoris pour accéder à vos meilleurs alliés en un seul click !",
            },
            {
                title: "Mon arborescence",
                selector: "#tools-matrix",
                message: "Laissez-vous guider et trouvez les outils qu'il vous faut.",
            },
            {
                title: "Gestionnaire BOX",
                selector: "#box-button",
                message: "Pour plus de détails et fonctionnalités sur BOX, accédez au gestionnaire depuis ce bouton",
            },
            {
                title: "GLORIX, le catalogue de sources 2.0.",
                selector: ".GLORIX",
                message: "Bienvenue dans GLORIX ! Ici, vous gérez vos sources et les liens des pages que vous consultez.",
            },
            {
                title: "Liens favoris, mes recherches à pleine vitesse.",
                selector: "#generic-sources-gallery",
                message: "Ajoutez des raccourcis pour ouvrir facilement vos liens favoris. Liés les à une source si possible pour améliorer le suivi de vos sources.",
            },
            {
                title: "Mes sources, à suivre attentivement",
                selector: "#sources-matrix",
                message: "Vos sources sont les gisements d'informations qui vous intéresse et qu'il vous est utile de suivre (comptes sur les réseaux sociaux, journaux, sites institutionnels...) <br> Caractériser les biens (types, tags, description), il est utile de pouvoir les retrouver, pour vous ou pour les autres.",
            },
            {
                title: "Mes listes de liens, pour ne rien perdre",
                selector: "#links-matrix",
                message: "Faites vivre vos sources en enregistrant les liens des pages que vous consultez et n'oubliez plus aucun lien. <br> Fini les recerches pour retrouver une page, il suffit de chercher dans la bonne liste.",
            },
            {
                title: "EPIX, pour n'oublier aucune information.",
                selector: ".EPIX",
                message: "Bienvenue dans EPIX ! Ici, vous créez et collaborez sur des projets.",
            },
            {
                title: "Mes projets, travaux en cours.",
                selector: ".EPIX-project",
                message: "Cliquez sur un de ces projets de démo pour explorer EPIX en détail.",
            }
        ],
    },
];


const linearSteps = [];
const titles = [];

function generateTutorialMenu() {
    const menuContent = document.querySelector('.tutorial-menu-content'); // Sélecteur du menu dans le DOM
    menuContent.innerHTML = ''; // Réinitialisation avant la génération

    steps.forEach((section, sectionIndex) => {
        // Création d'un conteneur pour la section
        const sectionDiv = document.createElement('div');
        sectionDiv.style.marginTop = '20px';
        sectionDiv.className = 'tutorial-section';
        const firstStepIndex = steps.slice(0, sectionIndex).reduce((acc, s) => acc + s.steps.length, 0); // Calculer l'index global de la première étape de la section
        const step = linearSteps[currentStep];
        sectionDiv.innerHTML = `<a onclick="goToStep(${firstStepIndex}, '${step.selector}')">${section.title}</a>`; // Lien clicable pour la section
        menuContent.appendChild(sectionDiv);



        // Créer la liste des étapes dans la section, si la section est active
        if (titles[currentStep] === section.title) {
            sectionDiv.style.fontWeight = 'bold';
            sectionDiv.style.fontSize = 'x-large';
            const stepsList = document.createElement('ul');

            section.steps.forEach((step, stepIndex) => {
                const stepLi = document.createElement('li');
                stepLi.style.marginTop = '5px';
                stepLi.className = `tutorial-step ${linearSteps[currentStep] === step ? "tutorial-step-active" : ""}`;
                const prevStep = linearSteps[currentStep];
                stepLi.innerHTML = `<a onclick="goToStep(${stepIndex}, '${prevStep.selector}')">${step.title}</a>`; // Lien clicable pour l'étape
                stepsList.appendChild(stepLi);
            });

            menuContent.appendChild(stepsList);
        }
    });
}

function generateTutorialMenu2() {
    const menuContent = document.querySelector('.tutorial-menu-content');
    menuContent.innerHTML = ''; // Vider le contenu actuel

    steps.forEach((section, sectionIndex) => {
        // Ajouter une section
        const sectionDiv = document.createElement('div');
        sectionDiv.className = 'tutorial-section';
        sectionDiv.innerHTML = `<strong>${section.title}</strong>`;
        menuContent.appendChild(sectionDiv);

        // Ajouter les étapes de la section
        const stepsList = document.createElement('ul');
        section.steps.forEach((step, stepIndex) => {
            const stepLi = document.createElement('li');
            stepLi.className = 'tutorial-step';
            stepLi.innerHTML = `<a href="javascript:void(0)">${step.title}</a>`;
            stepsList.appendChild(stepLi);
        });
        menuContent.appendChild(stepsList);
    });
}

function GenLinearSteps() {
    // Parcourir les sections et les étapes
    i = 0;
    steps.forEach((section) => {
        section.steps.forEach((step) => {
            // Ajouter l'étape directement dans le tableau linéaire à l'indice `id`
            linearSteps[i] = step;

            // Associer l'identifiant de l'étape au titre de la section principale
            titles[i] = section.title;
            i++;
        });
    });
}

function startTutorial() {
    currentStep = 0;
    GenLinearSteps();
    generateTutorialMenu();
    showStep();
    document.querySelector(".tutorial-popup").style.display = "block";
    document.querySelector(".tutorial-overlay").style.display = "block"; // Afficher l'obscurcissement
}

function showStep() {
    const step = linearSteps[currentStep];
    if (!step) {
        endTutorial();
        return;
    }
    if (step.script) {
        eval(step.script);
    }
    if (step.selector) {
        const element = document.querySelector(step.selector);
        if (element) {
            const popup = document.querySelector(".tutorial-popup");
            const rect = element.getBoundingClientRect(); // Obtenir les dimensions et la position de l'élément
            const viewportWidth = window.innerWidth;

            // Calcul de la position du popup
            const popupWidth = Math.min(35 * viewportWidth / 100, 400); // Limiter la taille max à 30vw (par défaut 300px si écran plus petit)
            const popupPadding = 10; // Marges autour du contenu
            const margin = 10; // Espace entre le popup et l'élément

            // Calcul de la position cible pour aligner l’élément avec une marge de 15px
            const targetPosition = rect.top + window.scrollY - margin;

            // Défilement fluide vers la position de l’élément
            window.scrollTo({
                top: targetPosition + 100, // Position ajustée au bord supérieur avec la marge de 15px
                behavior: "smooth", // Défilement animé
            });
            let left = rect.right + margin + 10; // Par défaut, à droite
            let popupPosition = "right";   // Par défaut, triangle orienté vers la gauche (popup à droite)

            // Basculer à gauche si pas assez d'espace à droite
            if (left + popupWidth > viewportWidth) {
                left = rect.left - popupWidth - margin - 8;
                popupPosition = "left"; // Triangle orienté vers la droite (popup à gauche)
            }

            popup.style.cssText = "";
            // Appliquer la position calculée au popup
            popup.style.position = "absolute";
            popup.style.width = `${popupWidth}px`;
            popup.style.top = `${rect.top + window.scrollY}px`; // Aligner le haut du popup avec le haut de l'élément
            popup.style.left = `${left}px`;
            popup.style.display = "block";
            popup.setAttribute("data-position", popupPosition); // Indiquer la position via un attribut

            // Ajouter le contenu du popup avec triangle
            popup.innerHTML = `
                <div class="tutorial-popup-content">
                    <h4 style ="color : #ff8787; text-align: center; font-size : large; margin-top: 0">${step.title}</h4>
                    <span style="text-align: justify">${step.message}</span><br>
                    <div style="display: flex; justify-content: space-between; margin-top : 15px">
                        <button onclick="goToStep(${parseInt(currentStep) - 1}, '${step.selector}')" class="previous-button">Précédent</button>
                        <button onclick="goToStep(${parseInt(currentStep) + 1}, '${step.selector}')" class="next-button">Suivant</button>
                    </div>
                    
                    <button class="quit-tutorial-btn" onclick="endTutorial()">Quitter le tutoriel</button>
                </div>
                <div class="tutorial-popup-triangle"></div>
            `;

            // Mettre l'élément sélectionné en surbrillance
            element.style.zIndex = 1000;

            element.style.backgroundColor = 'white';
            element.style.borderRadius = '20px';
            element.scrollIntoView({behavior: "smooth", block: "center"});
            element.style.position = "sticky";
            if (element.id == "box-button" || element.id == "glorix-button" || element.id == "epix-button"){
                element.style.backgroundColor = 'transparent'
            }
        }
    }
    else {
        // Ce cas ne correspond que à la première étape
        const popup = document.querySelector(".tutorial-popup");
        popup.style.position = "fixed";
        popup.style.transform = "translate(-50%, -50%)";
        popup.style.top = "50%";
        popup.style.left = "50%";
        popup.style.width = "35vw";
        popup.style.display = "block";
        popup.innerHTML = `
                <div class="tutorial-popup-content">
                    <h4 style ="color : #ff8787; font-family : 'math'; font-size: xx-large; ; text-align: center; width: 410px; margin-top: 0; margin-bottom: 40px">${step.title}</h4>
                    <span style="position:relative; font-family : 'math'; right : -230px; display: block; width: 190px; text-align: center; font-size: larger; margin-bottom: 30px">${step.message}</span><br>
                    <div style="display: flex; justify-content: space-between; width: 410px; vertical-align: bottom">                    
                        <button onclick="endTutorial()" class="skip-button">Passer</button>
                        <button onclick="goToStep(${parseInt(currentStep) + 1})" class="next-button" style="    font-weight: bold;
    text-transform: uppercase; font-family: math; background-color: rgba(255,135,135,0.76); position : relative; font-size: larger;">▶ C'est parti</button>
                    </div>
                    
                </div>
            `;
        if (step.style) {
            popup.style.cssText += step.style;
        }
    }
}


function goToStep(stepIndex, element) {
    const oldElement = document.querySelector(element);
    console.log(oldElement);
    if (oldElement) {
        oldElement.style.zIndex = 'unset';
        oldElement.style.backgroundColor = 'unset';
        oldElement.style.borderRadius = '0';
        oldElement.style.position = "inherit";

    }

    currentStep = stepIndex;
    showStep();
    document.querySelector(".tutorial-menu").style.display = "block";
    if (stepIndex == 1) {
        console.log(document.querySelector(".content"));
        document.querySelector(".content").classList.toggle('content-in-tutorial');
    }
    generateTutorialMenu();
    // Naviguer vers l'élément associé
    const targetElement = document.querySelector(linearSteps[currentStep]?.selector);
    if (targetElement) {
        targetElement.scrollIntoView({behavior: 'smooth', block: 'start'});
    }
    GenLinearSteps();
}

function endTutorial() {
    document.querySelector(".tutorial-overlay").style.display = "none";
    if (document.querySelector(".quit-tutorial-btn")) {
        document.querySelector(".quit-tutorial-btn").style.display = "none";

    }
    document.querySelector(".tutorial-popup").style.display = "none";
    document.querySelector(".tutorial-menu").style.display = "none";
    document.querySelector(".content").classList.remove = "content-in-tutorial";
    window.location.href = "index.php"; // Quitter vers la page d'accueil
}

// Fonction pour afficher ou cacher le menu
function toggleTutorialMenu() {
    const tutorialMenu = document.querySelector('.tutorial-menu');
    if (tutorialMenu.classList.contains('tutorial-menu-open')) {
        tutorialMenu.classList.remove('tutorial-menu-open'); // Masquer le menu
        document.querySelector('.tutorial-liseret').style.backgroundColor = "#ff8787"; // Réinitialiser le liseret
    } else {
        tutorialMenu.classList.add('tutorial-menu-open'); // Afficher le menu
        document.querySelector('.tutorial-liseret').style.backgroundColor = "#ff4d4d"; // Indiquer le menu ouvert via le liseret
    }
}


