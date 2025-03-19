<?php
namespace App;

use App\Database;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class App {
    private $router;
    private $twig;
    private $db;

    public function __construct() {
        // Set up database
        $this->db = new Database();

        // Set up router
        $this->router = new Router();

        // Define routes
        $this->router->get('/', 'Home', 'index')
            ->get('/about', 'Home', 'about')
            ->get('/users', 'User', 'index')
            ->get('/users/:id', 'User', 'show')
            ->post('/users', 'User', 'create')
            ->put('/users/:id', 'User', 'update')
            ->delete('/users/:id', 'User', 'delete');

        // Set up Twig
        $loader = new FilesystemLoader(__DIR__ . '/../templates');
        $this->twig = new Environment($loader, [
            'cache' => false,
        ]);
    }

    public function run() {
        // Extract the path from REQUEST_URI
        $uri = $_SERVER['REQUEST_URI'];
        // Remove script name and project path
        $uri = preg_replace('/^\/GroupeWeb\/index\.php/', '', $uri);
        $uri = preg_replace('/^\/GroupeWeb/', '', $uri);

        // If empty, set to root path
        if (empty($uri)) {
            $uri = '/';
        }

        $route = $this->router->resolve($_SERVER['REQUEST_METHOD'], $uri);

        if (!$route) {
            // 404 handling
            echo '404 Not Found';
            return;
        }

        $controllerName = "App\\Controllers\\" . $route['controller'] . "Controller";
        $controller = new $controllerName($this->twig, $this->db);

        // If route has parameters, pass them to the action
        if (isset($route['params'])) {
            $controller->{$route['action']}($route['params']);
        } else {
            $controller->{$route['action']}();
        }
    }
}