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
    } else {
        console.error("Un des éléments du menu burger est introuvable !");
    }
});
