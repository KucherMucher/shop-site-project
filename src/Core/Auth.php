<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;

        $id = (int) Session::get('user_id', 0);
        if ($id > 0) {
            self::$user = \App\Models\User::find($id);
            if (self::$user && (self::$user['status'] ?? '') !== 'active') {
                self::$user = null;
                Session::forget('user_id');
            }
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function role(): string
    {
        return self::user()['role_slug'] ?? 'guest';
    }

    public static function is(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        self::$user = $user;
        self::$loaded = true;
        \App\Models\Cart::mergeSessionCart((int) $user['id']);
    }

    public static function logout(): void
    {
        Session::forget('user_id');
        Session::regenerate();
        self::$user = null;
        self::$loaded = true;
    }

    public static function refresh(): void
    {
        self::$loaded = false;
        self::$user = null;
        self::user();
    }

    public static function isStaff(): bool
    {
        return self::is('admin', 'moderator');
    }
}
