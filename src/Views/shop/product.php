<?php
use App\Core\Auth;

/** @var array $product */
/** @var array $category */
/** @var array $variants */
/** @var array $colorImages */
/** @var array $reviews */
/** @var array $rating */
/** @var array|null $userReview */
/** @var array $related */
/** @var int $totalStock */
/** @var array $breadcrumbs */
$pageScripts = ['product.js'];
$gallery = !empty($product['gallery']) ? $product['gallery'] : [$product['image']];
if (!in_array($product['image'], $gallery, true)) {
    array_unshift($gallery, $product['image']);
}

$colors = [];
$sizes = [];
foreach ($variants as $variant) {
    if (!isset($colors[$variant['color']])) {
        $colors[$variant['color']] = $variant['color_hex'] ?: '#0b6bff';
    }
    $sizes[$variant['size']] = true;
}

$selectedVariant = null;
foreach ($variants as $variant) {
    if ((int) $variant['stock'] > 0) {
        $selectedVariant = $variant;
        break;
    }
}
$selectedVariant = $selectedVariant ?? ($variants[0] ?? null);

$variantData = [];
foreach ($variants as $variant) {
    $variantData[] = [
        'id' => (int) $variant['id'],
        'size' => $variant['size'],
        'color' => $variant['color'],
        'stock' => (int) $variant['stock'],
        'price' => round(product_price($product) + (float) ($variant['price_delta'] ?? 0), 2),
        'price_label' => money(product_price($product) + (float) ($variant['price_delta'] ?? 0)),
        'sku' => $variant['sku'],
        'images' => array_values(array_map(
            static fn (string $image): string => asset('img/' . $image),
            $colorImages[$variant['color']] ?? []
        )),
    ];
}

