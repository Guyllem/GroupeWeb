<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage Connect</title>
    <link rel="stylesheet" href="styles_page_admin.css">
</head>
<body>

    <?php
    $GLOBALS["responsiveFilter"] = true;
    $GLOBALS['adminPage'] = true;

    include '../page_pilote/navbar-pilote.php';

    ?>

<div class="slide-filter" id="slide-filter">
    <div class="slide-filter-content">
        <h3>Filtres</h3>
        <div class="slide-filter-section">
            <h4>Campus :</h4>
            <div class="dropdown">
                <button class="dropdown-toggle">Choisir ↓</button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item" data-value = "Nancy">Nancy</a>
                    <a href="#" class="dropdown-item" data-value = "Metz">Metz</a>
                    <a href="#" class="dropdown-item" data-value = "Listembourg">Listembourg</a>
                </div>
            </div>
        </div>
        <div class="slide-filter-section">
            <h4>Classe :</h4>
            <div class="dropdown">
                <button class="dropdown-toggle">Choisir ↓</button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item" data-value = "A1">A1</a>
                    <a href="#" class="dropdown-item" data-value = "A2 Info">A2 Info</a>
                    <a href="#" class="dropdown-item" data-value = "A2 Géné">A2 Géné</a>
                </div>
            </div>
        </div>
        <button class="slide-apply-btn">Appliquer</button>
        <button class="close-filter" id="close-filter">✖</button>
    </div>
</div>
</div>

<main>
    <div class="filters">
        <h3>Filtres</h3>
        <div class="filter-section">
            <h4>Campus :</h4>
            <div class="dropdown">
                <button class="dropdown-toggle">Choisir ↓</button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item" data-value = "Nancy">Nancy</a>
                    <a href="#" class="dropdown-item" data-value = "Metz">Metz</a>
                    <a href="#" class="dropdown-item" data-value = "Listembourg">Listembourg</a>
                </div>
            </div>
        </div>
        <div class="filter-section">
            <h4>Classe :</h4>
            <div class="dropdown">
                <button class="dropdown-toggle">Choisir ↓</button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item" data-value = "A1">A1</a>
                    <a href="#" class="dropdown-item" data-value = "A2 Info">A2 Info</a>
                    <a href="#" class="dropdown-item" data-value = "A2 Géné">A2 Géné</a>
                </div>
            </div>
        </div>
        <button class="apply-btn">Appliquer</button>
    </div>

    <div class="main-content-gestion">
        <div class="add-header">
            <input placeholder="Search..." class="search-input" name="search" type="search"/>
            <button class="add-something">Ajouter un nouveau pilote
                <div class="plus-icon">+</div>
            </button>
        </div>
    </div>

</main>
<?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>

