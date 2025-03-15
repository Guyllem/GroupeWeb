<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Page Connexion</title>
    <link rel="stylesheet" href="../styles.css" />
  </head>
  <body>
    <header>
      <img class="bulle1" src="../assets/images/bulle1.png" alt="bulle1" />
      <img class="arc-en-ciel" src="../assets/images/arc%20en%20ciel.png" alt="arc en ciel" />
      <h1 class="titre">StageConnect</h1>
      <img class="bulle2" src="../assets/images/bulle2.png" alt="bulle2" />
      <p class="slogan">La clé pour décrocher le stage parfait</p>
    </header>
    <main>
      <form>
        <div class="email-form">
          <label for="email">Email :</label><br />
          <input type="email" id="email" />
        </div>
        <div class="password-form">
          <label for="password">Password :</label><br />
          <input type="password" id="password" />
        </div>
        <div class="checkbox-form">
          <label> <input type="checkbox"/> </label> Rester connecté
        </div>
        <button type="submit" class="submit-btn">Se connecter</button>
      </form>
      <img
        src="../assets/images/arc%20en%20ciel2.png"
        alt="arc-en-ciel2"
        class="arc-en-ciel2"
      />
      <img src="../assets/images/bulle3.png" alt="bulle3" class="bulle3" />
    </main>
    <?php include 'footer.php'; ?>
  </body>
  <script src="../script.js"></script>
</html>
