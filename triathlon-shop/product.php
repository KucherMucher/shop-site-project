<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$id       = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product  = null;
$images   = [];
$variants = [];

if ($id) {
    try {
        $stmt = db()->prepare(
            'SELECT p.id, p.name, p.brand, p.description, p.price, p.sale_price,
                    c.slug AS category_slug, c.name AS category_name
             FROM products p JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        $product = $stmt->fetch() ?: null;

        if ($product) {
            $stmt = db()->prepare('SELECT path, alt FROM product_images WHERE product_id = ? ORDER BY sort_order, id');
            $stmt->execute([$id]);
            $images = $stmt->fetchAll();

            $stmt = db()->prepare('SELECT id, size, color, color_hex, stock FROM product_variants WHERE product_id = ? ORDER BY id');
            $stmt->execute([$id]);
            $variants = array_map(static fn(array $v): array => [
                'id'    => (int) $v['id'],
                'size'  => $v['size'],
                'color' => $v['color'],
                'hex'   => $v['color_hex'],
                'stock' => (int) $v['stock'],
            ], $stmt->fetchAll());
        }
    } catch (PDOException $ex) {
        $product = null;
    }
}

$page = 'product';

// Product not found: show a clear message instead of an empty page.
if (!$product) {
    http_response_code(404);
    $title = 'Product not found | Transition';
    require __DIR__ . '/partials/header.php';
    ?>
  <main id="main" class="wrap">
    <div class="state state--page">
      <h1>We could not find that product</h1>
      <p>It may have been removed, or the link is wrong. Search for it or browse a category.</p>
      <a class="btn" href="index.php">Back to the shop</a>
    </div>
  </main>
    <?php
    require __DIR__ . '/partials/footer.php';
    exit;
}

$title          = $product['name'] . ' | Transition';
$description    = $product['description'];
$activeCategory = $product['category_slug'];
require __DIR__ . '/partials/header.php';

$price  = (float) $product['price'];
$sale   = $product['sale_price'] !== null ? (float) $product['sale_price'] : null;
$onSale = $sale !== null && $sale < $price;

$clientData = [
    'product'  => [
        'id'            => (int) $product['id'],
        'name'          => $product['name'],
        'category_slug' => $product['category_slug'],
    ],
    'variants' => $variants,
];
?>

  <main id="main" class="wrap product">
    <nav class="crumbs" aria-label="Breadcrumb">
      <a href="index.php">Shop</a>
      <span aria-hidden="true">/</span>
      <a href="index.php?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a>
      <span aria-hidden="true">/</span>
      <span aria-current="page"><?= e($product['name']) ?></span>
    </nav>

    <div class="product__layout">
      <div class="gallery">
        <div class="gallery__main">
          <?php if ($images): ?>
            <img id="gallery-main" src="<?= e($images[0]['path']) ?>" alt="<?= e($images[0]['alt']) ?>" width="800" height="600">
          <?php else: ?>
            <div class="gallery__none">No photo yet</div>
          <?php endif; ?>
        </div>
        <?php if (count($images) > 1): ?>
          <ul class="gallery__thumbs">
            <?php foreach ($images as $i => $img): ?>
              <li>
                <button type="button" class="thumb" data-src="<?= e($img['path']) ?>" data-alt="<?= e($img['alt']) ?>"
                        aria-label="Show photo <?= $i + 1 ?> of <?= count($images) ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
                  <img src="<?= e($img['path']) ?>" alt="" width="160" height="120" loading="lazy">
                </button>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div class="product__info">
        <p class="product__brand"><?= e($product['brand']) ?></p>
        <h1 class="product__name"><?= e($product['name']) ?></h1>

        <p class="product__price">
          <?php if ($onSale): ?>
            <span class="price price--sale price--big"><?= money($sale) ?></span>
            <span class="price__old"><?= money($price) ?></span>
            <span class="badge">-<?= (int) round((1 - $sale / $price) * 100) ?>%</span>
          <?php else: ?>
            <span class="price price--big"><?= money($price) ?></span>
          <?php endif; ?>
        </p>

        <noscript><p class="msg msg--error">JavaScript is needed to choose options and use the cart.</p></noscript>

        <div class="opt" id="opt-color" hidden>
          <p class="opt__label">Color: <strong id="color-name">choose one</strong></p>
          <div class="swatches" role="group" aria-label="Color"></div>
        </div>
        <div class="opt" id="opt-size" hidden>
          <p class="opt__label">Size</p>
          <div class="sizes" role="group" aria-label="Size"></div>
        </div>

        <p id="stock-line" class="stock stock--line"></p>

        <div class="buy">
          <div class="qty">
            <button type="button" id="qty-minus" aria-label="Decrease quantity">&minus;</button>
            <input id="qty" type="number" min="1" value="1" inputmode="numeric" aria-label="Quantity">
            <button type="button" id="qty-plus" aria-label="Increase quantity">+</button>
          </div>
          <button type="button" id="add-btn" class="btn btn--big">Add to cart</button>
        </div>
        <p id="buy-msg" class="msg" role="status"></p>

        <div class="product__desc">
          <h2>About this product</h2>
          <p><?= e($product['description']) ?></p>
          <dl class="specs">
            <div><dt>Brand</dt><dd><?= e($product['brand']) ?></dd></div>
            <div><dt>Category</dt><dd><?= e($product['category_name']) ?></dd></div>
            <div><dt>Reference</dt><dd><?= str_pad((string) $product['id'], 4, '0', STR_PAD_LEFT) ?></dd></div>
          </dl>
        </div>
      </div>
    </div>

    <section class="related" id="related" hidden>
      <div class="section-head"><h2>More in <?= e($product['category_name']) ?></h2></div>
      <div id="related-grid" class="grid"></div>
    </section>
  </main>

  <script type="application/json" id="product-data"><?= json_encode($clientData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>

<?php
$scripts = ['assets/js/product.js'];
require __DIR__ . '/partials/footer.php';
