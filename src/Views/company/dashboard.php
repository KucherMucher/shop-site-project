<?php
/** @var array $company */
/** @var array $stats */
/** @var array $products */
/** @var array $orders */
?>
<div class="company-section-head">
    <div>
        <span class="eyebrow"><?= icon('chart', '', 13) ?> <?= e(t('company.overview')) ?></span>
        <h2><?= e(t('company.dashboard_title')) ?></h2>
        <p><?= e(t('company.dashboard_text')) ?></p>
    </div>
    <a class="btn btn--primary btn--sm" href="<?= url('/empresa/produtos/novo') ?>"><?= icon('plus', '', 16) ?> <?= e(t('company.new_product')) ?></a>
</div>

<div class="company-stats">
    <article class="company-stat">
        <span class="company-stat__icon"><?= icon('box', '', 20) ?></span>
        <div><strong><?= (int) $stats['products'] ?></strong><span><?= e(t('company.stat_products')) ?></span></div>
    </article>
    <article class="company-stat">
        <span class="company-stat__icon company-stat__icon--amber"><?= icon('clock', '', 20) ?></span>
        <div><strong><?= (int) $stats['pending'] ?></strong><span><?= e(t('company.stat_pending')) ?></span></div>
    </article>
    <article class="company-stat">
        <span class="company-stat__icon company-stat__icon--blue"><?= icon('package', '', 20) ?></span>
        <div><strong><?= (int) $stats['units'] ?></strong><span><?= e(t('company.stat_orders')) ?></span></div>
    </article>
    <article class="company-stat">
        <span class="company-stat__icon company-stat__icon--green"><?= icon('credit-card', '', 20) ?></span>
        <div><strong><?= money((float) $stats['revenue']) ?></strong><span><?= e(t('company.stat_revenue')) ?></span></div>
    </article>
</div>

<div class="company-columns">
    <section class="content-card">
        <div class="content-card__head content-card__head--row">
            <div>
                <span class="eyebrow"><?= icon('box', '', 13) ?> <?= e(t('company.products')) ?></span>
                <h2><?= e(t('company.products')) ?></h2>
            </div>
            <a class="link-arrow" href="<?= url('/empresa/produtos') ?>"><?= e(t('admin.view_all')) ?> <?= icon('arrow-right', '', 15) ?></a>
        </div>

        <?php if ($products): ?>
            <div class="company-products">
                <?php foreach ($products as $product): ?>
                    <article class="company-product">
                        <img src="<?= asset('img/' . $product['image']) ?>" alt="" width="60" height="60" loading="lazy">
                        <div class="company-product__info">
                            <strong><?= e(localized($product, 'name')) ?></strong>
                            <small><?= e($product['brand']) ?> · <?= e($product['category_name_pt'] ?? '') ?></small>
                        </div>
                        <div class="company-product__meta">
                            <span class="status-pill <?= $product['status'] === 'active' ? 'status-green' : ($product['status'] === 'pending' ? 'status-amber' : 'status-red') ?>"><?= e(t('company.proposal_' . $product['status'])) ?></span>
                            <small><?= (int) ($product['total_stock'] ?? 0) ?> <?= e(t('admin.stock_units')) ?></small>
                        </div>
                        <strong class="company-product__price"><?= money(product_price($product)) ?></strong>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state empty-state--sm">
                <?= icon('box', '', 30) ?>
                <p><?= e(t('company.no_products')) ?></p>
                <a class="btn btn--ghost btn--sm" href="<?= url('/empresa/produtos/novo') ?>"><?= e(t('company.new_product')) ?></a>
            </div>
        <?php endif; ?>
    </section>

    <section class="content-card">
        <div class="content-card__head">
            <span class="eyebrow"><?= icon('package', '', 13) ?> <?= e(t('company.sales')) ?></span>
            <h2><?= e(t('company.orders_of_products')) ?></h2>
        </div>

        <?php if ($orders): ?>
            <div class="company-orders">
                <?php foreach ($orders as $order): ?>
                    <div class="company-order">
                        <span class="company-order__icon"><?= icon('package', '', 18) ?></span>
                        <span class="company-order__info">
                            <strong><?= e($order['order_number']) ?></strong>
                            <small><?= e(format_date($order['created_at'], true)) ?> · <?= e($order['user_name'] ?? $order['email']) ?></small>
                        </span>
                        <span class="status-pill <?= order_status_class($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span>
                        <strong><?= money((float) $order['total']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state empty-state--sm">
                <?= icon('package', '', 30) ?>
                <p><?= e(t('company.no_sales')) ?></p>
            </div>
        <?php endif; ?>
    </section>
</div>

<div class="company-cta">
    <div>
        <?= icon('sparkles', '', 22) ?>
        <span><strong><?= e(t('company.cta_title')) ?></strong><small><?= e(t('company.cta_text')) ?></small></span>
    </div>
    <a class="btn btn--accent" href="<?= url('/empresa/produtos/novo') ?>"><?= icon('plus', '', 16) ?> <?= e(t('company.new_product')) ?></a>
</div>
