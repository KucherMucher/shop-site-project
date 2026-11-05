<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Review
{
    public static function forProduct(int $productId): array
    {
        return Database::fetchAll(
            'SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON u.id = r.user_id
             WHERE r.product_id = ? ORDER BY r.created_at DESC',
            [$productId]
        );
    }

    public static function findForUser(int $productId, int $userId): ?array
    {
        return Database::fetch('SELECT * FROM reviews WHERE product_id = ? AND user_id = ?', [$productId, $userId]);
    }

    public static function create(int $productId, int $userId, int $rating, string $title, string $body): int
    {
        return Database::insert('reviews', [
            'product_id' => $productId,
            'user_id' => $userId,
            'rating' => max(1, min(5, $rating)),
            'title' => $title,
            'body' => $body,
        ]);
    }

    public static function update(int $id, int $userId, int $rating, string $title, string $body): void
    {
        Database::update('reviews', [
            'rating' => max(1, min(5, $rating)),
            'title' => $title,
            'body' => $body,
        ], 'id = :id AND user_id = :uid', ['id' => $id, 'uid' => $userId]);
    }

    public static function latest(int $limit = 8): array
    {
        return Database::fetchAll(
            'SELECT r.*, u.name AS user_name, p.name_pt AS product_name, p.slug AS product_slug
             FROM reviews r JOIN users u ON u.id = r.user_id JOIN products p ON p.id = r.product_id
             ORDER BY r.created_at DESC LIMIT ' . max(1, $limit)
        );
    }

    public static function count(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM reviews');
    }

    public static function average(): float
    {
        return round((float) Database::scalar('SELECT COALESCE(AVG(rating), 0) FROM reviews'), 2);
    }
}
