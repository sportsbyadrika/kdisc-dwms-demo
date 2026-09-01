<?php
namespace App\Core;

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, $handler): void
    {
        $this->routes['GET'][$this->normalise($path)] = $handler;
    }

    public function post(string $path, $handler): void
    {
        $this->routes['POST'][$this->normalise($path)] = $handler;
    }

    public function any(string $path, $handler): void
    {
        $this->get($path, $handler);
        $this->post($path, $handler);
    }

    private function normalise(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri    = $this->normalise($uri);
        $method = strtoupper($method) === 'POST' ? 'POST' : 'GET';

        // 1. exact match
        if (isset($this->routes[$method][$uri])) {
            $this->invoke($this->routes[$method][$uri], []);
            return;
        }
        // 2. placeholder match  /jobs/{id}
        foreach ($this->routes[$method] as $route => $handler) {
            if (strpos($route, '{') === false) {
                continue;
            }
            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $route);
            if (preg_match('#^' . $pattern . '$#', $uri, $m)) {
                array_shift($m);
                $this->invoke($handler, $m);
                return;
            }
        }
        abort(404);
    }

    private function invoke($handler, array $params): void
    {
        if (is_callable($handler)) {
            echo call_user_func_array($handler, $params);
            return;
        }
        [$class, $action] = explode('@', $handler);
        $fqcn = '\\App\\Controllers\\' . $class;
        if (!class_exists($fqcn)) {
            abort(500, 'Controller ' . $class . ' not found.');
        }
        $controller = new $fqcn();
        if (!method_exists($controller, $action)) {
            abort(500, 'Action ' . $class . '@' . $action . ' not found.');
        }
        call_user_func_array([$controller, $action], $params);
    }
}
