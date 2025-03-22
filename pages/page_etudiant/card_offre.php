
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage Connect</title>
    <link rel="stylesheet" href="../page_pilote/styles_page_pilote.css">
    <link rel="stylesheet" href="styles_page_etudiant.css">
</head>
<body>

<?php

include 'navbar-etudiant.php';
?>

<main class="content-principal">
    <div class="back-button">
        <a href="javascript:history.back()"><span class="arrow">←</span> Accueil</a>
    </div>
    <div class="card-entreprise">
        <div class="header-entreprise">
            <div class="logo-container-entreprise">
                <div class="logo-entreprise"></div>
                <div class="company-name-entreprise">Stage Développeur Full-Stack</div>
            </div>
            <div class="tags-entreprise">
                <div class="tag-entreprise">Développement logiciel</div>
                <div class="tag-entreprise">Web</div>
            </div>
        </div>

        <div class="description-entreprise">
            Vous intégrerez une équipe dynamique en charge du développement d'une application web innovante. Vous participerez à la conception, au développement et au déploiement des nouvelles fonctionnalités en utilisant des technologies modernes comme React, Node.js et PostgreSQL. Vous serez également impliqué dans l'optimisation des performances et la correction des bugs.
        </div>

        <div class="info-container-entreprise">
            <div class="info-column-entreprise">
                <div class="stage-info-row">
                    <div class="info-label-entreprise">Niveau attendu :</div>
                    <div class="info-value-entreprise">Bac+2 et plus</div>
                </div>

                <div class="stage-info-row">
                    <div class="info-label-entreprise">Rémunération :</div>
                    <div class="info-value-entreprise">615 €/mois</div>
                </div>

                <div class="stage-info-row">
                    <div class="info-label-entreprise">Localisation :</div>
                    <div class="info-value-entreprise">Nancy</div>
                </div>
            </div>

            <div class="info-column-entreprise">
                <div class="stage-info-row">
                    <div class="info-label-entreprise">Date minimal du début du stage :</div>
                    <div class="info-value-entreprise">07 / 04 / 2025</div>
                </div>

                <div class="stage-info-row">
                    <div class="info-label-entreprise">Durée minimal du stage :</div>
                    <div class="info-value-entreprise">12 semaines</div>
                </div>

                <div class="stage-info-row">
                    <div class="info-label-entreprise">Durée maximal du stage :</div>
                    <div class="info-value-entreprise">16 semaines</div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <span class="publish-date">Publiée il y a 2 jours</span>
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
                <button class="view-btn" onclick="window.location.href='postulation.php'"> Postuler </button>
            </div>
</main>
<?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>