<?php
/** @var array $products */
/** @var array $result */
/** @var array $filters */
/** @var array $categories */
/** @var array $brands */
/** @var array $sizes */
/** @var array|null $category */
$heading = $heading ?? t('shop.title');
$subtitle = $subtitle ?? t('shop.subtitle');
$action = $category ? '/categoria/' . $category['slug'] : '/loja';
$from = $result['total'] > 0 ? (($result['page'] - 1) * $result['per_page']) + 1 : 0;
$to = min($result['page'] * $result['per_page'], $result['total']);
$activeFilters = array_filter([
    !empty($filters['min_price']) || !empty($filters['max_price']),
    !empty($filters['brand']),
    !empty($filters['size']),
    !empty($filters['in_stock']),
    !empty($filters['promo']),
]);
?>
<section class="page-head page-head--shop">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>"><?= e(t('product.breadcrumb_home')) ?></a>
            <?= icon('chevron-right', '', 13) ?>
            <?php if ($category): ?>
                <a href="<?= url('/loja') ?>"><?= e(t('nav.shop')) ?></a>
                <?= icon('chevron-right', '', 13) ?>
                <span><?= e($heading) ?></span>
            <?php else: ?>
                <span><?= e(t('nav.shop')) ?></span>
            <?php endif; ?>
        </nav>
        <div class="page-head__row">
            <div>
                <span class="eyebrow"><?= icon('grid', '', 13) ?> <?= e(t('nav.shop')) ?></span>
                <h1><?= e($heading) ?></h1>
                <p><?= e($subtitle) ?></p>
            </div>
            <div class="page-head__art" aria-hidden="true">
                <?= icon('target', '', 76) ?>
            </div>
        </div>
    </div>
</section>

<section class="section section--catalog">
    <div class="container">
        <div class="catalog-layout">
            <div class="filters-backdrop" data-filters-backdrop></div>
            <?php
            $showCategories = $category === null;
            require BASE_PATH . '/src/Views/shop/_filters.php';
            ?>

            <div class="catalog-results">
                <div class="catalog-toolbar">
                    <button class="btn btn--ghost btn--sm catalog-toolbar__filter" type="button" data-filters-open>
                        <?= icon('filter', '', 16) ?> <?= e(t('shop.mobile_filters')) ?>
                        <?php if ($activeFilters): ?><span class="catalog-toolbar__dot"></span><?php endif; ?>
                    </button>
                    <p class="catalog-toolbar__count">
                        <?= e(t('shop.showing', ['from' => $from, 'to' => $to, 'total' => $result['total']])) ?>
                    </p>
                    <label class="select-inline">
                        <span><?= e(t('shop.sort')) ?></span>
                        <select name="sort" form="catalog-filters" data-auto-submit>
                            <option value="featured" <?= ($filters['sort'] ?? '') === 'featured' ? 'selected' : '' ?>><?= e(t('shop.sort_featured')) ?></option>
                            <option value="price_asc" <?= ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>><?= e(t('shop.sort_price_asc')) ?></option>
                            <option value="price_desc" <?= ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>><?= e(t('shop.sort_price_desc')) ?></option>
                            <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>><?= e(t('shop.sort_newest')) ?></option>
                            <option value="rating" <?= ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' ?>><?= e(t('shop.sort_rating')) ?></option>
                            <option value="name" <?= ($filters['sort'] ?? '') === 'name' ? 'selected' : '' ?>><?= e(t('shop.sort_name')) ?></option>
                        </select>
                    </label>
                </div>

                <?php if ($products): ?>
                    <div class="product-grid product-grid--3">
                        <?php foreach ($products as $product): ?>
                            <?php require BASE_PATH . '/src/Views/partials/product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($result['pages'] > 1): ?>
                        <nav class="pagination" aria-label="Pagination">
                            <a class="pagination__arrow <?= $result['page'] <= 1 ? 'is-disabled' : '' ?>" href="<?= $result['page'] <= 1 ? '#' : e(url($action) . query_string(['page' => $result['page'] - 1])) ?>" aria-label="<?= e(t('common.previous')) ?>"><?= icon('chevron-left', '', 17) ?></a>
                            <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                                <?php if ($p === 1 || $p === $result['pages'] || abs($p - $result['page']) <= 1): ?>
                                    <a class="pagination__item <?= $p === $result['page'] ? 'is-active' : '' ?>" href="<?= e(url($action) . query_string(['page' => $p])) ?>"><?= $p ?></a>
                                <?php elseif ($p === 2 || $p === $result['pages'] - 1): ?>
                                    <span class="pagination__dots">…</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <a class="pagination__arrow <?= $result['page'] >= $result['pages'] ? 'is-disabled' : '' ?>" href="<?= $result['page'] >= $result['pages'] ? '#' : e(url($action) . query_string(['page' => $result['page'] + 1])) ?>" aria-label="<?= e(t('common.next')) ?>"><?= icon('chevron-right', '', 17) ?></a>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state empty-state--catalog">
                        <span class="empty-state__icon"><?= icon('search', '', 34) ?></span>
                        <h2><?= e(t('shop.no_products')) ?></h2>
                        <p><?= e(t('shop.no_products_hint')) ?></p>
                        <a class="btn btn--primary" href="<?= e(url($action)) ?>"><?= icon('refresh', '', 16) ?> <?= e(t('shop.reset_filters')) ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
