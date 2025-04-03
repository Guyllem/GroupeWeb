document.addEventListener('DOMContentLoaded', function() {
    const campusSelect = document.getElementById('campus');
    const promotionSelect = document.getElementById('promotion');

    // Stockage de toutes les options de promotion (sauf l'option par défaut)
    const allPromotions = Array.from(promotionSelect.options).slice(1);

    campusSelect.addEventListener('change', function() {
        const selectedCampusId = this.value;

        // Réinitialisation du select des promotions
        promotionSelect.innerHTML = '<option value="" selected disabled>Sélectionner une promotion</option>';

        // Filtrage des promotions correspondant au campus sélectionné
        const filteredPromotions = allPromotions.filter(option =>
            option.dataset.campusId === selectedCampusId || !option.dataset.campusId
        );

        // Ajout des promotions filtrées au select
        filteredPromotions.forEach(option => {
            promotionSelect.appendChild(option.cloneNode(true));
        });
    });
});