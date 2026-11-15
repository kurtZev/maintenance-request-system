<?php
// A small router: remembers which URL runs which controller method.
// Routes are registered in routes/web.php.

class Router
{
    /** @var array<string, array<string, array{action: array, access: ?string}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    /**
     * Register a page that is opened with a link or the address bar.
     *
     * @param string      $path   URL path, e.g. '/admin/dashboard'
     * @param array       $action [ControllerClass::class, 'methodName']
     * @param string|null $access null = everyone, 'guest' = only logged-out visitors,
     *                            'auth' = any logged-in user, 'admin' or 'requester' = that role
     */
    public function get(string $path, array $action, ?string $access = null): void
    {
        $this->routes['GET'][$path] = ['action' => $action, 'access' => $access];
    }

    /** Register a form submission. Same parameters as get(). */
    public function post(string $path, array $action, ?string $access = null): void
    {
        $this->routes['POST'][$path] = ['action' => $action, 'access' => $access];
    }

    /** True if a page (GET route) is registered for this path. */
    public function has(string $path): bool
    {
        return isset($this->routes['GET'][$path]);
    }

    /** Find the route for the current request and run it. */
    public function dispatch(string $method, string $uri): void
    {
        $method = $method === 'HEAD' ? 'GET' : $method;
        $path   = $this->cleanPath($uri);
        $route  = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        // Access check before the controller runs.
        switch ($route['access']) {
            case null:
                break;
            case 'guest':
                AuthMiddleware::requireGuest();
                break;
            case 'auth':
                AuthMiddleware::requireLogin();
                break;
            default:
                AuthMiddleware::requireRole($route['access']);
        }

        [$class, $action] = $route['action'];
        (new $class())->$action();
    }

    /** Turn "/maintenance-request-system/admin/dashboard?x=1" into "/admin/dashboard". */
    private function cleanPath(string $uri): string
    {
        $path = (string) parse_url($uri, PHP_URL_PATH);

        if (BASE_URL !== '' && stripos($path, BASE_URL) === 0) {
            $path = substr($path, strlen(BASE_URL));
        }

        // In case the public folder or index.php was typed in the address bar.
        $path = preg_replace('#^/public(?=/|$)#', '', $path);
        $path = preg_replace('#^/index\.php(?=/|$)#', '', $path);

        return '/' . trim($path, '/');
    }
}
