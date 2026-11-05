<?php
/** @var array $filters */
/** @var array $categories */
/** @var array $brands */
/** @var array $sizes */
$action = $action ?? url('/loja');
$showCategories = $showCategories ?? false;
$selectedBrands = (array) ($filters['brand'] ?? []);
$maxPrice = 3000;
?>
<form class="filters" id="catalog-filters" action="<?= e($action) ?>" method="get" data-filters-form>
    <?php if (!empty($filters['q'])): ?>
        <input type="hidden" name="q" value="<?= e($filters['q']) ?>">
    <?php endif; ?>
    <?php if (!empty($filters['category']) && !$showCategories): ?>
        <input type="hidden" name="category" value="<?= e($filters['category']) ?>">
    <?php endif; ?>

    <div class="filters__head">
        <h2><?= icon('filter', '', 17) ?> <?= e(t('shop.filters_title')) ?></h2>
        <a href="<?= e($action) ?>" class="filters__reset"><?= e(t('common.clear')) ?></a>
        <button type="button" class="icon-btn filters__close" data-filters-close aria-label="<?= e(t('nav.close')) ?>"><?= icon('close', '', 20) ?></button>
    </div>

    <?php if ($showCategories && !empty($categories)): ?>
        <div class="filter-group">
            <h3><?= e(t('product.category')) ?></h3>
            <div class="filter-categories">
                <?php foreach ($categories as $cat): ?>
                    <a class="filter-category <?= ($filters['category'] ?? '') === $cat['slug'] ? 'is-active' : '' ?>" href="<?= url('/categoria/' . $cat['slug']) ?>">
                        <span><?= e(localized($cat, 'name')) ?></span>
                        <small><?= (int) $cat['products_count'] ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="filter-group">
        <h3><?= e(t('shop.price')) ?></h3>
        <div class="price-range">
            <label>
                <span><?= e(t('shop.price_min')) ?></span>
                <input type="number" name="min_price" min="0" max="<?= $maxPrice ?>" step="1" value="<?= e($filters['min_price'] ?? '') ?>" placeholder="0">
            </label>
            <span class="price-range__dash">—</span>
            <label>
                <span><?= e(t('shop.price_max')) ?></span>
                <input type="number" name="max_price" min="0" max="<?= $maxPrice ?>" step="1" value="<?= e($filters['max_price'] ?? '') ?>" placeholder="<?= $maxPrice ?>">
            </label>
        </div>
    </div>

    <?php if (!empty($brands)): ?>
        <div class="filter-group">
            <h3><?= e(t('shop.brand')) ?></h3>
            <div class="filter-options">
                <?php foreach ($brands as $i => $brandRow): ?>
                    <?php $brandName = $brandRow['brand']; ?>
                    <label class="check-row">
                        <input type="checkbox" name="brand[]" value="<?= e($brandName) ?>" <?= in_array($brandName, $selectedBrands, true) ? 'checked' : '' ?>>
                        <span class="check-row__box"><?= icon('check', '', 12) ?></span>
                        <span><?= e($brandName) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($sizes)): ?>
        <div class="filter-group">
            <h3><?= e(t('common.size')) ?></h3>
            <div class="size-filter">
                <?php foreach ($sizes as $sizeRow): ?>
                    <?php $size = $sizeRow['size']; ?>
                    <label class="size-chip <?= ($filters['size'] ?? '') === $size ? 'is-active' : '' ?>">
                        <input type="radio" name="size" value="<?= e($size) ?>" <?= ($filters['size'] ?? '') === $size ? 'checked' : '' ?>>
                        <span><?= e($size) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="filter-group">
        <h3><?= e(t('shop.availability')) ?></h3>
        <div class="filter-options">
            <label class="check-row">
                <input type="checkbox" name="in_stock" value="1" <?= !empty($filters['in_stock']) ? 'checked' : '' ?>>
                <span class="check-row__box"><?= icon('check', '', 12) ?></span>
                <span><?= e(t('shop.only_in_stock')) ?></span>
            </label>
            <label class="check-row">
                <input type="checkbox" name="promo" value="1" <?= !empty($filters['promo']) ? 'checked' : '' ?>>
                <span class="check-row__box"><?= icon('check', '', 12) ?></span>
                <span><?= e(t('shop.promo_only')) ?></span>
            </label>
        </div>
    </div>

    <button class="btn btn--primary btn--block filters__apply" type="submit"><?= icon('filter', '', 16) ?> <?= e(t('common.apply')) ?></button>
</form>
