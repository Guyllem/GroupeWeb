document.addEventListener("DOMContentLoaded", function() {
    // Éléments DOM
    const addSkillButton = document.getElementById("add-skill-button");
    const skillDropdown = document.getElementById("skill-dropdown");

    // Gestion du menu déroulant
    if (addSkillButton && skillDropdown) {
        // Affichage/masquage du dropdown
        addSkillButton.addEventListener("click", function(e) {
            e.stopPropagation(); // Empêche la propagation au document
            skillDropdown.classList.toggle("active");
        });

        // Fermer le dropdown si on clique ailleurs
        document.addEventListener("click", function(e) {
            if (skillDropdown.classList.contains("active")) {
                if (!e.target.closest(".skill-dropdown-container")) {
                    skillDropdown.classList.remove("active");
                }
            }
        });
    }

    // Initialiser les gestionnaires pour les boutons de suppression existants
    const deleteButtons = document.querySelectorAll(".skill-delete");
    deleteButtons.forEach(btn => {
        btn.addEventListener("click", function(e) {
            // Empêcher la navigation immédiate pour confirmation
            e.preventDefault();

            const skillId = this.getAttribute("data-id");
            const skillName = this.parentElement.textContent.replace("×", "").trim();

            if (confirm(`Êtes-vous sûr de vouloir supprimer la compétence "${skillName}" ?`)) {
                // Naviguer vers l'URL de suppression
                window.location.href = `/etudiant/skills/delete/${skillId}`;
            }
        });
    });

    // Récupérer les compétences disponibles (simulées ou chargées directement dans le HTML)
    // Note: dans cette approche simplifiée, les compétences sont directement intégrées
    // dans le HTML par le serveur lors du rendu de la page
});