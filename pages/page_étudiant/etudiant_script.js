document.addEventListener("DOMContentLoaded", function () {

    const footer = document.getElementById('bottom-text');

    // Function to check if user is at absolute bottom of the page
    const checkScrollPosition = () => {
        // Total height of the document
        const totalHeight = document.documentElement.scrollHeight;

        // Current scroll position
        const currentScroll = window.innerHeight + window.scrollY;

        // Viewport height
        const viewportHeight = window.innerHeight;

        // Check if scrolled to within 10 pixels of the bottom
        if (currentScroll >= totalHeight - 10) {
            footer.classList.add('visible');
        } else {
            footer.classList.remove('visible');
        }
    };

    // Add scroll event listener
    window.addEventListener('scroll', checkScrollPosition);

    // Check on window resize
    window.addEventListener('resize', checkScrollPosition);

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
});
