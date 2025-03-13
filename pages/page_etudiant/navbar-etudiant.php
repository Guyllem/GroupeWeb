
<nav>
    <div class="nav-left">
        <a href="wishlist.php" class="nav-item">WishList</a>
        <a href="#" class="nav-item">Mes Offres</a>
    </div>
    <div class="logo">Stage Connect</div>
    <div class="nav-right">
        <a href="mon_profil.php" class="nav-item">Mon Profil</a>
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
    <h2><a href="page_etudiant.php" class="menu-item">Accueil</a></h2>
    <h2><a href="wishlist.php" class="menu-item">Wishlist</a></h2>
    <h2><a href="#" class="menu-item">Mes Offres</a></h2>
    <h2><a href="mon_profil.php" class="menu-item">Mon Profil</a></h2>
    <h2><a href="#" class="menu-item-logout">Se déconnecter</a></h2>
    <button class="close-menu" id="close-menu">✖</button>
</div>

