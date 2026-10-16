<?php
declare(strict_types=1);

/**
 * Cart details endpoint. Returns JSON.
 *
 * The cart itself lives in the browser (localStorage) and only stores variant ids and quantities.
 * This endpoint gives the current name, price, stock and photo for those variants,
 * so the cart never shows old prices.
 *
 *   GET api/cart.php?ids=3,7,12   (variant ids)
 */

require __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

// Keep only positive integers, no duplicates, at most 50.
$ids = array_values(array_unique(array_filter(
    array_map('intval', explode(',', (string) ($_GET['ids'] ?? ''))),
    static fn(int $n): bool => $n > 0
)));
$ids = array_slice($ids, 0, 50);

if (!$ids) {
    echo json_encode(['items' => []]);
    exit;
}

$marks = implode(',', array_fill(0, count($ids), '?'));
$sql = "SELECT v.id AS variant_id, v.size, v.color, v.stock,
               p.id AS product_id, p.name, p.brand, p.price, p.sale_price,
               (SELECT i.path FROM product_images i WHERE i.product_id = p.id ORDER BY i.sort_order, i.id LIMIT 1) AS image
        FROM product_variants v
        JOIN products p ON p.id = v.product_id
        WHERE v.id IN ($marks)";

try {
    $stmt = db()->prepare($sql);
    $stmt->execute($ids);

    $items = array_map(static function (array $r): array {
        $price = (float) $r['price'];
        $sale  = $r['sale_price'] !== null ? (float) $r['sale_price'] : null;
        return [
            'variant_id' => (int) $r['variant_id'],
            'product_id' => (int) $r['product_id'],
            'name'       => $r['name'],
            'brand'      => $r['brand'],
            'size'       => $r['size'],
            'color'      => $r['color'],
            'stock'      => (int) $r['stock'],
            'price'      => ($sale !== null && $sale < $price) ? $sale : $price,
            'old_price'  => ($sale !== null && $sale < $price) ? $price : null,
            'image'      => $r['image'],
        ];
    }, $stmt->fetchAll());

    echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error. Check config/db.php and that schema.sql was imported.']);
}
