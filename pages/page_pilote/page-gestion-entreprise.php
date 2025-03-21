
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
<main class="content-principal">
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

        <div class="button-container-entreprise">
            <button class="button-entreprise button-delete-entreprise">Supprimer</button>
            <button class="button-entreprise button-edit-entreprise">Modifier</button>
            <button class="button-entreprise button-evaluate-entreprise">Évaluer</button>
        </div>

        <div class="publication-info">
            Evaluation actuelle : 4.2 étoiles
        </div>
    </div>
</main>
<?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>