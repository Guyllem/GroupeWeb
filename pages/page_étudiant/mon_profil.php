<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage Connect</title>
    <link rel="stylesheet" href="styles_page_etudiant.css">
</head>
<main>
    <div class="container">
        <?php include 'navbar.php'; ?>
        <div class="profile-container">
            <div class="profile-header">
                <div class="back-button">
                    <a href="page_étudiant.php"><span class="arrow">←</span> Accueil</a>
                </div>
                <h1 class="profile-title">Mon Profil</h1>
            </div>

            <div class="profile-content">
                <div class="profile-info">
                    <div class="user-card">
                        <p class="username">Louis</p>
                        <p class="lastname">Durenne</p>
                        <p class="email">dresserlouis2005@gmail.com</p>
                    </div>
                </div>

                <div class="skills-section">
                    <h2 class="skills-title">Compétences :</h2>
                    <div class="skills-grid">
                        <div class="skill-badge">IA</div>
                        <div class="skill-badge">Web développement</div>
                        <div class="skill-badge">MySQL</div>
                        <div class="skill-badge">Figma</div>
                    </div>
                    <button class="add-skill-button">Ajouter une compétence</button>
                </div>
            </div>
        </div>
        </div>
</main>
<?php include 'footer.php'; ?>
<script src="etudiant_script.js"></script>
</html>