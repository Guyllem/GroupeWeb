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
        <h2 class="form-title">Modifier l'offre de stage</h2>

        <form id="add-offre-form">
            <div class="form-group">
                <label for="titre">Titre de l'offre *</label>
                <input type="text" id="titre" name="titre" class="form-control" placeholder="Ex: Développeur Full-Stack">
                <div id="titre-error" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="description">Description de l'offre *</label>
                <div class="textarea-container">
                    <textarea id="description" name="description" class="form-control" placeholder="Décrivez les missions, responsabilités et objectifs du stage" maxlength="1000"></textarea>
                    <div class="char-count"><span id="char-count">0</span>/1000</div>
                </div>
                <div id="description-error" class="error-message"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="remuneration">Rémunération mensuelle (€) *</label>
                    <input type="number" id="remuneration" name="remuneration" class="form-control" placeholder="Ex: 600" min="0" step="0.01">
                    <div id="remuneration-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="niveau_etude">Niveau d'étude requis *</label>
                    <select id="niveau_etude" name="niveau_etude" class="form-control">
                        <option value="">Sélectionner un niveau</option>
                        <option value="Bac+1">Bac+1</option>
                        <option value="Bac+2">Bac+2</option>
                        <option value="Bac+3">Bac+3</option>
                        <option value="Bac+4">Bac+4</option>
                        <option value="Bac+5">Bac+5</option>
                    </select>
                    <div id="niveau_etude-error" class="error-message"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date_debut">Date de début *</label>
                    <input type="date" id="date_debut" name="date_debut" class="form-control">
                    <div id="date_debut-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="duree_min">Durée minimale (semaines) *</label>
                    <input type="number" id="duree_min" name="duree_min" class="form-control" placeholder="Ex: 8" min="1">
                    <div id="duree_min-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="duree_max">Durée maximale (semaines)</label>
                    <input type="number" id="duree_max" name="duree_max" class="form-control" placeholder="Ex: 16" min="1">
                    <div id="duree_max-error" class="error-message"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="competences">Compétences requises</label>
                <select id="competences" name="competences" class="form-control">
                    <option value="">Sélectionner des compétences</option>
                    <option value="JavaScript">JavaScript</option>
                    <option value="PHP">PHP</option>
                    <option value="Java">Java</option>
                    <option value="Python">Python</option>
                    <option value="SQL">SQL</option>
                    <option value="React">React</option>
                    <option value="Angular">Angular</option>
                    <option value="Node.js">Node.js</option>
                    <option value="DevOps">DevOps</option>
                    <option value="AWS">AWS</option>
                    <option value="Git">Git</option>
                    <option value="Cybersécurité">Cybersécurité</option>
                </select>
                <div id="competences-error" class="error-message"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="entreprise">Entreprise associée *</label>
                    <select id="entreprise" name="entreprise" class="form-control">
                        <option value="">Sélectionner une entreprise</option>
                        <option value="1">Thalès</option>
                        <option value="2">EDF</option>
                        <option value="3">Atos</option>
                        <option value="4">Capgemini</option>
                        <option value="5">Orange</option>
                    </select>
                    <div id="entreprise-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="localisation">Localisation *</label>
                    <input type="text" id="localisation" name="localisation" class="form-control" placeholder="Ex: Paris, 75000">
                    <div id="localisation-error" class="error-message"></div>
                </div>
            </div>

            <div class="form-group" style="text-align: center;">
                <button type="submit" id="submit-btn" class="form-submit" disabled>Modifier l'offre</button>
            </div>
        </form>
    </div>


    <div class="confirmation-popup" id="confirmationPopup">
        <div class="popup-content">
            <p>L'offre a bien été modifiée</p>
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
<script src="form_offre.js"></script>
</html>