<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage Connect - Postuler</title>
    <link rel="stylesheet" href="styles_page_etudiant.css">
    <link rel="stylesheet" href="../modal.css">
</head>

<nav>
    <div class="logo">Stage Connect</div>
    <div class="nav-right">
    </div>
</nav>

<main>
    <div class="rate-modal">
        <div class="rate-container">
            <div class="company-info">
                <div class="company-name">Stage Développeur Full-Stack</div>
                <div class="company-sector">Développement logiciel</div>
            </div>

            <div class="postulate-section">
                <p>Veuillez télécharger votre CV et votre lettre de motivation :</p>

                <div class="file-upload-container">
                    <label for="cv-upload" class="file-upload-label">
                        <div class="file-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 3v4a1 1 0 0 0 1 1h4" stroke="#3F51B5" stroke-width="2" stroke-linecap="round"/>
                                <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z" stroke="#3F51B5" stroke-width="2" stroke-linecap="round"/>
                                <path d="M12 11v6M9 14h6" stroke="#3F51B5" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="file-info">
                            <div class="file-title">CV (PDF uniquement)</div>
                            <div class="file-subtitle" id="cv-file-name">Aucun fichier sélectionné</div>
                        </div>
                    </label>
                    <input type="file" id="cv-upload" accept=".pdf" class="file-input" />
                    <div class="file-error" id="cv-error"></div>
                </div>

                <div class="file-upload-container">
                    <label for="motivation-upload" class="file-upload-label">
                        <div class="file-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 3v4a1 1 0 0 0 1 1h4" stroke="#3F51B5" stroke-width="2" stroke-linecap="round"/>
                                <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z" stroke="#3F51B5" stroke-width="2" stroke-linecap="round"/>
                                <path d="M9 15h6M9 11h3" stroke="#3F51B5" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="file-info">
                            <div class="file-title">Lettre de motivation (PDF uniquement)</div>
                            <div class="file-subtitle" id="motivation-file-name">Aucun fichier sélectionné</div>
                        </div>
                    </label>
                    <input type="file" id="motivation-upload" accept=".pdf" class="file-input" />
                    <div class="file-error" id="motivation-error"></div>
                </div>
            </div>

            <div class="buttons-container">
                <button class="back-btn" onclick="history.back()">Retour</button>
                <button class="validate-btn" disabled>Postuler</button>
            </div>
        </div>
    </div>

    <div class="confirmation-popup" id="confirmationPopup">
        <div class="popup-content">
            <p>Votre candidature a bien été envoyée</p>
            <div class="submission-date" id="submissionDate"></div>
            <button class="close-popup" onclick="history.go(-1)">Fermer</button>
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

<script src="postulation.js"></script>
</html>