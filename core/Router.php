<?php

namespace App\Core;

class Router
{
    private static ?Router $instance = null;
    private array $routes = [];
    private array $groupMiddleware = [];
    private string $prefix = '';
    private array $middleware = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, string $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, string $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function addRoute(string $method, string $path, string $handler): void
    {
        $fullPath = $this->prefix . $path;
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => array_merge($this->groupMiddleware, $this->middleware),
        ];
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->groupMiddleware;

        if (isset($attributes['prefix'])) {
            $this->prefix .= '/' . trim($attributes['prefix'], '/');
            $this->prefix = rtrim($this->prefix, '/');
        }

        if (isset($attributes['middleware'])) {
            $middlewares = is_array($attributes['middleware'])
                ? $attributes['middleware']
                : [$attributes['middleware']];
            $this->groupMiddleware = array_merge($this->groupMiddleware, $middlewares);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            $params = $this->matchRoute($route['method'], $route['path'], $method, $uri);
            if ($params !== false) {
                $this->runMiddleware($route['middleware']);

                [$controller, $action] = explode('@', $route['handler']);
                $controllerClass = 'App\\Controllers\\' . $controller;
                $controllerFile = APP_ROOT . '/controllers/' . $controller . '.php';

                if (!file_exists($controllerFile)) {
                    http_response_code(500);
                    echo "Controller {$controller} not found.";
                    exit;
                }

                require_once $controllerFile;

                if (!class_exists($controllerClass)) {
                    http_response_code(500);
                    echo "Controller class {$controllerClass} not found.";
                    exit;
                }

                $instance = new $controllerClass();
                $instance->$action(...$params);
                return;
            }
        }

        http_response_code(404);
        require_once APP_ROOT . '/views/errors/404.php';
    }

    private function matchRoute(string $routeMethod, string $routePath, string $requestMethod, string $requestUri): array|false
    {
        if ($routeMethod !== $requestMethod) {
            return false;
        }

        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestUri, $matches)) {
            return array_values(array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY));
        }

        return false;
    }

    private function runMiddleware(array $middlewareList): void
    {
        foreach ($middlewareList as $middleware) {
            $middlewareClass = 'App\\Core\\Middleware';
            if (method_exists($middlewareClass, $middleware)) {
                Middleware::$middleware();
            }
        }
    }
}
