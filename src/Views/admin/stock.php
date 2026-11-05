<?php
/** @var array $variants */
/** @var array $summary */
/** @var array $filters */
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('box', '', 13) ?> <?= e(t('admin.catalog')) ?></span>
        <h1><?= e(t('admin.stock_title')) ?></h1>
        <p><?= e(t('admin.stock_subtitle')) ?></p>
    </div>
    <a class="btn btn--ghost btn--sm" href="<?= url('/admin/stock?state=out') ?>"><?= icon('alert', '', 15) ?> <?= e(t('admin.out_stock')) ?>: <?= (int) ($summary['out_of_stock'] ?? 0) ?></a>
</div>

<div class="admin-kpis admin-kpis--compact">
    <article class="admin-kpi">
        <span class="admin-kpi__label"><?= e(t('admin.stock_units')) ?></span>
        <strong><?= number_format((int) ($summary['units'] ?? 0), 0, ',', '.') ?></strong>
        <span class="admin-kpi__icon admin-kpi__icon--green"><?= icon('box', '', 20) ?></span>
    </article>
    <article class="admin-kpi">
        <span class="admin-kpi__label"><?= e(t('admin.variant')) ?></span>
        <strong><?= (int) ($summary['variants'] ?? 0) ?></strong>
        <span class="admin-kpi__icon admin-kpi__icon--blue"><?= icon('list', '', 20) ?></span>
    </article>
    <article class="admin-kpi">
        <span class="admin-kpi__label"><?= e(t('admin.low_stock')) ?></span>
        <strong><?= (int) ($summary['low_stock'] ?? 0) ?></strong>
        <span class="admin-kpi__icon admin-kpi__icon--amber"><?= icon('alert', '', 20) ?></span>
    </article>
    <article class="admin-kpi">
        <span class="admin-kpi__label"><?= e(t('admin.out_stock')) ?></span>
        <strong><?= (int) ($summary['out_of_stock'] ?? 0) ?></strong>
        <span class="admin-kpi__icon admin-kpi__icon--red"><?= icon('close', '', 20) ?></span>
    </article>
</div>

<form class="admin-filters" method="get" action="<?= url('/admin/stock') ?>">
    <label class="admin-search">
        <?= icon('search', '', 16) ?>
        <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e(t('admin.search_placeholder')) ?>">
    </label>
    <select name="state">
        <option value=""><?= e(t('admin.all_statuses')) ?></option>
        <option value="low" <?= $filters['state'] === 'low' ? 'selected' : '' ?>><?= e(t('admin.low_stock')) ?></option>
        <option value="out" <?= $filters['state'] === 'out' ? 'selected' : '' ?>><?= e(t('admin.out_stock')) ?></option>
    </select>
    <button class="btn btn--primary btn--sm" type="submit"><?= icon('filter', '', 15) ?> <?= e(t('admin.filter')) ?></button>
    <a class="btn btn--ghost btn--sm" href="<?= url('/admin/stock') ?>"><?= e(t('admin.reset')) ?></a>
</form>

<div class="admin-card admin-card--flush">
    <?php if ($variants): ?>
        <div class="table-wrap">
            <table class="admin-table admin-table--stock">
                <thead>
                    <tr>
                        <th><?= e(t('admin.product')) ?></th>
                        <th><?= e(t('admin.sku')) ?></th>
                        <th><?= e(t('admin.variant')) ?></th>
                        <th><?= e(t('common.status')) ?></th>
                        <th><?= e(t('admin.stock_units')) ?></th>
                        <th><?= e(t('admin.update_stock')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($variants as $variant): ?>
                        <?php $stock = (int) $variant['stock']; ?>
                        <tr id="variant-<?= (int) $variant['id'] ?>">
                            <td data-label="<?= e(t('admin.product')) ?>">
                                <div class="table-product">
                                    <img src="<?= asset('img/' . $variant['image']) ?>" alt="" width="42" height="42" loading="lazy">
                                    <span><strong><?= e(localized($variant, 'name')) ?></strong><small><?= e($variant['brand']) ?></small></span>
                                </div>
                            </td>
                            <td data-label="<?= e(t('admin.sku')) ?>"><code class="code-chip"><?= e($variant['sku']) ?></code></td>
                            <td data-label="<?= e(t('admin.variant')) ?>"><strong><?= e($variant['size']) ?></strong><small class="muted"><?= e($variant['color']) ?></small></td>
                            <td data-label="<?= e(t('common.status')) ?>">
                                <?php if ($stock === 0): ?>
                                    <span class="status-pill status-red"><?= e(t('admin.out_stock')) ?></span>
                                <?php elseif ($stock <= 5): ?>
                                    <span class="status-pill status-amber"><?= e(t('admin.low_stock')) ?></span>
                                <?php else: ?>
                                    <span class="status-pill status-green"><?= e(t('common.in_stock')) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?= e(t('admin.stock_units')) ?>"><strong class="stock-value"><?= $stock ?></strong></td>
                            <td data-label="<?= e(t('admin.update_stock')) ?>">
                                <form class="stock-form" action="<?= url('/admin/stock/' . $variant['id']) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="q" value="<?= e($filters['q']) ?>">
                                    <input type="hidden" name="state" value="<?= e($filters['state']) ?>">
                                    <div class="stock-stepper">
                                        <button type="button" class="stock-stepper__btn" data-stock-step="-1" aria-label="<?= e(t('common.decrease')) ?>"><?= icon('minus', '', 13) ?></button>
                                        <input type="number" name="stock" min="0" max="9999" value="<?= $stock ?>" aria-label="<?= e(t('admin.stock_units')) ?>">
                                        <button type="button" class="stock-stepper__btn" data-stock-step="1" aria-label="<?= e(t('common.increase')) ?>"><?= icon('plus', '', 13) ?></button>
                                    </div>
                                    <button class="btn btn--ghost btn--sm" type="submit"><?= e(t('common.save')) ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="admin-empty admin-empty--lg"><?= icon('box', '', 34) ?><p><?= e(t('common.no_results')) ?></p><a class="btn btn--ghost btn--sm" href="<?= url('/admin/stock') ?>"><?= e(t('admin.reset')) ?></a></div>
    <?php endif; ?>
</div>
