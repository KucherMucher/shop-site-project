<?php
/** @var float $revenue */
/** @var int $ordersTotal */
/** @var int $ordersMonth */
/** @var int $customers */
/** @var int $customersNew */
/** @var int $productsActive */
/** @var int $reviewsCount */
/** @var float $reviewsAvg */
/** @var array $recentOrders */
/** @var array $recentReviews */
/** @var array $topProducts */
/** @var array $stockAlerts */
/** @var array $monthly */
/** @var array $chatCounts */
/** @var array $pendingList */
$maxRevenue = max(1, ...array_map(fn ($m) => (float) $m['revenue'], $monthly));
$adminName = $currentUser['name'] ?? 'TRIVEX';
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('dashboard', '', 13) ?> <?= e(t('admin.overview')) ?></span>
        <h1><?= e(t('admin.welcome', ['name' => explode(' ', $adminName)[0]])) ?></h1>
        <p><?= e(t('admin.dashboard_subtitle')) ?></p>
    </div>
    <div class="admin-page-head__actions">
        <span class="status-pill status-green"><span class="pulse-dot"></span> <?= e(t('common.active')) ?></span>
        <a class="btn btn--primary btn--sm" href="<?= url('/admin/encomendas') ?>"><?= icon('package', '', 15) ?> <?= e(t('admin.orders')) ?></a>
    </div>
</div>

<div class="admin-kpis">
    <article class="admin-kpi admin-kpi--hero">
        <span class="admin-kpi__label"><?= e(t('admin.stat_revenue')) ?></span>
        <strong><?= money($revenue) ?></strong>
        <span class="admin-kpi__meta"><?= icon('arrow-up-right', '', 13) ?> <?= e(t('admin.all_time')) ?></span>
        <span class="admin-kpi__icon"><?= icon('credit-card', '', 22) ?></span>
    </article>
    <article class="admin-kpi">
        <span class="admin-kpi__label"><?= e(t('admin.stat_orders')) ?></span>
        <strong><?= (int) $ordersTotal ?></strong>
        <span class="admin-kpi__meta"><?= e(t('admin.last_30_days')) ?>: +<?= (int) $ordersMonth ?></span>
        <span class="admin-kpi__icon admin-kpi__icon--blue"><?= icon('package', '', 20) ?></span>
    </article>
    <article class="admin-kpi">
        <span class="admin-kpi__label"><?= e(t('admin.stat_customers')) ?></span>
        <strong><?= (int) $customers ?></strong>
        <span class="admin-kpi__meta">+<?= (int) $customersNew ?> <?= e(t('admin.last_30_days')) ?></span>
        <span class="admin-kpi__icon admin-kpi__icon--green"><?= icon('users', '', 20) ?></span>
    </article>
    <article class="admin-kpi">
        <span class="admin-kpi__label"><?= e(t('admin.stat_products')) ?></span>
        <strong><?= (int) $productsActive ?></strong>
        <span class="admin-kpi__meta"><?= e(t('admin.stat_reviews')) ?>: <?= (int) $reviewsCount ?></span>
        <span class="admin-kpi__icon admin-kpi__icon--purple"><?= icon('box', '', 20) ?></span>
    </article>
    <article class="admin-kpi">
        <span class="admin-kpi__label"><?= e(t('admin.stat_chats')) ?></span>
        <strong><?= (int) $chatCounts['open'] ?></strong>
        <span class="admin-kpi__meta"><?= (int) $chatCounts['in_progress'] ?> <?= e(t('chat.status.in_progress')) ?></span>
        <span class="admin-kpi__icon admin-kpi__icon--amber"><?= icon('chat', '', 20) ?></span>
    </article>
    <article class="admin-kpi">
        <span class="admin-kpi__label"><?= e(t('admin.stat_pending')) ?></span>
        <strong><?= count($pendingList) ?></strong>
        <span class="admin-kpi__meta"><?= e(t('admin.approvals')) ?></span>
        <span class="admin-kpi__icon admin-kpi__icon--red"><?= icon('shield-check', '', 20) ?></span>
    </article>
</div>

<div class="admin-grid admin-grid--main">
    <section class="admin-card admin-card--chart">
        <div class="admin-card__head">
            <div>
                <span class="eyebrow"><?= icon('chart', '', 13) ?> <?= e(t('admin.stat_revenue')) ?></span>
                <h2><?= e(t('admin.sales_chart')) ?></h2>
            </div>
            <span class="admin-chip"><?= icon('calendar', '', 13) ?> 6M</span>
        </div>
        <div class="admin-chart" data-admin-chart>
            <?php foreach ($monthly as $index => $month): ?>
                <?php $height = max(4, (int) round(((float) $month['revenue'] / $maxRevenue) * 100)); ?>
                <div class="admin-chart__col" style="--bar-h: <?= $height ?>%">
                    <span class="admin-chart__value"><?= money((float) $month['revenue']) ?></span>
                    <span class="admin-chart__bar" style="animation-delay: <?= $index * 70 ?>ms"></span>
                    <span class="admin-chart__label"><?= e($month['label']) ?></span>
                    <small><?= (int) $month['orders'] ?> enc.</small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <span class="eyebrow"><?= icon('shield-check', '', 13) ?> <?= e(t('admin.approvals')) ?></span>
                <h2><?= e(t('admin.pending_approvals')) ?></h2>
            </div>
            <a class="link-arrow" href="<?= url('/admin/aprovacoes') ?>"><?= e(t('admin.view_all')) ?> <?= icon('arrow-right', '', 14) ?></a>
        </div>
        <?php if ($pendingList): ?>
            <div class="admin-pending-list">
                <?php foreach ($pendingList as $item): ?>
                    <div class="admin-pending">
                        <span class="admin-pending__icon"><?= icon('clock', '', 16) ?></span>
                        <div>
                            <strong><?= e($item['summary']) ?></strong>
                            <small><?= e($item['requester_name']) ?> · <?= e(format_date($item['created_at'], true)) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="admin-empty"><?= icon('check-circle', '', 26) ?><p><?= e(t('admin.no_pending')) ?></p></div>
        <?php endif; ?>
    </section>
