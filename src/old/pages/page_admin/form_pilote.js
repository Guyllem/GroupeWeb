document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('add-eleve-form');
    const submitBtn = document.getElementById('submit-btn');
    const confirmationPopup = document.getElementById('confirmationPopup');

    // Éléments du formulaire
    const nomInput = document.getElementById('nom');
    const prenomInput = document.getElementById('prenom');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const promotionInput = document.getElementById('promotion');
    const villeInput = document.getElementById('ville');
    const telephoneInput = document.getElementById('telephone');

    // Messages d'erreur
    const nomError = document.getElementById('nom-error');
    const prenomError = document.getElementById('prenom-error');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');
    const promotionError = document.getElementById('promotion-error');
    const villeError = document.getElementById('ville-error');
    const telephoneError = document.getElementById('telephone-error');

    // Validation en temps réel pour activer/désactiver le bouton
    const inputs = [nomInput, prenomInput, emailInput, passwordInput,
        promotionInput, villeInput, telephoneInput];

    // Fonction de validation du nom (pas de chiffres)
    function validateName(value, errorElement) {
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

    // Fonction de validation d'email
    function validateEmail(value) {
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

    // Fonction de validation de mot de passe
    function validatePassword(value) {
        if (!value) {
            passwordError.textContent = 'Ce champ est obligatoire';
            return false;
        }
        if (value.length < 8) {
            passwordError.textContent = 'Le mot de passe doit contenir au moins 8 caractères';
            return false;
        }
        passwordError.textContent = '';
        return true;
    }

    // Fonction de validation de téléphone
    function validatePhone(value) {
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

    // Fonction de validation de selection
    function validateSelect(value, errorElement) {
        if (!value || value === '') {
            errorElement.textContent = 'Veuillez sélectionner une option';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    // Fonction pour vérifier si tous les champs sont valides
    function checkFormValidity() {
        const isNomValid = validateName(nomInput.value, nomError);
        const isPrenomValid = validateName(prenomInput.value, prenomError);
        const isEmailValid = validateEmail(emailInput.value);
        const isPasswordValid = validatePassword(passwordInput.value);
        const isPromotionValid = validateSelect(promotionInput.value, promotionError);
        const isVilleValid = validateSelect(villeInput.value, villeError);
        const isPhoneValid = validatePhone(telephoneInput.value);

        const formIsValid = isNomValid && isPrenomValid && isEmailValid &&
            isPasswordValid && isPromotionValid &&
            isVilleValid && isPhoneValid;

        submitBtn.disabled = !formIsValid;
    }

    // Ajouter des écouteurs d'événements pour la validation en temps réel
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (input === nomInput || input === prenomInput) {
                validateName(input.value, input === nomInput ? nomError : prenomError);
            } else if (input === emailInput) {
                validateEmail(input.value);
            } else if (input === passwordInput) {
                validatePassword(input.value);
            } else if (input === promotionInput) {
                validateSelect(input.value, promotionError);
            } else if (input === villeInput) {
                validateSelect(input.value, villeError);
            } else if (input === telephoneInput) {
                validatePhone(input.value);
            }

            checkFormValidity();
        });
    });

    // Gestion de la soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Revérifier tout le formulaire au moment de la soumission
        checkFormValidity();

        if (!submitBtn.disabled) {
            // Ici, vous pouvez ajouter le code pour envoyer les données au serveur
            // Par exemple avec fetch ou XMLHttpRequest

            // Simuler un délai de traitement (à remplacer par l'appel réel au serveur)
            setTimeout(() => {
                // Afficher la popup de confirmation
                confirmationPopup.style.display = 'flex';
            }, 500);
        }
    });
});