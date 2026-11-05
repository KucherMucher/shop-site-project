<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];
    private array $named = [];

    public function get(string $path, array|callable $handler, array $middleware = [], ?string $name = null): void
    {
        $this->add('GET', $path, $handler, $middleware, $name);
    }

    public function post(string $path, array|callable $handler, array $middleware = [], ?string $name = null): void
    {
        $this->add('POST', $path, $handler, $middleware, $name);
    }

    public function add(string $method, string $path, array|callable $handler, array $middleware = [], ?string $name = null): void
    {
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            $pattern = '#^/$#';
            $params = [];
        } else {
            $params = [];
            $pattern = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function ($m) use (&$params) {
                $params[] = $m[1];
                return '([^/]+)';
            }, $path);
            $pattern = '#^' . $pattern . '/?$#';
        }

        $this->routes[] = compact('method', 'path', 'pattern', 'params', 'handler', 'middleware', 'name');
        if ($name) {
            $this->named[$name] = $path;
        }
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $method = strtoupper($method);
        $path = rawurldecode(parse_url($uri, PHP_URL_PATH) ?: '/');

        if (BASE_URL !== '' && str_starts_with($path, BASE_URL)) {
            $path = substr($path, strlen(BASE_URL)) ?: '/';
        }
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }
            array_shift($matches);
            $args = [];
            foreach ($route['params'] as $i => $name) {
                $args[$name] = $matches[$i] ?? null;
            }

            foreach ($route['middleware'] as $middleware) {
                Middleware::handle($middleware);
            }

            $handler = $route['handler'];
            if (is_callable($handler) && !is_array($handler)) {
                return $handler(...array_values($args));
            }

            [$class, $action] = $handler;
            $controller = new $class();
            return $controller->$action(...array_values($args));
        }

        throw new HttpException(404);
    }

    public function named(string $name, array $params = []): string
    {
        $path = $this->named[$name] ?? '/';
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', rawurlencode((string) $value), $path);
        }
        return url($path);
    }
}
