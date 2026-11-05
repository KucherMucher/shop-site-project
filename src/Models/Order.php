<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Order
{
    public const STATUSES = ['placed', 'shipped', 'received', 'lost', 'not_received'];

    public static function create(array $data, array $items): array
    {
        return Database::transaction(function () use ($data, $items) {
            $nextId = (int) Database::scalar('SELECT COALESCE(MAX(id), 0) + 1000 FROM orders');
            $orderNumber = 'TRV-' . date('Y') . '-' . str_pad((string) ($nextId + 1), 4, '0', STR_PAD_LEFT);

            $orderId = Database::insert('orders', array_merge($data, [
                'order_number' => $orderNumber,
                'created_at' => date('Y-m-d H:i:s'),
            ]));

            foreach ($items as $item) {
                $variant = Database::fetch(
                    'SELECT v.*, p.name_pt, p.name_en, p.base_price, p.sale_price FROM product_variants v
                     JOIN products p ON p.id = v.product_id WHERE v.id = ? FOR UPDATE',
                    [$item['variant_id']]
                );
                if (!$variant || (int) $variant['stock'] < (int) $item['quantity']) {
                    throw new \RuntimeException(t('checkout.stock_error', ['name' => localized($variant ?? [], 'name')]));
                }
                Database::query(
                    'UPDATE product_variants SET stock = stock - ? WHERE id = ?',
                    [$item['quantity'], $item['variant_id']]
                );
                Database::insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $variant['product_id'],
                    'variant_id' => $variant['id'],
                    'product_name' => $variant['name_pt'],
                    'variant_label' => $variant['size'] . ' · ' . $variant['color'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['line_total'],
                ]);
            }

            return ['id' => $orderId, 'order_number' => $orderNumber];
        });
    }

    public static function findByNumber(string $number): ?array
    {
        return Database::fetch('SELECT * FROM orders WHERE order_number = ?', [$number]);
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT o.*, u.name AS user_name FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.id = ?',
            [$id]
        );
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::fetch('SELECT * FROM orders WHERE id = ? AND user_id = ?', [$id, $userId]);
    }

    public static function items(int $orderId): array
    {
        return Database::fetchAll(
            'SELECT oi.*, p.slug AS product_slug, p.image FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ? ORDER BY oi.id',
            [$orderId]
        );
    }

    public static function forUser(int $userId): array
    {
        return Database::fetchAll(
            'SELECT o.*, (SELECT SUM(quantity) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
             FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC',
            [$userId]
        );
    }

    public static function userStats(int $userId): array
    {
        $row = Database::fetch(
            "SELECT COUNT(*) AS orders_count,
                COALESCE(SUM(CASE WHEN status IN ('placed','shipped','received') THEN total ELSE 0 END), 0) AS total_spent,
                COALESCE((SELECT SUM(oi.quantity) FROM order_items oi JOIN orders o2 ON o2.id = oi.order_id WHERE o2.user_id = ?), 0) AS products_count
             FROM orders WHERE user_id = ?",
            [$userId, $userId]
        );
        return $row ?? ['orders_count' => 0, 'total_spent' => 0, 'products_count' => 0];
    }

    public static function latestForUser(int $userId, int $limit = 3): array
    {
        return Database::fetchAll(
            'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . max(1, $limit),
            [$userId]
        );
    }

    public static function all(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status']) && in_array($filters['status'], self::STATUSES, true)) {
            $where[] = 'o.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(o.order_number LIKE :q1 OR o.email LIKE :q2 OR o.shipping_name LIKE :q3)';
            $like = '%' . $filters['q'] . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
            $params['q3'] = $like;
        }
        $whereSql = implode(' AND ', $where);
        $total = (int) Database::scalar("SELECT COUNT(*) FROM orders o WHERE $whereSql", $params);
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows = Database::fetchAll(
            "SELECT o.*, u.name AS user_name,
                (SELECT SUM(quantity) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
             FROM orders o LEFT JOIN users u ON u.id = o.user_id
             WHERE $whereSql ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        );
        return [
            'items' => $rows,
            'total' => $total,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'per_page' => $perPage,
        ];
    }

    public static function statusCounts(): array
    {
        $rows = Database::fetchAll('SELECT status, COUNT(*) AS total FROM orders GROUP BY status');
        $counts = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    public static function updateStatus(int $id, string $status, ?string $tracking = null): void
    {
        $data = ['status' => $status];
        if ($tracking !== null) {
            $data['tracking_code'] = $tracking;
        }
        Database::update('orders', $data, 'id = :id', ['id' => $id]);
    }

    public static function revenue(): float
    {
        return (float) Database::scalar("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid'");
    }

    public static function total(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM orders');
    }

    public static function thisMonth(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM orders WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
    }

    public static function recent(int $limit = 6): array
    {
        return Database::fetchAll(
            'SELECT o.*, u.name AS user_name FROM orders o LEFT JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC LIMIT ' . max(1, $limit)
        );
    }

    public static function monthlySeries(int $months = 6): array
    {
        $rows = Database::fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS orders, COALESCE(SUM(total),0) AS revenue
             FROM orders WHERE created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL ? MONTH)
             GROUP BY ym ORDER BY ym",
            [$months - 1]
        );
        return $rows;
    }
}