$reviewRating = (int) old('rating', $userReview['rating'] ?? 5);
$discount = discount_percent($product);
$ratingCount = (int) $rating['count'];
$ratingAvg = (float) $rating['avg'];
$distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($reviews as $reviewRow) {
    $distribution[(int) $reviewRow['rating']] = ($distribution[(int) $reviewRow['rating']] ?? 0) + 1;
}
?>
<section class="product-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                <?php if ($index > 0): ?><?= icon('chevron-right', '', 13) ?><?php endif; ?>
                <?php if (!empty($crumb['url'])): ?>
                    <a href="<?= url($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
                <?php else: ?>
                    <span><?= e($crumb['label']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="product-detail" data-product-page data-variants="<?= e(json_encode($variantData, JSON_UNESCAPED_UNICODE)) ?>" data-currency="EUR">
            <div class="product-gallery" data-gallery>
                <div class="product-gallery__main">
                    <span class="product-gallery__badges">
                        <?php if ($discount > 0): ?><span class="badge badge--sale">−<?= $discount ?>%</span><?php endif; ?>
                        <?php if (!empty($product['is_new'])): ?><span class="badge badge--new"><?= e(t('common.new')) ?></span><?php endif; ?>
                        <?php if (!empty($product['featured'])): ?><span class="badge badge--featured"><?= icon('sparkles', '', 12) ?> <?= e(t('home.featured_eyebrow')) ?></span><?php endif; ?>
                    </span>
                    <button class="product-gallery__zoom" type="button" data-gallery-zoom aria-label="<?= e(t('product.gallery')) ?>">
                        <?= icon('search', '', 18) ?>
                    </button>
                    <img id="product-main-image" src="<?= asset('img/' . $gallery[0]) ?>" alt="<?= e(localized($product, 'name')) ?>" width="900" height="900">
                </div>
                <div class="product-gallery__thumbs">
                    <?php foreach ($gallery as $index => $image): ?>
                        <button type="button" class="product-gallery__thumb <?= $index === 0 ? 'is-active' : '' ?>" data-gallery-thumb data-src="<?= asset('img/' . $image) ?>" aria-label="<?= e(t('product.gallery')) ?> <?= $index + 1 ?>">
                            <img src="<?= asset('img/' . $image) ?>" alt="" loading="lazy" width="160" height="160">
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="product-gallery__perks">
                    <span><?= icon('refresh', '', 17) ?> <?= e(t('home.trust_3_title')) ?></span>
                    <span><?= icon('shield-check', '', 17) ?> <?= e(t('home.trust_4_title')) ?></span>
                </div>
            </div>

            <div class="product-info">
                <div class="product-info__meta">
                    <span class="product-info__brand"><?= e($product['brand']) ?></span>
                    <a href="<?= url('/categoria/' . $category['slug']) ?>" class="product-info__category"><?= e(localized($category, 'name')) ?></a>
                </div>
                <h1><?= e(localized($product, 'name')) ?></h1>
                <div class="product-info__rating">
                    <?= stars($ratingAvg) ?>
                    <a href="#reviews"><?= $ratingCount > 0 ? e(t('product.reviews_count', ['count' => $ratingCount])) : e(t('product.be_first_review')) ?></a>
                    <span class="product-info__sku"><?= e(t('product.sku')) ?>: <strong data-variant-sku><?= e($selectedVariant['sku'] ?? '—') ?></strong></span>
                </div>
                <p class="product-info__short"><?= e(localized($product, 'short')) ?></p>

                <div class="product-price">
                    <strong data-product-price><?= money(product_price($product) + (float) ($selectedVariant['price_delta'] ?? 0)) ?></strong>
                    <?php if ($discount > 0): ?>
                        <del><?= money($product['base_price']) ?></del>
                        <span class="product-price__save"><?= e(t('cart.vat_included')) ?></span>
                    <?php endif; ?>
                </div>
                <p class="product-price__tax"><?= e(t('product.tax_note')) ?></p>

                <div class="product-stock" data-stock-display>
                    <?php if ($totalStock <= 0): ?>
                        <span class="stock-dot is-out"></span><?= e(t('product.stock_out')) ?>
                    <?php elseif (($selectedVariant['stock'] ?? 0) <= 5): ?>
                        <span class="stock-dot is-low"></span><?= e(t('product.stock_low', ['count' => (int) ($selectedVariant['stock'] ?? 0)])) ?>
                    <?php else: ?>
                        <span class="stock-dot is-in"></span><?= e(t('product.stock_available', ['count' => (int) ($selectedVariant['stock'] ?? 0)])) ?>
                    <?php endif; ?>
                </div>

                <?php if ($variants): ?>
                    <form action="<?= url('/carrinho/adicionar') ?>" method="post" class="product-buy" data-cart-form data-product-form>
                        <?= csrf_field() ?>
                        <input type="hidden" name="variant_id" value="<?= (int) ($selectedVariant['id'] ?? 0) ?>" data-variant-input>

                        <?php if (count($colors) > 1): ?>
                            <div class="variant-group">
                                <div class="variant-group__head">
                                    <span><?= e(t('product.select_color')) ?>: <strong data-color-label><?= e($selectedVariant['color'] ?? array_key_first($colors)) ?></strong></span>
                                </div>
                                <div class="variant-colors">
                                    <?php foreach ($colors as $colorName => $hex): ?>
                                        <button type="button" class="variant-color <?= ($selectedVariant['color'] ?? '') === $colorName ? 'is-active' : '' ?>" data-color="<?= e($colorName) ?>" title="<?= e($colorName) ?>">
                                            <span style="background: <?= e($hex) ?>"></span>
                                            <em><?= e($colorName) ?></em>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (count($sizes) > 1): ?>
                            <div class="variant-group">
                                <div class="variant-group__head">
                                    <span><?= e(t('common.size')) ?>: <strong data-size-label><?= e($selectedVariant['size'] ?? array_key_first($sizes)) ?></strong></span>
                                    <button type="button" class="link-button" data-size-guide><?= icon('info', '', 14) ?> <?= e(t('product.size_guide')) ?></button>
                                </div>
                                <div class="variant-sizes">
                                    <?php foreach (array_keys($sizes) as $sizeName): ?>
                                        <button type="button" class="variant-size <?= ($selectedVariant['size'] ?? '') === $sizeName ? 'is-active' : '' ?>" data-size="<?= e($sizeName) ?>"><?= e($sizeName) ?></button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <input type="hidden" value="<?= e($selectedVariant['size'] ?? 'Único') ?>" data-static-size>
                        <?php endif; ?>

                        <div class="product-buy__actions">
                            <div class="qty-control" data-qty>
                                <button type="button" data-qty-minus aria-label="<?= e(t('common.decrease')) ?>"><?= icon('minus', '', 15) ?></button>
                                <input type="number" name="quantity" value="1" min="1" max="10" inputmode="numeric" aria-label="<?= e(t('common.quantity')) ?>">
                                <button type="button" data-qty-plus aria-label="<?= e(t('common.increase')) ?>"><?= icon('plus', '', 15) ?></button>
                            </div>
                            <button type="submit" class="btn btn--primary btn--lg product-buy__submit" <?= $totalStock <= 0 ? 'disabled' : '' ?> data-add-button>
                                <?= icon('cart', '', 19) ?>
                                <span data-add-label><?= $totalStock <= 0 ? e(t('common.out_of_stock')) : e(t('product.add_to_cart')) ?></span>
                            </button>
                        </div>
                        <p class="product-buy__hint"><?= icon('shield-check', '', 14) ?> <?= e(t('checkout.payment_simulated')) ?></p>
                    </form>
                <?php else: ?>
                    <div class="alert alert--warning"><?= e(t('product.not_found')) ?></div>
                <?php endif; ?>

                <div class="product-info__perks">
                    <div><?= icon('truck', '', 19) ?><span><strong><?= e(t('home.trust_1_title')) ?></strong><small><?= e(t('product.shipping_info')) ?></small></span></div>
                    <div><?= icon('refresh', '', 19) ?><span><strong><?= e(t('home.trust_3_title')) ?></strong><small><?= e(t('home.trust_3_text')) ?></small></span></div>
                    <div><?= icon('credit-card', '', 19) ?><span><strong><?= e(t('common.secure_payment')) ?></strong><small><?= e(t('product.payment_info')) ?></small></span></div>
                </div>

                <div class="product-info__help">
                    <?= icon('headset', '', 20) ?>
                    <span><strong><?= e(t('nav.help')) ?></strong><small><?= e(t('home.trust_2_text')) ?></small></span>
                    <a class="btn btn--ghost btn--sm" href="<?= url('/suporte') ?>"><?= e(t('nav.support')) ?></a>
                </div>
            </div>
        </div>

        <div class="product-content">
            <div class="product-content__main">
                <section class="content-card" id="description">
                    <div class="content-card__head">
                        <span class="eyebrow"><?= icon('file', '', 13) ?> <?= e(t('product.details')) ?></span>
                        <h2><?= e(t('product.description')) ?></h2>
                    </div>
                    <div class="prose">
                        <?= nl2br(e(localized($product, 'description'))) ?>
                    </div>
                    <?php if (!empty($product['specs'])): ?>
                        <div class="specs" id="specs">
                            <h3><?= e(t('product.specs')) ?></h3>
                            <dl class="specs__grid">
                                <?php foreach ($product['specs'] as $spec): ?>
                                    <div><dt><?= e(localized($spec, 'label')) ?></dt><dd><?= e($spec['value'] ?? '—') ?></dd></div>
                                <?php endforeach; ?>
                            </dl>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="content-card" id="reviews">
                    <div class="content-card__head">
                        <span class="eyebrow"><?= icon('heart', '', 13) ?> <?= e(t('product.reviews')) ?></span>
                        <h2><?= e(t('product.reviews')) ?> <small>(<?= $ratingCount ?>)</small></h2>
                    </div>

                    <div class="reviews-summary">
                        <div class="reviews-summary__score">
                            <strong><?= number_format($ratingAvg, 1, locale() === 'en' ? '.' : ',', '') ?></strong>
                            <?= stars($ratingAvg) ?>
                            <small><?= e(t('product.reviews_count', ['count' => $ratingCount])) ?></small>
                        </div>
                        <div class="reviews-summary__bars">
                            <?php for ($star = 5; $star >= 1; $star--): ?>
                                <?php $count = $distribution[$star] ?? 0; $width = $ratingCount > 0 ? ($count / $ratingCount) * 100 : 0; ?>
                                <div class="review-bar">
                                    <span><?= $star ?> <?= icon('star', '', 11) ?></span>
                                    <span class="review-bar__track"><span style="width: <?= $width ?>%"></span></span>
                                    <small><?= $count ?></small>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <?php if (Auth::check()): ?>
                            <a class="btn btn--ghost btn--sm" href="#review-form"><?= icon('plus', '', 15) ?> <?= e($userReview ? t('product.review_update') : t('product.write_review')) ?></a>
                        <?php else: ?>
                            <a class="btn btn--ghost btn--sm btn--wrap" href="<?= url('/login') ?>"><?= icon('user', '', 15) ?> <?= e(t('product.review_login')) ?></a>
                        <?php endif; ?>
                    </div>

                    <?php if ($reviews): ?>
                        <div class="review-list">
                            <?php foreach ($reviews as $review): ?>
                                <article class="review">
                                    <div class="review__head">
                                        <span class="avatar avatar--sm"><?= e(initials($review['user_name'])) ?></span>
                                        <div>
                                            <strong><?= e($review['user_name']) ?></strong>
                                            <small><?= e(format_date($review['created_at'])) ?> · <?= e(t('common.verified')) ?></small>
                                        </div>
                                        <div class="review__stars"><?= stars((float) $review['rating']) ?></div>
                                    </div>
                                    <?php if (!empty($review['title'])): ?><h4><?= e($review['title']) ?></h4><?php endif; ?>
                                    <p><?= nl2br(e($review['body'])) ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state empty-state--sm">
                            <?= icon('message', '', 28) ?>
                            <p><?= e(t('product.no_reviews')) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="review-form-wrap" id="review-form">
                        <?php if (Auth::check()): ?>
                            <h3><?= e($userReview ? t('product.review_update') : t('product.write_review')) ?></h3>
                            <?php if ($userReview): ?><p class="review-form__note"><?= e(t('product.review_exists')) ?></p><?php endif; ?>
                            <form action="<?= url('/avaliacao') ?>" method="post" class="review-form" data-rating-widget>
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <div class="rating-input">
                                    <span><?= e(t('product.your_rating')) ?></span>
                                    <div class="rating-input__stars">
                                        <?php for ($star = 5; $star >= 1; $star--): ?>
                                            <label>
                                                <input type="radio" name="rating" value="<?= $star ?>" <?= $reviewRating === $star ? 'checked' : '' ?>>
                                                <?= icon('star', '', 24) ?>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <label class="field field--full">
                                        <span><?= e(t('product.review_title')) ?> <em>(<?= e(t('common.optional')) ?>)</em></span>
                                        <input type="text" name="title" maxlength="150" value="<?= e(old('title', $userReview['title'] ?? '')) ?>" placeholder="<?= e(t('product.review_title_placeholder')) ?>">
                                    </label>
                                    <label class="field field--full">
                                        <span><?= e(t('product.review_body')) ?></span>
                                        <textarea name="body" rows="5" required maxlength="2000" placeholder="<?= e(t('product.review_placeholder')) ?>"><?= e(old('body', $userReview['body'] ?? '')) ?></textarea>
                                    </label>
                                </div>
                                <button class="btn btn--primary" type="submit"><?= icon('message', '', 16) ?> <?= e($userReview ? t('product.review_update') : t('product.review_submit')) ?></button>
                            </form>
                        <?php else: ?>
                            <div class="review-login-box">
                                <?= icon('user', '', 24) ?>
                                <div><strong><?= e(t('product.review_login')) ?></strong><p><?= e(t('product.review_only_users')) ?></p></div>
                                <a class="btn btn--primary btn--sm" href="<?= url('/login') ?>"><?= e(t('nav.login')) ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <aside class="product-content__aside">
                <div class="side-card side-card--spooky">
                    <span class="eyebrow eyebrow--light"><?= icon('ghost', '', 13) ?> <?= e(t('home.promo_eyebrow')) ?></span>
                    <h3><?= e(t('promo.spooky')) ?></h3>
                    <p><?= e(t('promo.subtitle')) ?></p>
                    <a class="btn btn--accent btn--sm" href="<?= url('/promocoes') ?>"><?= e(t('promo.cta')) ?> <?= icon('arrow-right', '', 15) ?></a>
                </div>
                <div class="side-card">
                    <h3><?= e(t('product.size_guide')) ?></h3>
                    <p><?= e(t('support.faq_3_a')) ?></p>
                    <a class="link-arrow" href="<?= url('/suporte') ?>"><?= e(t('nav.support')) ?> <?= icon('arrow-right', '', 15) ?></a>
                </div>
                <div class="side-card side-card--flat">
                    <span><?= icon('check-circle', '', 18) ?> <?= e(t('common.free_shipping', ['amount' => money(60)])) ?></span>
                    <span><?= icon('check-circle', '', 18) ?> <?= e(t('common.free_returns')) ?></span>
                    <span><?= icon('check-circle', '', 18) ?> <?= e(t('common.expert_support')) ?></span>
                </div>
            </aside>
        </div>

        <?php if ($related): ?>
            <section class="section section--related">
                <div class="section-head">
                    <div>
                        <span class="eyebrow"><?= icon('sparkles', '', 13) ?> <?= e(t('home.featured_eyebrow')) ?></span>
                        <h2><?= e(t('product.related')) ?></h2>
                    </div>
                    <a class="link-arrow" href="<?= url('/categoria/' . $category['slug']) ?>"><?= e(t('nav.all')) ?> <?= icon('arrow-right', '', 16) ?></a>
                </div>
                <div class="product-grid product-grid--4">
                    <?php foreach ($related as $product): ?>
                        <?php require BASE_PATH . '/src/Views/partials/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>

<div class="modal" data-size-modal hidden>
    <div class="modal__backdrop" data-modal-close></div>
    <div class="modal__panel">
        <button class="icon-btn modal__close" type="button" data-modal-close aria-label="<?= e(t('nav.close')) ?>"><?= icon('close', '', 20) ?></button>
        <span class="eyebrow"><?= icon('info', '', 13) ?> <?= e(t('product.size_guide')) ?></span>
        <h3><?= e(localized($product, 'name')) ?></h3>
        <p><?= e(t('support.faq_3_a')) ?></p>
        <table class="size-table">
            <thead><tr><th><?= e(t('common.size')) ?></th><th><?= e(t('common.fit')) ?></th></tr></thead>
            <tbody>
                <tr><td>S</td><td>1,65–1,72 m</td></tr>
                <tr><td>M</td><td>1,72–1,80 m</td></tr>
                <tr><td>L</td><td>1,80–1,88 m</td></tr>
                <tr><td>XL</td><td>1,88 m +</td></tr>
            </tbody>
        </table>
    </div>
</div>
