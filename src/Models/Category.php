<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Category
{
    private static function decode(array $row): array
    {
        return $row;
    }

    public static function allActive(): array
    {
        return Database::fetchAll(
            "SELECT c.*,
                (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'active') AS products_count
             FROM categories c WHERE c.active = 1 ORDER BY c.position, c.name_pt"
        );
    }

    public static function all(): array
    {
        return Database::fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'active') AS products_count
             FROM categories c ORDER BY c.position, c.name_pt"
        );
    }

    public static function findBySlug(string $slug, bool $activeOnly = true): ?array
    {
        $sql = 'SELECT * FROM categories WHERE slug = ?';
        if ($activeOnly) {
            $sql .= ' AND active = 1';
        }
        return Database::fetch($sql, [$slug]);
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE id = ?', [$id]);
    }
}
