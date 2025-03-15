<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stage Connect</title>
  <link rel="stylesheet" href="styles_page_etudiant.css">

</head>
<main>

  <div class="container">
      <?php include 'navbar-etudiant.php'; ?>
    <div class="main-section">
      <div class="back-button">
        <a href="page_etudiant.php"><span class="arrow">←</span> Accueil</a>
      </div>
      <div class="search-bar">
        <div class="tabs">
          <button >Entreprises</button>
          <button class="active">Offres</button>
        </div>
      </div>
      <p class="main-title-wishlist" >Votre Wish-List est actuellement vide.</p>
      <p class="sub-title-wishlist" >C’est bien dommage ...</p>
    </div>
      <div class="back-design">
        <img class="bulle2-1" src="../../assets/images/bulle2.png" alt="bulle2-1" />
        <img class="bulle2-2" src="../../assets/images/bulle2.png" alt="bulle2-2" />
        <img class="bulle3-1" src="../../assets/images/bulle3.png" alt="bulle3-1" />
        <img class="bulle3-2" src="../../assets/images/bulle3.png" alt="bulle3-2" />
        <img class="bulle3-3" src="../../assets/images/bulle3.png" alt="bulle3-3" />
      </div>
  </div>
</main>
<?php include 'footer.php'; ?>
<script src="../frontend_script.js"></script>
</html>
