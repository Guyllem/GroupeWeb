document.addEventListener("DOMContentLoaded", function () {
    const starInputs = document.querySelectorAll('.star-rating input');
    const validateBtn = document.querySelector('.validate-btn');
    const backBtn = document.querySelector('.back-btn');
    const ratingValue = document.querySelector('.rating-value');

    let selectedRating = 0;

    // Gestion des étoiles
    if (starInputs.length > 0) {
        starInputs.forEach(input => {
            input.addEventListener('change', function() {
                selectedRating = this.value;
                if (ratingValue) {
                    ratingValue.textContent = `${selectedRating}/5`;
                }

                // Activer le bouton de validation
                if (validateBtn) {
                    validateBtn.disabled = false;
                }

                // Mise à jour visuelle des étoiles sélectionnées
                updateStarsDisplay(selectedRating);
            });
        });
    }

    // Fonction pour mettre à jour l'affichage des étoiles
    function updateStarsDisplay(rating) {
        // Réinitialiser toutes les étoiles
        starInputs.forEach(input => {
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (label) {
                if (parseInt(input.value) <= parseInt(rating)) {
                    label.classList.add('selected');
                } else {
                    label.classList.remove('selected');
                }
            }
        });
    }

    // Gestion du bouton retour
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            // Redirection vers la page précédente
            window.history.back();
        });
    }


    // Animation sur hover des étoiles
    const starLabels = document.querySelectorAll('.star-rating label');

    if (starLabels.length > 0) {
        starLabels.forEach(label => {
            label.addEventListener('mouseover', function() {
                const rating = this.getAttribute('for').replace('star', '');

                // Effet visuel temporaire au survol
                showTemporaryRating(rating);
            });

            label.addEventListener('mouseout', function() {
                // Rétablir la note sélectionnée et l'affichage lorsque la souris quitte
                updateStarsDisplay(selectedRating);
                // Réinitialiser également le texte d'affichage de la note
                if (ratingValue) {
                    ratingValue.textContent = selectedRating > 0 ? `${selectedRating}/5` : `0/5`;
                }
            });
        });
    }

    function showTemporaryRating(rating) {
        if (ratingValue) {
            ratingValue.textContent = `${rating}/5`;
        }
    }
});