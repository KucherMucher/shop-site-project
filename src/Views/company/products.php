<?php
/** @var array $company */
/** @var array $products */
$counts = ['active' => 0, 'pending' => 0, 'rejected' => 0];
foreach ($products as $product) {
    $counts[$product['status']] = ($counts[$product['status']] ?? 0) + 1;
}
?>
<div class="company-section-head">
    <div>
        <span class="eyebrow"><?= icon('box', '', 13) ?> <?= e(t('company.products')) ?></span>
        <h2><?= e(t('company.products')) ?></h2>
        <p><?= e(t('company.products_text')) ?></p>
    </div>
    <a class="btn btn--primary btn--sm" href="<?= url('/empresa/produtos/novo') ?>"><?= icon('plus', '', 16) ?> <?= e(t('company.new_product')) ?></a>
</div>

<div class="company-toolbar">
    <div class="company-filters" data-company-filters>
        <button type="button" class="is-active" data-company-filter="all"><?= e(t('common.all')) ?> <span><?= count($products) ?></span></button>
        <button type="button" data-company-filter="active"><?= e(t('company.proposal_approved')) ?> <span><?= $counts['active'] ?></span></button>
        <button type="button" data-company-filter="pending"><?= e(t('company.proposal_pending')) ?> <span><?= $counts['pending'] ?></span></button>
        <button type="button" data-company-filter="rejected"><?= e(t('company.proposal_rejected')) ?> <span><?= $counts['rejected'] ?></span></button>
    </div>
</div>

<?php if (!$products): ?>
    <div class="empty-state empty-state--panel">
        <span class="empty-state__icon"><?= icon('box', '', 38) ?></span>
        <h3><?= e(t('company.no_products')) ?></h3>
        <p><?= e(t('company.product_form_intro')) ?></p>
        <a class="btn btn--primary" href="<?= url('/empresa/produtos/novo') ?>"><?= icon('plus', '', 16) ?> <?= e(t('company.new_product')) ?></a>
    </div>
<?php else: ?>
    <div class="company-panel" data-company-products>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= e(t('admin.product')) ?></th>
                        <th><?= e(t('admin.sku')) ?></th>
                        <th><?= e(t('admin.stock_units')) ?></th>
                        <th><?= e(t('company.base_price')) ?></th>
                        <th><?= e(t('account.order_status')) ?></th>
                        <th><?= e(t('admin.created_at')) ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr data-product-status="<?= e($product['status']) ?>">
                            <td data-label="<?= e(t('admin.product')) ?>">
                                <div class="table-product">
                                    <img src="<?= asset('img/' . $product['image']) ?>" alt="" width="46" height="46" loading="lazy">
                                    <span><strong><?= e(localized($product, 'name')) ?></strong><small><?= e($product['brand']) ?></small></span>
                                </div>
                            </td>
                            <td data-label="<?= e(t('admin.sku')) ?>"><code class="code-chip"><?= e($product['id']) ?></code></td>
                            <td data-label="<?= e(t('admin.stock_units')) ?>"><?= (int) ($product['total_stock'] ?? 0) ?> <small class="muted">/ <?= (int) ($product['variants_count'] ?? 0) ?> var.</small></td>
                            <td data-label="<?= e(t('company.base_price')) ?>"><strong><?= money(product_price($product)) ?></strong><?php if (discount_percent($product) > 0): ?><small class="muted price-old"><del><?= money((float) $product['base_price']) ?></del></small><?php endif; ?></td>
                            <td data-label="<?= e(t('account.order_status')) ?>"><span class="status-pill <?= $product['status'] === 'active' ? 'status-green' : ($product['status'] === 'pending' ? 'status-amber' : 'status-red') ?>"><?= e(t('company.proposal_' . $product['status'])) ?></span></td>
                            <td data-label="<?= e(t('admin.created_at')) ?>"><?= e(format_date($product['created_at'])) ?></td>
                            <td class="data-table__action">
                                <div class="data-table__actions">
                                    <a class="btn btn--ghost btn--sm" href="<?= url('/empresa/produtos/' . $product['id'] . '/editar') ?>"><?= icon('edit', '', 14) ?> <?= e(t('common.edit')) ?></a>
                                    <?php if ($product['status'] === 'active'): ?>
                                        <a class="btn btn--ghost btn--sm" href="<?= url('/produto/' . $product['slug']) ?>" target="_blank" rel="noopener"><?= icon('external', '', 14) ?> <?= e(t('company.view_catalog')) ?></a>
                                    <?php elseif ($product['rejection_note']): ?>
                                        <span class="muted"><?= e($product['rejection_note']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
