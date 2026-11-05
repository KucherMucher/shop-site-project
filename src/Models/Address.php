<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Address
{
    public static function forUser(int $userId): array
    {
        return Database::fetchAll(
            'SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default_shipping DESC, created_at DESC',
            [$userId]
        );
    }

    public static function find(int $id, int $userId): ?array
    {
        return Database::fetch('SELECT * FROM addresses WHERE id = ? AND user_id = ?', [$id, $userId]);
    }

    public static function create(int $userId, array $data): int
    {
        return Database::insert('addresses', array_merge($data, ['user_id' => $userId]));
    }

    public static function update(int $id, int $userId, array $data): void
    {
        Database::update('addresses', $data, 'id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
    }

    public static function delete(int $id, int $userId): void
    {
        Database::query('DELETE FROM addresses WHERE id = ? AND user_id = ?', [$id, $userId]);
    }

    public static function setDefault(int $id, int $userId, string $type = 'shipping'): void
    {
        $column = $type === 'billing' ? 'is_default_billing' : 'is_default_shipping';
        Database::query("UPDATE addresses SET $column = 0 WHERE user_id = ?", [$userId]);
        Database::query("UPDATE addresses SET $column = 1 WHERE id = ? AND user_id = ?", [$id, $userId]);
    }
}
