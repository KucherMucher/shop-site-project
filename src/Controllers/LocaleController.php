<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Lang;

final class LocaleController extends Controller
{
    public function switch(string $locale): never
    {
        if (in_array($locale, config('locales'), true)) {
            Lang::setLocale($locale);
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = parse_url($referer, PHP_URL_HOST);
        if ($referer && $host === ($_SERVER['HTTP_HOST'] ?? '')) {
            redirect($referer);
        }
        redirect('/');
    }
}
