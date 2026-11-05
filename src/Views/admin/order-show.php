<?php
/** @var array $order */
/** @var array $items */
$itemsCount = array_sum(array_map(fn ($item) => (int) $item['quantity'], $items));
?>
<div class="admin-page-head">
    <div>
        <a class="back-link" href="<?= url('/admin/encomendas') ?>"><?= icon('arrow-left', '', 14) ?> <?= e(t('admin.orders')) ?></a>
        <h1><?= e($order['order_number']) ?></h1>
        <p><?= e(t('common.date')) ?> <?= e(format_date($order['created_at'], true)) ?> · <?= (int) $itemsCount ?> <?= e(t('admin.items')) ?></p>
    </div>
    <div class="admin-page-head__actions">
        <span class="status-pill <?= order_status_class($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span>
        <span class="status-pill <?= $order['payment_status'] === 'paid' ? 'status-green' : 'status-amber' ?>"><?= e(t('order.payment_status.' . $order['payment_status'])) ?></span>
    </div>
</div>

<div class="admin-grid admin-grid--order">
    <div class="admin-main-col">
        <section class="admin-card">
            <div class="admin-card__head">
                <div>
                    <span class="eyebrow"><?= icon('package', '', 13) ?> <?= e(t('admin.items')) ?></span>
                    <h2><?= e(t('admin.order_detail')) ?></h2>
                </div>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th><?= e(t('admin.product')) ?></th><th><?= e(t('admin.variant')) ?></th><th><?= e(t('common.price')) ?></th><th><?= e(t('common.quantity')) ?></th><th><?= e(t('common.total')) ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td data-label="<?= e(t('admin.product')) ?>"><strong><?= e($item['product_name']) ?></strong><small class="muted"><?= e($item['sku'] ?? '') ?></small></td>
                                <td data-label="<?= e(t('admin.variant')) ?>"><?= e($item['variant_label']) ?></td>
                                <td data-label="<?= e(t('common.price')) ?>"><?= money((float) $item['unit_price']) ?></td>
                                <td data-label="<?= e(t('common.quantity')) ?>"><?= (int) $item['quantity'] ?></td>
                                <td data-label="<?= e(t('common.total')) ?>"><strong><?= money((float) $item['line_total']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="admin-totals">
                <div><span><?= e(t('cart.subtotal')) ?></span><strong><?= money((float) $order['subtotal']) ?></strong></div>
                <div><span><?= e(t('cart.shipping')) ?></span><strong><?= (float) $order['shipping'] <= 0 ? e(t('cart.free')) : money((float) $order['shipping']) ?></strong></div>
                <div class="admin-totals__grand"><span><?= e(t('cart.total')) ?></span><strong><?= money((float) $order['total']) ?></strong></div>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-card__head">
                <div>
                    <span class="eyebrow"><?= icon('refresh', '', 13) ?> <?= e(t('admin.update_status')) ?></span>
                    <h2><?= e(t('admin.process_status')) ?></h2>
                </div>
            </div>
            <form action="<?= url('/admin/encomendas/' . $order['id'] . '/estado') ?>" method="post" class="admin-form">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <label class="field">
                        <span><?= e(t('admin.process_status')) ?></span>
                        <select name="status">
                            <?php foreach (\App\Models\Order::STATUSES as $status): ?>
                                <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e(order_status_label($status)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span><?= e(t('admin.tracking_code')) ?></span>
                        <input type="text" name="tracking_code" value="<?= e($order['tracking_code'] ?? '') ?>" placeholder="TRV-EXP-000000">
                    </label>
                </div>
                <div class="admin-form__note"><?= icon('info', '', 15) ?> <?= e(t('admin.order_pending')) ?></div>
                <button class="btn btn--primary" type="submit"><?= icon('check', '', 16) ?> <?= e(t('common.save')) ?></button>
            </form>
        </section>
    </div>

    <aside class="admin-side-col">
        <section class="admin-card">
            <div class="admin-card__head"><h2><?= icon('user', '', 16) ?> <?= e(t('admin.customer')) ?></h2></div>
            <div class="admin-detail-list">
                <div><span><?= e(t('common.name')) ?></span><strong><?= e($order['user_name'] ?? $order['shipping_name']) ?></strong></div>
                <div><span><?= e(t('common.email')) ?></span><strong><?= e($order['email']) ?></strong></div>
                <div><span><?= e(t('common.phone')) ?></span><strong><?= e($order['shipping_phone'] ?? '—') ?></strong></div>
                <div><span><?= e(t('admin.customer')) ?></span><strong><?= $order['user_id'] ? '#' . (int) $order['user_id'] : e(t('checkout.guest')) ?></strong></div>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-card__head"><h2><?= icon('truck', '', 16) ?> <?= e(t('account.order_shipping_address')) ?></h2></div>
            <p class="admin-detail-address">
                <strong><?= e($order['shipping_name']) ?></strong><br>
                <?= e($order['shipping_address']) ?><br>
                <?= e($order['shipping_postal_code']) ?> <?= e($order['shipping_city']) ?><br>
                <?= e($order['shipping_country']) ?>
            </p>
        </section>

        <section class="admin-card">
            <div class="admin-card__head"><h2><?= icon('credit-card', '', 16) ?> <?= e(t('admin.payment')) ?></h2></div>
            <div class="admin-detail-list">
                <div><span><?= e(t('checkout.payment_method')) ?></span><strong><?= e(t('order.payment.' . $order['payment_method'])) ?></strong></div>
                <div><span><?= e(t('common.status')) ?></span><strong><?= e(t('order.payment_status.' . $order['payment_status'])) ?></strong></div>
                <div><span><?= e(t('checkout.reference')) ?></span><strong><?= e($order['payment_reference'] ?? '—') ?></strong></div>
            </div>
        </section>

        <?php if (!empty($order['notes'])): ?>
            <section class="admin-card">
                <div class="admin-card__head"><h2><?= icon('file', '', 16) ?> <?= e(t('account.order_notes')) ?></h2></div>
                <p class="admin-detail-address"><?= nl2br(e($order['notes'])) ?></p>
            </section>
        <?php endif; ?>
    </aside>
</div>