</div>

<div class="admin-grid admin-grid--two">
    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <span class="eyebrow"><?= icon('package', '', 13) ?> <?= e(t('admin.orders')) ?></span>
                <h2><?= e(t('admin.recent_orders')) ?></h2>
            </div>
            <a class="link-arrow" href="<?= url('/admin/encomendas') ?>"><?= e(t('admin.view_all')) ?> <?= icon('arrow-right', '', 14) ?></a>
        </div>
        <?php if ($recentOrders): ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th><?= e(t('admin.order')) ?></th><th><?= e(t('admin.customer')) ?></th><th><?= e(t('common.status')) ?></th><th><?= e(t('common.total')) ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><a class="table-link" href="<?= url('/admin/encomendas/' . $order['id']) ?>"><?= e($order['order_number']) ?><small><?= e(format_date($order['created_at'], true)) ?></small></a></td>
                                <td><?= e($order['user_name'] ?? $order['shipping_name']) ?><small><?= e($order['email']) ?></small></td>
                                <td><span class="status-pill <?= order_status_class($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                                <td><strong><?= money((float) $order['total']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-empty"><?= icon('package', '', 26) ?><p><?= e(t('admin.no_orders')) ?></p></div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <span class="eyebrow"><?= icon('alert', '', 13) ?> <?= e(t('admin.stock')) ?></span>
                <h2><?= e(t('admin.stock_alerts')) ?></h2>
            </div>
            <a class="link-arrow" href="<?= url('/admin/stock?state=low') ?>"><?= e(t('admin.view_all')) ?> <?= icon('arrow-right', '', 14) ?></a>
        </div>
        <?php if ($stockAlerts): ?>
            <div class="admin-alert-list">
                <?php foreach ($stockAlerts as $variant): ?>
                    <a class="admin-alert" href="<?= url('/admin/stock?q=' . urlencode($variant['sku'])) ?>">
                        <img src="<?= asset('img/' . $variant['image']) ?>" alt="" width="42" height="42" loading="lazy">
                        <span><strong><?= e(localized($variant, 'name')) ?></strong><small><?= e($variant['size']) ?> · <?= e($variant['sku']) ?></small></span>
                        <span class="status-pill <?= (int) $variant['stock'] === 0 ? 'status-red' : 'status-amber' ?>"><?= (int) $variant['stock'] ?> un.</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="admin-empty"><?= icon('check-circle', '', 26) ?><p><?= e(t('common.in_stock')) ?></p></div>
        <?php endif; ?>
    </section>
</div>

<div class="admin-grid admin-grid--two">
    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <span class="eyebrow"><?= icon('tag', '', 13) ?> <?= e(t('admin.top_products')) ?></span>
                <h2><?= e(t('admin.top_products')) ?></h2>
            </div>
        </div>
        <?php if ($topProducts): ?>
            <div class="admin-rank-list">
                <?php foreach ($topProducts as $index => $product): ?>
                    <div class="admin-rank">
                        <span class="admin-rank__number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <img src="<?= asset('img/' . $product['image']) ?>" alt="" width="40" height="40" loading="lazy">
                        <span class="admin-rank__info"><strong><?= e(localized($product, 'name')) ?></strong><small><?= e(t('admin.units_sold', ['count' => (int) $product['units']])) ?></small></span>
                        <strong><?= money((float) $product['revenue']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="admin-empty"><?= icon('chart', '', 26) ?><p><?= e(t('admin.no_data')) ?></p></div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card__head">
            <div>
                <span class="eyebrow"><?= icon('star', '', 13) ?> <?= e(t('admin.stat_reviews')) ?></span>
                <h2><?= e(t('admin.recent_reviews')) ?> · <?= number_format((float) $reviewsAvg, 1, ',', '.') ?>/5</h2>
            </div>
        </div>
        <?php if ($recentReviews): ?>
            <div class="admin-review-list">
                <?php foreach ($recentReviews as $review): ?>
                    <a class="admin-review" href="<?= url('/produto/' . $review['product_slug']) ?>#reviews" target="_blank" rel="noopener">
                        <span class="admin-review__head"><strong><?= e($review['user_name']) ?></strong><?= stars((float) $review['rating']) ?></span>
                        <p><?= e(mb_strimwidth((string) ($review['body'] ?? ''), 0, 120, '…')) ?></p>
                        <small><?= e($review['product_name']) ?> · <?= e(format_date($review['created_at'])) ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="admin-empty"><?= icon('star', '', 26) ?><p><?= e(t('admin.no_data')) ?></p></div>
        <?php endif; ?>
    </section>
</div>
