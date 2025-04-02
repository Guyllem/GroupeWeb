// Créer un nouveau fichier js/filtrage_promotion.js
document.addEventListener('DOMContentLoaded', function() {
    const campusSelect = document.getElementById('campus');
    const promotionSelect = document.getElementById('promotion');
    const allPromotions = Array.from(promotionSelect.options).slice(1); // Exclure l'option par défaut

    campusSelect.addEventListener('change', function() {
        const selectedCampusId = this.value;

        // Réinitialiser le select des promotions
        promotionSelect.innerHTML = '<option value="" selected disabled>Sélectionner une promotion</option>';

        // Filtrer les promotions correspondant au campus sélectionné
        const filteredPromotions = allPromotions.filter(option =>
            option.dataset.campusId === selectedCampusId || !option.dataset.campusId
        );

        // Ajouter les promotions filtrées
        filteredPromotions.forEach(option => {
            promotionSelect.appendChild(option.cloneNode(true));
        });
    });
});