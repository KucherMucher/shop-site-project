<?php
/** @var array $orders */
?>
<div class="section-head section-head--account">
    <div>
        <span class="eyebrow"><?= icon('package', '', 13) ?> <?= e(t('account.nav_orders')) ?></span>
        <h2><?= e(t('account.orders_title')) ?></h2>
        <p><?= e(t('account.orders_subtitle')) ?></p>
    </div>
    <a class="btn btn--ghost btn--sm" href="<?= url('/loja') ?>"><?= icon('grid', '', 16) ?> <?= e(t('cart.continue_shopping')) ?></a>
</div>

<?php if (!$orders): ?>
    <div class="empty-state empty-state--panel">
        <span class="empty-state__icon"><?= icon('package', '', 38) ?></span>
        <h3><?= e(t('account.orders_empty')) ?></h3>
        <p><?= e(t('account.orders_subtitle')) ?></p>
        <a class="btn btn--primary" href="<?= url('/loja') ?>"><?= e(t('cart.continue_shopping')) ?> <?= icon('arrow-right', '', 16) ?></a>
    </div>
<?php else: ?>
    <div class="account-panel">
        <div class="table-wrap">
            <table class="data-table data-table--account">
                <thead>
                    <tr>
                        <th><?= e(t('checkout.order_number')) ?></th>
                        <th><?= e(t('common.date')) ?></th>
                        <th><?= e(t('admin.items')) ?></th>
                        <th><?= e(t('account.order_status')) ?></th>
                        <th><?= e(t('common.total')) ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="<?= e(t('checkout.order_number')) ?>">
                                <a class="table-link" href="<?= url('/conta/encomendas/' . $order['id']) ?>"><?= e($order['order_number']) ?></a>
                            </td>
                            <td data-label="<?= e(t('common.date')) ?>"><?= e(format_date($order['created_at'], true)) ?></td>
                            <td data-label="<?= e(t('admin.items')) ?>"><?= (int) ($order['items_count'] ?? 0) ?></td>
                            <td data-label="<?= e(t('account.order_status')) ?>"><span class="status-pill <?= order_status_class($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                            <td data-label="<?= e(t('common.total')) ?>"><strong><?= money((float) $order['total']) ?></strong></td>
                            <td class="data-table__action">
                                <a class="btn btn--ghost btn--sm" href="<?= url('/conta/encomendas/' . $order['id']) ?>"><?= e(t('common.view')) ?> <?= icon('chevron-right', '', 14) ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
