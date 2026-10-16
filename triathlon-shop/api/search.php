<?php
declare(strict_types=1);

/**
 * Product search endpoint. Returns JSON.
 *
 * Parameters (all optional):
 *   q         search text. Every word must match the name, brand, description or category.
 *   category  category slug, e.g. "swim"
 *   sort      relevance (default) | price_asc | price_desc | name
 *   featured  if present, only featured products
 *   limit     1-48 (default 24)
 */

require __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$q        = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$sort     = (string) ($_GET['sort'] ?? 'relevance');
$limit    = max(1, min(48, (int) ($_GET['limit'] ?? 24)));

$terms = preg_split('/\s+/', mb_substr($q, 0, 100), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$terms = array_slice($terms, 0, 6);

// The $where entries and the $params are always added in the same order.
$where  = [];
$params = [];

foreach ($terms as $term) {
    // Escape % and _ so users cannot use them as wildcards.
    $like    = '%' . addcslashes($term, '%_\\') . '%';
    $where[] = '(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
    array_push($params, $like, $like, $like, $like);
}

if ($category !== '') {
    $where[]  = 'c.slug = ?';
    $params[] = $category;
}

if (isset($_GET['featured'])) {
    $where[] = 'p.featured = 1';
}

// ORDER BY. Its placeholders come after the WHERE ones, so its params go last.
switch ($sort) {
    case 'price_asc':
        $order = 'COALESCE(p.sale_price, p.price) ASC, p.name';
        break;
    case 'price_desc':
        $order = 'COALESCE(p.sale_price, p.price) DESC, p.name';
        break;
    case 'name':
        $order = 'p.name';
        break;
    default:
        if ($terms) {
            // Name matches first, then brand matches, then the rest.
            $first = '%' . addcslashes($terms[0], '%_\\') . '%';
            $order = 'CASE WHEN p.name LIKE ? THEN 0 WHEN p.brand LIKE ? THEN 1 ELSE 2 END, p.name';
            array_push($params, $first, $first);
        } else {
            $order = 'p.featured DESC, p.name';
        }
}

$sql = 'SELECT p.id, p.name, p.brand, p.description, p.price, p.sale_price, p.stock,
               c.slug AS category_slug, c.name AS category_name
        FROM products p
        JOIN categories c ON c.id = p.category_id'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY $order LIMIT $limit";

try {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    $products = array_map(static fn(array $r): array => [
        'id'            => (int) $r['id'],
        'name'          => $r['name'],
        'brand'         => $r['brand'],
        'description'   => $r['description'],
        'price'         => (float) $r['price'],
        'sale_price'    => $r['sale_price'] !== null ? (float) $r['sale_price'] : null,
        'stock'         => (int) $r['stock'],
        'category_slug' => $r['category_slug'],
        'category_name' => $r['category_name'],
    ], $stmt->fetchAll());

    echo json_encode(
        ['query' => $q, 'category' => $category, 'count' => count($products), 'products' => $products],
        JSON_UNESCAPED_UNICODE
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error. Check config/db.php and that schema.sql was imported.']);
}
