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
        <button class="apply-btn">Appliquer</button>
      </div>
    
      <div class="main-content">
      </div> 
    
  </main>
    <?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>