<?php
/** @var array $user */
/** @var array $stats */
/** @var array $recentOrders */
/** @var array $reviews */
$firstName = explode(' ', $user['name'])[0];
?>
<div class="section-head section-head--account">
    <div>
        <span class="eyebrow"><?= icon('dashboard', '', 13) ?> <?= e(t('account.nav_overview')) ?></span>
        <h2><?= e(t('account.hello', ['name' => $firstName])) ?></h2>
        <p><?= e(t('account.overview_subtitle')) ?></p>
    </div>
    <a class="btn btn--primary btn--sm" href="<?= url('/loja') ?>"><?= icon('grid', '', 16) ?> <?= e(t('nav.shop')) ?></a>
</div>

<div class="account-stats">
    <article class="account-stat">
        <span class="account-stat__icon"><?= icon('package', '', 20) ?></span>
        <div><strong><?= (int) $stats['orders_count'] ?></strong><span><?= e(t('account.stat_orders')) ?></span></div>
    </article>
    <article class="account-stat">
        <span class="account-stat__icon account-stat__icon--green"><?= icon('credit-card', '', 20) ?></span>
        <div><strong><?= money((float) $stats['total_spent']) ?></strong><span><?= e(t('account.stat_spent')) ?></span></div>
    </article>
    <article class="account-stat">
        <span class="account-stat__icon account-stat__icon--amber"><?= icon('box', '', 20) ?></span>
        <div><strong><?= (int) $stats['products_count'] ?></strong><span><?= e(t('account.stat_products')) ?></span></div>
    </article>
    <article class="account-stat">
        <span class="account-stat__icon account-stat__icon--purple"><?= icon('star', '', 20) ?></span>
        <div><strong><?= (int) $stats['reviews'] ?></strong><span><?= e(t('account.stat_reviews')) ?></span></div>
    </article>
</div>

<div class="account-columns">
    <section class="content-card account-card">
        <div class="content-card__head content-card__head--row">
            <div>
                <span class="eyebrow"><?= icon('package', '', 13) ?> <?= e(t('account.nav_orders')) ?></span>
                <h2><?= e(t('account.recent_orders')) ?></h2>
            </div>
            <a class="link-arrow" href="<?= url('/conta/encomendas') ?>"><?= e(t('account.view_all')) ?> <?= icon('arrow-right', '', 15) ?></a>
        </div>

        <?php if ($recentOrders): ?>
            <div class="account-orders-list">
                <?php foreach ($recentOrders as $order): ?>
                    <a class="account-order" href="<?= url('/conta/encomendas/' . $order['id']) ?>">
                        <span class="account-order__icon"><?= icon('package', '', 19) ?></span>
                        <span class="account-order__info">
                            <strong><?= e($order['order_number']) ?></strong>
                            <small><?= e(format_date($order['created_at'], true)) ?></small>
                        </span>
                        <span class="status-pill <?= order_status_class($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span>
                        <strong class="account-order__total"><?= money((float) $order['total']) ?></strong>
                        <?= icon('chevron-right', '', 16) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state empty-state--sm">
                <?= icon('package', '', 30) ?>
                <p><?= e(t('account.orders_empty')) ?></p>
                <a class="btn btn--ghost btn--sm" href="<?= url('/loja') ?>"><?= e(t('cart.continue_shopping')) ?></a>
            </div>
        <?php endif; ?>
    </section>

    <section class="content-card account-card">
        <div class="content-card__head content-card__head--row">
            <div>
                <span class="eyebrow"><?= icon('star', '', 13) ?> <?= e(t('account.review_history')) ?></span>
                <h2><?= e(t('account.review_history')) ?></h2>
            </div>
        </div>

        <?php if ($reviews): ?>
            <div class="account-reviews">
                <?php foreach ($reviews as $review): ?>
                    <a class="account-review" href="<?= url('/produto/' . $review['slug']) ?>#reviews">
                        <img src="<?= asset('img/' . $review['image']) ?>" alt="" width="52" height="52" loading="lazy">
                        <span class="account-review__info">
                            <strong><?= e(localized($review, 'name')) ?></strong>
                            <?= stars((float) $review['rating']) ?>
                            <?php if (!empty($review['title'])): ?><small><?= e($review['title']) ?></small><?php endif; ?>
                        </span>
                        <?= icon('chevron-right', '', 15) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state empty-state--sm">
                <?= icon('message', '', 30) ?>
                <p><?= e(t('account.no_reviews')) ?></p>
                <a class="btn btn--ghost btn--sm" href="<?= url('/loja') ?>"><?= e(t('product.write_review')) ?></a>
            </div>
        <?php endif; ?>
    </section>
</div>

<div class="account-quick">
    <a href="<?= url('/conta/perfil') ?>"><?= icon('user', '', 20) ?><span><strong><?= e(t('account.nav_profile')) ?></strong><small><?= e(t('account.profile_subtitle')) ?></small></span><?= icon('arrow-right', '', 15) ?></a>
    <a href="<?= url('/conta/moradas') ?>"><?= icon('map-pin', '', 20) ?><span><strong><?= e(t('account.nav_addresses')) ?></strong><small><?= e(t('account.addresses_subtitle')) ?></small></span><?= icon('arrow-right', '', 15) ?></a>
    <a href="<?= url('/suporte') ?>"><?= icon('headset', '', 20) ?><span><strong><?= e(t('account.nav_support')) ?></strong><small><?= e(t('pages.help_text')) ?></small></span><?= icon('arrow-right', '', 15) ?></a>
</div>
