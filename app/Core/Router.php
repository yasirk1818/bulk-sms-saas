<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function get(string $path, string $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, string $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, string $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function group(array $attributes, callable $callback): self
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
        return $this;
    }

    private function addRoute(string $method, string $path, string $handler, array $middleware = []): self
    {
        $prefix = '';
        $groupMiddleware = [];

        foreach ($this->groupStack as $group) {
            $prefix .= $group['prefix'] ?? '';
            if (isset($group['middleware'])) {
                $groupMiddleware = array_merge($groupMiddleware, (array) $group['middleware']);
            }
        }

        $fullPath = $prefix . $path;
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => array_merge($groupMiddleware, $middleware),
            'pattern' => $this->pathToPattern($fullPath)
        ];

        return $this;
    }

    private function pathToPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();

        // Support PUT/DELETE via POST with _method
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $middlewareClass) {
                    $mw = new $middlewareClass();
                    $mw->handle();
                }

                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        if ($this->isApiRequest($uri)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found', 'status' => 404]);
        } else {
            include APP_PATH . '/Views/errors/404.php';
        }
    }

    private function getUri(): string
    {
        $uri = $_GET['url'] ?? '';
        $uri = '/' . trim($uri, '/');
        return $uri ?: '/';
    }

    private function isApiRequest(string $uri): bool
    {
        return str_starts_with($uri, '/api/');
    }

    private function callHandler(string $handler, array $params): void
    {
        [$controllerClass, $method] = explode('@', $handler);
        $controllerClass = 'App\\Controllers\\' . $controllerClass;

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller not found: {$controllerClass}");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method not found: {$controllerClass}@{$method}");
        }

        call_user_func_array([$controller, $method], $params);
    }
}
