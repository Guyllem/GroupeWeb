document.addEventListener('DOMContentLoaded', function() {
    const cvUpload = document.getElementById('cv-upload');
    const motivationUpload = document.getElementById('motivation-upload');
    const cvFileName = document.getElementById('cv-file-name');
    const motivationFileName = document.getElementById('motivation-file-name');
    const cvError = document.getElementById('cv-error');
    const motivationError = document.getElementById('motivation-error');
    const submitBtn = document.getElementById('submitBtn');

    function validateFiles() {
    let cvValid = cvUpload.files.length > 0 && cvUpload.files[0].type === 'application/pdf';
    let motivationValid = motivationUpload.files.length > 0 && motivationUpload.files[0].type === 'application/pdf';

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

    submitBtn.disabled = !(cvValid && motivationValid);
}

    cvUpload.addEventListener('change', function() {
    if (this.files.length > 0) {
    cvFileName.textContent = this.files[0].name;
} else {
    cvFileName.textContent = 'Aucun fichier sélectionné';
}
    validateFiles();
});

    motivationUpload.addEventListener('change', function() {
    if (this.files.length > 0) {
    motivationFileName.textContent = this.files[0].name;
} else {
    motivationFileName.textContent = 'Aucun fichier sélectionné';
}
    validateFiles();
});
});
