<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Page Connexion</title>
    <link rel="stylesheet" href="styles_page_admin.css" />
  </head>
  <nav>
      <div class="logo">Stage Connect</div>
      <div class="nav-right">
          <a href="#" class="nav-item">Se déconnecter</a>
      </div>
      <button class="logout-icon" id="logout-icon">
          <img src="../../assets/icons/logout.png" alt="Logout Icon">
      </button>
  </nav>

  <body>
    <header>
      <img class="bulle1" src="../../assets/images/bulle3.png" alt="bulle1" />
      <img class="arc-en-ciel" src="../../assets/images/arc%20en%20ciel.png" alt="arc en ciel" />
      <img class="bulle2" src="../../assets/images/bulle2.png" alt="bulle2" />
    </header>
    <img src="../../assets/images/bulle3.png" alt="bulle3" class="bulle3" />

    <img
    src="../../assets/images/arc%20en%20ciel2.png"
    alt="arc-en-ciel2"
    class="arc-en-ciel2"
  />
  <img class="bulle4" src="../../assets/images/bulle2.png" alt="bulle4" />
    <div class="titre">Bienvenue dans votre espace administrateur</div>


    <main class="main-content">
        <a class="block" href="gestion_pilote_admin.php">Gestion des pilotes</a>
        <a class="block" href="gestion_eleves_admin.php">Gestion des élèves</a>
        <a class="block" href="gestion_offres_admin.php">Gestion des offres</a>
        <a class="block" href="gestion_entreprises_admin.php">Gestion des entreprises</a>
    </main>
    <?php include '../footer.php'; ?>
  </body>
  <script src="../frontend_script.js"></script>
</html>
