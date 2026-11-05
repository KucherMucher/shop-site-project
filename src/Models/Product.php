<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Product
{
    public static function decode(?array $row): ?array
    {
        if (!$row) {
            return null;
        }
        $row['specs'] = self::json($row['specs'] ?? null);
        $row['gallery'] = self::json($row['gallery'] ?? null);
        return $row;
    }

    private static function json(?string $value): array
    {
        if (!$value) {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function find(int $id, bool $activeOnly = false): ?array
    {
        $sql = 'SELECT p.*, c.slug AS category_slug, c.name_pt AS category_name_pt, c.name_en AS category_name_en,
                    co.name AS company_name
                FROM products p
                JOIN categories c ON c.id = p.category_id
                LEFT JOIN companies co ON co.id = p.company_id
                WHERE p.id = ?';
        if ($activeOnly) {
            $sql .= " AND p.status = 'active'";
        }
        return self::decode(Database::fetch($sql, [$id]));
    }

    public static function findBySlug(string $slug, bool $activeOnly = true): ?array
    {
        $sql = 'SELECT p.*, c.slug AS category_slug, c.name_pt AS category_name_pt, c.name_en AS category_name_en,
                    co.name AS company_name
                FROM products p
                JOIN categories c ON c.id = p.category_id
                LEFT JOIN companies co ON co.id = p.company_id
                WHERE p.slug = ?';
        if ($activeOnly) {
            $sql .= " AND p.status = 'active'";
        }
        return self::decode(Database::fetch($sql, [$slug]));
    }

    public static function variants(int $productId, bool $activeOnly = true): array
    {
        $sql = 'SELECT * FROM product_variants WHERE product_id = ?';
        if ($activeOnly) {
            $sql .= ' AND active = 1';
        }
        return Database::fetchAll($sql . ' ORDER BY id', [$productId]);
    }

    public static function variant(int $id): ?array
    {
        return Database::fetch(
            'SELECT v.*, p.name_pt, p.name_en, p.slug, p.image, p.base_price, p.sale_price, p.status AS product_status
             FROM product_variants v JOIN products p ON p.id = v.product_id WHERE v.id = ?',
            [$id]
        );
    }

    /**
     * Colour-specific photos, grouped by colour name.
     *
     * @return array<string, list<string>>
     */
    public static function colorImages(int $productId): array
    {
        $rows = Database::fetchAll(
            'SELECT color, image FROM product_color_images WHERE product_id = ? ORDER BY sort_order, id',
            [$productId]
        );
        $images = [];
        foreach ($rows as $row) {
            $images[(string) $row['color']][] = (string) $row['image'];
        }
        return $images;
    }

    public static function rating(int $productId): array
    {
        $row = Database::fetch(
            'SELECT COALESCE(AVG(rating), 0) AS avg_rating, COUNT(*) AS reviews_count FROM reviews WHERE product_id = ?',
            [$productId]
        );
        return ['avg' => round((float) ($row['avg_rating'] ?? 0), 1), 'count' => (int) ($row['reviews_count'] ?? 0)];
    }

    /**
     * Catalog query with all storefront filters.
     */
    public static function search(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $where = ["p.status = 'active'", 'c.active = 1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(p.name_pt LIKE :q1 OR p.name_en LIKE :q2 OR p.brand LIKE :q3 OR p.short_pt LIKE :q4 OR p.short_en LIKE :q5)';
            $like = '%' . $filters['q'] . '%';
            for ($qi = 1; $qi <= 5; $qi++) {
                $params['q' . $qi] = $like;
            }
        }
        if (!empty($filters['category'])) {
            $where[] = 'c.slug = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['brand'])) {
            if (is_array($filters['brand'])) {
                $placeholders = [];
                foreach (array_values($filters['brand']) as $i => $brand) {
                    $key = 'brand' . $i;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $brand;
                }
                $where[] = 'p.brand IN (' . implode(',', $placeholders) . ')';
            } else {
                $where[] = 'p.brand = :brand';
                $params['brand'] = $filters['brand'];
            }
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '' && is_numeric($filters['min_price'])) {
            $where[] = 'COALESCE(p.sale_price, p.base_price) >= :min_price';
            $params['min_price'] = (float) $filters['min_price'];
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '' && is_numeric($filters['max_price'])) {
            $where[] = 'COALESCE(p.sale_price, p.base_price) <= :max_price';
            $params['max_price'] = (float) $filters['max_price'];
        }
        if (!empty($filters['size'])) {
            $where[] = 'EXISTS (SELECT 1 FROM product_variants v WHERE v.product_id = p.id AND v.active = 1 AND v.size = :size)';
            $params['size'] = $filters['size'];
        }
        if (!empty($filters['in_stock'])) {
            $where[] = 'EXISTS (SELECT 1 FROM product_variants v2 WHERE v2.product_id = p.id AND v2.active = 1 AND v2.stock > 0)';
        }
        if (!empty($filters['promo'])) {
            $where[] = 'p.sale_price IS NOT NULL AND p.sale_price < p.base_price';
        }
        if (!empty($filters['featured'])) {
            $where[] = 'p.featured = 1';
        }
        if (!empty($filters['is_new'])) {
            $where[] = 'p.is_new = 1';
        }

        $whereSql = implode(' AND ', $where);

        $sort = $filters['sort'] ?? 'featured';
        $orderBy = match ($sort) {
            'price_asc' => 'COALESCE(p.sale_price, p.base_price) ASC',
            'price_desc' => 'COALESCE(p.sale_price, p.base_price) DESC',
            'newest' => 'p.created_at DESC',
            'name' => 'p.name_pt ASC',
            'rating' => '(SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.product_id = p.id) DESC',
            default => 'p.featured DESC, p.created_at DESC',
        };

        $total = (int) Database::scalar(
            "SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE $whereSql",
            $params
        );
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT p.*, c.slug AS category_slug, c.name_pt AS category_name_pt, c.name_en AS category_name_en,
                    (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.product_id = p.id) AS avg_rating,
                    (SELECT COUNT(*) FROM reviews r2 WHERE r2.product_id = p.id) AS reviews_count,
                    (SELECT COALESCE(SUM(v.stock),0) FROM product_variants v WHERE v.product_id = p.id AND v.active = 1) AS total_stock
                FROM products p JOIN categories c ON c.id = p.category_id
                WHERE $whereSql
                ORDER BY $orderBy
                LIMIT $perPage OFFSET $offset";
        $items = array_map([self::class, 'decode'], Database::fetchAll($sql, $params));

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public static function featured(int $limit = 4): array
    {
        $rows = Database::fetchAll(
            "SELECT p.*, c.slug AS category_slug, c.name_pt AS category_name_pt, c.name_en AS category_name_en,
                (SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.product_id = p.id) AS avg_rating,
                (SELECT COUNT(*) FROM reviews r2 WHERE r2.product_id = p.id) AS reviews_count,
                (SELECT COALESCE(SUM(v.stock),0) FROM product_variants v WHERE v.product_id = p.id AND v.active = 1) AS total_stock
             FROM products p JOIN categories c ON c.id = p.category_id
             WHERE p.status = 'active' AND c.active = 1 AND p.featured = 1
             ORDER BY p.created_at DESC LIMIT $limit"
        );
        return array_map([self::class, 'decode'], $rows);
    }

    public static function newest(int $limit = 8): array
    {
        $rows = Database::fetchAll(
            "SELECT p.*, c.slug AS category_slug, c.name_pt AS category_name_pt, c.name_en AS category_name_en,
                (SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.product_id = p.id) AS avg_rating,
                (SELECT COUNT(*) FROM reviews r2 WHERE r2.product_id = p.id) AS reviews_count,
                (SELECT COALESCE(SUM(v.stock),0) FROM product_variants v WHERE v.product_id = p.id AND v.active = 1) AS total_stock
             FROM products p JOIN categories c ON c.id = p.category_id
             WHERE p.status = 'active' AND c.active = 1
             ORDER BY p.is_new DESC, p.created_at DESC LIMIT $limit"
        );
        return array_map([self::class, 'decode'], $rows);
    }

    public static function promotions(): array
    {
        $rows = Database::fetchAll(
            "SELECT p.*, c.slug AS category_slug, c.name_pt AS category_name_pt, c.name_en AS category_name_en,
                (SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.product_id = p.id) AS avg_rating,
                (SELECT COUNT(*) FROM reviews r2 WHERE r2.product_id = p.id) AS reviews_count,
                (SELECT COALESCE(SUM(v.stock),0) FROM product_variants v WHERE v.product_id = p.id AND v.active = 1) AS total_stock
             FROM products p JOIN categories c ON c.id = p.category_id
             WHERE p.status = 'active' AND c.active = 1 AND p.sale_price IS NOT NULL AND p.sale_price < p.base_price
             ORDER BY ((p.base_price - p.sale_price) / p.base_price) DESC"
        );
        return array_map([self::class, 'decode'], $rows);
    }

    public static function related(int $categoryId, int $excludeId, int $limit = 4): array
    {
        $rows = Database::fetchAll(
            "SELECT p.*, c.slug AS category_slug, c.name_pt AS category_name_pt, c.name_en AS category_name_en,
                (SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.product_id = p.id) AS avg_rating,
                (SELECT COUNT(*) FROM reviews r2 WHERE r2.product_id = p.id) AS reviews_count,
                (SELECT COALESCE(SUM(v.stock),0) FROM product_variants v WHERE v.product_id = p.id AND v.active = 1) AS total_stock
             FROM products p JOIN categories c ON c.id = p.category_id
             WHERE p.status = 'active' AND c.active = 1 AND p.category_id = ? AND p.id <> ?
             ORDER BY p.featured DESC, RAND() LIMIT $limit",
            [$categoryId, $excludeId]
        );
        return array_map([self::class, 'decode'], $rows);
    }

    public static function brands(): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT brand FROM products WHERE status = 'active' ORDER BY brand"
        );
    }

    public static function sizes(): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT v.size FROM product_variants v JOIN products p ON p.id = v.product_id
             WHERE v.active = 1 AND p.status = 'active'
             ORDER BY CASE WHEN v.size REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, v.size"
        );
    }

    public static function reviews(int $productId): array
    {
        return Database::fetchAll(
            'SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON u.id = r.user_id
             WHERE r.product_id = ? ORDER BY r.created_at DESC',
            [$productId]
        );
    }

    public static function allForAdmin(): array
    {
        $rows = Database::fetchAll(
            "SELECT p.*, c.name_pt AS category_name, co.name AS company_name,
                (SELECT COALESCE(SUM(v.stock),0) FROM product_variants v WHERE v.product_id = p.id) AS total_stock,
                (SELECT COUNT(*) FROM product_variants v2 WHERE v2.product_id = p.id AND v2.stock <= 2) AS low_stock_variants
             FROM products p JOIN categories c ON c.id = p.category_id
             LEFT JOIN companies co ON co.id = p.company_id
             ORDER BY p.status = 'pending' DESC, p.created_at DESC"
        );
        return array_map([self::class, 'decode'], $rows);
    }

    public static function variantsWithProduct(): array
    {
        return Database::fetchAll(
            "SELECT v.*, p.name_pt, p.name_en, p.slug AS product_slug, p.image, p.brand
             FROM product_variants v JOIN products p ON p.id = v.product_id
             WHERE p.status = 'active'
             ORDER BY p.name_pt, v.size"
        );
    }

    public static function create(array $data): int
    {
        return Database::insert('products', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('products', $data, 'id = :id', ['id' => $id]);
    }

    public static function setStatus(int $id, string $status, ?string $note = null, ?int $adminId = null): void
    {
        $data = ['status' => $status, 'rejection_note' => $note];
        if ($status === 'active') {
            $data['approved_by'] = $adminId;
            $data['approved_at'] = date('Y-m-d H:i:s');
        }
        Database::update('products', $data, 'id = :id', ['id' => $id]);
    }

    public static function stockSummary(): array
    {
        return Database::fetch(
            'SELECT COALESCE(SUM(stock),0) AS units, COUNT(*) AS variants,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) AS out_of_stock,
                SUM(CASE WHEN stock > 0 AND stock <= 2 THEN 1 ELSE 0 END) AS low_stock
             FROM product_variants WHERE active = 1'
        ) ?? [];
    }

    public static function countActive(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM products WHERE status = 'active'");
    }

    public static function countPending(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM products WHERE status = 'pending'");
    }
}
