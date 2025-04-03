/**
 * Script de validation des formulaires pilote
 * Compatible avec les formulaires d'ajout et de modification
 * Implémente une validation robuste avec détection automatique du mode
 */
document.addEventListener("DOMContentLoaded", function() {
    // Éléments principaux du formulaire
    const form = document.getElementById('add-pilote-form');
    const submitBtn = document.getElementById('submit-btn');
    const confirmationPopup = document.getElementById('confirmationPopup');

    // Détection automatique du mode d'édition
    const isEditMode = form && form.querySelector('input[name="isEditMode"]') !== null;

    // Récupération sécurisée des champs de formulaire
    const nomInput = document.getElementById('nom');
    const prenomInput = document.getElementById('prenom');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const promotionInput = document.getElementById('promotion');
    const campusInput = document.getElementById('campus');
    const telephoneInput = document.getElementById('telephone');

    // Récupération sécurisée des conteneurs d'erreur
    const nomError = document.getElementById('nom-error');
    const prenomError = document.getElementById('prenom-error');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');
    const promotionError = document.getElementById('promotion-error');
    const campusError = document.getElementById('campus-error');
    const telephoneError = document.getElementById('telephone-error');

    // Liste filtrée des entrées existantes pour la validation
    const inputs = [nomInput, prenomInput, emailInput, passwordInput,
        promotionInput, campusInput, telephoneInput].filter(input => input !== null);

    /**
     * Valide le nom ou prénom (sans chiffres, longueur minimale)
     * @param {string} value - Valeur à valider
     * @param {HTMLElement} errorElement - Élément d'affichage d'erreur
     * @return {boolean} Validité du champ
     */
    function validateName(value, errorElement) {
        if (!errorElement) return true;

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
    }

    /**
     * Valide le format d'email
     * @param {string} value - Email à valider
     * @return {boolean} Validité de l'email
     */
    function validateEmail(value) {
        if (!emailError) return true;

        if (!value) {
            emailError.textContent = 'Ce champ est obligatoire';
            return false;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            emailError.textContent = 'Veuillez entrer une adresse email valide';
            return false;
        }
        emailError.textContent = '';
        return true;
    }

    /**
     * Valide le mot de passe avec comportement adaptatif selon le mode
     * @param {string} value - Mot de passe à valider
     * @return {boolean} Validité du mot de passe
     */
    function validatePassword(value) {
        if (!passwordInput || !passwordError) return true;

        // En mode édition, le mot de passe peut être vide
        if (isEditMode && !value) {
            passwordError.textContent = '';
            return true;
        }

        // En mode création, le mot de passe est obligatoire
        if (!value && !isEditMode) {
            passwordError.textContent = 'Ce champ est obligatoire';
            return false;
        }

        // Validation commune de la longueur si une valeur est fournie
        if (value && value.length < 8) {
            passwordError.textContent = 'Le mot de passe doit contenir au moins 8 caractères';
            return false;
        }

        passwordError.textContent = '';
        return true;
    }

    /**
     * Valide le format de téléphone français
     * @param {string} value - Numéro à valider
     * @return {boolean} Validité du numéro
     */
    function validatePhone(value) {
        if (!telephoneError) return true;

        if (!value) {
            telephoneError.textContent = 'Ce champ est obligatoire';
            return false;
        }
        const phoneRegex = /^0[1-9]([ .-]?[0-9]{2}){4}$/;
        if (!phoneRegex.test(value)) {
            telephoneError.textContent = 'Veuillez entrer un numéro de téléphone valide (format français)';
            return false;
        }
        telephoneError.textContent = '';
        return true;
    }

    /**
     * Valide les champs de sélection
     * @param {string} value - Valeur sélectionnée
     * @param {HTMLElement} errorElement - Élément d'affichage d'erreur
     * @return {boolean} Validité de la sélection
     */
    function validateSelect(value, errorElement) {
        if (!errorElement) return true;

        if (!value || value === '') {
            errorElement.textContent = 'Veuillez sélectionner une option';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    /**
     * Vérifie la validité globale du formulaire et active/désactive le bouton
     */
    function checkFormValidity() {
        const isNomValid = validateName(nomInput?.value || '', nomError);
        const isPrenomValid = validateName(prenomInput?.value || '', prenomError);
        const isEmailValid = validateEmail(emailInput?.value || '');
        const isPasswordValid = validatePassword(passwordInput?.value || '');
        const isPromotionValid = validateSelect(promotionInput?.value || '', promotionError);
        const isCampusValid = validateSelect(campusInput?.value || '', campusError);
        const isPhoneValid = validatePhone(telephoneInput?.value || '');

        const formIsValid = isNomValid && isPrenomValid && isEmailValid &&
            isPasswordValid && isPromotionValid &&
            isCampusValid && isPhoneValid;

        if (submitBtn) {
            submitBtn.disabled = !formIsValid;
        }
    }

    // Configuration des écouteurs d'événements pour validation en temps réel
    inputs.forEach(input => {
        if (input) {
            input.addEventListener('input', function() {
                if (input === nomInput || input === prenomInput) {
                    validateName(input.value, input === nomInput ? nomError : prenomError);
                } else if (input === emailInput) {
                    validateEmail(input.value);
                } else if (input === passwordInput) {
                    validatePassword(input.value);
                } else if (input === promotionInput) {
                    validateSelect(input.value, promotionError);
                } else if (input === campusInput) {
                    validateSelect(input.value, campusError);
                } else if (input === telephoneInput) {
                    validatePhone(input.value);
                }

                checkFormValidity();
            });
        }
    });

    // Gestion de la soumission du formulaire
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validation finale avant soumission
            checkFormValidity();

            if (submitBtn && submitBtn.disabled) {
                e.preventDefault();
            }
        });
    }

    // Initialisation spécifique au mode édition
    if (isEditMode) {
        // Initialiser l'état des champs pour validation
        inputs.forEach(input => {
            if (input && input.value) {
                const event = new Event('input', { bubbles: true });
                input.dispatchEvent(event);
            }
        });

        // Activation initiale du bouton en mode édition
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    }
});