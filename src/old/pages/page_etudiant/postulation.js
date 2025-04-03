document.addEventListener("DOMContentLoaded", function() {
    // Éléments du DOM
    const cvInput = document.getElementById('cv-upload');
    const motivationInput = document.getElementById('motivation-upload');
    const validateBtn = document.querySelector('.validate-btn');
    const confirmationPopup = document.getElementById('confirmationPopup');
    const submissionDate = document.getElementById('submissionDate');
    const cvFileName = document.getElementById('cv-file-name');
    const motivationFileName = document.getElementById('motivation-file-name');
    const cvError = document.getElementById('cv-error');
    const motivationError = document.getElementById('motivation-error');

    // Variables pour le suivi de la validation
    let isCVValid = false;
    let isMotivationValid = false;

    // Taille maximum (5Mo en octets)
    const MAX_FILE_SIZE = 5 * 1024 * 1024;

    // Fonction pour valider un fichier
    function validateFile(file, errorElement) {
        // Réinitialiser l'erreur
        errorElement.textContent = '';

        // Vérifier si un fichier est sélectionné
        if (!file) {
            return false;
        }

        // Vérifier l'extension du fichier
        if (!file.name.toLowerCase().endsWith('.pdf')) {
            errorElement.textContent = 'Format de fichier invalide. Seuls les fichiers PDF sont acceptés.';
            return false;
        }

        // Vérifier la taille du fichier
        if (file.size > MAX_FILE_SIZE) {
            errorElement.textContent = 'Fichier trop volumineux. La taille maximum est de 5 Mo.';
            return false;
        }

        return true;
    }

    // Fonction pour mettre à jour l'état du bouton de validation
    function updateSubmitButton() {
        validateBtn.disabled = !(isCVValid && isMotivationValid);
    }

    // Gestion de l'upload du CV
    cvInput.addEventListener('change', function() {
        const file = this.files[0];

        if (file) {
            cvFileName.textContent = file.name;
            isCVValid = validateFile(file, cvError);
        } else {
            cvFileName.textContent = 'Aucun fichier sélectionné';
            isCVValid = false;
        }

        updateSubmitButton();
    });

    // Gestion de l'upload de la lettre de motivation
    motivationInput.addEventListener('change', function() {
        const file = this.files[0];

        if (file) {
            motivationFileName.textContent = file.name;
            isMotivationValid = validateFile(file, motivationError);
        } else {
            motivationFileName.textContent = 'Aucun fichier sélectionné';
            isMotivationValid = false;
        }

        updateSubmitButton();
    });

    // Gestion du bouton de validation
    validateBtn.addEventListener('click', function() {
        if (isCVValid && isMotivationValid) {
            // Ici, on simulerait l'envoi des fichiers à un serveur
            // Dans un cas réel, on utiliserait FormData et fetch/XMLHttpRequest

            // Formatter la date actuelle
            const now = new Date();
            const dateOptions = {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            const formattedDate = now.toLocaleDateString('fr-FR', dateOptions).replace(',', ' à');

            // Afficher la date dans la popup
            submissionDate.textContent = `Envoyé le ${formattedDate}`;

            // Afficher la popup de confirmation
            confirmationPopup.style.display = 'flex';
        }
    });
});