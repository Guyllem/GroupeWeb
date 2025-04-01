<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage Connect - Évaluation</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../../../public/css/modal.css">
</head>

<nav>
    <div class="logo">Stage Connect</div>
    <div class="nav-right">
    </div>
</nav>

<main>
    <div class="rate-modal">
        <div class="rate-container">
            <h2>Évaluer l'entreprise</h2>
            <div class="company-info">
                <div class="company-name">Thalès</div>
                <div class="company-sector">Hautes technologies</div>
            </div>

            <div class="rating-section">
                <p>Sélectionnez une note pour cette entreprise :</p>
                <div class="stars-container">
                    <div class="stars-outer">
                        <div class="stars-inner"></div>
                    </div>
                    <div class="rating-value">0/5</div>
                </div>

                <div class="star-rating">
                    <input type="radio" id="star5" name="rating" value="5">
                    <label for="star5" title="5 étoiles"></label>

                    <input type="radio" id="star4" name="rating" value="4">
                    <label for="star4" title="4 étoiles"></label>

                    <input type="radio" id="star3" name="rating" value="3">
                    <label for="star3" title="3 étoiles"></label>

                    <input type="radio" id="star2" name="rating" value="2">
                    <label for="star2" title="2 étoiles"></label>

                    <input type="radio" id="star1" name="rating" value="1">
                    <label for="star1" title="1 étoile"></label>
                </div>
            </div>

            <div class="buttons-container">
                <button class="back-btn" onclick="history.back()">Retour</button>
                <button class="validate-btn" disabled>Valider</button>
            </div>
        </div>
    </div>

    <div class="confirmation-popup" id="confirmationPopup">
        <div class="popup-content">
            <p>Évaluation bien prise en compte</p>
            <button class="close-popup" onclick="history.back()">Fermer</button>
        </div>
    </div>
</main>

<footer>
    <p id="bottom-text"> © 2025 - Web4all - Tous droits réservés. |
        <a href="mentions_legales/mentions_legales.html">Mentions légales</a> |
        <a href="mentions_legales/politiques_de_confidentialité.html">Politique de confidentialité</a> |
        <a href="mentions_legales/condition_d'utilisation.html">Conditions d'utilisation</a>
    </p>
</footer>

<script src="frontend_script.js"></script>
</html>