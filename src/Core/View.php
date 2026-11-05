<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/app'): string
    {
        $file = BASE_PATH . '/src/Views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View [$view] not found.");
        }

        extract($data, EXTR_SKIP);
        $flashes = Session::pullFlash();
        $errors = Session::getErrors();
        $old = Session::getOld();
        $currentUser = Auth::user();
        $locale = Lang::locale();

        $GLOBALS['__old_data'] = $old;
        $GLOBALS['__errors'] = $errors;

        ob_start();
        require $file;
        $content = ob_get_clean();

        if ($layout === '') {
            return $content;
        }

        $layoutFile = BASE_PATH . '/src/Views/' . $layout . '.php';
        if (!is_file($layoutFile)) {
            throw new \RuntimeException("Layout [$layout] not found.");
        }
        ob_start();
        require $layoutFile;
        return ob_get_clean();
    }

    public static function partial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require BASE_PATH . '/src/Views/' . $view . '.php';
    }

    public static function raw(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require BASE_PATH . '/src/Views/' . $view . '.php';
        return ob_get_clean();
    }
}
