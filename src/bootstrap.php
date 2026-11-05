<?php

declare(strict_types=1);

use App\Core\Lang;
use App\Core\Session;

define('BASE_PATH', dirname(__DIR__));

/* --------------------------------------------------------------------------
 | Environment
 |------------------------------------------------------------------------- */
function env(string $key, mixed $default = null): mixed
{
    static $values = null;

    if ($values === null) {
        $values = [];
        $file = BASE_PATH . '/.env';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $v = trim($v);
                if (strlen($v) >= 2 && (($v[0] === '"' && str_ends_with($v, '"')) || ($v[0] === "'" && str_ends_with($v, "'")))) {
                    $v = substr($v, 1, -1);
                }
                $values[trim($k)] = $v;
            }
        }
        foreach ($_ENV as $k => $v) {
            $values[$k] = $v;
        }
    }

    return $values[$key] ?? $default;
}

$config = require BASE_PATH . '/config/config.php';
$GLOBALS['config'] = $config;

date_default_timezone_set($config['app']['timezone']);
mb_internal_encoding('UTF-8');
setlocale(LC_ALL, 'pt_PT.UTF-8', 'Portuguese_Portugal');

/* --------------------------------------------------------------------------
 | Autoloader (PSR-4)
 |------------------------------------------------------------------------- */
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require BASE_PATH . '/src/Support/helpers.php';
require BASE_PATH . '/src/Support/icons.php';

/* --------------------------------------------------------------------------
 | Errors
 |------------------------------------------------------------------------- */
if ($config['app']['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED);
}

/* --------------------------------------------------------------------------
 | Base path detection
 |------------------------------------------------------------------------- */
$detectedBase = '';
if (!empty($config['app']['origin'])) {
    $detectedBase = rtrim((string) parse_url($config['app']['origin'], PHP_URL_PATH), '/');
} else {
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $projectRoot = realpath(BASE_PATH) ?: BASE_PATH;
    $docRoot = str_replace('\\', '/', $docRoot);
    $projectRoot = str_replace('\\', '/', $projectRoot);
    $publicRoot = realpath(BASE_PATH . '/public') ?: '';

    if ($publicRoot && realpath($docRoot) === realpath($publicRoot)) {
        $detectedBase = '';
    } elseif ($docRoot && str_starts_with(strtolower($projectRoot), strtolower($docRoot))) {
        $detectedBase = rtrim(substr($projectRoot, strlen($docRoot)), '/');
    } else {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $pos = strpos($script, '/public/index.php');
        $detectedBase = $pos !== false ? rtrim(substr($script, 0, $pos), '/') : '';
    }
}
define('BASE_URL', rtrim($detectedBase, '/'));
$config['app']['base_path'] = BASE_URL;
$GLOBALS['config'] = $config;

/* --------------------------------------------------------------------------
 | Session + locale
 |------------------------------------------------------------------------- */
Session::start();

$locale = $_SESSION['locale'] ?? ($_COOKIE['trivex_locale'] ?? null);
if (!in_array($locale, $config['locales'], true)) {
    $locale = 'pt';
    $browser = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
    if (str_starts_with($browser, 'en')) {
        $locale = 'en';
    }
}
Lang::setLocale($locale);

require BASE_PATH . '/src/routes.php';
