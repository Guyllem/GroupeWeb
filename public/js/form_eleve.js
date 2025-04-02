document.addEventListener("DOMContentLoaded", function() {
    // Stratégie de détection des formulaires avec support pour add/edit
    const addForm = document.getElementById('add-student-form');
    const editForm = document.getElementById('edit-student-form');
    const form = addForm || editForm;

    if (!form) return; // Sortir si aucun formulaire n'est présent

    const isEditMode = !!editForm;
    const formType = isEditMode ? 'edit' : 'add';

    const submitBtn = document.getElementById('submit-btn');
    const confirmationPopup = document.getElementById('confirmationPopup');

    // Sélection adaptative des champs selon le contexte
    const nomInput = document.getElementById('nom');
    const prenomInput = document.getElementById('prenom');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password'); // Peut être null en mode édition
    const promotionInput = document.getElementById('promotion');
    const campusInput = document.getElementById('campus');
    const telephoneInput = document.getElementById('telephone');

    // Mappage des champs aux messages d'erreur
    const errorElements = {
        nom: document.getElementById('nom-error'),
        prenom: document.getElementById('prenom-error'),
        email: document.getElementById('email-error'),
        password: document.getElementById('password-error'),
        promotion: document.getElementById('promotion-error'),
        campus: document.getElementById('campus-error'),
        telephone: document.getElementById('telephone-error')
    };

    // Configuration spécifique selon le type de formulaire
    const formConfig = {
        add: {
            requiredInputs: [nomInput, prenomInput, emailInput, passwordInput, promotionInput, campusInput, telephoneInput],
            validationRules: {
                password: true
            }
        },
        edit: {
            requiredInputs: [nomInput, prenomInput, emailInput, promotionInput, campusInput, telephoneInput],
            validationRules: {
                password: false
            }
        }
    };

    // Liste des champs à valider pour ce formulaire
    const inputs = formConfig[formType].requiredInputs.filter(input => input !== null);

    // Validation par type de champ
    const validators = {
        name: function(value, errorElement) {
            if (!value) {
                errorElement.textContent = 'Ce champ est obligatoire';
                return false;
            }
            if (/\d/.test(value)) {
                errorElement.textContent = 'Le nom ne doit pas contenir de chiffres';
                return false;
            }
            if (value.length < 2) {
                errorElement.textContent = 'Le nom doit contenir au moins 2 caractères';
                return false;
            }
            errorElement.textContent = '';
            return true;
        },

        email: function(value, errorElement) {
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
        },

        password: function(value, errorElement) {
            if (!value) {
                errorElement.textContent = 'Ce champ est obligatoire';
                return false;
            }
            if (value.length < 8) {
                errorElement.textContent = 'Le mot de passe doit contenir au moins 8 caractères';
                return false;
            }
            errorElement.textContent = '';
            return true;
        },

        phone: function(value, errorElement) {
            if (!value) {
                errorElement.textContent = 'Ce champ est obligatoire';
                return false;
            }
            const phoneRegex = /^0[1-9]([ .-]?[0-9]{2}){4}$/;
            if (!phoneRegex.test(value)) {
                errorElement.textContent = 'Veuillez entrer un numéro de téléphone valide (format français)';
                return false;
            }
            errorElement.textContent = '';
            return true;
        },

        select: function(value, errorElement) {
            if (!value || value === '') {
                errorElement.textContent = 'Veuillez sélectionner une option';
                return false;
            }
            errorElement.textContent = '';
            return true;
        }
    };

    // Fonction centrale de validation
    function checkFormValidity() {
        let isValid = true;

        // Validation du nom et prénom
        isValid = validators.name(nomInput.value, errorElements.nom) && isValid;
        isValid = validators.name(prenomInput.value, errorElements.prenom) && isValid;

        // Validation de l'email
        isValid = validators.email(emailInput.value, errorElements.email) && isValid;

        // Validation du mot de passe (uniquement en mode ajout)
        if (formConfig[formType].validationRules.password && passwordInput) {
            isValid = validators.password(passwordInput.value, errorElements.password) && isValid;
        }

        // Validation des sélecteurs
        isValid = validators.select(promotionInput.value, errorElements.promotion) && isValid;
        isValid = validators.select(campusInput.value, errorElements.campus) && isValid;

        // Validation du téléphone
        isValid = validators.phone(telephoneInput.value, errorElements.telephone) && isValid;

        submitBtn.disabled = !isValid;
        return isValid;
    }

    // Application des écouteurs d'événements
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (input === nomInput || input === prenomInput) {
                validators.name(input.value, input === nomInput ? errorElements.nom : errorElements.prenom);
            } else if (input === emailInput) {
                validators.email(input.value, errorElements.email);
            } else if (input === passwordInput) {
                validators.password(input.value, errorElements.password);
            } else if (input === promotionInput) {
                validators.select(input.value, errorElements.promotion);
            } else if (input === campusInput) {
                validators.select(input.value, errorElements.campus);
            } else if (input === telephoneInput) {
                validators.phone(input.value, errorElements.telephone);
            }

            checkFormValidity();
        });
    });

    // Activation initiale des validations pour les formulaires d'édition
    if (isEditMode) {
        // Simuler des événements d'entrée pour déclencher la validation initiale
        inputs.forEach(input => {
            const event = new Event('input', { bubbles: true });
            input.dispatchEvent(event);
        });
    }

    // Gestion de la soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Vérification finale avant soumission
        if (checkFormValidity()) {
            // En production, soumettez ici ou effectuez une requête AJAX
            // form.submit();

            // Pour la démo, afficher la popup de confirmation
            setTimeout(() => {
                confirmationPopup.style.display = 'flex';
            }, 500);
        }
    });
});