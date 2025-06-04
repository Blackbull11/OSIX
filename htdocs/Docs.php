<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none';">
    <title>Documentation</title>
    <link rel="stylesheet" href="style/HeaderStyle.css">
    <link rel="stylesheet" href="style/FooterStyle.css">
    <link rel="stylesheet" href="style/IndexStyle.css">
    <link rel="stylesheet" href="style/DocsStyle.css">
</head>
<body>
    <?php require_once 'Includes/PHP/header.php'; ?>
    <div class="content">
        <div>
            <div class="doc-container">
                <div class="doc-image">
                    <img class="doc-image" src="Includes/DOC_Images/Memento%20Utilisateurs.PNG" alt="Memento Utilisateurs">
                </div>
                <hr>
                <div class="doc-content">
                    <h2>Mémento Utilsateur</h2>
                    <p>A garder toujours près de soi pour s'orienter sur la plateforme.</p>
                    <button class="pdf-button"><a href="Documentation/Memento%20uUtilisateurs.pdf" target="_blank">📄 PDF</a></button>
                    <button class="ppt-button"><a href="Documentation/Memento%20Utilisateurs.docx" target="_blank">📝 PPT</a></button>
                </div>
            </div>
            <div class="doc-container">
                <div class="doc-content">
                    <h2 style="text-align: right">Manuel Utilisateur</h2>
                    <p  style="text-align: right">Tout ce qu'il faut savoir pour employer <br>au mieux la plateform :</p>
                    <div style="display: flex; justify-content: right;">
                        <button  class="pdf-button"><a href="Documentation/Fiche%20Utilisateur.pdf" target="_blank">📄 PDF</a></button>
                        <button class="ppt-button"><a href="Documentation/Fiche%20Utilisateur.docx" target="_blank">📝 WORD</a></button>
                    </div>
                </div>
                <hr>
                <div class="doc-image">
                    <img class="doc-image" src="Includes/DOC_Images/Fiche%20Utilsateur.PNG" alt="Memento Utilisateurs">
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'Includes/PHP/footer.php'; ?>
</body>
</html>