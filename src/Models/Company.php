<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Company
{
    public static function findForUser(int $userId): ?array
    {
        return Database::fetch(
            'SELECT c.* FROM companies c JOIN users u ON u.company_id = c.id WHERE u.id = ?',
            [$userId]
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT c.*, u.id AS owner_id, u.name AS owner_name, u.email AS owner_email,
                (SELECT COUNT(*) FROM products p WHERE p.company_id = c.id) AS products_count
             FROM companies c LEFT JOIN users u ON u.company_id = c.id AND u.role_id = 3
             WHERE c.id = ?',
            [$id]
        );
    }

    public static function all(): array
    {
        return Database::fetchAll(
            "SELECT c.*, u.name AS owner_name, u.email AS owner_email, u.id AS owner_id,
                (SELECT COUNT(*) FROM products p WHERE p.company_id = c.id) AS products_count,
                (SELECT COUNT(*) FROM products p2 WHERE p2.company_id = c.id AND p2.status = 'pending') AS pending_products
             FROM companies c
             LEFT JOIN users u ON u.company_id = c.id AND u.role_id = 3
             ORDER BY FIELD(c.status,'pending','approved','rejected'), c.created_at DESC"
        );
    }

    public static function create(array $data): int
    {
        return Database::insert('companies', $data);
    }

    public static function setStatus(int $id, string $status, ?int $adminId = null, ?string $note = null): void
    {
        $data = ['status' => $status, 'review_note' => $note];
        if ($status === 'approved') {
            $data['approved_by'] = $adminId;
            $data['approved_at'] = date('Y-m-d H:i:s');
        }
        Database::update('companies', $data, 'id = :id', ['id' => $id]);
    }

    public static function pendingCount(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM companies WHERE status = 'pending'");
    }

    public static function approvedCount(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM companies WHERE status = 'approved'");
    }

    public static function proposals(): array
    {
        $rows = Database::fetchAll(
            "SELECT p.*, co.name AS company_name, c.name_pt AS category_name,
                    c.name_pt AS category_name_pt, c.name_en AS category_name_en, c.slug AS category_slug
             FROM products p
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN companies co ON co.id = p.company_id
             WHERE p.status = 'pending'
             ORDER BY p.created_at DESC"
        );
        return array_map([Product::class, 'decode'], $rows);
    }
}
