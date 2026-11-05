<?php

declare(strict_types=1);

/**
 * TRIVEX front controller.
 * Works both behind Laragon (project root / virtual host) and with PHP's
 * built-in server: php -S 127.0.0.1:8090 -t public public/router.php
 */

use App\Core\Csrf;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\View;

require dirname(__DIR__) . '/src/bootstrap.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

try {
    if ($method === 'POST' && !Csrf::verify($_POST['_token'] ?? null)) {
        throw new HttpException(419, t('errors.csrf'));
    }

    /** @var \App\Core\Router $router */
    require BASE_PATH . '/src/routes.php';
    echo $router->dispatch($method, $uri);
} catch (HttpException $e) {
    http_response_code($e->statusCode);
    $message = $e->getMessage();

    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        json_response(['error' => $message ?: 'HTTP ' . $e->statusCode], $e->statusCode);
    }

    try {
        echo View::render('errors/error', [
            'title' => t('errors.' . ($e->statusCode === 403 ? '403_title' : ($e->statusCode === 419 ? '419_title' : ($e->statusCode === 429 ? '429_title' : '404_title')))) . ' · ' . config('app.name'),
            'status' => $e->statusCode,
            'message' => $message,
        ]);
    } catch (\Throwable) {
        echo '<h1>' . e((string) $e->statusCode) . '</h1><p>' . e($message) . '</p>';
    }
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[TRIVEX] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    $dbError = $e instanceof \PDOException || str_contains($e->getMessage(), 'Database connection failed');
    if ($dbError) {
        try {
            echo View::render('errors/database', ['title' => t('errors.db')], '');
        } catch (\Throwable) {
            echo '<h1>Database error</h1><p>Run <code>php database/install.php</code> and check <code>.env</code>.</p>';
        }
        exit;
    }

    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        json_response(['error' => config('app.debug') ? $e->getMessage() : t('errors.500_text')], 500);
    }

    try {
        echo View::render('errors/error', [
            'title' => t('errors.500_title') . ' · ' . config('app.name'),
            'status' => 500,
            'message' => config('app.debug') ? $e->getMessage() : '',
            'debug' => config('app.debug') ? $e : null,
        ]);
    } catch (\Throwable) {
        echo '<h1>500</h1><p>' . (config('app.debug') ? e($e->getMessage()) : 'Unexpected error') . '</p>';
    }
}
