<?php
/** @var array $order */
/** @var array $items */
$isPending = ($order['payment_status'] ?? '') === 'pending';
$entity = '21312';
$reference = str_replace('MB-REF ', '', (string) ($order['payment_reference'] ?? ''));
?>
<section class="section section--success">
    <div class="container">
        <div class="success-hero" data-reveal>
            <span class="success-hero__icon"><?= icon('check-circle', '', 38) ?></span>
            <span class="eyebrow"><?= icon('sparkles', '', 13) ?> <?= e(t('checkout.order_number')) ?> <?= e($order['order_number']) ?></span>
            <h1><?= e(t('checkout.success_title')) ?></h1>
            <p><?= e(t('checkout.success_text', ['email' => $order['email']])) ?></p>
            <div class="success-hero__actions">
                <a class="btn btn--primary" href="<?= url('/loja') ?>"><?= icon('grid', '', 16) ?> <?= e(t('cart.continue_shopping')) ?></a>
                <?php if (!empty($order['user_id'])): ?>
                    <a class="btn btn--ghost" href="<?= url('/conta/encomendas/' . $order['id']) ?>"><?= icon('package', '', 16) ?> <?= e(t('checkout.track_order')) ?></a>
                <?php endif; ?>
                <a class="btn btn--ghost" href="<?= url('/') ?>"><?= icon('home', '', 16) ?> <?= e(t('checkout.back_home')) ?></a>
            </div>
        </div>

        <?php if ($isPending && $order['payment_method'] === 'multibanco'): ?>
            <div class="payment-reference" data-reveal>
                <div class="payment-reference__head">
                    <?= icon('barcode', '', 22) ?>
                    <div>
                        <strong><?= e(t('checkout.multibanco_reference')) ?></strong>
                        <p><?= e(t('checkout.multibanco_after')) ?></p>
                    </div>
                </div>
                <dl class="payment-reference__grid">
                    <div><dt><?= e(t('checkout.entity')) ?></dt><dd><?= e($entity) ?></dd></div>
                    <div><dt><?= e(t('checkout.reference')) ?></dt><dd><?= e($reference ?: '—') ?></dd></div>
                    <div><dt><?= e(t('checkout.amount')) ?></dt><dd><?= money((float) $order['total']) ?></dd></div>
                </dl>
            </div>
        <?php endif; ?>

        <div class="success-layout">
            <div class="success-main">
                <section class="content-card">
                    <div class="content-card__head">
                        <span class="eyebrow"><?= icon('package', '', 13) ?> <?= e(t('account.order_items')) ?></span>
                        <h2><?= e(t('cart.summary')) ?></h2>
                    </div>
                    <div class="success-items">
                        <?php foreach ($items as $item): ?>
                            <div class="success-item">
                                <span class="success-item__media">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?= asset('img/' . $item['image']) ?>" alt="" width="72" height="72" loading="lazy">
                                    <?php else: ?>
                                        <span class="success-item__placeholder"><?= icon('box', '', 24) ?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="success-item__info">
                                    <strong><?= e($item['product_name']) ?></strong>
                                    <small><?= e($item['variant_label']) ?> · <?= (int) $item['quantity'] ?> × <?= money((float) $item['unit_price']) ?></small>
                                </span>
                                <strong><?= money((float) $item['line_total']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-card__lines success-totals">
                        <div><span><?= e(t('cart.subtotal')) ?></span><strong><?= money((float) $order['subtotal']) ?></strong></div>
                        <div><span><?= e(t('cart.shipping')) ?></span><strong class="<?= (float) $order['shipping'] <= 0 ? 'is-free' : '' ?>"><?= (float) $order['shipping'] <= 0 ? e(t('cart.free')) : money((float) $order['shipping']) ?></strong></div>
                        <div><span><?= e(t('cart.total')) ?></span><strong><?= money((float) $order['total']) ?></strong></div>
                    </div>
                </section>

                <section class="content-card">
                    <div class="content-card__head">
                        <span class="eyebrow"><?= icon('truck', '', 13) ?> <?= e(t('checkout.shipping_details')) ?></span>
                        <h2><?= e(t('account.order_shipping_address')) ?></h2>
                    </div>
                    <div class="success-details">
                        <div>
                            <strong><?= e($order['shipping_name']) ?></strong>
                            <p><?= e($order['shipping_address']) ?><br><?= e($order['shipping_postal_code']) ?> <?= e($order['shipping_city']) ?><br><?= e($order['shipping_country']) ?></p>
                            <?php if (!empty($order['shipping_phone'])): ?><p class="muted"><?= icon('phone', '', 14) ?> <?= e($order['shipping_phone']) ?></p><?php endif; ?>
                        </div>
                        <div>
                            <strong><?= e(t('account.order_payment')) ?></strong>
                            <p><?= e(t('order.payment.' . $order['payment_method'])) ?><br><span class="muted"><?= e($order['payment_reference'] ?? '') ?></span></p>
                            <span class="status-pill <?= $order['payment_status'] === 'paid' ? 'status-green' : 'status-amber' ?>"><?= e(t('order.payment_status.' . $order['payment_status'])) ?></span>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="success-aside">
                <div class="side-card side-card--success">
                    <span class="eyebrow"><?= icon('mail', '', 13) ?> <?= e(t('common.email')) ?></span>
                    <h3><?= e(t('checkout.success_email')) ?></h3>
                    <p><?= e($order['email']) ?></p>
                    <span class="status-pill status-blue"><?= icon('clock', '', 14) ?> <?= e(t('checkout.delivery_estimate')) ?>: <?= e(t('checkout.delivery_estimate_value')) ?></span>
                </div>
                <div class="side-card">
                    <h3><?= e(t('pages.help_title')) ?></h3>
                    <p><?= e(t('pages.help_text')) ?></p>
                    <a class="btn btn--ghost btn--sm" href="<?= url('/suporte') ?>"><?= icon('headset', '', 15) ?> <?= e(t('pages.need_help')) ?></a>
                </div>
                <div class="side-card side-card--flat side-card--summary">
                    <div class="summary-meta"><span><?= e(t('checkout.order_number')) ?></span><strong><?= e($order['order_number']) ?></strong></div>
                    <div class="summary-meta"><span><?= e(t('common.date')) ?></span><strong><?= e(format_date($order['created_at'], true)) ?></strong></div>
                    <div class="summary-meta"><span><?= e(t('account.order_status')) ?></span><strong><?= e(order_status_label($order['status'])) ?></strong></div>
                </div>
            </aside>
        </div>
    </div>
</section>
