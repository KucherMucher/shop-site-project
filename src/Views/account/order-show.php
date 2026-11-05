<?php
/** @var array $order */
/** @var array $items */
$steps = ['placed', 'shipped', 'received'];
$currentIndex = array_search($order['status'], $steps, true);
$isLost = in_array($order['status'], ['lost', 'not_received'], true);
?>
<div class="section-head section-head--account">
    <div>
        <a class="back-link" href="<?= url('/conta/encomendas') ?>"><?= icon('arrow-left', '', 15) ?> <?= e(t('account.order_back')) ?></a>
        <h2><?= e(t('account.order_details')) ?></h2>
        <p><?= e($order['order_number']) ?> · <?= e(format_date($order['created_at'], true)) ?></p>
    </div>
    <span class="status-pill <?= order_status_class($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span>
</div>

<div class="order-layout">
    <div class="order-main">
        <div class="order-progress <?= $isLost ? 'is-lost' : '' ?>">
            <?php if ($isLost): ?>
                <div class="alert alert--warning"><?= icon('alert', '', 18) ?> <?= e(t('account.order_lost_note')) ?></div>
            <?php else: ?>
                <ol class="order-steps">
                    <?php foreach ($steps as $index => $step): ?>
                        <li class="<?= $index <= (int) $currentIndex ? 'is-done' : '' ?> <?= $index === (int) $currentIndex ? 'is-current' : '' ?>">
                            <span class="order-steps__dot"><?= $index <= (int) $currentIndex ? icon('check', '', 14) : $index + 1 ?></span>
                            <span>
                                <strong><?= e(t('order.status.' . $step)) ?></strong>
                                <small><?= e(t('order.timeline_' . $step)) ?></small>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
            <?php if (!empty($order['tracking_code'])): ?>
                <div class="order-tracking">
                    <?= icon('truck', '', 18) ?>
                    <span><small><?= e(t('account.order_tracking')) ?></small><strong><?= e($order['tracking_code']) ?></strong></span>
                </div>
            <?php endif; ?>
        </div>

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
                            <?php if (!empty($item['product_slug'])): ?>
                                <strong><a href="<?= url('/produto/' . $item['product_slug']) ?>"><?= e($item['product_name']) ?></a></strong>
                            <?php else: ?>
                                <strong><?= e($item['product_name']) ?></strong>
                            <?php endif; ?>
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
                <span class="eyebrow"><?= icon('clock', '', 13) ?> <?= e(t('account.order_timeline')) ?></span>
                <h2><?= e(t('account.order_timeline')) ?></h2>
            </div>
            <ul class="timeline">
                <li class="is-done">
                    <span class="timeline__dot"><?= icon('check', '', 12) ?></span>
                    <div><strong><?= e(t('order.status.placed')) ?></strong><small><?= e(format_date($order['created_at'], true)) ?> · <?= e(t('order.timeline_placed')) ?></small></div>
                </li>
                <?php if (in_array($order['status'], ['shipped', 'received'], true)): ?>
                    <li class="is-done">
                        <span class="timeline__dot"><?= icon('check', '', 12) ?></span>
                        <div><strong><?= e(t('order.status.shipped')) ?></strong><small><?= e(t('order.timeline_shipped')) ?></small></div>
                    </li>
                <?php endif; ?>
                <?php if ($order['status'] === 'received'): ?>
                    <li class="is-done">
                        <span class="timeline__dot"><?= icon('check', '', 12) ?></span>
                        <div><strong><?= e(t('order.status.received')) ?></strong><small><?= e(t('order.timeline_received')) ?></small></div>
                    </li>
                <?php elseif (!$isLost): ?>
                    <li>
                        <span class="timeline__dot"></span>
                        <div><strong><?= e(t('order.status.received')) ?></strong><small><?= e(t('order.timeline_pending')) ?></small></div>
                    </li>
                <?php endif; ?>
            </ul>
        </section>
    </div>

    <aside class="order-aside">
        <div class="side-card">
            <h3><?= icon('map-pin', '', 17) ?> <?= e(t('account.order_shipping_address')) ?></h3>
            <p>
                <strong><?= e($order['shipping_name']) ?></strong><br>
                <?= e($order['shipping_address']) ?><br>
                <?= e($order['shipping_postal_code']) ?> <?= e($order['shipping_city']) ?><br>
                <?= e($order['shipping_country']) ?>
            </p>
            <?php if (!empty($order['shipping_phone'])): ?><p class="muted"><?= icon('phone', '', 14) ?> <?= e($order['shipping_phone']) ?></p><?php endif; ?>
        </div>

        <div class="side-card">
            <h3><?= icon('credit-card', '', 17) ?> <?= e(t('account.order_payment')) ?></h3>
            <p><?= e(t('order.payment.' . $order['payment_method'])) ?><br><span class="muted"><?= e($order['payment_reference'] ?? '') ?></span></p>
            <span class="status-pill <?= $order['payment_status'] === 'paid' ? 'status-green' : 'status-amber' ?>"><?= e(t('order.payment_status.' . $order['payment_status'])) ?></span>
        </div>

        <?php if (!empty($order['notes'])): ?>
            <div class="side-card">
                <h3><?= icon('file', '', 17) ?> <?= e(t('account.order_notes')) ?></h3>
                <p><?= nl2br(e($order['notes'])) ?></p>
            </div>
        <?php endif; ?>

        <div class="side-card side-card--flat side-card--summary">
            <div class="summary-meta"><span><?= e(t('checkout.order_number')) ?></span><strong><?= e($order['order_number']) ?></strong></div>
            <div class="summary-meta"><span><?= e(t('common.total')) ?></span><strong><?= money((float) $order['total']) ?></strong></div>
            <div class="summary-meta"><span><?= e(t('order.tracking_code')) ?></span><strong><?= e($order['tracking_code'] ?: t('order.no_tracking')) ?></strong></div>
        </div>

        <a class="btn btn--ghost btn--block" href="<?= url('/suporte') ?>"><?= icon('headset', '', 16) ?> <?= e(t('account.nav_support')) ?></a>
    </aside>
</div>
