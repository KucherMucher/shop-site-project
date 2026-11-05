<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Lang;
use App\Models\Page;

final class PageController extends Controller
{
    public function show(string $slug): string
    {
        $page = Page::findBySlug($slug);
        if (!$page || empty($page['active'])) {
            throw new HttpException(404);
        }

        if (in_array($slug, ['home', 'loja', 'promocoes', 'apoio'], true)) {
            return match ($slug) {
                'home' => redirect('/'),
                'loja' => redirect('/loja'),
                'promocoes' => redirect('/promocoes'),
                'apoio' => redirect('/suporte'),
            };
        }

        return $this->render('pages/show', [
            'title' => Lang::field($page, 'title') . ' · ' . config('app.name'),
            'page' => $page,
            'content' => Lang::field($page, 'content'),
        ]);
    }
}
