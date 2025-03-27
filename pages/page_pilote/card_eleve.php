
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
    <main>
        <div class="main-content-gestion">
            <div class="back-button">
                <a href="javascript:history.back()"><span class="arrow">←</span> Retour</a>
            </div>
            <div class="eleve-detail">
                <div class="eleve-header">
                    <div class="eleve-nom-prenom">Durenne Louis</div>
                    <div class="eleve-promotion">CPI A2 INFO - Nancy</div>
                </div>

                <div class="eleve-info-container">
                    <div class="eleve-info-col">
                        <div class="eleve-info-groupe">
                            <label>Adresse mail :</label>
                            <input type="email" value="louis@viacesi.fr" readonly class="eleve-input">
                        </div>

                        <div class="eleve-info-groupe">
                            <label>Téléphone :</label>
                            <input type="tel" value="06 13 33 03 66" readonly class="eleve-input">
                        </div>

                        <button class="eleve-btn-password">Modifier le mot de passe</button>
                    </div>

                    <div class="eleve-competences-col">
                        <h2 class="eleve-competences-titre">Compétences :</h2>
                        <div class="eleve-competences-grid">
                            <div class="eleve-competence">IA</div>
                            <div class="eleve-competence">Web développement</div>
                            <div class="eleve-competence">MySQL</div>
                            <div class="eleve-competence">Figma</div>

                        </div>
                    </div>
                </div>

                <div class="eleve-actions">
                    <div class="eleve-liens">
                        <button class="eleve-btn-wishlist">Accéder à la wishlist</button>
                        <button class="eleve-btn-offres">Accéder aux offres postulées</button>
                    </div>

                    <div class="eleve-admin-actions">
                        <button class="eleve-btn-modifier">Modifier</button>
                        <button class="eleve-btn-supprimer" onclick="window.location.href='../delete.php'">Supprimer</button>
                    </div>
                </div>
            </div>
        </div>
  </main>
    <?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>