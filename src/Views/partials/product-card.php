<?php
$discount = discount_percent($product);
$isPromo = $discount > 0;
$stock = (int) ($product['total_stock'] ?? 0);
$rating = round((float) ($product['avg_rating'] ?? 0), 1);
$reviewsCount = (int) ($product['reviews_count'] ?? 0);
$halloween = $halloween ?? false;
?>
<article class="product-card <?= $halloween ? 'product-card--spooky' : '' ?>" data-reveal>
    <a class="product-card__media" href="<?= url('/produto/' . $product['slug']) ?>" aria-label="<?= e(localized($product, 'name')) ?>">
        <img src="<?= asset('img/' . $product['image']) ?>" alt="<?= e(localized($product, 'name')) ?>" loading="lazy" width="640" height="640">
        <span class="product-card__badges">
            <?php if ($halloween): ?>
                <span class="badge badge--halloween"><?= icon('ghost', '', 12) ?> <?= e(t('promo.badge')) ?></span>
            <?php endif; ?>
            <?php if ($isPromo): ?>
                <span class="badge badge--sale">−<?= $discount ?>%</span>
            <?php endif; ?>
            <?php if (!empty($product['is_new'])): ?>
                <span class="badge badge--new"><?= e(t('common.new')) ?></span>
            <?php endif; ?>
        </span>
        <span class="product-card__action"><?= icon('arrow-up-right', '', 18) ?></span>
    </a>
    <div class="product-card__body">
        <div class="product-card__meta">
            <span class="product-card__brand"><?= e($product['brand']) ?></span>
            <span class="product-card__dot"></span>
            <span><?= e(localized($product, 'category_name')) ?></span>
        </div>
        <h3 class="product-card__title"><a href="<?= url('/produto/' . $product['slug']) ?>"><?= e(localized($product, 'name')) ?></a></h3>
        <div class="product-card__rating">
            <?= stars($rating) ?>
            <span><?= $rating > 0 ? number_format($rating, 1, locale() === 'en' ? '.' : ',', '') : '—' ?></span>
            <small>(<?= $reviewsCount ?>)</small>
        </div>
        <div class="product-card__footer">
            <div class="product-card__price">
                <strong><?= money(product_price($product)) ?></strong>
                <?php if ($isPromo): ?>
                    <del><?= money($product['base_price']) ?></del>
                <?php endif; ?>
            </div>
            <span class="stock-dot <?= $stock <= 0 ? 'is-out' : ($stock <= 5 ? 'is-low' : 'is-in') ?>">
                <?= e($stock <= 0 ? t('common.out_of_stock') : ($stock <= 5 ? t('common.low_stock') : t('common.in_stock'))) ?>
            </span>
        </div>
    </div>
</article>
