<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Session;

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        $value = $GLOBALS['config'] ?? [];
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('t')) {
    function t(string $key, array $replace = []): string
    {
        return Lang::get($key, $replace);
    }
}

if (!function_exists('locale')) {
    function locale(): string
    {
        return Lang::locale();
    }
}

if (!function_exists('localized')) {
    function localized(array $row, string $field): string
    {
        return Lang::field($row, $field);
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        $path = '/' . ltrim($path, '/');
        return BASE_URL . ($path === '/' ? '/' : $path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('email_url')) {
    function email_url(string $path = '/'): string
    {
        $configured = config('app.origin');
        if (!empty($configured)) {
            return rtrim((string) $configured, '/') . '/' . ltrim($path, '/');
        }
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . url($path);
    }
}

if (!function_exists('slugify')) {
    function slugify(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $map = ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n','&'=>'e'];
        $value = strtr($value, $map);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $status = 302): never
    {
        $target = preg_match('#^https?://#i', $path) ? $path : url($path);
        if (!headers_sent()) {
            header('Location: ' . $target, true, $status);
        }
        exit;
    }
}

if (!function_exists('abort')) {
    function abort(int $status = 404, string $message = ''): never
    {
        throw new \App\Core\HttpException($status, $message);
    }
}

if (!function_exists('back')) {
    function back(string $fallback = '/'): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        if ($ref !== '' && str_starts_with($ref, 'http')) {
            $host = parse_url($ref, PHP_URL_HOST);
            if ($host === ($_SERVER['HTTP_HOST'] ?? '')) {
                redirect($ref);
            }
        }
        redirect($fallback);
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        global $__old_data;
        return $__old_data[$key] ?? $default;
    }
}

if (!function_exists('error')) {
    function error(string $key): string
    {
        global $__errors;
        return (string) ($__errors[$key] ?? '');
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }
}

if (!function_exists('money')) {
    function money(float|int|string $amount): string
    {
        $amount = (float) $amount;
        if (locale() === 'en') {
            return number_format($amount, 2, '.', ',') . ' €';
        }
        return number_format($amount, 2, ',', '.') . ' €';
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, bool $withTime = false): string
    {
        if (!$date) {
            return '—';
        }
        $ts = strtotime($date);
        if (locale() === 'en') {
            return date($withTime ? 'M j, Y · H:i' : 'M j, Y', $ts);
        }
        $months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        return date('j', $ts) . ' ' . $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts) . ($withTime ? ' · ' . date('H:i', $ts) : '');
    }
}

if (!function_exists('discount_percent')) {
    function discount_percent(array $product): int
    {
        $base = (float) ($product['base_price'] ?? 0);
        $sale = (float) ($product['sale_price'] ?? 0);
        if ($sale > 0 && $base > $sale) {
            return (int) round(($base - $sale) / $base * 100);
        }
        return 0;
    }
}

if (!function_exists('product_price')) {
    function product_price(array $product): float
    {
        $sale = (float) ($product['sale_price'] ?? 0);
        return $sale > 0 ? $sale : (float) ($product['base_price'] ?? 0);
    }
}

if (!function_exists('order_status_label')) {
    function order_status_label(string $status): string
    {
        return t('order.status.' . $status);
    }
}

if (!function_exists('order_status_class')) {
    function order_status_class(string $status): string
    {
        return match ($status) {
            'placed' => 'status-blue',
            'shipped' => 'status-amber',
            'received' => 'status-green',
            'lost' => 'status-red',
            'not_received' => 'status-gray',
            default => 'status-gray',
        };
    }
}

if (!function_exists('chat_status_label')) {
    function chat_status_label(string $status): string
    {
        return t('chat.status.' . $status);
    }
}

if (!function_exists('stars')) {
    function stars(float $rating): string
    {
        $html = '<span class="stars" aria-label="' . e(number_format($rating, 1)) . ' / 5">';
        for ($i = 1; $i <= 5; $i++) {
            $cls = $i <= round($rating) ? 'star filled' : 'star';
            $html .= '<svg class="' . $cls . '" viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.4l-5.8 3.1 1.1-6.5L2.6 9.4l6.5-.9L12 2.6z"/></svg>';
        }
        return $html . '</span>';
    }
}

if (!function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $letters ?: 'T';
    }
}

if (!function_exists('query_string')) {
    function query_string(array $overrides = []): string
    {
        $query = array_merge($_GET, $overrides);
        $query = array_filter($query, static fn ($v) => $v !== null && $v !== '');
        return $query ? '?' . http_build_query($query) : '';
    }
}

if (!function_exists('is_route')) {
    function is_route(string $prefix): bool
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if (BASE_URL !== '' && str_starts_with($path, BASE_URL)) {
            $path = substr($path, strlen(BASE_URL)) ?: '/';
        }
        return $prefix === '/' ? $path === '/' : str_starts_with($path, $prefix);
    }
}

if (!function_exists('json_response')) {
    function json_response(array $data, int $status = 200): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('request_json')) {
    function request_json(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}

if (!function_exists('request_input')) {
    function request_input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}

if (!function_exists('sanitize_html')) {
    function sanitize_html(string $html): string
    {
        return strip_tags($html, '<p><br><strong><em><ul><ol><li><h2><h3><a>');
    }
}

if (!function_exists('pagination')) {
    function pagination(int $total, int $perPage, int $current): array
    {
        $pages = max(1, (int) ceil($total / $perPage));
        return [
            'total' => $total,
            'per_page' => $perPage,
            'current' => $current,
            'pages' => $pages,
            'offset' => ($current - 1) * $perPage,
            'has_prev' => $current > 1,
            'has_next' => $current < $pages,
        ];
    }
}
