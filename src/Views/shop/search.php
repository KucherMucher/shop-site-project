<?php
/** @var array $products */
/** @var array $result */
/** @var array $filters */
/** @var array $categories */
/** @var array $brands */
/** @var array $sizes */
$q = $q ?? '';
$from = $result['total'] > 0 ? (($result['page'] - 1) * $result['per_page']) + 1 : 0;
$to = min($result['page'] * $result['per_page'], $result['total']);
?>
<section class="page-head page-head--shop">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>"><?= e(t('product.breadcrumb_home')) ?></a>
            <?= icon('chevron-right', '', 13) ?>
            <span><?= e(t('shop.search_title')) ?></span>
        </nav>
        <div class="page-head__row">
            <div>
                <span class="eyebrow"><?= icon('search', '', 13) ?> <?= e(t('shop.search_title')) ?></span>
                <?php if ($q !== ''): ?>
                    <h1><?= e(t('shop.search_for', ['q' => $q])) ?></h1>
                    <p><?= e(t('shop.showing', ['from' => $from, 'to' => $to, 'total' => $result['total']])) ?></p>
                <?php else: ?>
                    <h1><?= e(t('shop.search_title')) ?></h1>
                    <p><?= e(t('shop.search_placeholder')) ?></p>
                <?php endif; ?>
            </div>
            <form class="search-again" action="<?= url('/pesquisa') ?>" method="get" role="search">
                <input type="search" name="q" value="<?= e($q) ?>" placeholder="<?= e(t('nav.search_placeholder')) ?>" aria-label="<?= e(t('nav.search')) ?>">
                <button class="btn btn--primary" type="submit"><?= icon('search', '', 17) ?></button>
            </form>
        </div>
    </div>
</section>

<section class="section section--catalog">
    <div class="container">
        <div class="catalog-layout">
            <div class="filters-backdrop" data-filters-backdrop></div>
            <?php
            $action = url('/pesquisa');
            $showCategories = true;
            require BASE_PATH . '/src/Views/shop/_filters.php';
            ?>

            <div class="catalog-results">
                <div class="catalog-toolbar">
                    <button class="btn btn--ghost btn--sm catalog-toolbar__filter" type="button" data-filters-open>
                        <?= icon('filter', '', 16) ?> <?= e(t('shop.mobile_filters')) ?>
                    </button>
                    <p class="catalog-toolbar__count">
                        <?php if ($q !== ''): ?>
                            <?= e(t('shop.showing', ['from' => $from, 'to' => $to, 'total' => $result['total']])) ?>
                        <?php else: ?>
                            <?= e(t('shop.results_count', ['count' => $result['total']])) ?>
                        <?php endif; ?>
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
                            <a class="pagination__arrow <?= $result['page'] <= 1 ? 'is-disabled' : '' ?>" href="<?= $result['page'] <= 1 ? '#' : e(url('/pesquisa') . query_string(['page' => $result['page'] - 1])) ?>" aria-label="<?= e(t('common.previous')) ?>"><?= icon('chevron-left', '', 17) ?></a>
                            <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                                <?php if ($p === 1 || $p === $result['pages'] || abs($p - $result['page']) <= 1): ?>
                                    <a class="pagination__item <?= $p === $result['page'] ? 'is-active' : '' ?>" href="<?= e(url('/pesquisa') . query_string(['page' => $p])) ?>"><?= $p ?></a>
                                <?php elseif ($p === 2 || $p === $result['pages'] - 1): ?>
                                    <span class="pagination__dots">…</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <a class="pagination__arrow <?= $result['page'] >= $result['pages'] ? 'is-disabled' : '' ?>" href="<?= $result['page'] >= $result['pages'] ? '#' : e(url('/pesquisa') . query_string(['page' => $result['page'] + 1])) ?>" aria-label="<?= e(t('common.next')) ?>"><?= icon('chevron-right', '', 17) ?></a>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state empty-state--catalog">
                        <span class="empty-state__icon"><?= icon('search', '', 34) ?></span>
                        <h2><?= e(t('shop.no_products')) ?></h2>
                        <p><?= e(t('shop.no_products_hint')) ?></p>
                        <a class="btn btn--primary" href="<?= url('/loja') ?>"><?= icon('grid', '', 16) ?> <?= e(t('nav.shop')) ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
