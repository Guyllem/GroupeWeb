document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.alert');

    if (flashMessages.length > 0) {
        // Définir le délai avant disparition (en ms)
        const autoHideDelay = 5000; // 5 secondes

        flashMessages.forEach(message => {
            // Ajouter une classe pour l'animation de sortie après le délai
            setTimeout(() => {
                message.classList.add('fade-out');

                // Supprimer l'élément après la fin de l'animation
                message.addEventListener('animationend', function() {
                    if (message.classList.contains('fade-out')) {
                        message.remove();
                    }
                });
            }, autoHideDelay);
        });
    }
});