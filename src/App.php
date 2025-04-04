<?php
namespace App;

use App\Database;
use App\Utils\SecurityUtil;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Charger la configuration sécurisée des sessions
require_once __DIR__ . '/session_config.php';

class App {
    private $router;
    private $twig;
    private $db;
    private $sessionStarted = false;

    public function __construct() {
        // Set up database
        $this->db = new Database();

        // Set up router
        $this->router = new Router();

        // Define routes
        $this->router->get('/', 'Home', 'index')
            ->get('/about', 'Home', 'about')

            // Routes d'authentification
            ->get('/login', 'Auth', 'login')
            ->post('/login', 'Auth', 'login')
            ->get('/logout', 'Auth', 'logout')

            // Routes d'étudiant
            ->get('/etudiant', 'Etudiant', 'index') // To do problème
            ->get('/etudiant/mon_profil', 'Etudiant', 'profil') // good
            ->get('/etudiant/mes_offres', 'Etudiant', 'mesOffres') // good
            ->get('/etudiant/mes_candidatures', 'Etudiant', 'my_applications') // good
            ->get('/etudiant/wishlist', 'Etudiant', 'wishlist') // good
            ->post('/etudiant/wishlist/ajouter', 'Etudiant', 'ajouterWishlist') // A faire
            ->post('/etudiant/wishlist/retirer', 'Etudiant', 'retirerWishlist') // A faire
            // Routes de gestion des compétences avec GET
            ->get('/etudiant/skills/add/:id', 'Skills', 'addSkill')
            ->get('/etudiant/skills/delete/:id', 'Skills', 'deleteSkill')

            // Routes d'offres
            ->get('/offres', 'Offres', 'index') // good
            ->get('/offres/pages/:page', 'Offres', 'index') // good
            ->get('/offres/details/:id', 'Offres', 'details') // good
            ->post('/offres/rechercher', 'Offres', 'rechercher') // A faire
            ->get('/offres/wishlist/ajouter/:id', 'Offres', 'add_to_wishlist')
            ->get('/offres/wishlist/retirer/:id', 'Offres', 'remove_from_wishlist')
            ->get('/offres/details/:id/postuler', 'Etudiant', 'postuler')
            ->post('/offres/details/:id/postuler', 'Etudiant', 'validate_application')


            // Routes d'entreprises
            ->get('/entreprises', 'Entreprises', 'index')
            ->get('/entreprises/details/:id', 'Entreprises', 'details')
            ->post('/entreprises/rechercher', 'Entreprises', 'rechercher')
            ->get('/entreprises/details/:id/offres', 'Entreprises', 'associateOffers')
            ->get('/entreprises/details/:id/evaluer', 'Entreprises', 'afficherRate')
            ->post('/entreprises/details/:id/evaluer', 'Entreprises', 'rate')



            // Routes de pilote
            ->get('/pilotes', 'Pilotes', 'index') // good
            ->get('/pilotes/etudiants', 'Pilotes', 'etudiants') //good
            ->post('/pilotes/etudiants', 'Pilotes', 'rechercheEtudiant') // Ajout: recherche d'étudiant
            ->get('/pilotes/etudiants/:id', 'Pilotes', 'etudiantDetails')
            ->get('/pilotes/etudiants/ajouter', 'Pilotes', 'ajouterEtudiant')
            ->post('/pilotes/etudiants/ajouter', 'Pilotes', 'enregistrerEtudiant')
            ->get('/pilotes/etudiants/:id/modifier', 'Pilotes', 'modifierEtudiant')
            ->post('/pilotes/etudiants/:id/modifier', 'Pilotes', 'mettreAJourEtudiant')
            ->get('/pilotes/etudiants/:id/supprimer', 'Pilotes', 'etudiantSupprimer') // Ajout: supprimer étudiant
            ->post('/pilotes/etudiants/:id/supprimer', 'Pilotes', 'etudiantSupprimerValider')
            ->get('/pilotes/etudiants/:id/reset', 'Pilotes', 'afficherReset')
            ->post('/pilotes/etudiants/:id/reset', 'Pilotes', 'resetPassword')
            ->get('/pilotes/etudiants/:id/wishlist', 'Pilotes', 'etudiantWishlist')
            ->get('/pilotes/etudiants/:id/offres', 'Pilotes', 'etudiantOffres')
            ->get('/pilotes/etudiants/:id/password', 'Pilotes', 'afficherReset') // Ajout: page modif mot de passe
            ->post('/pilotes/etudiants/:id/password', 'Pilotes', 'resetPassword') // Ajout: enregistrer mot de passe

            ->get('/pilotes/entreprises', 'Pilotes', 'entreprises')
            ->post('/pilotes/entreprises', 'Pilotes', 'rechercheEntreprise') // Ajout: recherche d'entreprise
            ->get('/pilotes/entreprises/:id', 'Pilotes', 'entrepriseDetails')
            ->get('/pilotes/entreprises/ajouter', 'Pilotes', 'ajouterEntreprise')
            ->post('/pilotes/entreprises/ajouter', 'Pilotes', 'enregistrerEntreprise')
            ->get('/pilotes/entreprises/:id/modifier', 'Pilotes', 'modifierEntreprise')
            ->post('/pilotes/entreprises/:id/modifier', 'Pilotes', 'mettreAJourEntreprise')
            ->get('/pilotes/entreprises/:id/supprimer', 'Pilotes', 'afficherEntrepriseSupprimer') // Ajout: supprimer entreprise
            ->post('/pilotes/entreprises/:id/supprimer', 'Pilotes', 'entrepriseSupprimer') // Ajout: supprimer entreprise
            ->get('/pilotes/entreprises/:id/offres', 'Pilotes', 'entrepriseOffres') // Ajout: offres d'une entreprise
            ->get('/pilotes/entreprises/:id/evaluer', 'Pilotes', 'afficherRateEntreprise') // Ajout: évaluer entreprise
            ->post('/pilotes/entreprises/:id/evaluer', 'Pilotes', 'rateEnterprise') // Ajout: évaluer entreprise

            ->get('/pilotes/offres', 'Pilotes', 'offres')
            ->post('/pilotes/offres', 'Pilotes', 'rechercheOffre') // Ajout: recherche d'offre
            ->get('/pilotes/offres/:id', 'Pilotes', 'offreDetails')
            ->get('/pilotes/offres/ajouter', 'Pilotes', 'ajouterOffre')
            ->post('/pilotes/offres/ajouter', 'Pilotes', 'enregistrerOffre')
            ->get('/pilotes/offres/:id/modifier', 'Pilotes', 'modifierOffre')
            ->post('/pilotes/offres/:id/modifier', 'Pilotes', 'mettreAJourOffre')
            ->get('/pilotes/offres/:id/supprimer', 'Pilotes', 'afficherSupprimerOffre')
            ->post('/pilotes/offres/:id/supprimer', 'Pilotes', 'supprimerOffre')

            // Routes d'admin
            ->get('/admin', 'Admin', 'index')
            ->get('/admin/pilotes', 'Admin', 'pilotes')
            ->get('/admin/pilotes/:id', 'Admin', 'piloteDetails')
            ->get('/admin/pilotes/ajouter', 'Admin', 'ajouterPilote')
            ->post('/admin/pilotes/ajouter', 'Admin', 'enregistrerPilote')
            ->get('/admin/pilotes/:id/modifier', 'Admin', 'modifierPilote')
            ->post('/admin/pilotes/:id/modifier', 'Admin', 'mettreAJourPilote')
            ->get('/admin/pilotes/:id/supprimer', 'Admin', 'afficherSupprimerPilote')
            ->post('/admin/pilotes/:id/supprimer', 'Admin', 'supprimerPilote')
            ->get('/admin/pilotes/:id/reset', 'Admin', 'pilotePassword')
            ->post('/admin/pilotes/:id/reset', 'Admin', 'piloteSavePassword')



            ->get('/admin/etudiants', 'Admin', 'etudiants')
            ->get('/admin/etudiants/:id', 'Admin', 'etudiantDetails')
            ->get('/admin/etudiants/ajouter', 'Admin', 'ajouterEtudiant')
            ->post('/admin/etudiants/ajouter', 'Admin', 'enregistrerEtudiant')
            ->get('/admin/etudiants/:id/modifier', 'Admin', 'modifierEtudiant')
            ->post('/admin/etudiants/:id/modifier', 'Admin', 'mettreAJourEtudiant')
            ->get('/admin/etudiants/:id/supprimer', 'Admin', 'supprimerEtudiant')
            ->get('/admin/etudiants/:id/reset', 'Admin', 'etudiantPassword') // Ajout: page modif mot de passe
            ->post('/admin/etudiants/:id/reset', 'Admin', 'etudiantSavePassword')

            ->get('/admin/entreprises', 'Admin', 'entreprises')
            ->get('/admin/entreprises/:id', 'Admin', 'entrepriseDetails')
            ->get('/admin/entreprises/ajouter', 'Admin', 'ajouterEntreprise')
            ->post('/admin/entreprises/ajouter', 'Admin', 'enregistrerEntreprise')
            ->get('/admin/entreprises/:id/modifier', 'Admin', 'modifierEntreprise')
            ->post('/admin/entreprises/:id/modifier', 'Admin', 'mettreAJourEntreprise')
            ->get('/admin/entreprises/:id/supprimer', 'Admin', 'supprimerEntreprise')
            ->post('/admin/entreprises/:id/supprimer', 'Admin', 'confirmerSuppressionEntreprise')

            ->get('/admin/offres', 'Admin', 'offres')
            ->get('/admin/offres/:id', 'Admin', 'offreDetails')
            ->get('/admin/offres/ajouter', 'Admin', 'ajouterOffre')
            ->post('/admin/offres/ajouter', 'Admin', 'enregistrerOffre')
            ->get('/admin/offres/:id/modifier', 'Admin', 'modifierOffre')
            ->post('/admin/offres/:id/modifier', 'Admin', 'mettreAJourOffre')
            ->get('/admin/offres/:id/supprimer', 'Admin', 'supprimerOffre')
            ->post('/admin/offres/:id/supprimer', 'Admin', 'confirmerSuppressionOffre')

            // Routes légales
            ->get('/mentions-legales', 'Home', 'mentionsLegales')
            ->get('/politique-confidentialite', 'Home', 'politiqueConfidentialite')
            ->get('/conditions-utilisation', 'Home', 'conditionsUtilisation');

        // Set up Twig
        $loader = new FilesystemLoader(__DIR__ . '/../templates');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
            'strict_variables' => true
        ]);

        $this->twig->addExtension(new \Twig\Extension\DebugExtension());

        // Déterminer dynamiquement le chemin de base
        $baseUrl = '';

        // Ajouter les fonctions Twig
        $this->twig->addFunction(new \Twig\TwigFunction('asset', function ($path) use ($baseUrl) {
            return $baseUrl . '/' . ltrim($path, '/');
        }));

        // Ajout de la fonction url()
        $this->twig->addFunction(new \Twig\TwigFunction('url', function ($path) use ($baseUrl) {
            // Supprime le slash initial si présent pour éviter les doubles slashes
            return $baseUrl . '/' . ltrim($path, '/');
        }));
    }

    public function run() {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() == PHP_SESSION_NONE && !$this->sessionStarted) {
            session_start();
            $this->sessionStarted = true;
        }

        // Protection contre les attaques CSRF pour les requêtes POST, PUT, DELETE
        $this->checkCsrfProtection();

        // Vérifier si un token de persistance existe
        $authController = new \App\Controllers\AuthController($this->twig, $this->db);
        $authController->checkPersistentLogin();

        // Extraction du chemin de l'URL
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);

        // Protection des routes publiques pour utilisateurs déjà authentifiés
        if ($path === '/login' && isset($_SESSION['user_id'])) {
            // Rediriger en fonction du type d'utilisateur
            $userType = $_SESSION['user_type'];
            switch ($userType) {
                case 'admin':
                    header('Location: /admin');
                    break;
                case 'pilote':
                    header('Location: /pilotes');
                    break;
                case 'etudiant':
                    header('Location: /offres');
                    break;
                default:
                    // Aucun type reconnu, supprimer la session et continuer normalement
                    session_destroy();
                    break;
            }
            exit;
        }

        // Suppression des paramètres de requête
        $uri = parse_url($uri, PHP_URL_PATH);

        // If empty, set to root path
        if (empty($uri)) {
            $uri = '/';
        }

        $route = $this->router->resolve($_SERVER['REQUEST_METHOD'], $uri);

        if (!$route) {
            // 404 handling avec message de débogage
            echo '404 Not Found - URI demandée : ' . htmlspecialchars($uri);
            return;
        }

        $controllerName = "App\\Controllers\\" . $route['controller'] . "Controller";
        $controller = new $controllerName($this->twig, $this->db);

        try {
            // If route has parameters, pass them to the action
            if (isset($route['params'])) {
                $controller->{$route['action']}($route['params']);
            } else {
                $controller->{$route['action']}();
            }
        } catch (\Exception $e) {
            // Gestion globale des exceptions
            $this->handleException($e);
        }
    }

    /**
     * Vérifie la protection CSRF pour les requêtes modifiant des données
     *
     * Cette méthode assure que les requêtes POST, PUT et DELETE contiennent
     * un token CSRF valide pour protéger contre les attaques CSRF
     *
     * @return void
     * @throws \Exception Si le token CSRF est invalide ou manquant
     */
    private function checkCsrfProtection() {
        // On vérifie uniquement les méthodes modifiant des données
        $method = $_SERVER['REQUEST_METHOD'];
        if (!in_array($method, ['POST', 'PUT', 'DELETE'])) {
            return;
        }

        // Cas spécial pour certaines routes qui ne nécessitent pas de vérification CSRF
        // Par exemple, les webhooks externes ou les callbacks d'API
        $exemptRoutes = [
            // Ajoutez ici les routes exemptées si nécessaire
            '/api/competences',
            '/api/etudiant/competences/ajouter',
            '/api/etudiant/competences/modifier'
        ];

        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (in_array($currentPath, $exemptRoutes)) {
            return;
        }

        // Vérification du token CSRF
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$csrfToken) {
            // Journal de sécurité pour surveiller les tentatives d'attaque
            error_log('Tentative d\'accès sans token CSRF: ' . $_SERVER['REQUEST_URI']);

            // En environnement de production, on renvoie une erreur 403
            header('HTTP/1.1 403 Forbidden');
            echo $this->twig->render('error.html.twig', [
                'code' => 403,
                'message' => 'Accès refusé: token de sécurité manquant.'
            ]);
            exit;
        }

        // Utiliser notre utilitaire de sécurité pour vérifier le token
        if (!SecurityUtil::verifyCsrfToken($csrfToken)) {
            // Journal de sécurité avec informations contextuelles
            error_log('Token CSRF invalide détecté: ' . $_SERVER['REQUEST_URI'] . ' - IP: ' . $_SERVER['REMOTE_ADDR']);

            // Régénérer un nouveau token pour la session
            SecurityUtil::generateCsrfToken();

            // Erreur 403 avec message adapté
            header('HTTP/1.1 403 Forbidden');
            echo $this->twig->render('error.html.twig', [
                'code' => 403,
                'message' => 'Accès refusé: votre session a expiré ou est invalide. Veuillez rafraîchir la page et réessayer.'
            ]);
            exit;
        }
    }

    private function handleException(\Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo "<div style='background:#f8d7da; color:#721c24; padding:15px; margin:10px; border:1px solid #f5c6cb'>";
        echo "<h3>Erreur d'application</h3>";
        echo "<p><strong>Type:</strong> " . get_class($e) . "</p>";
        echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Fichier:</strong> " . $e->getFile() . " (ligne " . $e->getLine() . ")</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    }
}