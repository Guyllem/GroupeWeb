
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
        <div class="card">
            <div class="card-header">
                <h1>Louis Durenne</h1>
                    <span class="tag">Promotion</span>
            </div>
            <span class="info-com">Informations complémentaires</span>
            <div class="card-body">
                <div id="second-content">
                    <div id="mail-eleves">
                        <p>Adresse mail :</p>
                        <p id="mm">Adresse@mail.com</p>
                    </div>
                    <div id="telephone-eleves">
                        <p>Téléphone :</p>
                        <p id="mm">06 06 06 06 06</p>
                    </div>
                    <div id="Campus-eleves">
                        <p>Campus :</p>
                        <p id="mm">CESI Nancy</p>
                    </div>
                    <button class="boutton-pwd" type="button">Modifier le Mot de passe</button>
                </div>
                <div class="milieu-pages">
                <h1>Compétences</h1>
                <ul>
                    <div id="un"><li>IA</li></div>
                    <div id="de"><li>Web developpement</li></div>
                    <div id="un"><li>MySQL</li></div>
                    <div id="de"><li>Figma</li></div>
                </ul>
                </div>
            </div>
            <div class="btoo">
                <button class="boutton-wishlist" type="button">Accéder à la Wishlist</button>
                <button class="boutton-offres" type="button">Accéder aux offres postulées</button>
                <button class="boutton-supp" type="button">Supprimer</button>
                <button class="boutton-modif" type="button">Modifier</button>
            </div>
        </div>
  </main>
    <?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>