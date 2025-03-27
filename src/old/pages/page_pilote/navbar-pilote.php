<nav>
    <div class="nav-left">

        <?php
        if (isset($GLOBALS['adminPage']) && $GLOBALS['adminPage'] === true):
        ?>
        <a href="accueil_admin.php" class="nav-item">Accueil</a>
        <?php endif; ?>

        <?php
        if (isset($GLOBALS['pilotePage']) && $GLOBALS['pilotePage'] === true):
        ?>
        <a href="accueil_pilote.php" class="nav-item">Accueil</a>
        <?php endif; ?>


    </div>
    <div class="logo">Stage Connect</div>
    <div class="nav-right">
        <a href="../logout.php" class="nav-item">Se déconnecter</a>
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

    <?php
    if (isset($GLOBALS['adminPage']) && $GLOBALS['adminPage'] === true):
        ?>
        <h2><a href="../page_admin/accueil_admin.php" class="menu-item">Accueil</a></h2>
        <h2><a href="gestion_pilote_admin.php" class="menu-item">Gestion pilotes</a></h2>
        <h2><a href="gestion_eleves_admin.php" class="menu-item">Gestion élèves</a></h2>
        <h2><a href="gestion_offres_admin.php" class="menu-item">Gestion offres</a></h2>
        <h2><a href="gestion_entreprises_admin.php" class="menu-item">Gestion entreprises</a></h2>
    <?php endif; ?>

    <?php
    if (isset($GLOBALS['pilotePage']) && $GLOBALS['pilotePage'] === true):
    ?>
    <h2><a href="accueil_pilote.php" class="menu-item">Accueil</a></h2>
    <h2><a href="gestion_eleves_pilote.php" class="menu-item">Gestion élèves</a></h2>
    <h2><a href="gestion_offres_pilotes.php" class="menu-item">Gestion offres</a></h2>
    <h2><a href="gestion_entreprises_pilotes.php" class="menu-item">Gestion entreprises</a></h2>
    <?php endif; ?>

    <h2><a href="../logout.php" class="menu-item-logout">Se déconnecter</a></h2>
    <button class="close-menu" id="close-menu">✖</button>
</div>
