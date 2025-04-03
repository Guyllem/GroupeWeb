<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage Connect - Évaluation</title>
    <link rel="stylesheet" href="styles_page_pilote.css">
    <link rel="stylesheet" href="../modal.css">
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
        <h2 class="form-title">Ajouter un nouvel élève</h2>
        <form id="add-eleve-form" novalidate>
            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" id="nom" name="nom" class="form-input" required>
                        <div id="nom-error" class="form-error"></div>
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-input" required>
                        <div id="prenom-error" class="form-error"></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Adresse email</label>
                <input type="email" id="email" name="email" class="form-input" required>
                <div id="email-error" class="form-error"></div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required>
                <div id="password-error" class="form-error"></div>
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label for="promotion" class="form-label">Promotion</label>
                        <select id="promotion" name="promotion" class="form-input" required>
                            <option value="" selected disabled>Sélectionner une promotion</option>
                            <option value="A1">A1</option>
                            <option value="A2 Info">A2 Info</option>
                            <option value="A2 Géné">A2 Géné</option>
                            <option value="A3 Info">A3 Info</option>
                            <option value="A3 Géné">A3 Géné</option>
                            <option value="A4 Info">A4 Info</option>
                            <option value="A4 Géné">A4 Géné</option>
                            <option value="A5 Info">A5 Info</option>
                            <option value="A5 Géné">A5 Géné</option>
                        </select>
                        <div id="promotion-error" class="form-error"></div>
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="ville" class="form-label">Campus</label>
                        <select id="ville" name="ville" class="form-input" required>
                            <option value="" selected disabled>Sélectionner un campus</option>
                            <option value="Nancy">Nancy</option>
                            <option value="Metz">Metz</option>
                            <option value="Listembourg">Listembourg</option>
                        </select>
                        <div id="ville-error" class="form-error"></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="telephone" class="form-label">Numéro de téléphone</label>
                <input type="tel" id="telephone" name="telephone" class="form-input" required placeholder="Ex: 06 12 34 56 78">
                <div id="telephone-error" class="form-error"></div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-back" onclick="history.back()">Annuler</button>
                <button type="submit" class="btn btn-submit" id="submit-btn" disabled>Ajouter</button>
            </div>
        </form>
    </div>

    <div class="confirmation-popup" id="confirmationPopup">
        <div class="popup-content">
            <p>L'élève a bien été ajouté</p>
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
<script src="form_eleve.js"></script>
</html>