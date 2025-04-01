<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage Connect - Évaluation</title>
    <link rel="stylesheet" href="styles_page_pilote.css">
    <link rel="stylesheet" href="../../../../public/css/modal.css">
</head>

<nav>
    <div class="logo">Stage Connect</div>
    <div class="nav-right">
    </div>
</nav>

<main>
    <div class="form-container">
        <a href="javascript:history.back()" class="btn-return">
            <span class="arrow">←</span> Retour
        </a>
        <h2 class="form-title">Modifier l'entreprise</h2>

        <form id="add-offre-form">
            <div class="form-group">
                <label for="nom">Nom de l'entreprise *</label>
                <input type="text" id="nom" name="nom" class="form-control" placeholder="Ex: Thalès">
                <div id="nom-error" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="secteur">Secteur principal *</label>
                <input type="text" id="secteur" name="secteur" class="form-control" placeholder="Ex: Informatique">
                <div id="secteur-error" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="email">Adresse mail *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Ex: thales@thales.com">
                <div id="email-error" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="telephone">Numéro de téléphone *</label>
                <input type="text" id="telephone" name="telephone" class="form-control" placeholder="Ex: 0806041234">
                <div id="telephone-error" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="description">Description de l'entreprise</label>
                <div class="textarea-container">
                    <textarea id="description" name="description" class="form-control" placeholder="Décrivez simplement l'entreprise" maxlength="1000"></textarea>
                    <div class="char-count"><span id="char-count">0</span>/1000</div>
                </div>
                <div id="description-error" class="error-message"></div>
            </div>



            <div class="form-row">
                <div class="form-group">
                    <label for="localisation">Localisation *</label>
                    <input type="text" id="localisation" name="localisation" class="form-control" placeholder="Ex: Paris, 75000">
                    <div id="localisation-error" class="error-message"></div>
                </div>
            </div>

            <div class="form-group" style="text-align: center;">
                <button type="submit" id="submit-btn" class="form-submit" disabled>Ajouter l'entreprise</button>
            </div>
        </form>
    </div>


    <div class="confirmation-popup" id="confirmationPopup">
        <div class="popup-content">
            <p>L'entreprise a bien été modifiée</p>
            <button class="close-popup" onclick="history.back()">Fermer</button>
        </div>
    </div>
</main>

<footer>
    <p id="bottom-text"> © 2025 - Web4all - Tous droits réservés. |
        <a href="../mentions_legales/mentions_legales.html">Mentions légales</a> |
        <a href="../mentions_legales/politiques_de_confidentialité.html">Politique de confidentialité</a> |
        <a href="../mentions_legales/condition_d'utilisation.html">Conditions d'utilisation</a>
    </p>
</footer>

<script src="../frontend_script.js"></script>
<script src="form_entreprise.js"></script>
</html>