<nav>
    <div class="nav-left">
        <a href="accueil_pilote.php" class="nav-item">Accueil</a>
    </div>
    <div class="logo">Stage Connect</div>
    <div class="nav-right">
        <a href="#" class="nav-item">Se déconnecter</a>
    </div>

    <?php
    if (isset($GLOBALS['responsiveFilter']) && $GLOBALS['responsiveFilter'] === true):
        ?>
        <button class="filter-menu" id="filter-menu">
            <img src="../../assets/icons/entonnoir.png" alt="Filter Icon">
        </button>
    <?php endif; ?>

    <button class="burger-menu" id="burger-menu">☰</button>
</nav>
<div class="slide-menu" id="slide-menu">
    <h2><a href="accueil_pilote.php" class="menu-item">Accueil</a></h2>
    <h2><a href="gestion_eleves_pilote.php" class="menu-item">Gestion élèves</a></h2>
    <h2><a href="gestion_offres_pilotes.php" class="menu-item">Gestion offres</a></h2>
    <h2><a href="gestion_entreprises_pilotes.php" class="menu-item">Gestion entreprises</a></h2>
    <h2><a href="#" class="menu-item-logout">Se déconnecter</a></h2>
    <button class="close-menu" id="close-menu">✖</button>
</div>
