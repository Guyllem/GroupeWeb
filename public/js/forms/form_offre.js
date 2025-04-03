document.addEventListener("DOMContentLoaded", function() {
    // Identification du formulaire (support pour les modes ajout et édition)
    const form = document.getElementById('add-offre-form') || document.getElementById('edit-offre-form');
    const submitBtn = document.getElementById('submit-btn');

    // Éléments principaux
    const descriptionField = document.getElementById('description');
    const charCount = document.getElementById('char-count');

    // Éléments du formulaire (avec vérifications de null pour éviter les erreurs)
    const titreInput = document.getElementById('titre');
    const descriptionInput = document.getElementById('description');
    const remunerationInput = document.getElementById('remuneration');
    const niveauRequisInput = document.getElementById('niveau_requis');
    const dateDebutInput = document.getElementById('date_debut');
    const dureeMinInput = document.getElementById('duree_min');
    const dureeMaxInput = document.getElementById('duree_max');
    const competencesInput = document.getElementById('competences');
    const entrepriseInput = document.getElementById('id_entreprise');
    const selectedSkillsContainer = document.getElementById('selected-skills');

    // Messages d'erreur (avec vérifications de null)
    const titreError = document.getElementById('titre-error');
    const descriptionError = document.getElementById('description-error');
    const remunerationError = document.getElementById('remuneration-error');
    const niveauRequisError = document.getElementById('niveau_requis-error');
    const dateDebutError = document.getElementById('date_debut-error');
    const dureeMinError = document.getElementById('duree_min-error');
    const dureeMaxError = document.getElementById('duree_max-error');
    const competencesError = document.getElementById('competences-error');
    const entrepriseError = document.getElementById('id_entreprise-error');

    // Initialisation du compteur de caractères pour la description
    if (descriptionField && charCount) {
        // Afficher la longueur initiale (important pour le mode édition)
        charCount.textContent = descriptionField.value.length;

        // Mettre à jour lors de la saisie
        descriptionField.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // ===== FONCTIONS DE VALIDATION =====

    // Validation du titre (non vide, longueur minimale)
    function validateTitre() {
        if (!titreInput || !titreError) return true;

        const value = titreInput.value.trim();

        if (!value) {
            titreError.textContent = 'Le titre est obligatoire';
            return false;
        }

        if (value.length < 3) {
            titreError.textContent = 'Le titre doit contenir au moins 3 caractères';
            return false;
        }

        titreError.textContent = '';
        return true;
    }

    // Validation de la description (non vide, longueur min et max)
    function validateDescription() {
        if (!descriptionInput || !descriptionError) return true;

        const value = descriptionInput.value.trim();

        if (!value) {
            descriptionError.textContent = 'La description est obligatoire';
            return false;
        }

        if (value.length < 30) {
            descriptionError.textContent = 'La description doit contenir au moins 30 caractères';
            return false;
        }

        if (value.length > 1000) {
            descriptionError.textContent = 'La description ne doit pas dépasser 1000 caractères';
            return false;
        }

        descriptionError.textContent = '';
        return true;
    }

    // Validation de la rémunération (nombre positif)
    function validateRemuneration() {
        if (!remunerationInput || !remunerationError) return true;

        const value = remunerationInput.value.trim();

        if (!value) {
            remunerationError.textContent = 'La rémunération est obligatoire';
            return false;
        }

        const numValue = parseFloat(value);

        if (isNaN(numValue) || numValue < 0) {
            remunerationError.textContent = 'Veuillez entrer un montant valide';
            return false;
        }

        remunerationError.textContent = '';
        return true;
    }

    // Validation du niveau requis (sélection obligatoire)
    function validateNiveauRequis() {
        if (!niveauRequisInput || !niveauRequisError) return true;

        const value = niveauRequisInput.value;

        if (!value) {
            niveauRequisError.textContent = 'Veuillez sélectionner un niveau d\'étude';
            return false;
        }

        niveauRequisError.textContent = '';
        return true;
    }

    // Validation de la date de début (future et pas trop éloignée)
    function validateDateDebut() {
        if (!dateDebutInput || !dateDebutError) return true;

        const value = dateDebutInput.value;

        if (!value) {
            dateDebutError.textContent = 'La date de début est obligatoire';
            return false;
        }

        const selectedDate = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Normaliser la date actuelle

        if (isNaN(selectedDate.getTime())) {
            dateDebutError.textContent = 'Format de date invalide';
            return false;
        }

        if (selectedDate < today) {
            dateDebutError.textContent = 'La date doit être future';
            return false;
        }

        // Vérifier que la date n'est pas trop loin dans le futur (max 2 ans)
        const twoYearsLater = new Date();
        twoYearsLater.setFullYear(twoYearsLater.getFullYear() + 2);

        if (selectedDate > twoYearsLater) {
            dateDebutError.textContent = 'La date ne peut pas dépasser 2 ans dans le futur';
            return false;
        }

        dateDebutError.textContent = '';
        return true;
    }

    // Validation de la durée minimale (nombre entier positif)
    function validateDureeMin() {
        if (!dureeMinInput || !dureeMinError) return true;

        const value = dureeMinInput.value.trim();

        if (!value) {
            dureeMinError.textContent = 'La durée minimale est obligatoire';
            return false;
        }

        const numValue = parseInt(value);

        if (isNaN(numValue) || numValue <= 0) {
            dureeMinError.textContent = 'Veuillez entrer un nombre entier positif';
            return false;
        }

        if (numValue > 52) {
            dureeMinError.textContent = 'La durée minimale ne peut pas dépasser 52 semaines';
            return false;
        }

        dureeMinError.textContent = '';
        return true;
    }

    // Validation de la durée maximale (optionnelle, mais ≥ durée min si spécifiée)
    function validateDureeMax() {
        if (!dureeMaxInput || !dureeMaxError || !dureeMinInput) return true;

        const maxValue = dureeMaxInput.value.trim();

        // Si non renseignée, c'est valide (car optionnelle)
        if (!maxValue) {
            dureeMaxError.textContent = '';
            return true;
        }

        const parsedMaxValue = parseInt(maxValue);
        const parsedMinValue = parseInt(dureeMinInput.value.trim() || '0');

        if (isNaN(parsedMaxValue) || parsedMaxValue <= 0) {
            dureeMaxError.textContent = 'Veuillez entrer un nombre entier positif';
            return false;
        }

        if (parsedMaxValue > 52) {
            dureeMaxError.textContent = 'La durée maximale ne peut pas dépasser 52 semaines';
            return false;
        }

        if (parsedMinValue > 0 && parsedMaxValue < parsedMinValue) {
            dureeMaxError.textContent = 'La durée maximale doit être supérieure ou égale à la durée minimale';
            return false;
        }

        dureeMaxError.textContent = '';
        return true;
    }

    // Validation de l'entreprise (sélection obligatoire)
    function validateEntreprise() {
        if (!entrepriseInput || !entrepriseError) return true;

        const value = entrepriseInput.value;

        if (!value) {
            entrepriseError.textContent = 'Veuillez sélectionner une entreprise';
            return false;
        }

        entrepriseError.textContent = '';
        return true;
    }

    // Validation des compétences (au moins une sélectionnée)
    function validateCompetences() {
        if (!competencesInput || !competencesError) return true;

        // Vérifier qu'au moins une compétence est sélectionnée
        const hasSelectedCompetences = competencesInput.selectedOptions.length > 0;

        if (!hasSelectedCompetences) {
            competencesError.textContent = 'Veuillez sélectionner au moins une compétence';
            return false;
        }

        competencesError.textContent = '';
        return true;
    }

    // Configuration du multi-select des compétences avec tags visuels
    function setupCompetencesSelect() {
        if (!competencesInput || !selectedSkillsContainer) return;

        // Stocker les compétences sélectionnées
        let selectedCompetences = Array.from(competencesInput.selectedOptions).map(option => ({
            id: option.value,
            name: option.textContent.trim()
        }));

        // Fonction pour mettre à jour l'affichage des tags de compétences
        function updateCompetencesDisplay() {
            // Vider le conteneur
            selectedSkillsContainer.innerHTML = '';

            // Créer un tag pour chaque compétence sélectionnée
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
                    validateCompetences();
                    checkFormValidity();
                });

                selectedSkillsContainer.appendChild(tag);
            });
        }

        // Afficher les compétences déjà sélectionnées initialement
        updateCompetencesDisplay();

        // Écouter les changements de sélection
        competencesInput.addEventListener('change', function() {
            // Récupérer toutes les options sélectionnées actuellement
            const currentlySelected = Array.from(this.selectedOptions).map(option => ({
                id: option.value,
                name: option.textContent.trim()
            }));

            // Déterminer les nouvelles compétences sélectionnées
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

            // Mettre à jour l'affichage et la validation
            updateCompetencesDisplay();
            validateCompetences();
            checkFormValidity();
        });
    }

    // ===== VALIDATION GLOBALE ET ATTACHEMENT DES ÉVÉNEMENTS =====

    // Fonction pour vérifier la validité du formulaire dans son ensemble
    function checkFormValidity() {
        const isValid =
            validateTitre() &&
            validateDescription() &&
            validateRemuneration() &&
            validateNiveauRequis() &&
            validateDateDebut() &&
            validateDureeMin() &&
            validateDureeMax() &&
            validateCompetences() &&
            validateEntreprise();

        // Activer/désactiver le bouton de soumission
        if (submitBtn) {
            submitBtn.disabled = !isValid;
        }

        return isValid;
    }

    // Attachement des validateurs aux événements d'input pour chaque champ
    function attachValidators() {
        // Pour chaque champ, attacher le validateur à l'événement input
        if (titreInput) {
            titreInput.addEventListener('input', function() {
                validateTitre();
                checkFormValidity();
            });

            titreInput.addEventListener('blur', validateTitre);
        }

        if (descriptionInput) {
            descriptionInput.addEventListener('input', function() {
                validateDescription();
                checkFormValidity();
            });

            descriptionInput.addEventListener('blur', validateDescription);
        }

        if (remunerationInput) {
            remunerationInput.addEventListener('input', function() {
                validateRemuneration();
                checkFormValidity();
            });

            remunerationInput.addEventListener('blur', validateRemuneration);
        }

        if (niveauRequisInput) {
            niveauRequisInput.addEventListener('change', function() {
                validateNiveauRequis();
                checkFormValidity();
            });
        }

        if (dateDebutInput) {
            dateDebutInput.addEventListener('input', function() {
                validateDateDebut();
                checkFormValidity();
            });

            dateDebutInput.addEventListener('blur', validateDateDebut);
        }

        if (dureeMinInput) {
            dureeMinInput.addEventListener('input', function() {
                validateDureeMin();
                // Valider aussi la durée max qui dépend de la durée min
                validateDureeMax();
                checkFormValidity();
            });

            dureeMinInput.addEventListener('blur', function() {
                validateDureeMin();
                validateDureeMax();
            });
        }

        if (dureeMaxInput) {
            dureeMaxInput.addEventListener('input', function() {
                validateDureeMax();
                checkFormValidity();
            });

            dureeMaxInput.addEventListener('blur', validateDureeMax);
        }

        if (entrepriseInput) {
            entrepriseInput.addEventListener('change', function() {
                validateEntreprise();
                checkFormValidity();
            });
        }

        // Pour les compétences, cela est géré dans setupCompetencesSelect()
    }

    // Gestion de la soumission du formulaire
    if (form) {
        form.addEventListener('submit', function(e) {
            // Empêcher la soumission automatique
            e.preventDefault();

            // Valider tous les champs
            const isValid = checkFormValidity();

            // Si le formulaire est valide, le soumettre
            if (isValid) {
                this.submit();
            }
        });
    }

    // ===== INITIALISATION =====

    // Initialiser le sélecteur de compétences
    setupCompetencesSelect();

    // Attacher les validateurs aux événements d'input
    attachValidators();

    // Vérifier la validité initiale du formulaire
    // Utiliser un délai pour s'assurer que tout est initialisé
    setTimeout(checkFormValidity, 100);
});