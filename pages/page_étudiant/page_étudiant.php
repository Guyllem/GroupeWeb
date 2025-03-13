<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stage Connect</title>
  <link rel="stylesheet" href="styles_page_etudiant.css">
</head>
<body>
<main>
<div class="container">
    <?php include 'navbar.php'; ?>

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
        <input type="text" placeholder="Min: 600 €" class="input-min">
      </div>

      <div class="slide-filter-section">
        <h4>Durée du stage :</h4>
        <div class="duration-inputs">
          <input type="text" placeholder="Min: 1 semaines" class="input-min">
          <input type="text" placeholder="Max: 12 semaines" class="input-max">
        </div>
      </div>

      <div class="slide-filter-section">
        <h4>Niveau d'études :</h4>
        <div class="slide-checkbox-group">
          <div class="slide-checkbox-item"><label><input type="checkbox"> Bac +1</label></div>
          <div class="slide-checkbox-item"><label><input type="checkbox"> Bac +2</label></div>
          <div class="slide-checkbox-item"><label><input type="checkbox"> Bac +3</label></div>
          <div class="slide-checkbox-item"><label><input type="checkbox"> Bac +4</label></div>
          <div class="slide-checkbox-item"><label><input type="checkbox"> Bac +5</label></div>
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
  <div class="main-content">
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
        <input type="text" placeholder="Min: 600 €" class="input-min">
      </div>

      <div class="filter-section">
        <h4>Durée du stage :</h4>
        <div class="duration-inputs">
          <input type="text" placeholder="Min: 1 semaines" class="input-min">
          <input type="text" placeholder="Max: 12 semaines" class="input-max">
        </div>
      </div>

      <div class="filter-section">
        <h4>Niveau d'études :</h4>
        <div class="checkbox-group">
          <label><input type="checkbox"> Bac +1</label>
          <label><input type="checkbox"> Bac +2</label>
          <label><input type="checkbox"> Bac +3</label>
          <label><input type="checkbox"> Bac +4</label>
          <label><input type="checkbox"> Bac +5</label>
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

    <div class="main-section">
      <div class="search-bar">
        <div class="tabs">
          <button >Entreprises</button>
          <button class="active">Offres</button>
        </div>
        <div class="search-container">
          <input placeholder="Search..." class="search-input" name="search" type="search"/>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="logo-placeholder"></div>
          <h3>Stage Développeur Full-Stack</h3>
          <div class="tags">
            <span class="tag">Développement logiciel</span>
            <span class="tag">Web</span>
            <span class="tag">Informatique</span>

          </div>
        </div>
        <div class="card-body">
          <p>
            Vous intégrerez une équipe dynamique en charge du développement d’une application web innovante.
            Vous participerez à la conception, au développement et au déploiement des nouvelles fonctionnalités
            en utilisant des technologies modernes comme React, Node.js et PostgreSQL. Vous serez également impliqué
            dans l'optimisation des performances et la correction des bugs.
          </p>
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

            <button class="view-btn">Regarder</button>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>

</main>

<?php include 'footer.php'; ?>
</body>
<script src="etudiant_script.js"></script>
</html>