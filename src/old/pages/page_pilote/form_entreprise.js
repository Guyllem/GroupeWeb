document.addEventListener("DOMContentLoaded", function() {
    // Correction des IDs dans le formulaire (il faudra aussi corriger dans le HTML)
    const form = document.getElementById('add-offre-form');
    const submitBtn = document.getElementById('submit-btn');
    const confirmationPopup = document.getElementById('confirmationPopup');
    const descriptionField = document.getElementById('description');
    const charCount = document.getElementById('char-count');

    // Éléments du formulaire avec IDs corrigés
    const nomInput = document.getElementById('nom');
    const secteurInput = document.getElementById('secteur');
    const emailInput = document.getElementById('email');
    const telephoneInput = document.getElementById('telephone');
    const descriptionInput = document.getElementById('description');
    const localisationInput = document.getElementById('localisation');

    // Messages d'erreur avec IDs corrigés
    const nomError = document.getElementById('nom-error');
    const secteurError = document.getElementById('secteur-error');
    const emailError = document.getElementById('email-error');
    const telephoneError = document.getElementById('telephone-error');
    const descriptionError = document.getElementById('description-error');
    const localisationError = document.getElementById('localisation-error');

    // Compteur de caractères pour la description
    descriptionField.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });

    // Liste de tous les champs à valider
    const requiredInputs = [
        { input: nomInput, error: nomError, validator: validateText },
        { input: secteurInput, error: secteurError, validator: validateText },
        { input: emailInput, error: emailError, validator: validateEmail },
        { input: telephoneInput, error: telephoneError, validator: validatePhone },
        { input: descriptionInput, error: descriptionError, validator: validateDescription },
        { input: localisationInput, error: localisationError, validator: validateText }
    ];

    // Validation du nom et secteur (non vide, longueur minimale)
    function validateText(value, errorElement) {
        if (!value || value.trim() === '') {
            errorElement.textContent = 'Ce champ est obligatoire';
            return false;
        }
        if (value.trim().length < 2) {
            errorElement.textContent = 'Ce champ doit contenir au moins 2 caractères';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    // Validation de la description
    function validateDescription(value, errorElement) {
        if (!value || value.trim() === '') {
            errorElement.textContent = 'Ce champ est obligatoire';
            return false;
        }
        if (value.trim().length < 30) {
            errorElement.textContent = 'La description doit contenir au moins 30 caractères';
            return false;
        }
        if (value.trim().length > 1000) {
            errorElement.textContent = 'La description ne doit pas dépasser 1000 caractères';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    // Validation d'email
    function validateEmail(value, errorElement) {
        if (!value) {
            errorElement.textContent = 'Ce champ est obligatoire';
            return false;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            errorElement.textContent = 'Veuillez entrer une adresse email valide';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    // Validation de téléphone
    function validatePhone(value, errorElement) {
        if (!value) {
            errorElement.textContent = 'Ce champ est obligatoire';
            return false;
        }
        // Format français (accepte différentes notations)
        const phoneRegex = /^0[1-9]([ .-]?[0-9]{2}){4}$/;
        if (!phoneRegex.test(value)) {
            errorElement.textContent = 'Veuillez entrer un numéro de téléphone valide (format français)';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    // Fonction pour vérifier la validité du formulaire complet
    function checkFormValidity() {
        let isValid = true;

        // Vérifier tous les champs obligatoires
        requiredInputs.forEach(item => {
            if (item.input) { // Vérification que l'élément existe
                const fieldValid = item.validator(item.input.value, item.error);
                isValid = isValid && fieldValid;
            } else {
                isValid = false; // Si un élément n'existe pas, le formulaire n'est pas valide
            }
        });

        // Activer/désactiver le bouton de soumission
        submitBtn.disabled = !isValid;
    }

    // Ajout d'écouteurs d'événements pour validation en temps réel
    requiredInputs.forEach(item => {
        if (item.input) { // Vérification que l'élément existe
            item.input.addEventListener('input', function() {
                item.validator(this.value, item.error);
                checkFormValidity();
            });

            item.input.addEventListener('blur', function() {
                item.validator(this.value, item.error);
                checkFormValidity();
            });
        }
    });

    // Gestion de la soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Revérifier tout le formulaire au moment de la soumission
        checkFormValidity();

        if (!submitBtn.disabled) {
            // Simulation d'envoi au serveur (à remplacer par l'appel réel)
            setTimeout(() => {
                // Afficher la popup de confirmation
                confirmationPopup.style.display = 'flex';
                confirmationPopup.querySelector('.popup-content p').textContent = "L'entreprise a bien été ajoutée";
            }, 500);
        }
    });
});