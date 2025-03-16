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
                <h4>Localisation :</h4>
                <input type="text" placeholder="Ville, Code Postal, Département, Région" class="input-location">
            </div>
            <div class="slide-filter-section">
                <h4>Secteur :</h4>
                <input type="text" placeholder="Informatique, BTP, Industrie" class="input-skills">
            </div>
            <div class="slide-filter-section">
            <h4>Nombres d'offres :</h4>
            <div class="slide-radio-group">
                <label><input type="radio" name="sort"> Croissant</label>
                <label><input type="radio" name="sort"> Décroissant</label>
            </div>
            </div>
            <div class="slide-filter-section">
            <h4>Popularité :</h4>
            <div class="slide-radio-group">
                <label><input type="radio" name="sort"> Croissant</label>
                <label><input type="radio" name="sort"> Décroissant</label>
            </div>
            </div>
        <button class="slide-apply-btn">Appliquer</button>
        <button class="close-filter" id="close-filter">✖</button>
        </div>
    </div>
  <main>
      <div class="filters">
        <h3>Filtres</h3>
            <div class="filter-section">
                <h4>Localisation :</h4>
                <input type="text" placeholder="Ville, Code Postal, Département, Région" class="input-location">
              </div>
              <div class="filter-section">
                <h4>Secteur :</h4>
                <input type="text" placeholder="Informatique, BTP, Industrie" class="input-skills">
              </div>
            <div class="filter-section">
            <h4>Nombres d'offres :</h4>
            <div class="radio-group">
                <label><input type="radio" name="sort"> Croissant</label>
                <label><input type="radio" name="sort"> Décroissant</label>
            </div>
            </div>
            <div class="filter-section">
            <h4>Popularité :</h4>
            <div class="radio-group">
                <label><input type="radio" name="sort"> Croissant</label>
                <label><input type="radio" name="sort"> Décroissant</label>
            </div>
            </div>
        <button class="apply-btn">Appliquer</button>
      </div>

      <div class="main-content">
      </div>
  </main>
    <?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>