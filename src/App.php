<?php
namespace App;

use App\Database;
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
            ->get('/etudiant', 'Etudiant', 'index')
            ->get('/etudiant/mon_profil', 'Etudiant', 'profil')
            ->get('/etudiant/mes_offres', 'Etudiant', 'mesOffres')
            ->get('/etudiant/wishlist', 'Etudiant', 'wishlist')

            // Routes d'offres
            ->get('/offres', 'Offres', 'index')
            ->get('/offres/details/:id', 'Offres', 'details')

            // Routes d'entreprises
            ->get('/entreprises', 'Entreprises', 'index')
            ->get('/entreprises/details/:id', 'Entreprises', 'details')

            // Routes de pilote
            ->get('/pilotes', 'Pilotes', 'index')
            ->get('/pilotes/etudiants', 'Pilotes', 'etudiants')
            ->get('/pilotes/etudiants/:id', 'Pilotes', 'etudiantDetails')
            ->get('/pilotes/etudiants/ajouter', 'Pilotes', 'ajouterEtudiant')
            ->post('/pilotes/etudiants/ajouter', 'Pilotes', 'enregistrerEtudiant')
            ->get('/pilotes/etudiants/:id/modifier', 'Pilotes', 'modifierEtudiant')
            ->post('/pilotes/etudiants/:id/modifier', 'Pilotes', 'mettreAJourEtudiant')
            ->get('/pilotes/etudiants/:id/supprimer', 'Pilotes', 'supprimerEtudiant')
            ->get('/pilotes/etudiants/:id/wishlist', 'Pilotes', 'etudiantWishlist')
            ->get('/pilotes/etudiants/:id/offres', 'Pilotes', 'etudiantOffres')

            ->get('/pilotes/entreprises', 'Pilotes', 'entreprises')
            ->get('/pilotes/entreprises/:id', 'Pilotes', 'entrepriseDetails')
            ->get('/pilotes/entreprises/ajouter', 'Pilotes', 'ajouterEntreprise')
            ->post('/pilotes/entreprises/ajouter', 'Pilotes', 'enregistrerEntreprise')
            ->get('/pilotes/entreprises/:id/modifier', 'Pilotes', 'modifierEntreprise')
            ->post('/pilotes/entreprises/:id/modifier', 'Pilotes', 'mettreAJourEntreprise')
            ->get('/pilotes/entreprises/:id/supprimer', 'Pilotes', 'supprimerEntreprise')

            ->get('/pilotes/offres', 'Pilotes', 'offres')
            ->get('/pilotes/offres/:id', 'Pilotes', 'offreDetails')
            ->get('/pilotes/offres/ajouter', 'Pilotes', 'ajouterOffre')
            ->post('/pilotes/offres/ajouter', 'Pilotes', 'enregistrerOffre')
            ->get('/pilotes/offres/:id/modifier', 'Pilotes', 'modifierOffre')
            ->post('/pilotes/offres/:id/modifier', 'Pilotes', 'mettreAJourOffre')
            ->get('/pilotes/offres/:id/supprimer', 'Pilotes', 'supprimerOffre')

            // Routes d'admin
            ->get('/admin', 'Admin', 'index')
            ->get('/admin/pilotes', 'Admin', 'pilotes')
            ->get('/admin/pilotes/:id', 'Admin', 'piloteDetails')
            ->get('/admin/pilotes/ajouter', 'Admin', 'ajouterPilote')
            ->post('/admin/pilotes/ajouter', 'Admin', 'enregistrerPilote')
            ->get('/admin/pilotes/:id/modifier', 'Admin', 'modifierPilote')
            ->post('/admin/pilotes/:id/modifier', 'Admin', 'mettreAJourPilote')
            ->get('/admin/pilotes/:id/supprimer', 'Admin', 'supprimerPilote')

            ->get('/admin/etudiants', 'Admin', 'etudiants')
            ->get('/admin/etudiants/:id', 'Admin', 'etudiantDetails')
            ->get('/admin/etudiants/ajouter', 'Admin', 'ajouterEtudiant')
            ->post('/admin/etudiants/ajouter', 'Admin', 'enregistrerEtudiant')
            ->get('/admin/etudiants/:id/modifier', 'Admin', 'modifierEtudiant')
            ->post('/admin/etudiants/:id/modifier', 'Admin', 'mettreAJourEtudiant')
            ->get('/admin/etudiants/:id/supprimer', 'Admin', 'supprimerEtudiant')

            ->get('/admin/entreprises', 'Admin', 'entreprises')
            ->get('/admin/entreprises/:id', 'Admin', 'entrepriseDetails')
            ->get('/admin/entreprises/ajouter', 'Admin', 'ajouterEntreprise')
            ->post('/admin/entreprises/ajouter', 'Admin', 'enregistrerEntreprise')
            ->get('/admin/entreprises/:id/modifier', 'Admin', 'modifierEntreprise')
            ->post('/admin/entreprises/:id/modifier', 'Admin', 'mettreAJourEntreprise')
            ->get('/admin/entreprises/:id/supprimer', 'Admin', 'supprimerEntreprise')

            ->get('/admin/offres', 'Admin', 'offres')
            ->get('/admin/offres/:id', 'Admin', 'offreDetails')
            ->get('/admin/offres/ajouter', 'Admin', 'ajouterOffre')
            ->post('/admin/offres/ajouter', 'Admin', 'enregistrerOffre')
            ->get('/admin/offres/:id/modifier', 'Admin', 'modifierOffre')
            ->post('/admin/offres/:id/modifier', 'Admin', 'mettreAJourOffre')
            ->get('/admin/offres/:id/supprimer', 'Admin', 'supprimerOffre')

            // Routes légales
            ->get('/mentions-legales', 'Home', 'mentionsLegales')
            ->get('/politique-confidentialite', 'Home', 'politiqueConfidentialite')
            ->get('/conditions-utilisation', 'Home', 'conditionsUtilisation');

        // Set up Twig
        $loader = new FilesystemLoader(__DIR__ . '\..\templates');
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

        // Extract the path from REQUEST_URI
        $uri = $_SERVER['REQUEST_URI'];

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

    // Autres méthodes inchangées...
    private function checkCsrfProtection() {
        // Code inchangé...
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