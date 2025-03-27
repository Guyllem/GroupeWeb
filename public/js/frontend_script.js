document.addEventListener("DOMContentLoaded", function () {

    // Gestion du menu burger
    const burgerMenu = document.getElementById("burger-menu");
    const slideMenu = document.getElementById("slide-menu");
    const closeMenu = document.getElementById("close-menu");

    if (burgerMenu && slideMenu && closeMenu) {
        burgerMenu.addEventListener("click", function () {
            slideMenu.classList.add("open");
        });

        closeMenu.addEventListener("click", function () {
            slideMenu.classList.remove("open");
        });

        document.addEventListener("click", function (event) {
            if (!slideMenu.contains(event.target) && event.target !== burgerMenu) {
                slideMenu.classList.remove("open");
            }
        });
    }

    const filterMenuBtn = document.getElementById('filter-menu');
    const slideFilter = document.getElementById('slide-filter');
    const closeFilterBtn = document.getElementById('close-filter');

    if (filterMenuBtn && slideFilter && closeFilterBtn) {
        // Gestionnaire d'événement principal pour le bouton filtre
        filterMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation(); // Arrête la propagation de l'événement
            slideFilter.classList.add('open');
        });

        filterMenuBtn.querySelector('img').addEventListener('click', function (e) {
            e.stopPropagation(); // Arrête la propagation
            slideFilter.classList.add('open');
        });

        closeFilterBtn.addEventListener('click', function (e) {
            e.stopPropagation(); // Arrête la propagation
            slideFilter.classList.remove('open');
        });
    }

    // Fermeture des menus en cliquant en dehors
    document.addEventListener('click', function (event) {
        if (slideMenu && !slideMenu.contains(event.target) && event.target !== burgerMenu) {
            slideMenu.classList.remove("open");
        }

        if (slideFilter && !slideFilter.contains(event.target) && event.target !== filterMenuBtn && !filterMenuBtn.contains(event.target)) {
            slideFilter.classList.remove("open");
        }
    });

    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    const dropdownItems = document.querySelectorAll('.dropdown-item');

    // Gestion du clic pour ouvrir le menu
    dropdownToggles.forEach(function (dropdownToggle) {
        dropdownToggle.addEventListener('click', function () {
            const dropdown = this.parentElement;
            dropdown.classList.toggle('active'); // Toggle the active class
        });
    });

    // Gestion du clic sur une option de la liste
    dropdownItems.forEach(function (dropdownItem) {
        dropdownItem.addEventListener('click', function (e) {
            e.preventDefault();  // Empêche le comportement par défaut du lien
            const dropdown = this.closest('.dropdown'); // Récupère le parent
            const toggleButton = dropdown.querySelector('.dropdown-toggle'); // Le bouton à modifier

            // Met à jour le texte du bouton avec l'option sélectionnée
            toggleButton.textContent = this.getAttribute('data-value');

            // Ferme le menu
            dropdown.classList.remove('active');
        });
    });

    // Fermeture du menu si on clique en dehors
    window.addEventListener('click', function (e) {
        dropdownToggles.forEach(function (dropdownToggle) {
            const dropdown = dropdownToggle.parentElement;
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });

    // Éléments du DOM
    const starInputs = document.querySelectorAll('.star-rating input');
    const validateBtn = document.querySelector('.validate-btn');
    const backBtn = document.querySelector('.back-btn');
    const ratingValue = document.querySelector('.rating-value');
    const confirmationPopup = document.getElementById('confirmationPopup');
    const closePopupBtn = document.querySelector('.close-popup');

    let selectedRating = 0;

    // Gestion des étoiles
    starInputs.forEach(input => {
        input.addEventListener('change', function() {
            selectedRating = this.value;
            ratingValue.textContent = `${selectedRating}/5`;

            // Activer le bouton de validation
            validateBtn.disabled = false;

            // Mise à jour visuelle des étoiles sélectionnées
            updateStarsDisplay(selectedRating);
        });
    });

    // Fonction pour mettre à jour l'affichage des étoiles
    function updateStarsDisplay(rating) {
        // Réinitialiser toutes les étoiles
        starInputs.forEach(input => {
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (parseInt(input.value) <= parseInt(rating)) {
                label.classList.add('selected');
            } else {
                label.classList.remove('selected');
            }
        });
    }

    // Gestion du bouton retour
    backBtn.addEventListener('click', function() {
        // Redirection vers la page précédente
        window.history.back();
    });

    // Gestion du bouton valider
    validateBtn.addEventListener('click', function() {
        if (selectedRating > 0) {
            // Ici, vous pouvez ajouter le code pour envoyer la note à votre backend
            // Par exemple avec une requête AJAX ou fetch

            // Simulation d'envoi (à remplacer par votre logique d'envoi réelle)
            console.log(`Note envoyée: ${selectedRating}/5`);

            // Afficher la popup de confirmation
            confirmationPopup.style.display = 'flex';
        }
    });


    // Animation sur hover des étoiles
    const starLabels = document.querySelectorAll('.star-rating label');

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
            ratingValue.textContent = selectedRating > 0 ? `${selectedRating}/5` : `0/5`;
        });
    });

    function showTemporaryRating(rating) {
        ratingValue.textContent = `${rating}/5`;
    }
});
