
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage Connect</title>
    <link rel="stylesheet" href="../page_pilote/styles_page_pilote.css">
    <link rel="stylesheet" href="styles_page_etudiant.css"></head>
<body>

<?php

include 'navbar-etudiant.php';
?>
<main class="content-principal">
    <div class="back-button">
        <a href="page_etudiant.php"><span class="arrow">←</span> Accueil</a>
    </div>
    <div class="card-entreprise">
        <div class="header-entreprise">
            <div class="logo-container-entreprise">
                <div class="company-name-entreprise">Thalès</div>
            </div>
            <div class="tags-entreprise">
                <div class="tag-entreprise">Cybersécurité</div>
                <div class="tag-entreprise">Aérospatiale</div>
            </div>
        </div>

        <div class="description-entreprise">
            Thales est un groupe multinational français spécialisé dans les hautes technologies. Il opère dans plusieurs secteurs stratégiques, notamment la défense, l'aérospatiale, la cybersécurité et le transport. Présent dans plus de 68 pays, Thalès développe des solutions innovantes pour répondre aux défis de sécurité et de transformation numérique des industries critiques.
        </div>

        <div class="section-title-entreprise">Secteur principal : Hautes technologies</div>

        <div class="info-container-entreprise">
            <div class="info-column-entreprise">
                <div class="info-label-entreprise">Téléphone :</div>
                <div class="info-value-entreprise">06 12 45 78 89</div>

                <div class="info-label-entreprise">Localisation :</div>
                <div class="info-value-entreprise">10 Rue des Carmes, 54000, Nancy</div>
            </div>

            <div class="info-column-entreprise">
                <div class="info-label-entreprise">Adresse mail :</div>
                <div class="info-value-entreprise email-value-entreprise">contact.recrutement@thalesgroup.com</div>
            </div>
        </div>

        <div class="card-footer">
            <div class="publication-info">
                Evaluation actuelle : 4.2 étoiles
            </div>
            <div class="actions">
                <div class="heart-container" title="Like">
                    <input type="checkbox" class="checkbox" id="Give-It-An-Id">
                    <div class="svg-container">
                        <svg viewBox="0 0 24 24" class="svg-outline" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.5,1.917a6.4,6.4,0,0,0-5.5,3.3,6.4,6.4,0,0,0-5.5-3.3A6.8,6.8,0,0,0,0,8.967c0,4.547,4.786,9.513,8.8,12.88a4.974,4.974,0,0,0,6.4,0C19.214,18.48,24,13.514,24,8.967A6.8,6.8,0,0,0,17.5,1.917Zm-3.585,18.4a2.973,2.973,0,0,1-3.83,0C4.947,16.006,2,11.87,2,8.967a4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,11,8.967a1,1,0,0,0,2,0,4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,22,8.967C22,11.87,19.053,16.006,13.915,20.313Z">
                            </path>
                        </svg>
                        <svg viewBox="0 0 24 24" class="svg-filled" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.5,1.917a6.4,6.4,0,0,0-5.5,3.3,6.4,6.4,0,0,0-5.5-3.3A6.8,6.8,0,0,0,0,8.967c0,4.547,4.786,9.513,8.8,12.88a4.974,4.974,0,0,0,6.4,0C19.214,18.48,24,13.514,24,8.967A6.8,6.8,0,0,0,17.5,1.917Z">
                            </path>
                        </svg>
                        <svg class="svg-celebrate" width="100" height="100" xmlns="http://www.w3.org/2000/svg">
                            <polygon points="10,10 20,20"></polygon>
                            <polygon points="10,50 20,50"></polygon>
                            <polygon points="20,80 30,70"></polygon>
                            <polygon points="90,10 80,20"></polygon>
                            <polygon points="90,50 80,50"></polygon>
                            <polygon points="80,80 70,70"></polygon>
                        </svg>
                    </div>
                </div>
                <button class="view-btn" onclick="window.location.href='../rate.php'"> Evaluer </button>
            </div>
    </div>
</main>
<?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>