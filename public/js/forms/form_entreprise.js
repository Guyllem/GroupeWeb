document.addEventListener("DOMContentLoaded", function() {
    // Sélection des éléments du formulaire
    const form = document.getElementById('add-enterprise-form');
    const submitBtn = document.getElementById('submit-btn');
    const descriptionField = document.getElementById('description');
    const charCount = document.getElementById('char-count');

    // Éléments du formulaire
    const nomInput = document.getElementById('nom');
    const emailInput = document.getElementById('email');
    const telephoneInput = document.getElementById('telephone');
    const villeInput = document.getElementById('ville');
    const codePostalInput = document.getElementById('code_postal');
    const adresseInput = document.getElementById('adresse');
    const secteursInput = document.getElementById('secteurs');
    const descriptionInput = document.getElementById('description');
    const effectifInput = document.getElementById('effectif');

    // Messages d'erreur
    const nomError = document.getElementById('nom-error');
    const emailError = document.getElementById('email-error');
    const telephoneError = document.getElementById('telephone-error');
    const villeError = document.getElementById('ville-error');
    const codePostalError = document.getElementById('code_postal-error');
    const secteursError = document.getElementById('secteurs-error');
    const descriptionError = document.getElementById('description-error');

    // Compteur de caractères pour la description
    if (descriptionField && charCount) {
        descriptionField.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Liste des champs requis avec leurs validateurs
    const requiredFields = [
        { input: nomInput, error: nomError, validator: validateText, required: true },
        { input: emailInput, error: emailError, validator: validateEmail, required: true },
        { input: telephoneInput, error: telephoneError, validator: validatePhone, required: false },
        { input: villeInput, error: villeError, validator: validateText, required: true },
        { input: codePostalInput, error: codePostalError, validator: validatePostal, required: true },
        { input: secteursInput, error: secteursError, validator: validateSecteurs, required: false },
        { input: descriptionInput, error: descriptionError, validator: validateDescription, required: false }
    ];

    // Fonctions de validation
    function validateText(value, errorElement, required = true) {
        if (required && (!value || value.trim() === '')) {
            errorElement.textContent = 'Ce champ est obligatoire';
            return false;
        }
        if (value && value.trim().length < 2) {
            errorElement.textContent = 'Ce champ doit contenir au moins 2 caractères';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    function validateEmail(value, errorElement) {
        if (!value || value.trim() === '') {
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

    function validatePhone(value, errorElement) {
        if (!value || value.trim() === '') {
            return true; // Le téléphone n'est pas obligatoire
        }
        const phoneRegex = /^0[1-9]([ .-]?[0-9]{2}){4}$/;
        if (!phoneRegex.test(value)) {
            errorElement.textContent = 'Veuillez entrer un numéro de téléphone valide (format français)';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    function validatePostal(value, errorElement) {
        if (!value || value.trim() === '') {
            errorElement.textContent = 'Ce champ est obligatoire';
            return false;
        }
        const postalRegex = /^[0-9]{5}$/;
        if (!postalRegex.test(value)) {
            errorElement.textContent = 'Le code postal doit contenir 5 chiffres';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    function validateSecteurs(value, errorElement) {
        // Les secteurs ne sont pas obligatoires mais on peut vérifier le format si nécessaire
        if (value && value.trim() !== '') {
            // Vérification basique que les secteurs sont bien séparés par des virgules
            const sectors = value.split(',');
            for (let sector of sectors) {
                if (sector.trim().length < 2) {
                    errorElement.textContent = 'Chaque secteur doit contenir au moins 2 caractères';
                    return false;
                }
            }
        }
        errorElement.textContent = '';
        return true;
    }

    function validateDescription(value, errorElement) {
        if (!value || value.trim() === '') {
            return true; // La description n'est pas obligatoire
        }
        if (value.trim().length > 1000) {
            errorElement.textContent = 'La description ne doit pas dépasser 1000 caractères';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    // Vérification globale de la validité du formulaire
    function checkFormValidity() {
        let isValid = true;

        // Vérifier tous les champs requis
        requiredFields.forEach(field => {
            if (field.input) {
                isValid = field.validator(field.input.value, field.error, field.required) && isValid;
            } else {
                console.warn(`Le champ ${field.input} n'existe pas dans le DOM`);
            }
        });

        // Activer/désactiver le bouton de soumission
        if (submitBtn) {
            submitBtn.disabled = !isValid;
        }

        return isValid;
    }

    // Ajouter les écouteurs d'événements pour la validation en temps réel
    requiredFields.forEach(field => {
        if (field.input) {
            field.input.addEventListener('input', function() {
                field.validator(this.value, field.error, field.required);
                checkFormValidity();
            });

            field.input.addEventListener('blur', function() {
                field.validator(this.value, field.error, field.required);
                checkFormValidity();
            });
        }
    });

    // Validation initiale pour configurer correctement l'état du bouton de soumission
    checkFormValidity();

    // Gérer la soumission du formulaire
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Vérifier la validité du formulaire
            if (checkFormValidity()) {
                // Si tout est valide, soumettre le formulaire
                this.submit();
            }
        });
    }
});