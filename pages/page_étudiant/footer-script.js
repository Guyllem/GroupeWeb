document.addEventListener('DOMContentLoaded', () => {
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
});