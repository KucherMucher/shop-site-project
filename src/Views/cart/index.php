<?php
/** @var array $items */
/** @var float $subtotal */
/** @var float $shipping */
/** @var float $total */
/** @var float $freeFrom */
$remaining = max(0, $freeFrom - $subtotal);
$progress = $freeFrom > 0 ? min(100, ($subtotal / $freeFrom) * 100) : 100;
$count = array_sum(array_column($items, 'quantity'));
?>
<section class="page-head page-head--compact">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>"><?= e(t('product.breadcrumb_home')) ?></a>
            <?= icon('chevron-right', '', 13) ?>
            <span><?= e(t('cart.title')) ?></span>
        </nav>
        <div class="page-head__row">
            <div>
                <span class="eyebrow"><?= icon('cart', '', 13) ?> <?= e(t('cart.summary')) ?></span>
                <h1><?= e(t('cart.title')) ?></h1>
                <p><?= e($count) ?> <?= e($count === 1 ? t('cart.item') : t('cart.items')) ?></p>
            </div>
        </div>
    </div>
</section>

<section class="section section--cart">
    <div class="container">
        <?php if (!$items): ?>
            <div class="empty-state empty-state--cart">
                <span class="empty-state__icon"><?= icon('cart', '', 42) ?></span>
                <h2><?= e(t('cart.empty')) ?></h2>
                <p><?= e(t('cart.empty_text')) ?></p>
                <a class="btn btn--primary btn--lg" href="<?= url('/loja') ?>"><?= icon('grid', '', 17) ?> <?= e(t('cart.continue_shopping')) ?></a>
                <div class="empty-state__perks">
                    <span><?= icon('truck', '', 17) ?> <?= e(t('home.trust_1_title')) ?></span>
                    <span><?= icon('refresh', '', 17) ?> <?= e(t('home.trust_3_title')) ?></span>
                    <span><?= icon('shield-check', '', 17) ?> <?= e(t('home.trust_4_title')) ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-main">
                    <div class="free-shipping">
                        <div class="free-shipping__head">
                            <?= icon($remaining > 0 ? 'truck' : 'check-circle', '', 19) ?>
                            <p>
                                <?php if ($remaining > 0): ?>
                                    <?= e(t('cart.free_shipping_hint', ['amount' => money($remaining)])) ?>
                                <?php else: ?>
                                    <?= e(t('cart.free_shipping_reached')) ?>
                                <?php endif; ?>
                            </p>
                            <strong><?= money($subtotal) ?> / <?= money($freeFrom) ?></strong>
                        </div>
                        <div class="free-shipping__bar"><span style="width: <?= $progress ?>%"></span></div>
                    </div>

                    <div class="cart-list">
                        <div class="cart-list__head">
                            <span><?= e(t('cart.item')) ?></span>
                            <span><?= e(t('cart.unit_price')) ?></span>
                            <span><?= e(t('common.quantity')) ?></span>
                            <span><?= e(t('common.total')) ?></span>
                        </div>

                        <?php foreach ($items as $item): ?>
                            <?php $variant = $item['variant']; ?>
                            <article class="cart-item">
                                <div class="cart-item__product">
                                    <a class="cart-item__media" href="<?= url('/produto/' . $variant['slug']) ?>">
                                        <img src="<?= asset('img/' . $variant['image']) ?>" alt="<?= e(localized($variant, 'name')) ?>" loading="lazy" width="160" height="160">
                                    </a>
                                    <div class="cart-item__info">
                                        <span class="cart-item__brand"><?= e($variant['brand'] ?? 'TRIVEX') ?></span>
                                        <h3><a href="<?= url('/produto/' . $variant['slug']) ?>"><?= e(localized($variant, 'name')) ?></a></h3>
                                        <p><?= e($variant['size']) ?> · <?= e($variant['color']) ?></p>
                                        <small><?= e(t('product.sku')) ?>: <?= e($variant['sku']) ?></small>
                                        <?php if ($item['quantity'] > $item['stock']): ?>
                                            <span class="cart-item__warning"><?= icon('alert', '', 13) ?> <?= e(t('cart.insufficient_stock', ['name' => localized($variant, 'name'), 'stock' => $item['stock']])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="cart-item__unit" data-label="<?= e(t('cart.unit_price')) ?>">
                                    <?php if (discount_percent($variant) > 0): ?><del><?= money($variant['base_price']) ?></del><?php endif; ?>
                                    <strong><?= money($item['unit_price']) ?></strong>
                                </div>

                                <div class="cart-item__qty" data-label="<?= e(t('common.quantity')) ?>">
                                    <form action="<?= url('/carrinho/atualizar') ?>" method="post" class="cart-qty-form" data-auto-submit-form>
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="variant_id" value="<?= (int) $item['variant_id'] ?>">
                                        <div class="qty-control qty-control--sm">
                                            <button type="button" data-qty-minus aria-label="<?= e(t('common.decrease')) ?>"><?= icon('minus', '', 13) ?></button>
                                            <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="0" max="<?= max(1, min(10, $item['stock'])) ?>" inputmode="numeric" aria-label="<?= e(t('common.quantity')) ?>">
                                            <button type="button" data-qty-plus aria-label="<?= e(t('common.increase')) ?>"><?= icon('plus', '', 13) ?></button>
                                        </div>
                                        <button type="submit" class="link-button"><?= e(t('cart.update')) ?></button>
                                    </form>
                                </div>

                                <div class="cart-item__total" data-label="<?= e(t('common.total')) ?>">
                                    <strong><?= money($item['line_total']) ?></strong>
                                    <button type="submit" form="remove-cart-<?= (int) $item['variant_id'] ?>" class="cart-item__remove" aria-label="<?= e(t('cart.remove')) ?>"><?= icon('trash', '', 16) ?> <?= e(t('cart.remove')) ?></button>
                                </div>
                            </article>
                            <form id="remove-cart-<?= (int) $item['variant_id'] ?>" action="<?= url('/carrinho/remover') ?>" method="post" class="hidden-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="variant_id" value="<?= (int) $item['variant_id'] ?>">
                            </form>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-actions">
                        <a class="btn btn--ghost" href="<?= url('/loja') ?>"><?= icon('arrow-left', '', 16) ?> <?= e(t('cart.continue_shopping')) ?></a>
                        <form action="<?= url('/carrinho/limpar') ?>" method="post" data-confirm="<?= e(t('cart.clear_confirm')) ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="link-button link-button--danger"><?= icon('trash', '', 15) ?> <?= e(t('cart.clear_cart')) ?></button>
                        </form>
                    </div>

                    <div class="cart-assurance">
                        <div><?= icon('shield-check', '', 21) ?><span><strong><?= e(t('cart.secure')) ?></strong><small><?= e(t('checkout.payment_simulated')) ?></small></span></div>
                        <div><?= icon('truck', '', 21) ?><span><strong><?= e(t('home.trust_1_title')) ?></strong><small><?= e(t('home.trust_1_text')) ?></small></span></div>
                        <div><?= icon('refresh', '', 21) ?><span><strong><?= e(t('home.trust_3_title')) ?></strong><small><?= e(t('home.trust_3_text')) ?></small></span></div>
                    </div>
                </div>

                <aside class="cart-summary">
                    <div class="summary-card">
                        <div class="summary-card__head">
                            <h2><?= icon('barcode', '', 18) ?> <?= e(t('cart.summary')) ?></h2>
                            <span><?= $count ?> <?= e($count === 1 ? t('cart.item') : t('cart.items')) ?></span>
                        </div>
                        <div class="summary-card__lines">
                            <div><span><?= e(t('cart.subtotal')) ?></span><strong><?= money($subtotal) ?></strong></div>
                            <div><span><?= e(t('cart.shipping')) ?></span><strong class="<?= $shipping <= 0 ? 'is-free' : '' ?>"><?= $shipping <= 0 ? e(t('cart.free')) : money($shipping) ?></strong></div>
                        </div>
                        <div class="summary-card__total">
                            <span><?= e(t('cart.total')) ?> <small><?= e(t('cart.vat_included')) ?></small></span>
                            <strong><?= money($total) ?></strong>
                        </div>
                        <a class="btn btn--primary btn--lg btn--block" href="<?= url('/checkout') ?>"><?= icon('lock', '', 17) ?> <?= e(t('cart.checkout')) ?></a>
                        <p class="summary-card__note"><?= icon('shield-check', '', 14) ?> <?= e(t('checkout.payment_simulated')) ?></p>
                        <div class="summary-card__payments">
                            <span class="pay-badge">VISA</span>
                            <span class="pay-badge">MC</span>
                            <span class="pay-badge">MB WAY</span>
                            <span class="pay-badge">MB</span>
                        </div>
                    </div>
                    <div class="summary-help">
                        <span><?= icon('headset', '', 19) ?></span>
                        <div><strong><?= e(t('nav.help')) ?></strong><p><?= e(t('home.trust_2_text')) ?></p><a href="<?= url('/suporte') ?>"><?= e(t('nav.support')) ?> <?= icon('arrow-right', '', 13) ?></a></div>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>
