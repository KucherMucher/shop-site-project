<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Page
{
    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM pages ORDER BY position, id');
    }

    public static function findBySlug(string $slug, bool $activeOnly = false): ?array
    {
        $sql = 'SELECT * FROM pages WHERE slug = ?';
        if ($activeOnly) {
            $sql .= ' AND active = 1';
        }
        return Database::fetch($sql, [$slug]);
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM pages WHERE id = ?', [$id]);
    }

    public static function setActive(int $id, int $active): void
    {
        Database::update('pages', ['active' => $active], 'id = :id', ['id' => $id]);
    }

    public static function navPages(): array
    {
        return Database::fetchAll(
            "SELECT * FROM pages WHERE active = 1 AND slug IN ('sobre-nos','devolucoes','termos') ORDER BY position"
        );
    }
}
