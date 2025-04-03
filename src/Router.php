<?php
namespace App;

/**
 * Simple Router
 * Maps URLs to controller actions
 */
class Router
{
    /**
     * Registered routes
     * [
     *   'GET' => [
     *     '/path' => ['controller' => 'Home', 'action' => 'index']
     *   ],
     *   'POST' => [...]
     * ]
     */
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    /**
     * Register a GET route
     *
     * @param string $path URL path
     * @param string $controller Controller name (without 'Controller' suffix)
     * @param string $action Method name within the controller
     * @return Router Returns $this for method chaining
     */
    public function get($path, $controller, $action)
    {
        $this->routes['GET'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }

    /**
     * Register a POST route
     *
     * @param string $path URL path
     * @param string $controller Controller name (without 'Controller' suffix)
     * @param string $action Method name within the controller
     * @return Router Returns $this for method chaining
     */
    public function post($path, $controller, $action)
    {
        $this->routes['POST'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }

    /**
     * Register a PUT route
     *
     * @param string $path URL path
     * @param string $controller Controller name (without 'Controller' suffix)
     * @param string $action Method name within the controller
     * @return Router Returns $this for method chaining
     */
    public function put($path, $controller, $action)
    {
        $this->routes['PUT'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }

    /**
     * Register a DELETE route
     *
     * @param string $path URL path
     * @param string $controller Controller name (without 'Controller' suffix)
     * @param string $action Method name within the controller
     * @return Router Returns $this for method chaining
     */
    public function delete($path, $controller, $action)
    {
        $this->routes['DELETE'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }

    /**
     * Resolve the current request path to a controller/action
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path Request URI path
     * @return array|null Route information or null if not found
     */
    public function resolve($method, $path)
    {
        // Remove query string and trailing slash
        $path = parse_url($path, PHP_URL_PATH);
        if ($path !== null) {
            $path = rtrim($path, '/');
        }

        // Add leading slash if missing
        if (empty($path)) {
            $path = '/';
        }

        // Check if route exists
        if (isset($this->routes[$method][$path])) {
            return $this->routes[$method][$path];
        }

        // Check for dynamic routes with parameters
        foreach ($this->routes[$method] as $route => $params) {
            // Convert route to regex pattern
            if (strpos($route, ':') !== false) {
                $pattern = preg_replace('/:[^\/]+/', '([^/]+)', $route);
                $pattern = "#^$pattern$#";

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches); // Remove the full match

                    // Extract parameter names
                    preg_match_all('/:([^\/]+)/', $route, $paramNames);
                    $params['params'] = array_combine($paramNames[1], $matches);

                    return $params;
                }
            }
        }

        return null;
    }
}