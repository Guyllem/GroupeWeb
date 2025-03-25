<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage Connect - Profil</title>
    <link rel="stylesheet" href="styles_page_etudiant.css">
</head>
<body>
<main>
    <div class="container">
        <?php include 'navbar-etudiant.php'; ?>
        <div class="profile-container">
            <div class="profile-header">
                <div class="back-button">
                    <a href="page_etudiant.php"><span class="arrow">←</span> Accueil</a>
                </div>
                <h1 class="profile-title">Mon Profil</h1>
            </div>

            <div class="profile-content">
                <div class="profile-info">
                    <div class="user-card">
                        <p class="username">Louis</p>
                        <p class="lastname">Durenne</p>
                        <p class="campus">Nancy - CPI A2 INFO</p>
                        <p class="email">dresserlouis2005@gmail.com</p>
                    </div>
                </div>

                <div class="skills-section">
                    <h2 class="skills-title">Compétences :</h2>
                    <div class="skills-grid" id="skills-grid">
                        <div class="skill-badge">IA <span class="skill-delete">×</span></div>
                        <div class="skill-badge">Web développement <span class="skill-delete">×</span></div>
                        <div class="skill-badge">MySQL <span class="skill-delete">×</span></div>
                        <div class="skill-badge">Figma <span class="skill-delete">×</span></div>
                    </div>
                    <div class="skill-dropdown-container">
                        <button class="add-skill-button" id="add-skill-button">Ajouter une compétence</button>
                        <div class="skill-dropdown" id="skill-dropdown">
                            <div class="skill-dropdown-item">Java</div>
                            <div class="skill-dropdown-item">Python</div>
                            <div class="skill-dropdown-item">JavaScript</div>
                            <div class="skill-dropdown-item">HTML/CSS</div>
                            <div class="skill-dropdown-item">React</div>
                            <div class="skill-dropdown-item">Angular</div>
                            <div class="skill-dropdown-item">Vue.js</div>
                            <div class="skill-dropdown-item">Node.js</div>
                            <div class="skill-dropdown-item">PHP</div>
                            <div class="skill-dropdown-item">Laravel</div>
                            <div class="skill-dropdown-item">Symfony</div>
                            <div class="skill-dropdown-item">UX/UI Design</div>
                            <div class="skill-dropdown-item">DevOps</div>
                            <div class="skill-dropdown-item">AWS</div>
                            <div class="skill-dropdown-item">Docker</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../footer.php'; ?>
<script src="../frontend_script.js"></script>
<script src="skills.js"></script>

</body>
</html>