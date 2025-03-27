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
            <h4>Paramètres de tri :</h4>
            <div class="slide-radio-group">
                <label><input type="radio" name="sort"> Offres récentes</label>
                <label><input type="radio" name="sort"> Offres anciennes</label>
                <label><input type="radio" name="sort"> Plus populaire</label>
            </div>
        </div>
        <div class="slide-filter-section">
            <h4>Rémunération (mensuel) :</h4>
            <h5> Min: <input type="text" placeholder="600" class="input-min"> €</h5>
        </div>
        <div class="slide-filter-section">
            <h4>Durée du stage :</h4>
            <div class="duration-inputs">
                <h5> Min : <input type="text" placeholder="1" class="input-min"> semaines </h5>
                <h5> Max : <input type="text" placeholder="12" class="input-max"> semaines </h5>
            </div>
        </div>
        <div class="slide-filter-section">
            <h4>Localisation :</h4>
            <input type="text" placeholder="Ville, Code Postal, Département, Région" class="input-location">
        </div>
        <div class="slide-filter-section">
            <h4>Compétences :</h4>
            <input type="text" placeholder="Informatique, BTP, Industrie" class="input-skills">
        </div>
            <button class="slide-apply-btn">Appliquer</button>
            <button class="close-filter" id="close-filter">✖</button>
        </div>
    </div>

  <main>
      <div class="filters">
        <h3>Filtres</h3>
          <div class="filter-sections-container">
              <div class="filter-section">
              <h4>Paramètres de tri :</h4>
              <div class="radio-group">
                <label><input type="radio" name="sort"> Offres récentes</label>
                <label><input type="radio" name="sort"> Offres anciennes</label>
                <label><input type="radio" name="sort"> Plus populaire</label>
              </div>
            </div>
            <div class="filter-section">
              <h4>Rémunération (mensuel) :</h4>
              <h5> Min: <input type="text" placeholder="600" class="input-min"> €</h5>
            </div>
            <div class="filter-section">
              <h4>Durée du stage :</h4>
              <div class="duration-inputs">
                <h5> Min : <input type="text" placeholder="1" class="input-min"> semaines </h5>
                <h5> Max : <input type="text" placeholder="12" class="input-max"> semaines </h5>
              </div>
            </div>
            <div class="filter-section">
              <h4>Localisation :</h4>
              <input type="text" placeholder="Ville, Code Postal, Département, Région" class="input-location">
            </div>
            <div class="filter-section">
              <h4>Compétences :</h4>
              <input type="text" placeholder="Informatique, BTP, Industrie" class="input-skills">
            </div>
          </div>
        <button class="apply-btn">Appliquer</button>
      </div>

      <div class="main-content-gestion">
          <div class="add-header">
              <input placeholder="Search..." class="search-input" name="search" type="search"/>
              <button class="add-something" onclick="window.location.href='add_offre.php'">Ajouter une nouvel offre de stage
                  <div class="plus-icon">+</div>
              </button>
          </div>
          <div class="pilote-card-entreprise">
              <div class="pilote-card-header">
                  <div class="logo-container-entreprise">
                      <div class="company-name-entreprise">Ingénieur en informatique H/F</div>
                      <div class="name-company">Jupino</div>
                  </div>
                  <div class="pilote-tags-container">
                      <div class="pilote-tag">C++</div>
                      <div class="pilote-tag">Git / GitHub</div>
                      <div class="pilote-tag">Gestion de projet</div>
                      <div class="pilote-tag">Logique calculatoire</div>
                  </div>
              </div>

              <div class="pilote-description">
                  Thales est un groupe multinational français spécialisé dans les hautes technologies. Il opère dans plusieurs secteurs stratégiques, notamment la défense, l'aérospatiale, la cybersécurité et le transport. Présent dans plus de 68 pays, Thalès développe des solutions innovantes pour répondre aux défis de sécurité et de transformation numérique des industries critiques.
              </div>

              <div class="pilote-card-footer">
                  <div class="pilote-offres-count">Publiée il y a 17 jours</div>
                  <button class="pilote-view-btn" onclick="window.location.href='card_offre_pilote.php'">Regarder</button>
              </div>
          </div>
      </div>
    
  </main>
    <?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>