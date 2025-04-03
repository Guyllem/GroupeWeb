document.addEventListener('DOMContentLoaded', function() {
    const cvUpload = document.getElementById('cv-upload');
    const motivationUpload = document.getElementById('motivation-upload');
    const cvFileName = document.getElementById('cv-file-name');
    const motivationFileName = document.getElementById('motivation-file-name');
    const cvError = document.getElementById('cv-error');
    const motivationError = document.getElementById('motivation-error');
    const submitBtn = document.getElementById('submitBtn');

    // Fonction qui vérifie si les fichiers téléchargés sont valides (format PDF)
    // et gère l'affichage des messages d'erreur et l'état du bouton de soumission
    function validateFiles() {
        // Vérification des types de fichiers
        let cvValid = cvUpload.files.length > 0 && cvUpload.files[0].type === 'application/pdf';
        let motivationValid = motivationUpload.files.length > 0 && motivationUpload.files[0].type === 'application/pdf';

        // Gestion des messages d'erreur pour chaque fichier
        if (cvUpload.files.length > 0 && !cvValid) {
            cvError.textContent = 'Veuillez sélectionner un fichier PDF';
        } else {
            cvError.textContent = '';
        }

        if (motivationUpload.files.length > 0 && !motivationValid) {
            motivationError.textContent = 'Veuillez sélectionner un fichier PDF';
        } else {
            motivationError.textContent = '';
        }

        // Activation du bouton uniquement si les deux fichiers sont valides
        submitBtn.disabled = !(cvValid && motivationValid);
    }

    // Mettre à jour l'interface lors de la sélection des fichiers
    cvUpload.addEventListener('change', function() {
        // Affichage du nom du fichier CV sélectionné
        if (this.files.length > 0) {
            cvFileName.textContent = this.files[0].name;
        } else {
            cvFileName.textContent = 'Aucun fichier sélectionné';
        }
        validateFiles();
    });

    motivationUpload.addEventListener('change', function() {
        // Affichage du nom du fichier de lettre de motivation sélectionné
        if (this.files.length > 0) {
            motivationFileName.textContent = this.files[0].name;
        } else {
            motivationFileName.textContent = 'Aucun fichier sélectionné';
        }
        validateFiles();
    });
});