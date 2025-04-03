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

    // Gestion du slide-filter
    const filterMenuBtn = document.getElementById('filter-menu');
    const slideFilter = document.getElementById('slide-filter');
    const closeFilterBtn = document.getElementById('close-filter');

    if (filterMenuBtn && slideFilter && closeFilterBtn) {
        // Gestionnaire d'événement principal pour le bouton filtre
        filterMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation(); // Arrête la propagation de l'événement
            slideFilter.classList.add('open');
        });

        if (filterMenuBtn.querySelector('img')) {
            filterMenuBtn.querySelector('img').addEventListener('click', function (e) {
                e.stopPropagation(); // Arrête la propagation
                slideFilter.classList.add('open');
            });
        }

        closeFilterBtn.addEventListener('click', function (e) {
            e.stopPropagation(); // Arrête la propagation
            slideFilter.classList.remove('open');
        });
    }

    // Gestion de la fermeture des boutons de menu
    document.addEventListener('click', function (event) {
        if (slideMenu && !slideMenu.contains(event.target) && event.target !== burgerMenu) {
            slideMenu.classList.remove("open");
        }

        if (slideFilter && !slideFilter.contains(event.target) && event.target !== filterMenuBtn &&
            (filterMenuBtn && !filterMenuBtn.contains(event.target))) {
            slideFilter.classList.remove("open");
        }
    });

    // Gestion du clic pour ouvrir le menu slide
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    const dropdownItems = document.querySelectorAll('.dropdown-item');

    dropdownToggles.forEach(function (dropdownToggle) {
        dropdownToggle.addEventListener('click', function () {
            const dropdown = this.parentElement;
            dropdown.classList.toggle('active'); // Toggle the active class
        });
    });

    // Gestion du clic sur une option de la liste (menu déroulant)
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

    // Fermeture du menu dropdown lorsque l'on clique en dehors
    window.addEventListener('click', function (e) {
        dropdownToggles.forEach(function (dropdownToggle) {
            const dropdown = dropdownToggle.parentElement;
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });
});