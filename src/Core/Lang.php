<?php

declare(strict_types=1);

namespace App\Core;

final class Lang
{
    private static string $locale = 'pt';
    private static array $dictionary = [];

    public static function setLocale(string $locale): void
    {
        if (!in_array($locale, config('locales'), true)) {
            $locale = 'pt';
        }
        self::$locale = $locale;
        self::$dictionary = require BASE_PATH . '/lang/' . $locale . '.php';
        $_SESSION['locale'] = $locale;
        setcookie('trivex_locale', $locale, [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => BASE_URL !== '' ? BASE_URL . '/' : '/',
            'samesite' => 'Lax',
        ]);
        if (function_exists('locale_set_default')) {
            // keep numeric formatting explicit below
        }
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function get(string $key, array $replace = []): string
    {
        $value = self::$dictionary[$key] ?? $key;
        foreach ($replace as $search => $replacement) {
            $value = str_replace(':' . $search, (string) $replacement, $value);
        }
        return $value;
    }

    public static function has(string $key): bool
    {
        return isset(self::$dictionary[$key]);
    }

    public static function all(): array
    {
        return self::$dictionary;
    }

    public static function field(array $row, string $field): string
    {
        $localized = $row[$field . '_' . self::$locale] ?? null;
        return (string) ($localized ?? $row[$field . '_pt'] ?? $row[$field] ?? '');
    }
}
