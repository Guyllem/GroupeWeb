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

        <?php
        $GLOBALS['responsiveFilter'] = true;
        include 'navbar-etudiant.php';
        ?>


        <div class="slide-filter" id="slide-filter">
            <div class="slide-filter-content">
                <h3>Filtres</h3>
                <div class="slide-filter-section">
                    <h4>Nombres d'offres:</h4>
                    <div class="slide-radio-group">
                        <label><input type="radio" name="sort"> Croissant</label>
                        <label><input type="radio" name="sort"> Décroissant</label>
                    </div>
                </div>

                <div class="slide-filter-section">
                    <h4>Localisation :</h4>
                    <input type="text" placeholder="Ville, Code Postal, Département, Région" class="input-location">
                </div>

                <div class="slide-filter-section">
                    <h4>Secteur :</h4>
                    <input type="text" placeholder="Informatique, BTP, Industrie" class="input-skills">
                </div>

                <button class="slide-apply-btn">Appliquer</button>
                <button class="close-filter" id="close-filter">✖</button>
            </div>
        </div>
        <div class="main-content">
            <div class="filters">
                <h3>Filtres</h3>
                <div class="filter-sections-container">

                <div class="filter-section">
                    <h4>Nombres d'offres :</h4>
                    <div class="slide-radio-group">
                        <label><input type="radio" name="sort"> Croissant</label>
                        <label><input type="radio" name="sort"> Décroissant</label>
                    </div>
                </div>

                <div class="filter-section">
                    <h4>Popularité :</h4>
                    <div class="slide-radio-group">
                        <label><input type="radio" name="sort"> Croissant</label>
                        <label><input type="radio" name="sort"> Décroissant</label>
                    </div>
                </div>


                <div class="filter-section">
                    <h4>Localisation :</h4>
                    <input type="text" placeholder="Ville, Code Postal, Département, Région" class="input-location">
                </div>

                <div class="filter-section">
                    <h4>Secteurs :</h4>
                    <input type="text" placeholder="Informatique, BTP, Industrie" class="input-skills">
                </div>

                <button class="apply-btn">Appliquer</button>
                </div>
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
                        <h3>Thalès</h3>
                        <div class="tags">
                            <span class="tag">Développement logiciel</span>
                            <span class="tag">Web</span>
                            <span class="tag">Informatique</span>

                        </div>
                    </div>
                    <div class="card-body">
                        <p>
                            Description random d'une entreprise YOUHOU
                            Description random d'une entreprise YOUHOU
                            Description random d'une entreprise YOUHOU
                            Description random d'une entreprise YOUHOU
                            Description random d'une entreprise YOUHOU
                            Description random d'une entreprise YOUHOU
                        </p>
                    </div>

                    <div class="card-footer">
                        <span class="publish-date">Evaluation actuelle : 4.2 étoiles</span>
                        <div class="actions">
                            <button class="view-btn">Regarder</button>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Thalès</h3>
                        <div class="tags">
                            <span class="tag">Développement logiciel</span>
                            <span class="tag">Web</span>
                            <span class="tag">Informatique</span>

                        </div>
                    </div>
                    <div class="card-body">
                        <p>
                            Description random d'une entreprise YOUHOU
                            Description random d'une entreprise YOUHOU
                            Description random d'une entreprise YOUHOU
                            Description random d'une entreprise YOUHOU
                            Description random d'une entreprise YOUHOU
                            Description random d'une entreprise YOUHOU
                        </p>
                    </div>

                    <div class="card-footer">
                        <span class="publish-date">Evaluation actuelle : 4.2 étoiles</span>
                        <div class="actions">
                            <button class="view-btn" onclick="window.location.href='card_entreprise.php'"> Regarder </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<?php include '../footer.php'; ?>
</body>
<script src="../frontend_script.js"></script>
</html>