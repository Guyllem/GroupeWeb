
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage Connect</title>
    <link rel="stylesheet" href="styles_page_pilote.css">
</head>
<body>

<?php

if (isset($GLOBALS['adminPage']) && $GLOBALS['adminPage']) {
    $GLOBALS['pilotePage'] = false;
} else {
    $GLOBALS['pilotePage'] = true;
    $GLOBALS['adminPage'] = false;
}

include 'navbar-pilote.php';
?>
<main class="main-content-gestion">
    <div class="card-entreprise">
        <div class="header-entreprise">
            <div class="logo-container-entreprise">
                <div class="logo-entreprise"></div>
                <div class="logo-container-entreprise">
                    <div class="company-name-entreprise">Stage Développeur Full-Stack</div>
                    <div class="name-company">EDF</div>
                </div>
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

        <div class="button-container-entreprise">
            <div class="publication-info-container">
                <div class="publication-info">
                    Publiée il y a 17 jours
                </div>
            </div>
            <button class="button-entreprise button-edit-entreprise">Modifier</button>
            <button class="button-entreprise button-delete-entreprise">Supprimer</button>
        </div>
    </div>
</main>
<?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>