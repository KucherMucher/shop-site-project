<?php
/** @var array $orders */
/** @var array $result */
/** @var array $counts */
/** @var array $filters */
$tabs = [
    '' => ['label' => t('common.all'), 'count' => array_sum($counts)],
    'placed' => ['label' => t('order.status.placed'), 'count' => $counts['placed']],
    'shipped' => ['label' => t('order.status.shipped'), 'count' => $counts['shipped']],
    'received' => ['label' => t('order.status.received'), 'count' => $counts['received']],
    'lost' => ['label' => t('order.status.lost'), 'count' => $counts['lost']],
    'not_received' => ['label' => t('order.status.not_received'), 'count' => $counts['not_received']],
];
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('package', '', 13) ?> <?= e(t('admin.orders')) ?></span>
        <h1><?= e(t('admin.orders')) ?></h1>
        <p><?= e(t('admin.orders_subtitle')) ?></p>
    </div>
    <span class="admin-chip"><?= icon('package', '', 14) ?> <?= (int) $result['total'] ?> <?= e(t('common.results')) ?></span>
</div>

<div class="admin-tabs">
    <?php foreach ($tabs as $slug => $tab): ?>
        <a class="admin-tab <?= $filters['status'] === $slug ? 'is-active' : '' ?>" href="<?= url('/admin/encomendas' . ($slug ? '?status=' . $slug : '')) ?>">
            <?= e($tab['label']) ?> <span><?= (int) $tab['count'] ?></span>
        </a>
    <?php endforeach; ?>
</div>

<form class="admin-filters" method="get" action="<?= url('/admin/encomendas') ?>">
    <input type="hidden" name="status" value="<?= e($filters['status']) ?>">
    <label class="admin-search">
        <?= icon('search', '', 16) ?>
        <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e(t('admin.search_orders_placeholder')) ?>">
    </label>
    <button class="btn btn--primary btn--sm" type="submit"><?= icon('search', '', 15) ?> <?= e(t('admin.search')) ?></button>
    <a class="btn btn--ghost btn--sm" href="<?= url('/admin/encomendas') ?>"><?= e(t('admin.reset')) ?></a>
</form>

<div class="admin-card admin-card--flush">
    <?php if ($orders): ?>
        <div class="table-wrap">
            <table class="admin-table admin-table--orders">
                <thead>
                    <tr>
                        <th><?= e(t('admin.order')) ?></th>
                        <th><?= e(t('admin.customer')) ?></th>
                        <th><?= e(t('admin.items')) ?></th>
                        <th><?= e(t('admin.payment')) ?></th>
                        <th><?= e(t('common.status')) ?></th>
                        <th><?= e(t('common.total')) ?></th>
                        <th><?= e(t('common.date')) ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="<?= e(t('admin.order')) ?>">
                                <a class="table-link" href="<?= url('/admin/encomendas/' . $order['id']) ?>"><?= e($order['order_number']) ?></a>
                                <?php if (!empty($order['tracking_code'])): ?><small class="muted"><?= icon('truck', '', 11) ?> <?= e($order['tracking_code']) ?></small><?php endif; ?>
                            </td>
                            <td data-label="<?= e(t('admin.customer')) ?>">
                                <strong><?= e($order['user_name'] ?? $order['shipping_name']) ?></strong>
                                <small class="muted"><?= e($order['email']) ?></small>
                            </td>
                            <td data-label="<?= e(t('admin.items')) ?>"><?= (int) ($order['items_count'] ?? 0) ?></td>
                            <td data-label="<?= e(t('admin.payment')) ?>">
                                <span><?= e(t('order.payment.' . $order['payment_method'])) ?></span>
                                <small class="status-pill <?= $order['payment_status'] === 'paid' ? 'status-green' : 'status-amber' ?>"><?= e(t('order.payment_status.' . $order['payment_status'])) ?></small>
                            </td>
                            <td data-label="<?= e(t('common.status')) ?>"><span class="status-pill <?= order_status_class($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                            <td data-label="<?= e(t('common.total')) ?>"><strong><?= money((float) $order['total']) ?></strong></td>
                            <td data-label="<?= e(t('common.date')) ?>"><?= e(format_date($order['created_at'])) ?><small class="muted"><?= e(date('H:i', strtotime($order['created_at']))) ?></small></td>
                            <td class="admin-table__actions">
                                <a class="btn btn--ghost btn--sm" href="<?= url('/admin/encomendas/' . $order['id']) ?>"><?= e(t('admin.view')) ?> <?= icon('chevron-right', '', 13) ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($result['pages'] > 1): ?>
            <div class="admin-pagination">
                <span><?= e(t('shop.showing', ['from' => $result['page'] * $result['per_page'] - $result['per_page'] + 1, 'to' => min($result['total'], $result['page'] * $result['per_page']), 'total' => $result['total']])) ?></span>
                <div class="admin-pagination__links">
                    <?php if ($result['page'] > 1): ?>
                        <a class="btn btn--ghost btn--sm" href="<?= url('/admin/encomendas') . query_string(['page' => $result['page'] - 1]) ?>"><?= icon('chevron-left', '', 14) ?> <?= e(t('common.previous')) ?></a>
                    <?php endif; ?>
                    <span class="admin-chip"><?= (int) $result['page'] ?> / <?= (int) $result['pages'] ?></span>
                    <?php if ($result['page'] < $result['pages']): ?>
                        <a class="btn btn--ghost btn--sm" href="<?= url('/admin/encomendas') . query_string(['page' => $result['page'] + 1]) ?>"><?= e(t('common.next')) ?> <?= icon('chevron-right', '', 14) ?></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="admin-empty admin-empty--lg"><?= icon('package', '', 34) ?><p><?= e(t('admin.no_orders')) ?></p></div>
    <?php endif; ?>
</div>
