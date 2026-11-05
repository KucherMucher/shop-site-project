<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name, c.name AS company_name, c.status AS company_status
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN companies c ON c.id = u.company_id
             WHERE u.id = ?',
            [$id]
        );
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = ?',
            [strtolower(trim($email))]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert('users', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('users', $data, 'id = :id', ['id' => $id]);
    }

    public static function allWithStats(string $search = '', string $role = '', string $status = ''): array
    {
        $sql = "SELECT u.*, r.slug AS role_slug, r.name AS role_name, co.name AS company_name,
                    (SELECT COUNT(*) FROM reviews rv WHERE rv.user_id = u.id) AS reviews_count,
                    (SELECT COALESCE(SUM(oi.quantity), 0) FROM orders o
                        JOIN order_items oi ON oi.order_id = o.id
                        WHERE o.user_id = u.id AND o.status NOT IN ('lost','not_received')) AS products_bought,
                    (SELECT COUNT(*) FROM orders o2 WHERE o2.user_id = u.id) AS orders_count,
                    (SELECT COALESCE(SUM(o3.total), 0) FROM orders o3 WHERE o3.user_id = u.id AND o3.payment_status = 'paid') AS total_spent
                FROM users u
                JOIN roles r ON r.id = u.role_id
                LEFT JOIN companies co ON co.id = u.company_id
                WHERE 1=1";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (u.name LIKE :q1 OR u.email LIKE :q2)';
            $like = '%' . $search . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
        }
        if ($role !== '') {
            $sql .= ' AND r.slug = :role';
            $params['role'] = $role;
        }
        if ($status !== '') {
            $sql .= ' AND u.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY u.created_at DESC';
        return Database::fetchAll($sql, $params);
    }

    public static function setStatus(int $id, string $status): void
    {
        Database::update('users', ['status' => $status], 'id = :id', ['id' => $id]);
    }

    public static function countByRole(string $role): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = ?',
            [$role]
        );
    }

    public static function totalCustomers(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'user'");
    }

    public static function newThisMonth(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM users WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
    }

    public static function moderators(): array
    {
        return Database::fetchAll(
            "SELECT u.*, r.slug AS role_slug,
                (SELECT COUNT(*) FROM chats c WHERE c.moderator_id = u.id AND c.status = 'concluded') AS chats_handled
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE r.slug = 'moderator'
             ORDER BY u.created_at DESC"
        );
    }

    public static function createModerator(string $name, string $email, string $password): int
    {
        $roleId = (int) Database::scalar("SELECT id FROM roles WHERE slug = 'moderator'");
        return self::create([
            'role_id' => $roleId,
            'name' => $name,
            'email' => strtolower($email),
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function admins(): array
    {
        return Database::fetchAll("SELECT u.* FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'admin'");
    }

    public static function verifyToken(string $token): ?array
    {
        return Database::fetch(
            'SELECT * FROM users WHERE verification_token = ? AND verification_expires_at > NOW() LIMIT 1',
            [$token]
        );
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = ?';
        $params = [strtolower(trim($email))];
        if ($exceptId) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        return (int) Database::scalar($sql, $params) > 0;
    }
}
