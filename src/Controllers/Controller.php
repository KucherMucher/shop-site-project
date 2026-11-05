<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'layouts/app'): string
    {
        return View::render($view, $data, $layout);
    }

    protected function pageNumber(): int
    {
        return max(1, (int) ($_GET['page'] ?? 1));
    }

    protected function wantsJson(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
            || isset($_GET['json']);
    }
}
