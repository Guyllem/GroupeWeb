document.addEventListener("DOMContentLoaded", function() {
    // Éléments DOM
    const addSkillButton = document.getElementById("add-skill-button");
    const skillDropdown = document.getElementById("skill-dropdown");
    const skillsGrid = document.getElementById("skills-grid");

    // Gestion du menu déroulant
    if (addSkillButton) {
        addSkillButton.addEventListener("click", function(e) {
            e.stopPropagation(); // Empêche la propagation au document
            skillDropdown.classList.toggle("active");
        });
    }

    // Fermer le dropdown si on clique ailleurs
    document.addEventListener("click", function(e) {
        if (skillDropdown && skillDropdown.classList.contains("active")) {
            if (!e.target.closest(".skill-dropdown-container")) {
                skillDropdown.classList.remove("active");
$            }
        }
    });

    // Sélection d'une compétence
    const skillItems = document.querySelectorAll(".skill-dropdown-item");
    skillItems.forEach(item => {
        item.addEventListener("click", function() {
            const skillName = this.textContent;

            // Vérifier si la compétence existe déjà
            const existingSkills = document.querySelectorAll(".skill-badge");
            let skillExists = false;

            existingSkills.forEach(skill => {
                // Comparer en ignorant la croix de suppression
                if (skill.textContent.replace("×", "").trim() === skillName) {
                    skillExists = true;
                }
            });

            // Ajouter uniquement si la compétence n'existe pas déjà
            if (!skillExists) {
                const newSkill = document.createElement("div");
                newSkill.className = "skill-badge";

                // Créer le contenu avec la croix de suppression
                const skillText = document.createTextNode(skillName + " ");
                const deleteSpan = document.createElement("span");
                deleteSpan.className = "skill-delete";
                deleteSpan.textContent = "×";

                // Ajouter le gestionnaire de suppression
                deleteSpan.addEventListener("click", function(e) {
                    e.stopPropagation();
                    this.parentElement.remove();
                });

                // Assembler et ajouter au DOM
                newSkill.appendChild(skillText);
                newSkill.appendChild(deleteSpan);
                skillsGrid.appendChild(newSkill);
            }

            // Fermer le menu déroulant
            skillDropdown.classList.remove("active");
        });
    });

    // Initialiser les gestionnaires pour les boutons de suppression existants
    const deleteButtons = document.querySelectorAll(".skill-delete");
    deleteButtons.forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.stopPropagation();
            const skillName = this.parentElement.textContent.replace("×", "").trim();
            this.parentElement.remove();
        });
    });
});