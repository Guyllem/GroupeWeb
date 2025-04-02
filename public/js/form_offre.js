document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('add-offre-form') || document.getElementById('edit-offre-form');
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

        // Vérifier les compétences
        const competencesValid = validateCompetences();
        isValid = isValid && competencesValid;

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
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Revérifier tout le formulaire au moment de la soumission
            checkFormValidity();

            if (!submitBtn.disabled) {
                // Soumettre le formulaire si valide
                this.submit();
            }
        });
    }

    // Configuration du sélecteur multiple pour les compétences
    function setupMultiSelectCompetences() {
        if (!competencesInput) return;

        // Conteneur pour afficher les compétences sélectionnées sous forme de tags
        let selectedCompetencesContainer = document.createElement('div');
        selectedCompetencesContainer.className = 'selected-skills';
        competencesInput.parentNode.insertBefore(selectedCompetencesContainer, competencesError);

        // Initialiser les tags pour les options déjà sélectionnées au chargement
        let selectedCompetences = Array.from(competencesInput.selectedOptions).map(option => {
            return {
                id: option.value,
                name: option.textContent.trim()
            };
        });

        // Afficher les compétences déjà sélectionnées
        updateCompetencesDisplay();

        // Écouter les changements de sélection
        competencesInput.addEventListener('change', function() {
            // Récupérer toutes les options sélectionnées actuelles
            const currentlySelected = Array.from(this.selectedOptions).map(option => ({
                id: option.value,
                name: option.textContent.trim()
            }));

            // Déterminer quelles compétences ont été ajoutées
            const newlySelected = currentlySelected.filter(item =>
                !selectedCompetences.some(existing => existing.id === item.id)
            );

            // Mettre à jour la liste des compétences sélectionnées
            selectedCompetences = [
                ...selectedCompetences.filter(item =>
                    currentlySelected.some(selected => selected.id === item.id)
                ),
                ...newlySelected
            ];

            // Mettre à jour l'affichage
            updateCompetencesDisplay();

            // Vérifier la validité du formulaire
            checkFormValidity();
        });

        // Fonction pour mettre à jour l'affichage des tags de compétences
        function updateCompetencesDisplay() {
            // Vider le conteneur
            selectedCompetencesContainer.innerHTML = '';

            // Recréer les tags pour chaque compétence sélectionnée
            selectedCompetences.forEach(competence => {
                const tag = document.createElement('div');
                tag.className = 'skill-tag';
                tag.innerHTML = `${competence.name} <button type="button" class="remove-skill" data-id="${competence.id}">×</button>`;

                // Ajouter le gestionnaire d'événement pour supprimer la compétence
                tag.querySelector('.remove-skill').addEventListener('click', function() {
                    const compId = this.getAttribute('data-id');

                    // Retirer la compétence de la liste des sélectionnées
                    selectedCompetences = selectedCompetences.filter(comp => comp.id !== compId);

                    // Désélectionner l'option correspondante dans le select
                    Array.from(competencesInput.options).forEach(option => {
                        if (option.value === compId) {
                            option.selected = false;
                        }
                    });

                    // Mettre à jour l'affichage
                    updateCompetencesDisplay();

                    // Vérifier la validité du formulaire
                    checkFormValidity();
                });

                selectedCompetencesContainer.appendChild(tag);
            });
        }
    }

    // Fonction de validation des compétences
    function validateCompetences() {
        if (!competencesInput) return true;

        // Vérifier qu'au moins une compétence est sélectionnée
        const hasSelectedCompetences = competencesInput.selectedOptions.length > 0;

        if (!hasSelectedCompetences) {
            competencesError.textContent = 'Veuillez sélectionner au moins une compétence';
            return false;
        }

        competencesError.textContent = '';
        return true;
    }

    // Gestion des menus déroulants personnalisés
    function setupCustomDropdowns() {
        const entrepriseToggle = document.getElementById('entreprise-toggle');
        const entrepriseMenu = document.getElementById('entreprise-menu');

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

    function setupCustomCompetencesDropdown() {
        const competencesToggle = document.getElementById('competences-toggle');
        const competencesMenu = document.getElementById('competences-menu');
        const competencesValues = document.getElementById('competences-values');
        const selectedCompetencesContainer = document.getElementById('selected-competences');
        const competencesError = document.getElementById('competences-error');

        if (!competencesToggle || !competencesMenu || !competencesValues || !selectedCompetencesContainer) return;

        // Récupérer les compétences déjà sélectionnées (depuis l'input hidden)
        let selectedCompetences = competencesValues.value ? competencesValues.value.split(',') : [];

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

        // Sélection d'une compétence dans le menu
        document.querySelectorAll('#competences-menu .dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();

                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                // Vérifier si cette compétence est déjà sélectionnée
                if (!selectedCompetences.includes(id)) {
                    // Ajouter l'ID à la liste des compétences sélectionnées
                    selectedCompetences.push(id);

                    // Mettre à jour l'input hidden
                    competencesValues.value = selectedCompetences.join(',');

                    // Créer le tag affiché pour cette compétence
                    const tag = document.createElement('div');
                    tag.className = 'skill-tag';
                    tag.innerHTML = `${name} <button type="button" class="remove-skill" data-id="${id}">×</button>`;

                    // Ajouter l'événement pour supprimer cette compétence
                    tag.querySelector('.remove-skill').addEventListener('click', function() {
                        const skillId = this.getAttribute('data-id');
                        removeCompetence(skillId);
                    });

                    selectedCompetencesContainer.appendChild(tag);

                    // Mettre à jour la validation
                    validateCompetences();
                    checkFormValidity();
                }

                // Fermer le menu déroulant
                competencesMenu.classList.remove('active');

                // Supprimer le backdrop si présent
                const backdrop = document.querySelector('.dropdown-backdrop');
                if (backdrop) backdrop.remove();
            });
        });

        // Configurer les boutons de suppression existants
        document.querySelectorAll('#selected-competences .remove-skill').forEach(btn => {
            btn.addEventListener('click', function() {
                const skillId = this.getAttribute('data-id');
                removeCompetence(skillId);
            });
        });

        // Fonction pour supprimer une compétence
        function removeCompetence(id) {
            // Retirer l'ID de la liste
            selectedCompetences = selectedCompetences.filter(skillId => skillId !== id);

            // Mettre à jour l'input hidden
            competencesValues.value = selectedCompetences.join(',');

            // Supprimer le tag correspondant
            const tagToRemove = Array.from(selectedCompetencesContainer.querySelectorAll('.skill-tag')).find(
                tag => tag.querySelector('.remove-skill').getAttribute('data-id') === id
            );

            if (tagToRemove) {
                tagToRemove.remove();
            }

            // Mettre à jour la validation
            validateCompetences();
            checkFormValidity();
        }

        // Fonction pour valider les compétences sélectionnées
        function validateCompetences() {
            if (selectedCompetences.length === 0) {
                competencesError.textContent = 'Veuillez sélectionner au moins une compétence';
                return false;
            } else {
                competencesError.textContent = '';
                return true;
            }
        }

        // Fermer le menu si on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!competencesToggle.contains(e.target) && !competencesMenu.contains(e.target)) {
                competencesMenu.classList.remove('active');

                // Supprimer le backdrop si présent
                const backdrop = document.querySelector('.dropdown-backdrop');
                if (backdrop) backdrop.remove();
            }
        });

        // Validation initiale
        validateCompetences();
    }

    // Initialiser toutes les fonctionnalités du formulaire
    setupCustomDropdowns();
    setupMultiSelectCompetences();

    // Vérifier la validité initiale du formulaire
    setTimeout(checkFormValidity, 100);
    setupCustomCompetencesDropdown();
});