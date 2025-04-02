document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('add-offre-form');
    const submitBtn = document.getElementById('submit-btn');
    const confirmationPopup = document.getElementById('confirmationPopup');
    const descriptionField = document.getElementById('description');
    const charCount = document.getElementById('char-count');

    // Éléments du formulaire
    const titreInput = document.getElementById('titre');
    const descriptionInput = document.getElementById('description');
    const remunerationInput = document.getElementById('remuneration');
    const niveauEtudeInput = document.getElementById('niveau_etude');
    const dateDebutInput = document.getElementById('date_debut');
    const dureeMinInput = document.getElementById('duree_min');
    const dureeMaxInput = document.getElementById('duree_max');
    const competencesInput = document.getElementById('competences');
    const entrepriseInput = document.getElementById('entreprise');
    const localisationInput = document.getElementById('localisation');

    // Messages d'erreur
    const titreError = document.getElementById('titre-error');
    const descriptionError = document.getElementById('description-error');
    const remunerationError = document.getElementById('remuneration-error');
    const niveauEtudeError = document.getElementById('niveau_etude-error');
    const dateDebutError = document.getElementById('date_debut-error');
    const dureeMinError = document.getElementById('duree_min-error');
    const dureeMaxError = document.getElementById('duree_max-error');
    const competencesError = document.getElementById('competences-error');
    const entrepriseError = document.getElementById('entreprise-error');
    const localisationError = document.getElementById('localisation-error');

    // Compteur de caractères pour la description
    descriptionField.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });

    // Liste de tous les champs obligatoires à valider
    const requiredInputs = [
        { input: titreInput, error: titreError, validator: validateText },
        { input: descriptionInput, error: descriptionError, validator: validateDescription },
        { input: remunerationInput, error: remunerationError, validator: validateRemuneration },
        { input: niveauEtudeInput, error: niveauEtudeError, validator: validateSelect },
        { input: dateDebutInput, error: dateDebutError, validator: validateDate },
        { input: dureeMinInput, error: dureeMinError, validator: validateDuree },
        { input: entrepriseInput, error: entrepriseError, validator: validateSelect },
        { input: localisationInput, error: localisationError, validator: validateText }
    ];

    // Validation du titre et autres champs texte (non vide, longueur minimale)
    function validateText(value, errorElement) {
        if (!value || value.trim() === '') {
            errorElement.textContent = 'Ce champ est obligatoire';
            return false;
        }
        if (value.trim().length < 3) {
            errorElement.textContent = 'Ce champ doit contenir au moins 3 caractères';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    // Validation de la description (non vide, longueur minimale et maximale)
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

    // Validation de la rémunération (nombre positif)
    function validateRemuneration(value, errorElement) {
        if (!value || value === '') {
            errorElement.textContent = 'Ce champ est obligatoire';
            return false;
        }
        const numValue = parseFloat(value);
        if (isNaN(numValue) || numValue < 0) {
            errorElement.textContent = 'Veuillez entrer un montant valide';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    // Validation pour les listes déroulantes
    function validateSelect(value, errorElement) {
        if (!value || value === '') {
            errorElement.textContent = 'Veuillez sélectionner une option';
            return false;
        }
        errorElement.textContent = '';
        return true;
    }

    // Validation de la date (doit être future)
    function validateDate(value, errorElement) {
        if (!value || value === '') {
            errorElement.textContent = 'Ce champ est obligatoire';
            return false;
        }

        const selectedDate = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Normaliser la date actuelle

        if (selectedDate < today) {
            errorElement.textContent = 'La date doit être future';
            return false;
        }

        // Vérifier que la date n'est pas trop loin dans le futur (ex: max 2 ans)
        const twoYearsLater = new Date();
        twoYearsLater.setFullYear(twoYearsLater.getFullYear() + 2);

        if (selectedDate > twoYearsLater) {
            errorElement.textContent = 'La date ne peut pas dépasser 2 ans dans le futur';
            return false;
        }

        errorElement.textContent = '';
        return true;
    }

    // Validation de la durée (nombre entier positif)
    function validateDuree(value, errorElement) {
        if (!value || value === '') {
            errorElement.textContent = 'Ce champ est obligatoire';
            return false;
        }

        const numValue = parseInt(value);
        if (isNaN(numValue) || numValue <= 0) {
            errorElement.textContent = 'Veuillez entrer un nombre entier positif';
            return false;
        }

        errorElement.textContent = '';
        return true;
    }

    // Validation spécifique pour la durée maximale (optionnelle, mais doit être >= durée minimale)
    function validateDureeMax() {
        const minValue = parseInt(dureeMinInput.value);
        const maxValue = parseInt(dureeMaxInput.value);

        // Si la durée max est vide, c'est valide car c'est optionnel
        if (!dureeMaxInput.value || dureeMaxInput.value === '') {
            dureeMaxError.textContent = '';
            return true;
        }

        if (isNaN(maxValue) || maxValue <= 0) {
            dureeMaxError.textContent = 'Veuillez entrer un nombre entier positif';
            return false;
        }

        if (maxValue < minValue) {
            dureeMaxError.textContent = 'La durée maximale doit être supérieure ou égale à la durée minimale';
            return false;
        }

        dureeMaxError.textContent = '';
        return true;
    }

    // Fonction pour vérifier la validité de tout le formulaire
    function checkFormValidity() {
        let isValid = true;

        // Vérifier tous les champs obligatoires
        requiredInputs.forEach(item => {
            const fieldValid = item.validator(item.input.value, item.error);
            isValid = isValid && fieldValid;
        });

        // Vérifier la durée maximale (règle spéciale)
        const dureeMaxValid = validateDureeMax();
        isValid = isValid && dureeMaxValid;

        // Activer/désactiver le bouton de soumission
        submitBtn.disabled = !isValid;
    }

    // Ajout d'écouteurs d'événements pour validation en temps réel
    requiredInputs.forEach(item => {
        item.input.addEventListener('input', function() {
            item.validator(this.value, item.error);
            checkFormValidity();
        });

        item.input.addEventListener('blur', function() {
            item.validator(this.value, item.error);
            checkFormValidity();
        });
    });

    // Validation spéciale pour la durée maximale
    dureeMaxInput.addEventListener('input', function() {
        validateDureeMax();
        checkFormValidity();
    });

    dureeMinInput.addEventListener('change', function() {
        validateDureeMax();
        checkFormValidity();
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
            }, 500);
        }
    });

    // Gestion du menu des compétences
    const competencesSelect = document.getElementById('competences');

    // Créer un tableau pour stocker les compétences sélectionnées
    let selectedCompetences = [];

    // Écouter les changements dans le menu déroulant
    competencesSelect.addEventListener('change', function() {
        // Récupérer l'option sélectionnée
        const selectedOption = this.options[this.selectedIndex];
        const selectedValue = selectedOption.value;

        // Si une compétence valide est sélectionnée
        if (selectedValue && !selectedCompetences.includes(selectedValue)) {
            // Ajouter à la liste des compétences sélectionnées
            selectedCompetences.push(selectedValue);

            // Afficher les compétences sélectionnées
            updateCompetencesList();

            // Réinitialiser la sélection
            this.selectedIndex = 0;
        }

        // Valider le champ
        validateCompetences();
    });

    // Fonction pour mettre à jour l'affichage des compétences
    function updateCompetencesList() {
        // Créer ou obtenir la liste des compétences sélectionnées
        let competencesList = document.getElementById('selected-competences');

        if (!competencesList) {
            competencesList = document.createElement('div');
            competencesList.id = 'selected-competences';
            competencesList.className = 'selected-skills';
            competencesSelect.parentNode.insertBefore(competencesList, competencesError);
        }

        // Vider et reconstruire la liste
        competencesList.innerHTML = '';

        selectedCompetences.forEach(competence => {
            const tag = document.createElement('span');
            tag.className = 'skill-tag';
            tag.innerHTML = `${competence} <button type="button" class="remove-skill" data-value="${competence}">×</button>`;
            competencesList.appendChild(tag);
        });

        // Ajouter les écouteurs d'événements pour la suppression
        document.querySelectorAll('.remove-skill').forEach(btn => {
            btn.addEventListener('click', function() {
                const competence = this.getAttribute('data-value');
                selectedCompetences = selectedCompetences.filter(item => item !== competence);
                updateCompetencesList();
                validateCompetences();
            });
        });
    }

    // Fonction de validation des compétences
    function validateCompetences() {
        // Supprimez la validation de présence
        competencesError.textContent = '';
        return true; // Toujours valide car non obligatoire
    }

    // Gestion des menus déroulants personnalisés
    function setupCustomDropdowns() {
        const entrepriseToggle = document.getElementById('entreprise-toggle');
        const entrepriseMenu = document.getElementById('entreprise-menu');
        const entrepriseInput = document.getElementById('entreprise');
        const entrepriseError = document.getElementById('entreprise-error');

        if (entrepriseToggle && entrepriseMenu) {
            // Ouvrir/fermer le menu déroulant
            entrepriseToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                entrepriseMenu.classList.toggle('active');

                // Créer un backdrop pour mobile
                if (entrepriseMenu.classList.contains('active') && window.innerWidth <= 768) {
                    const backdrop = document.createElement('div');
                    backdrop.className = 'dropdown-backdrop';
                    document.body.appendChild(backdrop);

                    backdrop.addEventListener('click', function() {
                        entrepriseMenu.classList.remove('active');
                        this.remove();
                    });
                }
            });

            // Gestion de la sélection d'une option
            document.querySelectorAll('#entreprise-menu .custom-dropdown-item').forEach(item => {
                item.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const text = this.textContent;

                    entrepriseInput.value = value;
                    entrepriseToggle.textContent = text;
                    entrepriseMenu.classList.remove('active');

                    // Retirer le backdrop si présent
                    const backdrop = document.querySelector('.dropdown-backdrop');
                    if (backdrop) backdrop.remove();

                    // Valider le champ
                    validateSelect(value, entrepriseError);
                    checkFormValidity();
                });
            });

            // Fermer le menu si on clique ailleurs
            document.addEventListener('click', function(e) {
                if (!entrepriseToggle.contains(e.target) && !entrepriseMenu.contains(e.target)) {
                    entrepriseMenu.classList.remove('active');

                    // Retirer le backdrop si présent
                    const backdrop = document.querySelector('.dropdown-backdrop');
                    if (backdrop) backdrop.remove();
                }
            });
        }
    }

    // Initialiser les dropdowns personnalisés
    setupCustomDropdowns();

    // Gestion du menu déroulant des compétences
    function setupCompetencesDropdown() {
        const competencesToggle = document.getElementById('competences-toggle');
        const competencesMenu = document.getElementById('competences-menu');
        const competencesValues = document.getElementById('competences-values');
        const selectedCompetences = document.getElementById('selected-competences');

        // Tableau pour stocker les compétences sélectionnées
        let selectedItems = [];

        // Initialiser le tableau avec les compétences déjà sélectionnées
        document.querySelectorAll('#selected-competences .skill-tag').forEach(tag => {
            const id = tag.querySelector('.remove-skill').getAttribute('data-id');
            const name = tag.textContent.trim().replace('×', '').trim();
            selectedItems.push({ id, name });
        });

        // Mettre à jour le champ caché avec les valeurs sélectionnées
        function updateHiddenField() {
            competencesValues.value = selectedItems.map(item => item.id).join(',');
        }

        // Initialiser le champ caché
        updateHiddenField();

        // Ouvrir/fermer le menu déroulant
        competencesToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            competencesMenu.classList.toggle('active');

            // Ajouter un backdrop sur mobile
            if (competencesMenu.classList.contains('active') && window.innerWidth <= 768) {
                const backdrop = document.createElement('div');
                backdrop.className = 'dropdown-backdrop';
                document.body.appendChild(backdrop);

                backdrop.addEventListener('click', function() {
                    competencesMenu.classList.remove('active');
                    this.remove();
                });
            }
        });

        // Sélection d'une compétence
        document.querySelectorAll('#competences-menu .dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();

                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                // Vérifier si la compétence est déjà sélectionnée
                if (!selectedItems.some(item => item.id === id)) {
                    // Ajouter à la liste des compétences sélectionnées
                    selectedItems.push({ id, name });

                    // Créer et ajouter le tag
                    const tag = document.createElement('span');
                    tag.className = 'skill-tag';
                    tag.innerHTML = `${name} <button type="button" class="remove-skill" data-id="${id}">×</button>`;
                    selectedCompetences.appendChild(tag);

                    // Ajouter l'événement de suppression
                    tag.querySelector('.remove-skill').addEventListener('click', function() {
                        const skillId = this.getAttribute('data-id');
                        selectedItems = selectedItems.filter(item => item.id !== skillId);
                        this.parentElement.remove();
                        updateHiddenField();
                    });

                    // Mettre à jour le champ caché
                    updateHiddenField();
                }

                // Fermer le menu
                competencesMenu.classList.remove('active');

                // Supprimer le backdrop si présent
                const backdrop = document.querySelector('.dropdown-backdrop');
                if (backdrop) backdrop.remove();
            });
        });

        // Gérer les boutons de suppression existants
        document.querySelectorAll('#selected-competences .remove-skill').forEach(btn => {
            btn.addEventListener('click', function() {
                const skillId = this.getAttribute('data-id');
                selectedItems = selectedItems.filter(item => item.id !== skillId);
                this.parentElement.remove();
                updateHiddenField();
            });
        });

        // Fermer le menu si on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!competencesToggle.contains(e.target) && !competencesMenu.contains(e.target)) {
                competencesMenu.classList.remove('active');

                // Supprimer le backdrop si présent
                const backdrop = document.querySelector('.dropdown-backdrop');
                if (backdrop) backdrop.remove();
            }
        });
    }

    // Initialiser le dropdown des compétences
    setupCompetencesDropdown();
});