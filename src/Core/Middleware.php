<?php

declare(strict_types=1);

namespace App\Core;

final class Middleware
{
    public static function handle(string $middleware): void
    {
        [$name, $args] = array_pad(explode(':', $middleware, 2), 2, '');
        $args = $args !== '' ? explode(',', $args) : [];

        match ($name) {
            'auth' => self::auth(),
            'guest' => self::guest(),
            'role' => self::role($args),
            'verified' => self::verified(),
            'company' => self::company(),
            'throttle' => self::throttle($args),
            default => null,
        };
    }

    private static function auth(): void
    {
        if (!Auth::check()) {
            Session::set('intended_url', $_SERVER['REQUEST_URI'] ?? null);
            Session::flash('warning', t('auth.login_required'));
            redirect('/login');
        }
    }

    private static function guest(): void
    {
        if (Auth::check()) {
            redirect(Auth::isStaff() ? '/admin' : '/conta');
        }
    }

    private static function role(array $roles): void
    {
        if (!Auth::check()) {
            redirect('/login');
        }
        if (!Auth::is(...$roles)) {
            throw new HttpException(403);
        }
    }

    private static function verified(): void
    {
        $user = Auth::user();
        if (!$user || empty($user['email_verified_at'])) {
            Session::flash('warning', t('auth.verify_required'));
            redirect('/conta');
        }
    }

    private static function company(): void
    {
        if (!Auth::check() || !Auth::is('company')) {
            throw new HttpException(403);
        }
        $company = \App\Models\Company::findForUser((int) Auth::id());
        if (!$company || $company['status'] !== 'approved') {
            redirect('/empresa/pendente');
        }
    }

    private static function throttle(array $args): void
    {
        $key = 'throttle_' . ($args[0] ?? 'default');
        $limit = (int) ($args[1] ?? 8);
        $window = (int) ($args[2] ?? 60);
        $data = Session::get($key, ['count' => 0, 'start' => time()]);
        if (time() - $data['start'] > $window) {
            $data = ['count' => 0, 'start' => time()];
        }
        $data['count']++;
        Session::set($key, $data);
        if ($data['count'] > $limit) {
            throw new HttpException(429, t('errors.too_many_requests'));
        }
    }
}
