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
    $GLOBALS['responsiveFilter'] = true;

    if (isset($GLOBALS['adminPage']) && $GLOBALS['adminPage']) {
        $GLOBALS['pilotePage'] = false;
    } else {
        $GLOBALS['pilotePage'] = true;
        $GLOBALS['adminPage'] = false;
    }

    include 'navbar-pilote.php';
    ?>

    <div class="slide-filter" id="slide-filter">
        <div class="slide-filter-content">
            <h3>Filtres</h3>
            <div class="slide-filter-section">
                <h4>Campus :</h4>
                <div class="dropdown">
                    <button class="dropdown-toggle">Choisir ↓</button>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-value = "Nancy">Nancy</a>
                        <a href="#" class="dropdown-item" data-value = "Metz">Metz</a>
                        <a href="#" class="dropdown-item" data-value = "Listembourg">Listembourg</a>
                    </div>
                </div>
            </div>
            <div class="slide-filter-section">
                <h4>Classe :</h4>
                <div class="dropdown">
                    <button class="dropdown-toggle">Choisir ↓</button>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-value = "A1">A1</a>
                        <a href="#" class="dropdown-item" data-value = "A2 Info">A2 Info</a>
                        <a href="#" class="dropdown-item" data-value = "A2 Géné">A2 Géné</a>
                    </div>
                </div>
            </div>
            <div class="slide-filter-section">
                <h4>Nombres d'offres postulées :</h4>
                <h5> Min: <input type="text" placeholder="0" class="input-min"> </h5>
                <h5> Max : <input type="text" placeholder="10" class="input-max"> </h5>
            </div>
            <div class="slide-filter-section">
                <h4>Nombres d'offres en wishlist :</h4>
                <div class="duration-inputs">
                    <h5> Min : <input type="text" placeholder="0" class="input-min"> </h5>
                    <h5> Max : <input type="text" placeholder="15" class="input-max"> </h5>
                </div>
            </div>
                <button class="slide-apply-btn">Appliquer</button>
                <button class="close-filter" id="close-filter">✖</button>
            </div>
        </div>
    </div>

  <main>
      <div class="filters">
          <h3>Filtres</h3>
          <div class="filter-section">
              <h4>Campus :</h4>
              <div class="dropdown">
                  <button class="dropdown-toggle">Choisir ↓</button>
                  <div class="dropdown-menu">
                      <a href="#" class="dropdown-item" data-value = "Nancy">Nancy</a>
                      <a href="#" class="dropdown-item" data-value = "Metz">Metz</a>
                      <a href="#" class="dropdown-item" data-value = "Listembourg">Listembourg</a>
                  </div>
              </div>
          </div>
          <div class="filter-section">
              <h4>Classe :</h4>
              <div class="dropdown">
                  <button class="dropdown-toggle">Choisir ↓</button>
                  <div class="dropdown-menu">
                      <a href="#" class="dropdown-item" data-value = "A1">A1</a>
                      <a href="#" class="dropdown-item" data-value = "A2 Info">A2 Info</a>
                      <a href="#" class="dropdown-item" data-value = "A2 Géné">A2 Géné</a>
                  </div>
              </div>
            </div>
            <div class="filter-section">
              <h4>Nombres d'offres postulées :</h4>
              <h5> Min: <input type="text" placeholder="0" class="input-min"> </h5>
              <h5> Max : <input type="text" placeholder="10" class="input-max"> </h5>
            </div>
            <div class="filter-section">
              <h4>Nombres d'offres en wishlist :</h4>
              <div class="duration-inputs">
                  <h5> Min : <input type="text" placeholder="0" class="input-min"> </h5>
                  <h5> Max : <input type="text" placeholder="15" class="input-max"> </h5>
              </div>
            </div>
              <button class="apply-btn">Appliquer</button>
          </div>

      <div class="main-content">
          <div class="haut-page">
              <div id="carré_blanc">/</div>
              <h1>Durenne Louis</h1>
              <p>Promotion</p>
          </div>
          <p id="info_com">Informations complémentaires</p>
          <div class="milieu-pages">
          <div id="second-content">
              <div id="mail-eleves">
                  <p>Adresse mail :</p>
                  <p id="mm">Adresse@mail.com</p>
              </div>
              <div id="telephone-eleves">
                  <p>Téléphone :</p>
                  <p id="mm">06 06 06 06 06</p>
              </div>
              <button class="boutton-pwd" type="button">Modifier le Mot de passe</button>
          </div>
          <h1>Compétences</h1>
          <ul>
              <div id="un"><li>IA</li></div>
              <div id="de"><li>Web developpement</li></div>
              <div id="un"><li>MySQL</li></div>
              <div id="de"><li>Figma</li></div>
          </ul>
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