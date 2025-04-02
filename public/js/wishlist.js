document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner toutes les checkboxes de wishlist
    const wishlistCheckboxes = document.querySelectorAll('.heart-container .checkbox');

    // Ajouter un écouteur d'événement à chaque checkbox
    wishlistCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function(event) {
            // Empêcher le comportement par défaut
            event.preventDefault();

            // Récupérer l'ID de l'offre à partir de l'attribut id de la checkbox
            const offerId = this.id.split('-')[1];

            // Déterminer l'URL en fonction de l'état de la checkbox
            let targetUrl;
            if (this.checked) {
                targetUrl = `/offres/wishlist/ajouter/${offerId}`;
            } else {
                targetUrl = `/offres/wishlist/retirer/${offerId}`;
            }

            // Rediriger vers l'URL appropriée
            window.location.href = targetUrl;
        });
    });
});