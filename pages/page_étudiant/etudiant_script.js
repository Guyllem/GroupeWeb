document.addEventListener("DOMContentLoaded", function () {
    console.log("Script chargé et DOM prêt !");
    // Gestion du footer
    const footer = document.getElementById("bottom-text");

    const checkScrollPosition = () => {
        const totalHeight = document.documentElement.scrollHeight;
        const currentScroll = window.innerHeight + window.scrollY;

        if (currentScroll >= totalHeight - 10) {
            footer.classList.add("visible");
        } else {
            footer.classList.remove("visible");
        }
    };

    window.addEventListener("scroll", checkScrollPosition);
    window.addEventListener("resize", checkScrollPosition);

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
