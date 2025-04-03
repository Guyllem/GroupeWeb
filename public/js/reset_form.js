/**
 * Script de validation pour le formulaire de réinitialisation de mot de passe
 *
 * Fonctionnalités:
 * - Validation en temps réel de la complexité du mot de passe
 * - Vérification de correspondance des deux champs
 * - Activation conditionnelle du bouton de soumission
 * - Feedback visuel sur les critères de validation
 */
document.addEventListener("DOMContentLoaded", function() {
    // Éléments du formulaire
    const form = document.getElementById('reset-password-form');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submit-btn');

    // Éléments de feedback visuel
    const passwordError = document.getElementById('password-error');
    const confirmPasswordError = document.getElementById('confirm-password-error');
    const lengthCheck = document.getElementById('length-check');
    const matchCheck = document.getElementById('match-check');

    // Constantes de validation
    const MIN_PASSWORD_LENGTH = 8;

    /**
     * Vérifie la validité du mot de passe principal
     * @returns {boolean} True si le mot de passe est valide
     */
    function validatePassword() {
        const password = passwordInput.value;

        // Vérification de la longueur minimale
        if (!password || password.length < MIN_PASSWORD_LENGTH) {
            passwordError.textContent = `Le mot de passe doit contenir au moins ${MIN_PASSWORD_LENGTH} caractères`;
            lengthCheck.classList.remove('valid');
            return false;
        }

        // Mot de passe valide
        passwordError.textContent = '';
        lengthCheck.classList.add('valid');
        return true;
    }

    /**
     * Vérifie que les deux mots de passe correspondent
     * @returns {boolean} True si les mots de passe correspondent
     */
    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Vérification si le champ de confirmation est vide
        if (!confirmPassword) {
            confirmPasswordError.textContent = 'Veuillez confirmer le mot de passe';
            matchCheck.classList.remove('valid');
            return false;
        }

        // Vérification de correspondance
        if (password !== confirmPassword) {
            confirmPasswordError.textContent = 'Les mots de passe ne correspondent pas';
            matchCheck.classList.remove('valid');
            return false;
        }

        // Mots de passe correspondent
        confirmPasswordError.textContent = '';
        matchCheck.classList.add('valid');
        return true;
    }

    /**
     * Vérifie la validité globale du formulaire et active/désactive le bouton
     */
    function checkFormValidity() {
        const isPasswordValid = validatePassword();
        const doPasswordsMatch = validatePasswordMatch();

        // Activer/désactiver le bouton de soumission
        submitBtn.disabled = !(isPasswordValid && doPasswordsMatch);
    }

    // Initialiser les styles des indicateurs de validation
    function initValidationStyles() {
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .password-requirements {
                    margin: 15px 0;
                    padding: 15px;
                    background-color: #f8f9fa;
                    border-radius: 5px;
                    border-left: 3px solid #3F51B5;
                }
                .password-requirements p {
                    margin-top: 0;
                    color: #3F51B5;
                    font-weight: bold;
                }
                .password-requirements ul {
                    padding-left: 20px;
                    margin-bottom: 0;
                }
                .password-requirements li {
                    color: #6c757d;
                    margin-bottom: 5px;
                    position: relative;
                    list-style-type: none;
                }
                .password-requirements li:before {
                    content: "✖";
                    color: #dc3545;
                    margin-right: 8px;
                }
                .password-requirements li.valid:before {
                    content: "✓";
                    color: #28a745;
                }
                .password-requirements li.valid {
                    color: #28a745;
                    font-weight: 500;
                }
            </style>
        `);
    }

    // Ajouter les écouteurs d'événements pour la validation en temps réel
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePassword();
            if (confirmPasswordInput.value) {
                validatePasswordMatch();
            }
            checkFormValidity();
        });

        passwordInput.addEventListener('blur', function() {
            validatePassword();
            checkFormValidity();
        });
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            validatePasswordMatch();
            checkFormValidity();
        });

        confirmPasswordInput.addEventListener('blur', function() {
            validatePasswordMatch();
            checkFormValidity();
        });
    }

    // Validation à la soumission du formulaire
    if (form) {
        form.addEventListener('submit', function(e) {
            // Forcer une validation complète avant soumission
            const isPasswordValid = validatePassword();
            const doPasswordsMatch = validatePasswordMatch();

            // Bloquer la soumission si le formulaire est invalide
            if (!(isPasswordValid && doPasswordsMatch)) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Initialiser les styles et l'état du formulaire
    initValidationStyles();
    checkFormValidity();
});